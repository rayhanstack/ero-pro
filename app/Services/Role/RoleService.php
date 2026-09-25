<?php

namespace App\Services\Role;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleService
{
    /**
     * Get paginated roles with counts.
     */
    public function getPaginatedRoles(int $perPage = 15): LengthAwarePaginator
    {
        return Role::withCount(['users', 'permissions'])
            ->orderBy('id', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get all permissions grouped by module.
     *
     * @return array<string, Collection>
     */
    public function getGroupedPermissions(): array
    {
        $permissions = Permission::where('guard_name', 'web')->orderBy('name', 'asc')->get();

        $grouped = [];
        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name, 2);
            $module = $parts[0] ?? 'general';
            $grouped[$module][] = $permission;
        }

        return $grouped;
    }

    /**
     * Create a new role with permissions.
     */
    public function createRole(array $data): Role
    {
        return DB::transaction(function () use ($data) {
            $role = Role::create([
                'name' => $data['name'],
                'guard_name' => 'web',
            ]);

            if (! empty($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            return $role;
        });
    }

    /**
     * Update role and sync permissions.
     */
    public function updateRole(Role $role, array $data): Role
    {
        return DB::transaction(function () use ($role, $data) {
            $role->update([
                'name' => $data['name'],
            ]);

            $permissions = $data['permissions'] ?? [];
            $role->syncPermissions($permissions);

            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            return $role;
        });
    }

    /**
     * Delete role if allowed.
     */
    public function deleteRole(Role $role): bool
    {
        if ($role->name === 'Super Admin') {
            throw new \InvalidArgumentException(_trans('common.Super Admin role cannot be deleted.'));
        }

        return DB::transaction(function () use ($role) {
            $role->syncPermissions([]);
            $deleted = $role->delete();

            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            return (bool) $deleted;
        });
    }
}
