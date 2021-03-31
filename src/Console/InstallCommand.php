<?php

namespace Wisnet\LaravelStarterKit\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class InstallCommand extends Command
{

    const TELESCOPE_KEY = 'TELESCOPE_ENABLED=';
    const HANDLER_FILE = 'Exceptions/Handler.php';
    const FORTIFY_PROVIDER = 'Providers/FortifyServiceProvider.php';
    const SENTRY_REPORT_SEARCH = 'public function report';
    const FORTIFY_BOOT_SEARCH = 'Fortify::resetUserPasswordsUsing(ResetUserPassword::class);';
    const CLOSING_BRACKET = '}';
    const REPORT_PATH = __DIR__ . '/../report.txt';
    const FORTIFY_PATH = __DIR__ . '/../fortify.txt';
    const LAYOUTS_DIR = 'layouts';
    const PASSWORDS_DIR = 'auth/passwords';
    const SASS_DIR = 'sass';
    const VIEWS_DIR = 'views';
    const JS_DIR = 'js';
    const COMPONENTS_DIR = 'components';
    const SASS_ABSTRACTS = 'abstracts';
    const SASS_BASE = 'base';
    const SASS_COMPONENTS = 'components';
    const SASS_LAYOUT = 'layout';
    const SASS_MODULES = 'modules';
    const SASS_PAGES = 'pages';
    const DEV_DEPENDENCIES = 'devDependencies';
    const DEPENDENCIES = 'dependencies';
    const SCRIPTS = 'scripts';
    const PACKAGE = 'package.json';
    const WEBPACK = 'webpack.mix.js';

    const DIRECTORIES = [
        self::VIEWS_DIR => [
            self::LAYOUTS_DIR,
            self::PASSWORDS_DIR,
        ],
        self::SASS_DIR => [
            self::SASS_ABSTRACTS,
            self::SASS_BASE,
            self::SASS_COMPONENTS,
            self::SASS_LAYOUT,
            self::SASS_MODULES,
            self::SASS_PAGES
        ],
        self::JS_DIR => [
            self::COMPONENTS_DIR
        ]
    ];
    const SCRIPTS_EXCEPTIONS = [
        'development',
        'watch',
        'watch-poll',
        'hot',
        'production'
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'starter-kit:install {--force : Overwrite existing views by default}';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Installs the starter kit';

    protected $views = [
        'auth/login.stub' => 'auth/login.blade.php',
        'auth/passwords/confirm.stub' => 'auth/passwords/confirm.blade.php',
        'auth/passwords/email.stub' => 'auth/passwords/email.blade.php',
        'auth/passwords/reset.stub' => 'auth/passwords/reset.blade.php',
        'auth/register.stub' => 'auth/register.blade.php',
        'auth/verify.stub' => 'auth/verify.blade.php',
        'home.stub' => 'home.blade.php',
        'layouts/app.stub' => 'layouts/app.blade.php',
    ];

    protected $sassFiles = [
        'abstracts/_abstracts.scss',
        'abstracts/_colors.scss',
        'abstracts/_functions.scss',
        'abstracts/_mixins.scss',
        'abstracts/_typography.scss',
        'base/_base.scss',
        'base/_buttons.scss',
        'base/_form-elements.scss',
        'base/_headings.scss',
        'base/_links.scss',
        'components/_components.scss',
        'layout/_dashboard.scss',
        'layout/_footer-main.scss',
        'layout/_header-main.scss',
        'layout/_layout.scss',
        'modules/_modules.scss',
        'pages/_pages.scss',
        'app.scss',
    ];

    protected $jsFiles = [
        'app.js',
        'bootstrap.js',
        'components/ExampleComponent.vue'
    ];

    protected $devDependencies = [
        '@vue/compiler-sfc' => '^3.0.2',
        'axios' => '^0.21',
        'bootstrap' => '^4.5.3',
        'cross-env' => '^7.0',
        'jquery' => '^3.5.1',
        'laravel-mix' => '^6.0.13',
        'laravel-mix-eslint-config' => '^0.1.7',
        'lodash' => '^4.17.19',
        'postcss' => '^8.1.14',
        'resolve-url-loader' => '^3.1.0',
        'sass' => '^1.29.0',
        'sass-loader' => '^11.0.1',
        'vue' => '^3.0.2',
        'vue-loader' => '^16.2.0',
        'eslint' => '^7.9.0',
        'eslint-loader' => '^4.0.2',
        'eslint-plugin-vue' => '^7.8.0',
        'stylelint' => '^13.6.1',
        'stylelint-config-standard' => '^21.0.0',
        'stylelint-order' => '^4.1.0',
        'stylelint-scss' => '^3.18.0'
    ];

    protected $scripts = [
        'development' => 'mix',
        'watch' => 'mix watch',
        'watch-poll' => 'mix watch -- --watch-options-poll=1000',
        'hot' => 'mix watch --hot',
        'production' => 'mix --production'
    ];

    protected $filesystem;

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        // Back-end processes
        $this->info('Adding Telescope key to the .env file');
        $this->addTelescopeToEnvFile();

        $this->info('Installing Telescope');
        $this->call('telescope:install');
        $this->info('Publishing Telescope migrations');
        $this->call('vendor:publish', ['--tag' => 'telescope-migrations']);

        $this->info('Publishing Fortify assets');
        $this->call('vendor:publish', ['--provider' => 'Laravel\Fortify\FortifyServiceProvider']);
        $this->publishFortifyServiceProvider();

        $this->info('Installing Dusk');
        $this->call('dusk:install');

        $this->info('Organizing migrations');
        $this->call('migrate:organise');

        $this->checkDirectories();
        $this->info('Publishing views');
        $this->publishViews();

        $this->info('Registering views with Fortify');
        $this->registerViews();

        $this->info('Adding Sentry reporting to application\'s error handler');
        $this->addSentryReporting();

        // Front-end processes
        $this->info('Publishing front-end assets');
        $this->publishSassAssets();
        $this->publishJsAssets();

        $this->info('Updating package.json');
        $this->addNodePackages();
        $this->info('package.json updated');

        $this->info('Updating webpack.mix.js');
        $this->updateWebpack();
        $this->info('webpack.mix.js updated');

        $this->info('Starter Kit installed successfully!');
        $this->warn('Run "npm install && mix" to compile resources');
    }

