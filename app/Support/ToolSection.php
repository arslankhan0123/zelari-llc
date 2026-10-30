<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Shared admin-controlled state for a browser-side tool section.
 * Subclasses define key()/configName(); behaviour mirrors PdfTools/ImageTools:
 *   - master switch : setting '<key>_enabled' ('1'/'0')
 *   - per-tool state: setting '<key>_overrides' (JSON {slug: 'live'|'soon'})
 * Defaults come from config/<configName>.php.
 */
abstract class ToolSection
{
    abstract protected static function key(): string;

    protected static function configName(): string
    {
        return static::key();
    }

    public static function enabled(): bool
    {
        try {
            return (string) Setting::get(static::key() . '_enabled', '1') !== '0';
        } catch (\Throwable $e) {
            return true;
        }
    }

    public static function overrides(): array
    {
        try {
            $arr = json_decode((string) Setting::get(static::key() . '_overrides', '{}'), true);

            return is_array($arr) ? $arr : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function all(): array
    {
        $tools = config(static::configName(), []);
        foreach (static::overrides() as $slug => $state) {
            if (isset($tools[$slug])) {
                $tools[$slug]['live'] = $state === 'live';
            }
        }

        return $tools;
    }

    public static function isLive(string $slug): bool
    {
        $tools = static::all();

        return (bool) ($tools[$slug]['live'] ?? false);
    }
}
