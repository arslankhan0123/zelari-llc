<?php
namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function home()
    {
        $parentCategories = Category::whereNull('parent_id')
            ->where('status', 'active')
            ->with('childrenRecursive')
            ->orderBy('name')
            ->get();
        $countMap = Category::productCountMap();
        $parentCategories->each(fn($c) => $c->assignCounts($countMap));

        $base = fn() => Product::where('status', 'active')->with(['category', 'images']);

        // Hero: products that have an image, for the 3D floating visuals
        $heroProducts = $base()->whereNotNull('feature_image')->latest()->take(3)->get();
        if ($heroProducts->isEmpty()) {
            $heroProducts = $base()->latest()->take(3)->get();
        }

        // Category sections (first 3 parent categories that actually have products)
        $categorySections = collect();
        foreach ($parentCategories as $cat) {
            $ids = $cat->descendantIds();
            $products = $base()->whereIn('category_id', $ids)->latest()->take(10)->get();
            if ($products->count() >= 1) {
                $categorySections->push(['category' => $cat, 'products' => $products]);
            }
            if ($categorySections->count() >= 3) break;
        }

        $newArrivals = $base()->latest()->take(10)->get();

        $deals = $base()->whereNotNull('sale_price')
            ->whereColumn('sale_price', '<', 'purchase_price')
            ->orderByRaw('(purchase_price - sale_price) / purchase_price DESC')
            ->take(10)->get();
        if ($deals->isEmpty()) $deals = $newArrivals;

        // Top selling by ordered quantity
        $topIds = OrderItem::selectRaw('product_id, SUM(quantity) as qty')
            ->whereNotNull('product_id')
            ->groupBy('product_id')->orderByDesc('qty')->take(4)->pluck('product_id');
        $topSelling = $topIds->isNotEmpty()
            ? $base()->whereIn('id', $topIds)->get()->sortBy(fn($p) => array_search($p->id, $topIds->all()))->values()
            : $newArrivals->take(4);

        $trending      = $deals->take(4);
        $recentlyAdded = $newArrivals->take(4);
        $topRated      = $base()->inRandomOrder()->take(4)->get();

        return view('shop.home', compact(
            'parentCategories', 'heroProducts', 'categorySections', 'newArrivals',
            'deals', 'topSelling', 'trending', 'recentlyAdded', 'topRated'
        ));
    }

    public function index(Request $request)
    {
        $query = Product::where('status', 'active')
            ->with(['category', 'images']);

        // Category / subcategory filter
        $selectedCategory = $request->filled('category')
            ? Category::where('slug', $request->category)->with('childrenRecursive', 'parent')->first()
            : null;

        if ($selectedCategory) {
            $query->whereIn('category_id', $selectedCategory->descendantIds());
        }

        // Price range — typed in the visitor's currency, compared in the base currency
        $rate = (float) (zcur()['rate'] ?: 1);
        if ($request->filled('min_price')) {
            $query->whereRaw('COALESCE(sale_price, purchase_price) >= ?', [(float) $request->min_price / $rate]);
        }
        if ($request->filled('max_price')) {
            $query->whereRaw('COALESCE(sale_price, purchase_price) <= ?', [(float) $request->max_price / $rate]);
        }

        // Brand filter (used by the Brands page links)
        $selectedBrand = $request->filled('brand')
            ? Manufacturer::where('slug', $request->brand)->first()
            : null;
        if ($selectedBrand) {
            $query->where('manufacturer_id', $selectedBrand->id);
        }

        // In stock only
        if ($request->boolean('in_stock')) {
            $query->where('quantity', '>', 0);
        }

        // On sale only
        if ($request->boolean('on_sale')) {
            $query->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'purchase_price');
        }

        // Search
        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(fn($q) =>
                $q->where('name', 'like', "%$term%")
                  ->orWhere('short_description', 'like', "%$term%")
                  ->orWhere('description', 'like', "%$term%")
            );
        }

        // Sorting
        match ($request->sort) {
            'price_asc'  => $query->orderByRaw('COALESCE(sale_price, purchase_price) ASC'),
            'price_desc' => $query->orderByRaw('COALESCE(sale_price, purchase_price) DESC'),
            'name_asc'   => $query->orderBy('name'),
            'discount'   => $query->orderByRaw('CASE WHEN sale_price IS NULL THEN 0 ELSE (purchase_price - sale_price)/purchase_price END DESC'),
            default      => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        $categories = Category::whereNull('parent_id')
            ->where('status', 'active')
            ->with('childrenRecursive')
            ->orderBy('name')
            ->get();
        $countMap = Category::productCountMap();
        $categories->each(fn($c) => $c->assignCounts($countMap));

        // Sub-category view: also offer products from the rest of the parent category
        $moreProducts   = collect();
        $parentCategory = null;
        if ($selectedCategory && $selectedCategory->parent) {
            $parentCategory = Category::with('childrenRecursive')->find($selectedCategory->parent_id);
            $moreProducts = Product::where('status', 'active')
                ->whereIn('category_id', array_diff($parentCategory->descendantIds(), $selectedCategory->descendantIds()))
                ->with(['category', 'images'])
                ->latest()->take(10)->get();
        }

        $priceBounds = Product::where('status', 'active')
            ->selectRaw('MIN(COALESCE(sale_price, purchase_price)) as min_p, MAX(COALESCE(sale_price, purchase_price)) as max_p')
            ->first();

        return view('shop.index', compact('products', 'categories', 'selectedCategory', 'priceBounds', 'moreProducts', 'parentCategory'));
    }

    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('status', 'active')
            ->with(['category.parent', 'images'])
            ->firstOrFail();

        $related = Product::where('status', 'active')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['images', 'category'])
            ->take(10)
            ->get();

        if ($related->count() < 4) {
            $more = Product::where('status', 'active')
                ->where('id', '!=', $product->id)
                ->whereNotIn('id', $related->pluck('id'))
                ->with(['images', 'category'])
                ->latest()->take(10 - $related->count())->get();
            $related = $related->concat($more);
        }

        // Reviews: only approved ones are public
        $reviews   = $product->approvedReviews()->get();
        $breakdown = $product->ratingBreakdown();
        $user      = request()->user();

        // Already reviewed? (matched by account or by the e-mail they used)
        $myReview = $product->reviewBy($user);

        // Logged-in customers whose account already shows the purchase skip the order form
        $canReview = $user && !$myReview && $product->purchaseOrderFor($user) !== null;

        // Guests get the order number pre-filled when they just checked out
        $sessionOrder = null;
        if (!$canReview && session('last_order_id')) {
            $sessionOrder = \App\Models\Order::where('id', session('last_order_id'))
                ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
                ->first();
            if ($sessionOrder && !$myReview) {
                $myReview = $product->reviewByEmail($sessionOrder->customer_email);
            }
        }

        return view('shop.show', compact('product', 'related', 'reviews', 'breakdown', 'myReview', 'canReview', 'sessionOrder'));
    }

    /** /brands — every active manufacturer, featured ones first. */
    public function brands()
    {
        $brands = Manufacturer::active()
            ->withCount(['products' => fn ($q) => $q->where('status', 'active')])
            ->ordered()
            ->get();

        $stats = [
            'brands'    => $brands->count(),
            'products'  => (int) $brands->sum('products_count'),
            'countries' => $brands->pluck('country')->filter()->map(fn ($c) => trim($c))->unique()->count(),
            'oldest'    => $brands->pluck('established_year')->filter()->min(),
        ];

        return view('shop.brands', compact('brands', 'stats'));
    }

    public function about()
    {
        return view('shop.about');
    }

    public function contact(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'name'    => 'required|string|max:100',
                'email'   => 'required|email|max:150',
                'subject' => 'nullable|string|max:200',
                'message' => 'required|string|min:10',
            ]);

            // Saved first, so a message is never lost when SMTP is down or unconfigured.
            $contact = \App\Models\ContactMessage::create($data + [
                'ip'         => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
            ]);

            $this->notifyAdminOfContact($contact);

            $done = 'Thank you! Your message has been received. We\'ll get back to you soon.';

            // The form posts over fetch(); a validation failure already returns 422 JSON on its own.
            if ($request->expectsJson()) {
                return response()->json(['message' => $done]);
            }

            return back()->with('success', $done);
        }

        return view('shop.contact');
    }

    /** Email the contact message to the admin over the SMTP saved in Settings. Never breaks the form. */
    private function notifyAdminOfContact(\App\Models\ContactMessage $contact): void
    {
        $to = trim((string) \App\Models\Setting::get('mail_admin_address'));

        try {
            if ($to === '') {
                $contact->update(['email_error' => 'No admin notification email set in Settings.']);
                return;
            }
            if (!\App\Models\Setting::applyMailConfig()) {
                $contact->update(['email_error' => 'SMTP is not configured in Settings.']);
                return;
            }

            \Illuminate\Support\Facades\Mail::to($to)->send(new \App\Mail\ContactMessageReceived($contact));

            $contact->update(['emailed' => true, 'email_error' => null]);
            \App\Models\Setting::recordMailResult(null);
        } catch (\Throwable $e) {
            report($e);
            $contact->update(['email_error' => mb_substr($e->getMessage(), 0, 255)]);
            \App\Models\Setting::recordMailResult('Contact message #' . $contact->id . ': ' . $e->getMessage());
        }
    }

}
