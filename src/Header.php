<?php

namespace AetherUpload;

use Illuminate\Support\Facades\Storage;

class Header
{
    public $disk;
    public $path;
    public $name;

    public function __construct($tempBaseName)
    {
        $this->disk = Storage::disk(ConfigMapper::get('header_storage_disk'));
        $this->name = $tempBaseName;
        $this->path = $this->getRelativePath();
    }

    public function create()
    {
        if ( $this->disk->put($this->path, 0) === false ) {
            throw new \Exception(trans('aetherupload::messages.create_header_fail'));
        }
    }

    public function write($content)
    {
        if ( $this->disk->put($this->path, $content) === false ) {
            throw new \Exception(trans('aetherupload::messages.write_header_fail'));
        }
    }

    public function read()
    {
        if ( ($content = $this->disk->read($this->path)) === false ) {
            throw new \Exception(trans('aetherupload::messages.read_header_fail'));
        }

        return $content;
    }

    public function delete()
    {
        if ( $this->disk->delete($this->path) === false ) {
            throw new \Exception(trans('aetherupload::messages.delete_header_fail'));
        }
    }

    private function getRelativePath()
    {
        return ConfigMapper::get('root_dir') . DIRECTORY_SEPARATOR . '_header' . DIRECTORY_SEPARATOR . $this->name;
    }


}