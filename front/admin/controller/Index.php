<?php
// +----------------------------------------------------------------------
// | Author: zengjie@cnfol.com
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\common\util\Dir;
use think\Db;
/**
 * 后台默认首页控制器
 * @package app\admin\controller
 */

class Index extends Admin
{
    /**
     * 首页
     * @author zengjie@cnfol.com
     * @return mixed
     */
    public function index()
    {
        if (cookie('hisi_iframe')) {
            $this->view->engine->layout(false);
            $authentication = $this->_check_current_authentication();
            $this->view->assign('authentication', $authentication);
	        $this->_load_echarts_data();
            return $this->fetch('iframe');
        } else {
	        $this->_load_echarts_data();
            return $this->fetch();
        }
    }

    protected function _load_echarts_data(){
	    $btime = date('Y-m-d', strtotime('-30 day'));
	    $etime = date('Y-m-d', strtotime('-1 day'));
	    $where = " time between '$btime' and '$etime'";
	    $chart_data = $this->_getChartsData($where);
	    $x_data_temp = '';
	    $y_data_temp ='';
	    foreach($chart_data as $k => $v)
	    {
		    $x_data_temp .= "," . "'" . $v['time'] . "'";
		    $y_data_temp .= "," . $v['sum'];
	    }
	    $x_data = '[' . trim($x_data_temp,',');
	    $y_data = '[' . trim($y_data_temp,',');
	    $x_data .= ']';
	    $y_data .= ']';
	    $this->assign('x_data', $x_data);
	    $this->assign('y_data', $y_data);
    }

	private function _check_current_authentication()
	{
		if(in_array(ADMIN_ROLE, [1, 2, 5]))
		{
			return true;
		} else {
			$map['uid'] = ADMIN_ID;
			$map['status'] = 1;
			return (boolean)Db::table('license_auth_current')->where($map)->find();
		}
	}

    /**
     * 欢迎首页
     * @author zengjie@cnfol.com
     * @return mixed
     */
    public function welcome()
    {
	    $this->_load_echarts_data();
        return $this->fetch();
    }

	protected function _getChartsData($where)
	{
		$where .= ' AND userid = '. ADMIN_ID .' AND count(*) > 0';
		return Db::table('record_day')
		         ->field('adsystemid,userid, advertisementid, materialid, time, sum(sum) as sum, sum(cost) as cost')
		         ->group('time')
		         ->having($where)
		         ->order('time')
		         ->select();
	}

    /**
     * 清理缓存
     * @author zengjie@cnfol.com
     * @return mixed
     */
    public function clear()
    {
        if (Dir::delDir(RUNTIME_PATH) === false) {
            return $this->error('缓存清理失败！');
        }
        return $this->success('缓存清理成功！');
    }
}
