<?php

/*
 * Text / writing tools shown at /text-tools.
 * Each entry: name, icon (font-awesome), color, cat (count|convert|clean|generate), live, desc.
 * Admin can override per-tool state and hide the whole section (App\Support\TextTools).
 */
return [
    'word-counter'           => ['name' => 'Word Counter',            'icon' => 'fa-calculator',     'color' => '#6d28d9', 'cat' => 'count',    'live' => true, 'desc' => 'Count words, characters, sentences, paragraphs and reading time as you type.'],
    'case-converter'         => ['name' => 'Case Converter',          'icon' => 'fa-font',           'color' => '#6d28d9', 'cat' => 'convert',  'live' => true, 'desc' => 'Convert text to UPPERCASE, lowercase, Title Case, Sentence case and more.'],
    'reverse-text'           => ['name' => 'Reverse Text',            'icon' => 'fa-arrow-right-arrow-left', 'color' => '#6d28d9', 'cat' => 'convert', 'live' => true, 'desc' => 'Reverse letters, words or whole lines of any text instantly.'],
    'remove-duplicate-lines' => ['name' => 'Remove Duplicate Lines',  'icon' => 'fa-clone',          'color' => '#1f7244', 'cat' => 'clean',    'live' => true, 'desc' => 'Delete repeated lines and optionally trim blank lines from a list.'],
    'sort-lines'             => ['name' => 'Sort Text Lines',         'icon' => 'fa-arrow-down-a-z',  'color' => '#1f7244', 'cat' => 'clean',    'live' => true, 'desc' => 'Sort lines A→Z, Z→A, by length, or shuffle them randomly.'],
    'find-replace'           => ['name' => 'Find and Replace',        'icon' => 'fa-magnifying-glass', 'color' => '#1f7244', 'cat' => 'clean',   'live' => true, 'desc' => 'Find and replace text — plain or regex, case sensitive or not.'],
    'lorem-ipsum'            => ['name' => 'Lorem Ipsum Generator',   'icon' => 'fa-paragraph',      'color' => '#e5322d', 'cat' => 'generate', 'live' => true, 'desc' => 'Generate placeholder paragraphs, sentences or words for mockups.'],
    'slug-generator'         => ['name' => 'Slug Generator',          'icon' => 'fa-link',           'color' => '#e5322d', 'cat' => 'generate', 'live' => true, 'desc' => 'Turn any title into a clean, URL-friendly slug for pages and products.'],
];
