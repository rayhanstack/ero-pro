<?php

namespace App\Http\Controllers\Admin\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Services\Role\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(
        protected RoleService $roleService
    ) {}

    /**
     * Display a listing of roles.
     */
    public function index(): View
    {
        $roles = $this->roleService->getPaginatedRoles();

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role with permission matrix.
     */
    public function create(): View
    {
        $groupedPermissions = $this->roleService->getGroupedPermissions();

        return view('admin.roles.create', compact('groupedPermissions'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->roleService->createRole($request->validated());

        return redirect()
            ->route('roles.index')
            ->with('success', _trans('common.Role created successfully.'));
    }

    /**
     * Show the form for editing the specified role and its permission matrix.
     */
    public function edit(Role $role): View
    {
        $groupedPermissions = $this->roleService->getGroupedPermissions();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('admin.roles.edit', compact('role', 'groupedPermissions', 'rolePermissions'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->roleService->updateRole($role, $request->validated());

        return redirect()
            ->route('roles.index')
            ->with('success', _trans('common.Role updated successfully.'));
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role): RedirectResponse
    {
        try {
            $this->roleService->deleteRole($role);

            return redirect()
                ->route('roles.index')
                ->with('success', _trans('common.Role deleted successfully.'));
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('roles.index')
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect()
                ->route('roles.index')
                ->with('error', _trans('common.Failed to delete role.'));
        }
    }
}
