<?php

namespace App\Models;

use App\Enums\SalaryComponentCalcTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSalaryComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'component_id',
        'value',
    ];

    protected $casts = [
        'value' => 'decimal:2',
    ];

    /* -------------------------------------------------------------------------- */
    /*                                Relationships                               */
    /* -------------------------------------------------------------------------- */

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(SalaryComponent::class, 'component_id');
    }

    /* -------------------------------------------------------------------------- */
    /*                               Helper Methods                               */
    /* -------------------------------------------------------------------------- */

    /**
     * Get effective value (custom override if set, else component default).
     */
    public function getEffectiveValueAttribute(): float
    {
        if ($this->value !== null) {
            return (float) $this->value;
        }

        return $this->component ? (float) $this->component->value : 0.00;
    }

    /**
     * Calculate amount based on basic salary.
     */
    public function calculateAmount(float $basicSalary): float
    {
        if (! $this->component) {
            return 0.00;
        }

        return $this->component->calculateAmount($basicSalary, $this->value);
    }
}
