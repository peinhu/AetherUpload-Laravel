<?php

namespace AetherUpload;

class ConfigMapper
{
    private static $_instance = null;
    private $root_dir;
    private $resource_subdir_rule;
    private $chunk_size;
    private $resource_maxsize;
    private $resource_extensions;
    private $group;
    private $group_dir;
    private $middleware_preprocess;
    private $middleware_uploading;
    private $middleware_display;
    private $middleware_download;
    private $event_before_upload_complete;
    private $event_upload_complete;
    private $header_storage_disk;
    private $distributed_deployment_enable;
    private $distributed_deployment_role;
    private $distributed_deployment_middleware_cors;
    private $distributed_deployment_storage_host;
    private $distributed_deployment_allow_origin;
    private $forbidden_extensions;
    private $instant_completion;
    private $route_preprocess;
    private $route_uploading;
    private $route_display;
    private $route_download;
    private $lax_mode;

    private function __construct()
    {
        //disallow new instance
    }

    public static function instance()
    {
        if ( self::$_instance === null ) {
            self::$_instance = (new self())->applyCommonConfig();
        }

        return self::$_instance;
    }

    private function applyCommonConfig()
    {
        $config = app('config');
        $this->root_dir = $config->get('aetherupload.root_dir');
        $this->chunk_size = $config->get('aetherupload.chunk_size');
        $this->resource_subdir_rule = $config->get('aetherupload.resource_subdir_rule');
        $this->header_storage_disk = $config->get('aetherupload.header_storage_disk');
        $this->distributed_deployment_enable = $config->get('aetherupload.distributed_deployment.enable');
        $this->distributed_deployment_role = $config->get('aetherupload.distributed_deployment.role');
        $this->distributed_deployment_storage_host = $config->get('aetherupload.distributed_deployment.web.storage_host');
        $this->distributed_deployment_middleware_cors = $config->get('aetherupload.distributed_deployment.storage.middleware_cors');
        $this->distributed_deployment_allow_origin = $config->get('aetherupload.distributed_deployment.storage.allow_origin');
        $this->forbidden_extensions = $config->get('aetherupload.forbidden_extensions');
        $this->middleware_preprocess = $config->get('aetherupload.middleware_preprocess');
        $this->middleware_uploading = $config->get('aetherupload.middleware_uploading');
        $this->middleware_display = $config->get('aetherupload.middleware_display');
        $this->middleware_download = $config->get('aetherupload.middleware_download');
        $this->instant_completion = $config->get('aetherupload.instant_completion');
        $this->route_preprocess = $config->get('aetherupload.route_preprocess');
        $this->route_uploading = $config->get('aetherupload.route_uploading');
        $this->route_display = $config->get('aetherupload.route_display');
        $this->route_download = $config->get('aetherupload.route_download');
        $this->lax_mode = $config->get('aetherupload.lax_mode');

        return $this;
    }

    public function applyGroupConfig($group)
    {
        $config = app('config');

        if ( ! in_array($group, array_keys($config->get('aetherupload.groups'))) ) {
            throw new \Exception(trans('aetherupload::messages.invalid_operation'));
        }

        $this->group = $group;
        $this->group_dir = $config->get('aetherupload.groups.' . $group . '.group_dir');
        $this->resource_maxsize = $config->get('aetherupload.groups.' . $group . '.resource_maxsize');
        $this->resource_extensions = $config->get('aetherupload.groups.' . $group . '.resource_extensions');
        $this->event_before_upload_complete = $config->get('aetherupload.groups.' . $group . '.event_before_upload_complete');
        $this->event_upload_complete = $config->get('aetherupload.groups.' . $group . '.event_upload_complete');

        return $this;
    }

    public static function get($property)
    {
        return self::instance()->{$property};
    }

    public static function set($property, $value)
    {
        self::instance()->{$property} = $value;
    }

}