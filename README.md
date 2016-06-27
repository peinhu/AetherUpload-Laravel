# aetherupload-laravel
提供超大文件上传的laravel扩展包，带百分比进度显示，支持断点续传，支持自定义中间件，基于laravel 5开发。

# 用法
0) 在终端内切换到你的laravel项目根目录，执行`composer require peinhu/aetherupload-laravel dev-master`  

1) 在`config/app.php`的`providers`数组中添加一行`Peinhu\AetherUpload\AetherUploadServiceProvider::class,`  
  
2) 执行`php artisan vendor:publish`来发布一些文件和目录  
  
3) 赋予上传目录相应权限，执行`chmod 755 /项目根目录的绝对路径/storage/app/uploads -R`    
  
4) 在浏览器访问`http://根域名/aetherupload`可到达示例页面  

提示：更改相关配置选项请编辑`config/aetherupload.php`。  

# 优化建议
* 利用Linux的tmpfs文件系统，来达到将临时文件放到内存中快速读写的目的。  
执行以下命令：  
`mkdir /dev/shm/tmp`  
`chmod 1777 /dev/shm/tmp`  
`mount --bind /dev/shm/tmp /tmp`  

# 贡献者  
[peinhu](https://github.com/peinhu)  
……(期待你的加入)

# 许可证
使用GPLv2许可证, 查看LICENCE文件以获得更多信息。

