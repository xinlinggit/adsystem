<?php
// +----------------------------------------------------------------------
// | Author: zengjie@cnfol.com
// +----------------------------------------------------------------------
/**
 * 系统默认控制器
// +----------------------------------------------------------------------
// | 警告：请勿在index模块下开发任何东西，系统升级会自动覆盖此模块
// +----------------------------------------------------------------------
 * @package app\index\controller
 */
namespace app\index\controller;
use app\common\controller\Common;
class Home extends Common
{
    /**
     * 初始化方法
     */
    protected function _initialize()
    {
        parent::_initialize();
    }
    
    /**
     * 将返回结果以json格式输出
     * @author zengjie@cnfol.com
     */
    public function apiReturn($msg = '', $code = 0, $data = [])
    {
        $arr = [];
        $arr['code'] = $code;
        $arr['msg'] = $msg;
        $arr['data'] = $data;
        if ($code == 1) {
            $status_code = 200;
        } else {
            $status_code = 202;
        }
        return json($arr, $status_code);
        exit;
    }
}
