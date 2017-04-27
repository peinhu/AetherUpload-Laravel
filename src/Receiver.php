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
    static public $UPLOAD_FILE_DIR;
    static public $UPLOAD_HEAD_DIR;
    static public $UPLOAD_PATH;
    static public $CHUNK_SIZE;

    public function __construct()
    {
        self::$UPLOAD_PATH = config('aetherupload.UPLOAD_PATH');
        self::$UPLOAD_FILE_DIR = config('aetherupload.UPLOAD_FILE_DIR');
        self::$UPLOAD_HEAD_DIR = config('aetherupload.UPLOAD_HEAD_DIR');
        self::$CHUNK_SIZE = config('aetherupload.CHUNK_SIZE');
    }

    /**
     * filter and create the file
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function createFile()
    {
        $uploadBasename = $this->generateNewName();
        $this->uploadFilePartial = $this->getUploadFilePartialPath($uploadBasename, $this->uploadExt);
        $this->uploadHead = $this->getUploadHeadPath($uploadBasename);

        if ( ! (@touch($this->uploadFilePartial) && @touch($this->uploadHead)) ) {
            return Responser::reportError('无法创建文件');
        }

        return $uploadBasename;
    }

    /**
     * write data to the existing file
     * @return bool|\Illuminate\Http\JsonResponse
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

        return true;
    }

    protected function generateNewName()
    {
        return time() . mt_rand(100, 999);
    }

    public function getUploadFilePartialPath($uploadBasename, $uploadExt)
    {
        return self::$UPLOAD_PATH . DIRECTORY_SEPARATOR . self::$UPLOAD_FILE_DIR . DIRECTORY_SEPARATOR . $uploadBasename . '.' . $uploadExt . '.part';
    }

    public function getUploadHeadPath($uploadBasename)
    {
        return self::$UPLOAD_PATH . DIRECTORY_SEPARATOR . self::$UPLOAD_HEAD_DIR . DIRECTORY_SEPARATOR . $uploadBasename . '.head';
    }


}