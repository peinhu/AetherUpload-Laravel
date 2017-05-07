<?php

namespace Peinhu\AetherUpload;

class ResourceHandler extends \Illuminate\Routing\Controller
{
    public $config;

    public function __construct()
    {
        $group = request()->route('group');
        $this->config = ConfigMapper::getInstance()->applyConfigByGroup($group);
        $this->middleware($this->config->get('MIDDLEWARE_DISPLAY'))->only('displayResource');
        $this->middleware($this->config->get('MIDDLEWARE_DOWNLOAD'))->only('downloadResource');
    }

    /**
     * display the uploaded file
     * @param $group
     * @param $subDir
     * @param $resourceName
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function displayResource($group, $subDir, $resourceName)
    {
        $uploadedFile = $this->config->get('UPLOAD_PATH') . DIRECTORY_SEPARATOR . $this->config->get('FILE_DIR') . DIRECTORY_SEPARATOR . $subDir . DIRECTORY_SEPARATOR . $resourceName;

        if ( ! is_file($uploadedFile) ) {
            abort(404);
        }

        return response()->download($uploadedFile, '', [], 'inline');
    }

    /**
     * download the uploaded file
     * @param $group
     * @param $subDir
     * @param $resourceName
     * @param $newName
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadResource($group, $subDir, $resourceName, $newName)
    {
        $uploadedFile = $this->config->get('UPLOAD_PATH') . DIRECTORY_SEPARATOR . $this->config->get('FILE_DIR') . DIRECTORY_SEPARATOR . $subDir . DIRECTORY_SEPARATOR . $resourceName;

        if ( ! is_file($uploadedFile) ) {
            abort(404);
        }

        return response()->download($uploadedFile, $newName, [], 'attachment');
    }

    /**
     * get the absolute path of the uploaded file on disk
     * @param $savedPath
     * @return string
     */
    public static function getResourcePath($savedPath)
    {
        return config('aetherupload.UPLOAD_PATH') . DIRECTORY_SEPARATOR . $savedPath;
    }

    /**
     * remove partial files which are created two days ago
     */
    public function cleanUpDir()
    {
        $dueTime = strtotime('-2 day');
        $headFileNameArr = scandir($this->config->get('UPLOAD_PATH') . DIRECTORY_SEPARATOR . $this->config->get('HEAD_DIR'));

        foreach ( $headFileNameArr as $headFileName ) {
            $headFile = $this->config->get('UPLOAD_PATH') . DIRECTORY_SEPARATOR . $this->config->get('HEAD_DIR') . DIRECTORY_SEPARATOR . $headFileName;

            if ( pathinfo($headFile, PATHINFO_EXTENSION) != 'head' ) {
                continue;
            }

            $createTime = substr(pathinfo($headFile, PATHINFO_BASENAME), 0, 10);

            if ( $createTime < $dueTime ) {
                @unlink($headFile);
            }
        }

        $groupNameArr = array_keys(config('aetherupload.GROUPS'));

        foreach ( $groupNameArr as $groupName ) {
            $subDirNameArr = scandir($this->config->get('UPLOAD_PATH') . DIRECTORY_SEPARATOR . $groupName);

            foreach ( $subDirNameArr as $subDirName ) {
                $subDir = $this->config->get('UPLOAD_PATH') . DIRECTORY_SEPARATOR . $groupName . DIRECTORY_SEPARATOR . $subDirName;

                if ( $subDirName === '.' || $subDirName === '..' || ! is_dir($subDir) ) {
                    continue;
                }

                $fileNameArr = scandir($subDir);

                foreach ( $fileNameArr as $fileName ) {
                    $uploadedFile = $subDir . DIRECTORY_SEPARATOR . $fileName;

                    if ( $fileName === '.' || $fileName === '..' || pathinfo($uploadedFile, PATHINFO_EXTENSION) != 'part' ) {
                        continue;
                    }

                    $createTime = substr(pathinfo($uploadedFile, PATHINFO_BASENAME), 0, 10);

                    if ( $createTime < $dueTime ) {
                        @unlink($uploadedFile);
                    }
                }
            }

        }

    }


}