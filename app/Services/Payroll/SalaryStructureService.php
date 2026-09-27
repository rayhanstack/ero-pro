<?php

namespace App\Services\Payroll;

use App\Enums\EmployeeStatusEnum;
use App\Enums\SalaryComponentTypeEnum;
use App\Models\EmployeeDetail;
use App\Models\EmployeeSalaryComponent;
use App\Models\SalaryComponent;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalaryStructureService
{
    /**
     * Get paginated employees with their salary breakdown details.
     */
    public function getPaginatedEmployeesWithStructure(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::query()
            ->whereHas('employeeDetail')
            ->with([
                'employeeDetail.department',
                'employeeDetail.designation',
                'employeeSalaryComponents.component',
            ]);

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['department_id'])) {
            $query->filterByDepartment($filters['department_id']);
        }

        if (! empty($filters['designation_id'])) {
            $query->filterByDesignation($filters['designation_id']);
        }

        if (! empty($filters['status'])) {
            $query->filterByStatus($filters['status']);
        } else {
            $query->active();
        }

        $paginator = $query->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        // Attach computed payroll breakdown to each employee
        $paginator->getCollection()->transform(function (User $employee) {
            $breakdown = $this->calculateEmployeeBreakdown($employee);
            $employee->salary_breakdown = $breakdown;

            return $employee;
        });

        return $paginator;
    }

    /**
     * Get full salary structure details for a single employee.
     */
    public function getEmployeeSalaryStructure(User $employee): array
    {
        $employee->loadMissing([
            'employeeDetail.department',
            'employeeDetail.designation',
            'employeeDetail.shift',
            'employeeSalaryComponents.component',
        ]);

        $basicSalary = (float) ($employee->employeeDetail?->basic_salary ?? 0.00);
        $assignedComponents = $employee->employeeSalaryComponents->keyBy('component_id');

        $allComponents = SalaryComponent::active()
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $earnings = [];
        $deductions = [];
        $totalEarnings = 0.00;
        $totalDeductions = 0.00;

        foreach ($allComponents as $comp) {
            $isAssigned = $assignedComponents->has($comp->id);
            $assignedRecord = $isAssigned ? $assignedComponents->get($comp->id) : null;
            $overrideValue = $assignedRecord ? $assignedRecord->value : null;

            $effectiveValue = $overrideValue !== null ? (float) $overrideValue : (float) $comp->value;
            $calculatedAmount = $comp->calculateAmount($basicSalary, $overrideValue);

            $item = [
                'component' => $comp,
                'is_assigned' => $isAssigned,
                'override_value' => $overrideValue,
                'effective_value' => $effectiveValue,
                'calculated_amount' => $calculatedAmount,
            ];

            if ($comp->type === SalaryComponentTypeEnum::EARNING) {
                $earnings[] = $item;
                if ($isAssigned) {
                    $totalEarnings += $calculatedAmount;
                }
            } else {
                $deductions[] = $item;
                if ($isAssigned) {
                    $totalDeductions += $calculatedAmount;
                }
            }
        }

        $grossSalary = $basicSalary + $totalEarnings;
        $netSalary = max(0, $grossSalary - $totalDeductions);

        return [
            'employee' => $employee,
            'basic_salary' => $basicSalary,
            'earnings' => $earnings,
            'deductions' => $deductions,
            'total_earnings' => round($totalEarnings, 2),
            'total_deductions' => round($totalDeductions, 2),
            'gross_salary' => round($grossSalary, 2),
            'net_salary' => round($netSalary, 2),
        ];
    }

    /**
     * Update an employee's basic salary and assigned salary components.
     */
    public function updateEmployeeSalaryStructure(User $employee, array $data): array
    {
        return DB::transaction(function () use ($employee, $data) {
            $basicSalary = isset($data['basic_salary']) ? (float) $data['basic_salary'] : 0.00;
            $componentsInput = $data['components'] ?? [];

            // 1. Update basic_salary in employee_details
            if ($employee->employeeDetail) {
                $employee->employeeDetail->update(['basic_salary' => $basicSalary]);
            } else {
                EmployeeDetail::create([
                    'user_id' => $employee->id,
                    'emp_code' => 'EMP-' . str_pad((string) $employee->id, 4, '0', STR_PAD_LEFT),
                    'basic_salary' => $basicSalary,
                ]);
            }

            // 2. Sync employee_salary_components
            // Delete all current assignments
            EmployeeSalaryComponent::where('employee_id', $employee->id)->delete();

            // Insert newly enabled components
            $allActiveComponents = SalaryComponent::active()->get()->keyBy('id');

            foreach ($componentsInput as $compId => $config) {
                $isEnabled = ! empty($config['enabled']);
                if (! $isEnabled || ! $allActiveComponents->has($compId)) {
                    continue;
                }

                $comp = $allActiveComponents->get($compId);
                $customVal = (isset($config['value']) && $config['value'] !== '') ? (float) $config['value'] : null;

                // If user typed the exact same value as default, can keep or store
                EmployeeSalaryComponent::create([
                    'employee_id' => $employee->id,
                    'component_id' => $compId,
                    'value' => $customVal,
                ]);
            }

            return $this->getEmployeeSalaryStructure($employee->fresh());
        });
    }

    /**
     * Calculate live salary breakdown preview from raw inputs.
     */
    public function calculatePreview(float $basicSalary, array $componentsInput): array
    {
        $allComponents = SalaryComponent::active()->get()->keyBy('id');

        $earnings = [];
        $deductions = [];
        $totalEarnings = 0.00;
        $totalDeductions = 0.00;

        foreach ($componentsInput as $compId => $config) {
            $isEnabled = ! empty($config['enabled']);
            if (! $isEnabled || ! $allComponents->has($compId)) {
                continue;
            }

            $comp = $allComponents->get($compId);
            $customVal = (isset($config['value']) && $config['value'] !== '') ? (float) $config['value'] : null;
            $calculatedAmount = $comp->calculateAmount($basicSalary, $customVal);

            $item = [
                'id' => $comp->id,
                'name' => $comp->name,
                'type' => $comp->type->value,
                'calc_type' => $comp->calc_type->value,
                'rate_or_value' => $customVal !== null ? $customVal : (float) $comp->value,
                'amount' => $calculatedAmount,
            ];

            if ($comp->type === SalaryComponentTypeEnum::EARNING) {
                $earnings[] = $item;
                $totalEarnings += $calculatedAmount;
            } else {
                $deductions[] = $item;
                $totalDeductions += $calculatedAmount;
            }
        }

        $grossSalary = $basicSalary + $totalEarnings;
        $netSalary = max(0, $grossSalary - $totalDeductions);

        return [
            'basic_salary' => round($basicSalary, 2),
            'earnings' => $earnings,
            'deductions' => $deductions,
            'total_earnings' => round($totalEarnings, 2),
            'total_deductions' => round($totalDeductions, 2),
            'gross_salary' => round($grossSalary, 2),
            'net_salary' => round($netSalary, 2),
        ];
    }

    /**
     * Get statistics summary for salary structure overview.
     */
    public function getStats(): array
    {
        $activeEmployeesCount = User::active()->whereHas('employeeDetail')->count();
        $totalBasicPayroll = (float) EmployeeDetail::sum('basic_salary');

        return [
            'total_employees' => $activeEmployeesCount,
            'total_basic_payroll' => $totalBasicPayroll,
            'active_components_count' => SalaryComponent::active()->count(),
            'configured_employees' => EmployeeSalaryComponent::distinct('employee_id')->count('employee_id'),
        ];
    }

    /* -------------------------------------------------------------------------- */
    /*                               Helper Methods                               */
    /* -------------------------------------------------------------------------- */

    protected function calculateEmployeeBreakdown(User $employee): array
    {
        $basicSalary = (float) ($employee->employeeDetail?->basic_salary ?? 0.00);
        $totalEarnings = 0.00;
        $totalDeductions = 0.00;

        foreach ($employee->employeeSalaryComponents as $assigned) {
            $comp = $assigned->component;
            if (! $comp || $comp->status->value !== 'active') {
                continue;
            }

            $amount = $comp->calculateAmount($basicSalary, $assigned->value);

            if ($comp->type === SalaryComponentTypeEnum::EARNING) {
                $totalEarnings += $amount;
            } else {
                $totalDeductions += $amount;
            }
        }

        $grossSalary = $basicSalary + $totalEarnings;
        $netSalary = max(0, $grossSalary - $totalDeductions);

        return [
            'basic' => $basicSalary,
            'total_earnings' => round($totalEarnings, 2),
            'total_deductions' => round($totalDeductions, 2),
            'gross_salary' => round($grossSalary, 2),
            'net_pay' => round($netSalary, 2),
            'components_count' => $employee->employeeSalaryComponents->count(),
        ];
    }
}
