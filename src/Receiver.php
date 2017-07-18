<?php

namespace Peinhu\AetherUpload;

class Receiver
{
    public $uploadHead;
    public $uploadPartialFile;
    public $chunkIndex;
    public $chunkTotalCount;
    public $file;
    public $uploadExt;
    public $uploadBaseName;
    public $savedFilePath;
    public $config;

    public function __construct()
    {
        $this->config = ConfigMapper::getInstance();
    }

    /**
     * filter and create the file
     */
    public function createFile()
    {
        $this->uploadBaseName = $this->generateTempFileName();
        $this->uploadPartialFile = $this->getUploadPartialFilePath();
        $this->uploadHead = $this->getUploadHeadPath();

        if ( ! (@touch($this->uploadPartialFile) && @touch($this->uploadHead)) ) {
            return Responser::reportError('无法创建文件');
        }

        return 'success';
    }

    /**
     * write data to the existing file
     */
    public function writeFile()
    {
        # 写入上传文件内容
        if ( @file_put_contents($this->uploadPartialFile, @file_get_contents($this->file->getRealPath()), FILE_APPEND) === false ) {
            return Responser::reportError('写文件失败', true, $this->uploadHead, $this->uploadPartialFile);
        }
        # 写入头文件内容
        if ( @file_put_contents($this->uploadHead, $this->chunkIndex) === false ) {
            return Responser::reportError('写头文件失败', true, $this->uploadHead, $this->uploadPartialFile);
        }

        return 'success';
    }

    public function renameTempFile()
    {
        $savedFileHash = $this->generateSavedFileHash($this->uploadPartialFile);

        if ( RedisHandler::hashExists($savedFileHash) ) {
            $this->savedFilePath = RedisHandler::getFilePathByHash($savedFileHash);
        } else {
            $this->savedFilePath = $this->config->get('FILE_DIR') . DIRECTORY_SEPARATOR . $this->config->get('FILE_SUB_DIR') . DIRECTORY_SEPARATOR . $savedFileHash . '.' . $this->uploadExt;

            if ( ! @rename($this->uploadPartialFile, $this->config->get('UPLOAD_PATH') . DIRECTORY_SEPARATOR . $this->savedFilePath) ) {
                return false;
            }
        }

        return $this->savedFilePath;
    }

    public function getUploadPartialFilePath($subDir = null)
    {
        if ( $subDir === null ) {
            $subDir = $this->config->get('FILE_SUB_DIR');
        }

        return $this->config->get('UPLOAD_PATH') . DIRECTORY_SEPARATOR . $this->config->get('FILE_DIR') . DIRECTORY_SEPARATOR . $subDir . DIRECTORY_SEPARATOR . $this->uploadBaseName . '.' . $this->uploadExt . '.part';
    }

    public function getUploadHeadPath()
    {
        return $this->config->get('UPLOAD_PATH') . DIRECTORY_SEPARATOR . $this->config->get('HEAD_DIR') . DIRECTORY_SEPARATOR . $this->uploadBaseName . '.head';
    }

    public function getUploadFileSubFolderPath()
    {
        return $this->config->get('UPLOAD_PATH') . DIRECTORY_SEPARATOR . $this->config->get('FILE_DIR') . DIRECTORY_SEPARATOR . $this->config->get('FILE_SUB_DIR');
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