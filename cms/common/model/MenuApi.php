<?php
namespace cms\common\model;

/**
 * menu_api表模型
 * @package app\common\model
 */
class MenuApi extends Common
{

    /**
     * 关联查询父级title
     * @return $this
     */
    public function menuApi(){

        return $this->hasOne('MenuApi','id','pid')->bind(['pid_title'=>'title']);
    }
}
