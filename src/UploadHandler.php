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
    public function preprocess()
    {
        $fileName = request('file_name', 0);
        $fileSize = request('file_size', 0);
        $fileHash = request('file_hash', 0);

        $result = [
            'error'          => 0,
            'chunkSize'      => $this->config->get('CHUNK_SIZE'),
            'subDir'         => $this->config->get('FILE_SUB_DIR'),
            'uploadBaseName' => '',
            'uploadExt'      => '',
            'savedFilePath'  => '',
        ];

        if ( ! ($fileName && $fileSize) ) {
            return Responser::reportError('缺少必要的文件参数');
        }

        if ( ! is_dir($uploadFileSubFolderPath = $this->receiver->getUploadFileSubFolderPath()) ) {
            @mkdir($uploadFileSubFolderPath, 0755);
        }

        $this->receiver->uploadExt = strtolower(substr($fileName, strripos($fileName, '.') + 1));

        if ( ($reportError = $this->filterBySize($fileSize)) != 'pass' ) {
            return $reportError;
        }

        if ( ($reportError = $this->filterByExt($this->receiver->uploadExt)) != 'pass' ) {
            return $reportError;
        }

        if ( $fileHash ) {
            if ( RedisHandler::hashExists($fileHash) ) {
                $result['savedFilePath'] = RedisHandler::getFilePathByHash($fileHash);

                return Responser::returnResult($result);
            }
        }

        if ( ($reportError = $this->receiver->createFile()) != 'success' ) {
            return $reportError;
        }

        $result['uploadExt'] = $this->receiver->uploadExt;
        $result['uploadBaseName'] = $this->receiver->uploadBaseName;

        return Responser::returnResult($result);
    }

    /**
     * handle and save the uploaded data
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveChunk()
    {
        $this->receiver->chunkTotalCount = request('chunk_total', 0);# 分片总数
        $this->receiver->chunkIndex = request('chunk_index', 0);# 当前分片号
        $this->receiver->uploadBaseName = request('upload_basename', 0);# 文件重命名
        $this->receiver->uploadExt = request('upload_ext', 0); # 文件扩展名
        $this->receiver->file = request()->file('file', 0);# 文件
        $subDir = request('sub_dir', 0);# 子目录名
        $this->receiver->uploadHead = $this->receiver->getUploadHeadPath();
        $this->receiver->uploadPartialFile = $this->receiver->getUploadPartialFilePath($subDir);
        $result = [
            'error'         => 0,
            'savedFilePath' => '',
        ];

        if ( ! ($this->receiver->chunkTotalCount && $this->receiver->chunkIndex && $this->receiver->uploadExt && $this->receiver->uploadBaseName && $subDir) ) {
            return Responser::reportError('缺少必要的文件块参数', true, $this->receiver->uploadHead, $this->receiver->uploadPartialFile);
        }
        # 防止被人为跳过验证过程直接调用保存方法，从而上传恶意文件
        if ( ! is_file($this->receiver->uploadPartialFile) ) {
            return Responser::reportError('此文件不被允许上传', true, $this->receiver->uploadHead, $this->receiver->uploadPartialFile);
        }

        if ( $this->receiver->file->getError() > 0 ) {
            return Responser::reportError($this->receiver->file->getErrorMessage(), true, $this->receiver->uploadHead, $this->receiver->uploadPartialFile);
        }

        if ( ! $this->receiver->file->isValid() ) {
            return Responser::reportError('文件必须通过HTTP POST上传', true, $this->receiver->uploadHead, $this->receiver->uploadPartialFile);
        }
        # 头文件指针验证，防止断线造成的重复传输某个文件块
        if ( @file_get_contents($this->receiver->uploadHead) != $this->receiver->chunkIndex - 1 ) {
            return Responser::returnResult($result);
        }

        if ( ($reportError = $this->receiver->writeFile()) != 'success' ) {
            return $reportError;
        }
        # 判断文件传输完成
        if ( $this->receiver->chunkIndex === $this->receiver->chunkTotalCount ) {
            @unlink($this->receiver->uploadHead);

            if ( ! ($result['savedFilePath'] = $this->receiver->renameTempFile()) ) {
                return Responser::reportError('重命名文件失败', true, $this->receiver->uploadHead, $this->receiver->uploadPartialFile);
            }

            RedisHandler::setOneHash(pathinfo($this->receiver->savedFilePath, PATHINFO_FILENAME), $this->receiver->savedFilePath);

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