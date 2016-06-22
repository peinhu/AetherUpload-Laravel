<?php

return [
    "ENABLE_EXAMPLE_PAGE" => true,# 启用示例页面，访问aetherupload/example或aetherupload，生产环境下请将该选项设置为false
    "UPLOAD_FILE_MAXSIZE" => 0,# 被允许的上传文件大小（MB），0为不限制
    "UPLOAD_FILE_EXTENSIONS" => "",# 被允许的上传文件扩展名，空为不限制
    "UPLOAD_PATH" => storage_path().DIRECTORY_SEPARATOR."app".DIRECTORY_SEPARATOR."uploads", # 上传目录的本地物理路径
    "UPLOAD_FILE_DIR" => DIRECTORY_SEPARATOR."aetherupload_file", # 上传文件的目录
    "UPLOAD_HEAD_DIR" => DIRECTORY_SEPARATOR."aetherupload_head", # 头部指针文件的目录
    "CHUNK_SIZE" => 2 * 1024 * 1024,# 上传时的分块大小（B），默认为2M，不能大于web服务器和php.ini中设置的上传限值
    "MIDDLEWARE_UPLOAD" => [],# 文件上传时的路由中间件
    "MIDDLEWARE_DISPLAY" => [],# 文件展示时的路由中间件
    "MIDDLEWARE_DOWNLOAD" => [],# 文件下载时的路由中间件

];