
var AetherUpload = {

    init: function(){

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#aetherupload-file").change($.proxy(this.upload, this));

    },

    upload: function(){

        $("#aetherupload-output").text('开始上传');

        this.file = $("#aetherupload-file")[0].files[0],

        this.fileName = this.file.name,

        this.fileSize = this.file.size,

        this.uploadBasename = "",

        this.uploadExt = "",

        this.chunkSize = 0,

        this.chunkCount = 0,

        this.i = 0;
		
		var _this = this;
		
		$.post('/aetherupload/init',{file_name:_this.fileName,file_size:_this.fileSize},function(res){

		    if(res.error != 0){
                $("#aetherupload-output").text(res.error);
                return;
            }

			_this.uploadBasename = res.uploadBasename;

            _this.uploadExt = res.uploadExt;

            _this.chunkSize = res.chunkSize;

            _this.chunkCount = Math.ceil(_this.fileSize / _this.chunkSize),

			_this.uploadChunkInterval = setInterval($.proxy(_this.uploadChunk,_this),0);
		},'json');

    },

    uploadChunk:function(){

        if((this.i+1)==this.chunkCount){clearInterval(this.uploadChunkInterval);}

        var start = this.i * this.chunkSize,end = Math.min(this.fileSize, start + this.chunkSize);

        var form = new FormData();

        form.append("file", this.file.slice(start,end));

        form.append("upload_ext",this.uploadExt);

        form.append("chunk_total", this.chunkCount);

        form.append("chunk_index", this.i + 1);

        form.append("upload_basename",this.uploadBasename);

        var _this = this;

        $.ajax({

            url: "/aetherupload/upload",

            type: "POST",

            data: form,

            dataType: 'json',

            async: false,

            processData: false,

            contentType: false,

            success: function (res) {

                if (res.error != 0) {
                    $("#aetherupload-output").text(res.error);
                    clearInterval(_this.uploadChunkInterval);
                    return;
                }

                var percent = parseInt((_this.i + 1) / _this.chunkCount * 100) + "%";
                $("#aetherupload-bar").css("width", percent);
                $("#aetherupload-output").text(percent);

                if (res.complete) {
                    $("#aetherupload-uploadname") .val(res.uploadName);
                    $("#aetherupload-output").text('上传完毕！');
                    $("#aetherupload-file").attr('disabled', 'disabled');

                }

                ++_this.i;
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
				if(XMLHttpRequest.status===0){
					$("#aetherupload-output").text('网络故障，正在重试……');
					_this.sleep(3000);
				}else{
					$("#aetherupload-output").text('发生故障，上传失败。');
					clearInterval(_this.uploadChunkInterval);
				}
            }

        });

    },
    sleep:function(milliSecond){
        var wakeUpTime = new Date().getTime() + milliSecond;
        while (true) {
            if (new Date().getTime() > wakeUpTime) return;
        }
    }

};


$(function(){

    AetherUpload.init();

});
