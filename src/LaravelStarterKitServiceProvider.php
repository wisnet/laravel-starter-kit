<?php

namespace Wisnet\LaravelStarterKit;

use Illuminate\Support\ServiceProvider;
use Wisnet\LaravelStarterKit\Console\InstallCommand;

class LaravelStarterKitServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->commands([InstallCommand::class]);
    }
}