<?php

namespace AetherUpload;

use App\User;

class UploadController extends \Illuminate\Routing\Controller
{
    private $partialFileHandler;
    private $headerHandler;

    public function __construct(PartialFileHandler $partialFileHandler, HeaderHandler $headerHandler)
    {
        \App::setLocale(request('locale'));
        $this->partialFileHandler = $partialFileHandler;
        $this->headerHandler = $headerHandler;
        ConfigMapper::getInstance()->applyGroupConfig(request('group'));
        $this->middleware(ConfigMapper::get('MIDDLEWARE_PREPROCESS'))->only('preprocess');
        $this->middleware(ConfigMapper::get('MIDDLEWARE_SAVE_CHUNK'))->only('saveChunk');
    }

    /**
     * preprocess the upload request
     * @return \Illuminate\Http\JsonResponse
     */
    public function preprocess()
    {
        $fileName = request('file_name', 0);
        $fileSize = request('file_size', 0);
        $fileHash = request('file_hash', 0);
        $result = [
            'error'          => 0,
            'chunkSize'      => ConfigMapper::get('CHUNK_SIZE'),
            'subDir'         => ConfigMapper::get('FILE_SUB_DIR'),
            'uploadBaseName' => '',
            'uploadExt'      => '',
            'savedPath'      => '',
        ];

        if ( ! ($fileName && $fileSize) ) {
            return Responser::reportError(trans('aetherupload::messages.invalid_file_params'));
        }

        if ( $error = $this->filterBySize($fileSize) ) {
            return Responser::reportError($error);
        }

        if ( $error = $this->filterByExt($uploadExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION))) ) {
            return Responser::reportError($error);
        }

        # 检测是否可以秒传
        if ( $fileHash && FileHashHandler::hashExists($fileHash) ) {
            $result['savedPath'] = FileHashHandler::getFilePathByHash($fileHash);

            return Responser::returnResult($result);
        }

        # 创建文件子目录
        if ( ! is_dir($uploadFileSubFolderPath = $this->partialFileHandler->getUploadFileSubFolderPath()) ) {
            @mkdir($uploadFileSubFolderPath, 0755);
        }

        # 预创建头文件
        if ( $error = $this->headerHandler->createHeader($uploadBaseName = $this->generateTempFileName()) ) {
            return Responser::reportError($error);
        }

        # 预创建文件
        if ( $error = $this->partialFileHandler->createFile($uploadBaseName, $uploadExt) ) {
            return Responser::reportError($error);
        }

        $result['uploadExt'] = $uploadExt;
        $result['uploadBaseName'] = $uploadBaseName;

        return Responser::returnResult($result);
    }

    /**
     * handle and save the uploaded data
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveChunk()
    {
        UploadInfo::instance()->chunkTotalCount = request('chunk_total', 0);# 分片总数
        UploadInfo::instance()->chunkIndex = request('chunk_index', 0);# 当前分片号
        UploadInfo::instance()->uploadBaseName = request('upload_basename', 0);# 文件临时名
        UploadInfo::instance()->uploadExt = request('upload_ext', 0); # 文件扩展名
        UploadInfo::instance()->file = request()->file('file', 0);# 文件
        UploadInfo::instance()->subDir = request('sub_dir', 0);# 子目录名
        UploadInfo::instance()->uploadPartialFile = $this->partialFileHandler->getUploadPartialFilePath(UploadInfo::uploadBaseName(),UploadInfo::uploadExt());

        $result = [
            'error'     => 0,
            'savedPath' => '',
        ];

        if ( ! (UploadInfo::chunkTotalCount() && UploadInfo::chunkIndex() && UploadInfo::uploadExt() && UploadInfo::uploadBaseName() && UploadInfo::subDir()) ) {
            return Responser::reportError(trans('aetherupload::messages.invalid_chunk_params'));
        }
        # 防止被人为跳过验证过程直接调用保存方法，从而上传恶意文件
        if ( ! is_file(UploadInfo::uploadPartialFile()) ) {
            return Responser::reportError(trans('aetherupload::messages.invalid_operation'));
        }

        if ( UploadInfo::file()->getError() > 0 ) {
            return Responser::reportError(UploadInfo::file()->getErrorMessage());
        }

        if ( ! UploadInfo::file()->isValid() ) {
            return Responser::reportError(trans('aetherupload::messages.http_post_only'));
        }
        # 头文件指针验证，防止断线造成的重复传输某个文件块
        if ( $this->headerHandler->readHeader(UploadInfo::uploadBaseName()) != UploadInfo::chunkIndex() - 1 ) {
            return Responser::returnResult($result);
        }
        # 写入数据到预创建的文件
        if ( $error = $this->partialFileHandler->appendFile() ) {
            return Responser::reportError($error, true, $this->fileHandler->uploadHead, $this->fileHandler->uploadPartialFile);
        }
        # 判断文件传输完成
        if ( $this->fileHandler->chunkIndex === $this->fileHandler->chunkTotalCount ) {
            @unlink($this->fileHandler->uploadHead);
            # 触发上传完成前事件
            if ( ! empty($beforeUploadCompleteEvent = ConfigMapper::get('EVENT_BEFORE_UPLOAD_COMPLETE')) ) {
                event(new $beforeUploadCompleteEvent($this->fileHandler));
            }

            if ( ! ($result['savedPath'] = $this->fileHandler->renameTempFile()) ) {
                return Responser::reportError(trans('aetherupload::messages.rename_file_fail'), true, $this->fileHandler->uploadHead, $this->fileHandler->uploadPartialFile);
            }

            FileHashHandler::setOneHash(pathinfo($this->fileHandler->savedPath, PATHINFO_FILENAME), $this->fileHandler->savedPath);
            # 触发上传完成事件
            if ( ! empty($uploadCompleteEvent = ConfigMapper::get('EVENT_UPLOAD_COMPLETE')) ) {
                event(new $uploadCompleteEvent($this->fileHandler));
            }

        }

        return Responser::returnResult($result);
    }

    /**
     * @param $fileSize
     * @return bool|string
     */
    public function filterBySize($fileSize)
    {
        $MAXSIZE = ConfigMapper::get('FILE_MAXSIZE') * 1000 * 1000;
        # 文件大小过滤
        if ( $fileSize > $MAXSIZE && $MAXSIZE != 0 ) {
            return trans('aetherupload::messages.invalid_file_size');
        }

        return false;
    }

    /**
     * @param $uploadExt
     * @return bool|string
     */
    public function filterByExt($uploadExt)
    {
        $EXTENSIONS = ConfigMapper::get('FILE_EXTENSIONS');
        # 文件类型过滤
        if ( ($EXTENSIONS != '' && ! in_array($uploadExt, explode(',', $EXTENSIONS))) || in_array($uploadExt, static::getDangerousExtList()) ) {
            return trans('aetherupload::messages.invalid_file_type');
        }

        return false;
    }

    /**
     * get the extensions that may harm a server
     * @return array
     */
    private static function getDangerousExtList()
    {
        return ['php', 'part', 'html', 'shtml', 'htm', 'shtm', 'js', 'jsp', 'asp', 'java', 'py', 'sh', 'bat', 'exe', 'dll', 'cgi', 'htaccess', 'reg', 'aspx', 'vbs'];
    }

    public function generateTempFileName()
    {
        return time() . mt_rand(100, 999);
    }


}