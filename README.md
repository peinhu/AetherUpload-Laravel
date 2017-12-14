# AetherUpload-Laravel  
[![Build Status](https://travis-ci.org/peinhu/AetherUpload-Laravel.svg?branch=master)](https://travis-ci.org/peinhu/AetherUpload-Laravel)
[![Latest Stable Version](https://poser.pugx.org/peinhu/aetherupload-laravel/v/stable)](https://packagist.org/packages/peinhu/aetherupload-laravel)
[![Total Downloads](https://poser.pugx.org/peinhu/aetherupload-laravel/downloads)](https://packagist.org/packages/peinhu/aetherupload-laravel)
[![Latest Unstable Version](https://poser.pugx.org/peinhu/aetherupload-laravel/v/unstable)](https://packagist.org/packages/peinhu/aetherupload-laravel)
[![License](https://poser.pugx.org/peinhu/aetherupload-laravel/license)](https://packagist.org/packages/peinhu/aetherupload-laravel)  
    
提供**超大文件**上传的Laravel扩展包，支持**分组配置**、**断线续传**、**秒传**等功能，简单易用，满足多数人的主流需求，**无需编写**适配代码，几乎开箱即用。基于Laravel 5开发，目前支持5.1~5.5版本。  

我们知道，在以前，文件上传采用的是直接传整个文件的方式，这种方式对付一些小文件是没有问题的。而当需要上传大文件时，此种方式不仅操作繁琐，需要修改web服务器和后端语言的配置，而且会大量占用服务器的内存，导致服务器内存吃紧，严重的甚至传输超时或文件过大无法上传。很显然，普通的文件上传方式已无法满足现在越来越高的要求。  
  
随着技术的发展，如今我们可以利用HTML5的分块上传技术来轻松解决这个困扰，通过将大文件分割成小块逐个上传再拼合，来降低服务器内存的占用，突破服务器及后端语言配置中的上传大小限制，可上传任意大小的文件，同时也简化了操作，提供了直观的进度显示。 

![示例页面](http://wx2.sinaimg.cn/mw690/69e23056gy1fho6ymepjlg20go0aknar.gif) 

# 功能特性
- [x] 百分比进度条  
- [x] 文件类型和大小限制  
- [x] 分组配置  
- [x] 自定义中间件   
- [x] 上传完成事件   
- [x] 同步上传 *①*  
- [x] 断线续传 *②*  
- [x] 文件秒传 *③* 

*①：同步上传相比异步上传，在上传带宽足够大的情况下速度稍慢，但同步可在上传同时进行文件的拼合，而异步因文件块上传完成的先后顺序不确定，需要在所有文件块都完成时才能拼合，将会导致异步上传在接近完成时需等待较长时间。同步上传每次只有一个文件块在上传，在单位时间内占用服务器的内存较少，相比异步方式可支持更多人同时上传。*  

*②：断线续传和断点续传不同，断线续传是指遇到断网或无线网络不稳定时，在不关闭页面的情况下，上传组件会定时自动重试，一旦网络恢复，文件会从未上传成功的那个文件块开始继续上传。断线续传在刷新页面或关闭后重开是无法续传的，之前上传的部分已成为无效文件。*  

*③：文件秒传需服务端Redis和客户端浏览器支持(FileReader、File.slice())，两者缺一则秒传功能无法生效。* 

# 用法
**安装**  

0 在终端内切换到你的laravel项目根目录，执行`composer require peinhu/aetherupload-laravel ~1.0`  

1 （Laravel 5.5请跳过）在`config/app.php`的`providers`数组中添加一行`AetherUpload\AetherUploadServiceProvider::class,`  
  
2 执行`php artisan aetherupload:publish`来发布一些文件和目录  
  
3 赋予上传目录相应权限，在项目根目录下，执行`chmod -R 755 storage/app/aetherupload`    
  
4 在浏览器访问`http://域名/aetherupload`可到达示例页面  

*提示：更改相关配置选项请编辑`config/aetherupload.php`。*  

**基本用法**  
  
文件上传：参考示例文件注释的部分，在需要上传大文件的页面引入相应文件和代码。  

分组配置：在配置文件的GROUPS下新增分组，运行`php artisan aetherupload:groups`自动创建对应目录。  

自定义中间件：参考laravel文档中间件部分，创建你的中间件并在`Kernel.php`中注册，将你注册的中间件名称填入配置文件对应部分，如['middleware1','middleware2']。  

上传完成事件：分为上传完成前和上传完成后事件，参考laravel文档事件系统部分，在`EventServiceProvider`中注册你的事件和监听器，运行`php artisan event:generate`生成事件和监听器，将你注册的事件完整类名填入配置文件对应部分，如'App\Events\OrderShipped'。

**添加秒传功能（需Redis及浏览器支持）**

安装Redis并启动服务端。安装predis包`composer require predis/predis`，在.env文件中配置Redis的相关参数。确保上传页面引入了spark-md5.min.js文件。

*提示：在Redis中维护了一份与实际资源文件对应的hash清单，文件的md5哈希值为资源文件的唯一标识符，实际资源文件的增删造成的变化均需要同步到hash清单中，否则会产生脏数据，扩展包已包含新增部分，删除（deleteOneHash）则需要使用者自行调用相关方法处理，详情参考RedisHandler类。*   

**使用方便的artisan命令**  

`php artisan aetherupload:groups` 列出所有分组并自动创建对应目录  
`php artisan aetherupload:build` 在Redis中重建资源文件的hash清单  
`php artisan aetherupload:clean` 清除几天前的无效临时文件  
`php artisan aetherupload:publish` vendor:publish的简化命令，覆盖发布一些目录和文件

# 优化建议
* （推荐）设置每天自动清除无效的临时文件。  
由于上传流程存在意外终止的情况，如在传输过程中强行关闭页面或浏览器，将会导致已产生的文件部分成为无效文件，占据大量的存储空间，我们可以使用Laravel的任务调度功能来定期清除它们。  
在Linux中运行`crontab -e`命令，确保文件中包含这行代码：  
`* * * * * php /项目根目录的绝对路径/artisan schedule:run 1>> /dev/null 2>&1`  
在`app/Console/Kernel.php`中的`schedule`方法中添加以下代码：
```php
  $schedule->call(function () {
      \AetherUpload\ResourceHandler::cleanUpDir();
  })->daily();
```  
* 设置每天自动重建Redis中的hash清单。  
不恰当的处理和某些极端情况可能使hash清单中出现脏数据，从而影响到秒传功能的准确性，重建hash清单可消除脏数据，恢复与实际资源文件的同步。  
在Linux中运行`crontab -e`命令，确保文件中包含这行代码：  
`* * * * * php /项目根目录的绝对路径/artisan schedule:run 1>> /dev/null 2>&1`  
在`app/Console/Kernel.php`中的`schedule`方法中添加以下代码：
```php
  $schedule->call(function () {
      \AetherUpload\RedisHandler::build();
  })->daily();
```
* 提高临时文件读写速度。  
利用Linux的tmpfs文件系统，来达到将临时文件放到内存中快速读写的目的。执行以下命令：    
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
AetherUpload并未使用Content-Type(Mime-Type)来检测上传文件类型，而是以白名单的形式直接限制了保存文件扩展名，来阻止上传可执行文件(默认屏蔽了常见的可执行文件扩展名)，因为Content-Type(Mime-Type)也可伪造，无法起到应有的作用，安全起见白名单一栏不应留空。  

虽然做了诸多安全工作，但恶意文件上传是防不胜防的，建议确保所有上传目录权限为755，文件权限为644。  

# 更新日志  
**2017-09-11 v1.0.6**  
添加多控件调用支持  
简化命名空间

**2017-08-03 v1.0.5**  
添加上传完成前事件  
添加上传完成后事件

**2017-07-31 v1.0.4**  
添加自动创建分组目录的artisan命令  
修正当predis不存在时会报错的情况  

**2017-07-18 v1.0.3**  
添加文件秒传支持  
添加方便的artisan命令  

**2017-05-08 v1.0.2**  
添加分组配置支持  
添加子目录支持  

**2017-04-27 v1.0.1**  
修正[局部刷新导致的上传无响应问题](https://github.com/peinhu/AetherUpload-Laravel/issues/6)  
后端代码结构大幅优化  
后端代码格式统一规范化  
前端代码改进  
一些其它改进  

**2016-07-13 v1.0.0正式版**  
添加完整说明，修正一些小问题。  

**2016-06-24 v1.0.0测试版**  
初次提交

# 许可证
使用GPLv2许可证, 查看LICENCE文件以获得更多信息。

