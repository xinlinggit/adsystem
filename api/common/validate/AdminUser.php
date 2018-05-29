<?php
//------------------------
// 用户验证器
//-------------------------

namespace api\common\validate;

use think\Validate;

class AdminUser extends Validate
{
    protected $rule = [
        "realname|姓名" => "require",
        "account|帐号"  => "unique:admin_user",
    ];
}