var AetherUpload = {

    upload: function () {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        this.resourceDom = this.wrapperDom.find('#aetherupload-resource'),

            this.outputDom = this.wrapperDom.find('#aetherupload-output'),

            this.progressBarDom = this.wrapperDom.find('#aetherupload-progressbar'),

            this.savedPathDom = this.wrapperDom.find('#aetherupload-savedpath'),

            this.resource = this.resourceDom[0].files[0],

            this.resourceName = this.resource.name,

            this.resourceSize = this.resource.size,

            this.resourceTempBaseName = '',

            this.resourceExt = '',

            this.chunkSize = 0,

            this.chunkCount = 0,

            this.groupSubdir = '',

            this.savedPath = '',

            this.resourceHash = '',

            this.blobSlice = File.prototype.slice || File.prototype.mozSlice || File.prototype.webkitSlice,

            this.i = 0,

            this.locale,

            this.messages = this.getLocalizedMessages(),

            this.storageHost = $('#aetherupload-storage-host').length ? $('#aetherupload-storage-host').val() : '';

        if (!this.blobSlice) {

            this.outputDom.text(this.messages.error_unsupported_browser);

            return;

        }

        if (this.resourceSize === 0) {

            this.outputDom.text(this.messages.error_invalid_resource_size);

            return;

        }

        if (this.resourceName.substring(this.resourceName.lastIndexOf('.') + 1, this.resourceName.length) === '') {

            this.outputDom.text(this.messages.error_invalid_resource_type);

            return;

        }

        this.outputDom.text(this.messages.status_upload_begin);

        if (!('FileReader' in window) || !('File' in window)) {

            this.preprocess(); //浏览器不支持读取本地文件，跳过计算hash

        } else {

            this.calculateHash();

        }

    },

    calculateHash: function () { //计算hash

        var _this = this,

            chunkSize = 2000000,

            chunks = Math.ceil(_this.resource.size / chunkSize),

            currentChunk = 0,

            spark = new SparkMD5.ArrayBuffer(),

            fileReader = new FileReader();

        fileReader.onload = function (e) {

            spark.append(e.target.result);

            ++currentChunk;

            _this.outputDom.text(_this.messages.status_hashing + ' ' + parseInt(currentChunk / chunks * 100) + '%');

            if (currentChunk < chunks) {

                loadNext();

            } else {

                _this.resourceHash = spark.end();

                _this.preprocess();

            }
        };

        fileReader.onerror = function () {

            _this.preprocess();

        };

        function loadNext() {

            var start = currentChunk * chunkSize,

                end = start + chunkSize >= _this.resource.size ? _this.resource.size : start + chunkSize;

            fileReader.readAsArrayBuffer(_this.blobSlice.call(_this.resource, start, end));

        }

        loadNext();

    },

    preprocess: function () { //预处理

        var _this = this;

        $.ajax({

            url: _this.storageHost + '/aetherupload/preprocess',

            type: 'POST',

            dataType: 'json',

            xhrFields: {
                withCredentials: true
            },

            crossDomain: true,

            data: {

                resource_name: _this.resourceName,

                resource_size: _this.resourceSize,

                resource_hash: _this.resourceHash,

                locale: _this.locale,

                group: _this.group

            },

            success: function (rst) {

                if (rst.error) {

                    _this.outputDom.text(rst.error);

                    return;
                }

                _this.resourceTempBaseName = rst.resourceTempBaseName;

                _this.resourceExt = rst.resourceExt;

                _this.chunkSize = rst.chunkSize;

                _this.chunkCount = Math.ceil(_this.resourceSize / _this.chunkSize);

                _this.groupSubdir = rst.groupSubdir;

                if (rst.savedPath.length === 0) {

                    _this.uploadChunkInterval = setInterval($.proxy(_this.uploadChunk, _this), 0);

                } else {

                    _this.progressBarDom.css('width', '100%');

                    _this.savedPath = rst.savedPath;

                    _this.savedPathDom.val(_this.savedPath);

                    _this.resourceDom.attr('disabled', 'disabled');

                    _this.outputDom.text(_this.messages.status_instant_completion_success);

                    typeof(_this.callback) !== 'undefined' ? _this.callback() : null;

                }

            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {

                _this.outputDom.text(_this.messages.error_upload_fail);

            }

        });

    },

    uploadChunk: function () {

        var _this = this,

            start = this.i * this.chunkSize,

            end = Math.min(this.resourceSize, start + this.chunkSize),

            form = new FormData();

        form.append('resource_chunk', this.resource.slice(start, end));

        form.append('resource_ext', this.resourceExt);

        form.append('chunk_total', this.chunkCount);

        form.append('chunk_index', this.i + 1);

        form.append('resource_temp_basename', this.resourceTempBaseName);

        form.append('group', this.group);

        form.append('group_subdir', this.groupSubdir);

        form.append('locale', this.locale);

        form.append('resource_hash', this.resourceHash);

        $.ajax({

            url: _this.storageHost + '/aetherupload/uploading',

            type: 'POST',

            data: form,

            dataType: 'json',

            xhrFields: {
                withCredentials: true
            },

            cache: false,

            crossDomain: true,

            async: false,

            processData: false,

            contentType: false,

            success: function (rst) {

                if ((rst instanceof Object) !== true) {

                    _this.outputDom.text(_this.messages.error_invalid_server_return);

                    clearInterval(_this.uploadChunkInterval);

                    return;
                }

                if (rst.error === 'undefined' || rst.error) {

                    _this.outputDom.text(rst.error);

                    clearInterval(_this.uploadChunkInterval);

                    return;

                }

                var percent = parseInt((_this.i + 1) / _this.chunkCount * 100);

                _this.progressBarDom.css('width', percent + '%');

                _this.outputDom.text(_this.messages.status_uploading + ' ' + percent + '%');

                if (rst.savedPath !== 'undefined' && rst.savedPath !== '') {

                    clearInterval(_this.uploadChunkInterval);

                    _this.savedPath = rst.savedPath;

                    _this.savedPathDom.val(_this.savedPath);

                    _this.resourceDom.attr('disabled', 'disabled');

                    _this.outputDom.text(_this.messages.status_upload_succeed);

                    _this.progressBarDom.css('width', '100%');

                    typeof(_this.callback) !== 'undefined' ? _this.callback() : null;

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

        this.locale = 'en';

        return this.text[this.locale];

    },

    text: {
        en: {
            status_upload_begin: 'upload begin',
            error_unsupported_browser: 'Error: unsupported browser',
            status_hashing: 'hashing',
            status_instant_completion_success: 'upload succeed (instant completion) ',
            status_uploading: 'uploading',
            status_upload_succeed: 'upload succeed',
            status_retrying: 'network problem, retrying...',
            error_upload_fail: 'Error: upload fail',
            error_invalid_server_return: 'Error: invalid server return value',
            error_invalid_resource_size: 'Error: invalid resource size',
            error_invalid_resource_type: 'Error: invalid resource type'
        },
        zh: {
            status_upload_begin: '开始上传',
            error_unsupported_browser: '错误：上传组件不被此浏览器支持',
            status_hashing: '正在哈希',
            status_instant_completion_success: '上传成功（秒传）',
            status_uploading: '正在上传',
            status_upload_succeed: '上传成功',
            status_retrying: '网络故障，正在重试……',
            error_upload_fail: '错误：上传失败',
            error_invalid_server_return: '错误：无效的服务器返回值',
            error_invalid_resource_size: '错误：无效的文件大小',
            error_invalid_resource_type: '错误：无效的文件类型'
        }
    }


};

/*
 * 创建AetherUpload对象的全局方法
 * resource 文件对象
 * group 分组名
 */
function aetherupload(resource, group) {

    var newInstance = Object.create(AetherUpload);

    newInstance.wrapperDom = $(resource).parents('#aetherupload-wrapper');

    newInstance.group = group;

    return newInstance;
}







