<?php

namespace AetherUpload;

use Illuminate\Support\Facades\Request;

class ResourceController extends \Illuminate\Routing\Controller
{
    public $group, $groupSubdir, $resourceName;

    public function __construct()
    {
        if ( request('uri') !== null ) {
            list($this->group, $this->groupSubdir, $this->resourceName) = explode('_', Request::route('uri'));
            ConfigMapper::instance()->applyGroupConfig($this->group);
            $this->middleware(ConfigMapper::get('middleware_display'))->only('display');
            $this->middleware(ConfigMapper::get('middleware_download'))->only('download');
        }
    }

    public function display($uri)
    {
        $resource = new Resource($this->resourceName, $this->groupSubdir);

        if ( $resource->exists($resource->path) === false ) {
            abort(404);
        }

        return response()->download($resource->getRealPath(), '', [], 'inline');
    }

    public function download($uri, $newName = null)
    {
        $resource = new Resource($this->resourceName, $this->groupSubdir);

        if ( $resource->exists($resource->path) === false ) {
            abort(404);
        }

        $newResource = Util::getFileName($newName, pathinfo($resource->name, PATHINFO_EXTENSION));

        return response()->download($resource->getRealPath(), $newResource, [], 'attachment');
    }


}