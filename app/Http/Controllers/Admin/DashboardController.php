<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $dashboardData = $this->dashboardService->getDashboardData($user);

        return view('admin.dashboard', array_merge([
            'title' => _trans('common.Dashboard'),
        ], $dashboardData));
    }

    public function components()
    {
        $data['title'] = _trans('common.UI Components');
        $data['countries'] = Country::orderBy('name')->pluck('name', 'id')->toArray();

        return view('admin.components')->with($data);
    }
}

