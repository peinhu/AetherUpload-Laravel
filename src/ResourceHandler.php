<?php

namespace AetherUpload;

class ResourceHandler extends \Illuminate\Routing\Controller
{

    public function __construct()
    {
        ConfigMapper::getInstance()->applyGroupConfig(request()->route('group'));
        $this->middleware(ConfigMapper::get('MIDDLEWARE_DISPLAY'))->only('displayResource');
        $this->middleware(ConfigMapper::get('MIDDLEWARE_DOWNLOAD'))->only('downloadResource');
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
        $uploadedFile = ConfigMapper::get('UPLOAD_PATH') . DIRECTORY_SEPARATOR . ConfigMapper::get('FILE_DIR') . DIRECTORY_SEPARATOR . $subDir . DIRECTORY_SEPARATOR . $resourceName;

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
        $uploadedFile = ConfigMapper::get('UPLOAD_PATH') . DIRECTORY_SEPARATOR . ConfigMapper::get('FILE_DIR') . DIRECTORY_SEPARATOR . $subDir . DIRECTORY_SEPARATOR . $resourceName;

        $ext = pathinfo($uploadedFile, PATHINFO_EXTENSION);

        if ( ! is_file($uploadedFile) ) {
            abort(404);
        }

        return response()->download($uploadedFile, $newName . '.' . $ext, [], 'attachment');
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
     * remove partial files which are created a few days ago
     */
    public static function cleanUpDir()
    {
        $dueTime = strtotime('-2 day');
        $uploadPath = config('aetherupload.UPLOAD_PATH');
        $headDir = config('aetherupload.HEAD_DIR');
        $headFileNames = scandir($uploadPath . DIRECTORY_SEPARATOR . $headDir);

        foreach ( $headFileNames as $headFileName ) {
            $headFile = $uploadPath . DIRECTORY_SEPARATOR . $headDir . DIRECTORY_SEPARATOR . $headFileName;

            if ( pathinfo($headFile, PATHINFO_EXTENSION) != 'head' ) {
                continue;
            }

            $createTime = substr(pathinfo($headFile, PATHINFO_BASENAME), 0, 10);

            if ( $createTime < $dueTime ) {
                @unlink($headFile);
            }
        }

        $groupNames = array_keys(config('aetherupload.GROUPS'));

        foreach ( $groupNames as $groupName ) {
            $subDirNames = scandir($uploadPath . DIRECTORY_SEPARATOR . $groupName);

            foreach ( $subDirNames as $subDirName ) {
                $subDir = $uploadPath . DIRECTORY_SEPARATOR . $groupName . DIRECTORY_SEPARATOR . $subDirName;

                if ( $subDirName === '.' || $subDirName === '..' || ! is_dir($subDir) ) {
                    continue;
                }

                $fileNames = scandir($subDir);

                foreach ( $fileNames as $fileName ) {
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