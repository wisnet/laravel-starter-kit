<?php


namespace Wisnet\LaravelStarterKit\Console;


class WebpackUpdateCommand extends InstallCommand
{

    const SIGNATURE = 'starter-kit:webpack';

    const WEBPACK = 'webpack.mix.js';

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
    protected $description = 'Updates webpack.mix.js';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Updating webpack.mix.js');
        $this->updateWebpack();
        $this->info('webpack.mix.js updated');
    }

    private function updateWebpack()
    {
        copy(
            __DIR__ . '/../' . self::WEBPACK,
            base_path(self::WEBPACK)
        );
    }

}