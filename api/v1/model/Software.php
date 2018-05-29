<?php
namespace api\v1\model;

use \think\Model;
use \think\Db;
class Software extends Model
{
    public function getPackageUrlAttr($value)
    {
        //return str_replace('taskbao.cms.cnfol.com','taskbao.dev.cnfol.wh',$value);
        if (2 == $this->getData('platform'))
            return $value;
        return config('app_domain') . $value;
    }
}