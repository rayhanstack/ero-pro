<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define modules and their permissions
        $modules = [
            'dashboard' => ['view'],
            'employee' => ['view', 'create', 'edit', 'delete'],
            'department' => ['view', 'create', 'edit', 'delete'],
            'designation' => ['view', 'create', 'edit', 'delete'],
            'shift' => ['view', 'create', 'edit', 'delete'],
            'weekend' => ['view', 'create', 'edit', 'delete'],
            'holiday' => ['view', 'create', 'edit', 'delete'],
            'attendance' => ['view', 'create', 'edit', 'delete', 'manage'],
            'leave' => ['view', 'create', 'edit', 'delete', 'approve', 'manage'],
            'client' => ['view', 'create', 'edit', 'delete'],
            'project' => ['view', 'create', 'edit', 'delete'],
            'team' => ['view', 'create', 'edit', 'delete'],
            'task' => ['view', 'create', 'edit', 'delete'],
            'meeting' => ['view', 'create', 'edit', 'delete', 'record_minutes'],
            'payroll' => ['view', 'create', 'edit', 'delete', 'process'],
            'finance' => ['view', 'create', 'edit', 'delete'],
            'report' => ['view', 'create', 'edit', 'delete'],
            'ai' => ['view', 'create', 'edit', 'delete'],
            'setting' => ['view', 'create', 'edit', 'delete'],
            'role' => ['view', 'create', 'edit', 'delete'],
            'user' => ['view', 'create', 'edit', 'delete'],
        ];

        $allPermissions = [];
        $permissionRecords = [];
        $now = now();

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $permissionName = "{$module}.{$action}";
                $allPermissions[] = $permissionName;
                $permissionRecords[] = [
                    'name' => $permissionName,
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Bulk insert permissions if not exist
        foreach ($permissionRecords as $record) {
            Permission::firstOrCreate(
                ['name' => $record['name'], 'guard_name' => $record['guard_name']],
                $record
            );
        }

        // Create Roles
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $hrRole = Role::firstOrCreate(['name' => 'HR', 'guard_name' => 'web']);
        $managerRole = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $employeeRole = Role::firstOrCreate(['name' => 'Employee', 'guard_name' => 'web']);

        // Assign all permissions to Super Admin and Admin
        $allDbPermissions = Permission::where('guard_name', 'web')->get();
        $superAdminRole->syncPermissions($allDbPermissions);
        $adminRole->syncPermissions($allDbPermissions);

        // HR Permissions
        $hrPermissions = Permission::where('guard_name', 'web')->where(function ($query) {
            $query->where('name', 'like', 'dashboard.%')
                ->orWhere('name', 'like', 'employee.%')
                ->orWhere('name', 'like', 'department.%')
                ->orWhere('name', 'like', 'designation.%')
                ->orWhere('name', 'like', 'shift.%')
                ->orWhere('name', 'like', 'weekend.%')
                ->orWhere('name', 'like', 'holiday.%')
                ->orWhere('name', 'like', 'attendance.%')
                ->orWhere('name', 'like', 'leave.%')
                ->orWhere('name', 'like', 'meeting.%')
                ->orWhere('name', 'like', 'payroll.%')
                ->orWhere('name', 'like', 'report.view')
                ->orWhere('name', 'like', 'user.view')
                ->orWhere('name', 'like', 'role.view');
        })->get();
        $hrRole->syncPermissions($hrPermissions);

        // Manager Permissions
        $managerPermissions = Permission::where('guard_name', 'web')->where(function ($query) {
            $query->where('name', 'like', 'dashboard.%')
                ->orWhere('name', 'like', 'project.%')
                ->orWhere('name', 'like', 'task.%')
                ->orWhere('name', 'like', 'team.%')
                ->orWhere('name', 'like', 'client.%')
                ->orWhere('name', 'like', 'meeting.%')
                ->orWhere('name', 'like', 'attendance.view')
                ->orWhere('name', 'like', 'leave.view')
                ->orWhere('name', 'like', 'leave.create')
                ->orWhere('name', 'like', 'leave.approve')
                ->orWhere('name', 'like', 'report.view')
                ->orWhere('name', 'like', 'employee.view')
                ->orWhere('name', 'like', 'user.view');
        })->get();
        $managerRole->syncPermissions($managerPermissions);

        // Employee Permissions
        $employeePermissions = Permission::where('guard_name', 'web')->whereIn('name', [
            'dashboard.view',
            'task.view',
            'attendance.view',
            'leave.view',
            'leave.create',
            'meeting.view',
            'project.view',
            'payroll.view',
        ])->get();
        $employeeRole->syncPermissions($employeePermissions);
    }
}
