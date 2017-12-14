<!DOCTYPE html>
<html lang="en-us">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"><!--需要有csrf token-->
    <link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.6/css/bootstrap.min.css"/>
</head>
<body>

<div class="container">
    <div class="page-header">
        <h1>This is an example page.</h1>
        <i>view the source code in <a href="/aetherupload/example_source" target="_blank">vendor/peinhu/aetherupload-laravel/views/example.blade.php</a></i>
    </div>

    <div class="row">
        <form method="post" action="">

            <div class="form-group " id="aetherupload-wrapper" ><!--组件最外部需要有一个名为aetherupload-wrapper的id，用以包装组件-->
                <label>文件1(带回调)：</label>
                <div class="controls" >
                    <input type="file" id="file"  onchange="aetherupload(this,'file').success(someCallback).upload()"/><!--需要有一个名为file的id，用以标识上传的文件，aetherupload(file,group)中第二个参数为分组名，success方法可用于声名上传成功后的回调方法名-->
                    <div class="progress " style="height: 6px;margin-bottom: 2px;margin-top: 10px;width: 200px;">
                        <div id="progressbar" style="background:blue;height:6px;width:0;"></div><!--需要有一个名为progressbar的id，用以标识进度条-->
                    </div>
                    <span style="font-size:12px;color:#aaa;" id="output"></span><!--需要有一个名为output的id，用以标识提示信息-->
                    <input type="hidden" name="file1" id="savedpath" ><!--需要有一个名为savedpath的id，用以标识文件保存路径的表单字段，还需要一个任意名称的name-->
                </div>
            </div>

            <div class="form-group " id="aetherupload-wrapper">
                <label>文件2(无回调)：</label>
                <div class="controls" >
                    <input type="file" id="file" onchange="aetherupload(this,'file').upload()"/>
                    <div class="progress " style="height: 6px;margin-bottom: 2px;margin-top: 10px;width: 200px;">
                        <div id="progressbar" style="background:blue;height:6px;width:0;"></div>
                    </div>
                    <span style="font-size:12px;color:#aaa;" id="output"></span>
                    <input type="hidden" name="file2" id="savedpath" >
                </div>
            </div>

            <button type="submit" class="btn btn-primary">点击提交</button>
        </form>

        <hr/>

        <div id="result"></div>

    </div>
</div>
<script src="{{ URL::asset('js/spark-md5.min.js') }}"></script><!--需要引入spark-md5.min.js-->
<script src="//cdn.bootcss.com/jquery/2.2.3/jquery.min.js"></script><!--需要引入jquery.min.js-->
<script src="{{ URL::asset('js/aetherupload.js') }}"></script><!--需要引入aetherupload.js-->
<script>
    // success(callback)中声名的回调方法需在此定义，参数callback可为任意名称，此方法将会在上传完成后被调用
    // 可使用this对象获得fileName,fileSize,uploadBaseName,uploadExt,subDir,group,savedPath等属性的值
    someCallback = function(){
        // Example
        $('#result').append(
            '<p>执行回调 - 文件原名：<span >'+this.fileName+'</span> | 文件大小：<span >'+parseFloat(this.fileSize / (1000 * 1000)).toFixed(2) + 'MB'+'</span> | 文件储存名：<span >'+this.savedPath.substr(this.savedPath.lastIndexOf('/') + 1)+'</span></p>'
        );
    }

</script>
</body>
</html>
