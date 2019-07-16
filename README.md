# AetherUpload-Laravel  
[![996.icu](https://img.shields.io/badge/link-996.icu-red.svg)](https://996.icu)
[![Build Status](https://travis-ci.org/peinhu/AetherUpload-Laravel.svg?branch=master)](https://travis-ci.org/peinhu/AetherUpload-Laravel)
[![Latest Stable Version](https://poser.pugx.org/peinhu/aetherupload-laravel/v/stable)](https://packagist.org/packages/peinhu/aetherupload-laravel)
[![Total Downloads](https://poser.pugx.org/peinhu/aetherupload-laravel/downloads)](https://packagist.org/packages/peinhu/aetherupload-laravel)
[![Latest Unstable Version](https://poser.pugx.org/peinhu/aetherupload-laravel/v/unstable)](https://packagist.org/packages/peinhu/aetherupload-laravel)
[![License](https://poser.pugx.org/peinhu/aetherupload-laravel/license)](https://github.com/peinhu/AetherUpload-Laravel/blob/master/LICENSE)
[![LICENSE](https://img.shields.io/badge/license-Anti%20996-blue.svg)](https://github.com/peinhu/AetherUpload-Laravel/blob/master/LICENSE_996)
    
提供**超大文件**上传的Laravel扩展包，支持**分组配置**、**断线续传**、**秒传**、**分布式部署**等功能，简单易用，满足多数人的主流需求。**无感知化**的设计理念，可实现由扩展自动接管上传和访问请求，开发者专注于业务，**无需关心**上传流程，**无需编写**适配代码，几乎**开箱即用**，节省大量开发时间。基于Laravel 5开发，支持5.1+版本。永久免费不接受赞助！欢迎提出问题和建议！

我们知道，在以前，文件上传采用的是直接传整个文件的方式，这种方式对付一些小文件是没有问题的。而当需要上传大文件时，此种方式不仅操作繁琐，需要修改web服务器和后端语言的配置，而且会大量占用服务器的内存，导致服务器内存吃紧，严重的甚至传输超时或文件过大无法上传。很显然，普通的文件上传方式已无法满足现在越来越高的要求。  
  
随着技术的发展，如今我们可以利用HTML5的分块上传技术来轻松解决这个困扰，通过将大文件分割成小块逐个上传再拼合，来降低服务器内存的占用，突破服务器及后端语言配置中的上传大小限制，可上传任意大小的文件，同时也简化了操作，提供了直观的进度显示。 

![示例页面](http://wx2.sinaimg.cn/mw690/69e23056gy1fho6ymepjlg20go0aknar.gif) 

# 功能特性
- [x] 百分比进度条  
- [x] 文件类型限制  
- [x] 文件大小限制  
- [x] 多语言  
- [x] 分组配置  
- [x] 自定义中间件   
- [x] 自定义路由   
- [x] 上传完成事件   
- [x] 同步上传 *①*  
- [x] 断线续传 *②*  
- [x] 文件秒传 *③* 
- [x] 分布式部署 *④*  

*①：同步上传相比异步上传，在上传带宽足够大的情况下速度稍慢，但同步可在上传同时进行文件的拼合，而异步因文件块上传完成的先后顺序不确定，需要在所有文件块都完成时才能拼合，将会导致异步上传在接近完成时需等待较长时间。同步上传每次只有一个文件块在上传，在单位时间内占用服务器的内存较少，相比异步方式可支持更多人同时上传。*  

*②：断线续传和断点续传不同，断线续传是指遇到断网或无线网络不稳定时，在不关闭页面的情况下，上传组件会定时自动重试，一旦网络恢复，文件会从未上传成功的那个文件块开始继续上传。断线续传在刷新页面或关闭后重开是无法续传的，之前上传的部分已成为无效文件。*  

*③：文件秒传需服务端Redis和客户端浏览器支持(FileReader、File.slice())，两者缺一则秒传功能无法生效。默认关闭，需在配置文件中开启。* 

*④：分布式部署需要在应用服务器与储存服务器进行跨域配置，通过填写相关配置项可实现自动跨域，并共享cookie和session。*  

# 用法
**安装**  

0 在终端内切换到你的laravel项目根目录，执行`composer require peinhu/aetherupload-laravel ~2.0`  

1 （Laravel 5.5+请跳过）在`config/app.php`的`providers`数组中添加一行`AetherUpload\AetherUploadServiceProvider::class,`  
  
2 执行`php artisan aetherupload:publish`来发布一些文件和目录    
  
3 在浏览器访问`http://域名/aetherupload`可到达示例页面  

*提示：更改相关配置选项请编辑`config/aetherupload.php`。*  

**基本用法**  
  
文件上传：参考示例文件注释的部分，在需要上传大文件的页面引入相应文件和代码。可使用自定义中间件来对文件上传进行额外过滤，还可使用上传完成事件对上传的文件进一步处理。  

分组配置：在配置文件的groups下新增分组，运行`php artisan aetherupload:groups`自动创建对应目录。  

自定义中间件：参考laravel文档中间件部分，创建你的中间件并在`Kernel.php`中注册，将你注册的中间件名称填入配置文件对应部分，如`['middleware1','middleware2']`。  

上传完成事件：分为上传完成前和上传完成后事件，参考laravel文档事件系统部分，在`EventServiceProvider`中注册你的事件和监听器，运行`php artisan event:generate`生成事件和监听器，将你注册的事件完整类名填入配置文件对应部分，如'App\Events\OrderShipped'。

**添加秒传功能（需Redis及浏览器支持）**

安装Redis并启动服务端。安装predis包`composer require predis/predis`。确保上传页面引入了spark-md5.min.js文件。

*提示：在Redis中维护了一份与实际资源文件对应的秒传清单，实际资源文件的增删造成的变化均需要同步到秒传清单中，否则会产生脏数据，扩展包已包含新增部分，当删除资源文件时，使用者需手动调用对应方法删除秒传清单中的记录。* 
```php
\AetherUpload\Util::deleteResource($savedPath); //删除对应的资源文件
\AetherUpload\Util::deleteRedisSavedPath($savedPath); //删除对应的redis秒传记录
```

**分布式部署（需Redis及域名跨域支持）**

分布式部署通过将应用服务器与储存服务器分离，可减少应用服务器负载，增加应用并发连接数，降低耦合，减少单点故障风险，提高访问效率，启用分布式部署后应用服务器将不处理任何上传和访问请求。  

安装Redis并启动服务端。安装predis包`composer require predis/predis`。确保上传页面表单中包含`{{ storage_host_field() }}`。

应用服务器配置：  
在`config/aetherupload.php`中配置`distributed_deployment`项，将`enable`设置为`true`，`role`设置为`web`，`storage_host`设置为储存服务器的域名`http://storage.your-domain.com`。  
在`.env`中将`APP_NAME`和`APP_KEY`配置项改为对应特定值，与储存服务器配置一致。新增配置`SESSION_DOMAIN=.your-domain.com`，用以共享cookie。修改配置`SESSION_DRIVER=redis`，用以共享session。  

储存服务器配置：  
在`config/aetherupload.php`中配置`distributed_deployment`项，将`enable`设置为`true`，`role`设置为`storage`，`middleware_cors`设置为跨域中间件AetherUploadCORS类在Kernel.php中注册的名称，`allow_origin`设置为应用服务器的域名`http://www.your-domain.com`。  
在`.env`中将`APP_NAME`和`APP_KEY`配置项改为对应特定值，与应用服务器配置一致。新增配置`SESSION_DOMAIN=.your-domain.com`，用以共享cookie。修改配置`SESSION_DRIVER=redis`，用以共享session。  
  
**使用方便的artisan命令**  

`php artisan aetherupload:groups` 列出所有分组并自动创建对应目录  
`php artisan aetherupload:build` 在Redis中重建资源文件的秒传清单  
`php artisan aetherupload:clean 2` 清除2天前的无效临时文件  
`php artisan aetherupload:publish` vendor:publish的简化命令，覆盖发布一些目录和文件

# 优化建议
* （推荐）设置每天自动清除无效的临时文件。  
由于上传流程存在意外终止的情况，如在传输过程中强行关闭页面或浏览器，将会导致已产生的文件部分成为无效文件，占据大量的存储空间，我们可以使用Laravel的任务调度功能来定期清除它们。  
在Linux中运行`crontab -e`命令，确保文件中包含这行代码：  
```php
* * * * * php /项目根目录的绝对路径/artisan schedule:run 1>> /dev/null 2>&1  
```  
在`app/Console/Kernel.php`中的`schedule`方法中添加以下代码：
```php
  $schedule->command('aetherupload:clean 2')->daily();
```  
* （推荐）提高头文件读写效率。  
通过将头文件的文件系统由本地硬盘改为Redis，提高头文件读写效率。  
在`config/aetherupload.php`中将`header_storage_disk`项对应值改为`redis`。  
在`config/filesystems.php`的`disks`项中添加`redis`配置：
```php
    'disks' => [
        ...
        'redis' => [
           'driver' => 'redis',
           'disable_asserts'=>true,
        ],
        ...
    ]
```  
* 设置每天自动重建Redis中的秒传清单。  
不恰当的处理和某些极端情况可能使秒传清单中出现脏数据，从而影响到秒传功能的准确性，重建秒传清单可消除脏数据，恢复与实际资源文件的同步。  
在Linux中运行`crontab -e`命令，确保文件中包含这行代码：  
```php
* * * * * php /项目根目录的绝对路径/artisan schedule:run 1>> /dev/null 2>&1  
```  
在`app/Console/Kernel.php`中的`schedule`方法中添加以下代码：
```php
  $schedule->command('aetherupload:build')->daily();
```  
* 提高分块临时文件读写速度（仅对PHP生效）。  
利用Linux的tmpfs文件系统，来达到将上传的分块临时文件放到内存中快速读写的目的，通过以空间换时间，提升读写效率，将会**额外占用**部分内存（约1个分块大小）。  
将php.ini中上传临时目录`upload_tmp_dir`项的值设置为`"/dev/shm"`，重启fpm或apache服务。  

* 提高分块临时文件读写速度（对系统临时目录生效）。  
利用Linux的tmpfs文件系统，来达到将上传的分块临时文件放到内存中快速读写的目的，通过以空间换时间，提升读写效率，将会**额外占用**部分内存（约1个分块大小）。  
执行以下命令：    
`mkdir /dev/shm/tmp`  
`chmod 1777 /dev/shm/tmp`  
`mount --bind /dev/shm/tmp /tmp`  

# 兼容性
<table>
  <th></th>
  <th>IE</th>
  <th>Edge</th>
  <th>Firefox</th>
  <th>Chrome</th>
  <th>Safari</th>
  <tr>
  <td>上传</td>
  <td>10+</td>
  <td>12+</td>
  <td>3.6+</td>
  <td>6+</td>
  <td>5.1+</td>
  </tr>
  <tr>
  <td>秒传</td>
  <td>10+</td>
  <td>12+</td>
  <td>3.6+</td>
  <td>6+</td>
  <td>6+</td>
  </tr>
</table>

# 安全性
AetherUpload在上传前使用白名单+黑名单的形式进行文件后缀名过滤，上传后再检查文件的Mime-Type类型。白名单直接限制了保存文件扩展名，黑名单默认屏蔽了常见的可执行文件扩展名，来阻止上传恶意文件，安全起见白名单一栏不应留空。  

虽然做了诸多安全工作，但恶意文件上传是防不胜防的，建议正确设置上传目录权限，确保相关程序对资源文件没有执行权限。

# 更新日志  
**2019-07-16 v2.0.7**  
添加宽松模式支持    
优化代码 

详见[CHANGELOG.md](https://github.com/peinhu/AetherUpload-Laravel/blob/master/CHANGELOG.md)  

# 衍生项目  
[laravel-admin](https://github.com/z-song/laravel-admin)表单扩展：[large-file-upload](https://github.com/laravel-admin-extensions/large-file-upload)

# 许可证
使用GPLv2许可证及Anti 996许可证, 查看[LICENCE](https://github.com/peinhu/AetherUpload-Laravel/blob/master/LICENSE)文件及[LICENSE_996](https://github.com/peinhu/AetherUpload-Laravel/blob/master/LICENSE_996)文件以获得更多信息。  
