<?php

namespace Peinhu\AetherUpload;

use Illuminate\Support\ServiceProvider;
use Peinhu\AetherUpload\Console\BuildRedisHashesCommand;
use Peinhu\AetherUpload\Console\CleanUpDirCommand;
use Peinhu\AetherUpload\Console\PublishCommand;

class AetherUploadServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../views', 'aetherupload');

        $this->publishes([
            __DIR__ . '/../config/aetherupload.php'   => config_path('aetherupload.php'),
            __DIR__ . '/../assets/aetherupload.js'    => public_path('js/aetherupload.js'),
            __DIR__ . '/../assets/spark-md5.min.js'    => public_path('js/spark-md5.min.js'),
            __DIR__ . '/../uploads/aetherupload_file' => storage_path('app/aetherupload/file'),
            __DIR__ . '/../uploads/aetherupload_head' => storage_path('app/aetherupload/_head'),
        ], 'aetherupload');

        if ( ! $this->app->routesAreCached() ) {
            require __DIR__ . '/../routes/routes.php';
        }
    }


    public function register()
    {
        $this->app->singleton(
            'command.aetherupload.publish',
            function () {
                return new PublishCommand();
            }
        );

        $this->app->singleton(
            'command.aetherupload.build',
            function () {
                return new BuildRedisHashesCommand();
            }
        );

        $this->app->singleton(
            'command.aetherupload.clean',
            function () {
                return new CleanUpDirCommand();
            }
        );

        $this->commands('command.aetherupload.publish','command.aetherupload.build','command.aetherupload.clean');
    }


}
