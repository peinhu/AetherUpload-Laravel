<?php

namespace AetherUpload;

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

        try {

            if ( ! ($fileName && $fileSize) ) {
                throw new \Exception(trans('aetherupload::messages.invalid_file_params'));
            }

            # 文件大小过滤
            $this->filterBySize($fileSize);

            # 文件类型过滤
            $this->filterByExt($uploadExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)));

            # 检测是否可以秒传
            if ( $fileHash && FileHashHandler::hashExists($fileHash) ) {
                $result['savedPath'] = FileHashHandler::getFilePathByHash($fileHash);

                return Responser::returnResult($result);
            }

            # 创建文件子目录
            if ( ! is_dir($this->partialFileHandler->getUploadFileSubFolderPath()) ) {
                $this->partialFileHandler->createUploadFileSubFolder();
            }

            # 预创建头文件
            $this->headerHandler->createHeader($uploadBaseName = $this->generateTempFileName());

            # 预创建文件
            $this->partialFileHandler->createFile($uploadBaseName, $uploadExt);

        } catch ( \Exception $e ) {

            return Responser::reportError($e->getMessage());
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
        UploadInfo::instance()->uploadPartialFile = $this->partialFileHandler->getUploadPartialFilePath(UploadInfo::uploadBaseName(), UploadInfo::uploadExt());

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
        # 更新预创建的头文件
        if ( $error = $this->headerHandler->writeHeader(UploadInfo::uploadBaseName(), UploadInfo::chunkIndex()) ) {
            return Responser::reportError($error);
        }

        # 追加数据到预创建的文件
        if ( $error = $this->partialFileHandler->appendFile() ) {
            return Responser::reportError($error);
        }
        # 判断文件传输完成
        if ( UploadInfo::chunkIndex() === UploadInfo::chunkTotalCount() ) {
            $this->headerHandler->deleteHeader(UploadInfo::uploadBaseName());
            # 触发上传完成前事件
            if ( ! empty($beforeUploadCompleteEvent = ConfigMapper::get('EVENT_BEFORE_UPLOAD_COMPLETE')) ) {
                event(new $beforeUploadCompleteEvent($this->partialFileHandler));
            }

            if ( ! (UploadInfo::instance()->savedPath = $this->partialFileHandler->renameTempFile()) ) {

                return Responser::reportError(trans('aetherupload::messages.rename_file_fail'));
            }

            FileHashHandler::setOneHash(pathinfo(UploadInfo::savedPath(), PATHINFO_FILENAME), UploadInfo::savedPath());
            # 触发上传完成事件
            if ( ! empty($uploadCompleteEvent = ConfigMapper::get('EVENT_UPLOAD_COMPLETE')) ) {
                event(new $uploadCompleteEvent($this->partialFileHandler));
            }

            $result['savedPath'] = UploadInfo::savedPath();

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
     * get the file extensions that may harm a server
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