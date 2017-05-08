<?php

namespace Peinhu\AetherUpload;

class UploadHandler extends \Illuminate\Routing\Controller
{
    public $config;
    public $receiver;

    public function __construct(Receiver $receiver)
    {
        $group = request('group');
        $this->receiver = $receiver;
        $this->config = ConfigMapper::getInstance()->applyConfigByGroup($group);
        $this->middleware($this->config->get('MIDDLEWARE_INIT'))->only('init');
        $this->middleware($this->config->get('MIDDLEWARE_SAVE_CHUNK'))->only('saveChunk');
    }

    /**
     * initialize the upload handler
     * @return \Illuminate\Http\JsonResponse
     */
    public function init()
    {
        $fileName = request('file_name', 0);
        $fileSize = request('file_size', 0);
        $result = [
            'error'          => 0,
            'chunkSize'      => $this->config->get('CHUNK_SIZE'),
            'subDir'         => $this->config->get('FILE_SUB_DIR'),
            'uploadBaseName' => '',
            'uploadExt'      => '',
        ];

        if ( ! ($fileName && $fileSize) ) {
            return Responser::reportError('缺少必要的文件参数');
        }

        if ( ! is_dir($uploadFileSubFolderPath = $this->receiver->getUploadFileSubFolderPath()) ) {
            @mkdir($uploadFileSubFolderPath, 0755);
        }

        $fileExt = strtolower(substr($fileName, strripos($fileName, '.') + 1));
        $this->receiver->set('uploadExt', $fileExt);

        if ( ($reportError = $this->filterBySize($fileSize)) != 'pass' ) {
            return $reportError;
        }

        if ( ($reportError = $this->filterByExt($fileExt)) != 'pass' ) {
            return $reportError;
        }

        if ( ($reportError = $this->receiver->createFile()) != 'success' ) {
            return $reportError;
        }

        $result['uploadExt'] = $fileExt;
        $result['uploadBaseName'] = $this->receiver->get('uploadBaseName');

        return Responser::returnResult($result);
    }

    /**
     * handle and save the uploaded data
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveChunk()
    {
        $chunkTotalCount = $this->receiver->set('chunkTotalCount', request('chunk_total', 0));# 分片总数
        $chunkIndex = $this->receiver->set('chunkIndex', request('chunk_index', 0));# 当前分片号
        $uploadBaseName = $this->receiver->set('uploadBaseName', request('upload_basename', 0));# 文件重命名
        $uploadExt = $this->receiver->set('uploadExt', request('upload_ext', 0)); # 文件扩展名
        $file = $this->receiver->set('file', request()->file('file', 0));# 文件
        $subDir = request('sub_dir', 0);# 子目录名
        $uploadHead = $this->receiver->set('uploadHead', $this->receiver->getUploadHeadPath());
        $uploadPartialFile = $this->receiver->set('uploadPartialFile', $this->receiver->getUploadPartialFilePath($subDir));
        $result = [
            'error' => 0,
        ];

        if ( ! ($chunkTotalCount && $chunkIndex && $uploadExt && $uploadBaseName && $subDir) ) {
            return Responser::reportError('缺少必要的文件块参数', true, $uploadHead, $uploadPartialFile);
        }
        # 防止被人为跳过验证过程直接调用保存方法，从而上传恶意文件
        if ( ! is_file($uploadPartialFile) ) {
            return Responser::reportError('此文件不被允许上传', true, $uploadHead, $uploadPartialFile);
        }

        if ( $file->getError() > 0 ) {
            return Responser::reportError($file->getErrorMessage(), true, $uploadHead, $uploadPartialFile);
        }

        if ( ! $file->isValid() ) {
            return Responser::reportError('文件必须通过HTTP POST上传', true, $uploadHead, $uploadPartialFile);
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
        $MAXSIZE = $this->config->get('FILE_MAXSIZE') * 1000 * 1000;
        # 文件大小过滤
        if ( $fileSize > $MAXSIZE && $MAXSIZE != 0 ) {
            return Responser::reportError('文件过大');
        }

        return 'pass';
    }

    public function filterByExt($uploadExt)
    {
        $EXTENSIONS = $this->config->get('FILE_EXTENSIONS');
        # 文件类型过滤
        if ( ($EXTENSIONS != '' && ! in_array($uploadExt, explode(',', $EXTENSIONS))) || in_array($uploadExt, static::getDangerousExtList()) ) {
            return Responser::reportError('文件类型不正确');
        }

        return 'pass';
    }

    private static function getDangerousExtList()
    {
        return ['php', 'part', 'html', 'shtml', 'htm', 'shtm', 'js', 'jsp', 'asp', 'node', 'py', 'sh', 'bat', 'exe'];
    }

}