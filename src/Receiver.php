<?php

namespace Peinhu\AetherUpload;

class Receiver
{
    public $uploadHead;
    public $uploadFilePartial;
    public $chunkIndex;
    public $chunkTotalCount;
    public $file;
    public $uploadExt;
    public $uploadBasename;
    public $config;

    /**
     * filter and create the file
     */
    public function createFile()
    {
        $this->uploadBasename = $this->generateNewName();
        $this->uploadFilePartial = $this->getUploadFilePartialPath($this->uploadBasename, $this->uploadExt);
        $this->uploadHead = $this->getUploadHeadPath($this->uploadBasename);

        if ( ! (@touch($this->uploadFilePartial) && @touch($this->uploadHead)) ) {
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
        if ( @file_put_contents($this->uploadFilePartial, @file_get_contents($this->file->getRealPath()), FILE_APPEND) === false ) {
            return Responser::reportError('写文件失败', true, $this->uploadHead, $this->uploadFilePartial);
        }
        # 写入头文件内容
        if ( @file_put_contents($this->uploadHead, $this->chunkIndex) === false ) {
            return Responser::reportError('写头文件失败', true, $this->uploadHead, $this->uploadFilePartial);
        }
        # 判断文件传输完成
        if ( $this->chunkIndex === $this->chunkTotalCount ) {
            @unlink($this->uploadHead);

            if ( ! @rename($this->uploadFilePartial, str_ireplace('.part', '', $this->uploadFilePartial)) ) {
                return Responser::reportError('重命名文件失败', true, $this->uploadHead, $this->uploadFilePartial);
            }

        }

        return 'success';
    }

    protected function generateNewName()
    {
        return time() . mt_rand(100, 999);
    }

    public function getUploadFilePartialPath($uploadBasename, $uploadExt)
    {
        return $this->config->UPLOAD_PATH . DIRECTORY_SEPARATOR . $this->config->UPLOAD_FILE_DIR . DIRECTORY_SEPARATOR . $uploadBasename . '.' . $uploadExt . '.part';
    }

    public function getUploadHeadPath($uploadBasename)
    {
        return $this->config->UPLOAD_PATH . DIRECTORY_SEPARATOR . $this->config->UPLOAD_HEAD_DIR . DIRECTORY_SEPARATOR . $uploadBasename . '.head';
    }


}