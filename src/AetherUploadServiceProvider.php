<?php

namespace Peinhu\AetherUpload;

use Illuminate\Support\ServiceProvider;

class AetherUploadServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../views', 'aetherupload');

        $this->publishes([
            __DIR__.'/../config/aetherupload.php' => config_path('aetherupload.php'),
            __DIR__.'/../assets/aetherupload.js' => public_path('js/aetherupload.js'),
            __DIR__.'/../views/example.blade.php' => base_path('resources/views/vendor/aetherupload/example.blade.php'),
            __DIR__.'/../uploads/aetherupload_file' => storage_path('app/uploads/aetherupload_file'),
            __DIR__.'/../uploads/aetherupload_head' => storage_path('app/uploads/aetherupload_head'),
        ],'aetherupload');

        if (!$this->app->routesAreCached()) {
            require __DIR__ . '/../routes.php';
        }
    }


    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/aetherupload.php', 'aetherupload'
        );

    }

}
