<?php

namespace App\Http\Controllers\Admin\Holiday;

use App\Enums\HolidayTypeEnum;
use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Holiday\StoreHolidayRequest;
use App\Http\Requests\Holiday\UpdateHolidayRequest;
use App\Models\Holiday;
use App\Services\Holiday\HolidayService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HolidayController extends Controller
{
    public function __construct(
        protected HolidayService $holidayService
    ) {}

    /**
     * Display listing of holidays with year filter and month calendar view.
     */
    public function index(Request $request): View
    {
        $currentYear = (int) $request->input('year', date('Y'));
        $currentMonth = (int) $request->input('month', date('n'));
        $viewMode = $request->input('view', 'list'); // 'list' or 'calendar'

        $filters = [
            'search' => $request->input('search'),
            'year' => $currentYear,
            'type' => $request->input('type'),
            'status' => $request->input('status'),
        ];

        $holidays = $this->holidayService->getPaginatedHolidays($filters);
        $monthHolidays = $this->holidayService->getHolidaysForMonth($currentYear, $currentMonth);

        $types = HolidayTypeEnum::cases();
        $statuses = StatusEnum::cases();

        // Available years for dropdown
        $availableYears = range(date('Y') - 2, date('Y') + 3);

        return view('admin.holidays.index', compact(
            'holidays',
            'monthHolidays',
            'filters',
            'types',
            'statuses',
            'currentYear',
            'currentMonth',
            'viewMode',
            'availableYears'
        ));
    }

    /**
     * Show form for creating a new holiday.
     */
    public function create(): View
    {
        $types = HolidayTypeEnum::cases();
        $statuses = StatusEnum::cases();

        return view('admin.holidays.create', compact('types', 'statuses'));
    }

    /**
     * Store a newly created holiday.
     */
    public function store(StoreHolidayRequest $request): RedirectResponse
    {
        $this->holidayService->createHoliday($request->validated());

        return redirect()
            ->route('holidays.index', ['year' => Carbon::parse($request->from_date)->year])
            ->with('success', _trans('common.Holiday created successfully.'));
    }

    /**
     * Show form for editing a holiday.
     */
    public function edit(Holiday $holiday): View
    {
        $types = HolidayTypeEnum::cases();
        $statuses = StatusEnum::cases();

        return view('admin.holidays.edit', compact('holiday', 'types', 'statuses'));
    }

    /**
     * Update the specified holiday.
     */
    public function update(UpdateHolidayRequest $request, Holiday $holiday): RedirectResponse
    {
        $this->holidayService->updateHoliday($holiday, $request->validated());

        return redirect()
            ->route('holidays.index', ['year' => Carbon::parse($request->from_date)->year])
            ->with('success', _trans('common.Holiday updated successfully.'));
    }

    /**
     * Remove the specified holiday.
     */
    public function destroy(Holiday $holiday): RedirectResponse
    {
        $this->holidayService->deleteHoliday($holiday);

        return redirect()
            ->route('holidays.index')
            ->with('success', _trans('common.Holiday deleted successfully.'));
    }
}
