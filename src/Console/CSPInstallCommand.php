<?php


namespace Wisnet\LaravelStarterKit\Console;


use Illuminate\Support\Str;

class CSPInstallCommand extends InstallCommand
{

    const SIGNATURE = 'starter-kit:csp';

    const CSP_KEY = 'CSP_ENABLED=';

    const POLICIES_DIRECTORY = 'Policies';

    const CONFIG_FILENAME = 'csp.php';
    const CONFIG_SEARCH = "'policy' => Spatie\Csp\Policies\Basic::class,";
    const CONFIG_REPLACE = "'policy' => \App\Policies\CSPPolicy::class,";

    const KERNEL_FILE = 'Http/Kernel.php';
    const HANDLER_FILE = 'Exceptions/Handler.php';
    const POLICY_FILE = 'Policies/CSPPolicy.php';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = self::SIGNATURE;

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Installs CSP dependency, publishes its dependencies and adds the necessary security headers';

    protected $views = [
        'app.stub' => 'layouts/app.blade.php',
    ];

    protected $publishAssets = [
        'CSPPolicy.stub' => self::POLICY_FILE,
        'Handler.stub' => self::HANDLER_FILE,
        'Kernel.stub' => self::KERNEL_FILE,
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
            $this->publishCSPViews();
            $this->publishAssets();

            $this->info('Adding CSP key to the .env file');
            $this->addCspToEnvFile();
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

    private function publishCSPViews()
    {
        foreach ($this->views as $key => $value) {
            $view = sprintf('%s/%s', resource_path(self::VIEWS_DIR), $value);

            copy(
                __DIR__ . '/../resources/csp/views/' . $key,
                $view
            );
        }
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

    private function publishAssets()
    {
        foreach ($this->publishAssets as $stub => $asset)
        {
            copy(
                __DIR__ . '/../resources/csp/' . $stub,
                app_path($asset)
            );
        }
    }

}