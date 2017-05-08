var AetherUpload = {

    upload: function (group) { //group对应配置文件中的分组名

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        this.fileDom = $('#aetherupload-file'),

        this.outputDom = $('#aetherupload-output'),

        this.progressBarDom = $('#aetherupload-progressbar'),

        this.savedPathDom = $('#aetherupload-savedpath'),

        this.file = this.fileDom[0].files[0],

        this.fileName = this.file.name,

        this.fileSize = this.file.size,

        this.uploadBaseName = "",

        this.uploadExt = "",

        this.chunkSize = 0,

        this.chunkCount = 0,

        this.group = group,

        this.subDir = "",

        this.i = 0;

        var _this = this;

        this.outputDom.text('开始上传');

        $.post('/aetherupload/initialize', {
            file_name: _this.fileName,
            file_size: _this.fileSize,
            group: _this.group
        }, function (rst) {

            if (rst.error != 0) {

                _this.outputDom.text(rst.error);

                return;

            }

            _this.uploadBaseName = rst.uploadBaseName;

            _this.uploadExt = rst.uploadExt;

            _this.chunkSize = rst.chunkSize;

            _this.chunkCount = Math.ceil(_this.fileSize / _this.chunkSize);

            _this.subDir = rst.subDir;

            _this.uploadChunkInterval = setInterval($.proxy(_this.uploadChunk, _this), 0);

        }, 'json');

    },

    uploadChunk: function () {

        var start = this.i * this.chunkSize, end = Math.min(this.fileSize, start + this.chunkSize);

        var form = new FormData();

        form.append("file", this.file.slice(start, end));

        form.append("upload_ext", this.uploadExt);

        form.append("chunk_total", this.chunkCount);

        form.append("chunk_index", this.i + 1);

        form.append("upload_basename", this.uploadBaseName);

        form.append("group", this.group);

        form.append("sub_dir", this.subDir);

        var _this = this;

        $.ajax({

            url: "/aetherupload/uploading",

            type: "POST",

            data: form,

            dataType: 'json',

            async: false,

            processData: false,

            contentType: false,

            success: function (rst) {

                if (rst.error != 0) {

                    _this.outputDom.text(rst.error);

                    clearInterval(_this.uploadChunkInterval);

                    return;

                }

                _this.refreshProgress();

                if (_this.i + 1 == _this.chunkCount) {

                    clearInterval(_this.uploadChunkInterval);

                    _this.savedPathDom.val(_this.group + '/' + _this.subDir + '/' + _this.uploadBaseName + '.' + _this.uploadExt);

                    _this.outputDom.text('上传完毕');

                    _this.fileDom.attr('disabled', 'disabled');

                    _this.success();

                }

                ++_this.i;

            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {

                if (XMLHttpRequest.status === 0) {

                    _this.outputDom.text('网络故障，正在重试……');

                    _this.sleep(3000);

                } else {

                    _this.outputDom.text('发生故障，上传失败。');

                    clearInterval(_this.uploadChunkInterval);

                }
            }

        });

    },

    refreshProgress: function () {

        var percent = parseInt((this.i + 1) / this.chunkCount * 100) + "%";

        this.progressBarDom.css("width", percent);

        this.outputDom.text("正在上传 " + percent);

    },

    sleep: function (milliSecond) {

        var wakeUpTime = new Date().getTime() + milliSecond;

        while (true) {

            if (new Date().getTime() > wakeUpTime) {

                return;

            }
        }
    },

    success: function () {
        //
    }

};