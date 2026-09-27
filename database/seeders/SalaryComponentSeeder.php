<?php

namespace Database\Seeders;

use App\Enums\SalaryComponentCalcTypeEnum;
use App\Enums\SalaryComponentStatusEnum;
use App\Enums\SalaryComponentTypeEnum;
use App\Models\EmployeeSalaryComponent;
use App\Models\SalaryComponent;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalaryComponentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $components = [
            // Earnings
            [
                'name' => 'House Rent Allowance (HRA)',
                'type' => SalaryComponentTypeEnum::EARNING,
                'calc_type' => SalaryComponentCalcTypeEnum::PERCENT_OF_BASIC,
                'value' => 40.00,
                'is_taxable' => true,
                'status' => SalaryComponentStatusEnum::ACTIVE,
                'description' => '40% of basic salary for residential accommodation.',
            ],
            [
                'name' => 'Medical Allowance',
                'type' => SalaryComponentTypeEnum::EARNING,
                'calc_type' => SalaryComponentCalcTypeEnum::FIXED,
                'value' => 200.00,
                'is_taxable' => false,
                'status' => SalaryComponentStatusEnum::ACTIVE,
                'description' => 'Fixed monthly medical allowance for health expenses.',
            ],
            [
                'name' => 'Conveyance Allowance',
                'type' => SalaryComponentTypeEnum::EARNING,
                'calc_type' => SalaryComponentCalcTypeEnum::FIXED,
                'value' => 150.00,
                'is_taxable' => false,
                'status' => SalaryComponentStatusEnum::ACTIVE,
                'description' => 'Monthly transport and conveyance allowance.',
            ],
            [
                'name' => 'Special Allowance',
                'type' => SalaryComponentTypeEnum::EARNING,
                'calc_type' => SalaryComponentCalcTypeEnum::PERCENT_OF_BASIC,
                'value' => 10.00,
                'is_taxable' => true,
                'status' => SalaryComponentStatusEnum::ACTIVE,
                'description' => 'Performance and role-based special allowance.',
            ],
            [
                'name' => 'Mobile & Internet Reimbursement',
                'type' => SalaryComponentTypeEnum::EARNING,
                'calc_type' => SalaryComponentCalcTypeEnum::FIXED,
                'value' => 50.00,
                'is_taxable' => false,
                'status' => SalaryComponentStatusEnum::ACTIVE,
                'description' => 'Connectivity allowance for remote and on-duty work.',
            ],

            // Deductions
            [
                'name' => 'Provident Fund (PF)',
                'type' => SalaryComponentTypeEnum::DEDUCTION,
                'calc_type' => SalaryComponentCalcTypeEnum::PERCENT_OF_BASIC,
                'value' => 10.00,
                'is_taxable' => false,
                'status' => SalaryComponentStatusEnum::ACTIVE,
                'description' => '10% employee statutory contribution to retirement provident fund.',
            ],
            [
                'name' => 'Health & Life Insurance',
                'type' => SalaryComponentTypeEnum::DEDUCTION,
                'calc_type' => SalaryComponentCalcTypeEnum::FIXED,
                'value' => 80.00,
                'is_taxable' => false,
                'status' => SalaryComponentStatusEnum::ACTIVE,
                'description' => 'Group health and life insurance premium contribution.',
            ],
            [
                'name' => 'Professional Tax',
                'type' => SalaryComponentTypeEnum::DEDUCTION,
                'calc_type' => SalaryComponentCalcTypeEnum::FIXED,
                'value' => 50.00,
                'is_taxable' => false,
                'status' => SalaryComponentStatusEnum::ACTIVE,
                'description' => 'Standard monthly municipal and professional tax deduction.',
            ],
        ];

        $createdComponents = [];
        foreach ($components as $compData) {
            $createdComponents[] = SalaryComponent::create($compData);
        }

        // Assign standard components to seeded employees with basic_salary > 0
        $employees = User::whereHas('employeeDetail', function ($q) {
            $q->where('basic_salary', '>', 0);
        })->get();

        foreach ($employees as $employee) {
            // Assign HRA, Medical, PF, and Insurance to all employees by default
            foreach ($createdComponents as $c) {
                if (in_array($c->name, ['House Rent Allowance (HRA)', 'Medical Allowance', 'Provident Fund (PF)', 'Health & Life Insurance'])) {
                    EmployeeSalaryComponent::create([
                        'employee_id' => $employee->id,
                        'component_id' => $c->id,
                        'value' => null, // inherits component default
                    ]);
                }
            }
        }
    }
}
