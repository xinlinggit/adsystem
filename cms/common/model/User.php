<?php
namespace cms\common\model;

/**
 * 用户管理类
 */
class User extends Base
{
    protected $auto = [];
    protected $updateTime = false;
    // 设置主表名
    protected $table = 'user';
    // 定义关联模型列表
    public function Userinfo(){
        return $this->hasOne('Userinfo','id','id','','LEFT');
    }
    public function setTelAttr($tel)
    {
        return empty($tel)?$tel:mobileEncode($tel);
    }
    public function getTelAttr($value)
    {
        return empty($value)?$value:mobileDecode($value);
    }
}

/* End of file User.php */
/* Location: ./app_cms/common/model/User.php */