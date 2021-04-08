<?php


namespace Wisnet\LaravelStarterKit\Console;


use Illuminate\Filesystem\Filesystem;

class PublishViewsCommand extends InstallCommand
{
    const SIGNATURE = 'starter-kit:views';
    const VIEWS_DIR = 'views';
    const LAYOUTS_DIR = 'layouts';
    const PASSWORDS_DIR = 'auth/passwords';

    const DIRECTORIES = [
        self::VIEWS_DIR => [
            self::LAYOUTS_DIR,
            self::PASSWORDS_DIR,
        ],
    ];

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
    protected $description = 'Publishes views';

    protected $views = [
        'home.stub' => 'home.blade.php',
        'layouts/app.stub' => 'layouts/app.blade.php',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Publishing views');
        $this->checkDirectories();
        $this->publishViews();
    }

    private function checkDirectories()
    {
        $this->filesystem = new Filesystem();
        foreach (self::DIRECTORIES as $topDir => $dirs) {
            $this->filesystem->ensureDirectoryExists(resource_path($topDir));
            foreach ($dirs as $dir => $path) {
                $this->filesystem->ensureDirectoryExists(sprintf('%s/%s', resource_path($topDir), $path));
            }
        }
    }

    private function publishViews()
    {
        foreach ($this->views as $key => $value) {
            $view = sprintf('%s/%s', resource_path(self::VIEWS_DIR), $value);
            if (file_exists($view) && !$this->option('force')) {
                if (!$this->confirm("The [{$value}] view already exists. Do you want to replace it?")) {
                    continue;
                }
            }

            copy(
                __DIR__ . '/../resources/views/' . $key,
                $view
            );
        }
    }

}