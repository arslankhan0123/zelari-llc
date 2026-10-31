<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('build_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('phase');
            $table->string('phase_title');
            $table->string('title');
            $table->string('status', 20)->default('todo'); // todo | in_progress | done
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        $now = now();
        $plan = [
            [1, 'Phase 1 — Vendor Foundation', [
                ['vendors table, vendor_id on products, vendor roles', 'done'],
                ['Vendor model + User/Product relations', 'done'],
                ['Admin Vendors module (list / approve / suspend / review)', 'done'],
                ['Public vendor registration form', 'done'],
                ['Vendor Panel shell (login area + dashboard)', 'done'],
            ]],
            [2, 'Phase 2 — Vendor Catalog & Orders', [
                ['Vendor product management (CRUD scoped to vendor)', 'todo'],
                ['Product approval workflow (draft → pending → published/rejected)', 'todo'],
                ['Sub-orders schema (parent order + per-vendor sub-orders)', 'todo'],
                ['Order-splitting logic at checkout', 'todo'],
                ['Vendor order management (status, tracking)', 'todo'],
                ['Admin order view: vendor filter + split visibility', 'todo'],
            ]],
            [3, 'Phase 3 — Commissions, Earnings & Payouts', [
                ['Commission rules (global / per-vendor / per-category)', 'todo'],
                ['Earnings ledger per vendor', 'todo'],
                ['Payout requests → admin approval → paid', 'todo'],
                ['Vendor statements (PDF/CSV)', 'todo'],
            ]],
            [4, 'Phase 4 — Storefronts & Multi-Vendor UX', [
                ['Public vendor storefront pages (/vendor/{slug})', 'todo'],
                ['"Sold by {vendor}" on product pages', 'todo'],
                ['Vendor filter in product discovery', 'todo'],
                ['Multi-vendor cart & checkout (per-vendor shipping)', 'todo'],
            ]],
            [5, 'Phase 5 — Shipping & Tax Engines', [
                ['Shipping zones / methods / rates', 'todo'],
                ['Vendor-specific shipping + mixed-cart rules', 'todo'],
                ['Tax engine (VAT/GST, inclusive/exclusive, exemptions)', 'todo'],
            ]],
            [6, 'Phase 6 — Catalog Depth', [
                ['Attributes + variations (variable products)', 'todo'],
                ['Product types: digital / downloadable / bundled', 'todo'],
                ['Bulk import / export (CSV/Excel) for vendors', 'todo'],
            ]],
            [7, 'Phase 7 — Operations', [
                ['Inventory & warehouse (reservations, suppliers)', 'todo'],
                ['Support ticketing (SLAs, canned replies)', 'todo'],
                ['Returns / exchanges / disputes / refunds flow', 'todo'],
                ['Reports & BI exports + customer segmentation', 'todo'],
            ]],
            [8, 'Phase 8 — Automation, Growth & Security', [
                ['Reusable automation rule engine (event→condition→action)', 'todo'],
                ['Marketing: segments, workflows, SMS/push', 'todo'],
                ['Loyalty / referral / affiliate', 'todo'],
                ['SEO automation (metadata templates, redirects, blog)', 'todo'],
                ['Security: 2FA, granular permissions, audit logs', 'todo'],
                ['Vendor subscription plans + listing limits', 'todo'],
            ]],
        ];
        $rows = []; $sort = 0;
        foreach ($plan as [$phase, $ptitle, $tasks]) {
            foreach ($tasks as [$title, $status]) {
                $rows[] = [
                    'phase' => $phase, 'phase_title' => $ptitle, 'title' => $title,
                    'status' => $status, 'sort' => $sort++,
                    'created_at' => $now, 'updated_at' => $now,
                ];
            }
        }
        DB::table('build_tasks')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('build_tasks');
    }
};
