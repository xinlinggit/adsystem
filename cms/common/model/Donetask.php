<?php
namespace cms\common\model;

/**
 * 用户管理类
 */
class Donetask extends Base
{
    protected $auto = [];
    protected $updateTime = 'check_time';

    public function getResultAttr($value)
    {
        //return str_replace('taskbao.cms.cnfol.com','taskbao.dev.cnfol.wh',$value);
        return config('app_domain') . $value;
    }

    /**
     * 关联查询
     */
    public function Task(){
        return $this->hasOne('Task','id','taskid','','INNER');
    }

    /**
     * 关联查询
     */
    public function User(){
        return $this->hasOne('User','id','userid','','INNER');
    }

    /**
     * 关联查询
     */
    public function Userinfo(){
        return $this->hasOne('Userinfo','id','userid','','INNER');
    }

    /**
     * 关联查询
     */
    public function Checkuser(){
        return $this->hasOne('ChannelUser','id','check_userid','','INNER');
    }
}

/* End of file User.php */
/* Location: ./app_cms/common/model/User.php */