<?php

namespace AetherUpload;

class Receiver
{
    public $uploadHead;
    public $uploadPartialFile;
    public $chunkIndex;
    public $chunkTotalCount;
    public $file;
    public $uploadExt;
    public $uploadBaseName;
    public $savedPath;

    /**
     * filter and create the file
     */
    public function createFile()
    {
        $this->uploadBaseName = $this->generateTempFileName();
        $this->uploadPartialFile = $this->getUploadPartialFilePath();
        $this->uploadHead = $this->getUploadHeadPath();

        if ( ! (@touch($this->uploadPartialFile) && @touch($this->uploadHead)) ) {
            return trans('aetherupload::messages.create_file_fail');
        }

        return false;
    }

    /**
     * write data to the existing file
     */
    public function writeFile()
    {
        # 写入上传文件内容
        if ( @file_put_contents($this->uploadPartialFile, @file_get_contents($this->file->getRealPath()), FILE_APPEND) === false ) {
            return trans('aetherupload::messages.write_file_fail');
        }
        # 写入头文件内容
        if ( @file_put_contents($this->uploadHead, $this->chunkIndex) === false ) {
            return trans('aetherupload::messages.write_head_fail');
        }

        return false;
    }

    public function renameTempFile()
    {
        $savedFileHash = $this->generateSavedFileHash($this->uploadPartialFile);

        if ( RedisHandler::hashExists($savedFileHash) ) {
            $this->savedPath = RedisHandler::getFilePathByHash($savedFileHash);
        } else {
            $this->savedPath = ConfigMapper::get('FILE_DIR') . DIRECTORY_SEPARATOR . ConfigMapper::get('FILE_SUB_DIR') . DIRECTORY_SEPARATOR . $savedFileHash . '.' . $this->uploadExt;

            if ( ! @rename($this->uploadPartialFile, ConfigMapper::get('UPLOAD_PATH') . DIRECTORY_SEPARATOR . $this->savedPath) ) {
                return false;
            }
        }

        return $this->savedPath;
    }

    public function getUploadPartialFilePath($subDir = null)
    {
        if ( $subDir === null ) {
            $subDir = ConfigMapper::get('FILE_SUB_DIR');
        }

        return ConfigMapper::get('UPLOAD_PATH') . DIRECTORY_SEPARATOR . ConfigMapper::get('FILE_DIR') . DIRECTORY_SEPARATOR . $subDir . DIRECTORY_SEPARATOR . $this->uploadBaseName . '.' . $this->uploadExt . '.part';
    }

    public function getUploadHeadPath()
    {
        return ConfigMapper::get('UPLOAD_PATH') . DIRECTORY_SEPARATOR . ConfigMapper::get('HEAD_DIR') . DIRECTORY_SEPARATOR . $this->uploadBaseName . '.head';
    }

    public function getUploadFileSubFolderPath()
    {
        return ConfigMapper::get('UPLOAD_PATH') . DIRECTORY_SEPARATOR . ConfigMapper::get('FILE_DIR') . DIRECTORY_SEPARATOR . ConfigMapper::get('FILE_SUB_DIR');
    }

    protected function generateSavedFileHash($filePath)
    {
        return md5_file($filePath);
    }

    protected function generateTempFileName()
    {
        return time() . mt_rand(100, 999);
    }


}