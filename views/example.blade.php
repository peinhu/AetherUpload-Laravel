<!DOCTYPE html>
<html lang="zh_cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}"><!--need to have csrf token here-->
    <link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.6/css/bootstrap.min.css" />
</head>
<body>

<div class="container">
    <div class="page-header">
        <h1>This is an example page.</h1>
        <i>view the source code in <a href="/aetherupload/example_source" target="_blank">vendor/peinhu/aetherupload-laravel/views/example.blade.php</a></i>
    </div>

    <div class="row">
        <form method="post" action="#">

            <div class="form-group">
                <label >文件：</label>
                <div class="controls">
                    <input type="file"  id="aetherupload-file"/><!--need to have an id "aetherupload-file" here for the file to be uploaded-->
                    <div class="progress " style="height: 6px;margin-bottom: 2px;margin-top: 10px;width: 200px;">
                        <div id="aetherupload-bar" style="background:blue;height:6px;width:0;"></div><!--need to have an id "aetherupload-bar" here for the progress bar-->
                    </div>
                    <span style="font-size:12px;color:#aaa;" id="aetherupload-output">等待上传</span><!--need to have an id "aetherupload-output" here for the prompt message-->
                </div>
            </div>

            <div class="form-group">
                <label >名称：</label>
                <div class="controls">
                    <input type="text" value="" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label >描述：</label>
                <div class="controls">
                    <input type="text" value="" class="form-control">
                </div>
            </div>

            <input type="hidden" name="uploadname" id="aetherupload-uploadname" value=""><!--need to have an id "aetherupload-uploadname"  here for the name of the uploaded file-->
            <button type="button" class="btn btn-primary">提交</button>

        </form>

        <hr/>

        <div >
        <p>原文件名：<span id="test1"></span></p>
        <p>原文件大小：<span id="test2"></span></p>
        <p>上传文件名：<span id="test3"></span></p>
        </div>

        <a href="/aetherupload/display/UPLOADED-FILE-NAME" target="_blank" id="display">[获得上传的文件]</a>
        <a href="/aetherupload/download/UPLOADED-FILE-NAME/DOWNLOAD-FILE-NEWNAME" target="_blank" id="download">[下载上传的文件]</a>
    </div>
</div>

<script src="//cdn.bootcss.com/jquery/2.2.3/jquery.min.js"></script><!--need to have jquery here-->
<script src="{{ URL::asset('js/aetherupload.js') }}"></script><!--need to have aetherupload.js here-->
<script>
    // this function will be called after file is uploaded
    AetherUpload.success = function(){
        $('#test1').text(this.fileName);
        $('#test2').text(parseInt(this.fileSize/(1024 * 1024))+"MB");
        $('#test3').text(this.uploadBasename+"."+this.uploadExt);
        $('#display').attr("href","/aetherupload/display/"+this.uploadBasename+"."+this.uploadExt);
        $('#download').attr("href","/aetherupload/download/"+this.uploadBasename+"."+this.uploadExt+"/test."+this.uploadExt);
    }
</script>
</body>
</html>
