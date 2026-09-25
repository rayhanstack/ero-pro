<?php

namespace App\Http\Controllers\Admin\Department;

use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Models\Department;
use App\Services\Department\DepartmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function __construct(
        protected DepartmentService $departmentService
    ) {}

    /**
     * Display a listing of departments.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status']);
        $departments = $this->departmentService->getPaginatedDepartments($filters);
        $statuses = StatusEnum::cases();

        return view('admin.departments.index', compact('departments', 'filters', 'statuses'));
    }

    /**
     * Show the form for creating a new department.
     */
    public function create(): View
    {
        $statuses = StatusEnum::cases();

        return view('admin.departments.create', compact('statuses'));
    }

    /**
     * Store a newly created department.
     */
    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $this->departmentService->createDepartment($request->validated());

        return redirect()
            ->route('departments.index')
            ->with('success', _trans('common.Department created successfully.'));
    }

    /**
     * Show the form for editing the department.
     */
    public function edit(Department $department): View
    {
        $statuses = StatusEnum::cases();

        return view('admin.departments.edit', compact('department', 'statuses'));
    }

    /**
     * Update the specified department.
     */
    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $this->departmentService->updateDepartment($department, $request->validated());

        return redirect()
            ->route('departments.index')
            ->with('success', _trans('common.Department updated successfully.'));
    }

    /**
     * Remove the specified department.
     */
    public function destroy(Department $department): RedirectResponse
    {
        $this->departmentService->deleteDepartment($department);

        return redirect()
            ->route('departments.index')
            ->with('success', _trans('common.Department deleted successfully.'));
    }
}
