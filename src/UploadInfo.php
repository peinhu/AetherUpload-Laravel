<?php

namespace AetherUpload;

class UploadInfo
{
    private static $_instance = null;
    private $chunkTotalCount;
    private $chunkIndex;
    private $uploadBaseName;
    private $uploadExt;
    private $file;
    private $subDir;
    private $uploadHead;
    private $uploadPartialFile;

    private function __construct()
    {
        //disallow new instance
    }

    public static function instance()
    {
        if ( self::$_instance === null ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function __set($name, $value)
    {
        self::$_instance->{$name} = $value;
    }

    public static function __callStatic($name, $arguments)
    {
        return self::$_instance->{$name};
    }


}