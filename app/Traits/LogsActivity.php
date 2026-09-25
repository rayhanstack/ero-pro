<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function (Model $model) {
            $model->recordActivity('created', null, $model->getActivityNewAttributes());
        });

        static::updated(function (Model $model) {
            $changes = $model->getActivityNewAttributes();
            $original = $model->getActivityOldAttributes();

            if (! empty($changes)) {
                $model->recordActivity('updated', $original, $changes);
            }
        });

        static::deleted(function (Model $model) {
            $model->recordActivity('deleted', $model->getActivityOldAttributes(), null);
        });
    }

    protected function recordActivity(string $action, ?array $old = null, ?array $new = null): void
    {
        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'subject_type' => static::class,
                'subject_id' => $this->getKey(),
                'old' => $old,
                'new' => $new,
                'ip' => Request::ip(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Silently catch in case migration is not run yet or in CLI without table
        }
    }

    protected function getActivityHiddenAttributes(): array
    {
        $defaultHidden = ['password', 'remember_token', 'created_at', 'updated_at'];
        $modelHidden = property_exists($this, 'dontLogAttributes') ? (array) $this->dontLogAttributes : [];

        return array_unique(array_merge($defaultHidden, $this->getHidden(), $modelHidden));
    }

    protected function getActivityNewAttributes(): array
    {
        $attributes = $this->getChanges();
        if (empty($attributes)) {
            $attributes = $this->getAttributes();
        }

        return Arr::except($attributes, $this->getActivityHiddenAttributes());
    }

    protected function getActivityOldAttributes(): array
    {
        $attributes = $this->getOriginal();

        return Arr::except($attributes, $this->getActivityHiddenAttributes());
    }
}
