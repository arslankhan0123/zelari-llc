<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Admin-controlled state for the Image Tools section.
 * - Master switch  : setting 'imagetools_enabled' ('1'/'0')
 * - Per-tool state : setting 'imagetools_overrides' (JSON {slug: 'live'|'soon'})
 * Defaults come from config/imagetools.php.
 */
class ImageTools
{
    public static function enabled(): bool
    {
        try {
            return (string) Setting::get('imagetools_enabled', '1') !== '0';
        } catch (\Throwable $e) {
            return true;
        }
    }

    public static function overrides(): array
    {
        try {
            $raw = Setting::get('imagetools_overrides', '{}');
            $arr = json_decode((string) $raw, true);

            return is_array($arr) ? $arr : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** config/imagetools.php merged with the admin overrides. */
    public static function all(): array
    {
        $tools = config('imagetools', []);
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
