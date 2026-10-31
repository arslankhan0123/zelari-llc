# Zelari → Multi-Vendor Marketplace — Gap Analysis & Phased Roadmap

_Companion to `multi_vendor_ecommerce_specification.md`. Maps the current single‑vendor Zelari codebase against the marketplace spec and lays out a dependency‑ordered build plan._

---

## 1. Executive Summary

Zelari today is a **feature-rich single-vendor store** built on Laravel. It already ships a lot of what the spec calls for — catalog, orders, payments, customers, reviews, coupons, SEO auditing, marketing/newsletter, live chat, RBAC, settings, and a full admin panel — but **all of it assumes one seller (the store owner)**.

The spec describes a **multi-vendor marketplace**: many independent vendors, each with their own storefront, products, orders, earnings, and payouts, plus the platform-level machinery to manage them (commissions, approvals, order-splitting, subscriptions).

**The core transformation is the "vendor" dimension.** Roughly:
- ~40% of the spec **already exists** (needs to be made *vendor-aware*, not rebuilt).
- ~25% is **partially present** (exists single-vendor; needs extension).
- ~35% is **net-new** (vendors, vendor panel, commissions, payouts, shipping/tax engines, inventory/warehouse, support tickets, automation rule engine).

**Recommended approach:** build the vendor foundation first (everything depends on it), then make existing modules vendor-aware, then add the net-new marketplace modules. Do **not** rebuild working modules — extend them.

---

## 2. Current State Snapshot

| Area | Status in Zelari today |
|---|---|
| **Catalog** | Products, Categories, Manufacturers (brands), ProductImages, Reviews. Simple products (no variants). |
| **Orders** | Order + OrderItem, statuses, payment fields, currency/location, invoices/emails. Single fulfiller. |
| **Payments** | Stripe + PayPal + COD wired in Checkout; Payment model; Stripe webhook. |
| **Customers** | Customer model, accounts, addresses, wishlist, order history. |
| **Access/RBAC** | Role model (`super_admin`, `admin`, `user`) + role-on-user. No vendor role yet. |
| **Marketing** | Newsletter/Subscribers, product feeds (Google/Meta), coupons + spin wheel, content plan. |
| **SEO** | Automated SEO audit + report, dynamic sitemap, JSON-LD structured data. |
| **Support** | Live chat, contact messages, sourcing requests. No ticketing. |
| **Admin panel** | Full two-tier panel (rail + modules), settings, users & roles, dashboards. |
| **Settings** | Branding, SMTP, store details, payment methods, multi-currency. |
| **Vendors / Commissions / Payouts / Shipping zones / Tax engine / Warehouse / Tickets** | **None — do not exist.** |

---

## 3. Gap Analysis (by spec section)

Legend: ✅ Have · 🟡 Partial (single-vendor, needs extension) · ❌ Missing

### 1. Admin Panel
| Spec area | Status | Notes |
|---|---|---|
| 1.1 Dashboard & Analytics | 🟡 | Rich dashboard exists; missing vendor/commission/payout metrics, cohort/CLV, abandonment. |
| 1.2 Vendor Management | ❌ | No vendor concept at all — the single biggest gap. |
| 1.3 Product & Catalog | 🟡 | Strong, but no vendor ownership, approval workflow, variants, digital/bundled types, bulk import for vendors. |
| 1.4 Category/Brand/Attribute | 🟡 | Categories + brands exist; no attribute/variation system, no category-level SEO templates. |
| 1.5 Order Management | 🟡 | Orders exist; no **vendor order-splitting**, custom statuses, returns/exchange/dispute flow. |
| 1.6 Commission, Payments & Payouts | ❌ | Entire commission + payout ledger is new. |
| 1.7 Customer Management | 🟡 | Profiles/orders exist; no segments, CLV, consent timeline. |
| 1.8 Shipping & Fulfillment | ❌ | Orders capture shipping address only; no zones/methods/rates/carriers. |
| 1.9 Payment & Tax Config | 🟡 | Gateways + multi-currency exist; no tax zones/VAT/GST engine. |
| 1.10 Promotions & Discounts | 🟡 | Coupons + wheel exist; no flash sales, BOGO, bundles, scheduling. |
| 1.11 Reviews & Moderation | 🟡 | Reviews + approval exist; no vendor-level moderation/reporting. |
| 1.12 CMS & Website Content | 🟡 | Content controller + hero/banners; no full page/menu/blog builder. |
| 1.13 Support & Communication | 🟡 | Live chat + contact; no ticket system, SLAs, canned replies. |

