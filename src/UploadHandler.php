<?php

namespace Peinhu\AetherUpload;

class UploadHandler extends \Illuminate\Routing\Controller
{
    public $config;
    public $receiver;
    public $configMapper;

    public function __construct(Receiver $receiver, ConfigMapper $configMapper)
    {
        $this->receiver = $receiver;
        $group = request('group');
        $this->config = $this->receiver->config = $configMapper->getConfigByGroup($group);
        $this->middleware(config('aetherupload.groups.' . $group . '.MIDDLEWARE_INIT'), ['only' => ['init']]);
        $this->middleware(config('aetherupload.groups.' . $group . '.MIDDLEWARE_SAVECHUNK'), ['only' => ['saveChunk']]);
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

        if ( ($reportError = $this->filterBySize($fileSize)) != 'pass' ) {
            return $reportError;
        }

        if ( ($reportError = $this->filterByExt($fileExt)) != 'pass' ) {
            return $reportError;
        }

        if ( ($reportError = $this->receiver->createFile()) != 'success' ) {
            return $reportError;
        }

        $result = [
            'error'          => 0,
            'chunkSize'      => $this->config->CHUNK_SIZE,
            'uploadBasename' => $this->receiver->uploadBasename,
            'uploadExt'      => $fileExt,
        ];

        return Responser::returnResult($result);
    }

    /**
     * handle and save the uploaded data
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveChunk()
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
        # 防止被人为跳过验证过程直接调用保存方法，从而上传恶意文件
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

        if ( ($reportError = $this->receiver->writeFile()) != 'success' ) {
            return $reportError;
        }

        return Responser::returnResult($result);
    }

    public function filterBySize($fileSize)
    {
        $MAXSIZE = $this->config->UPLOAD_FILE_MAXSIZE * 1000 * 1000;
        # 文件大小过滤
        if ( $fileSize > $MAXSIZE && $MAXSIZE != 0 ) {
            return Responser::reportError('文件过大');
        }

        return 'pass';
    }

    public function filterByExt($uploadExt)
    {
        $EXTENSIONS = $this->config->UPLOAD_FILE_EXTENSIONS;
        # 文件类型过滤
        if ( ($EXTENSIONS != '' && ! in_array($uploadExt, explode(',', $EXTENSIONS))) || in_array($uploadExt, static::getDangerousExtList()) ) {
            return Responser::reportError('文件类型不正确');
        }

        return 'pass';
    }

    protected static function getDangerousExtList()
    {
        return ['php', 'part', 'html', 'shtml', 'htm', 'shtm', 'js', 'jsp', 'asp', 'node', 'py', 'sh', 'bat', 'exe'];
    }

}