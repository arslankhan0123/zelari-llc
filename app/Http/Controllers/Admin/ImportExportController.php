<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use League\Csv\Reader;
use League\Csv\Writer;

class ImportExportController extends Controller
{
    /* ───────────────────────────────────────────
     |  EXPORT  –  products as CSV
     |  Columns: name, sku, short_description,
     |  purchase_price, sale_price, quantity,
     |  status, parent_category, sub_category
     * ─────────────────────────────────────────── */
    public function export()
    {
        $products = Product::with('category.parent')->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="products_export_' . now()->format('Y-m-d') . '.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = [
            'name', 'sku', 'short_description',
            'purchase_price', 'sale_price', 'quantity', 'status',
            'parent_category', 'sub_category',
        ];

        $callback = function () use ($products, $columns) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $columns);

            foreach ($products as $p) {
                $cat    = $p->category;
                $parent = $cat?->parent;

                fputcsv($handle, [
                    $p->name,
                    $p->sku ?? '',
                    $p->short_description ?? '',
                    $p->purchase_price,
                    $p->sale_price ?? '',
                    $p->quantity,
                    $p->status,
                    // If category has a parent → parent_category = grandparent name, sub_category = category name
                    // If category is top-level → parent_category = category name, sub_category = ''
                    $parent ? $parent->name : ($cat?->name ?? ''),
                    $parent ? $cat->name    : '',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /* ───────────────────────────────────────────
     |  SAMPLE TEMPLATE download
     * ─────────────────────────────────────────── */
    public function sampleTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="products_import_template.csv"',
        ];

        $rows = [
            ['name','sku','short_description','purchase_price','sale_price','quantity','status','parent_category','sub_category'],
            ['Sample T-Shirt','SKU-001','A comfortable cotton t-shirt','500','450','100','active','Clothing','Men'],
            ['Blue Jeans','SKU-002','Slim fit denim jeans','1200','999','50','active','Clothing','Men'],
            ['Red Dress','SKU-003','Elegant evening dress','2000','1800','30','active','Clothing','Women'],
            ['Running Shoes','SKU-004','Lightweight sports shoes','3000','','25','active','Footwear',''],
            ['Wireless Earbuds','SKU-005','Bluetooth 5.0 earbuds','4500','3999','60','draft','Electronics','Audio'],
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
     * ─────────────────────────────────────────── */
    public function importProcess(Request $request)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $handle = fopen($path, 'r');

        // Read header row
        $headers = array_map('trim', fgetcsv($handle));
        $required = ['name', 'purchase_price', 'quantity', 'status'];
        foreach ($required as $col) {
            if (!in_array($col, $headers)) {
                return back()->withErrors(['csv_file' => "CSV header missing required column: \"$col\""]);
            }
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors  = [];
        $row     = 1; // header is row 0

        while (($data = fgetcsv($handle)) !== false) {
            $row++;
            if (count($data) < count($headers)) {
                $errors[] = "Row $row: not enough columns, skipped.";
                $skipped++;
                continue;
            }

            $record = array_combine($headers, array_map('trim', $data));

            $name = $record['name'] ?? '';
            if (empty($name)) {
                $errors[] = "Row $row: empty name, skipped.";
                $skipped++;
                continue;
            }

            // ── Resolve / create category ──────────────────
            $categoryId = null;
            $parentName = $record['parent_category'] ?? '';
            $subName    = $record['sub_category']    ?? '';

            if (!empty($parentName)) {
                // Find or create parent category
                $parentCat = Category::firstOrCreate(
                    ['name' => $parentName, 'parent_id' => null],
                    ['slug' => Str::slug($parentName), 'status' => 'active']
                );

                if (!empty($subName)) {
                    // Find or create sub-category under that parent
                    $subCat = Category::firstOrCreate(
                        ['name' => $subName, 'parent_id' => $parentCat->id],
                        ['slug' => Str::slug($subName) . '-' . $parentCat->id, 'status' => 'active']
                    );
                    $categoryId = $subCat->id;
                } else {
                    $categoryId = $parentCat->id;
                }
            }

            // ── Upsert product by SKU (if given) or name ──
            $sku = $record['sku'] ?? '';

            $productData = [
                'category_id'       => $categoryId,
                'name'              => $name,
                'purchase_price'    => is_numeric($record['purchase_price'] ?? '') ? $record['purchase_price'] : 0,
                'sale_price'        => is_numeric($record['sale_price'] ?? '') && $record['sale_price'] !== '' ? $record['sale_price'] : null,
                'quantity'          => is_numeric($record['quantity'] ?? '') ? (int)$record['quantity'] : 0,
                'short_description' => $record['short_description'] ?? null,
                'status'            => in_array($record['status'] ?? '', ['active','inactive','draft']) ? $record['status'] : 'draft',
            ];

            // Slug
            $productData['slug'] = Str::slug($name);
            if (!empty($sku)) {
                $productData['sku'] = $sku;
            }

            // Try to find existing product
            $existing = null;
            if (!empty($sku)) {
                $existing = Product::where('sku', $sku)->first();
            }
            if (!$existing) {
                $existing = Product::where('name', $name)->first();
            }

            if ($existing) {
                $existing->update($productData);
                $updated++;
            } else {
                // Ensure unique slug
                $baseSlug = $productData['slug'];
                $slugCount = 1;
                while (Product::where('slug', $productData['slug'])->exists()) {
                    $productData['slug'] = $baseSlug . '-' . $slugCount++;
                }
                Product::create($productData);
                $created++;
            }
        }

        fclose($handle);

        $message = "Import complete: {$created} created, {$updated} updated, {$skipped} skipped.";
        return back()->with('success', $message)->with('import_errors', $errors);
    }
}
