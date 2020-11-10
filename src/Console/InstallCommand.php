<?php

namespace Wisnet\LaravelStarterKit\Console;

use Illuminate\Console\Command;

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
    const DIRECTORIES = [
        self::VIEWS_DIR => [
            self::LAYOUTS_DIR,
            self::PASSWORDS_DIR,
        ],
        self::SASS_DIR => []
    ];

    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'starter-kit:install';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Installs the starter kit {--force : Overwrite existing views by default}';

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

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Adding Telescope key to the .env file');
        $this->addTelescopeToEnvFile();

        $this->info('Installing Telescope');
        $this->call('telescope:install');
        $this->info('Publishing Telescope migrations');
        $this->call('vendor:publish', ['--tag' => 'telescope-migrations']);

        $this->info('Publishing Fortify assets');
        $this->call('vendor:publish', ['--provider' => 'Laravel\Fortify\FortifyServiceProvider']);

        $this->info('Organizing migrations');
        $this->call('migrate:organise');

        $this->checkDirectories();
        $this->info('Publishing views');
        $this->publishViews();

        $this->info('Registering views with Fortify');
        $this->registerViews();

        $this->info('Adding Sentry reporting to application\'s error handler');
        $this->addSentryReporting();

        $this->info('You\'re all set! If you already have a DSN from Sentry make sure to run the following command:');
        $this->info('sentry:publish --dsn=your_DSN');
        $this->info('Additionally, uncomment the report method inside your application\'s exception handler.');
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

    private function checkDirectories()
    {
        foreach (self::DIRECTORIES as $topDir => $dirs) {
            if (!is_dir($directory = $this->buildPath($topDir))) {
                mkdir($directory, 0755, true);
            }
            foreach ($dirs as $dir => $path) {
                if (!is_dir($directory = $this->buildPath($topDir, $path))) {
                    mkdir($directory, 0755, true);
                }
            }
        }
    }

    private function buildPath(string $topDir, ?string $path = ''): string
    {
        return implode(
            DIRECTORY_SEPARATOR,
            [
                resource_path($topDir),
                $path,
            ]
        );
    }

    private function publishViews()
    {
        foreach ($this->views as $key => $value) {
            if (file_exists($view = $this->buildPath($value)) && !$this->option('force')) {
                if (!$this->confirm("The [{$value}] view already exists. Do you want to replace it?")) {
                    continue;
                }
            }

            copy(
                __DIR__ . resource_path(self::VIEWS_DIR) . $key,
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
}