### 2. Vendor Panel — ❌ **Entirely new** (dashboard, store profile, product mgmt, order mgmt, earnings/payouts, vendor marketing, reports, staff).

### 3. Customer-Facing Website
| Spec area | Status | Notes |
|---|---|---|
| 3.1 Homepage | 🟡 | Exists; add vendor/marketplace blocks. |
| 3.2 Product Discovery | 🟡 | Search/filter exist; add attribute filters, vendor filter. |
| 3.3 Product Detail | 🟡 | Exists; add "sold by vendor", variants, Q&A. |
| 3.4 Vendor Storefronts | ❌ | New — per-vendor public store pages. |
| 3.5 Cart & Checkout | 🟡 | Works; needs **multi-vendor cart split**, per-vendor shipping. |
| 3.6 Customer Account | 🟡 | Exists; add returns, tickets, following vendors. |
| 3.7 Trust & Support | 🟡 | Partial. |

### 4. SEO Module — 🟡 Strong base (audit, sitemap, schema). Missing: dynamic metadata templates, redirects manager, blog automation, **rule engine**, vendor/brand SEO pages.

### 5. Marketing Module — 🟡 Good base (newsletter, feeds, coupons, content plan). Missing: segmentation, automation workflows (rule engine), SMS/push, loyalty/referral/affiliate, on-site (popups/abandoned-cart).

### 6. Inventory & Warehouse — ❌ New (warehouses, stock reservations, suppliers, transfers).

### 7. Reports & BI — 🟡 Dashboard-level only. Missing: exports, vendor/commission/tax reports, attribution.

### 8. Platform Settings & Security — 🟡 Settings + RBAC exist. Missing: 2FA, granular permissions, activity/audit logs, backups, maintenance mode, fraud/rate-limit controls.

### 9/10. Module structure & Automation engine — The spec's reusable **rule-based automation engine** (events → conditions → actions → audit) does not exist; SEO/marketing today are hardcoded workflows.

---

## 4. Phased Roadmap (dependency-ordered)

> Each phase is shippable on its own. Build top-to-bottom — later phases depend on earlier ones. "Vendor-aware" = add `vendor_id` + scoping, not a rebuild.

### Phase 1 — Vendor Foundation  _(unblocks everything)_
- `vendors` table (profile, logo, banner, slug, status, commission rate, policies) + `Vendor` model.
- Extend RBAC: add `vendor` + `vendor_staff` roles; link users→vendor.
- Vendor **registration** (public apply form) + admin **approval workflow** (pending/approved/suspended/rejected).
- Add `vendor_id` to `products` (+ backfill existing products to a default "house" vendor).
- Basic **Vendor Panel** shell (own login area, dashboard skeleton) — reuse the admin layout pattern.
- Admin → Vendors module (list, review, approve/suspend, notes).

### Phase 2 — Vendor Catalog & Orders
- Vendor product management (CRUD scoped to their vendor_id) + **product approval** workflow (draft→pending→published/rejected).
- **Order-splitting**: one customer order → per-vendor sub-orders/line grouping; vendor sees only their items.
- Vendor order management (status updates, packing slip, tracking) within their scope.
- Admin order view gains vendor filter + split visibility.

