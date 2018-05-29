<?php
//------------------------
// 角色验证器
//-------------------------

namespace api\common\validate;

use think\Validate;

class AdminRole extends Validate
{
    protected $rule = [
        "name|名称"   => "require|unique:admin_role",
        "status|状态" => "require",
    ];
}