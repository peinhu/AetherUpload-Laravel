<?php

namespace Peinhu\AetherUpload;

class ResourceHandler
{
    static protected $UPLOAD_FILE_DIR;
    static protected $UPLOAD_HEAD_DIR;
    static protected $UPLOAD_PATH;

    public function __construct()
    {
        self::$UPLOAD_PATH = config('aetherupload.UPLOAD_PATH');
        self::$UPLOAD_FILE_DIR = config('aetherupload.UPLOAD_FILE_DIR');
        self::$UPLOAD_HEAD_DIR = config('aetherupload.UPLOAD_HEAD_DIR');
    }

    /**
     * display the uploaded file
     * @param $resourceName
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function displayResource($resourceName)
    {
        $uploadedFile = self::$UPLOAD_PATH . DIRECTORY_SEPARATOR . self::$UPLOAD_FILE_DIR . DIRECTORY_SEPARATOR . $resourceName;

        if ( ! is_file($uploadedFile) ) {
            abort(404);
        }

        return response()->download($uploadedFile, '', [], 'inline');
    }

    /**
     * download the uploaded file
     * @param $resourceName
     * @param $newName
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadResource($resourceName, $newName)
    {
        $uploadedFile = self::$UPLOAD_PATH . DIRECTORY_SEPARATOR . self::$UPLOAD_FILE_DIR . DIRECTORY_SEPARATOR . $resourceName;

        if ( ! is_file($uploadedFile) ) {
            abort(404);
        }

        return response()->download($uploadedFile, $newName, [], 'attachment');
    }

    /**
     * remove partial files which are created two days ago
     */
    public function cleanUpDir()
    {
        $dueTime = strtotime('-2 day');
        $headArr = scandir(self::$UPLOAD_PATH . DIRECTORY_SEPARATOR . self::$UPLOAD_HEAD_DIR);
        $uploadArr = scandir(self::$UPLOAD_PATH . DIRECTORY_SEPARATOR . self::$UPLOAD_FILE_DIR);

        foreach ( $headArr as $head ) {
            $headFile = self::$UPLOAD_PATH . DIRECTORY_SEPARATOR . self::$UPLOAD_HEAD_DIR . DIRECTORY_SEPARATOR . $head;

            if ( ! file_exists($headFile) ) {
                continue;
            }

            $createTime = substr(pathinfo($headFile, PATHINFO_BASENAME), 0, 10);

            if ( $createTime < $dueTime ) {
                @unlink($headFile);
            }
        }

        foreach ( $uploadArr as $upload ) {
            $uploadFile = self::$UPLOAD_PATH . DIRECTORY_SEPARATOR . self::$UPLOAD_FILE_DIR . DIRECTORY_SEPARATOR . $upload;

            if ( ! file_exists($uploadFile) || pathinfo($uploadFile, PATHINFO_EXTENSION) != 'part' ) {
                continue;
            }

            $createTime = substr(pathinfo($uploadFile, PATHINFO_BASENAME), 0, 10);

            if ( $createTime < $dueTime ) {
                @unlink($uploadFile);
            }
        }
    }

}