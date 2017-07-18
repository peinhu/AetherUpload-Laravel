<!DOCTYPE html>
<html lang="zh_cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"><!--need to have csrf token here-->
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
            <div class="form-group">
                <label>文件：</label>
                <div class="controls">
                    <input type="file" id="aetherupload-file" onchange="AetherUpload.upload('file')"/><!--need to have an id "aetherupload-file" here for the file to be uploaded, 'file' is the default group name in config/aetherupload.php-->
                    <div class="progress " style="height: 6px;margin-bottom: 2px;margin-top: 10px;width: 200px;">
                        <div id="aetherupload-progressbar" style="background:blue;height:6px;width:0;"></div><!--need to have an id "aetherupload-progressbar" here for the progress bar-->
                    </div>
                    <span style="font-size:12px;color:#aaa;" id="aetherupload-output">等待上传</span><!--need to have an id "aetherupload-output" here for the prompt message-->
                </div>
            </div>

            <input type="hidden" name="savedpath" id="aetherupload-savedpath" value=""><!--need to have an id "aetherupload-savedpath"  and a name "savedpath" here for the saved path of the uploaded file-->
            <button type="submit" class="btn btn-primary">点击这里提交</button>
        </form>

        <hr/>

        <div>
            <p>原文件名：<span id="test1"></span></p>
            <p>原文件大小：<span id="test2"></span></p>
            <p>储存文件名：<span id="test3"></span></p>
        </div>

    </div>
</div>
<script src="{{ URL::asset('js/spark-md5.min.js') }}"></script><!--need to have spark-md5.js for md5 calculation-->
<script src="//cdn.bootcss.com/jquery/2.2.3/jquery.min.js"></script><!--need to have jquery-->
<script src="{{ URL::asset('js/aetherupload.js') }}"></script><!--need to have aetherupload.js-->
<script>
    // this function will be called after file is uploaded successfully
    // you can get fileName,fileSize,uploadExt,chunkCount,chunkSize,subDir,group,savedFilePath of the uploaded file
    AetherUpload.success = function () {
        // Example
        $('#test1').text(this.fileName);
        $('#test2').text(parseFloat(this.fileSize / (1000 * 1000)).toFixed(2) + 'MB');
        $('#test3').text(this.savedFilePath.substr(this.savedFilePath.lastIndexOf('/') + 1));
    };

</script>
</body>
</html>
