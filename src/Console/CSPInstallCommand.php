<?php


namespace Wisnet\LaravelStarterKit\Console;


use Illuminate\Support\Str;

class CSPInstallCommand extends InstallCommand
{

    const SIGNATURE = 'starter-kit:csp';
    const CSP_KEY = 'CSP_ENABLED=';
    const MIDDLEWARE_SEARCH = 'protected $middleware = [';
    const KERNEL_FILE = 'Http/Kernel.php';
    const CLOSING_BRACKET = ']';
    const CSP_CLASS = '\Spatie\Csp\AddCspHeaders::class,';
    const POLICIES_DIRECTORY = 'Policies';
    const CONFIG_FILENAME = 'csp.php';
    const STUB_PATH = '/../resources/csp/CSPPolicy.stub';
    const POLICY_PATH = 'Policies/CSPPolicy.php';
    const CONFIG_SEARCH = "'policy' => Spatie\Csp\Policies\Basic::class,";
    const CONFIG_REPLACE = "'policy' => \App\Policies\CSPPolicy::class,";
    const HANDLER_FILE = 'Exceptions/Handler.php';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = self::SIGNATURE . ' {--force : Overwrite existing assets by default}';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Installs CSP dependency, published its dependencies and adds the necessary security headers';

    protected $views = [
        'csp/views/app.stub' => 'layouts/app.blade.php',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->filesystem->ensureDirectoryExists(app_path(self::POLICIES_DIRECTORY));
        $this->checkDirectories(self::VIEWS_DIR);
    }

    public function handle()
    {
        if ($this->envFileExists) {
            $this->info('Publishing CSP assets');
            $this->call('vendor:publish', ['--provider' => 'Spatie\Csp\CspServiceProvider', '--tag' => 'config']);
            $this->publishCSPConfig();
            $this->publishCSPPolicy();
            $this->publishCSPViews();
            $this->publishHandler();

            $this->info('Adding CSP key to the .env file');
            $this->addCspToEnvFile();

            $this->info('Registering CSP headers in application\'s Kernel');
            $this->registerCSPHeadersInKernel();
        } else {
            $this->displayEnvFileNotFoundMessage(self::SIGNATURE);
        }
    }

    private function publishCSPConfig()
    {
        $cspConfig = file_get_contents(config_path(self::CONFIG_FILENAME));

        file_put_contents(
            config_path(self::CONFIG_FILENAME),
            str_replace(
                self::CONFIG_SEARCH,
                self::CONFIG_REPLACE,
                $cspConfig
            )
        );
    }

    private function publishCSPPolicy()
    {
        copy(
            __DIR__ . self::STUB_PATH,
            app_path(self::POLICY_PATH)
        );
    }

    private function publishCSPViews()
    {
        foreach ($this->views as $key => $value) {
            $view = sprintf('%s/%s', resource_path(self::VIEWS_DIR), $value);
            if (file_exists($view) && !$this->option('force')) {
                if (!$this->confirm("The [{$value}] view already exists. Do you want to replace it?")) {
                    continue;
                }
            }

            copy(
                __DIR__ . '/../resources/csp/views/' . $key,
                $view
            );
        }
    }

    private function publishHandler()
    {
        $handler = app_path(self::HANDLER_FILE);
        if (file_exists($handler) && !$this->option('force')) {
            if (!$this->confirm('Handler.php already exists. Do you want to replace it?')) {
                return;
            }
        }

        copy(
            __DIR__ . '/../resources/csp/Handler.stub',
            $handler
        );
    }

    private function addCspToEnvFile()
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);
        $keyPos = strpos($str, self::CSP_KEY);

        if ($keyPos === false) {
            $str .= PHP_EOL . self::CSP_KEY . 'true' . PHP_EOL;
        }

        file_put_contents($envFile, $str);
    }

    private function registerCSPHeadersInKernel()
    {
        $kernelFile = app_path(self::KERNEL_FILE);
        $str = file_get_contents($kernelFile);

        if (Str::contains($str, self::CSP_CLASS)) {
            return;
        }

        $middlewarePos = strpos($str, self::MIDDLEWARE_SEARCH);

        if ($middlewarePos !== false) {
            $kernelHalf = explode(self::MIDDLEWARE_SEARCH, $str)[1];
            $closingPos = strpos($kernelHalf, self::CLOSING_BRACKET);

            $str = substr_replace($str, self::CSP_CLASS, $closingPos - 1);

            file_put_contents($kernelFile, $str);
        }
    }

}