<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'old',
        'new',
        'ip',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old' => 'array',
            'new' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($activity) {
            $activity->created_at = $activity->created_at ?? now();
            $activity->ip = $activity->ip ?? Request::ip();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope query to apply filters (user_id, module/subject_type, date range, action).
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['module'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('subject_type', 'like', "%{$filters['module']}%")
                    ->orWhere('action', 'like', "{$filters['module']}.%");
            });
        }

        if (! empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    /**
     * Helper to log an activity manually.
     */
    public static function log(
        string $action,
        $subject = null,
        ?array $old = null,
        ?array $new = null,
        ?int $userId = null,
        ?string $ip = null
    ): self {
        return self::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id ?? null,
            'old' => $old,
            'new' => $new,
            'ip' => $ip ?? Request::ip(),
            'created_at' => now(),
        ]);
    }
}
