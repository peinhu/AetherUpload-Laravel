<?php

namespace AetherUpload;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use AetherUpload\Console\BuildRedisHashesCommand;
use AetherUpload\Console\CleanUpDirectoryCommand;
use AetherUpload\Console\CreateGroupDirectoryCommand;
use AetherUpload\Console\PublishCommand;
use League\Flysystem\Filesystem;

class AetherUploadServiceProvider extends ServiceProvider
{

    protected $defer = false;

    public function boot()
    {

        $this->loadViewsFrom(__DIR__ . '/../views', 'aetherupload');

        $this->loadTranslationsFrom(__DIR__ . '/../translations', 'aetherupload');

        $this->publishes([
            __DIR__ . '/../config/aetherupload.php'         => config_path('aetherupload.php'),
            __DIR__ . '/../assets/aetherupload.js'          => public_path('vendor/aetherupload/js/aetherupload.js'),
            __DIR__ . '/../assets/spark-md5.min.js'         => public_path('vendor/aetherupload/js/spark-md5.min.js'),
            __DIR__ . '/../uploads/aetherupload_file'       => storage_path('app/aetherupload/file'),
            __DIR__ . '/../uploads/aetherupload_header'     => storage_path('app/aetherupload/_header'),
            __DIR__ . '/../translations/zh/messages.php'    => resource_path('lang/vendor/aetherupload/zh/messages.php'),
            __DIR__ . '/../translations/en/messages.php'    => resource_path('lang/vendor/aetherupload/en/messages.php'),
            __DIR__ . '/../middleware/AetherUploadCORS.php' => app_path('Http/Middleware/AetherUploadCORS.php'),
        ], 'aetherupload');

        if ( ! $this->app->routesAreCached() ) {
            require __DIR__ . '/../routes/routes.php';
        }

        Storage::extend('redis', function ($app, $config) {

            return new Filesystem(new RedisAdapter(new RedisClient()), $config);
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                PublishCommand::class,
                BuildRedisHashesCommand::class,
                CleanUpDirectoryCommand::class,
                CreateGroupDirectoryCommand::class,
            ]);
        }
    }

    public function register()
    {
        //
    }


}
