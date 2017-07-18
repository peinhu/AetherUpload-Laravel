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

        this.savedFilePath = "",

        this.fileHash = "",

        this.blobSlice = File.prototype.slice || File.prototype.mozSlice || File.prototype.webkitSlice,

        this.i = 0;

        this.outputDom.text('开始上传');

        if (!this.blobSlice) {

            this.outputDom.text("上传组件不被此浏览器支持");

            return;

        }

        if (!('FileReader' in window) || !('File' in window)) {

            this.preprocess(); //浏览器不支持读取本地文件，跳过计算hash

        } else {

            this.calculateHash();

        }

    },

    calculateHash: function () { //计算hash

        var _this = this,

            chunkSize = 2000000,

            chunks = Math.ceil(_this.file.size / chunkSize),

            currentChunk = 0,

            spark = new SparkMD5.ArrayBuffer(),

            fileReader = new FileReader();

        fileReader.onload = function (e) {

            spark.append(e.target.result);

            ++currentChunk;

            _this.outputDom.text('正在hash ' + parseInt(currentChunk / chunks * 100) + '%');

            if (currentChunk < chunks) {

                loadNext();

            } else {

                _this.fileHash = spark.end();

                _this.preprocess();

            }
        };

        fileReader.onerror = function () {

            _this.preprocess();

        };

        function loadNext() {

            var start = currentChunk * chunkSize,

                end = start + chunkSize >= _this.file.size ? _this.file.size : start + chunkSize;

            fileReader.readAsArrayBuffer(_this.blobSlice.call(_this.file, start, end));

        }

        loadNext();

    },

    preprocess: function () { //预处理

        var _this = this;

        $.post('/aetherupload/preprocess', {

            file_name: _this.fileName,

            file_size: _this.fileSize,

            file_hash: _this.fileHash,

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

            if (rst.savedFilePath.length === 0) {

                _this.uploadChunkInterval = setInterval($.proxy(_this.uploadChunk, _this), 0);

            } else {

                _this.progressBarDom.css("width", "100%");

                _this.savedFilePath = rst.savedFilePath;

                _this.savedPathDom.val(_this.savedFilePath);

                _this.fileDom.attr('disabled', 'disabled');

                _this.outputDom.text("秒传成功");

                _this.success();

            }

        }, 'json');

    },

    uploadChunk: function () {

        var _this = this,

            start = this.i * this.chunkSize,

            end = Math.min(this.fileSize, start + this.chunkSize),

            form = new FormData();

        form.append("file", this.file.slice(start, end));

        form.append("upload_ext", this.uploadExt);

        form.append("chunk_total", this.chunkCount);

        form.append("chunk_index", this.i + 1);

        form.append("upload_basename", this.uploadBaseName);

        form.append("group", this.group);

        form.append("sub_dir", this.subDir);

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

                var percent = parseInt((_this.i + 1) / _this.chunkCount * 100);

                _this.progressBarDom.css("width", percent + "%");

                _this.outputDom.text("正在上传 " + percent + "%");

                if (_this.i + 1 === _this.chunkCount) {

                    clearInterval(_this.uploadChunkInterval);

                    _this.savedFilePath = rst.savedFilePath;

                    _this.savedPathDom.val(_this.savedFilePath);

                    _this.fileDom.attr('disabled', 'disabled');

                    _this.outputDom.text("上传完毕");

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