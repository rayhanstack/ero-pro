<?php

namespace App\Models;

use App\Enums\AttendanceSourceEnum;
use App\Enums\AttendanceStatusEnum;
use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attendances';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'work_minutes',
        'late_minutes',
        'overtime_minutes',
        'status',
        'source',
        'note',
        'ip',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'check_in' => 'datetime',
            'check_out' => 'datetime',
            'work_minutes' => 'integer',
            'late_minutes' => 'integer',
            'overtime_minutes' => 'integer',
            'status' => AttendanceStatusEnum::class,
            'source' => AttendanceSourceEnum::class,
        ];
    }

    /**
     * Get the employee/user associated with the attendance.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Alias for employee.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Scope query for a specific date.
     */
    public function scopeForDate(Builder $query, string|Carbon $date): Builder
    {
        $dateStr = $date instanceof Carbon ? $date->format('Y-m-d') : $date;

        return $query->whereDate('date', $dateStr);
    }

    /**
     * Scope query for a specific employee.
     */
    public function scopeForEmployee(Builder $query, int $employeeId): Builder
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Scope query for a specific month and year.
     */
    public function scopeForMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->whereYear('date', $year)
            ->whereMonth('date', $month);
    }

    /**
     * Scope query for department.
     */
    public function scopeForDepartment(Builder $query, int $departmentId): Builder
    {
        return $query->whereHas('employee.detail', function (Builder $q) use ($departmentId) {
            $q->where('department_id', $departmentId);
        });
    }

    /**
     * Scope query by status.
     */
    public function scopeByStatus(Builder $query, AttendanceStatusEnum|string $status): Builder
    {
        $statusVal = $status instanceof AttendanceStatusEnum ? $status->value : $status;

        return $query->where('status', $statusVal);
    }

    /**
     * Scope query for late attendances.
     */
    public function scopeLate(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('status', AttendanceStatusEnum::LATE->value)
                ->orWhere('late_minutes', '>', 0);
        });
    }

    /**
     * Check if this attendance record is late.
     */
    public function getIsLateAttribute(): bool
    {
        return $this->status === AttendanceStatusEnum::LATE || $this->late_minutes > 0;
    }

    /**
     * Formatted check-in time (e.g., 09:15 AM).
     */
    public function getCheckInTimeAttribute(): ?string
    {
        return $this->check_in ? $this->check_in->format('h:i A') : null;
    }


    /**
     * Formatted check-out time (e.g., 06:00 PM).
     */
    public function getCheckOutTimeAttribute(): ?string
    {
        return $this->check_out ? $this->check_out->format('h:i A') : null;
    }

    /**
     * Formatted work duration (e.g. 8h 30m).
     */
    public function getWorkDurationFormattedAttribute(): string
    {
        if ($this->work_minutes <= 0) {
            return '0m';
        }

        $hours = intdiv($this->work_minutes, 60);
        $minutes = $this->work_minutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        }

        return "{$minutes}m";
    }

    /**
     * Formatted late duration.
     */
    public function getLateDurationFormattedAttribute(): string
    {
        if ($this->late_minutes <= 0) {
            return '-';
        }

        $hours = intdiv($this->late_minutes, 60);
        $minutes = $this->late_minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }

    /**
     * Formatted overtime duration.
     */
    public function getOvertimeDurationFormattedAttribute(): string
    {
        if ($this->overtime_minutes <= 0) {
            return '-';
        }

        $hours = intdiv($this->overtime_minutes, 60);
        $minutes = $this->overtime_minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }
}