### Phase 3 — Commissions, Earnings & Payouts
- Commission rules (global / per-vendor / per-category; fixed/%/mixed).
- **Earnings ledger** per vendor (gross, commission, fees, refunds, net; pending/available/paid).
- Payout requests → admin approval → mark paid; thresholds + statements (PDF/CSV).
- Dashboard + reports gain commission/payout metrics.

### Phase 4 — Vendor Storefronts & Multi-Vendor Customer UX
- Public **vendor storefront** pages (`/vendor/{slug}`) with branding, product grid, ratings.
- "Sold by {vendor}" on product pages; vendor filter in discovery.
- **Multi-vendor cart & checkout** (group by vendor, per-vendor shipping, single payment → split).

### Phase 5 — Shipping & Tax Engines
- Shipping zones/methods/rates (flat, free, weight/price-based), vendor-specific methods, mixed-cart rules.
- Tax engine (country/region rules, VAT/GST, inclusive/exclusive, exemptions).

### Phase 6 — Catalog Depth
- Attributes + **variations** (variable products), attribute-based filters.
- Product types: digital/downloadable, bundled, configurable.
- Bulk import/export (CSV/Excel) for vendors; category-level attribute forms.

### Phase 7 — Operations Modules
- Inventory & warehouse (stock reservations, suppliers, transfers).
- Support ticketing (SLAs, canned replies) alongside existing live chat.
- Returns / exchanges / disputes / refunds workflow (customer + vendor + admin).
- Reports & BI exports; vendor/customer segmentation.

### Phase 8 — Automation, Growth & Security
- Reusable **automation rule engine** (event → condition → action → audit, with approval gates) — refactor SEO + marketing onto it.
- Marketing: segments, workflows, SMS/push, loyalty/referral/affiliate, abandoned-cart.
- SEO: dynamic metadata templates, redirects manager, blog automation, vendor/brand SEO.
- Security hardening: 2FA, granular permissions, activity/audit logs, maintenance mode, backups.
- Vendor subscription plans + listing limits.

---

## 5. Key Architectural Decisions & Risks

1. **Backfill strategy** — Introducing `vendor_id` on a live store: create a default "house vendor" and assign all existing products/orders to it, so nothing breaks on day one.
2. **Order model** — Decide early: *sub-orders* (one parent order + N vendor sub-orders) vs *line-level vendor tagging*. Sub-orders scale better for payouts/fulfillment; pick before Phase 2.
3. **Money & commissions** — Keep the existing base-currency (PKR) + rate model; commission/ledger math must be in base currency to stay consistent with current `zprice()`/`zusd()`.
4. **Permissions** — Extend the existing Role system rather than bolting on a new one; add granular per-vendor-staff abilities.
5. **Don't rebuild** — Products/Orders/Coupons/Reviews/SEO/Marketing already work; add scoping + approval, not rewrites.
6. **Automation last, not first** — Build features directly in early phases; refactor onto the rule engine (Phase 8) once patterns are clear. Keep approval gates for bulk/price/SEO/messaging actions.
7. **Risk** — Multi-vendor cart/checkout + split payouts is the hardest integration point; prototype Phase 4 checkout early even if polished later.

---

## 6. Quick Wins vs Heavy Lifts

- **Quick wins (days):** vendor table + model, vendor role, admin Vendors list/approve, `vendor_id` backfill, vendor dashboard shell.
- **Medium (1–2 weeks each):** order-splitting, commission ledger, vendor storefronts, shipping engine.
- **Heavy (multi-week):** variations system, tax engine, inventory/warehouse, automation rule engine, full returns/dispute flow.

---

## 7. Recommended Immediate Next Step

Start **Phase 1 (Vendor Foundation)** — it is the dependency root and includes the quick wins above. Concretely:
1. `vendors` migration + `Vendor` model + default house vendor.
2. `vendor` role + user→vendor link.
3. Vendor registration form + admin approval module.
4. `vendor_id` on products (backfilled).
5. Vendor Panel shell (login + dashboard skeleton).

Once Phase 1 is in, every later phase has something to attach to.
