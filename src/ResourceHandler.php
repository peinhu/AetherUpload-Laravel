<?php

namespace Peinhu\AetherUpload;

class ResourceHandler extends \Illuminate\Routing\Controller
{
    public $config;
    public $configMapper;

    public function __construct(ConfigMapper $configMapper)
    {
        $this->configMapper = $configMapper;
        $this->middleware(config('aetherupload.groups.' . request()->route('group') . '.MIDDLEWARE_DISPLAY'), ['only' => ['displayResource']]);
        $this->middleware(config('aetherupload.groups.' . request()->route('group') . '.MIDDLEWARE_DOWNLOAD'), ['only' => ['downloadResource']]);

    }

    /**
     * display the uploaded file
     * @param $group
     * @param $resourceName
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function displayResource($group, $resourceName)
    {
        $this->config = $this->configMapper->getConfigByGroup($group);

        $uploadedFile = $this->config->UPLOAD_PATH . DIRECTORY_SEPARATOR . $this->config->UPLOAD_FILE_DIR . DIRECTORY_SEPARATOR . $resourceName;

        if ( ! is_file($uploadedFile) ) {
            abort(404);
        }

        return response()->download($uploadedFile, '', [], 'inline');
    }

    /**
     * download the uploaded file
     * @param $group
     * @param $resourceName
     * @param $newName
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadResource($group, $resourceName, $newName)
    {
        $this->config = $this->configMapper->getConfigByGroup($group);

        $uploadedFile = $this->config->UPLOAD_PATH . DIRECTORY_SEPARATOR . $this->config->UPLOAD_FILE_DIR . DIRECTORY_SEPARATOR . $resourceName;

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

        foreach ( config('aetherupload.groups') as $group ) {
            $headArr = scandir($group['UPLOAD_PATH'] . DIRECTORY_SEPARATOR . $group['UPLOAD_HEAD_DIR']);
            $fileArr = scandir($group['UPLOAD_PATH'] . DIRECTORY_SEPARATOR . $group['UPLOAD_FILE_DIR']);

            foreach ( $headArr as $head ) {
                $headFile = $group['UPLOAD_PATH'] . DIRECTORY_SEPARATOR . $group['UPLOAD_HEAD_DIR'] . DIRECTORY_SEPARATOR . $head;

                if ( ! file_exists($headFile) ) {
                    continue;
                }

                $createTime = substr(pathinfo($headFile, PATHINFO_BASENAME), 0, 10);

                if ( $createTime < $dueTime ) {
                    @unlink($headFile);
                }
            }

            foreach ( $fileArr as $file ) {
                $uploadFile = $group['UPLOAD_PATH'] . DIRECTORY_SEPARATOR . $group['UPLOAD_FILE_DIR'] . DIRECTORY_SEPARATOR . $file;

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


}