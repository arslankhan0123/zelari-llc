<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorOrder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EarningsController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user?->isVendor()) {
            $vendor = $user->vendor;
            abort_unless($vendor, 403, 'No vendor profile found for your account.');
            $summary = $vendor->earnings();
            $rows = $vendor->vendorOrders()->with('order:id,order_number,created_at')
                        ->latest()->paginate(15);
            return view('admin.earnings.index', [
                'isVendor' => true,
                'vendor'   => $vendor,
                'summary'  => $summary,
                'rows'     => $rows,
            ]);
        }

        // Admin overview: one row per vendor.
        $vendors = Vendor::orderBy('name')->get();
        $overview = $vendors->map(function ($v) {
            return ['vendor' => $v] + $v->earnings();
        });

        $totals = [
            'gross'     => $overview->sum('gross'),
            'commission'=> $overview->sum('commission'),
            'earned'    => $overview->sum('earned'),
            'pending'   => $overview->sum('pending'),
            'paid'      => $overview->sum('paid'),
            'available' => $overview->sum('available'),
        ];

        return view('admin.earnings.index', [
            'isVendor' => false,
            'overview' => $overview,
            'totals'   => $totals,
        ]);
    }

    /** Download an earnings statement (CSV). Vendors get their own; admins can pass ?vendor=ID. */
    public function export(Request $request): StreamedResponse
    {
        $user = auth()->user();

        $query = VendorOrder::with(['order:id,order_number,created_at', 'vendor:id,name']);
        $label = 'all-vendors';

        if ($user?->isVendor()) {
            $vendor = $user->vendor;
            abort_unless($vendor, 403);
            $query->where('vendor_id', $vendor->id);
            $label = $vendor->slug ?: ('vendor-' . $vendor->id);
        } elseif ($request->filled('vendor')) {
            $query->where('vendor_id', $request->vendor);
            $label = 'vendor-' . $request->vendor;
        }

        $filename = 'earnings-' . $label . '-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Order', 'Sub #', 'Vendor', 'Date', 'Subtotal', 'Commission', 'Payout', 'Status']);
            $query->latest()->chunk(200, function ($chunk) use ($out) {
                foreach ($chunk as $vo) {
                    fputcsv($out, [
                        optional($vo->order)->order_number,
                        $vo->sub_number,
                        optional($vo->vendor)->name,
                        optional($vo->created_at)->format('Y-m-d'),
                        number_format((float) $vo->subtotal, 2, '.', ''),
                        number_format((float) $vo->commission_amount, 2, '.', ''),
                        number_format((float) $vo->payout_amount, 2, '.', ''),
                        ucfirst($vo->status),
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
