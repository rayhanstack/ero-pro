<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreCategoryRequest;
use App\Http\Requests\Finance\UpdateCategoryRequest;
use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use App\Services\Finance\FinanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        protected FinanceService $financeService
    ) {}

    /**
     * Display a listing of income and expense categories.
     */
    public function index(Request $request): View
    {
        $activeTab = $request->get('tab', 'income');
        $incomeCategories = IncomeCategory::withCount('transactions')->orderBy('name')->get();
        $expenseCategories = ExpenseCategory::withCount('transactions')->orderBy('name')->get();
        $statuses = StatusEnum::cases();

        $title = _trans('common.Finance Categories');

        return view('admin.finance.categories.index', compact(
            'title',
            'incomeCategories',
            'expenseCategories',
            'statuses',
            'activeTab'
        ));
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse|JsonResponse
    {
        $category = $this->financeService->createCategory($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Category created successfully.'),
                'data' => $category,
            ]);
        }

        $type = $request->input('type', 'income');

        return redirect()->route('finance.categories.index', ['tab' => $type])
            ->with('success', _trans('common.Category created successfully.'));
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, int $id): RedirectResponse|JsonResponse
    {
        $type = $request->input('type', 'income');
        $category = $this->financeService->updateCategory($id, $type, $request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Category updated successfully.'),
                'data' => $category,
            ]);
        }

        return redirect()->route('finance.categories.index', ['tab' => $type])
            ->with('success', _trans('common.Category updated successfully.'));
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $type = $request->input('type', 'income');
        $this->financeService->deleteCategory($id, $type);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Category deleted successfully.'),
            ]);
        }

        return redirect()->route('finance.categories.index', ['tab' => $type])
            ->with('success', _trans('common.Category deleted successfully.'));
    }
}
