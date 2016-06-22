# AetherUpload-laravel
提供超大文件上传的laravel扩展包，基于laravel 5开发。

# 用法
0) 在终端内切换到你的laravel项目根目录，执行`composer require "peinhu/AetherUpload-laravel"`  
  
1) 执行`php artisan vendor:publish`来发布一些文件和目录  
  
2) 赋予上传目录相应权限，执行`chmod 755 /到项目根目录的路径/storage/app/uploads -R`  
  
3) 在`config/app.php`的`providers`数组中添加一行`Peinhu\AetherUpload\AetherUploadServiceProvider::class,`  
  
4) 在浏览器访问`http://根域名/aetherupload`可到达示例页面  
  
# 许可证
使用GPLv2许可证, 查看LICENCE文件以获得更多信息。

