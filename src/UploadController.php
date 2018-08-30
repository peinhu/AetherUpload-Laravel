<?php

namespace AetherUpload;

class UploadController extends \Illuminate\Routing\Controller
{
    private $resourceHandler;
    private $headerHandler;
    private $group;

    public function __construct(ResourceHandler $resourceHandler, HeaderHandler $headerHandler)
    {
        $this->resourceHandler = $resourceHandler;
        $this->headerHandler = $headerHandler;
        ConfigMapper::getInstance()->applyGroupConfig($this->group = request('aetherupload_group', 'file'));
        if ( ConfigMapper::get('DISTRIBUTED_DEPLOYMENT_ENABLE') === true && ConfigMapper::get('DISTRIBUTED_DEPLOYMENT_ROLE') === "storage" ) {
            $this->middleware(ConfigMapper::get('DISTRIBUTED_DEPLOYMENT_MIDDLEWARE_CORS'))->only(['preprocess', 'saveChunk', 'options']);
        }
        $this->middleware(ConfigMapper::get('MIDDLEWARE_PREPROCESS'))->only('preprocess');
        $this->middleware(ConfigMapper::get('MIDDLEWARE_SAVE_CHUNK'))->only('saveChunk');
    }

    /**
     * Preprocess the upload request
     * @return \Illuminate\Http\JsonResponse
     */
    public function preprocess()
    {
        $resourceName = request('aetherupload_resource_name', false);
        $resourceSize = request('aetherupload_resource_size', false);
        $resourceHash = request('aetherupload_resource_hash', false);

        $result = [
            'error'                => 0,
            'chunkSize'            => ConfigMapper::get('CHUNK_SIZE'),
            'resourceSubDir'       => "",
            'resourceTempBaseName' => "",
            'resourceExt'          => "",
            'savedPath'            => "",
        ];

        try {

            if ( ConfigMapper::get('DISTRIBUTED_DEPLOYMENT_ENABLE') === true && ConfigMapper::get('DISTRIBUTED_DEPLOYMENT_ROLE') === "web" ) {
                throw new \Exception(trans('aetherupload::messages.upload_error'));
            }

            if ( $resourceSize === false || $resourceName === false ) {
                throw new \Exception(trans('aetherupload::messages.invalid_resource_params'));
            }

            $this->filterBySize($resourceSize);

            $this->filterByExt($resourceExt = strtolower(pathinfo($resourceName, PATHINFO_EXTENSION)));

            # Determine if this upload meets the condition of instant completion
            if ( $resourceHash !== false && ResourceHashHandler::hashExists($this->group.$resourceHash) ) {
                $result['savedPath'] = ResourceHashHandler::getPathByHash($this->group.$resourceHash);

                return Responser::returnResult($result);
            }

            # Create header file
            $this->headerHandler->createHeader($resourceTempBaseName = $this->generateTempName());

            # Create resource file
            $this->resourceHandler->createResource($this->resourceHandler->getResourceName($resourceTempBaseName, $resourceExt), $resourceSubDirName = $this->resourceHandler->generateResourceSubDirName(), ConfigMapper::get('RESOURCE_DIR'));

        } catch ( \Exception $e ) {

            return Responser::reportError($result, $e->getMessage());
        }

        $result['resourceSubDir'] = $resourceSubDirName;
        $result['resourceExt'] = $resourceExt;
        $result['resourceTempBaseName'] = $resourceTempBaseName;

        return Responser::returnResult($result);
    }

