<?php

/*
 * Seller / ecommerce tools shown at /seller-tools.
 * cat groups: pricing | shipping | catalog | invoice | fees | marketing | inventory
 */
return [
    // Pricing
    'profit-margin'        => ['name' => 'Profit Margin Calculator', 'icon' => 'fa-scale-balanced',     'color' => '#1f7244', 'cat' => 'pricing',   'live' => true, 'desc' => 'Profit, margin % and markup % from your cost and selling price.'],
    'markup-calculator'    => ['name' => 'Markup Calculator',        'icon' => 'fa-arrow-trend-up',     'color' => '#1f7244', 'cat' => 'pricing',   'live' => true, 'desc' => 'Find the selling price from a cost and a target markup percentage.'],
    'break-even-calculator'=> ['name' => 'Break-Even Calculator',    'icon' => 'fa-chart-line',         'color' => '#1f7244', 'cat' => 'pricing',   'live' => true, 'desc' => 'How many units you must sell to cover fixed and variable costs.'],
    'free-shipping-threshold'=>['name' => 'Free-Shipping Threshold', 'icon' => 'fa-truck',              'color' => '#1f7244', 'cat' => 'pricing',   'live' => true, 'desc' => 'Find the order value at which offering free shipping stays profitable.'],
    'landed-cost'          => ['name' => 'Landed Cost Calculator',   'icon' => 'fa-ship',               'color' => '#1f7244', 'cat' => 'pricing',   'live' => true, 'desc' => 'True per-unit cost including shipping, duty and fees — great for dropshipping.'],
    'tiered-pricing'       => ['name' => 'Bulk / Tiered Pricing',    'icon' => 'fa-layer-group',        'color' => '#1f7244', 'cat' => 'pricing',   'live' => true, 'desc' => 'Build quantity price breaks and see the price and savings per tier.'],
    'price-per-unit'       => ['name' => 'Price Per Unit Compare',   'icon' => 'fa-equals',             'color' => '#1f7244', 'cat' => 'pricing',   'live' => true, 'desc' => 'Compare two products by price per unit to see which is the better deal.'],

    // Marketplace fees / net profit
    'payment-fee-calculator'=>['name' => 'Payment Fee Calculator',   'icon' => 'fa-credit-card',        'color' => '#0ea5e9', 'cat' => 'fees',      'live' => true, 'desc' => 'PayPal / Stripe processing fees and what you actually receive.'],
    'fba-calculator'       => ['name' => 'Amazon FBA Profit',        'icon' => 'fa-box',                'color' => '#0ea5e9', 'cat' => 'fees',      'live' => true, 'desc' => 'Estimate FBA profit after referral and fulfillment fees.'],
    'ebay-etsy-fees'       => ['name' => 'eBay / Etsy Fees',         'icon' => 'fa-store',              'color' => '#0ea5e9', 'cat' => 'fees',      'live' => true, 'desc' => 'Estimate marketplace fees and net payout for eBay and Etsy sales.'],
    'net-profit-calculator'=> ['name' => 'Net Profit Calculator',    'icon' => 'fa-sack-dollar',        'color' => '#0ea5e9', 'cat' => 'fees',      'live' => true, 'desc' => 'Sale price minus cost, fees and shipping — your real take-home per order.'],

    // Marketing / ads ROI
    'roas-calculator'      => ['name' => 'ROAS Calculator',          'icon' => 'fa-bullseye',           'color' => '#6d28d9', 'cat' => 'marketing', 'live' => true, 'desc' => 'Return on ad spend and ACOS from revenue and ad cost.'],
    'cac-calculator'       => ['name' => 'CAC / CPA Calculator',     'icon' => 'fa-user-plus',          'color' => '#6d28d9', 'cat' => 'marketing', 'live' => true, 'desc' => 'Customer acquisition cost from your marketing spend and new customers.'],
    'clv-calculator'       => ['name' => 'Customer Lifetime Value',  'icon' => 'fa-gem',                'color' => '#6d28d9', 'cat' => 'marketing', 'live' => true, 'desc' => 'Estimate CLV from order value, frequency, lifespan and margin.'],
    'aov-calculator'       => ['name' => 'Average Order Value',      'icon' => 'fa-cart-shopping',      'color' => '#6d28d9', 'cat' => 'marketing', 'live' => true, 'desc' => 'Average order value from total revenue and number of orders.'],
    'conversion-rate'      => ['name' => 'Conversion Rate',          'icon' => 'fa-percent',            'color' => '#6d28d9', 'cat' => 'marketing', 'live' => true, 'desc' => 'Conversion rate from visitors and orders, plus revenue per visitor.'],
    'break-even-roas'      => ['name' => 'Break-Even ROAS',          'icon' => 'fa-scale-unbalanced',   'color' => '#6d28d9', 'cat' => 'marketing', 'live' => true, 'desc' => 'The minimum ROAS your ads need to hit before you lose money.'],

    // Inventory / logistics
    'reorder-point'        => ['name' => 'Reorder Point',            'icon' => 'fa-rotate',             'color' => '#f2b705', 'cat' => 'inventory', 'live' => true, 'desc' => 'When to reorder stock from daily sales, lead time and safety stock.'],
    'eoq-calculator'       => ['name' => 'Economic Order Quantity',  'icon' => 'fa-boxes-stacked',      'color' => '#f2b705', 'cat' => 'inventory', 'live' => true, 'desc' => 'The most cost-efficient order size (EOQ) for your inventory.'],
    'inventory-turnover'   => ['name' => 'Inventory Turnover',       'icon' => 'fa-arrows-rotate',      'color' => '#f2b705', 'cat' => 'inventory', 'live' => true, 'desc' => 'Inventory turnover ratio and days of inventory on hand.'],
    'dim-weight'           => ['name' => 'Dimensional Weight',       'icon' => 'fa-ruler-combined',     'color' => '#f2b705', 'cat' => 'inventory', 'live' => true, 'desc' => 'Volumetric (DIM) weight couriers use to decide your shipping charge.'],

    // Catalog / invoice
    'shipping-estimator'   => ['name' => 'Shipping Estimator',       'icon' => 'fa-truck-fast',         'color' => '#8b5cf6', 'cat' => 'shipping',  'live' => true, 'desc' => 'Estimate shipping cost from weight, rate and handling fees.'],
    'sku-generator'        => ['name' => 'SKU Generator',            'icon' => 'fa-barcode',            'color' => '#8b5cf6', 'cat' => 'catalog',   'live' => true, 'desc' => 'Generate clean, consistent SKU codes from product attributes.'],
    'invoice-generator'    => ['name' => 'Invoice Generator',        'icon' => 'fa-file-invoice-dollar','color' => '#e5322d', 'cat' => 'invoice',   'live' => true, 'desc' => 'Create a simple invoice and download it as a PDF — no sign-up.'],
];
