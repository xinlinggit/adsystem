<?php
// +----------------------------------------------------------------------
// | HisiPHP框架[基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 http://www.HisiPHP.com
// +----------------------------------------------------------------------
// | Author: zengjie@cnfol.com
// +----------------------------------------------------------------------
namespace app\common\controller;

use think\View;
use think\Controller;

/**
 * 项目公共控制器
 * @package app\common\controller
 */
class Common extends Controller
{
    protected function _initialize()
    {
    }

    /**
     * 渲染后台模板
     * 模块区分前后台时需用此方法
     * @param string $template 模板路径
     * @author zengjie@cnfol.com
     * @return string
     */
    protected function afetch($template = '') {
        if ($template) {
            return $this->fetch($template);
        }
        $dispatch = request()->dispatch();
        if (!$dispatch['module'][2]) {
            $dispatch['module'][2] = 'index';
        }
        return $this->fetch($dispatch['module'][1].DS.$dispatch['module'][2]);
    }
    
    /**
     * 渲染插件模板
     * @param string $template 模板名称
     * @author zengjie@cnfol.com
     * @return mixed
     */
    final protected function pfetch($template = '', $vars = [], $replace = [], $config = [])
    {
        $plugin = $_GET['_p'];
        $controller = $_GET['_c'];
        $action = $_GET['_a'];
        $template = $template ? $template : $controller.'/'.$action;
        if(defined('ENTRANCE') && ENTRANCE == 'admin') {
            $template = 'admin/'.$template;
        }
        $template_path = strtolower("plugins/{$plugin}/view/{$template}.".config('template.view_suffix'));
        return parent::fetch($template_path, $vars, $replace, $config);
    }
}