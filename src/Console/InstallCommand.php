<?php

namespace Wisnet\LaravelStarterKit\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

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

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'starter-kit:install';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Installs the starter kit';

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

        // Node
        $this->call('starter-kit:node');

        // Webpack
        $this->call('starter-kit:webpack');

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

}