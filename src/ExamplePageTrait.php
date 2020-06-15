<?php

namespace AetherUpload;

trait ExamplePageTrait
{

    function getExamplePage()
    {
        return view('aetherupload::example');
    }

    function postExamplePage()
    {
        echo '表单提交的数据（已上传资源的保存路径）：';
        echo '<pre>';
        print_r(request()->all());

        echo PHP_EOL;
        echo '获得已上传资源的<b>访问链接</b>' . PHP_EOL;
        echo 'a.(手动)通过请求路由"域名(分布式启用时应当为储存服务器的域名)/aetherupload/display/"+file1 ';
        echo '<a href="' . (ConfigMapper::get('distributed_deployment_enable') ? ConfigMapper::get('distributed_deployment_storage_host') : '') . ConfigMapper::get('route_display') . '/' . request()->input('file1') . '" target="_blank">访问file1</a> ' . PHP_EOL;
        echo 'b.(自动)通过全局帮助方法{{ aetherupload_display_link(file1)  }} ';
        echo '<a href="' . aetherupload_display_link(request()->input('file1')) . '" target="_blank">访问file1</a>' . PHP_EOL;
        echo 'c.(自动)通过工具类方法{{ \AetherUpload\Util::getDisplayLink(file1)  }} ';
        echo '<a href="' . Util::getDisplayLink(request()->input('file1')) . '" target="_blank">访问file1</a>' . PHP_EOL;

        echo PHP_EOL;
        echo '获得已上传资源的<b>下载链接</b>' . PHP_EOL;
        echo 'a.(手动)通过请求路由"域名(分布式启用时应当为储存服务器的域名)/aetherupload/download/"+file1+"/newname" ';
        echo '<a href="' . (ConfigMapper::get('distributed_deployment_enable') ? ConfigMapper::get('distributed_deployment_storage_host') : '') . ConfigMapper::get('route_download') . '/' . request()->input('file1') . '/newname" target="_blank">下载file1</a> ' . PHP_EOL;
        echo 'b.(自动)通过全局帮助方法{{ aetherupload_download_link(file1,newname)  }} ';
        echo '<a href="' . aetherupload_download_link(request()->input('file1'), 'newname') . '" target="_blank">下载file1</a>' . PHP_EOL;
        echo 'c.(自动)通过工具类方法{{ \AetherUpload\Util::getDownloadLink(file1,newname)  }} ';
        echo '<a href="' . Util::getDownloadLink(request()->input('file1'), 'newname') . '" target="_blank">下载file1</a>' . PHP_EOL;
    }

    function examplePageSource()
    {
        return '<html><body style="background:#222;color:#ddd;font-size:16px;"><pre>' . htmlspecialchars(\File::get(__DIR__ . '/../views/example.blade.php')) . '</pre></body></html>';
    }
}