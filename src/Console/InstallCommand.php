<?php

namespace Wisnet\LaravelStarterKit\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;

class InstallCommand extends Command
{

    public $envFileExists;

    const VIEWS_DIR = 'views';
    const LAYOUTS_DIR = 'layouts';
    const PASSWORDS_DIR = 'auth/passwords';
    const SASS_DIR = 'sass';
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

    public function __construct()
    {
        parent::__construct();

        $this->checkForEnvFile();

        $this->filesystem = new Filesystem();
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        // Telescope
        $this->call('starter-kit:telescope');

        // Fortify
        $this->call('starter-kit:fortify');

        // Dusk
        $this->info('Installing Dusk');
        $this->call('dusk:install');

        // Sentry
        $this->call('starter-kit:sentry');

        // Migrations organizer
        $this->info('Organizing migrations');
        $this->call('migrate:organise');

        // Views
        $this->call('starter-kit:views');

        // Front-end assets
        $this->call('starter-kit:assets');

        $this->info('Updating package.json');
        $this->addNodePackages();
        $this->info('package.json updated');

        $this->info('Updating webpack.mix.js');
        $this->updateWebpack();
        $this->info('webpack.mix.js updated');

        $this->info('Starter Kit installed successfully!');
        $this->warn('Run "npm install && mix" to compile resources');
    }

    public function checkForEnvFile()
    {
        $envFile = app()->environmentFilePath();

        $this->envFileExists = !empty(file_get_contents($envFile));
    }

    public function displayEnvFileNotFoundMessage(string $signature)
    {
        $this->info('.env file not found, please generate it before running the ' . $signature . ' command.');
    }

    public function checkDirectories(string $directory)
    {
        $this->filesystem->ensureDirectoryExists(resource_path($directory));
        foreach (self::DIRECTORIES[$directory] as $path) {
            $this->filesystem->ensureDirectoryExists(sprintf('%s/%s', resource_path($directory), $path));
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