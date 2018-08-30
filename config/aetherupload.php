<?php

return [

    "ENABLE_EXAMPLE_PAGE"    => true, # 启用示例页面，访问域名/aetherupload，生产环境下请将该选项设置为false
    "DISTRIBUTED_DEPLOYMENT" => [
        "ENABLE"  => false, # 启用分布式部署，使应用服务与储存服务分离
        "ROLE"    => "web", # web|storage 服务器角色
        "WEB"     => [
            "STORAGE_HOST" => "", # 角色为web时，储存服务器的host
        ],
        "STORAGE" => [
            "MIDDLEWARE_CORS" => "", # 角色为storage时，跨域中间件AetherUploadCORS在Kernel.php中注册的名称
            "WEB_HOSTS"       => [], # 角色为storage时，跨域中间件AetherUploadCORS中允许的来源host
        ],
    ],
    "ROOT_DIR"               => "aetherupload", # 上传根目录的名称
    "CHUNK_SIZE"             => 1 * 1000 * 1000, # 上传时的分块大小（B），建议1M～4M之间，需要小于web服务器和php.ini中的上传限值
    "RESOURCE_SUBDIR_RULE"   => "month", # year|month|date|static 资源目录的子目录生成规则
    "HEADER_STORAGE_DISK"    => "local", # local|redis 头文件所储存disk的配置名称，详见config/filesystems.php
    "GROUPS"                 => [ # 资源分组，可设置多个不同分组，各自拥有独立配置
        "file"  => [ # 分组名
            "RESOURCE_MAXSIZE"             => 0, # 被允许的资源文件大小（MB），0为不限制
            "RESOURCE_EXTENSIONS"          => [], # 被允许的资源文件扩展名，空为不限制
            "MIDDLEWARE_PREPROCESS"        => [], # 上传预处理时的路由中间件
            "MIDDLEWARE_SAVE_CHUNK"        => [], # 上传文件分块时的路由中间件
            "MIDDLEWARE_DISPLAY"           => [], # 文件展示时的路由中间件
            "MIDDLEWARE_DOWNLOAD"          => [], # 文件下载时的路由中间件
            "EVENT_BEFORE_UPLOAD_COMPLETE" => "", # 上传完成前触发的事件（临时文件），PartialFileHandler的实例被注入
            "EVENT_UPLOAD_COMPLETE"        => "", # 上传完成后触发的事件（已存文件），PartialFileHandler的实例被注入
        ],

    ],
];