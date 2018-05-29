<?php
//------------------------
// 用户模型
//-------------------------

namespace api\common\model;

use think\Model;

class AdminUser extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    //自动完成
    protected $auto = ['password'];

    protected function setPasswordAttr($value)
    {
        return password_hash_tp($value);
    }

    /**
     * 修改密码
     */
    public function updatePassword($uid, $password)
    {
        return $this->where("id", $uid)->update(['password' => password_hash_tp($password)]);
    }
}