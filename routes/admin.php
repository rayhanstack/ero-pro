<?php

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\Setting\SettingController;
use App\Http\Controllers\Admin\TaskController;
use Illuminate\Support\Facades\Route;

Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'dashboard')->name('dashboard');
        Route::get('/components', 'components')->name('components');
    });

Route::controller(SettingController::class)->group(function () {
        Route::get('/settings', 'index')->name('settings');
    });
Route::controller(ProjectController::class)->group(function () {
        Route::get('/project', 'index')->name('project');
    });
Route::controller(ClientController::class)->group(function () {
        Route::get('/client', 'index')->name('client');
    });
Route::controller(TaskController::class)->group(function () {
        Route::get('/task', 'index')->name('task');
    });
