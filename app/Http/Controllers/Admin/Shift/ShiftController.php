<?php

namespace App\Http\Controllers\Admin\Shift;

use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shift\StoreShiftRequest;
use App\Http\Requests\Shift\UpdateShiftRequest;
use App\Models\Shift;
use App\Services\Shift\ShiftService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function __construct(
        protected ShiftService $shiftService
    ) {}

    /**
     * Display listing of shifts.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status']);
        $shifts = $this->shiftService->getPaginatedShifts($filters);
        $statuses = StatusEnum::cases();

        return view('admin.shifts.index', compact('shifts', 'filters', 'statuses'));
    }

    /**
     * Show form for creating a new shift.
     */
    public function create(): View
    {
        $statuses = StatusEnum::cases();

        return view('admin.shifts.create', compact('statuses'));
    }

    /**
     * Store a newly created shift.
     */
    public function store(StoreShiftRequest $request): RedirectResponse
    {
        $this->shiftService->createShift($request->validated());

        return redirect()
            ->route('shifts.index')
            ->with('success', _trans('common.Shift created successfully.'));
    }

    /**
     * Show form for editing a shift.
     */
    public function edit(Shift $shift): View
    {
        $statuses = StatusEnum::cases();

        return view('admin.shifts.edit', compact('shift', 'statuses'));
    }

    /**
     * Update the specified shift.
     */
    public function update(UpdateShiftRequest $request, Shift $shift): RedirectResponse
    {
        $this->shiftService->updateShift($shift, $request->validated());

        return redirect()
            ->route('shifts.index')
            ->with('success', _trans('common.Shift updated successfully.'));
    }

    /**
     * Remove the specified shift.
     */
    public function destroy(Shift $shift): RedirectResponse
    {
        $this->shiftService->deleteShift($shift);

        return redirect()
            ->route('shifts.index')
            ->with('success', _trans('common.Shift deleted successfully.'));
    }
}
