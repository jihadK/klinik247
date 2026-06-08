<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Share $currentUser (dengan role + site) ke layout admin
        View::composer(['admin.layouts.app', 'admin.dashboard'], function ($view) {
            if (auth()->check()) {
                $view->with('currentUser', auth()->user()->load('role', 'site'));
            }
        });
    }
}
