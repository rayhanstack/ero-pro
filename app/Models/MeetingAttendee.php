<?php

namespace App\Models;

use App\Enums\MeetingAttendeeResponseEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MeetingAttendee extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'attendee_type',
        'attendee_id',
        'response',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'response' => MeetingAttendeeResponseEnum::class,
        ];
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function attendee(): MorphTo
    {
        return $this->morphTo();
    }
}
