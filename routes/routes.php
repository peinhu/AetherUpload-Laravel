<?php

if ( Config::get('aetherupload.ENABLE_EXAMPLE_PAGE') === true ) {
    Route::get('aetherupload', function () {

        return view('aetherupload::example');
    });

    Route::get('aetherupload/example', function () {

        return view('aetherupload::example');
    });

    Route::get('aetherupload/example_source', function () {

        return '<html><body style="background:#222;color:#bbb;"><pre>' . htmlspecialchars(File::get(__DIR__ . '/../views/example.blade.php')) . '</pre></body></html>';
    });
}

Route::post('aetherupload/initialize', '\Peinhu\AetherUpload\UploadHandler@init');

Route::post('aetherupload/uploading', '\Peinhu\AetherUpload\UploadHandler@saveChunk');

Route::get('aetherupload/display/{group}/{resourceName}', '\Peinhu\AetherUpload\ResourceHandler@displayResource');

Route::get('aetherupload/download/{group}/{resourceName}/{newName}', '\Peinhu\AetherUpload\ResourceHandler@downloadResource');

