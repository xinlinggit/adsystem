<?php
namespace cms\common\model;

/**
 * 用户管理类
 */
class Task extends Base
{
    protected $auto = [];
    protected $updateTime = false;

    public function getImageAttr($value)
    {
        //return str_replace('taskbao.cms.cnfol.com','taskbao.dev.cnfol.wh',$value);
        return config('app_domain') . $value;
    }

    /**
     * 关联查询
     */
    public function Channel(){
        return $this->hasOne('Channel','id','publish_channel_id');
    }

    /**
     * 关联查询
     */
    public function User(){
        return $this->hasOne('ChannelUser','id','publish_userid');
    }
}

/* End of file User.php */
/* Location: ./app_cms/common/model/User.php */