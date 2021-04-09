<?php

namespace Wisnet\LaravelStarterKit;

use Illuminate\Support\ServiceProvider;
use Wisnet\LaravelStarterKit\Console\FortifyInstallCommand;
use Wisnet\LaravelStarterKit\Console\InstallCommand;
use Wisnet\LaravelStarterKit\Console\NodePackagesInstallCommand;
use Wisnet\LaravelStarterKit\Console\PublishAssetsCommand;
use Wisnet\LaravelStarterKit\Console\PublishViewsCommand;
use Wisnet\LaravelStarterKit\Console\SentryInstallCommand;
use Wisnet\LaravelStarterKit\Console\TelescopeInstallCommand;
use Wisnet\LaravelStarterKit\Console\WebpackUpdateCommand;

class LaravelStarterKitServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->commands(
            [
                InstallCommand::class,
                FortifyInstallCommand::class,
                NodePackagesInstallCommand::class,
                PublishAssetsCommand::class,
                PublishViewsCommand::class,
                SentryInstallCommand::class,
                TelescopeInstallCommand::class,
                WebpackUpdateCommand::class
            ]
        );
    }
}