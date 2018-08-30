<?php

namespace AetherUpload;

class ConfigMapper
{
    private static $_instance = null;
    private $ROOT_DIR;
    private $RESOURCE_DIR;
    private $RESOURCE_SUBDIR_RULE;
    private $CHUNK_SIZE;
    private $RESOURCE_MAXSIZE;
    private $RESOURCE_EXTENSIONS;
    private $MIDDLEWARE_PREPROCESS;
    private $MIDDLEWARE_SAVE_CHUNK;
    private $MIDDLEWARE_DISPLAY;
    private $MIDDLEWARE_DOWNLOAD;
    private $EVENT_BEFORE_UPLOAD_COMPLETE;
    private $EVENT_UPLOAD_COMPLETE;
    private $HEADER_STORAGE_DISK;
    private $DISTRIBUTED_DEPLOYMENT_ENABLE;
    private $DISTRIBUTED_DEPLOYMENT_ROLE;
    private $DISTRIBUTED_DEPLOYMENT_MIDDLEWARE_CORS;
    private $DISTRIBUTED_DEPLOYMENT_STORAGE_HOST;
    private $DISTRIBUTED_DEPLOYMENT_WEB_HOSTS;

    private function __construct()
    {
        //disallow new instance
    }

    public static function getInstance()
    {
        if ( self::$_instance === null ) {
            self::$_instance = (new self())->applyCommonConfig();
        }

        return self::$_instance;
    }

    private function applyCommonConfig()
    {
        $this->ROOT_DIR = config('aetherupload.ROOT_DIR');
        $this->CHUNK_SIZE = config('aetherupload.CHUNK_SIZE');
        $this->RESOURCE_SUBDIR_RULE = config('aetherupload.RESOURCE_SUBDIR_RULE');
        $this->HEADER_STORAGE_DISK = config('aetherupload.HEADER_STORAGE_DISK');
        $this->DISTRIBUTED_DEPLOYMENT_ENABLE = config('aetherupload.DISTRIBUTED_DEPLOYMENT.ENABLE');
        $this->DISTRIBUTED_DEPLOYMENT_ROLE = config('aetherupload.DISTRIBUTED_DEPLOYMENT.ROLE');
        $this->DISTRIBUTED_DEPLOYMENT_STORAGE_HOST = config('aetherupload.DISTRIBUTED_DEPLOYMENT.WEB.STORAGE_HOST');
        $this->DISTRIBUTED_DEPLOYMENT_MIDDLEWARE_CORS = config('aetherupload.DISTRIBUTED_DEPLOYMENT.STORAGE.MIDDLEWARE_CORS');
        $this->DISTRIBUTED_DEPLOYMENT_WEB_HOSTS = config('aetherupload.DISTRIBUTED_DEPLOYMENT.STORAGE.WEB_HOSTS');

        return $this;
    }

    public function applyGroupConfig($group)
    {
        $this->RESOURCE_DIR = $group;
        $this->RESOURCE_MAXSIZE = config('aetherupload.GROUPS.' . $group . '.RESOURCE_MAXSIZE');
        $this->RESOURCE_EXTENSIONS = config('aetherupload.GROUPS.' . $group . '.RESOURCE_EXTENSIONS');
        $this->MIDDLEWARE_PREPROCESS = config('aetherupload.GROUPS.' . $group . '.MIDDLEWARE_PREPROCESS');
        $this->MIDDLEWARE_SAVE_CHUNK = config('aetherupload.GROUPS.' . $group . '.MIDDLEWARE_SAVE_CHUNK');
        $this->MIDDLEWARE_DISPLAY = config('aetherupload.GROUPS.' . $group . '.MIDDLEWARE_DISPLAY');
        $this->MIDDLEWARE_DOWNLOAD = config('aetherupload.GROUPS.' . $group . '.MIDDLEWARE_DOWNLOAD');
        $this->EVENT_BEFORE_UPLOAD_COMPLETE = config('aetherupload.GROUPS.' . $group . '.EVENT_BEFORE_UPLOAD_COMPLETE');
        $this->EVENT_UPLOAD_COMPLETE = config('aetherupload.GROUPS.' . $group . '.EVENT_UPLOAD_COMPLETE');

        return $this;
    }

    public static function get($property)
    {
        return self::getInstance()->{$property};
    }

    public static function set($property, $value)
    {
        self::getInstance()->{$property} = $value;
    }

}