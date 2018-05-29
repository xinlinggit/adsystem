#!/usr/bin/env php
<?php
// [ 应用入口文件 ]

// 定义应用目录
define('SHELL_PATH', __DIR__ . '/');
define('PHP_EXE', '/usr/local/bin/php');
define('APP_PATH', __DIR__ . '/../cms/');
define('FOLDER_PATH', __DIR__ . '/folder/');
define('APP_NAMESPACE','cms');
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
