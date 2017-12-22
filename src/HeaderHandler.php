<?php

namespace AetherUpload;

class HeaderHandler
{
    public $header;

    public function __construct()
    {
        $headerDriver = __NAMESPACE__.'\\'.ucfirst(ConfigMapper::get('HEADER_DRIVER')) .'Header';
        $this->header = new $headerDriver;
    }

    public function createHeader($name)
    {
        if ( $this->header->create($name) === false ) {
            throw new \Exception(trans('aetherupload::messages.create_header_fail'));
        }
    }

    public function writeHeader($name,$content)
    {
        if ( $this->header->write($name,$content) === false) {
            throw new \Exception(trans('aetherupload::messages.write_header_fail'));
        }

    }

    public function readHeader($name)
    {
        if($content = $this->header->read($name) === false){
            throw new \Exception(trans('aetherupload::messages.read_header_fail'));
        }
        return $content;
    }

    public function deleteHeader($name)
    {
        if ( $this->header->delete($name) === false) {
            throw new \Exception(trans('aetherupload::messages.delete_header_fail'));
        }

    }


}