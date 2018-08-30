<?php

if ( ! function_exists('displayLink') ) {
    function AetherUploadDisplayLink($savedPath)
    {
        return \AetherUpload\ResourceHandler::getDisplayLink($savedPath);
    }
}

if ( ! function_exists('downloadLink') ) {
    function AetherUploadDownloadLink($savedPath, $newName)
    {
        return \AetherUpload\ResourceHandler::getDownloadLink($savedPath, $newName);
    }
}

if ( ! function_exists('storage_host_field') ) {
    function storage_host_field()
    {
        return '<input type="hidden" id="aetherupload-storage-host" value="{{config(\'aetherupload.DISTRIBUTED_DEPLOYMENT.ENABLE\')&&(config(\'aetherupload.DISTRIBUTED_DEPLOYMENT.ROLE\') === \'web\')?config(\'aetherupload.DISTRIBUTED_DEPLOYMENT.WEB.STORAGE_HOST\'):\'\'}}" />';
    }
}






