<?php

namespace AetherUpload;

class FileHeader implements HeaderInterface
{
    public static function instance()
    {
        return new self;
    }

    public function create($name)
    {
        if ( ! @touch($this->getUploadHeadPath($name))) {
            return false;
        }

        return true;
    }

    public function write($name, $content)
    {
        if ( ! @file_put_contents($this->getUploadHeadPath($name), $content)) {
            return false;
        }

        return true;
    }

    public function read($name)
    {
        return @file_get_contents($this->getUploadHeadPath($name));
    }

    public function delete($name)
    {
        if ( ! @unlink($this->getUploadHeadPath($name))) {
            return false;
        }

        return true;
    }

    public function getUploadHeadPath($uploadBaseName)
    {
        return ConfigMapper::get('UPLOAD_PATH') . DIRECTORY_SEPARATOR . ConfigMapper::get('HEAD_DIR') . DIRECTORY_SEPARATOR . $uploadBaseName;
    }


}