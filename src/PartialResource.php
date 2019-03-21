<?php

namespace AetherUpload;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PartialResource
{
    public $disk;
    public $tempName;
    public $group;
    public $groupDir;
    public $groupSubDir;
    public $header;
    public $path;
    public $realPath;
    private $chunkIndex = null;

    public function __construct($tempBaseName, $extension, $groupSubDir)
    {
        $this->disk = Storage::disk('local');
        $this->tempName = Util::getFileName($tempBaseName, $extension);
        $this->group = ConfigMapper::get('group');
        $this->groupDir = ConfigMapper::get('group_dir');
        $this->groupSubDir = $groupSubDir;
        $this->path = $this->getPath();
        $this->realPath = $this->getRealPath();
        $this->header = new Header($tempBaseName);
    }

    public function create()
    {
        if ( $this->createGroupSubDir() === false ) {
            throw new \Exception(trans('aetherupload::messages.create_subfolder_fail'));
        }

        if ( $this->disk->put($this->path, '') === false ) {
            throw new \Exception(trans('aetherupload::messages.create_resource_fail'));
        }

    }

    public function append($chunkRealPath)
    {
        $handle = @fopen($chunkRealPath, 'rb');

        if ( File::append($this->realPath, $handle) === false ) {
            throw new \Exception(trans('aetherupload::messages.write_resource_fail'));
        }

        fclose($handle);

    }

    public function delete()
    {
        if ( $this->disk->delete($this->path) === false ) {
            throw new \Exception(trans('aetherupload::messages.delete_resource_fail'));
        }

    }

    public function rename($completeName)
    {
        $completePath = $this->getCompletePath($completeName);

        if ( $this->disk->exists($completePath) === false ) {

            if ( $this->disk->move($this->path, $completePath) === false ) {
                throw new \Exception(trans('aetherupload::messages.rename_resource_fail'));
            }
        }
    }

    public function filterBySize($resourceSize)
    {
        $maxSize = (int)ConfigMapper::get('resource_maxsize');

        if ( (int)$resourceSize === 0 || ((int)$resourceSize > $maxSize && $maxSize !== 0) ) {
            throw new \Exception(trans('aetherupload::messages.invalid_resource_size'));
        }

    }

    public function filterByExtension($resourceExt)
    {
        $extensions = ConfigMapper::get('resource_extensions');

        if ( empty($resourceExt) || (empty($extensions) === false && in_array($resourceExt, $extensions) === false) || in_array($resourceExt, ConfigMapper::get('forbidden_extensions')) === true ) {
            throw new \Exception(trans('aetherupload::messages.invalid_resource_type'));
        }
    }

    public function checkSize()
    {
        $this->filterBySize(filesize($this->realPath));
    }

    public function checkMimeType()
    {
        $extension = MimeType::search(mime_content_type($this->realPath));

        $this->filterByExtension($extension);
    }

    public function exists()
    {
        return $this->disk->exists($this->path);
    }

    public function createGroupSubDir()
    {
        $groupDir = dirname($groupSubDir = $this->getGroupSubDirPath());

        if ( $this->disk->exists($groupDir) === false ) {
            return false;
        }

        if ( $this->disk->exists($groupSubDir) === false ) {
            if ( $this->disk->makeDirectory($groupSubDir) === false ) {
                return false;
            }
        }

        return true;
    }

    public function calculateHash()
    {
        return md5_file($this->realPath);
    }

    public function getPath()
    {
        return ConfigMapper::get('root_dir') . DIRECTORY_SEPARATOR . $this->groupDir . DIRECTORY_SEPARATOR . $this->groupSubDir . DIRECTORY_SEPARATOR . $this->tempName . '.part';
    }

    public function getRealPath()
    {
        return storage_path('app/') . $this->path;
    }

    public function getCompletePath($name)
    {
        return ConfigMapper::get('root_dir') . DIRECTORY_SEPARATOR . $this->groupDir . DIRECTORY_SEPARATOR . $this->groupSubDir . DIRECTORY_SEPARATOR . $name;
    }

    public function getGroupSubDirPath()
    {
        return ConfigMapper::get('root_dir') . DIRECTORY_SEPARATOR . $this->groupDir . DIRECTORY_SEPARATOR . $this->groupSubDir;
    }

    public function __set($property, $value)
    {
        if ( $property === 'chunkIndex' ) {
            $this->header->write($value);
        }
    }

    public function __get($property)
    {
        if ( $property === 'chunkIndex' ) {
            return $this->header->read();
        }

        return null;
    }

    public function __unset($property)
    {
        if ( $property === 'chunkIndex' ) {
            $this->header->delete();
        }
    }

    public function __call($name, $arguments)
    {
        if ( method_exists($this->disk, $name) ) {
            return call_user_func_array([$this->disk, $name], $arguments);
        }

        return null;
    }


}