    /**
     * Handle and save the uploaded chunks
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveChunk()
    {
        $chunkTotalCount = request('aetherupload_chunk_total', false);
        $chunkIndex = request('aetherupload_chunk_index', false);
        $resourceTempBaseName = request('aetherupload_resource_temp_basename', false);
        $resourceExt = request('aetherupload_resource_ext', false);
        $resource = request()->file('aetherupload_resource', false);
        $resourceSubDir = request('aetherupload_sub_dir', false);
        $resourceHash = request('aetherupload_resource_hash', false);
        $resourceTempName = $this->resourceHandler->getResourceName($resourceTempBaseName, $resourceExt);
        $group = ConfigMapper::get('RESOURCE_DIR');

        $result = [
            'error'     => 0,
            'savedPath' => '',
        ];

        try {

            if ( $chunkTotalCount === false || $chunkIndex === false || $resourceExt === false || $resourceTempBaseName === false || $resourceSubDir === false || $resource === false || $resourceHash === false ) {
                throw new \Exception(trans('aetherupload::messages.invalid_chunk_params'));
            }

            # Do a check of preventing security intrusions
            if ( $this->resourceHandler->partialResourceExists($resourceTempName, $resourceSubDir, $group) === false ) {
                throw new \Exception(trans('aetherupload::messages.invalid_operation'));
            }

            # Determine if this upload meets the condition of instant completion
            if ( $resourceHash !== false && ResourceHashHandler::hashExists($this->group.$resourceHash) ) {
                $this->headerHandler->deleteHeader($resourceTempBaseName);
                $this->resourceHandler->deleteResource($resourceTempName, $resourceSubDir, $group);
                $result['savedPath'] = ResourceHashHandler::getPathByHash($this->group.$resourceHash);

                return Responser::returnResult($result);
            }

            if ( $resource->getError() > 0 ) {
                throw new \Exception(trans('aetherupload::messages.upload_error'));
            }

            if ( $resource->isValid() === false ) {
                throw new \Exception(trans('aetherupload::messages.http_post_only'));
            }

            # Validate the data in header file to avoid the errors when network issue occurs
            if ( intval($this->headerHandler->readHeader($resourceTempBaseName)) !== $chunkIndex - 1 ) {
                return Responser::returnResult($result);
            }

            # Write data to the header file
            $this->headerHandler->writeHeader($resourceTempBaseName, $chunkIndex);

            # Write data to the resource file
            $this->resourceHandler->appendResource($resourceTempName, $resourceSubDir, $group, $resource->getRealPath());

            # Determine if the resource file is completed
            if ( $chunkIndex === $chunkTotalCount ) {
                # Trigger the event before an upload completes
                if ( empty($beforeUploadCompleteEvent = ConfigMapper::get('EVENT_BEFORE_UPLOAD_COMPLETE')) === false ) {
                    event(new $beforeUploadCompleteEvent($this->resourceHandler->getPartialResourceRelativePath($resourceTempName, $resourceSubDir, $group)));
                }

                $resourceHash = $this->resourceHandler->calculateHash($resourceTempName, $resourceSubDir, $group);

                $this->resourceHandler->renameResource($this->resourceHandler->getResourceName($resourceTempBaseName,$resourceExt), $resourceSubDir, $group, $saveName = $this->resourceHandler->getResourceName($resourceHash, $resourceExt));

                $savedPath = $this->resourceHandler->getResourceSavedPath($resourceHash, $resourceExt, $resourceSubDir, $group);

                ResourceHashHandler::setOneHash($this->group.$resourceHash, $savedPath);

                $this->headerHandler->deleteHeader($resourceTempBaseName);

                # Trigger the event when an upload completes
                if ( empty($uploadCompleteEvent = ConfigMapper::get('EVENT_UPLOAD_COMPLETE')) === false ) {
                    event(new $uploadCompleteEvent($this->resourceHandler->getResourceRelativePath($saveName, $resourceSubDir, $group)));
                }

                $result['savedPath'] = $savedPath;

            }

        } catch ( \Exception $e ) {
            $this->headerHandler->deleteHeader($resourceTempBaseName);
            $this->resourceHandler->deleteResource($resourceTempName, $resourceSubDir, $group);

            return Responser::reportError($result, $e->getMessage());
        }

        return Responser::returnResult($result);

    }

    /**
     * Filter by size
     * @param $resourceSize
     * @throws \Exception
     */
    public function filterBySize($resourceSize)
    {
        $MAXSIZE = ConfigMapper::get('RESOURCE_MAXSIZE') * 1000 * 1000;

        if ( $resourceSize > $MAXSIZE && $MAXSIZE !== 0 ) {
            throw new \Exception(trans('aetherupload::messages.invalid_resource_size'));
        }

    }

    /**
     * Filter by extension
     * @param $resourceExt
     * @throws \Exception
     */
    public function filterByExt($resourceExt)
    {
        $EXTENSIONS = ConfigMapper::get('RESOURCE_EXTENSIONS');

        if ( (empty($EXTENSIONS) === false && in_array($resourceExt, $EXTENSIONS) === false) || in_array($resourceExt, static::getDangerousExtList()) === true ) {
            throw new \Exception(trans('aetherupload::messages.invalid_resource_type'));
        }
    }

    /**
     * Get the resource extensions that may harm a server
     * @return array
     */
    private static function getDangerousExtList()
    {
        return ['php', 'part', 'html', 'shtml', 'htm', 'shtm', 'js', 'jsp', 'asp', 'java', 'py', 'sh', 'bat', 'exe', 'dll', 'cgi', 'htaccess', 'reg', 'aspx', 'vbs'];
    }

    /**
     * The rule of naming a temporary file
     * @return string
     */
    public function generateTempName()
    {
        return time() . mt_rand(100000, 999999);
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