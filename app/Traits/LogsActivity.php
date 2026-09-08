<?php

namespace App\Traits;

use App\Models\ActivityLog;

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
        $desc = "{$classBasename} #{$model->id} was {$event}.";

        if ($event === 'updated' && isset($newValues['status_id'])) {
            $desc = "{$classBasename} #{$model->id} status changed.";
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
