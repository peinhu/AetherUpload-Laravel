<?php

return [

    "ENABLE_EXAMPLE_PAGE" => true, # 启用示例页面，访问域名/aetherupload/example或域名/aetherupload，生产环境下请将该选项设置为false
    "groups"              => [ # 分组，可设置多个不同分组，各自拥有独立配置
        "default" => [ # 默认分组，如新增请尽量使用movie,music之类有意义的分组名，获取资源时需拼接到路由地址中
            "UPLOAD_FILE_MAXSIZE"    => 0, # 被允许的上传文件大小（MB），0为不限制
            "UPLOAD_FILE_EXTENSIONS" => "", # 被允许的上传文件扩展名，空为不限制
            "UPLOAD_PATH"            => storage_path() . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "uploads", # 上传目录的本地物理路径
            "UPLOAD_FILE_DIR"        => "aetherupload_file", # 上传文件的目录
            "UPLOAD_HEAD_DIR"        => "aetherupload_head", # 指针头文件的目录
            "CHUNK_SIZE"             => 1 * 1000 * 1000, # 上传时的分块大小（B），默认为1M，越大传输越快，需要小于web服务器和php.ini中设置的上传限值
            "MIDDLEWARE_INIT"        => [], # 上传初始化时的路由中间件
            "MIDDLEWARE_SAVECHUNK"   => [], # 上传文件分块时的路由中间件
            "MIDDLEWARE_DISPLAY"     => [], # 文件展示时的路由中间件
            "MIDDLEWARE_DOWNLOAD"    => [], # 文件下载时的路由中间件
        ],

    ]
];