<?php
// +----------------------------------------------------------------------
// | Author: zengjie@cnfol.com
// +----------------------------------------------------------------------
namespace app\admin\controller;

/**
 * 后台开发工具控制器
 * 仅供开发人员使用
 * @package app\admin\controller
 */
class Develop extends Admin
{
    public $tab_data = [];
    /**
     * 初始化方法
     */
    protected function _initialize()
    {
        parent::_initialize();

        $tab_data['menu'] = [
            [
                'title' => '模板预览'
            ],
            [
                'title' => '查看代码'
            ],
        ];

        $this->tab_data = $tab_data;
    }

    /**
     * 列表演示
     * @author zengjie@cnfol.com
     * @return mixed
     */
    public function lists()
    {
        $this->assign('tab_data', $this->tab_data);
        $this->assign('tab_type', 2);
        return $this->fetch();
    }

    /**
     * 编辑演示
     * @author zengjie@cnfol.com
     * @return mixed
     */
    public function edit()
    {
        $this->assign('tab_data', $this->tab_data);
        $this->assign('tab_type', 2);
        return $this->fetch();
    }
}
