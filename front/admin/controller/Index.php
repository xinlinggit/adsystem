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
            $authentication = $this->_check_authentication();
            $data = $this->_load_finance_index_data();
	        $this->view->assign('data', $data);
	        $this->view->assign('authentication', $authentication);
            return $this->fetch('iframe');
        } else {
	        $data = $this->_load_finance_index_data();
	        $this->view->assign('data', $data);
            return $this->fetch();
        }
    }

	/**
	 * 获取余额
	 * @return mixed
	 */
	protected function _get_blance()
	{
		$row = Db::table('userinfo')
		         ->field('round(account/100, 2) account')
		         ->where('uid = ' . ADMIN_ID)
		         ->find();
		return $row['account'];
	}

	/**
	 * 获取历史充值/退款金额之和
	 */
	protected function _get_charge_sum(){
		$charge_sum = Db::table('transaction_flow')->where(['user_id' => ADMIN_ID, 'type' => 1])->sum('money');
		$withdraw_sum = Db::table('transaction_flow')->where(['user_id' => ADMIN_ID, 'type' => 2])->sum('money');
		return round(($charge_sum - $withdraw_sum) / 100, 2);
	}

    protected function _load_finance_index_data(){
	    $blance = $this->_get_blance();
	    $charge_sum = $this->_get_charge_sum();
	    if($charge_sum > 0)
	    {
		    $spendding = $charge_sum - $blance;
	    } elseif($charge_sum == 0) {
		    $spendding = 0;
	    }
	    $data = ['charge_sum' => $charge_sum, 'blance' => $blance, 'spendding' => round($spendding, 2)];
	    return $data;
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

	private function _check_authentication()
	{
		$res = in_array(ADMIN_ROLE, [1, 2, 5]);
		$res2 = Db::table('license_auth')->where(['uid' => ADMIN_ID, 'status' => 1])->find();
		if($res || $res2)
		{
			return true;
		} else {
			return false;
		}
	}

    /**
     * 欢迎首页
     * @author zengjie@cnfol.com
     * @return mixed
     */
    public function welcome()
    {
	    $data = $this->_load_finance_index_data();
	    $this->view->assign('data', $data);
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
    	$res1 = (Dir::delDir(RUNTIME_PATH . DS . 'cache') === false);
    	$res2 = (Dir::delDir(RUNTIME_PATH . DS . 'temp') === false);
        if ($res1 && $res2) {
            return $this->error('缓存清理失败！');
        }
        return $this->success('缓存清理成功！');
    }
}
