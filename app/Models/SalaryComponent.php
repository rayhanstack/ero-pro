<?php

namespace App\Models;

use App\Enums\SalaryComponentCalcTypeEnum;
use App\Enums\SalaryComponentStatusEnum;
use App\Enums\SalaryComponentTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryComponent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'calc_type',
        'value',
        'is_taxable',
        'status',
        'description',
    ];

    protected $casts = [
        'type' => SalaryComponentTypeEnum::class,
        'calc_type' => SalaryComponentCalcTypeEnum::class,
        'value' => 'decimal:2',
        'is_taxable' => 'boolean',
        'status' => SalaryComponentStatusEnum::class,
    ];

    /* -------------------------------------------------------------------------- */
    /*                                Relationships                               */
    /* -------------------------------------------------------------------------- */

    public function employeeSalaryComponents(): HasMany
    {
        return $this->hasMany(EmployeeSalaryComponent::class, 'component_id');
    }

    public function payslipItems(): HasMany
    {
        return $this->hasMany(PayslipItem::class, 'component_id');
    }

    /* -------------------------------------------------------------------------- */
    /*                                   Scopes                                   */
    /* -------------------------------------------------------------------------- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', SalaryComponentStatusEnum::ACTIVE->value);
    }

    public function scopeEarnings(Builder $query): Builder
    {
        return $query->where('type', SalaryComponentTypeEnum::EARNING->value);
    }

    public function scopeDeductions(Builder $query): Builder
    {
        return $query->where('type', SalaryComponentTypeEnum::DEDUCTION->value);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /* -------------------------------------------------------------------------- */
    /*                               Helper Methods                               */
    /* -------------------------------------------------------------------------- */

    /**
     * Calculate final amount based on given basic salary and optional value override.
     */
    public function calculateAmount(float $basicSalary, ?float $overrideValue = null): float
    {
        $val = $overrideValue !== null ? $overrideValue : (float) $this->value;

        if ($this->calc_type === SalaryComponentCalcTypeEnum::PERCENT_OF_BASIC) {
            return round(($basicSalary * $val) / 100, 2);
        }

        return round($val, 2);
    }

    /**
     * Display formatted rate/percentage string.
     */
    public function getFormattedValueAttribute(): string
    {
        if ($this->calc_type === SalaryComponentCalcTypeEnum::PERCENT_OF_BASIC) {
            return number_format((float) $this->value, 2) . '%';
        }

        return currency_format((float) $this->value);
    }
}
