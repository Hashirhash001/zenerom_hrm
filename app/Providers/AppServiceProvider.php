<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // This composer will run every time the partials.menu view is rendered.
        View::composer('partials.menu', function ($view) {
            // Inject the session variables, or provide default values if not set.
            $view->with('uid', session('uid', null));
            $view->with('uname', session('uname', 'Guest'));
        });
    }
}
