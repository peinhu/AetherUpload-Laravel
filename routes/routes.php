<?php

if ( Config::get('aetherupload.ENABLE_EXAMPLE_PAGE') === true ) {

    Route::get('aetherupload', function () {

        return view('aetherupload::example');
    });

    Route::post('aetherupload', function () {

        echo '表单提交的数据(由脚本在上传完成后自动填入)：';
        echo '<pre>';
        print_r(request()->all());
        echo '通过路由"/aetherupload/display/"+savedpath';
        echo '<a href="/aetherupload/display/' . request()->input('savedpath') . '">获得文件</a>' . PHP_EOL;
        echo '通过路由"/aetherupload/download/"+savedpath+"/"+newname';
        echo '<a href="/aetherupload/download/' . request()->input('savedpath') . '/newname">下载文件</a>' . PHP_EOL;
    });

    Route::get('aetherupload/example_source', function () {

        return '<html><body style="background:#222;color:#bbb;"><pre>' . htmlspecialchars(File::get(__DIR__ . '/../views/example.blade.php')) . '</pre></body></html>';
    });
}

Route::post('aetherupload/preprocess', '\Peinhu\AetherUpload\UploadHandler@preprocess');

Route::post('aetherupload/uploading', '\Peinhu\AetherUpload\UploadHandler@saveChunk');

Route::get('aetherupload/display/{group}/{subDir}/{resourceName}', '\Peinhu\AetherUpload\ResourceHandler@displayResource');

Route::get('aetherupload/download/{group}/{subDir}/{resourceName}/{newName}', '\Peinhu\AetherUpload\ResourceHandler@downloadResource');

