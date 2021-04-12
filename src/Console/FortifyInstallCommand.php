<?php


namespace Wisnet\LaravelStarterKit\Console;


use Illuminate\Support\Str;

class FortifyInstallCommand extends InstallCommand
{

    const SIGNATURE = 'starter-kit:fortify';
    const FORTIFY_PROVIDER = 'Providers/FortifyServiceProvider.php';
    const FORTIFY_BOOT_SEARCH = 'Fortify::resetUserPasswordsUsing(ResetUserPassword::class);';
    const FORTIFY_PATH = __DIR__ . '/../fortify.txt';

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
    protected $description = 'Installs Fortify and publishes its assets';

    protected $views = [
        'auth/login.stub' => 'auth/login.blade.php',
        'auth/passwords/confirm.stub' => 'auth/passwords/confirm.blade.php',
        'auth/passwords/email.stub' => 'auth/passwords/email.blade.php',
        'auth/passwords/reset.stub' => 'auth/passwords/reset.blade.php',
        'auth/register.stub' => 'auth/register.blade.php',
        'auth/verify.stub' => 'auth/verify.blade.php',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Publishing Fortify assets');
        $this->call('vendor:publish', ['--provider' => 'Laravel\Fortify\FortifyServiceProvider']);
        $this->publishFortifyServiceProvider();

        $this->info('Publishing and registering views with Fortify');
        $this->publishFortifyViews();
        $this->registerFortifyViews();
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

        file_put_contents(
            config_path('app.php'),
            str_replace(
                "{$namespace}\\Providers\RouteServiceProvider::class," . $eol,
                "{$namespace}\\Providers\RouteServiceProvider::class," . $eol . "        {$namespace}\Providers\FortifyServiceProvider::class," . $eol,
                $appConfig
            )
        );
    }

    private function publishFortifyViews()
    {
        foreach ($this->views as $key => $value) {
            $view = sprintf('%s/%s', resource_path(self::VIEWS_DIR), $value);

            copy(
                __DIR__ . '/../resources/views/' . $key,
                $view
            );
        }
    }

    private function registerFortifyViews()
    {
        $provider = app_path(self::FORTIFY_PROVIDER);
        $str = file_get_contents($provider);
        $lPos = strpos($str, self::FORTIFY_BOOT_SEARCH);

        $txt = file_get_contents(self::FORTIFY_PATH);
        $str = substr_replace($str, $txt, $lPos + strlen(self::FORTIFY_BOOT_SEARCH));
        file_put_contents($provider, $str);
    }

}