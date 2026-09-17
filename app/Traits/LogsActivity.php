<?php

namespace App\Traits;

use App\Models\ActivityLog;
use App\Models\Status;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            static::recordActivity($model, 'created', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            unset($changes['updated_at']);

            if (empty($changes)) {
                return;
            }

            $original = array_intersect_key($model->getOriginal(), $changes);

            static::recordActivity($model, 'updated', $original, $changes);
        });

        static::deleted(function ($model) {
            static::recordActivity($model, 'deleted', $model->getOriginal(), null);
        });
    }

    protected static function recordActivity($model, string $event, ?array $oldValues, ?array $newValues): void
    {
        $classBasename = class_basename($model);
        $tag = $model->asset_tag ?? $model->component_tag ?? $model->name ?? "#{$model->id}";
        $desc = "{$classBasename} {$tag} was {$event}.";

        if ($event === 'updated' && isset($newValues['status_id'])) {
            $oldStatus = isset($oldValues['status_id']) ? Status::find($oldValues['status_id'])?->name : null;
            $newStatus = Status::find($newValues['status_id'])?->name ?? 'New Status';
            $desc = "{$classBasename} {$tag} status changed to {$newStatus}".($oldStatus ? " (was {$oldStatus})" : '').'.';
        } elseif ($event === 'updated' && ! empty($newValues)) {
            $changedKeys = array_keys($newValues);
            $desc = "{$classBasename} {$tag} updated fields: ".implode(', ', $changedKeys).'.';
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'loggable_type' => get_class($model),
            'loggable_id' => $model->id,
            'event' => $event,
            'description' => $desc,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'loggable')->latest();
    }
}
