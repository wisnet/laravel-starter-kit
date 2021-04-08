<?php


namespace Wisnet\LaravelStarterKit\Console;


class PublishViewsCommand extends InstallCommand
{

    const SIGNATURE = 'starter-kit:views';

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

        $this->checkDirectories(self::VIEWS_DIR);
    }

    public function handle()
    {
        $this->info('Publishing views');
        $this->publishViews();
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