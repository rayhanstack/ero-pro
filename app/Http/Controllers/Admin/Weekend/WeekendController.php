<?php

namespace App\Http\Controllers\Admin\Weekend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Weekend\UpdateWeekendRequest;
use App\Services\Weekend\WeekendService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WeekendController extends Controller
{
    public function __construct(
        protected WeekendService $weekendService
    ) {}

    /**
     * Display weekend configuration single page form.
     */
    public function index(): View
    {
        $weekends = $this->weekendService->getAllWeekends();

        return view('admin.weekends.index', compact('weekends'));
    }

    /**
     * Update all weekend days at once.
     */
    public function update(UpdateWeekendRequest $request): RedirectResponse
    {
        $selectedDays = $request->input('weekends', []);
        $this->weekendService->updateWeekends($selectedDays);

        return redirect()
            ->route('weekends.index')
            ->with('success', _trans('common.Weekend settings updated successfully.'));
    }
}
