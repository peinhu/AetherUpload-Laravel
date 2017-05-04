<?php

namespace Peinhu\AetherUpload;

class ConfigMapper
{
    public $UPLOAD_PATH;
    public $UPLOAD_FILE_DIR;
    public $UPLOAD_HEAD_DIR;
    public $CHUNK_SIZE;
    public $UPLOAD_FILE_MAXSIZE;
    public $UPLOAD_FILE_EXTENSIONS;

    public function getConfigByGroup($group)
    {
        $this->UPLOAD_PATH = config('aetherupload.groups.' . $group . '.UPLOAD_PATH');
        $this->UPLOAD_FILE_DIR = config('aetherupload.groups.' . $group . '.UPLOAD_FILE_DIR');
        $this->UPLOAD_HEAD_DIR = config('aetherupload.groups.' . $group . '.UPLOAD_HEAD_DIR');
        $this->CHUNK_SIZE = config('aetherupload.groups.' . $group . '.CHUNK_SIZE');
        $this->UPLOAD_FILE_MAXSIZE = config('aetherupload.groups.' . $group . '.UPLOAD_FILE_MAXSIZE');
        $this->UPLOAD_FILE_EXTENSIONS = config('aetherupload.groups.' . $group . '.UPLOAD_FILE_EXTENSIONS');

        return $this;
    }
}