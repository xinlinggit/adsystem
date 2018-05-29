<?php

//------------------------
// 节点快速导入验证器
//-------------------------

namespace cms\common\validate;

use think\Validate;

class AdminNodeLoad extends Validate
{
    protected $rule = [
        "name|节点名称" => "require|unique:admin_node_load",
    ];
}