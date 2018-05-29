<?php
//------------------------
// 分组管理验证器
//-------------------------

namespace api\common\validate;

use think\Validate;

class AdminGroup extends Validate
{
    protected $rule = [
        "name|分组名称" => "require|unique:admin_group",
    ];
}