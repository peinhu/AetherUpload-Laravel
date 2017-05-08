<?php

namespace Peinhu\AetherUpload;

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
    private $MIDDLEWARE_INIT;
    private $MIDDLEWARE_SAVE_CHUNK;
    private $MIDDLEWARE_DISPLAY;
    private $MIDDLEWARE_DOWNLOAD;

    private function __construct()
    {
        //disallow new instance
    }

    public static function getInstance()
    {
        if ( self::$_instance === null ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function applyConfigByGroup($group = null)
    {
        $this->UPLOAD_PATH = config('aetherupload.UPLOAD_PATH');
        $this->CHUNK_SIZE = config('aetherupload.CHUNK_SIZE');
        $this->HEAD_DIR = config('aetherupload.HEAD_DIR');
        $this->FILE_DIR = $group;
        $this->FILE_SUB_DIR = config('aetherupload.FILE_SUB_DIR');
        $this->FILE_MAXSIZE = config('aetherupload.GROUPS.' . $group . '.FILE_MAXSIZE');
        $this->FILE_EXTENSIONS = config('aetherupload.GROUPS.' . $group . '.FILE_EXTENSIONS');
        $this->MIDDLEWARE_INIT = config('aetherupload.GROUPS.' . $group . '.MIDDLEWARE_INIT');
        $this->MIDDLEWARE_SAVE_CHUNK = config('aetherupload.GROUPS.' . $group . '.MIDDLEWARE_SAVE_CHUNK');
        $this->MIDDLEWARE_DISPLAY = config('aetherupload.GROUPS.' . $group . '.MIDDLEWARE_DISPLAY');
        $this->MIDDLEWARE_DOWNLOAD = config('aetherupload.GROUPS.' . $group . '.MIDDLEWARE_DOWNLOAD');

        return $this;
    }

    public function get($property)
    {
        return $this->{$property};
    }

    public function set($property, $value)
    {
        return $this->{$property} = $value;
    }

}