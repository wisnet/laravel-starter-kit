<?php


namespace Wisnet\LaravelStarterKit\Console;


use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;

class NodePackagesInstallCommand extends InstallCommand
{

    const SIGNATURE = 'starter-kit:node';

    const PACKAGE = 'package.json';
    const DEV_DEPENDENCIES = 'devDependencies';
    const DEPENDENCIES = 'dependencies';
    const SCRIPTS = 'scripts';

    const SCRIPTS_EXCEPTIONS = [
        'development',
        'watch',
        'watch-poll',
        'hot',
        'production'
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
    protected $description = 'Updates or creates package.json';

    protected $devDependencies = [
        '@vue/compiler-sfc' => '^3.0.2',
        'axios' => '^0.21',
        'bootstrap' => '^4.5.3',
        'cross-env' => '^7.0',
        'jquery' => '^3.5.1',
        'laravel-mix' => '^6.0.13',
        'laravel-mix-eslint-config' => '^0.1.7',
        'lodash' => '^4.17.19',
        'postcss' => '^8.1.14',
        'resolve-url-loader' => '^3.1.0',
        'sass' => '^1.29.0',
        'sass-loader' => '^11.0.1',
        'vue' => '^3.0.2',
        'vue-loader' => '^16.2.0',
        'eslint' => '^7.9.0',
        'eslint-loader' => '^4.0.2',
        'eslint-plugin-vue' => '^7.8.0',
        'stylelint' => '^13.6.1',
        'stylelint-config-standard' => '^21.0.0',
        'stylelint-order' => '^4.1.0',
        'stylelint-scss' => '^3.18.0'
    ];

    protected $scripts = [
        'development' => 'mix',
        'watch' => 'mix watch',
        'watch-poll' => 'mix watch -- --watch-options-poll=1000',
        'hot' => 'mix watch --hot',
        'production' => 'mix --production'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Updating package.json');
        $this->addNodePackages();
        $this->info('package.json updated');
    }

    private function addNodePackages()
    {
        try {
            $file = $this->filesystem->get(base_path(self::PACKAGE));

            $packages = json_decode($file, true);
            $devDependencies = array_key_exists(
                self::DEV_DEPENDENCIES,
                $packages
            ) ? $packages[self::DEV_DEPENDENCIES] : [];
            $scripts = array_key_exists(self::SCRIPTS, $packages) ? $packages[self::SCRIPTS] : [];

            $packages[self::DEV_DEPENDENCIES] = $this->devDependencies + Arr::except($devDependencies, ['laravel-mix']);
            $packages[self::SCRIPTS] = $this->scripts + Arr::except($scripts, self::SCRIPTS_EXCEPTIONS);

            ksort($packages[self::DEV_DEPENDENCIES]);

            $this->filesystem->put(
                base_path(self::PACKAGE),
                json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL
            );
        } catch (FileNotFoundException $e) {
            $this->makePackageJson();
        }
    }

    private function makePackageJson()
    {
        $this->info('No package.json found, creating one');

        copy(
            __DIR__ . '/../' . self::PACKAGE,
            base_path(self::PACKAGE)
        );
    }

}