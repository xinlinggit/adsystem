<?php
// +----------------------------------------------------------------------
// | Author: zengjie@cnfol.com
// +----------------------------------------------------------------------
namespace app\admin\validate;

use think\Validate;

/**
 * 钩子验证器
 * @package app\admin\validate
 */
class Hook extends Validate
{
    //定义验证规则
    protected $rule = [
        // 'name|钩子名称' => 'require|unique:admin_hook',
    ];
}
