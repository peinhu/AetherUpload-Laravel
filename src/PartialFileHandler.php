<?php

namespace AetherUpload;

class PartialFileHandler
{

    public function createFile($uploadBaseName, $uploadExt)
    {
        if ( ! @touch($this->getUploadPartialFilePath($uploadBaseName, $uploadExt))) {
            return trans('aetherupload::messages.create_file_fail');
        }

        return false;
    }

    public function appendFile()
    {
        if ( ! @file_put_contents(UploadInfo::uploadPartialFile(), @file_get_contents(UploadInfo::uploadExt()->getRealPath()), FILE_APPEND)){

            return trans('aetherupload::messages.write_file_fail');
        }

        return false;
    }

    public function readFile()
    {
        if ( ! @file_get_contents($this->getUploadPartialFilePath(UploadInfo::uploadBaseName(), UploadInfo::uploadExt()))) {
            return trans('aetherupload::messages.read_file_fail');
        }

        return false;
    }

    public function deleteFile()
    {
        if ( ! @unlink($this->getUploadPartialFilePath(UploadInfo::uploadBaseName(), UploadInfo::uploadExt()))) {
            return trans('aetherupload::messages.delete_file_fail');
        }

        return false;
    }

//???
    public function renameTempFile()
    {
        $savedFileHash = $this->calculateSavedFileHash($this->getUploadPartialFilePath(UploadInfo::uploadBaseName(), UploadInfo::uploadExt()));

        if ( FileHashHandler::hashExists($savedFileHash) ) {
            $savedPath = FileHashHandler::getFilePathByHash($savedFileHash);
        } else {
            $savedPath = $this->getSavedPath($savedFileHash, UploadInfo::uploadExt());

            if ( ! @rename(UploadInfo::uploadPartialFile(), $this->getUploadFilePath($savedFileHash,UploadInfo::uploadExt())) ) {
                return false;
            }
        }

        return $savedPath;
    }

    public function getUploadFileSubFolderPath()
    {
        return ConfigMapper::get('UPLOAD_PATH') . DIRECTORY_SEPARATOR . ConfigMapper::get('FILE_DIR') . DIRECTORY_SEPARATOR . ConfigMapper::get('FILE_SUB_DIR');
    }

    public function getSavedPath($uploadBaseName, $uploadExt)
    {
        return ConfigMapper::get('FILE_DIR') . DIRECTORY_SEPARATOR . ConfigMapper::get('FILE_SUB_DIR') . DIRECTORY_SEPARATOR . $uploadBaseName . '.' . $uploadExt;
    }

    public function getUploadPartialFilePath($uploadBaseName, $uploadExt)
    {
        return ConfigMapper::get('UPLOAD_PATH') . DIRECTORY_SEPARATOR . ConfigMapper::get('FILE_DIR') . DIRECTORY_SEPARATOR . ConfigMapper::get('FILE_SUB_DIR') . DIRECTORY_SEPARATOR . $uploadBaseName . '.' . $uploadExt . '.part';
    }

    public function getUploadFilePath($uploadBaseName, $uploadExt)
    {
        return ConfigMapper::get('UPLOAD_PATH') . DIRECTORY_SEPARATOR . ConfigMapper::get('FILE_DIR') . DIRECTORY_SEPARATOR . ConfigMapper::get('FILE_SUB_DIR') . DIRECTORY_SEPARATOR . $uploadBaseName . '.' . $uploadExt;
    }

    protected function calculateSavedFileHash($savedFile)
    {
        return md5_file($savedFile);
    }



}