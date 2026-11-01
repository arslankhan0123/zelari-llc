<?php

/**
 * RBAC permission matrix. Every module has the same 5 actions:
 *   list   → see the listing / index (data view)
 *   view   → open a single record's details
 *   create → access the create form + store
 *   edit   → access the edit form + update (incl. approve/moderate/manage actions)
 *   delete → delete a record
 * Permission string = "module.action" (e.g. "products.create").
 * Master Admin implicitly has every permission (see Gate::before).
 */
$actions = ['list', 'view', 'create', 'edit', 'delete'];

$modules = [
    'dashboard'     => 'Dashboard',
    'products'      => 'Products',
    'categories'    => 'Categories',
    'orders'        => 'Orders',
    'customers'     => 'Customers',
    'manufacturers' => 'Manufacturers',
    'reviews'       => 'Reviews',
    'coupons'       => 'Coupons',
    'marketing'     => 'Marketing',
    'seo'           => 'SEO',
    'sourcing'      => 'Sourcing',
    'messages'      => 'Messages',
    'chats'         => 'Live Chat',
    'vendors'       => 'Vendors',
    'approvals'     => 'Product Approvals',
    'commissions'   => 'Commission Rules',
    'earnings'      => 'Earnings',
    'payouts'       => 'Payouts',
    'shipping'      => 'Shipping',
    'tax'           => 'Tax',
    'suppliers'     => 'Suppliers',
    'inventory'     => 'Inventory',
    'tickets'       => 'Support Tickets',
    'returns'       => 'Returns & Refunds',
    'reports'       => 'Reports',
    'staff'         => 'Vendor Staff',
    'payments'      => 'Payments',
    'content'       => 'Website Content',
    'tools'         => 'Tools',
    'settings'      => 'Settings',
    'users'         => 'Users & Roles',
];

return collect($modules)->map(fn ($label) => ['label' => $label, 'actions' => $actions])->all();
