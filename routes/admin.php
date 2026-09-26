<?php

use App\Http\Controllers\Admin\ActivityLog\ActivityLogController;
use App\Http\Controllers\Admin\Ajax\LocationAjaxController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Language\LanguageController;
use App\Http\Controllers\Admin\Profile\ProfileController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\Role\RoleController;
use App\Http\Controllers\Admin\Setting\SettingController;
use App\Http\Controllers\Admin\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    // Dashboard & UI Components
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'dashboard')->name('dashboard')->middleware('can:dashboard.view');
        Route::get('/components', 'components')->name('components')->middleware('can:dashboard.view');
    });

    // Role Management & Permissions
    Route::controller(RoleController::class)->prefix('roles')->name('roles.')->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:role.view');
        Route::get('/create', 'create')->name('create')->middleware('can:role.create');
        Route::post('/', 'store')->name('store')->middleware('can:role.create');
        Route::get('/{role}/edit', 'edit')->name('edit')->middleware('can:role.edit');
        Route::put('/{role}', 'update')->name('update')->middleware('can:role.edit');
        Route::delete('/{role}', 'destroy')->name('destroy')->middleware('can:role.delete');
    });
    Route::controller(\App\Http\Controllers\Admin\Employee\EmployeeController::class)->prefix('employees')->name('employees.')->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:employee.view');
        Route::get('/create', 'create')->name('create')->middleware('can:employee.create');
        Route::post('/', 'store')->name('store')->middleware('can:employee.create');
        Route::get('/{employee}', 'show')->name('show')->middleware('can:employee.view');
        Route::get('/{employee}/edit', 'edit')->name('edit')->middleware('can:employee.edit');
        Route::put('/{employee}', 'update')->name('update')->middleware('can:employee.edit');
        Route::delete('/{employee}', 'destroy')->name('destroy')->middleware('can:employee.delete');
        Route::post('/{id}/restore', 'restore')->name('restore')->middleware('can:employee.delete');
        Route::patch('/{employee}/status', 'changeStatus')->name('status')->middleware('can:employee.edit');
        Route::post('/{employee}/documents', 'storeDocument')->name('documents.store')->middleware('can:employee.edit');
        Route::delete('/documents/{document}', 'destroyDocument')->name('documents.destroy')->middleware('can:employee.edit');
        Route::get('/documents/{document}/download', 'downloadDocument')->name('documents.download')->middleware('can:employee.view');
    });

    // Departments Management
    Route::controller(\App\Http\Controllers\Admin\Department\DepartmentController::class)->prefix('departments')->name('departments.')->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:department.view');
        Route::get('/create', 'create')->name('create')->middleware('can:department.create');
        Route::post('/', 'store')->name('store')->middleware('can:department.create');
        Route::get('/{department}/edit', 'edit')->name('edit')->middleware('can:department.edit');
        Route::put('/{department}', 'update')->name('update')->middleware('can:department.edit');
        Route::delete('/{department}', 'destroy')->name('destroy')->middleware('can:department.delete');
    });

    // Designations Management
    Route::controller(\App\Http\Controllers\Admin\Designation\DesignationController::class)->prefix('designations')->name('designations.')->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:designation.view');
        Route::get('/create', 'create')->name('create')->middleware('can:designation.create');
        Route::post('/', 'store')->name('store')->middleware('can:designation.create');
        Route::get('/{designation}/edit', 'edit')->name('edit')->middleware('can:designation.edit');
        Route::put('/{designation}', 'update')->name('update')->middleware('can:designation.edit');
        Route::delete('/{designation}', 'destroy')->name('destroy')->middleware('can:designation.delete');
    });

    // Shifts Management
    Route::controller(\App\Http\Controllers\Admin\Shift\ShiftController::class)->prefix('shifts')->name('shifts.')->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:shift.view');
        Route::get('/create', 'create')->name('create')->middleware('can:shift.create');
        Route::post('/', 'store')->name('store')->middleware('can:shift.create');
        Route::get('/{shift}/edit', 'edit')->name('edit')->middleware('can:shift.edit');
        Route::put('/{shift}', 'update')->name('update')->middleware('can:shift.edit');
        Route::delete('/{shift}', 'destroy')->name('destroy')->middleware('can:shift.delete');
    });

    // Weekends Management
    Route::controller(\App\Http\Controllers\Admin\Weekend\WeekendController::class)->prefix('weekends')->name('weekends.')->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:weekend.view');
        Route::put('/', 'update')->name('update')->middleware('can:weekend.edit');
    });

    // Holidays Management
    Route::controller(\App\Http\Controllers\Admin\Holiday\HolidayController::class)->prefix('holidays')->name('holidays.')->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:holiday.view');
        Route::get('/create', 'create')->name('create')->middleware('can:holiday.create');
        Route::post('/', 'store')->name('store')->middleware('can:holiday.create');
        Route::get('/{holiday}/edit', 'edit')->name('edit')->middleware('can:holiday.edit');
        Route::put('/{holiday}', 'update')->name('update')->middleware('can:holiday.edit');
        Route::delete('/{holiday}', 'destroy')->name('destroy')->middleware('can:holiday.delete');
    });

    // Attendance Management
    Route::controller(\App\Http\Controllers\Admin\Attendance\AttendanceController::class)->prefix('attendances')->name('attendances.')->group(function () {
        // Employee Self Service
        Route::get('/my', 'my')->name('my')->middleware('can:attendance.view');
        Route::post('/punch', 'punch')->name('punch')->middleware('can:attendance.view');
        Route::get('/punch-status', 'punchStatus')->name('punch-status')->middleware('can:attendance.view');

        // Management / HR Views
        Route::get('/daily', 'daily')->name('daily')->middleware('can:attendance.manage');
        Route::get('/monthly', 'monthly')->name('monthly')->middleware('can:attendance.manage');
        Route::post('/manual', 'store')->name('store')->middleware('can:attendance.manage');
        Route::put('/manual/{attendance}', 'update')->name('update')->middleware('can:attendance.manage');
        Route::delete('/{attendance}', 'destroy')->name('destroy')->middleware('can:attendance.manage');

        // Regularization Requests
        Route::get('/regularizations', 'regularizations')->name('regularizations')->middleware('can:attendance.view');
        Route::post('/regularizations', 'storeRegularization')->name('regularizations.store')->middleware('can:attendance.view');
        Route::patch('/regularizations/{regularization}/action', 'actionRegularization')->name('regularizations.action')->middleware('can:attendance.manage');
    });

    // Leave Management
    Route::controller(\App\Http\Controllers\Admin\Leave\LeaveController::class)->prefix('leaves')->name('leaves.')->group(function () {
        // Employee Self Service
        Route::get('/my', 'my')->name('my')->middleware('can:leave.view');
        Route::post('/apply', 'apply')->name('apply')->middleware('can:leave.create');
        Route::post('/calculate-days', 'calculateDaysAjax')->name('calculate-days')->middleware('can:leave.view');
        Route::delete('/{leave}/cancel', 'cancel')->name('cancel')->middleware('can:leave.view');

        // Management / HR Views
        Route::get('/requests', 'requests')->name('requests')->middleware('can:leave.approve');
        Route::patch('/requests/{leave}/action', 'action')->name('action')->middleware('can:leave.approve');
        Route::get('/balances', 'balances')->name('balances')->middleware('can:leave.view');
        Route::post('/balances/adjust', 'adjustBalance')->name('balances.adjust')->middleware('can:leave.manage');
        Route::get('/calendar', 'calendar')->name('calendar')->middleware('can:leave.view');
    });

    // Leave Types Management
    Route::controller(\App\Http\Controllers\Admin\Leave\LeaveTypeController::class)->prefix('leave-types')->name('leave-types.')->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:leave.manage');
        Route::get('/create', 'create')->name('create')->middleware('can:leave.manage');
        Route::post('/', 'store')->name('store')->middleware('can:leave.manage');
        Route::get('/{leave_type}/edit', 'edit')->name('edit')->middleware('can:leave.manage');
        Route::put('/{leave_type}', 'update')->name('update')->middleware('can:leave.manage');
        Route::delete('/{leave_type}', 'destroy')->name('destroy')->middleware('can:leave.manage');
    });

    // Profile & Password Settings
    Route::controller(ProfileController::class)->prefix('settings/profile')->name('profile.')->group(function () {
        Route::put('/', 'updateProfile')->name('update');
        Route::put('/password', 'updatePassword')->name('password');
    });

    // Settings
    Route::controller(SettingController::class)->prefix('settings')->group(function () {
        Route::get('/', 'index')->name('settings')->middleware('can:setting.view');
        Route::put('/company', 'updateCompany')->name('settings.company')->middleware('can:setting.edit');
        Route::put('/localization', 'updateLocalization')->name('settings.localization')->middleware('can:setting.edit');
        Route::put('/attendance', 'updateAttendance')->name('settings.attendance')->middleware('can:setting.edit');
        Route::put('/leave', 'updateLeave')->name('settings.leave')->middleware('can:setting.edit');
        Route::put('/payroll', 'updatePayroll')->name('settings.payroll')->middleware('can:setting.edit');
        Route::put('/mail', 'updateMail')->name('settings.mail')->middleware('can:setting.edit');
    });

    // Languages Management
    Route::controller(LanguageController::class)->prefix('languages')->name('languages.')->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:setting.view');
        Route::get('/create', 'create')->name('create')->middleware('can:setting.create');
        Route::post('/', 'store')->name('store')->middleware('can:setting.create');
        Route::get('/{language}/edit', 'edit')->name('edit')->middleware('can:setting.edit');
        Route::put('/{language}', 'update')->name('update')->middleware('can:setting.edit');
        Route::patch('/{language}/default', 'setDefault')->name('default')->middleware('can:setting.edit');
        Route::delete('/{language}', 'destroy')->name('destroy')->middleware('can:setting.delete');
    });

    // Activity Logs
    Route::controller(ActivityLogController::class)->prefix('activity-logs')->name('activity-logs.')->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:setting.view');
    });

    // Modules
    Route::controller(ProjectController::class)->group(function () {
        Route::get('/project', 'index')->name('project')->middleware('can:project.view');
    });

    // Clients Management
    Route::controller(\App\Http\Controllers\Admin\Client\ClientController::class)->prefix('clients')->name('clients.')->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:client.view');
        Route::get('/create', 'create')->name('create')->middleware('can:client.create');
        Route::post('/', 'store')->name('store')->middleware('can:client.create');
        Route::get('/{client}', 'show')->name('show')->middleware('can:client.view');
        Route::get('/{client}/edit', 'edit')->name('edit')->middleware('can:client.edit');
        Route::put('/{client}', 'update')->name('update')->middleware('can:client.edit');
        Route::delete('/{client}', 'destroy')->name('destroy')->middleware('can:client.delete');
        Route::post('/{client}/restore', 'restore')->name('restore')->middleware('can:client.delete');

        // Client Contacts
        Route::post('/{client}/contacts', 'addContact')->name('contacts.store')->middleware('can:client.edit');
        Route::delete('/contacts/{contact}', 'deleteContact')->name('contacts.destroy')->middleware('can:client.edit');

        // Client Notes
        Route::post('/{client}/notes', 'addNote')->name('notes.store')->middleware('can:client.edit');
        Route::delete('/notes/{note}', 'deleteNote')->name('notes.destroy')->middleware('can:client.edit');
    });

    // Legacy client route alias
    Route::get('/client', [\App\Http\Controllers\Admin\Client\ClientController::class, 'index'])->name('client')->middleware('can:client.view');

    Route::controller(TaskController::class)->group(function () {
        Route::get('/task', 'index')->name('task')->middleware('can:task.view');
    });

    // AJAX Location & Cascading Endpoints
    Route::prefix('admin/ajax')->name('admin.ajax.')->group(function () {
        Route::get('/states/{country}', [LocationAjaxController::class, 'getStates'])->name('states');
        Route::get('/cities/{state}', [LocationAjaxController::class, 'getCities'])->name('cities');
        Route::get('/designations/{department}', [\App\Http\Controllers\Admin\Employee\EmployeeController::class, 'getDesignationsByDepartment'])->name('designations');
    });

    Route::prefix('ajax')->name('ajax.')->group(function () {
        Route::get('/states/{country}', [LocationAjaxController::class, 'getStates'])->name('states');
        Route::get('/cities/{state}', [LocationAjaxController::class, 'getCities'])->name('cities');
        Route::get('/designations/{department}', [\App\Http\Controllers\Admin\Employee\EmployeeController::class, 'getDesignationsByDepartment'])->name('designations');
    });
});
