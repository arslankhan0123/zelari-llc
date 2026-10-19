<?php

/*
|--------------------------------------------------------------------------
| Brand theme — THE one place to change colours
|--------------------------------------------------------------------------
| Every button, link, badge and gradient on the storefront, the admin panel,
| the PDF invoice and the order emails reads from here. Change a value, run
| `php artisan optimize:clear`, and it updates everywhere.
|
| Storefront & admin: these become CSS variables (--brand, --brand-grad, …)
| printed by resources/views/partials/theme-vars.blade.php and used by the
| shared classes in public/css/theme.css (.btn-brand, .bg-brand, .text-brand …).
| PDF & email: read directly with ztheme('brand') because they can't use CSS vars.
*/

return [

    // Primary brand colour (buttons, links, active states) — the dark navy of the logo tile
    'brand'        => '#1e1b4b',
    'brand_dark'   => '#0f1230',   // hover / pressed, strong headings, table heads
    'brand_light'  => '#3b3680',   // gradient end, decorative orbs
    'brand_soft'   => '#ecebf6',   // tinted backgrounds behind brand text (selected rows, chips)

    // Secondary accent — the cyan lightning in the logo (small highlights, ticker dots, focus rings)
    'accent'       => '#22d3ee',
    'accent_soft'  => '#e4fbff',

    // Gradient used on primary buttons, icon tiles and hero banners
    'grad'         => 'linear-gradient(135deg,#0f1230 0%,#1e1b4b 55%,#3b3680 100%)',
    'grad_hover'   => 'linear-gradient(135deg,#1e1b4b 0%,#2a2e52 55%,#4c2fd9 100%)',

    // Status colours
    'sale'         => '#ff4d7d',   // discounts, "Shop Now" hero button, cart badge
    'success'      => '#14b88a',
    'warning'      => '#f6a609',
    'danger'       => '#dc2626',
    'star'         => '#ffb400',

    // Text & surfaces (storefront)
    'ink'          => '#0f1230',
    'ink_2'        => '#2a2e52',
    'muted'        => '#6b7191',
    'faint'        => '#9aa0bd',
    'surface'      => '#f6f7fc',
    'line'         => '#e7e9f3',
    'line_2'       => '#d9dcec',

    // Shadows / glow derived from the brand (rgb triplet of brand_dark)
    'shadow_rgb'   => '15,18,48',
];
