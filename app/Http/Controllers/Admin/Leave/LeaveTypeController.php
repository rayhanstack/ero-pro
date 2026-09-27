<?php

namespace App\Http\Controllers\Admin\Leave;

use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Leave\StoreLeaveTypeRequest;
use App\Http\Requests\Leave\UpdateLeaveTypeRequest;
use App\Models\LeaveType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of leave types.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $query = LeaveType::latest();

        if ($search) {
            $query->search($search);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $leaveTypes = $query->paginate(15)->withQueryString();

        return view('admin.leaves.types.index', compact('leaveTypes', 'search', 'status'));
    }

    /**
     * Show the form for creating a new leave type.
     */
    public function create(): View
    {
        $statuses = StatusEnum::cases();

        return view('admin.leaves.types.create', compact('statuses'));
    }

    /**
     * Store a newly created leave type in storage.
     */
    public function store(StoreLeaveTypeRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['is_paid'] = (bool) ($request->input('is_paid', false));
        $data['carry_forward'] = (bool) ($request->input('carry_forward', false));
        $data['max_carry'] = $data['carry_forward'] ? ($data['max_carry'] ?? 0) : 0;
        $data['color'] = $data['color'] ?: '#4f46e5';

        $leaveType = LeaveType::create($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Leave type created successfully.'),
                'data' => $leaveType,
            ]);
        }

        return redirect()->route('leave-types.index')->with('success', _trans('common.Leave type created successfully.'));
    }

    /**
     * Show the form for editing the specified leave type.
     */
    public function edit(LeaveType $leaveType): View
    {
        $statuses = StatusEnum::cases();

        return view('admin.leaves.types.edit', compact('leaveType', 'statuses'));
    }

    /**
     * Update the specified leave type in storage.
     */
    public function update(UpdateLeaveTypeRequest $request, LeaveType $leaveType): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['is_paid'] = (bool) ($request->input('is_paid', false));
        $data['carry_forward'] = (bool) ($request->input('carry_forward', false));
        $data['max_carry'] = $data['carry_forward'] ? ($data['max_carry'] ?? 0) : 0;
        $data['color'] = $data['color'] ?: '#4f46e5';

        $leaveType->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Leave type updated successfully.'),
                'data' => $leaveType,
            ]);
        }

        return redirect()->route('leave-types.index')->with('success', _trans('common.Leave type updated successfully.'));
    }

    /**
     * Remove the specified leave type from storage (Soft Delete).
     */
    public function destroy(LeaveType $leaveType): RedirectResponse|JsonResponse
    {
        $leaveType->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Leave type deleted successfully.'),
            ]);
        }

        return redirect()->route('leave-types.index')->with('success', _trans('common.Leave type deleted successfully.'));
    }
}
