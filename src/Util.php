<?php

namespace AetherUpload;

class Util
{
    /**
     * The rule of naming a temporary file
     * @return string
     */
    public static function generateTempName()
    {
        return time() . mt_rand(100000, 999999);
    }

    public static function getFileName($baseName, $ext)
    {
        return $baseName . '.' . $ext;
    }

    public static function generateSubDirName()
    {
        switch ( ConfigMapper::get('resource_subdir_rule') ) {
            case "year":
                $name = @date("Y", time());
                break;
            case "month":
                $name = @date("Ym", time());
                break;
            case "date":
                $name = @date("Ymd", time());
                break;
            case "const":
                $name = "subdir";
                break;
            default :
                $name = @date("Ym", time());
                break;
        }

        return $name;
    }

    public static function getDisplayLink($savedPath)
    {
        $storageHost = self::isDistributedApplicationHost() ? ConfigMapper::get('distributed_deployment_storage_host') : '';

        return $storageHost . '/aetherupload/display/' . $savedPath;
    }

    public static function getDownloadLink($savedPath, $newName)
    {
        $storageHost = self::isDistributedApplicationHost() ? ConfigMapper::get('distributed_deployment_storage_host') : '';

        return $storageHost . '/aetherupload/download/' . $savedPath . '/' . $newName;
    }

    public static function getStorageHostField()
    {
        return new \Illuminate\Support\HtmlString('<input type="hidden" id="aetherupload-storage-host" value="' . (self::isDistributedApplicationHost() ? ConfigMapper::get('distributed_deployment_storage_host') : '') . '" />');
    }

    public static function isStorageHost()
    {
        return ! ConfigMapper::get('distributed_deployment_enable') || ConfigMapper::get('distributed_deployment_role') === 'storage';
    }

    public static function isDistributedStorageHost()
    {
        return ConfigMapper::get('distributed_deployment_enable') === true && ConfigMapper::get('distributed_deployment_role') === 'storage';
    }

    public static function isDistributedApplicationHost()
    {
        return ConfigMapper::get('distributed_deployment_enable') === true && ConfigMapper::get('distributed_deployment_role') === 'application';
    }


}