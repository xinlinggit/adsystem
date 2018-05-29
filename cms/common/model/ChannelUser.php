<?php
namespace cms\common\model;

/**
 * 用户管理类
 */
class ChannelUser extends Base
{
    protected $auto = [];
    protected $updateTime = false;
    // 设置主表名
    protected $table = 'channel_user';
    // 定义关联模型列表
    protected $relationModel = [
        // 给关联模型设置数据表
        'AccessControl'   =>  'access_control',
    ];
    // 定义关联外键
    protected $fk = 'id';
    protected $mapFields = [
        // 为混淆字段定义映射
        'id'      =>  'ChannelUser.id',
        'info_id' =>  'AccessControl.id',
    ];
    protected $hidden = ['password','wrong_times','create_time','last_login_time','info_id','loginname'];
}

/* End of file User.php */
/* Location: ./app_cms/common/model/User.php */