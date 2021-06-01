<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MyDayNameTranslater extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        require_once app_path() . '/Http/Helper/MyDayNameTranslater.php';
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
