<?php

namespace AetherUpload;

use Composer\Script\Event;

class Script
{
    public static function checkTimeZone(Event $event)
    {
        if ( exec('date "+%H:%M"  ') === date('H:i', time()) ) {
            echo 'Success: 通过时区一致性检测。' . PHP_EOL;
        } else {
            echo 'Warning: 请检查Laravel和系统时区的设置，不一致可能会导致任务调度和子目录命名出现异常。' . PHP_EOL;
        }
    }
}