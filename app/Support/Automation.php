<?php
namespace App\Support;

use App\Models\AutomationRule;
use App\Models\AutomationRun;
use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;

class Automation
{
    /** Fire an event; evaluate enabled rules and run their actions. Never throws. */
    public static function fire(string $event, array $context = []): void
    {
        try {
            $rules = AutomationRule::where('event', $event)->where('enabled', true)->get();
            foreach ($rules as $rule) {
                if (!static::matches($rule->conditions ?? [], $context)) {
                    continue;
                }
                $result = static::runActions($rule->actions ?? [], $context);
                $rule->forceFill(['run_count' => $rule->run_count + 1, 'last_run_at' => now()])->save();
                AutomationRun::create(['rule_id' => $rule->id, 'event' => $event, 'result' => $result]);
            }
        } catch (\Throwable $e) {
            // Automation must never break the business flow.
        }
    }

    private static function matches(array $conditions, array $context): bool
    {
        foreach ($conditions as $c) {
            $field = $c['field'] ?? null;
            if (!$field) continue;
            $actual = Arr::get($context, $field);
            $value  = $c['value'] ?? null;
            $op     = $c['op'] ?? '=';
            $ok = match ($op) {
                '='        => (string) $actual === (string) $value,
                '!='       => (string) $actual !== (string) $value,
                '>'        => (float) $actual > (float) $value,
                '<'        => (float) $actual < (float) $value,
                'contains' => str_contains((string) $actual, (string) $value),
                default    => true,
            };
            if (!$ok) return false;
        }
        return true;
    }

    private static function runActions(array $actions, array $context): string
    {
        $log = [];
        foreach ($actions as $a) {
            $type = $a['type'] ?? null;
            try {
                switch ($type) {
                    case 'email':
                        $to = $a['to'] ?? Arr::get($context, 'email');
                        if ($to && Setting::applyMailConfig()) {
                            $subject = static::tpl($a['subject'] ?? 'Notification', $context);
                            $body    = static::tpl($a['body'] ?? '', $context);
                            Mail::raw($body, fn ($m) => $m->to($to)->subject($subject));
                            $log[] = "emailed {$to}";
                        } else {
                            $log[] = 'email skipped (no mail config / recipient)';
                        }
                        break;
                    case 'webhook':
                        if (!empty($a['url'])) {
                            \Illuminate\Support\Facades\Http::timeout(5)->post($a['url'], $context);
                            $log[] = 'webhook sent';
                        }
                        break;
                    case 'note':
                        $log[] = 'note: ' . static::tpl($a['text'] ?? '', $context);
                        break;
                    default:
                        $log[] = "unknown action: {$type}";
                }
            } catch (\Throwable $e) {
                $log[] = ($type ?: 'action') . ' failed';
            }
        }
        return implode('; ', $log) ?: 'no actions';
    }

    private static function tpl(string $text, array $context): string
    {
        return preg_replace_callback('/\{(\w[\w.]*)\}/', fn ($m) => (string) (Arr::get($context, $m[1]) ?? $m[0]), $text);
    }
}
