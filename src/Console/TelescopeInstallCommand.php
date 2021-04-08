<?php


namespace Wisnet\LaravelStarterKit\Console;

class TelescopeInstallCommand extends InstallCommand
{

    const SIGNATURE = 'starter-kit:telescope';
    const TELESCOPE_KEY = 'TELESCOPE_ENABLED=';

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
    protected $description = 'Installs Telescope and publishes its assets';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if ($this->envFileExists) {
            $this->info('Adding Telescope key to the .env file');
            $this->addTelescopeToEnvFile();

            $this->info('Installing Telescope');
            $this->call('telescope:install');
            $this->info('Publishing Telescope migrations');
            $this->call('vendor:publish', ['--tag' => 'telescope-migrations']);
        } else {
            $this->displayEnvFileNotFoundMessage(self::SIGNATURE);
        }
    }

    private function addTelescopeToEnvFile()
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);
        $keyPos = strpos($str, self::TELESCOPE_KEY);

        if ($keyPos === false) {
            $str .= PHP_EOL . self::TELESCOPE_KEY . 'true' . PHP_EOL;
        }

        file_put_contents($envFile, $str);
    }

}