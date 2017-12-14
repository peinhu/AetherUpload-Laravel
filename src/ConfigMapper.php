<?php

namespace AetherUpload;

class ConfigMapper
{
    private static $_instance = null;
    private $UPLOAD_PATH;
    private $FILE_DIR;
    private $FILE_SUB_DIR;
    private $HEAD_DIR;
    private $CHUNK_SIZE;
    private $FILE_MAXSIZE;
    private $FILE_EXTENSIONS;
    private $MIDDLEWARE_PREPROCESS;
    private $MIDDLEWARE_SAVE_CHUNK;
    private $MIDDLEWARE_DISPLAY;
    private $MIDDLEWARE_DOWNLOAD;
    private $EVENT_BEFORE_UPLOAD_COMPLETE;
    private $EVENT_UPLOAD_COMPLETE;

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
        $this->UPLOAD_PATH = config('aetherupload.UPLOAD_PATH');
        $this->CHUNK_SIZE = config('aetherupload.CHUNK_SIZE');
        $this->HEAD_DIR = config('aetherupload.HEAD_DIR');
        $this->FILE_SUB_DIR = config('aetherupload.FILE_SUB_DIR');

        return $this;
    }

    public function applyGroupConfig($group)
    {
        $this->FILE_DIR = $group;
        $this->FILE_MAXSIZE = config('aetherupload.GROUPS.' . $group . '.FILE_MAXSIZE');
        $this->FILE_EXTENSIONS = config('aetherupload.GROUPS.' . $group . '.FILE_EXTENSIONS');
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

}