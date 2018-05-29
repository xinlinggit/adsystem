<?php
// +----------------------------------------------------------------------
// | Author: zengjie@cnfol.com
// +----------------------------------------------------------------------
namespace app\admin\validate;

use think\Validate;

/**
 * 插件验证器
 * @package app\admin\validate
 */
class Plugins extends Validate
{
    //定义验证规则
    protected $rule = [
        'name|插件名' => 'require|unique:admin_plugins',
        'title|插件标题'    => 'require',
        'identifier|插件标识'  => 'require|unique:admin_plugins',
    ];
}
