<?php
namespace cms\common\model;

/**
 * 用户管理类
 */
class Software extends Base
{
    protected $auto = [];
    protected $updateTime = false;
    protected $createTime = 'update_time';

    public function getPackageUrlAttr($value)
    {
        //return str_replace('taskbao.cms.cnfol.com','taskbao.dev.cnfol.wh',$value);
        if (1 == $this->getData('platform')){
            return config('app_domain') . $value;
        } else {
            return $value;
        }

    }
}

/* End of file User.php */
/* Location: ./app_cms/common/model/User.php */