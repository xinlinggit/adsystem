<?php
// +----------------------------------------------------------------------
// | Author: zengjie@cnfol.com
// +----------------------------------------------------------------------
// [ 后台入口文件 ]
header('Content-Type:text/html;charset=utf-8');
// 检测PHP环境
if(version_compare(PHP_VERSION,'5.5.0','<'))  die('PHP版本过低，最少需要PHP5.5，请升级PHP版本！');

// 定义应用目录
define('APP_PATH', __DIR__ . '/../front/');

// 定义入口为admin
define('ENTRANCE', 'admin');

// 检查是否安装
if(!is_file(APP_PATH.'install/install.lock')) {
    header('Location: /');
    exit;
}

// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';