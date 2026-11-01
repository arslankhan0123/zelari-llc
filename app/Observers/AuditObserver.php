<?php
namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Schema;

class AuditObserver
{
    public function created($model): void { $this->log('created', $model); }
    public function updated($model): void { $this->log('updated', $model); }
    public function deleted($model): void { $this->log('deleted', $model); }

    private function log(string $action, $model): void
    {
        try {
            if (!auth()->check() || !Schema::hasTable('audit_logs')) return;
            // Skip the audit log itself and noisy chat traffic.
            if ($model instanceof AuditLog) return;

            $name = class_basename($model);
            $label = $model->name ?? $model->title ?? $model->order_number ?? $model->ref ?? ('#' . $model->getKey());

            AuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => $action,
                'model'       => $name,
                'model_id'    => $model->getKey(),
                'description' => "{$name} {$label}",
                'ip'          => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            // auditing must never break the request
        }
    }
}
