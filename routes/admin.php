<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\Setting\SettingController;
use Illuminate\Support\Facades\Route;

Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'dashboard')->name('dashboard');
    });

Route::controller(SettingController::class)->group(function () {
        Route::get('/settings', 'index')->name('settings');
    });