    private function addTelescopeToEnvFile()
    {
        $envFile = app()->environmentFilePath();
        try {
            $str = file_get_contents($envFile);
            $keyPos = strpos($str, self::TELESCOPE_KEY);

            if ($keyPos === false) {
                $str .= PHP_EOL . self::TELESCOPE_KEY . 'true' . PHP_EOL;
            }

            file_put_contents($envFile, $str);
        } catch (\ErrorException $e) {
            $this->info('.env file not found, please generate it before running install command.');
        }
    }

    private function addSentryReporting()
    {
        $handlerFile = app_path(self::HANDLER_FILE);
        $str = file_get_contents($handlerFile);
        $fPos = strpos($str, self::SENTRY_REPORT_SEARCH);

        if ($fPos === false) {
            $closerPos = strpos($str, self::CLOSING_BRACKET, -1);
            $fn = file_get_contents(self::REPORT_PATH) . PHP_EOL . self::CLOSING_BRACKET;

            $str = substr_replace($str, $fn, $closerPos - 2);

            file_put_contents($handlerFile, $str);
        }
    }

    private function publishFortifyServiceProvider()
    {
        $namespace = Str::replaceLast('\\', '', $this->laravel->getNamespace());

        $appConfig = file_get_contents(config_path('app.php'));

        if (Str::contains($appConfig, $namespace . '\\Providers\\FortifyServiceProvider::class')) {
            return;
        }

        $lineEndingCount = [
            "\r\n" => substr_count($appConfig, "\r\n"),
            "\r" => substr_count($appConfig, "\r"),
            "\n" => substr_count($appConfig, "\n"),
        ];

        $eol = array_keys($lineEndingCount, max($lineEndingCount))[0];

        file_put_contents(config_path('app.php'), str_replace(
            "{$namespace}\\Providers\RouteServiceProvider::class,".$eol,
            "{$namespace}\\Providers\RouteServiceProvider::class,".$eol."        {$namespace}\Providers\FortifyServiceProvider::class,".$eol,
            $appConfig
        ));
    }

    private function checkDirectories()
    {
        $this->filesystem = new Filesystem();
        foreach (self::DIRECTORIES as $topDir => $dirs) {
            $this->filesystem->ensureDirectoryExists(resource_path($topDir));
            foreach ($dirs as $dir => $path) {
                $this->filesystem->ensureDirectoryExists(sprintf('%s/%s', resource_path($topDir), $path));
            }
        }
    }

    private function publishViews()
    {
        foreach ($this->views as $key => $value) {
            $view = sprintf('%s/%s', resource_path(self::VIEWS_DIR), $value);
            if (file_exists($view) && !$this->option('force')) {
                if (!$this->confirm("The [{$value}] view already exists. Do you want to replace it?")) {
                    continue;
                }
            }

            copy(
                __DIR__ . '/../resources/views/' . $key,
                $view
            );
        }
    }

    private function registerViews()
    {
        $provider = app_path(self::FORTIFY_PROVIDER);
        $str = file_get_contents($provider);
        $lPos = strpos($str, self::FORTIFY_BOOT_SEARCH);

        $txt = file_get_contents(self::FORTIFY_PATH);
        $str = substr_replace($str, $txt, $lPos + strlen(self::FORTIFY_BOOT_SEARCH));
        file_put_contents($provider, $str);
    }

    private function publishSassAssets()
    {
        foreach ($this->sassFiles as $key => $value) {
            $file = sprintf('%s/%s', resource_path(self::SASS_DIR), $value);

            copy(
                __DIR__ . '/../resources/sass/' . $value,
                $file
            );
        }
    }

    private function publishJsAssets()
    {
        foreach ($this->jsFiles as $key => $value) {
            $file = sprintf('%s/%s', resource_path(self::JS_DIR), $value);

            copy(
                __DIR__ . '/../resources/js/' . $value,
                $file
            );
        }
    }

    private function addNodePackages()
    {
        try {
            $file = $this->filesystem->get(base_path(self::PACKAGE));

            $packages = json_decode($file, true);
            $devDependencies = array_key_exists(
                self::DEV_DEPENDENCIES,
                $packages
            ) ? $packages[self::DEV_DEPENDENCIES] : [];
            $scripts = array_key_exists(self::SCRIPTS, $packages) ? $packages[self::SCRIPTS] : [];

            $packages[self::DEV_DEPENDENCIES] = $this->devDependencies + Arr::except($devDependencies, ['laravel-mix']);
            $packages[self::SCRIPTS] = $this->scripts + Arr::except($scripts, self::SCRIPTS_EXCEPTIONS);

            ksort($packages[self::DEV_DEPENDENCIES]);

            $this->filesystem->put(
                base_path(self::PACKAGE),
                json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL
            );
        } catch (FileNotFoundException $e) {
            $this->makePackageJson();
        }
    }

    private function makePackageJson()
    {
        $this->info('No package.json found, creating one');

        copy(
            __DIR__ . '/../' . self::PACKAGE,
            base_path(self::PACKAGE)
        );
    }

    private function updateWebpack()
    {
        copy(
            __DIR__ . '/../' . self::WEBPACK,
            base_path(self::WEBPACK)
        );
    }
}