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
        $resourceName = Request::input('resource_name', false);
        $resourceSize = Request::input('resource_size', false);
        $resourceHash = Request::input('resource_hash', false);
        $group = Request::input('group', false);

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

            if ( $resourceSize === false || $resourceName === false || $group === false ) {
                throw new \Exception(trans('aetherupload::messages.invalid_resource_params'));
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
            if ( $resourceHash !== false && RedisSavedPath::exists($resourceHash) ) {
                $result['savedPath'] = RedisSavedPath::get($resourceHash);

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
        $chunkTotalCount = Request::input('chunk_total', false);
        $chunkIndex = Request::input('chunk_index', false);
        $resourceTempBaseName = Request::input('resource_temp_basename', false);
        $resourceExt = Request::input('resource_ext', false);
        $chunk = Request::file('resource_chunk', false);
        $groupSubDir = Request::input('group_subdir', false);
        $resourceHash = Request::input('resource_hash', false);
        $group = Request::input('group', false);
        $partialResource = null;

        $result = [
            'error'     => 0,
            'savedPath' => '',
        ];

        try {

            if ( $chunkTotalCount === false || $chunkIndex === false || $resourceExt === false || $resourceTempBaseName === false || $groupSubDir === false || $chunk === false || $resourceHash === false || $group === false ) {
                throw new \Exception(trans('aetherupload::messages.invalid_chunk_params'));
            }

            ConfigMapper::instance()->applyGroupConfig($group);

            $partialResource = new PartialResource($resourceTempBaseName, $resourceExt, $groupSubDir);

            // do a check to prevent security intrusions
            if ( $partialResource->exists() === false ) {
                throw new \Exception(trans('aetherupload::messages.invalid_operation'));
            }

            // determine if this upload meets the condition of instant completion
            if ( $resourceHash !== false && RedisSavedPath::exists($resourceHash) ) {
                $partialResource->delete();
                unset($partialResource->chunkIndex);
                $result['savedPath'] = RedisSavedPath::get($resourceHash);

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
                if ( empty($beforeUploadCompleteEvent = ConfigMapper::get('event_before_upload_complete')) === false ) {
                    event(new $beforeUploadCompleteEvent($partialResource));
                }

                $resourceHash = $partialResource->calculateHash();

                $partialResource->rename($completeName = Util::getFileName($resourceHash, $resourceExt));

                $resource = new Resource($completeName, $groupSubDir);

                RedisSavedPath::set($resourceHash, $savedPath = $resource->getSavedPath());

                unset($partialResource->chunkIndex);

                // trigger the event when an upload completes
                if ( empty($uploadCompleteEvent = ConfigMapper::get('event_upload_complete')) === false ) {
                    event(new $uploadCompleteEvent($resource));
                }

                $result['savedPath'] = $savedPath;

            }

            return Responser::returnResult($result);

        } catch ( \Exception $e ) {

            $partialResource->delete();
            unset($partialResource->chunkIndex);

            return Responser::reportError($result, $e->getMessage());
        }

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