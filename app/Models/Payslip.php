<?php

namespace App\Models;

use App\Enums\PayslipStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payslip extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payslip_number',
        'payroll_period_id',
        'employee_id',
        'basic',
        'total_earnings',
        'total_deductions',
        'overtime_amount',
        'absent_deduction',
        'tax',
        'bonus',
        'net_pay',
        'working_days',
        'present_days',
        'leave_days',
        'absent_days',
        'status',
        'paid_at',
        'payment_method',
        'note',
    ];

    protected $casts = [
        'basic' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'absent_deduction' => 'decimal:2',
        'tax' => 'decimal:2',
        'bonus' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'working_days' => 'decimal:2',
        'present_days' => 'decimal:2',
        'leave_days' => 'decimal:2',
        'absent_days' => 'decimal:2',
        'status' => PayslipStatusEnum::class,
        'paid_at' => 'datetime',
    ];

    /* -------------------------------------------------------------------------- */
    /*                                Relationships                               */
    /* -------------------------------------------------------------------------- */

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayslipItem::class, 'payslip_id');
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(PayslipItem::class, 'payslip_id')->where('type', 'earning');
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(PayslipItem::class, 'payslip_id')->where('type', 'deduction');
    }

    /* -------------------------------------------------------------------------- */
    /*                                   Scopes                                   */
    /* -------------------------------------------------------------------------- */

    public function scopeForPeriod(Builder $query, int $periodId): Builder
    {
        return $query->where('payroll_period_id', $periodId);
    }

    public function scopeForEmployee(Builder $query, int $employeeId): Builder
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeStatus(Builder $query, string|PayslipStatusEnum $status): Builder
    {
        $val = $status instanceof PayslipStatusEnum ? $status->value : $status;

        return $query->where('status', $val);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('payslip_number', 'like', "%{$term}%")
                ->orWhereHas('employee', function ($eq) use ($term) {
                    $eq->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhereHas('employeeDetail', function ($edq) use ($term) {
                            $edq->where('emp_code', 'like', "%{$term}%");
                        });
                });
        });
    }

    /* -------------------------------------------------------------------------- */
    /*                               Helper Methods                               */
    /* -------------------------------------------------------------------------- */

    public function getGrossSalaryAttribute(): float
    {
        return (float) $this->basic + (float) $this->total_earnings + (float) $this->overtime_amount + (float) $this->bonus;
    }
}
