<?php

namespace AetherUpload;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Adapter\Polyfill\StreamedCopyTrait;

class ResourceHandler
{
    public $disk;

    public function __construct()
    {
        $this->disk = Storage::disk('local');
    }

    public function createResource($name, $subDirName, $group)
    {
        if ( $this->createSubFolder($subDirName, $group) === false ) {
            throw new \Exception(trans('aetherupload::messages.create_subfolder_fail'));
        }

        if ( $this->disk->put($this->getPartialResourceRelativePath($name, $subDirName, $group), "") === false ) {
            throw new \Exception(trans('aetherupload::messages.create_resource_fail'));
        }

    }

    public function appendResource($name, $subDirName, $group, $resourceRealPath)
    {
        $handle = $this->getStreamHandle($resourceRealPath, 'rb');

        if ( File::append($this->getPartialResourcePath($name, $subDirName, $group), $handle) === false ) {
            throw new \Exception(trans('aetherupload::messages.write_resource_fail'));
        }

        fclose($handle);
    }

    public function deleteResource($name, $subDirName, $group)
    {
        if ( $this->disk->delete($this->getPartialResourceRelativePath($name, $subDirName, $group)) === false ) {
            throw new \Exception(trans('aetherupload::messages.delete_resource_fail'));
        }

    }

    public function renameResource($uploadName, $subDirName, $group, $saveName)
    {
        $completeResource = $this->getResourceRelativePath($saveName, $subDirName, $group);

        if ( $this->disk->exists($completeResource) === false ) {

            if ( $this->disk->move($this->getPartialResourceRelativePath($uploadName, $subDirName, $group), $completeResource) === false ) {
                throw new \Exception(trans('aetherupload::messages.rename_resource_fail'));
            }

        }

    }

    public function partialResourceExists($resourceTempName, $subDirName, $group)
    {
        return $this->disk->exists($this->getPartialResourceRelativePath($resourceTempName, $subDirName, $group));
    }

    public function resourceExists($resourceName, $subDirName, $group)
    {
        return $this->disk->exists($this->getResourceRelativePath($resourceName, $subDirName, $group));
    }

    public function createSubFolder($subDirName, $group)
    {
        $resourceSubDir = $this->getSubFolderRelativePath($subDirName, $group);

        if ( $this->disk->exists($resourceSubDir) === false ) {
            if ( $this->disk->makeDirectory($resourceSubDir) === false ) {
                return false;
            }
        }

        return true;
    }

    public function calculateHash($name, $subDirName, $group)
    {
        return md5_file($this->getPartialResourcePath($name, $subDirName, $group));
    }

    public function getResourceSavedPath($resourceHash, $uploadExt, $subDirName, $group)
    {
        return $group . "_" . $subDirName . "_" . $resourceHash . "." . $uploadExt;
    }

    public function getSubFolderRelativePath($subDirName, $group)
    {
        return ConfigMapper::get('ROOT_DIR') . DIRECTORY_SEPARATOR . $group . DIRECTORY_SEPARATOR . $subDirName;
    }

    public function getPartialResourceRelativePath($uploadName, $subDirName, $group)
    {
        return ConfigMapper::get('ROOT_DIR') . DIRECTORY_SEPARATOR . $group . DIRECTORY_SEPARATOR . $subDirName . DIRECTORY_SEPARATOR . $uploadName . ".part";
    }

    public function getResourceRelativePath($name, $subDirName, $group)
    {
        return ConfigMapper::get('ROOT_DIR') . DIRECTORY_SEPARATOR . $group . DIRECTORY_SEPARATOR . $subDirName . DIRECTORY_SEPARATOR . $name;
    }

    public function getResourceName($baseName, $ext)
    {
        return $baseName . "." . $ext;
    }

    public function getResourcePath($name, $subDirName, $group)
    {
        return storage_path('app/') . $this->getResourceRelativePath($name, $subDirName, $group);
    }

    public function getPartialResourcePath($name, $subDirName, $group)
    {
        return storage_path('app/') . $this->getPartialResourceRelativePath($name, $subDirName, $group);
    }

    public static function getDisplayLink($savedPath)
    {
        $storageHost = ConfigMapper::get('DISTRIBUTED_DEPLOYMENT_ENABLE') ? ConfigMapper::get('DISTRIBUTED_DEPLOYMENT_STORAGE_HOST') : '';

        return $storageHost . "/aetherupload/display/" . $savedPath;
    }

    public static function getDownloadLink($savedPath, $newName)
    {
        $storageHost = ConfigMapper::get('DISTRIBUTED_DEPLOYMENT_ENABLE') ? ConfigMapper::get('DISTRIBUTED_DEPLOYMENT_STORAGE_HOST') : '';

        return $storageHost . "/aetherupload/download/" . $savedPath . "/" . $newName;
    }

    public function generateResourceSubDirName()
    {
        switch ( ConfigMapper::get('RESOURCE_SUBDIR_RULE') ) {
            case "year":
                $name = @date("Y", time());
                break;
            case "month":
                $name = @date("Ym", time());
                break;
            case "date":
                $name = @date("Ymd", time());
                break;
            case "static":
                $name = "subdir";
                break;
            default :
                $name = @date("Ym", time());
                break;
        }

        return $name;
    }

    private function getStreamHandle($path, $mode)
    {
        return @fopen($path, $mode);
    }

    public function __call($name, $arguments)
    {
        if ( method_exists($this->disk, $name) ) {
            return call_user_func_array([$this->disk, $name], $arguments);
        }

        return null;
    }


}