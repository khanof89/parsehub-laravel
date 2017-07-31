<?php

namespace Shahrukh\Parsehub;

use Illuminate\Support\ServiceProvider;

class ParsehubServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('Shahrukh\Parsehub\Parsehub');
    }
}