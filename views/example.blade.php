<!DOCTYPE html>
<html lang="zh_cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}"><!--need to have csrf token-->
    <link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.6/css/bootstrap.min.css" />
</head>
<body>

<div class="container">
    <div class="page-header">
        <h1>This is an example page.</h1>
        <i>view the source code in resources/views/vendor/aetherupload/example.blade.php</i>
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

        <a href="/aetherupload/display/UPLOADED-FILE-NAME">[查看某个上传的文件]</a>
        <a href="/aetherupload/download/UPLOADED-FILE-NAME/DOWNLOAD-FILE-NEWNAME">[下载某个上传的文件]</a>
        <p><i>仅演示写法，请查看源码</i></p>
    </div>
</div>

<script src="//cdn.bootcss.com/jquery/2.2.3/jquery.min.js"></script><!--need to have jquery-->
<script src="{{ URL::asset('js/aetherupload.js') }}"></script><!--need to have aetherupload.js-->
</body>
</html>
