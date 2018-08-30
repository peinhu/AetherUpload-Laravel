<?php

namespace AetherUpload;

class ResourceController extends \Illuminate\Routing\Controller
{
    public $resourceHandler, $group, $resourceSubDir, $resourceName;

    public function __construct(ResourceHandler $resourceHandler)
    {
        $this->resourceHandler = $resourceHandler;
        list($this->group, $this->resourceSubDir, $this->resourceName) = explode('_', request()->route('uri'));
        ConfigMapper::getInstance()->applyGroupConfig($this->group);
        $this->middleware(ConfigMapper::get('MIDDLEWARE_DISPLAY'))->only('displayResource');
        $this->middleware(ConfigMapper::get('MIDDLEWARE_DOWNLOAD'))->only('downloadResource');
    }

    public function displayResource($uri)
    {
        if ( $this->resourceHandler->resourceExists($this->resourceName, $this->resourceSubDir, $this->group) === false ) {
            abort(404);
        }

        $resource = $this->resourceHandler->getResourcePath($this->resourceName, $this->resourceSubDir, $this->group);

        return response()->download($resource, '', [], 'inline');
    }

    public function downloadResource($uri, $newName = null)
    {
        if ( $this->resourceHandler->resourceExists($this->resourceName, $this->resourceSubDir, $this->group) === false ) {
            abort(404);
        }

        $resource = $this->resourceHandler->getResourcePath($this->resourceName, $this->resourceSubDir, $this->group);

        $newResource = $this->resourceHandler->getResourceName($newName, pathinfo($resource, PATHINFO_EXTENSION));

        return response()->download($resource, $newResource, [], 'attachment');
    }


}