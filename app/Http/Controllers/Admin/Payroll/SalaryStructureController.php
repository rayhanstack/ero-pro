<?php

namespace App\Http\Controllers\Admin\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\UpdateSalaryStructureRequest;
use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use App\Services\Payroll\SalaryStructureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalaryStructureController extends Controller
{
    public function __construct(
        protected SalaryStructureService $salaryStructureService
    ) {}

    /**
     * Display a listing of employees and their salary structure status.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'department_id', 'designation_id', 'status']);

        $employees = $this->salaryStructureService->getPaginatedEmployeesWithStructure($filters, 15);
        $stats = $this->salaryStructureService->getStats();
        $departments = Department::active()->orderBy('name')->get();
        $designations = Designation::active()->orderBy('name')->get();

        $title = _trans('common.Employee Salary Structures');

        return view('admin.payroll.salary-structure.index', compact(
            'title',
            'employees',
            'stats',
            'filters',
            'departments',
            'designations'
        ));
    }

    /**
     * Show the salary structure configuration screen for a specific employee.
     */
    public function edit(User $employee): View
    {
        $structure = $this->salaryStructureService->getEmployeeSalaryStructure($employee);

        $title = _trans('common.Salary Structure') . ' — ' . $employee->name;

        return view('admin.payroll.salary-structure.edit', compact(
            'title',
            'employee',
            'structure'
        ));
    }

    /**
     * Update the salary structure for a specific employee.
     */
    public function update(UpdateSalaryStructureRequest $request, User $employee): RedirectResponse|JsonResponse
    {
        $structure = $this->salaryStructureService->updateEmployeeSalaryStructure($employee, $request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Salary structure updated successfully for :name.', ['name' => $employee->name]),
                'data' => $structure,
                'redirect' => route('payroll.salary-structure.index'),
            ]);
        }

        return redirect()->route('payroll.salary-structure.index')
            ->with('success', _trans('common.Salary structure updated successfully for :name.', ['name' => $employee->name]));
    }

    /**
     * Calculate live salary breakdown preview via AJAX.
     */
    public function calculatePreview(Request $request): JsonResponse
    {
        $basicSalary = (float) $request->input('basic_salary', 0);
        $components = $request->input('components', []);

        $breakdown = $this->salaryStructureService->calculatePreview($basicSalary, $components);

        return response()->json($breakdown);
    }
}
