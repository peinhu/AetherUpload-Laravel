<?php

namespace AetherUpload;

use Illuminate\Support\Facades\Storage;

class Resource
{
    public $disk;
    public $name;
    public $group;
    public $groupDir;
    public $groupSubdir;
    public $path;
    public $realPath;

    public function __construct($name, $groupSubdir)
    {
        $this->disk = Storage::disk('local');
        $this->name = $name;
        $this->group = ConfigMapper::get('group');
        $this->groupDir = ConfigMapper::get('group_dir');
        $this->groupSubdir = $groupSubdir;
        $this->path = $this->getPath();
        $this->realPath = $this->getRealPath();
    }

    public function getPath()
    {
        return ConfigMapper::get('root_dir') . DIRECTORY_SEPARATOR . $this->groupDir . DIRECTORY_SEPARATOR . $this->groupSubdir . DIRECTORY_SEPARATOR . $this->name;
    }

    public function getRealPath()
    {
        return storage_path('app/') . $this->path;
    }

    public function getSavedPath()
    {
        return $this->group . '_' . $this->groupSubdir . '_' . $this->name;
    }

    public function __call($name, $arguments)
    {
        if ( method_exists($this->disk, $name) ) {
            return call_user_func_array([$this->disk, $name], $arguments);
        }

        return null;
    }


}