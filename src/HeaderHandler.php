<?php

namespace AetherUpload;

use Illuminate\Support\Facades\Storage;

class HeaderHandler
{
    public $disk;

    public function __construct()
    {
        $this->disk = Storage::disk(ConfigMapper::get('HEADER_STORAGE_DISK'));
    }

    public function createHeader($name)
    {
        if ( $this->disk->put($this->getHeaderPath($name), "0") === false ) {
            throw new \Exception(trans('aetherupload::messages.create_header_fail'));
        }
    }

    public function writeHeader($name, $content)
    {
        if ( $this->disk->put($this->getHeaderPath($name), $content) === false ) {
            throw new \Exception(trans('aetherupload::messages.write_header_fail'));
        }
    }

    public function readHeader($name)
    {
        if ( ($content = $this->disk->read($this->getHeaderPath($name))) === false ) {
            throw new \Exception(trans('aetherupload::messages.read_header_fail'));
        }

        return $content;
    }

    public function deleteHeader($name)
    {
        if ( $this->disk->delete($this->getHeaderPath($name)) === false ) {
            throw new \Exception(trans('aetherupload::messages.delete_header_fail'));
        }

    }

    public function files($dir)
    {
        return $this->disk->files($dir);
    }

    private function getHeaderPath($name)
    {
        return ConfigMapper::get('ROOT_DIR') . DIRECTORY_SEPARATOR . '_header' . DIRECTORY_SEPARATOR . $name;
    }


}