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
    private $middleware_save_chunk;
    private $middleware_display;
    private $middleware_download;
    private $event_before_upload_complete;
    private $event_upload_complete;
    private $header_storage_disk;
    private $route_display;
    private $route_download;
    private $distributed_deployment_enable;
    private $distributed_deployment_role;
    private $distributed_deployment_middleware_cors;
    private $distributed_deployment_storage_host;
    private $distributed_deployment_web_hosts;
    private $forbidden_extensions;

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
        $this->route_display = $config->get('aetherupload.route_display');
        $this->route_download = $config->get('aetherupload.route_download');
        $this->distributed_deployment_enable = $config->get('aetherupload.distributed_deployment.enable');
        $this->distributed_deployment_role = $config->get('aetherupload.distributed_deployment.role');
        $this->distributed_deployment_storage_host = $config->get('aetherupload.distributed_deployment.web.storage_host');
        $this->distributed_deployment_middleware_cors = $config->get('aetherupload.distributed_deployment.storage.middleware_cors');
        $this->distributed_deployment_web_hosts = $config->get('aetherupload.distributed_deployment.storage.web_hosts');
        $this->forbidden_extensions = $config->get('aetherupload.forbidden_extensions');

        return $this;
    }

    public function applyGroupConfig($group)
    {
        $config = app('config');
        // 前端传递的分组名不在配置中
        if ( ! in_array($group, array_keys($config->get('aetherupload.groups'))) ) {
            die('invalid group');
        }
        $this->group = $group;
        $this->group_dir = $config->get('aetherupload.groups.' . $group . '.group_dir');
        $this->resource_maxsize = $config->get('aetherupload.groups.' . $group . '.resource_maxsize');
        $this->resource_extensions = $config->get('aetherupload.groups.' . $group . '.resource_extensions');
        $this->middleware_preprocess = $config->get('aetherupload.groups.' . $group . '.middleware_preprocess');
        $this->middleware_save_chunk = $config->get('aetherupload.groups.' . $group . '.middleware_save_chunk');
        $this->middleware_display = $config->get('aetherupload.groups.' . $group . '.middleware_display');
        $this->middleware_download = $config->get('aetherupload.groups.' . $group . '.middleware_download');
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