<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'allocated',
        'used',
        'carried',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'allocated' => 'float',
            'used' => 'float',
            'carried' => 'float',
        ];
    }

    /**
     * Total available leave days (allocated + carried).
     */
    public function getTotalAvailableAttribute(): float
    {
        return (float) ($this->allocated + $this->carried);
    }

    /**
     * Remaining leave days (allocated + carried - used).
     */
    public function getRemainingAttribute(): float
    {
        return max(0.0, (float) ($this->allocated + $this->carried - $this->used));
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
}
