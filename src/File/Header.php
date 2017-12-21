<?php

namespace AetherUpload\File;

use AetherUpload\ConfigMapper;
use AetherUpload\HeaderInterface;

class Header implements HeaderInterface
{

    public function create($name)
    {
        if ( ! @touch($this->getUploadHeadPath($name))) {
            return trans('aetherupload::messages.create_file_fail');
        }

        return false;
    }

    public function write($name, $content)
    {
        if ( ! @file_put_contents($name, $content)) {
            return trans('aetherupload::messages.write_head_fail');
        }

        return false;
    }

    public function read($name)
    {
        if ( ! @file_get_contents($this->getUploadHeadPath($name))) {
            return trans('aetherupload::messages.read_head_fail');
        }

        return false;
    }

    public function delete($name)
    {
        if ( ! @unlink($name)) {
            return trans('aetherupload::messages.delete_head_fail');
        }

        return false;
    }

    public function getUploadHeadPath($uploadBaseName)
    {
        return ConfigMapper::get('UPLOAD_PATH') . DIRECTORY_SEPARATOR . ConfigMapper::get('HEAD_DIR') . DIRECTORY_SEPARATOR . $uploadBaseName;
    }


}