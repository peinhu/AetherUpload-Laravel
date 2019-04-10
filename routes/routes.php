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
            echo 'a.(手动)通过请求路由"域名(分布式启用时应当为储存服务器的域名)/aetherupload/display/"+file1 ';
            echo '<a href="' . (\AetherUpload\ConfigMapper::get('distributed_deployment_enable') ? \AetherUpload\ConfigMapper::get('distributed_deployment_storage_host') : '') . \AetherUpload\ConfigMapper::get('route_display') . '/' . request()->input('file1') . '" target="_blank">访问file1</a> ' . PHP_EOL;
            echo 'b.(自动)通过全局帮助方法{{ aetherupload_display_link(file1)  }} ';
            echo '<a href="' . aetherupload_display_link(request()->input('file1')) . '" target="_blank">访问file1</a>' . PHP_EOL;
            echo 'c.(自动)通过工具类方法{{ \AetherUpload\Util::getDisplayLink(file1)  }} ';
            echo '<a href="' . \AetherUpload\Util::getDisplayLink(request()->input('file1')) . '" target="_blank">访问file1</a>' . PHP_EOL;

            echo PHP_EOL;
            echo '获得已上传资源的<b>下载链接</b>' . PHP_EOL;
            echo 'a.(手动)通过请求路由"域名(分布式启用时应当为储存服务器的域名)/aetherupload/download/"+file1+"/newname" ';
            echo '<a href="' . (\AetherUpload\ConfigMapper::get('distributed_deployment_enable') ? \AetherUpload\ConfigMapper::get('distributed_deployment_storage_host') : '') . \AetherUpload\ConfigMapper::get('route_download') . '/' . request()->input('file1') . '/newname" target="_blank">下载file1</a> ' . PHP_EOL;
            echo 'b.(自动)通过全局帮助方法{{ aetherupload_download_link(file1,newname)  }} ';
            echo '<a href="' . aetherupload_download_link(request()->input('file1'), 'newname') . '" target="_blank">下载file1</a>' . PHP_EOL;
            echo 'c.(自动)通过工具类方法{{ \AetherUpload\Util::getDownloadLink(file1,newname)  }} ';
            echo '<a href="' . \AetherUpload\Util::getDownloadLink(request()->input('file1'), 'newname') . '" target="_blank">下载file1</a>' . PHP_EOL;
        });

        Route::get('/aetherupload/example_source', function () {

            return '<html><body style="background:#222;color:#ddd;font-size:16px;"><pre>' . htmlspecialchars(File::get(__DIR__ . '/../views/example.blade.php')) . '</pre></body></html>';
        });
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