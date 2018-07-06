<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]

// 定义应用目录
define('SHELL_PATH', __DIR__ . '/');
define('PHP_EXE', '/usr/local/php/bin/php');
define('APP_PATH', __DIR__ . '/../cms/');
define('FOLDER_PATH', __DIR__ . '/upload/');
define('APP_NAMESPACE','cms');
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
