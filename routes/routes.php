<?php

if ( Config::get('aetherupload.ENABLE_EXAMPLE_PAGE') === true ) {

    Route::get('aetherupload', function () {

        return view('aetherupload::example');
    });

    Route::post('aetherupload', function () {

        echo '表单提交的数据(由脚本在上传完成后自动填入)：';
        echo '<pre>';
        print_r(request()->all());
        echo PHP_EOL;
        echo '通过请求"/aetherupload/display/"+file1 ';
        echo '<a href="/aetherupload/display/' . request()->input('file1') . '" target="_blank">获得file1</a>' . PHP_EOL;
        echo '通过请求"/aetherupload/download/"+file1+"/"+newname ';
        echo '<a href="/aetherupload/download/' . request()->input('file1') . '/newname">下载file1</a>' . PHP_EOL;
        echo PHP_EOL;
        echo '通过请求"/aetherupload/display/"+file2 ';
        echo '<a href="/aetherupload/display/' . request()->input('file2') . '" target="_blank">获得file2</a>' . PHP_EOL;
        echo '通过请求"/aetherupload/download/"+file2+"/"+newname ';
        echo '<a href="/aetherupload/download/' . request()->input('file2') . '/newname">下载file2</a>' . PHP_EOL;
    });

    Route::get('aetherupload/example_source', function () {

        return '<html><body style="background:#222;color:#bbb;"><pre>' . htmlspecialchars(File::get(__DIR__ . '/../views/example.blade.php')) . '</pre></body></html>';
    });
}

Route::post('aetherupload/preprocess', '\AetherUpload\UploadHandler@preprocess');

Route::post('aetherupload/uploading', '\AetherUpload\UploadHandler@saveChunk');

Route::get('aetherupload/display/{group}/{subDir}/{resourceName}', '\AetherUpload\ResourceHandler@displayResource');

Route::get('aetherupload/download/{group}/{subDir}/{resourceName}/{newName}', '\AetherUpload\ResourceHandler@downloadResource');

