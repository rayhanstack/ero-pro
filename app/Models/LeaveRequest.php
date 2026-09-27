<?php

namespace App\Models;

use App\Enums\LeaveRequestStatusEnum;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'from_date',
        'to_date',
        'days',
        'half_day',
        'half_day_type',
        'reason',
        'attachment',
        'status',
        'approved_by',
        'approved_at',
        'remark',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'from_date' => 'date',
            'to_date' => 'date',
            'days' => 'float',
            'half_day' => 'boolean',
            'status' => LeaveRequestStatusEnum::class,
            'approved_at' => 'datetime',
        ];
    }

    /**
     * Employee relationship.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Leave type relationship.
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    /**
     * Approver relationship.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope query to pending requests.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', LeaveRequestStatusEnum::PENDING);
    }

    /**
     * Scope query to approved requests.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', LeaveRequestStatusEnum::APPROVED);
    }

    /**
     * Scope query to rejected requests.
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', LeaveRequestStatusEnum::REJECTED);
    }

    /**
     * Scope query to cancelled requests.
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', LeaveRequestStatusEnum::CANCELLED);
    }

    /**
     * Scope query to given year.
     */
    public function scopeYear(Builder $query, ?int $year): Builder
    {
        if (empty($year)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($year) {
            $q->whereYear('from_date', $year)
                ->orWhereYear('to_date', $year);
        });
    }
}
