<?php

//------------------------
// 分组模型
//-------------------------

namespace cms\common\model;

use think\Model;

class AdminGroup extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    /**
     * 列表
     */
    public function getList($field = 'id,name', $where = 'isdelete=0 AND status=1')
    {
        return $this->field($field)->where($where)->select();
    }
}