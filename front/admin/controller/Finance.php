<?php
/**
 * Created by PhpStorm.
 * User: hudy@cnfol.net
 * Date: 2018/5/28 0028
 * Time: 14:46
 */

namespace app\admin\controller;

use think\Db;


class Finance extends Admin {

	/**
	 * 财务状况
	 */
	public function index()
	{
		$blance = $this->_get_blance();
		$charge_sum = $this->_get_charge_sum();
		$spendding = $charge_sum - $blance;
		$data = ['charge_sum' => $charge_sum, 'blance' => $blance, 'spendding' => $spendding];
		$this->assign('data', $data);
		return $this->fetch();
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
		$row = Db::table('transaction_flow')->where('user_id = '. ADMIN_ID)->sum('money');
		return round($row / 100, 2);
	}

	/**
	 * 转账记录
	 */
	public function record()
	{
		$where = 1;
		if($this->request->isGet())
		{
			$param = $this->request->param();
			if(!empty($param['type']))
			{
				$where .= ' AND type = ' . $param['type'];
			}
			if(!empty($param['time']))
			{
				$time_arr = explode(' - ', $param['time']);
				$where .= ' AND operate_time between "' . $time_arr[0] . '"and "' . $time_arr[1] . '"';
			}

		}
		$where .= ' AND user_id = ' . ADMIN_ID;
		$row = Db::table('transaction_flow')->where($where)->select();
		$this->assign('selected_type', input('type'));
		$this->assign('data', $row);
		return $this->fetch();
	}
}