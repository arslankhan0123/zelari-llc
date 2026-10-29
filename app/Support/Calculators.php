<?php

namespace App\Support;

use App\Models\Setting;

/** Admin-controlled state for the Calculators section (mirrors PdfTools). */
class Calculators
{
    public static function enabled(): bool
    {
        try {
            return (string) Setting::get('calculators_enabled', '1') !== '0';
        } catch (\Throwable $e) {
            return true;
        }
    }

    public static function overrides(): array
    {
        try {
            $arr = json_decode((string) Setting::get('calculators_overrides', '{}'), true);

            return is_array($arr) ? $arr : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function all(): array
    {
        $tools = config('calculators', []);
        foreach (self::overrides() as $slug => $state) {
            if (isset($tools[$slug])) {
                $tools[$slug]['live'] = $state === 'live';
            }
        }

        return $tools;
    }
}
