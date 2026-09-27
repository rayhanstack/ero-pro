<?php

namespace App\Models;

use App\Enums\PayrollPeriodStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollPeriod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'month',
        'year',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => PayrollPeriodStatusEnum::class,
    ];

    /* -------------------------------------------------------------------------- */
    /*                                Relationships                               */
    /* -------------------------------------------------------------------------- */

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class, 'payroll_period_id');
    }

    /* -------------------------------------------------------------------------- */
    /*                                   Scopes                                   */
    /* -------------------------------------------------------------------------- */

    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    public function scopeStatus(Builder $query, string|PayrollPeriodStatusEnum $status): Builder
    {
        $val = $status instanceof PayrollPeriodStatusEnum ? $status->value : $status;

        return $query->where('status', $val);
    }

    /* -------------------------------------------------------------------------- */
    /*                               Helper Methods                               */
    /* -------------------------------------------------------------------------- */

    public function getFormattedPeriodAttribute(): string
    {
        if ($this->name) {
            return $this->name;
        }

        return Carbon::createFromDate($this->year, $this->month, 1)->format('F Y');
    }

    public function getPayslipsCountAttribute(): int
    {
        return $this->payslips()->count();
    }

    public function getTotalEmployeesAttribute(): int
    {
        return $this->payslips()->count();
    }

    public function getTotalDisbursedAttribute(): float
    {
        return (float) $this->payslips()->sum('net_pay');
    }
}
