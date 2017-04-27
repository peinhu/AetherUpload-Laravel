<?php

namespace Peinhu\AetherUpload;

class UploadHandler
{
    public $result;

    public function __construct(Receiver $receiver)
    {
        $this->receiver = $receiver;
    }

    /**
     * initialize the upload handler
     * @return \Illuminate\Http\JsonResponse
     */
    public function init()
    {
        $fileName = request('file_name', 0);
        $fileSize = request('file_size', 0);

        if ( ! ($fileName && $fileSize) ) {
            return Responser::reportError('缺少必要的文件参数');
        }

        $fileExt = $this->receiver->uploadExt = strtolower(substr($fileName, strripos($fileName, '.') + 1));
        $this->filterBySize($fileSize);
        $this->filterByExt($fileExt);
        $uploadBasename = $this->receiver->createFile();

        $result = [
            'error'          => 0,
            'chunkSize'      => Receiver::$CHUNK_SIZE,
            'uploadBasename' => $uploadBasename,
            'uploadExt'      => $fileExt,
        ];

        return Responser::returnResult($result);
    }

    /**
     * handle and save the uploaded data
     * @return \Illuminate\Http\JsonResponse
     */
    public function save()
    {
        $chunkTotalCount = request('chunk_total', 0);# 分片总数
        $chunkIndex = request('chunk_index', 0);# 当前分片号
        $uploadBasename = request('upload_basename', 0);# 文件重命名
        $uploadExt = request('upload_ext', 0);# 文件扩展名
        $file = request()->file('file');
        $uploadHead = $this->receiver->getUploadHeadPath($uploadBasename);
        $uploadFilePartial = $this->receiver->getUploadFilePartialPath($uploadBasename, $uploadExt);
        $this->receiver->uploadHead = $uploadHead;
        $this->receiver->uploadFilePartial = $uploadFilePartial;
        $this->receiver->chunkIndex = $chunkIndex;
        $this->receiver->chunkTotalCount = $chunkTotalCount;
        $this->receiver->file = $file;

        $result = [
            'error' => 0,
        ];

        if ( ! ($chunkTotalCount && $chunkIndex && $uploadExt && $uploadBasename) ) {
            return Responser::reportError('缺少必要的文件块参数', true, $uploadHead, $uploadFilePartial);
        }
        # 防止被人为跳过验证过程直接调用save方法，从而上传恶意文件
        if ( ! is_file($uploadFilePartial) ) {
            return Responser::reportError('此文件不被允许上传', true, $uploadHead, $uploadFilePartial);
        }

        if ( $file->getError() > 0 ) {
            return Responser::reportError($file->getErrorMessage(), true, $uploadHead, $uploadFilePartial);
        }

        if ( ! $file->isValid() ) {
            return Responser::reportError('文件必须通过HTTP POST上传', true, $uploadHead, $uploadFilePartial);
        }
        # 头文件指针验证，防止断线造成的重复传输某个文件块
        if ( @file_get_contents($uploadHead) != $chunkIndex - 1 ) {
            return Responser::returnResult($result);
        }

        $this->receiver->writeFile();

        return Responser::returnResult($result);
    }

    public function filterBySize($fileSize)
    {
        $MAXSIZE = config('aetherupload.UPLOAD_FILE_MAXSIZE') * 1024 * 1024;
        # 文件大小过滤
        if ( $fileSize > $MAXSIZE && $MAXSIZE != 0 ) {
            return Responser::reportError('文件过大');
        }

        return true;
    }

    public function filterByExt($uploadExt)
    {
        $EXTENSIONS = config('aetherupload.UPLOAD_FILE_EXTENSIONS');
        # 文件类型过滤
        if ( ($EXTENSIONS != '' && ! in_array($uploadExt, explode(',', $EXTENSIONS))) || in_array($uploadExt, static::getDangerousExtList()) ) {
            return Responser::reportError('文件类型不正确');
        }

        return true;
    }

    protected static function getDangerousExtList()
    {
        return ['php', 'part', 'html', 'shtml', 'htm', 'shtm', 'js', 'jsp', 'asp', 'node', 'py', 'sh', 'bat', 'exe'];
    }

}