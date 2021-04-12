<?php


namespace Wisnet\LaravelStarterKit\Console;


class SentryInstallCommand extends InstallCommand
{

    const SIGNATURE = 'starter-kit:sentry';
    const HANDLER_FILE = 'Exceptions/Handler.php';

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
    protected $description = 'Installs Sentry';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Adding Sentry reporting to application\'s error handler');
        $this->publishHandler();
    }

    private function publishHandler()
    {
        $handler = app_path(self::HANDLER_FILE);

        copy(
            __DIR__ . '/../resources/sentry/Handler.stub',
            $handler
        );
    }

}