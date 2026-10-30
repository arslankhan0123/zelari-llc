<?php

/*
 * Image tools shown at /image-tools. Everything runs in the visitor's browser
 * (canvas) — no uploads, no server load. Each entry: name, icon (font-awesome),
 * color, cat (edit|convert|optimize|enhance|more), desc, live.
 * Admin can override per-tool state and hide the whole section (see App\Support\ImageTools).
 */
return [
    'compress-image'     => ['name' => 'Compress Image',     'icon' => 'fa-file-zipper',       'color' => '#16a34a', 'cat' => 'optimize', 'live' => true, 'desc' => 'Shrink JPG, PNG and WebP file size with an adjustable quality slider — see the savings instantly.'],
    'resize-image'       => ['name' => 'Resize Image',       'icon' => 'fa-up-right-and-down-left-from-center', 'color' => '#2563eb', 'cat' => 'edit', 'live' => true, 'desc' => 'Resize to exact pixels or by percentage, with the aspect ratio locked or free.'],
    'crop-image'         => ['name' => 'Crop Image',         'icon' => 'fa-crop-simple',       'color' => '#2563eb', 'cat' => 'edit',     'live' => true, 'desc' => 'Drag a box over your photo to keep exactly the area you want.'],
    'rotate-image'       => ['name' => 'Rotate Image',       'icon' => 'fa-rotate-right',      'color' => '#2563eb', 'cat' => 'edit',     'live' => true, 'desc' => 'Rotate your image 90, 180 or 270 degrees in one click.'],
    'flip-image'         => ['name' => 'Flip Image',         'icon' => 'fa-left-right',        'color' => '#2563eb', 'cat' => 'edit',     'live' => true, 'desc' => 'Mirror your image horizontally or vertically.'],
    'round-image'        => ['name' => 'Round Corners',      'icon' => 'fa-circle',            'color' => '#2563eb', 'cat' => 'edit',     'live' => true, 'desc' => 'Rounded corners or a perfect circle crop with a transparent background (PNG).'],

    'convert-to-jpg'     => ['name' => 'Convert to JPG',     'icon' => 'fa-image',             'color' => '#b45309', 'cat' => 'convert',  'live' => true, 'desc' => 'Convert PNG, WebP, GIF or BMP images to JPG — several files at once.'],
    'convert-to-png'     => ['name' => 'Convert to PNG',     'icon' => 'fa-images',            'color' => '#b45309', 'cat' => 'convert',  'live' => true, 'desc' => 'Convert JPG, WebP, GIF or BMP images to PNG — several files at once.'],
    'convert-to-webp'    => ['name' => 'Convert to WebP',    'icon' => 'fa-bolt',              'color' => '#b45309', 'cat' => 'convert',  'live' => true, 'desc' => 'Convert JPG or PNG to modern WebP for smaller, faster-loading images.'],

    'image-filters'      => ['name' => 'Photo Filters',      'icon' => 'fa-wand-magic-sparkles', 'color' => '#7c3aed', 'cat' => 'enhance', 'live' => true, 'desc' => 'Grayscale, sepia, invert, brightness and contrast with a live preview.'],
    'image-watermark'    => ['name' => 'Image Watermark',    'icon' => 'fa-stamp',             'color' => '#7c3aed', 'cat' => 'enhance',  'live' => true, 'desc' => 'Stamp a text watermark on your photo — position, size, color and opacity.'],

    'image-color-picker' => ['name' => 'Color Picker',       'icon' => 'fa-eye-dropper',       'color' => '#0ea5e9', 'cat' => 'more',     'live' => true, 'desc' => 'Click anywhere on your image to get the exact HEX and RGB color codes.'],
];
