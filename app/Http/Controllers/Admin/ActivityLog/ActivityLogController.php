<?php

namespace App\Http\Controllers\Admin\ActivityLog;

use App\Http\Controllers\Controller;
use App\Services\ActivityLog\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function __construct(
        protected ActivityLogService $activityLogService
    ) {}

    /**
     * Display a listing of system activity logs.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['user_id', 'module', 'action', 'date_from', 'date_to']);
        $logs = $this->activityLogService->getPaginatedLogs($filters);
        $users = $this->activityLogService->getUsersList();
        $actionTypes = $this->activityLogService->getActionTypes();

        return view('admin.activity-logs.index', compact('logs', 'users', 'actionTypes', 'filters'));
    }
}
