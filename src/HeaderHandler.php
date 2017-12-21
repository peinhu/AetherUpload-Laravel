<?php

namespace AetherUpload;

use AetherUpload\File\Header as FileHeader;
use AetherUpload\Redis\Header as RedisHeader;

class HeaderHandler
{
    public $header;

    public function __construct()
    {
        $headerDriver = ucfirst(ConfigMapper::get('HEADER_DRIVER')) . "Header";

        $this->header = new $headerDriver();
    }

    public function createHeader($name)
    {
        if ( ! $this->header->create($name) ) {
            return trans('aetherupload::messages.create_file_fail');
        }

        return false;
    }

    public function writeHeader($name,$content)
    {
        if ( ! $this->header->write($name,$content) ) {
            return trans('aetherupload::messages.write_file_fail');
        }

        return false;
    }

    public function readHeader($name)
    {
        if ( ! $this->header->read($name) ) {
            return trans('aetherupload::messages.read_file_fail');
        }

        return false;
    }

    public function deleteHeader($name)
    {
        if ( ! $this->header->delete($name) ) {
            return trans('aetherupload::messages.delete_file_fail');
        }

        return false;
    }


}