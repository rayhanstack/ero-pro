<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Display a listing of the users.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'role', 'status']);
        $users = $this->userService->getPaginatedUsers($filters);
        $roles = Role::where('guard_name', 'web')->orderBy('name', 'asc')->get();

        return view('admin.users.index', compact('users', 'roles', 'filters'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $roles = Role::where('guard_name', 'web')->orderBy('name', 'asc')->get();

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->userService->createUser($request->validated());

        return redirect()
            ->route('users.index')
            ->with('success', _trans('common.User created successfully.'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $roles = Role::where('guard_name', 'web')->orderBy('name', 'asc')->get();
        $userRole = $user->roles->first()?->name;

        return view('admin.users.edit', compact('user', 'roles', 'userRole'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->userService->updateUser($user, $request->validated());

        return redirect()
            ->route('users.index')
            ->with('success', _trans('common.User updated successfully.'));
    }

    /**
     * Toggle user active/inactive status.
     */
    public function toggleStatus(Request $request, User $user): JsonResponse|RedirectResponse
    {
        try {
            $this->userService->toggleStatus($user);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'status' => $user->status,
                    'message' => _trans('common.User status updated successfully.'),
                ]);
            }

            return redirect()
                ->back()
                ->with('success', _trans('common.User status updated successfully.'));
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        try {
            $this->userService->deleteUser($user);

            return redirect()
                ->route('users.index')
                ->with('success', _trans('common.User deleted successfully.'));
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('users.index')
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect()
                ->route('users.index')
                ->with('error', _trans('common.Failed to delete user.'));
        }
    }
}
