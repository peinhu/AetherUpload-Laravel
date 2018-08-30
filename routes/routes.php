<?php

Route::group(['middleware' => 'web'], function () {

    if ( Config::get('aetherupload.ENABLE_EXAMPLE_PAGE') === true ) {

        Route::get('aetherupload', function () {

            return view('aetherupload::example');
        });

        Route::post('aetherupload', function () {

            echo '表单提交的数据：';
            echo '<pre>';
            print_r(request()->all());
            echo PHP_EOL;
            echo '通过请求路由"/aetherupload/display/"+file1 或 \AetherUpload\ResourceHandler::getDisplayLink($file1) ';
            echo '<a href="' . \AetherUpload\ResourceHandler::getDisplayLink(request()->input('file1')) . '" target="_blank">获得file1</a>' . PHP_EOL;
            echo '通过请求路由"/aetherupload/download/"+file1+"/"+newname 或 \AetherUpload\ResourceHandler::getDownloadLink($file1,$newname) ';
            echo '<a href="' . \AetherUpload\ResourceHandler::getDownloadLink(request()->input('file1'), "newname") . '">下载file1</a>' . PHP_EOL;
            echo PHP_EOL;
            echo '通过请求路由"/aetherupload/display/"+file2 或 \AetherUpload\ResourceHandler::getDisplayLink($file2) ';
            echo '<a href="' . \AetherUpload\ResourceHandler::getDisplayLink(request()->input('file2')) . '" target="_blank">获得file2</a>' . PHP_EOL;
            echo '通过请求路由"/aetherupload/download/"+file2+"/"+newname 或 \AetherUpload\ResourceHandler::getDownloadLink($file2,$newname) ';
            echo '<a href="' . \AetherUpload\ResourceHandler::getDownloadLink(request()->input('file2'), "newname") . '">下载file2</a>' . PHP_EOL;
        });

        Route::get('aetherupload/example_source', function () {

            return '<html><body style="background:#222;color:#bbb;"><pre>' . htmlspecialchars(File::get(__DIR__ . '/../views/example.blade.php')) . '</pre></body></html>';
        });
    }

    Route::post('aetherupload/preprocess', '\AetherUpload\UploadController@preprocess');

    Route::options('aetherupload/preprocess', '\AetherUpload\UploadController@options');

    Route::post('aetherupload/uploading', '\AetherUpload\UploadController@saveChunk');

    Route::options('aetherupload/uploading', '\AetherUpload\UploadController@options');

    Route::get('aetherupload/display/{uri}', '\AetherUpload\ResourceController@displayResource');

    Route::get('aetherupload/download/{uri}/{newName}', '\AetherUpload\ResourceController@downloadResource');


});