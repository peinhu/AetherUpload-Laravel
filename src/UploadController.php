<?php

namespace AetherUpload;

use Illuminate\Support\Facades\Request;

class UploadController extends \App\Http\Controllers\Controller
{

    public function __construct()
    {
        \App::setLocale(Request::input('locale', 'en'));

        // add AetherUploadCORS middleware to the storage server when distributed deployment is enabled
        if ( Util::isDistributedStorageHost() ) {
            $this->middleware(ConfigMapper::get('distributed_deployment_middleware_cors'));
        }
    }

    /**
     * Preprocess the upload request
     * @return \Illuminate\Http\JsonResponse
     */
    public function preprocess()
    {
        $this->validate(request(), [
            'resource_name' => 'required',
            'resource_size' => 'required',
            'group'         => 'required',
            'resource_hash' => 'present',
        ]);

        $resourceName = Request::input('resource_name');
        $resourceSize = Request::input('resource_size');
        $resourceHash = Request::input('resource_hash');
        $group = Request::input('group');

        $result = [
            'error'                => 0,
            'chunkSize'            => 0,
            'groupSubDir'          => '',
            'resourceTempBaseName' => '',
            'resourceExt'          => '',
            'savedPath'            => '',
        ];

        try {

            // prevents uploading files to the application server when distributed deployment is enabled
            if ( Util::isDistributedWebHost() ) {
                throw new \Exception(trans('aetherupload::messages.upload_error'));
            }

            ConfigMapper::instance()->applyGroupConfig($group);

            $result['resourceTempBaseName'] = $resourceTempBaseName = Util::generateTempName();
            $result['resourceExt'] = $resourceExt = strtolower(pathinfo($resourceName, PATHINFO_EXTENSION));
            $result['groupSubDir'] = $groupSubDir = Util::generateSubDirName();
            $result['chunkSize'] = ConfigMapper::get('chunk_size');

            $partialResource = new PartialResource($resourceTempBaseName, $resourceExt, $groupSubDir);

            $partialResource->filterBySize($resourceSize);

            $partialResource->filterByExtension($resourceExt);

            // determine if this upload meets the condition of instant completion
            if ( ConfigMapper::get('instant_completion') === true && !empty($resourceHash) && RedisSavedPath::exists($savedPathKey = RedisSavedPath::getKey($group, $resourceHash)) === true ) {
                $result['savedPath'] = RedisSavedPath::get($savedPathKey);

                return Responser::returnResult($result);
            }

            $partialResource->create();

            $partialResource->chunkIndex = 0;

        } catch ( \Exception $e ) {

            return Responser::reportError($result, $e->getMessage());
        }

        return Responser::returnResult($result);
    }

    /**
     * Handle and save the uploaded chunks
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function saveChunk()
    {
        $this->validate(request(), [
            'chunk_total'            => 'required',
            'chunk_index'            => 'required',
            'resource_temp_basename' => 'required',
            'resource_ext'           => 'required',
            'resource_chunk'         => 'required',
            'group_subdir'           => 'required',
            'group'                  => 'required',
            'resource_hash'          => 'present',
        ]);

        $chunkTotalCount = Request::input('chunk_total');
        $chunkIndex = Request::input('chunk_index');
        $resourceTempBaseName = Request::input('resource_temp_basename');
        $resourceExt = Request::input('resource_ext');
        $chunk = Request::file('resource_chunk');
        $groupSubDir = Request::input('group_subdir');
        $resourceHash = Request::input('resource_hash');
        $group = Request::input('group');
        $savedPathKey = RedisSavedPath::getKey($group, $resourceHash);
        $partialResource = null;

        $result = [
            'error'     => 0,
            'savedPath' => '',
        ];

        try {

            ConfigMapper::instance()->applyGroupConfig($group);

            $partialResource = new PartialResource($resourceTempBaseName, $resourceExt, $groupSubDir);

            // do a check to prevent security intrusions
            if ( $partialResource->exists() === false ) {
                throw new \Exception(trans('aetherupload::messages.invalid_operation'));
            }

            // determine if this upload meets the condition of instant completion
            if ( ConfigMapper::get('instant_completion') === true && !empty($resourceHash) && RedisSavedPath::exists($savedPathKey) === true ) {
                $partialResource->delete();
                unset($partialResource->chunkIndex);
                $result['savedPath'] = RedisSavedPath::get($savedPathKey);

                return Responser::returnResult($result);
            }

            if ( $chunk->getError() > 0 ) {
                throw new \Exception(trans('aetherupload::messages.upload_error'));
            }

            if ( $chunk->isValid() === false ) {
                throw new \Exception(trans('aetherupload::messages.http_post_only'));
            }

            // validate the data in header file to avoid the errors when network issue occurs
            if ( (int)($partialResource->chunkIndex) !== (int)$chunkIndex - 1 ) {
                return Responser::returnResult($result);
            }

            $partialResource->append($chunk->getRealPath());

            $partialResource->chunkIndex = $chunkIndex;

            // determine if the resource file is completed
            if ( $chunkIndex === $chunkTotalCount ) {

                $partialResource->checkSize();

                $partialResource->checkMimeType();

                // trigger the event before an upload completes
                if ( !empty($beforeUploadCompleteEvent = ConfigMapper::get('event_before_upload_complete'))) {
                    event(new $beforeUploadCompleteEvent($partialResource));
                }

                $resourceRealHash = $partialResource->calculateHash();

                if ( ConfigMapper::get('lax_mode') === false && $resourceHash !== $resourceRealHash ) {
                    throw new \Exception(trans('aetherupload::messages.upload_error'));
                }

                $partialResource->rename($completeName = Util::getFileName($resourceRealHash, $resourceExt));

                $savedPath = SavedPathResolver::encode($group, $groupSubDir, $completeName);

                if ( ConfigMapper::get('instant_completion') === true ) {
                    RedisSavedPath::set($savedPathKey, $savedPath);
                }

                unset($partialResource->chunkIndex);

                // trigger the event when an upload completes
                if ( !empty($uploadCompleteEvent = ConfigMapper::get('event_upload_complete'))) {
                    event(new $uploadCompleteEvent(new Resource($group, ConfigMapper::get('group_dir'), $groupSubDir, $completeName)));
                }

                $result['savedPath'] = $savedPath;

            }

        } catch ( \Exception $e ) {

            $partialResource->delete();
            unset($partialResource->chunkIndex);

            return Responser::reportError($result, $e->getMessage());
        }

        return Responser::returnResult($result);

    }

    /**
     * Handle the request of option method in CORS
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function options()
    {
        return response('');
    }


}
