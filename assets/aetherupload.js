var AetherUpload = {

    upload: function () {

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        this.fileDom = this.wrapperDom.find("#file"),

            this.outputDom = this.wrapperDom.find("#output"),

            this.progressBarDom = this.wrapperDom.find("#progressbar"),

            this.savedPathDom = this.wrapperDom.find("#savedpath"),

            this.file = this.fileDom[0].files[0],

            this.fileName = this.file.name,

            this.fileSize = this.file.size,

            this.uploadBaseName = "",

            this.uploadExt = "",

            this.chunkSize = 0,

            this.chunkCount = 0,

            this.subDir = "",

            this.savedPath = "",

            this.fileHash = "",

            this.blobSlice = File.prototype.slice || File.prototype.mozSlice || File.prototype.webkitSlice,

            this.i = 0,

            this.locale,

            this.messages = this.getLocalizedMessages();

        this.outputDom.text(this.messages.status_upload_begin);

        if (!this.blobSlice) {

            this.outputDom.text(this.messages.error_unsupported_browser);

            return;

        }

        if (!("FileReader" in window) || !("File" in window)) {

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

            _this.outputDom.text(_this.messages.status_hashing + ' ' + parseInt(currentChunk / chunks * 100) + "%");

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

        $.post("/aetherupload/preprocess", {

            file_name: _this.fileName,

            file_size: _this.fileSize,

            file_hash: _this.fileHash,

            locale: _this.locale,

            group: _this.group

        }, function (rst) {

            if (rst.error) {

                _this.outputDom.text(rst.error);

                return;

            }

            _this.uploadBaseName = rst.uploadBaseName;

            _this.uploadExt = rst.uploadExt;

            _this.chunkSize = rst.chunkSize;

            _this.chunkCount = Math.ceil(_this.fileSize / _this.chunkSize);

            _this.subDir = rst.subDir;

            if (rst.savedPath.length === 0) {

                _this.outputDom.text(_this.messages.status_uploading + " 0%");

                _this.uploadChunkInterval = setInterval($.proxy(_this.uploadChunk, _this), 0);

            } else {

                _this.progressBarDom.css("width", "100%");

                _this.savedPath = rst.savedPath;

                _this.savedPathDom.val(_this.savedPath);

                _this.fileDom.attr("disabled", "disabled");

                _this.outputDom.text(_this.messages.status_instant_completion_success);

                typeof(_this.callback) !== "undefined" ? _this.callback() : null;

            }

        }, "json");

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

        form.append("locale", this.locale);

        $.ajax({

            url: "/aetherupload/uploading",

            type: "POST",

            data: form,

            dataType: "json",

            async: false,

            processData: false,

            contentType: false,

            success: function (rst) {

                if (rst.error) {

                    _this.outputDom.text(rst.error);

                    clearInterval(_this.uploadChunkInterval);

                    return;

                }

                var percent = parseInt((_this.i + 1) / _this.chunkCount * 100);

                _this.progressBarDom.css("width", percent + "%");

                _this.outputDom.text(_this.messages.status_uploading + " " + percent + "%");

                if (_this.i + 1 === _this.chunkCount) {

                    clearInterval(_this.uploadChunkInterval);

                    _this.savedPath = rst.savedPath;

                    _this.savedPathDom.val(_this.savedPath);

                    _this.fileDom.attr("disabled", "disabled");

                    _this.outputDom.text(_this.messages.status_upload_success);

                    typeof(_this.callback) !== "undefined" ? _this.callback() : null;

                }

                ++_this.i;

            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {

                if (XMLHttpRequest.status === 0) {

                    _this.outputDom.text(_this.messages.status_retrying);

                    _this.sleep(3000);

                } else {

                    _this.outputDom.text(_this.messages.error_upload_fail);

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

    success: function (callback) {

        this.callback = callback;

        return this;
    },

    getLocalizedMessages: function () {

        var lang = navigator.language ? navigator.language : navigator.browserLanguage;

        var locales = Object.getOwnPropertyNames(this.text);

        for (var k in locales) {

            if (lang.indexOf(locales[k]) > -1) {

                this.locale = locales[k];

                return this.text[this.locale];

            }

        }

        this.locale = "en";

        return this.text[this.locale];

    },

    text: {
        en: {
            status_upload_begin: "upload begin",
            error_unsupported_browser: "Error: unsupported browser",
            status_hashing: "hashing",
            status_instant_completion_success: "upload success (instant completion) ",
            status_uploading: "uploading",
            status_upload_success: "upload success",
            status_retrying: "network problem, retrying...",
            error_upload_fail: "Error: upload failed"
        },
        zh: {
            status_upload_begin: "开始上传",
            error_unsupported_browser: "错误：上传组件不被此浏览器支持",
            status_hashing: "正在哈希",
            status_instant_completion_success: "上传成功（秒传）",
            status_uploading: "正在上传",
            status_upload_success: "上传成功",
            status_retrying: "网络故障，正在重试……",
            error_upload_fail: "错误：上传失败"
        }
    }


};

/*
 * 创建AetherUpload对象的全局方法
 * file 文件对象
 * group 分组名
 */
function aetherupload(file, group) {

    var newInstance = Object.create(AetherUpload);

    newInstance.wrapperDom = $(file).parents("#aetherupload-wrapper");

    newInstance.group = group;

    return newInstance;
}







