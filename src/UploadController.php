<?php

namespace AetherUpload;

use Illuminate\Support\Facades\Request;

class UploadController extends \App\Http\Controllers\Controller
{

    public function __construct()
    {
        if ( Request::exists('group') ) {
            \App::setLocale(Request::input('locale'));
            ConfigMapper::instance()->applyGroupConfig(Request::input('group'));
            $this->middleware(ConfigMapper::get('middleware_preprocess'))->only('preprocess');
            $this->middleware(ConfigMapper::get('middleware_save_chunk'))->only('saveChunk');
        }
        // Determine if the distributed deployment is enabled and the server's role is set to storage
        if ( ConfigMapper::get('distributed_deployment_enable') === true && ConfigMapper::get('distributed_deployment_role') === 'storage' ) {
            $this->middleware(ConfigMapper::get('distributed_deployment_middleware_cors'))->only(['preprocess', 'saveChunk', 'options']);
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

        $result = [
            'error'                => 0,
            'chunkSize'            => ConfigMapper::get('chunk_size'),
            'groupSubdir'          => '',
            'resourceTempBaseName' => '',
            'resourceExt'          => '',
            'savedPath'            => '',
        ];

        try {

            // Determine if the distributed deployment is enabled and the server's role is set to web
            if ( ConfigMapper::get('distributed_deployment_enable') === true && ConfigMapper::get('distributed_deployment_role') === 'web' ) {
                throw new \Exception(trans('aetherupload::messages.upload_error'));
            }

            if ( $resourceSize === false || $resourceName === false ) {
                throw new \Exception(trans('aetherupload::messages.invalid_resource_params'));
            }

            $result['resourceTempBaseName'] = $resourceTempBaseName = Util::generateTempName();
            $result['resourceExt'] = $resourceExt = strtolower(pathinfo($resourceName, PATHINFO_EXTENSION));
            $result['groupSubdir'] = $resourceSubDirName = Util::generateSubDirName();

            $partialResource = new PartialResource($resourceTempBaseName, $resourceExt, $resourceSubDirName);

            $partialResource->filterBySize($resourceSize);

            $partialResource->filterByExtension($resourceExt);

            // Determine if this upload meets the condition of instant completion
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
        $groupSubdir = Request::input('group_subdir', false);
        $resourceHash = Request::input('resource_hash', false);
        $partialResource = new PartialResource($resourceTempBaseName, $resourceExt, $groupSubdir);

        $result = [
            'error'     => 0,
            'savedPath' => '',
        ];

        try {

            if ( $chunkTotalCount === false || $chunkIndex === false || $resourceExt === false || $resourceTempBaseName === false || $groupSubdir === false || $chunk === false || $resourceHash === false ) {
                throw new \Exception(trans('aetherupload::messages.invalid_chunk_params'));
            }

            // Do a check to prevent security intrusions
            if ( $partialResource->exists() === false ) {
                throw new \Exception(trans('aetherupload::messages.invalid_operation'));
            }

            // Determine if this upload meets the condition of instant completion
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

            // Validate the data in header file to avoid the errors when network issue occurs
            if ( (int)($partialResource->chunkIndex) !== (int)$chunkIndex - 1 ) {
                return Responser::returnResult($result);
            }

            $partialResource->append($chunk->getRealPath());

            $partialResource->chunkIndex = $chunkIndex;

            // Determine if the resource file is completed
            if ( $chunkIndex === $chunkTotalCount ) {

                $partialResource->checkSize();

                $partialResource->checkMimeType();

                // Trigger the event before an upload completes
                if ( empty($beforeUploadCompleteEvent = ConfigMapper::get('event_before_upload_complete')) === false ) {
                    event(new $beforeUploadCompleteEvent($partialResource));
                }

                $resourceHash = $partialResource->calculateHash();

                $partialResource->rename($completeName = Util::getFileName($resourceHash, $resourceExt));

                $resource = new Resource($completeName, $groupSubdir);

                RedisSavedPath::set($resourceHash, $savedPath = $resource->getSavedPath());

                unset($partialResource->chunkIndex);

                // Trigger the event when an upload completes
                if ( empty($uploadCompleteEvent = ConfigMapper::get('event_upload_complete')) === false ) {
                    event(new $uploadCompleteEvent($resource));
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
        \Illuminate\Support\Facades\Config::set('session.driver', 'array');

        return response('');
    }


}