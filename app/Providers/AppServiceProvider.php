<?php

namespace App\Providers;

use App\Events\PayslipPaid;
use App\Listeners\CreateExpenseFromPayslip;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        Event::listen(
            PayslipPaid::class,
            CreateExpenseFromPayslip::class,
        );
    }
}
