<?php

namespace App\Models;

use App\Enums\MeetingAttendeeResponseEnum;
use App\Enums\MeetingStatusEnum;
use App\Enums\MeetingTypeEnum;
use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Meeting extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'title',
        'agenda',
        'date',
        'start_time',
        'end_time',
        'location',
        'meeting_link',
        'type',
        'organizer_id',
        'project_id',
        'status',
        'reminder_sent',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'type' => MeetingTypeEnum::class,
            'status' => MeetingStatusEnum::class,
            'reminder_sent' => 'boolean',
        ];
    }

    /* -------------------------------------------------------------------------- */
    /*                                Relationships                               */
    /* -------------------------------------------------------------------------- */

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(MeetingAttendee::class);
    }

    public function employeeAttendees(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'attendee', 'meeting_attendees')
            ->withPivot('id', 'response', 'notes')
            ->withTimestamps();
    }

    public function clientAttendees(): MorphToMany
    {
        return $this->morphedByMany(Client::class, 'attendee', 'meeting_attendees')
            ->withPivot('id', 'response', 'notes')
            ->withTimestamps();
    }

    public function minutes(): HasOne
    {
        return $this->hasOne(MeetingMinute::class);
    }

    /* -------------------------------------------------------------------------- */
    /*                                   Scopes                                   */
    /* -------------------------------------------------------------------------- */

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('agenda', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%");
        });
    }

    public function scopeStatus(Builder $query, $status): Builder
    {
        if (blank($status)) {
            return $query;
        }

        $val = $status instanceof MeetingStatusEnum ? $status->value : $status;

        return $query->where('status', $val);
    }

    public function scopeType(Builder $query, $type): Builder
    {
        if (blank($type)) {
            return $query;
        }

        $val = $type instanceof MeetingTypeEnum ? $type->value : $type;

        return $query->where('type', $val);
    }

    public function scopeProject(Builder $query, $projectId): Builder
    {
        if (blank($projectId)) {
            return $query;
        }

        return $query->where('project_id', $projectId);
    }

    public function scopeOrganizer(Builder $query, $organizerId): Builder
    {
        if (blank($organizerId)) {
            return $query;
        }

        return $query->where('organizer_id', $organizerId);
    }

    public function scopeDateRange(Builder $query, ?string $start, ?string $end): Builder
    {
        if (! blank($start)) {
            $query->where('date', '>=', $start);
        }

        if (! blank($end)) {
            $query->where('date', '<=', $end);
        }

        return $query;
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('organizer_id', $userId)
                ->orWhereHas('attendees', function ($aq) use ($userId) {
                    $aq->where('attendee_type', User::class)
                        ->where('attendee_id', $userId);
                });
        });
    }

    /* -------------------------------------------------------------------------- */
    /*                                 Attributes                                 */
    /* -------------------------------------------------------------------------- */

    public function getStartDateTimeAttribute(): ?Carbon
    {
        if (! $this->date || ! $this->start_time) {
            return null;
        }

        $time = is_string($this->start_time) ? substr($this->start_time, 0, 5) : $this->start_time;

        return Carbon::parse($this->date->format('Y-m-d') . ' ' . $time);
    }

    public function getEndDateTimeAttribute(): ?Carbon
    {
        if (! $this->date || ! $this->end_time) {
            return null;
        }

        $time = is_string($this->end_time) ? substr($this->end_time, 0, 5) : $this->end_time;

        return Carbon::parse($this->date->format('Y-m-d') . ' ' . $time);
    }

    public function getFormattedTimeRangeAttribute(): string
    {
        $start = $this->start_time ? Carbon::parse($this->start_time)->format('h:i A') : '';
        $end = $this->end_time ? Carbon::parse($this->end_time)->format('h:i A') : '';

        return "{$start} - {$end}";
    }

    public function getDurationMinutesAttribute(): int
    {
        if (! $this->start_time || ! $this->end_time) {
            return 0;
        }

        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        return max(0, $start->diffInMinutes($end));
    }

    public function getIsPastAttribute(): bool
    {
        if (! $this->date) {
            return false;
        }

        $end = $this->end_date_time ?? Carbon::parse($this->date->format('Y-m-d') . ' 23:59:59');

        return $end->isPast();
    }

    public function getAcceptedCountAttribute(): int
    {
        return $this->attendees->where('response', MeetingAttendeeResponseEnum::ACCEPTED)->count();
    }

    public function getDeclinedCountAttribute(): int
    {
        return $this->attendees->where('response', MeetingAttendeeResponseEnum::DECLINED)->count();
    }

    public function getPendingCountAttribute(): int
    {
        return $this->attendees->where('response', MeetingAttendeeResponseEnum::PENDING)->count();
    }
}
