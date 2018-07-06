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
	 * 依赖: 退款总和 << 充值总和
	 */
	public function index()
	{
		$blance = $this->_get_blance();
		$charge_sum = $this->_load_finance_index_data();
		if($charge_sum > 0)
		{
			$spendding = $charge_sum - $blance;
		} elseif($charge_sum == 0) {
			$spendding = 0;
		}
		$data = ['charge_sum' => $charge_sum, 'blance' => $blance, 'spendding' => round($spendding, 2)];
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
	protected function _load_finance_index_data(){
		$charge_sum = Db::table('transaction_flow')->where(['user_id' => ADMIN_ID, 'type' => 1])->sum('money');
		$withdraw_sum = Db::table('transaction_flow')->where(['user_id' => ADMIN_ID, 'type' => 2])->sum('money');
		$merge_money = $charge_sum - $withdraw_sum;
		return round(($merge_money) / 100, 2);
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
		$row = Db::table('transaction_flow')->where($where)->order('id desc')->select();
		$this->assign('selected_type', input('type'));
		$this->assign('data', $row);
		return $this->fetch();
	}
}