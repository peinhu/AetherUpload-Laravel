<?php

Route::group(['middleware' => 'web'], function () {

    if ( Config::get('app.env') !== 'production' ) {

        Route::get('/aetherupload', '\AetherUpload\UploadController@getExamplePage');

        Route::post('/aetherupload', '\AetherUpload\UploadController@postExamplePage');

        Route::get('/aetherupload/example_source', '\AetherUpload\UploadController@examplePageSource');
    }

    if ( \AetherUpload\Util::isStorageHost() ) {

        Route::post(\AetherUpload\ConfigMapper::get('route_preprocess'), '\AetherUpload\UploadController@preprocess')->middleware(\AetherUpload\ConfigMapper::get('middleware_preprocess'));

        Route::options(\AetherUpload\ConfigMapper::get('route_preprocess'), '\AetherUpload\UploadController@options');

        Route::post(\AetherUpload\ConfigMapper::get('route_uploading'), '\AetherUpload\UploadController@saveChunk')->middleware(\AetherUpload\ConfigMapper::get('middleware_uploading'));

        Route::options(\AetherUpload\ConfigMapper::get('route_uploading'), '\AetherUpload\UploadController@options');

        Route::get(\AetherUpload\ConfigMapper::get('route_display').'/{uri}', '\AetherUpload\ResourceController@display')->middleware(\AetherUpload\ConfigMapper::get('middleware_display'));

        Route::get(\AetherUpload\ConfigMapper::get('route_download').'/{uri}/{newName}', '\AetherUpload\ResourceController@download')->middleware(\AetherUpload\ConfigMapper::get('middleware_download'));
    }

});