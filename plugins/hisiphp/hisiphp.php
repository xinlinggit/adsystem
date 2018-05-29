<?php
// +----------------------------------------------------------------------
// | Author: zengjie@cnfol.com
// +----------------------------------------------------------------------
namespace plugins\hisiphp;
use app\common\controller\Plugins;
defined('IN_SYSTEM') or die('Access Denied');
/**
 * 系统基础信息插件
 * @package plugins\hisiphp
 */
class hisiphp extends Plugins
{
    /**
     * @var array 插件钩子清单
     */
    public $hooks = [
        // 钩子名称 => 钩子说明【系统钩子，说明不用填写】
        'system_admin_index',
    ];

    /**
     * system_admin_tips钩子方法
     * @param $params
     */
    public function systemAdminIndex(&$params)
    {
        return $this->fetch();
    }

    /**
     * 安装前的业务处理，可在此方法实现，默认返回true
     * @return bool
     */
    public function install()
    {
        return true;
    }

    /**
     * 安装后的业务处理，可在此方法实现，默认返回true
     * @return bool
     */
    public function installAfter()
    {
        return true;
    }

    /**
     * 卸载前的业务处理，可在此方法实现，默认返回true
     * @return bool
     */
    public function uninstall()
    {
        return true;
    }

    /**
     * 卸载后的业务处理，可在此方法实现，默认返回true
     * @return bool
     */
    public function uninstallAfter()
    {
        return true;
    }

}