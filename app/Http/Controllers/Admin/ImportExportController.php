<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Csv\Reader;
use League\Csv\Writer;

class ImportExportController extends Controller
{
    /* ───────────────────────────────────────────
     |  EXPORT  –  products as CSV
     |  Every product field + full category info
     |  (parent / sub, each with its description
     |  and image link) + image links.
     * ─────────────────────────────────────────── */

    /** Column order shared by the export and the sample template. */
    private const COLUMNS = [
        'id', 'name', 'sku', 'slug', 'status',
        'purchase_price', 'sale_price', 'discount_percent', 'quantity',
        'short_description', 'description',
        'feature_image', 'gallery_images',
        'parent_category', 'parent_category_description', 'parent_category_image',
        'sub_category', 'sub_category_description', 'sub_category_image',
        'created_at', 'updated_at',
    ];

    /** Absolute URL of a stored image path (blank when there is none). */
    private function imageUrl(?string $path): string
    {
        if (!$path) {
            return '';
        }
        return Str::startsWith($path, ['http://', 'https://', '//'])
            ? $path
            : asset('storage/' . $path);
    }

    public function export()
    {
        $products = Product::with(['category.parent', 'images'])->orderBy('id')->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="products_export_' . now()->format('Y-m-d') . '.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($products) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, self::COLUMNS);

            foreach ($products as $p) {
                $cat    = $p->category;
                $parent = $cat?->parent;

                // Top-level category  → parent_* holds it, sub_* stays blank
                // Sub-category        → parent_* holds the parent, sub_* holds the category
                $parentCat = $parent ?: $cat;
                $subCat    = $parent ? $cat : null;

                fputcsv($handle, [
                    $p->id,
                    $p->name,
                    $p->sku ?? '',
                    $p->slug,
                    $p->status,
                    $p->purchase_price,
                    $p->sale_price ?? '',
                    $p->discount_percent !== null ? $p->discount_percent . '%' : '',
                    $p->quantity,
                    $p->short_description ?? '',
                    $p->description ?? '',
                    $this->imageUrl($p->feature_image),
                    $p->images->map(fn ($i) => $this->imageUrl($i->image_path))->filter()->implode(' | '),
                    $parentCat?->name ?? '',
                    $parentCat?->description ?? '',
                    $this->imageUrl($parentCat?->image),
                    $subCat?->name ?? '',
                    $subCat?->description ?? '',
                    $this->imageUrl($subCat?->image),
                    $p->created_at?->format('Y-m-d H:i'),
                    $p->updated_at?->format('Y-m-d H:i'),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /* ───────────────────────────────────────────
     |  SAMPLE TEMPLATE download
     |  Same columns as the export so an exported
     |  file can be edited and re-imported as is.
     * ─────────────────────────────────────────── */
    public function sampleTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="products_import_template.csv"',
        ];

        $rows = [
            self::COLUMNS,
            ['', 'Sample T-Shirt', 'SKU-001', '', 'active', '500', '450', '', '100', 'A comfortable cotton t-shirt', 'Full description of the t-shirt, shown on the product page.', '', '', 'Clothing', 'Everyday clothing for all ages', '', 'Men', "Men's clothing", '', '', ''],
            ['', 'Blue Jeans', 'SKU-002', '', 'active', '1200', '999', '', '50', 'Slim fit denim jeans', '', '', '', 'Clothing', '', '', 'Men', '', '', '', ''],
            ['', 'Running Shoes', 'SKU-003', '', 'active', '3000', '', '', '25', 'Lightweight sports shoes', '', '', '', 'Footwear', 'Shoes and sandals', '', '', '', '', '', ''],
            ['', 'Wireless Earbuds', 'SKU-004', '', 'draft', '4500', '3999', '', '60', 'Bluetooth 5.0 earbuds', '', '', '', 'Electronics', '', '', 'Audio', '', '', '', ''],
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /* ───────────────────────────────────────────
     |  IMPORT  –  show form
     * ─────────────────────────────────────────── */
    public function importForm()
    {
        return view('admin.import-export.import');
    }

    /* ───────────────────────────────────────────
     |  IMPORT  –  process uploaded CSV
     |
     |  · Parent categories are matched by name (case-insensitive) and reused;
     |    only missing ones are created.
     |  · Sub-categories are matched by name *within their parent* and created
     |    under it when missing — never duplicated.
     |  · Category image links are downloaded into storage/app/public/categories
     |    and the stored path is saved on the category.
     |  · The product is linked to the sub-category when one is given, otherwise
     |    to the parent category.
     |  · A product that already exists (same SKU, or same name) is updated in
     |    place, so importing the same file twice never duplicates products.
     * ─────────────────────────────────────────── */

    /** Images accepted for download, mapped from content type to extension. */
    private const IMAGE_TYPES = [
        'image/jpeg' => 'jpg', 'image/jpg'  => 'jpg', 'image/pjpeg' => 'jpg',
        'image/png'  => 'png', 'image/webp' => 'webp',
        'image/gif'  => 'gif', 'image/avif' => 'avif',
    ];

    private const MAX_IMAGE_BYTES = 5242880; // 5 MB

    /** Resolved categories for this run: "parentId|lowername" => Category */
    private array $categoryCache = [];

    /** Counters surfaced in the result message. */
    private int $catCreated = 0;
    private int $imgSaved   = 0;

    public function importProcess(Request $request)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        @set_time_limit(300); // image downloads can be slow

        $handle = fopen($request->file('csv_file')->getRealPath(), 'r');

        // Header row (strip the UTF-8 BOM Excel keeps on the first column)
        $headers = array_map(fn ($h) => $this->clean((string) $h), (array) fgetcsv($handle));
        $required = ['name', 'purchase_price', 'quantity', 'status'];
        foreach ($required as $col) {
            if (!in_array($col, $headers, true)) {
                fclose($handle);
                return back()->withErrors(['csv_file' => "CSV header missing required column: \"$col\""]);
            }
        }

        $this->categoryCache = [];
        $this->catCreated    = 0;
        $this->imgSaved      = 0;

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors  = [];
        $row     = 1; // header is row 1

        while (($data = fgetcsv($handle)) !== false) {
            $row++;

            // Ignore completely blank lines
            if (count($data) === 1 && trim((string) $data[0]) === '') {
                continue;
            }
            // Pad / truncate so the row always lines up with the header
            $data   = array_map(fn ($v) => $this->clean((string) $v), $data);
            $data   = array_slice(array_pad($data, count($headers), ''), 0, count($headers));
            $record = array_combine($headers, $data);

            $name = $record['name'] ?? '';
            if ($name === '') {
                $errors[] = "Row $row: empty name, skipped.";
                $skipped++;
                continue;
            }

            try {
                // ── 1. Categories: find or create, never duplicate ──────────
                $parentName = $record['parent_category'] ?? '';
                $subName    = $record['sub_category']    ?? '';

                $parentCat = $parentName !== ''
                    ? $this->resolveCategory($parentName, null, $record['parent_category_description'] ?? '', $record['parent_category_image'] ?? '', $row, $errors)
                    : null;

                // A sub-category always sits under its parent; with no parent given it
                // becomes a top-level category of its own.
                $subCat = $subName !== ''
                    ? $this->resolveCategory($subName, $parentCat?->id, $record['sub_category_description'] ?? '', $record['sub_category_image'] ?? '', $row, $errors)
                    : null;

                // ── 2. Sub-category wins; otherwise the parent ──────────────
                $categoryId = $subCat?->id ?? $parentCat?->id;

                // ── 3. Product: update when it already exists, never duplicate ──
                $sku = $record['sku'] ?? '';

                // Match by SKU first, then by name (case-insensitive)
                $existing = $sku !== '' ? Product::where('sku', $sku)->first() : null;
                if (!$existing) {
                    $existing = Product::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();
                }
                $existingStatus = $existing?->status;

                $productData = [
                    'category_id'       => $categoryId,
                    'name'              => $name,
                    'purchase_price'    => is_numeric($record['purchase_price'] ?? '') ? $record['purchase_price'] : 0,
                    'sale_price'        => is_numeric($record['sale_price'] ?? '') && $record['sale_price'] !== '' ? $record['sale_price'] : null,
                    'quantity'          => is_numeric($record['quantity'] ?? '') ? (int) $record['quantity'] : 0,
                    'short_description' => ($record['short_description'] ?? '') !== '' ? $record['short_description'] : null,
                    'description'       => ($record['description'] ?? '') !== '' ? $record['description'] : null,
                    'status'            => $this->normalizeStatus($record['status'] ?? '', $existingStatus, $row, $errors),
                ];
                if ($sku !== '') {
                    $productData['sku'] = $sku;
                }

                if ($existing) {
                    // Keep the existing slug; updating it would break old links
                    $existing->update($productData);
                    $product = $existing;
                    $updated++;
                } else {
                    $productData['slug'] = $this->uniqueSlug(Product::class, $name);
                    $product = Product::create($productData);
                    $created++;
                }

                // ── 4. Product images (only filled in when missing) ─────────
                $this->fillProductImages($product, $record, $row, $errors);
            } catch (\Throwable $e) {
                $errors[] = "Row $row: " . $e->getMessage();
                $skipped++;
            }
        }

        fclose($handle);

        $message = "Import complete: {$created} products created, {$updated} updated, {$skipped} skipped"
            . ($this->catCreated ? ", {$this->catCreated} categories created" : '')
            . ($this->imgSaved ? ", {$this->imgSaved} images saved" : '') . '.';

        return back()->with('success', $message)->with('import_errors', $errors);
    }

    /* ───────────────────────────────────────────
     |  Helpers
     * ─────────────────────────────────────────── */

    /**
     * Find a category by name within the given parent, or create it.
     * The lookup is case-insensitive and cached for the whole import run, so the
     * same category is never created twice — not across rows, not across runs.
     */
    private function resolveCategory(string $name, ?int $parentId, string $description, string $imageUrl, int $row, array &$errors): Category
    {
        $name = trim($name);
        $key  = ($parentId ?? 0) . '|' . mb_strtolower($name);

        if (isset($this->categoryCache[$key])) {
            $category = $this->categoryCache[$key];
        } else {
            $category = Category::where('parent_id', $parentId)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                ->first();

            if (!$category) {
                $category = Category::create([
                    'name'      => $name,
                    'slug'      => $this->uniqueSlug(Category::class, $name),
                    'parent_id' => $parentId,
                    'status'    => 'active',
                ]);
                $this->catCreated++;
            }

            $this->categoryCache[$key] = $category;
        }

        // Fill in description / image when the category doesn't have them yet
        $dirty = false;

        if ($description !== '' && blank($category->description)) {
            $category->description = $description;
            $dirty = true;
        }

        if ($imageUrl !== '' && !$category->hasImage()) {
            $path = $this->resolveImage($imageUrl, 'categories', $row, $errors);
            if ($path) {
                $category->image = $path;
                $dirty = true;
            }
        }

        if ($dirty) {
            $category->save();
        }

        return $category;
    }

    /** Feature / gallery images from the CSV, only when the product has none. */
    private function fillProductImages(Product $product, array $record, int $row, array &$errors): void
    {
        $feature = trim((string) ($record['feature_image'] ?? ''));
        if ($feature !== '' && blank($product->feature_image)) {
            $path = $this->resolveImage($feature, 'products/feature', $row, $errors);
            if ($path) {
                $product->forceFill(['feature_image' => $path])->save();
            }
        }

        $gallery = trim((string) ($record['gallery_images'] ?? ''));
        if ($gallery !== '' && $product->images()->doesntExist()) {
            foreach (preg_split('/\s*[|,]\s*/', $gallery, -1, PREG_SPLIT_NO_EMPTY) as $i => $url) {
                $path = $this->resolveImage($url, 'products/gallery', $row, $errors);
                if ($path) {
                    $product->images()->create(['image_path' => $path, 'sort_order' => $i]);
                }
            }
        }
    }

    /**
     * Turn an image reference from the CSV into a path on the "public" disk.
     *
     * 1. A file this site already stores (a re-imported export) is reused as is.
     * 2. Anything else that is a real http(s) URL is downloaded into $folder.
     *
     * Returns null — and records a note — when the image can't be used.
     */
    private function resolveImage(string $url, string $folder, int $row, array &$errors): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        // 1. A plain path, or a link back to this site (a re-imported export):
        //    reuse the file we already store. Never fetch our own URL over HTTP —
        //    the dev server handles one request at a time and would hang.
        if (!Str::startsWith($url, ['http://', 'https://']) || $this->isOwnHost($url)) {
            if ($path = $this->storedImagePath($url)) {
                return $path;
            }
            $errors[] = "Row $row: image \"$url\" was not found in storage, skipped.";
            return null;
        }

        // 2. Download
        try {
            $response = Http::timeout(20)->connectTimeout(10)->withHeaders(['Accept' => 'image/*'])->get($url);

            if (!$response->successful()) {
                $errors[] = "Row $row: could not download image ({$response->status()}) — $url";
                return null;
            }

            $type = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));
            $ext  = self::IMAGE_TYPES[$type] ?? null;

            if (!$ext) {
                // Fall back to the extension in the URL when the server sends a vague type
                $urlExt = strtolower(pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
                $urlExt = $urlExt === 'jpeg' ? 'jpg' : $urlExt;
                $ext    = in_array($urlExt, self::IMAGE_TYPES, true) ? $urlExt : null;
            }
            if (!$ext) {
                $errors[] = "Row $row: \"$url\" is not an image, skipped.";
                return null;
            }

            $body = $response->body();
            if (strlen($body) === 0 || strlen($body) > self::MAX_IMAGE_BYTES) {
                $errors[] = "Row $row: image is empty or larger than 5 MB, skipped — $url";
                return null;
            }

            $path = trim($folder, '/') . '/' . Str::random(40) . '.' . $ext;
            Storage::disk('public')->put($path, $body);
            $this->imgSaved++;

            return $path;
        } catch (\Throwable $e) {
            $errors[] = "Row $row: image download failed — " . $e->getMessage();
            return null;
        }
    }

    /** Turn ".../storage/categories/x.jpg" back into "categories/x.jpg" when that file exists. */
    private function storedImagePath(string $url): ?string
    {
        $path = (string) parse_url(trim($url), PHP_URL_PATH);
        $path = ltrim(rawurldecode($path), '/');
        if ($path === '') {
            return null;
        }
        if (($pos = strpos($path, 'storage/')) !== false) {
            $path = substr($path, $pos + strlen('storage/'));
        }
        return Storage::disk('public')->exists($path) ? $path : null;
    }

    /** True when the URL points back at this same application. */
    private function isOwnHost(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if ($host === '') {
            return false;
        }
        $own = array_filter([
            strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST)),
            strtolower((string) request()->getHost()),
        ]);
        return in_array($host, $own, true) || in_array($host, ['127.0.0.1', 'localhost', '::1'], true);
    }

    /**
     * Map the CSV status onto one the database accepts.
     * "Active", "ACTIVE", "enabled", "published", "1" … all mean active; the same
     * goes for the inactive and draft spellings. A blank cell keeps the product's
     * current status (new products default to draft).
     */
    private function normalizeStatus(string $value, ?string $current, int $row, array &$errors): string
    {
        $map = [
            'active' => 'active', 'enable' => 'active', 'enabled' => 'active',
            'publish' => 'active', 'published' => 'active', 'live' => 'active',
            'visible' => 'active', 'yes' => 'active', 'true' => 'active', '1' => 'active',

            'inactive' => 'inactive', 'disable' => 'inactive', 'disabled' => 'inactive',
            'unpublish' => 'inactive', 'unpublished' => 'inactive', 'hidden' => 'inactive',
            'archived' => 'inactive', 'no' => 'inactive', 'false' => 'inactive', '0' => 'inactive',

            'draft' => 'draft', 'drafts' => 'draft', 'pending' => 'draft', 'unfinished' => 'draft',
        ];

        // Drop every kind of space (including the non-breaking and zero-width ones
        // that come with copy-pasted text) so " Active " and "in active" still match.
        $key = mb_strtolower((string) preg_replace('/[\s\x{00A0}\x{200B}-\x{200D}\x{2060}\x{FEFF}]+/u', '', $value));

        if (isset($map[$key])) {
            return $map[$key];
        }

        if ($key !== '') {
            $errors[] = "Row $row: unknown status \"$value\", saved as " . ($current ?: 'draft') . '.';
        } elseif ($current === null) {
            // New product with a blank status cell — say so, it is the usual cause
            // of "why is my product a draft?" (an unsaved edit in Excel).
            $errors[] = "Row $row: the status column was empty, so the product was saved as a draft.";
        }

        return $current ?: 'draft';
    }

    /**
     * Trim a CSV cell. Besides normal whitespace this strips the BOM and the
     * invisible characters spreadsheets pick up from copy-pasted text
     * (non-breaking space, zero-width space, LTR/RTL marks) — they are what make
     * a value like "active" silently not match. Inner line breaks are kept, so
     * descriptions survive unchanged.
     */
    private function clean(string $value): string
    {
        $invisible = '[\s\x{00A0}\x{200B}-\x{200F}\x{2028}\x{2029}\x{2060}\x{FEFF}]+';
        return (string) preg_replace(['/^' . $invisible . '/u', '/' . $invisible . '$/u'], '', $value);
    }

    /** A slug for $model that no row is using yet. */
    private function uniqueSlug(string $model, string $name): string
    {
        $base = Str::slug($name) ?: Str::random(8);
        $slug = $base;
        $i    = 1;
        while ($model::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
