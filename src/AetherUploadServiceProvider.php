<?php

namespace AetherUpload;

use Illuminate\Support\ServiceProvider;
use AetherUpload\Console\BuildRedisHashesCommand;
use AetherUpload\Console\CleanUpDirectoryCommand;
use AetherUpload\Console\CreateGroupDirectoryCommand;
use AetherUpload\Console\PublishCommand;

class AetherUploadServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../views', 'aetherupload');

        $this->loadTranslationsFrom(__DIR__.'/../translations', 'aetherupload');

        $this->publishes([
            __DIR__ . '/../config/aetherupload.php'   => config_path('aetherupload.php'),
            __DIR__ . '/../assets/aetherupload.js'    => public_path('js/aetherupload.js'),
            __DIR__ . '/../assets/spark-md5.min.js'   => public_path('js/spark-md5.min.js'),
            __DIR__ . '/../uploads/aetherupload_file' => storage_path('app/aetherupload/file'),
            __DIR__ . '/../uploads/aetherupload_head' => storage_path('app/aetherupload/_head'),
            __DIR__.'/../translations/zh/messages.php' => resource_path('lang/vendor/aetherupload/zh/messages.php'),
            __DIR__.'/../translations/en/messages.php' => resource_path('lang/vendor/aetherupload/en/messages.php'),
        ], 'aetherupload');

        if ( ! $this->app->routesAreCached() ) {
            require __DIR__ . '/../routes/routes.php';
        }
    }

    public function register()
    {
        $this->app->bind(
            'command.aetherupload.publish',
            function () {
                return new PublishCommand();
            }
        );

        $this->app->bind(
            'command.aetherupload.build',
            function () {
                return new BuildRedisHashesCommand();
            }
        );

        $this->app->bind(
            'command.aetherupload.clean',
            function () {
                return new CleanUpDirectoryCommand();
            }
        );

        $this->app->bind(
            'command.aetherupload.groups',
            function () {
                return new CreateGroupDirectoryCommand();
            }
        );

        $this->commands('command.aetherupload.publish', 'command.aetherupload.build', 'command.aetherupload.clean', 'command.aetherupload.groups');

    }


}
