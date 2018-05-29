<?php
namespace cms\common\model;

/**
 * 用户管理类
 */
class Order extends Base
{
    protected $auto = [];
    protected $updateTime = false;

    public function getRemarksAttr($value)
    {
        return $value === null ? '' : $value;
    }
    public function getResultImageAttr($value)
    {
        //return str_replace('taskbao.cms.cnfol.com','taskbao.dev.cnfol.wh',$value);
        return empty($value) ? null : config('app_domain') . $value;
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
    public function DealUser(){
        return $this->hasOne('ChannelUser','id','deal_user','','INNER');
    }

    /**
     * 关联查询
     */
    public function ResultUser(){
        return $this->hasOne('ChannelUser','id','result_user','','INNER');
    }
}

/* End of file User.php */
/* Location: ./app_cms/common/model/User.php */