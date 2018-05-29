<?php
//------------------------
// 角色模型
//-------------------------

namespace api\common\model;

use think\Model;

class AdminRole extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
}