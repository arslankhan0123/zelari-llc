<?php

/*
|--------------------------------------------------------------------------
| PDF Tools — the catalog behind /tools
|--------------------------------------------------------------------------
| Add a tool here and it appears on the tools page automatically.
| cat: organize | optimize | convert | edit | security
*/

return [

    'merge-pdf'      => ['name' => 'Merge PDF',      'icon' => 'fa-object-group',        'color' => '#e5322d', 'cat' => 'organize', 'live' => true, 'desc' => 'Combine PDFs in the order you want with the easiest PDF merger available.'],
    'split-pdf'      => ['name' => 'Split PDF',      'icon' => 'fa-scissors',            'color' => '#e5322d', 'cat' => 'organize', 'live' => true, 'desc' => 'Separate one page or a whole set for easy conversion into independent PDF files.'],
    'organize-pdf'   => ['name' => 'Organize PDF',   'icon' => 'fa-folder-tree',         'color' => '#e5322d', 'cat' => 'organize', 'live' => true, 'desc' => 'Sort, delete or add pages to your PDF file however you like.'],
    'compress-pdf'   => ['name' => 'Compress PDF',   'icon' => 'fa-file-zipper',         'color' => '#1db954', 'cat' => 'optimize', 'live' => true, 'desc' => 'Reduce file size while optimizing for maximal PDF quality.'],
    'repair-pdf'     => ['name' => 'Repair PDF',     'icon' => 'fa-screwdriver-wrench',  'color' => '#1db954', 'cat' => 'optimize', 'live' => true, 'desc' => 'Repair a damaged PDF and recover data from corrupt PDF files.'],
    'ocr-pdf'        => ['name' => 'OCR PDF',        'icon' => 'fa-font',                'color' => '#1db954', 'cat' => 'optimize', 'live' => true, 'desc' => 'Convert scanned PDF into searchable and selectable documents.'],
    'pdf-to-word'    => ['name' => 'PDF to Word',    'icon' => 'fa-file-word',           'color' => '#2b7cd3', 'cat' => 'convert',  'live' => true, 'desc' => 'Convert your PDF files into easy to edit DOC and DOCX documents.'],
    'pdf-to-ppt'     => ['name' => 'PDF to PowerPoint', 'icon' => 'fa-file-powerpoint',  'color' => '#d35230', 'cat' => 'convert',  'live' => true, 'desc' => 'Turn your PDF files into easy to edit PPT and PPTX slideshows.'],
    'pdf-to-excel'   => ['name' => 'PDF to Excel',   'icon' => 'fa-file-excel',          'color' => '#1f7244', 'cat' => 'convert',  'live' => true, 'desc' => 'Pull data straight from PDFs into Excel spreadsheets in a few seconds.'],
    'word-to-pdf'    => ['name' => 'Word to PDF',    'icon' => 'fa-file-word',           'color' => '#2b7cd3', 'cat' => 'convert',  'live' => true, 'desc' => 'Make DOC and DOCX files easy to read by converting them to PDF.'],
    'ppt-to-pdf'     => ['name' => 'PowerPoint to PDF', 'icon' => 'fa-file-powerpoint',  'color' => '#d35230', 'cat' => 'convert',  'live' => true, 'desc' => 'Make PPT and PPTX slideshows easy to view by converting them to PDF.'],
    'excel-to-pdf'   => ['name' => 'Excel to PDF',   'icon' => 'fa-file-excel',          'color' => '#1f7244', 'cat' => 'convert',  'live' => true, 'desc' => 'Make Excel spreadsheets easy to read by converting them to PDF.'],
    'pdf-to-jpg'     => ['name' => 'PDF to JPG',     'icon' => 'fa-file-image',          'color' => '#f2b705', 'cat' => 'convert',  'desc' => 'Convert each PDF page into a JPG or extract all images contained in a PDF.'],
    'jpg-to-pdf'     => ['name' => 'JPG to PDF',     'icon' => 'fa-image',               'color' => '#f2b705', 'cat' => 'convert',  'live' => true, 'desc' => 'Convert JPG images to PDF in seconds. Easily adjust orientation and margins.'],
    'html-to-pdf'    => ['name' => 'HTML to PDF',    'icon' => 'fa-globe',               'color' => '#f2b705', 'cat' => 'convert',  'desc' => 'Convert webpages to PDF. Paste the URL of the page and convert it with a click.'],
    'edit-pdf'       => ['name' => 'Edit PDF',       'icon' => 'fa-file-pen',            'color' => '#8b5cf6', 'cat' => 'edit',     'desc' => 'Add text, images, shapes or freehand annotations to a PDF document.'],
    'rotate-pdf'     => ['name' => 'Rotate PDF',     'icon' => 'fa-rotate',              'color' => '#8b5cf6', 'cat' => 'edit',     'live' => true, 'desc' => 'Rotate your PDFs the way you need them. Rotate multiple PDFs at once!'],
    'page-numbers'   => ['name' => 'Page numbers',   'icon' => 'fa-list-ol',             'color' => '#8b5cf6', 'cat' => 'edit',     'live' => true, 'desc' => 'Add page numbers into PDFs with ease, with custom positions and typography.'],
    'watermark-pdf'  => ['name' => 'Watermark',      'icon' => 'fa-stamp',               'color' => '#8b5cf6', 'cat' => 'edit',     'live' => true, 'desc' => 'Stamp an image or text over your PDF in seconds.'],
    'crop-pdf'       => ['name' => 'Crop PDF',       'icon' => 'fa-crop',                'color' => '#8b5cf6', 'cat' => 'edit',     'desc' => 'Crop margins of PDF documents or select specific areas.'],
    'sign-pdf'       => ['name' => 'Sign PDF',       'icon' => 'fa-signature',           'color' => '#0ea5e9', 'cat' => 'security', 'desc' => 'Sign yourself or request electronic signatures from others.'],
    'unlock-pdf'     => ['name' => 'Unlock PDF',     'icon' => 'fa-unlock',              'color' => '#0ea5e9', 'cat' => 'security', 'desc' => 'Remove PDF password security, giving you freedom to use your PDFs.'],
    'protect-pdf'    => ['name' => 'Protect PDF',    'icon' => 'fa-lock',                'color' => '#0ea5e9', 'cat' => 'security', 'desc' => 'Protect PDF files with a password to prevent unauthorized access.'],
    'redact-pdf'     => ['name' => 'Redact PDF',     'icon' => 'fa-eraser',              'color' => '#0ea5e9', 'cat' => 'security', 'desc' => 'Redact text and graphics to permanently remove sensitive information.'],
    'compare-pdf'    => ['name' => 'Compare PDF',    'icon' => 'fa-code-compare',        'color' => '#0ea5e9', 'cat' => 'security', 'desc' => 'Show a side-by-side comparison and easily spot changes between file versions.'],

];
