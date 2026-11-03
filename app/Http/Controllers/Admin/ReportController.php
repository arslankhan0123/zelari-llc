<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\VendorOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private function vid(): ?int { return auth()->user()->vendorId(); }

    public function index()
    {
        $vid = $this->vid();

        if ($vid) {
            return $this->vendorReport($vid);
        }

        $paid = Order::where('payment_status', 'paid');
        $revenue   = (float) (clone $paid)->sum('total');
        $orders    = Order::count();
        $paidCount = (clone $paid)->count();
        $aov       = $paidCount ? $revenue / $paidCount : 0;

        $byStatus = Order::select('status', DB::raw('count(*) as c'))->groupBy('status')->pluck('c', 'status');

        $series = $this->dailySeries(
            Order::where('payment_status', 'paid'),
            'total'
        );

        $topProducts = OrderItem::select('product_name', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(subtotal) as revenue'))
            ->groupBy('product_name')->orderByDesc('revenue')->take(10)->get();

        $segments = $this->segmentCounts();

        return view('admin.reports.index', compact('revenue', 'orders', 'paidCount', 'aov', 'byStatus', 'series', 'topProducts', 'segments'));
    }

    private function vendorReport(int $vid)
    {
        $paidItems = OrderItem::where('vendor_id', $vid)
            ->whereHas('order', fn ($o) => $o->where('payment_status', 'paid'));

        $revenue   = (float) (clone $paidItems)->sum('subtotal');
        $orders    = Order::whereHas('items', fn ($q) => $q->where('vendor_id', $vid))->count();
        $paidCount = Order::where('payment_status', 'paid')->whereHas('items', fn ($q) => $q->where('vendor_id', $vid))->count();
        $aov       = $paidCount ? $revenue / $paidCount : 0;

        $byStatus = VendorOrder::where('vendor_id', $vid)->select('status', DB::raw('count(*) as c'))
            ->groupBy('status')->pluck('c', 'status');

        // daily revenue from their paid line items
        $since = now()->subDays(29)->startOfDay();
        $daily = OrderItem::where('order_items.vendor_id', $vid)
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', 'paid')->where('orders.created_at', '>=', $since)
            ->select(DB::raw('DATE(orders.created_at) as d'), DB::raw('SUM(order_items.subtotal) as t'))
            ->groupBy('d')->pluck('t', 'd');
        $series = [];
        for ($i = 0; $i < 30; $i++) {
            $day = now()->subDays(29 - $i)->format('Y-m-d');
            $series[$day] = (float) ($daily[$day] ?? 0);
        }

        $topProducts = OrderItem::where('vendor_id', $vid)
            ->select('product_name', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(subtotal) as revenue'))
            ->groupBy('product_name')->orderByDesc('revenue')->take(10)->get();

        $segments = ['new' => 0, 'repeat' => 0, 'vip' => 0, 'lapsed' => 0]; // segmentation is platform-level

        return view('admin.reports.index', compact('revenue', 'orders', 'paidCount', 'aov', 'byStatus', 'series', 'topProducts', 'segments'));
    }

    private function dailySeries($query, string $col): array
    {
        $since = now()->subDays(29)->startOfDay();
        $daily = (clone $query)->where('created_at', '>=', $since)
            ->select(DB::raw('DATE(created_at) as d'), DB::raw("SUM($col) as t"))
            ->groupBy('d')->pluck('t', 'd');
        $series = [];
        for ($i = 0; $i < 30; $i++) {
            $day = now()->subDays(29 - $i)->format('Y-m-d');
            $series[$day] = (float) ($daily[$day] ?? 0);
        }
        return $series;
    }

    private function segmentCounts(): array
    {
        $seg = ['new' => 0, 'repeat' => 0, 'vip' => 0, 'lapsed' => 0];
        foreach ($this->customerStats() as $r) {
            $seg[$this->segmentOf($r)]++;
        }
        return $seg;
    }

    private function customerStats()
    {
        return Customer::select('customers.id', 'customers.name', 'customers.email', 'customers.created_at')
            ->selectRaw('COUNT(orders.id) as orders_count')
            ->selectRaw('COALESCE(SUM(CASE WHEN orders.payment_status = "paid" THEN orders.total ELSE 0 END),0) as spent')
            ->selectRaw('MAX(orders.created_at) as last_order')
            ->leftJoin('orders', 'orders.customer_id', '=', 'customers.id')
            ->groupBy('customers.id', 'customers.name', 'customers.email', 'customers.created_at')
            ->get();
    }

    private function segmentOf($r): string
    {
        $spent = (float) $r->spent; $count = (int) $r->orders_count;
        $last  = $r->last_order ? \Carbon\Carbon::parse($r->last_order) : null;
        if ($count === 0) return 'new';
        if ($spent >= 50000) return 'vip';
        if ($last && $last->lt(now()->subDays(180))) return 'lapsed';
        if ($count >= 2) return 'repeat';
        return 'new';
    }

    public function exportSales(): StreamedResponse
    {
        $vid = $this->vid();
        $filename = 'sales-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($vid) {
            $out = fopen('php://output', 'w');
            if ($vid) {
                fputcsv($out, ['Sub #', 'Order', 'Date', 'Subtotal', 'Commission', 'Payout', 'Status']);
                VendorOrder::where('vendor_id', $vid)->with('order:id,order_number,created_at')->latest()->chunk(200, function ($chunk) use ($out) {
                    foreach ($chunk as $vo) {
                        fputcsv($out, [$vo->sub_number, optional($vo->order)->order_number, optional($vo->order)->created_at?->format('Y-m-d'),
                            number_format((float) $vo->subtotal, 2, '.', ''), number_format((float) $vo->commission_amount, 2, '.', ''),
                            number_format((float) $vo->payout_amount, 2, '.', ''), ucfirst($vo->status)]);
                    }
                });
            } else {
                fputcsv($out, ['Order', 'Date', 'Customer', 'Email', 'Status', 'Payment', 'Subtotal', 'Shipping', 'Tax', 'Total']);
                Order::with('customer')->latest()->chunk(200, function ($chunk) use ($out) {
                    foreach ($chunk as $o) {
                        fputcsv($out, [$o->order_number, $o->created_at?->format('Y-m-d H:i'), optional($o->customer)->name, $o->customer_email,
                            $o->status, $o->payment_status, number_format((float) $o->subtotal, 2, '.', ''),
                            number_format((float) $o->shipping_fee, 2, '.', ''), number_format((float) $o->tax, 2, '.', ''),
                            number_format((float) $o->total, 2, '.', '')]);
                    }
                });
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportSegments(): StreamedResponse
    {
        abort_if($this->vid(), 403, 'Customer segmentation is available to the platform admin only.');
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Customer', 'Email', 'Orders', 'Total spent', 'Last order', 'Segment']);
            foreach ($this->customerStats() as $r) {
                fputcsv($out, [$r->name, $r->email, (int) $r->orders_count, number_format((float) $r->spent, 2, '.', ''),
                    $r->last_order ? \Carbon\Carbon::parse($r->last_order)->format('Y-m-d') : '', $this->segmentOf($r)]);
            }
            fclose($out);
        }, 'customer-segments-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }
}
