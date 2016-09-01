# aetherupload-laravel
提供超大文件上传的laravel扩展包，带百分比进度显示，支持断点续传，支持自定义中间件，基于laravel 5开发。  
  
我们知道，在以前，文件上传采用的是直接传整个文件的方式，这种方式对付一些小文件是没有问题的。而当需要上传大文件时，此种方式不仅操作繁琐，需要修改web服务器和后端语言的配置，而且会大量占用服务器的内存，导致服务器内存吃紧，严重的甚至传输超时或文件过大无法上传。很显然，普通的文件上传方式已无法满足现在越来越高的要求。  
  
随着技术的发展，如今我们可以利用HTML5的分块上传技术来轻松解决这个困扰，通过将大文件分割成小块逐个上传再拼合，来降低服务器内存的占用，突破服务器及后端语言配置中的上传大小限制，可上传任意大小的文件，同时也简化了操作，提供了直观的进度显示。
![示例页面](http://ww2.sinaimg.cn/large/69e23056gw1f59r78adaij20n70ft0u2.jpg)

# 用法
0) 在终端内切换到你的laravel项目根目录，执行`composer require peinhu/aetherupload-laravel dev-master`  

1) 在`config/app.php`的`providers`数组中添加一行`Peinhu\AetherUpload\AetherUploadServiceProvider::class,`  
  
2) 执行`php artisan vendor:publish`来发布一些文件和目录  
  
3) 赋予上传目录相应权限，在项目根目录下，执行`chmod 755 storage/app/uploads -R`    
  
4) 在浏览器访问`http://域名/aetherupload`可到达示例页面  

提示：更改相关配置选项请编辑`config/aetherupload.php`。  

# 优化建议
* （推荐）设置每天自动清除无效文件。  
由于上传流程存在意外终止的情况，如在传输过程中强行关闭页面或浏览器，将会导致已产生的临时文件成为无效文件，占据大量的存储空间，我们可以使用Laravel的任务调度功能来定期清除它们。  
在Linux中运行`crontab -e`命令，确保文件中包含这行代码：  
`* * * * * php /项目根目录的绝对路径/artisan schedule:run 1>> /dev/null 2>&1`  
在`app/Console/Kernel.php`中的`schedule`方法中添加以下代码：
```php
  $schedule->call(function () {
      (new \Peinhu\AetherUpload\Uploader())->cleanUpDir();
  })->daily();
```
* 提高临时文件读写速度。  
利用Linux的tmpfs文件系统，来达到将临时文件放到内存中快速读写的目的。执行以下命令：    
`mkdir /dev/shm/tmp`  
`chmod 1777 /dev/shm/tmp`  
`mount --bind /dev/shm/tmp /tmp`  

# 更新日志
**2016-07-13 v1.0.0正式版**  

添加完整说明，修正一些小问题。  

  
**2016-06-24 v1.0.0测试版**  


# 许可证
使用GPLv2许可证, 查看LICENCE文件以获得更多信息。

