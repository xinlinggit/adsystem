<?php
namespace cms\common\model;

/**
 * 用户管理类
 */
class Applog extends Base
{
    protected $auto = [];
    protected $updateTime = false;
    protected $createTime = 'create_time';

    /**
     * 关联查询
     */
    public function User(){
        return $this->hasOne('User','id','userid','','INNER');
    }
}

/* End of file User.php */
/* Location: ./app_cms/common/model/User.php */