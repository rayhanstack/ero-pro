<?php

namespace App\Http\Controllers\Admin\Designation;

use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Designation\StoreDesignationRequest;
use App\Http\Requests\Designation\UpdateDesignationRequest;
use App\Models\Designation;
use App\Services\Department\DepartmentService;
use App\Services\Designation\DesignationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DesignationController extends Controller
{
    public function __construct(
        protected DesignationService $designationService,
        protected DepartmentService $departmentService
    ) {}

    /**
     * Display a listing of designations.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'department_id', 'status']);
        $designations = $this->designationService->getPaginatedDesignations($filters);
        $departments = $this->departmentService->getAllActiveDepartments();
        $statuses = StatusEnum::cases();

        return view('admin.designations.index', compact('designations', 'departments', 'filters', 'statuses'));
    }

    /**
     * Show the form for creating a new designation.
     */
    public function create(): View
    {
        $departments = $this->departmentService->getAllActiveDepartments();
        $statuses = StatusEnum::cases();

        return view('admin.designations.create', compact('departments', 'statuses'));
    }

    /**
     * Store a newly created designation.
     */
    public function store(StoreDesignationRequest $request): RedirectResponse
    {
        $this->designationService->createDesignation($request->validated());

        return redirect()
            ->route('designations.index')
            ->with('success', _trans('common.Designation created successfully.'));
    }

    /**
     * Show the form for editing the designation.
     */
    public function edit(Designation $designation): View
    {
        $departments = $this->departmentService->getAllActiveDepartments();
        $statuses = StatusEnum::cases();

        return view('admin.designations.edit', compact('designation', 'departments', 'statuses'));
    }

    /**
     * Update the specified designation.
     */
    public function update(UpdateDesignationRequest $request, Designation $designation): RedirectResponse
    {
        $this->designationService->updateDesignation($designation, $request->validated());

        return redirect()
            ->route('designations.index')
            ->with('success', _trans('common.Designation updated successfully.'));
    }

    /**
     * Remove the specified designation.
     */
    public function destroy(Designation $designation): RedirectResponse
    {
        $this->designationService->deleteDesignation($designation);

        return redirect()
            ->route('designations.index')
            ->with('success', _trans('common.Designation deleted successfully.'));
    }
}
