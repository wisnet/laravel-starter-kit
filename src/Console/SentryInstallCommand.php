<?php


namespace Wisnet\LaravelStarterKit\Console;


class SentryInstallCommand extends InstallCommand
{

    const SIGNATURE = 'starter-kit:sentry';
    const HANDLER_FILE = 'Exceptions/Handler.php';
    const SENTRY_REPORT_SEARCH = 'public function report';
    const CLOSING_BRACKET = '}';
    const REPORT_PATH = __DIR__ . '/../report.txt';

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
        $this->addSentryReporting();
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

}