<?php

namespace Wisnet\LaravelStarterKit\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{

    const TELESCOPE_KEY = 'TELESCOPE_ENABLED=';
    const HANDLER_FILE = 'Exceptions/Handler.php';
    const SENTRY_REPORT_SEARCH = 'public function report';
    const CLOSING_BRACKET = '}';
    const REPORT_PATH = __DIR__ . '/report.txt';

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
    protected $description = 'Install packages in the starter kit';

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
            $fn = file_get_contents(base_path(self::REPORT_PATH)) . PHP_EOL . self::CLOSING_BRACKET;

            $str = substr_replace($str, $fn, $closerPos - 2);

            file_put_contents($handlerFile, $str);
        }
    }
}