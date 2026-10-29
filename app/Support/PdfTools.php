<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Admin-controlled state for the PDF Tools section.
 * - Master switch  : setting 'pdftools_enabled' ('1'/'0')
 * - Per-tool state : setting 'pdftools_overrides' (JSON {slug: 'live'|'soon'})
 * Defaults come from config/pdftools.php and are only overridden when the
 * admin flips a toggle, so new tools added in config keep working as before.
 */
class PdfTools
{
    public static function enabled(): bool
    {
        try {
            return (string) Setting::get('pdftools_enabled', '1') !== '0';
        } catch (\Throwable $e) {
            return true;
        }
    }

    public static function overrides(): array
    {
        try {
            $raw = Setting::get('pdftools_overrides', '{}');
            $arr = json_decode((string) $raw, true);

            return is_array($arr) ? $arr : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** config/pdftools.php merged with the admin overrides. */
    public static function all(): array
    {
        $tools = config('pdftools', []);
        foreach (self::overrides() as $slug => $state) {
            if (isset($tools[$slug])) {
                $tools[$slug]['live'] = $state === 'live';
            }
        }

        return $tools;
    }

    public static function isLive(string $slug): bool
    {
        $tools = self::all();

        return (bool) ($tools[$slug]['live'] ?? false);
    }
}
