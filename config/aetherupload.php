<?php

return [

    "ENABLE_EXAMPLE_PAGE" => true, # 启用示例页面，访问域名/aetherupload，生产环境下请将该选项设置为false
    "CHUNK_SIZE"          => 1 * 1000 * 1000, # 上传时的分块大小（B），默认为1M，越大传输越快，需要小于web服务器和php.ini中设置的上传限值
    "UPLOAD_PATH"         => storage_path() . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "aetherupload", # 上传目录的本地物理路径
    "HEAD_DIR"            => "_head", # 指针头文件目录的名称，建议保持默认
    "FILE_SUB_DIR"        => @date("Ym", time()), #资源文件目录的子目录生成规则，变量或常量均可
    "REDIS_KEY"           => "aetherupload_file_hashes", #redis中hashes的key名称
    "GROUPS"              => [ # 分组，可设置多个不同分组，各自拥有独立配置
        "file" => [ # 新增分组请尽量使用video、audio等有意义的分组名
            "FILE_MAXSIZE"                 => 0, # 被允许的资源文件大小（MB），0为不限制
            "FILE_EXTENSIONS"              => "", # 被允许的资源文件扩展名，空为不限制，多个值以逗号分隔
            "MIDDLEWARE_PREPROCESS"        => [], # 上传预处理时的路由中间件
            "MIDDLEWARE_SAVE_CHUNK"        => [], # 上传文件分块时的路由中间件
            "MIDDLEWARE_DISPLAY"           => [], # 文件展示时的路由中间件
            "MIDDLEWARE_DOWNLOAD"          => [], # 文件下载时的路由中间件
            "EVENT_BEFORE_UPLOAD_COMPLETE" => '', # 上传完成前触发的事件（临时文件），Receiver的实例被注入
            "EVENT_UPLOAD_COMPLETE"        => '', # 上传完成后触发的事件（已存文件），Receiver的实例被注入
        ],

    ],
];