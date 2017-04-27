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

Route::post('aetherupload/initialize', function (\Peinhu\AetherUpload\UploadHandler $uploadHandler) {

    return $uploadHandler->init();
})->middleware(Config::get('aetherupload.MIDDLEWARE_UPLOAD'));

Route::post('aetherupload/uploading', function (\Peinhu\AetherUpload\UploadHandler $uploadHandler) {

    return $uploadHandler->save();
});

Route::get('aetherupload/display/{resourceName}', function (\Peinhu\AetherUpload\ResourceHandler $resourceHandler, $resourceName) {

    return $resourceHandler->displayResource($resourceName);
})->middleware(Config::get('aetherupload.MIDDLEWARE_DISPLAY'));

Route::get('aetherupload/download/{resourceName}/{newName}', function (\Peinhu\AetherUpload\ResourceHandler $resourceHandler, $resourceName, $newName) {

    return $resourceHandler->downloadResource($resourceName, $newName);
})->middleware(Config::get('aetherupload.MIDDLEWARE_DOWNLOAD'));

