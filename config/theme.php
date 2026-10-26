<?php

/*
|--------------------------------------------------------------------------
| Brand theme — THE one place to change colours
|--------------------------------------------------------------------------
| Every button, link, badge and gradient on the storefront, the admin panel,
| the PDF invoice and the order emails reads from here. Change a value, run
| `php artisan optimize:clear`, and it updates everywhere.
*/

return [

    // Primary brand colour (buttons, links, active states) — Zelari deep navy/indigo (logo tile)
    'brand'        => '#1e1b4b',
    'brand_dark'   => '#0f1230',   // hover / pressed, strong headings, table heads
    'brand_light'  => '#3b3680',   // gradient end, decorative orbs
    'brand_soft'   => '#ecebf6',   // tinted backgrounds behind brand text (selected rows, chips)

    // Secondary accent — gold (small highlights, ticker dots, focus rings)
    'accent'       => '#fbbf24',
    'accent_soft'  => '#fef3c7',

    // Gradient used on primary buttons, icon tiles and hero banners
    'grad'         => 'linear-gradient(135deg,#0f1230 0%,#1e1b4b 55%,#3b3680 100%)',
    'grad_hover'   => 'linear-gradient(135deg,#1e1b4b 0%,#2a2e52 55%,#4c46a0 100%)',

    // Status colours
    'sale'         => '#f97316',   // discounts, "Shop Now" hero button, cart badge — orange CTA
    'success'      => '#14b88a',
    'warning'      => '#d97706',
    'danger'       => '#dc2626',
    'star'         => '#fbbf24',

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
