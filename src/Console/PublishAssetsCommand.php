<?php


namespace Wisnet\LaravelStarterKit\Console;


class PublishAssetsCommand extends InstallCommand
{

    const SIGNATURE = 'starter-kit:assets';

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
    protected $description = 'Publishes CSS and JS assets';

    protected $sassFiles = [
        'abstracts/_abstracts.scss',
        'abstracts/_colors.scss',
        'abstracts/_functions.scss',
        'abstracts/_mixins.scss',
        'abstracts/_typography.scss',
        'base/_base.scss',
        'base/_buttons.scss',
        'base/_form-elements.scss',
        'base/_headings.scss',
        'base/_links.scss',
        'components/_components.scss',
        'layout/_dashboard.scss',
        'layout/_footer-main.scss',
        'layout/_header-main.scss',
        'layout/_layout.scss',
        'modules/_modules.scss',
        'pages/_pages.scss',
        'app.scss',
    ];

    protected $jsFiles = [
        'app.js',
        'bootstrap.js',
        'components/ExampleComponent.vue'
    ];

    public function __construct()
    {
        parent::__construct();

        foreach ([self::SASS_DIR, self::JS_DIR] as $directory) {
            $this->checkDirectories($directory);
        }
    }

    public function handle()
    {
        $this->info('Publishing front-end assets');
        $this->publishSassAssets();
        $this->publishJsAssets();
    }

    private function publishSassAssets()
    {
        foreach ($this->sassFiles as $key => $value) {
            $file = sprintf('%s/%s', resource_path(self::SASS_DIR), $value);

            copy(
                __DIR__ . '/../resources/sass/' . $value,
                $file
            );
        }
    }

    private function publishJsAssets()
    {
        foreach ($this->jsFiles as $key => $value) {
            $file = sprintf('%s/%s', resource_path(self::JS_DIR), $value);

            copy(
                __DIR__ . '/../resources/js/' . $value,
                $file
            );
        }
    }

}