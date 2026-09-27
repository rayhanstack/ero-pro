<?php

namespace App\Http\Controllers\Admin\Payroll;

use App\Enums\SalaryComponentCalcTypeEnum;
use App\Enums\SalaryComponentStatusEnum;
use App\Enums\SalaryComponentTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\StoreSalaryComponentRequest;
use App\Http\Requests\Payroll\UpdateSalaryComponentRequest;
use App\Models\SalaryComponent;
use App\Services\Payroll\SalaryComponentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalaryComponentController extends Controller
{
    public function __construct(
        protected SalaryComponentService $componentService
    ) {}

    /**
     * Display a listing of salary components.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'type', 'calc_type', 'status']);

        $components = $this->componentService->getPaginatedComponents($filters, 15);
        $stats = $this->componentService->getStats();
        $types = SalaryComponentTypeEnum::cases();
        $calcTypes = SalaryComponentCalcTypeEnum::cases();
        $statuses = SalaryComponentStatusEnum::cases();

        $title = _trans('common.Salary Components');

        return view('admin.payroll.components.index', compact(
            'title',
            'components',
            'stats',
            'filters',
            'types',
            'calcTypes',
            'statuses'
        ));
    }

    /**
     * Show the form for creating a new salary component.
     */
    public function create(): View
    {
        $title = _trans('common.Add Salary Component');
        $types = SalaryComponentTypeEnum::cases();
        $calcTypes = SalaryComponentCalcTypeEnum::cases();
        $statuses = SalaryComponentStatusEnum::cases();

        return view('admin.payroll.components.create', compact(
            'title',
            'types',
            'calcTypes',
            'statuses'
        ));
    }

    /**
     * Store a newly created salary component in storage.
     */
    public function store(StoreSalaryComponentRequest $request): RedirectResponse|JsonResponse
    {
        $component = $this->componentService->create($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Salary component created successfully.'),
                'data' => $component,
                'redirect' => route('payroll.components.index'),
            ]);
        }

        return redirect()->route('payroll.components.index')
            ->with('success', _trans('common.Salary component created successfully.'));
    }

    /**
     * Show the form for editing the specified salary component.
     */
    public function edit(SalaryComponent $salaryComponent): View
    {
        $title = _trans('common.Edit Salary Component') . ' — ' . $salaryComponent->name;
        $types = SalaryComponentTypeEnum::cases();
        $calcTypes = SalaryComponentCalcTypeEnum::cases();
        $statuses = SalaryComponentStatusEnum::cases();

        return view('admin.payroll.components.edit', compact(
            'title',
            'salaryComponent',
            'types',
            'calcTypes',
            'statuses'
        ));
    }

    /**
     * Update the specified salary component in storage.
     */
    public function update(UpdateSalaryComponentRequest $request, SalaryComponent $salaryComponent): RedirectResponse|JsonResponse
    {
        $this->componentService->update($salaryComponent, $request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Salary component updated successfully.'),
                'data' => $salaryComponent,
                'redirect' => route('payroll.components.index'),
            ]);
        }

        return redirect()->route('payroll.components.index')
            ->with('success', _trans('common.Salary component updated successfully.'));
    }

    /**
     * Remove the specified salary component from storage.
     */
    public function destroy(Request $request, SalaryComponent $salaryComponent): RedirectResponse|JsonResponse
    {
        $this->componentService->delete($salaryComponent);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Salary component deleted successfully.'),
            ]);
        }

        return redirect()->route('payroll.components.index')
            ->with('success', _trans('common.Salary component deleted successfully.'));
    }
}
