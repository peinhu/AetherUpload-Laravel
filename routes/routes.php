<?php

Route::group(['middleware' => 'web'], function () {

    if ( Config::get('app.env') !== 'production' ) {

        Route::get('/aetherupload', function () {

            return view('aetherupload::example');
        });

        Route::post('/aetherupload', function () {

            echo '表单提交的数据（已上传资源的保存路径）：';
            echo '<pre>';
            print_r(request()->all());

            echo PHP_EOL;
            echo '获得已上传资源的<b>访问链接</b>' . PHP_EOL;
            echo 'a.(手动)通过请求路由"域名(分布式启用时应当为储存服务器的域名)/配置中自定义的访问路由/"+file1 ';
            echo '<a href="' . (Config::get('aetherupload.distributed_deployment.enable') ? Config::get('aetherupload.distributed_deployment.web.storage_host') : '') . '/aetherupload/display/' . request()->input('file1') . '" target="_blank">访问file1</a> ' . PHP_EOL;
            echo 'b.(自动)通过全局帮助方法{{ aetherupload_display_link(file1)  }} ';
            echo '<a href="' . aetherupload_display_link(request()->input('file1')) . '" target="_blank">访问file1</a>' . PHP_EOL;
            echo 'c.(自动)通过工具类方法{{ \AetherUpload\Util::getDisplayLink(file1)  }} ';
            echo '<a href="' . \AetherUpload\Util::getDisplayLink(request()->input('file1')) . '" target="_blank">访问file1</a>' . PHP_EOL;

            echo PHP_EOL;
            echo '获得已上传资源的<b>下载链接</b>' . PHP_EOL;
            echo 'a.(手动)通过请求路由"域名(分布式启用时应当为储存服务器的域名)/配置中自定义的访问路由/"+file1+"/newname" ';
            echo '<a href="' . (Config::get('aetherupload.distributed_deployment.enable') ? Config::get('aetherupload.distributed_deployment.web.storage_host') : '') . '/aetherupload/download/' . request()->input('file1') . '/newname" target="_blank">下载file1</a> ' . PHP_EOL;
            echo 'b.(自动)通过全局帮助方法{{ aetherupload_download_link(file1,newname)  }} ';
            echo '<a href="' . aetherupload_download_link(request()->input('file1'), 'newname') . '" target="_blank">下载file1</a>' . PHP_EOL;
            echo 'c.(自动)通过工具类方法{{ \AetherUpload\Util::getDownloadLink(file1,newname)  }} ';
            echo '<a href="' . \AetherUpload\Util::getDownloadLink(request()->input('file1'), 'newname') . '" target="_blank">下载file1</a>' . PHP_EOL;
        });

        Route::get('/aetherupload/example_source', function () {

            return '<html><body style="background:#222;color:#ddd;font-size:16px;"><pre>' . htmlspecialchars(File::get(__DIR__ . '/../views/example.blade.php')) . '</pre></body></html>';
        });
    }

    if(Config::get('aetherupload.distributed_deployment.enable') && Config::get('aetherupload.distributed_deployment.role') === 'web'){

    Route::post('/aetherupload/preprocess', '\AetherUpload\UploadController@preprocess');

    Route::options('/aetherupload/preprocess', '\AetherUpload\UploadController@options');

    Route::post('/aetherupload/uploading', '\AetherUpload\UploadController@saveChunk');

    Route::options('/aetherupload/uploading', '\AetherUpload\UploadController@options');

    Route::get(Config::get('aetherupload.route_display') . '/{uri}', '\AetherUpload\ResourceController@display');

    Route::get(Config::get('aetherupload.route_download') . '/{uri}/{newName}', '\AetherUpload\ResourceController@download');
    }

});