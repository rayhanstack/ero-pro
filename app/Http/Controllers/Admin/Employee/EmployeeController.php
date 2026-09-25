<?php

namespace App\Http\Controllers\Admin\Employee;

use App\Enums\BloodGroupEnum;
use App\Enums\EmployeeStatusEnum;
use App\Enums\EmploymentTypeEnum;
use App\Enums\GenderEnum;
use App\Enums\MaritalStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\ChangeEmployeeStatusRequest;
use App\Http\Requests\Employee\StoreEmployeeDocumentRequest;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Models\City;
use App\Models\Country;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\Shift;
use App\Models\State;
use App\Services\Employee\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EmployeeController extends Controller
{
    public function __construct(
        protected EmployeeService $employeeService
    ) {}

    /**
     * Display a listing of employees.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'department_id', 'designation_id', 'status', 'employment_type', 'view']);
        $viewMode = $request->get('view', 'table');

        $employees = $this->employeeService->getPaginatedEmployees($filters, $viewMode === 'grid' ? 12 : 15);
        $stats = $this->employeeService->getEmployeeStats();
        $departments = Department::active()->orderBy('name')->get();
        $designations = ! empty($filters['department_id'])
            ? Designation::where('department_id', $filters['department_id'])->active()->orderBy('name')->get()
            : Designation::active()->orderBy('name')->get();

        $statuses = EmployeeStatusEnum::cases();
        $employmentTypes = EmploymentTypeEnum::cases();

        return view('admin.employees.index', compact(
            'employees',
            'stats',
            'departments',
            'designations',
            'statuses',
            'employmentTypes',
            'filters',
            'viewMode'
        ));
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create(): View
    {
        $departments = Department::active()->orderBy('name')->get();
        $designations = Designation::active()->orderBy('name')->get();
        $shifts = Shift::active()->orderBy('name')->get();
        $managers = Employee::active()->orderBy('first_name')->get();
        $countries = Country::orderBy('name')->get();
        $roles = Role::where('guard_name', 'web')->orderBy('name')->get();

        $genders = GenderEnum::cases();
        $maritalStatuses = MaritalStatusEnum::cases();
        $bloodGroups = BloodGroupEnum::cases();
        $employmentTypes = EmploymentTypeEnum::cases();
        $statuses = EmployeeStatusEnum::cases();

        return view('admin.employees.create', compact(
            'departments',
            'designations',
            'shifts',
            'managers',
            'countries',
            'roles',
            'genders',
            'maritalStatuses',
            'bloodGroups',
            'employmentTypes',
            'statuses'
        ));
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $employee = $this->employeeService->create($request->validated());

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', _trans('common.Employee created successfully.'));
    }

    /**
     * Display the specified employee profile.
     */
    public function show(Employee $employee): View
    {
        $employee->load([
            'department',
            'designation',
            'shift',
            'manager',
            'user.roles',
            'country',
            'state',
            'city',
            'bankAccounts',
            'emergencyContacts',
            'documents',
        ]);

        $statuses = EmployeeStatusEnum::cases();

        return view('admin.employees.show', compact('employee', 'statuses'));
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(Employee $employee): View
    {
        $employee->load(['primaryBankAccount', 'emergencyContacts']);

        $departments = Department::active()->orderBy('name')->get();
        $designations = $employee->department_id
            ? Designation::where('department_id', $employee->department_id)->active()->orderBy('name')->get()
            : Designation::active()->orderBy('name')->get();

        $shifts = Shift::active()->orderBy('name')->get();
        $managers = Employee::active()->where('id', '!=', $employee->id)->orderBy('first_name')->get();
        $countries = Country::orderBy('name')->get();
        $states = $employee->country_id ? State::where('country_id', $employee->country_id)->orderBy('name')->get() : collect();
        $cities = $employee->state_id ? City::where('state_id', $employee->state_id)->orderBy('name')->get() : collect();

        $genders = GenderEnum::cases();
        $maritalStatuses = MaritalStatusEnum::cases();
        $bloodGroups = BloodGroupEnum::cases();
        $employmentTypes = EmploymentTypeEnum::cases();
        $statuses = EmployeeStatusEnum::cases();

        return view('admin.employees.edit', compact(
            'employee',
            'departments',
            'designations',
            'shifts',
            'managers',
            'countries',
            'states',
            'cities',
            'genders',
            'maritalStatuses',
            'bloodGroups',
            'employmentTypes',
            'statuses'
        ));
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $this->employeeService->update($employee, $request->validated());

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', _trans('common.Employee updated successfully.'));
    }

    /**
     * Remove the specified employee from storage (Soft Delete).
     */
    public function destroy(Employee $employee): RedirectResponse
    {
        $this->employeeService->delete($employee);

        return redirect()
            ->route('employees.index')
            ->with('success', _trans('common.Employee deleted successfully.'));
    }

    /**
     * Restore a soft-deleted employee.
     */
    public function restore(int $id): RedirectResponse
    {
        $this->employeeService->restore($id);

        return redirect()
            ->route('employees.index')
            ->with('success', _trans('common.Employee restored successfully.'));
    }

    /**
     * Change employee status.
     */
    public function changeStatus(ChangeEmployeeStatusRequest $request, Employee $employee): RedirectResponse|JsonResponse
    {
        $this->employeeService->changeStatus($employee, $request->validated('status'));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Employee status updated successfully.'),
            ]);
        }

        return redirect()->back()->with('success', _trans('common.Employee status updated successfully.'));
    }

    /**
     * Upload and store a new document for the employee.
     */
    public function storeDocument(StoreEmployeeDocumentRequest $request, Employee $employee): RedirectResponse
    {
        $this->employeeService->addDocument($employee, $request->validated());

        return redirect()->back()->with('success', _trans('common.Document uploaded successfully.'));
    }

    /**
     * Remove a document from storage.
     */
    public function destroyDocument(EmployeeDocument $document): RedirectResponse
    {
        $this->employeeService->deleteDocument($document);

        return redirect()->back()->with('success', _trans('common.Document deleted successfully.'));
    }

    /**
     * Download an employee document.
     */
    public function downloadDocument(EmployeeDocument $document)
    {
        $data = json_decode($document->file, true);
        $filePath = is_array($data) ? ($data['file'] ?? null) : $document->file;
        $disk = is_array($data) ? ($data['disk'] ?? 'public') : 'public';

        if ($filePath && Storage::disk($disk)->exists($filePath)) {
            return Storage::disk($disk)->download($filePath, $document->title . '.' . pathinfo($filePath, PATHINFO_EXTENSION));
        }

        return redirect()->back()->with('error', _trans('common.File not found on server.'));
    }

    /**
     * AJAX endpoint to fetch designations by department.
     */
    public function getDesignationsByDepartment(Department $department): JsonResponse
    {
        $designations = Designation::where('department_id', $department->id)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'level']);

        return response()->json($designations);
    }
}
