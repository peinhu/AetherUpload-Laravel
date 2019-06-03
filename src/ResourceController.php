<?php

namespace AetherUpload;

use Illuminate\Support\Facades\Request;

class ResourceController extends \Illuminate\Routing\Controller
{

    public function display($uri)
    {
        $resource = null;

        try {

            $params = SavedPathResolver::decode(Request::route('uri'));

            ConfigMapper::instance()->applyGroupConfig($params->group);

            $resource = new Resource($params->group, ConfigMapper::get('group_dir'), $params->groupSubDir, $params->resourceName);

            if ( $resource->exists($resource->path) === false ) {
                throw new \Exception;
            }

        } catch ( \Exception $e ) {

            abort(404);
        }

        return response()->download($resource->getRealPath(), '', [], 'inline');
    }

    public function download($uri, $newName = null)
    {
        $resource = $newResource = null;

        try {

            $params = SavedPathResolver::decode(Request::route('uri'));

            ConfigMapper::instance()->applyGroupConfig($params->group);

            $resource = new Resource($params->group, ConfigMapper::get('group_dir'), $params->groupSubDir, $params->resourceName);

            if ( $resource->exists($resource->path) === false ) {
                throw new \Exception;
            }

            $newResource = Util::getFileName($newName, pathinfo($resource->name, PATHINFO_EXTENSION));

        } catch ( \Exception $e ) {

            abort(404);
        }

        return response()->download($resource->getRealPath(), $newResource, [], 'attachment');
    }


}
