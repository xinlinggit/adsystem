<?php
/**
 * Created by PhpStorm.
 * User: hudy@cnfol.net
 * Date: 2018/4/8 0008
 * Time: 11:07
 */

namespace cms\adagent\controller;
use cnfol\Tools;
use cnfol\unit\ArrayUnit;
use think\Db;
use think\Exception;
use think\Loader;
use think\Config;
use cms\common\model;


class Adagent extends Base
{
	public function fetch_list(){
		/*设置数组*/
		$set = [];
		$set['order'] = 'id';
		$set['thead'] = ['id','create_time'];
		return $this->fetch_base($set);
	}

	/**
	 * 页面渲染基础方法
	 *
	 * @param array $set 配置数组
	 *
	 * @return mixed
	 */
	protected function fetch_base($set = [])
	{
		/*获取排序参数，默认按ID倒序*/
		$order = $this->request->param('order', isset($set['order'])?$set['order']:'id');
		$by = $this->request->param('by', isset($set['by'])?$set['by']:'desc');
		$this->get_thead(isset($set['thead'])?$set['thead']:[$order], $order, $by);

		/*分页设置，默认20，小于100*/
		$per_page = $this->request->param('num', 20);
		$per_page = min(100, $per_page);

		/*全局筛选条件*/
		$map = $this->get_map();

		unset($map['id']);

		$this->get_map_like($map, 'username');
		/*默认值回调处理*/
		if (isset($set['map']) && is_callable($set['map'])) {
			$set['map']($map);
		};

		/*分页查询*/
		$page = Db::table('ad_admin_user_agent')
		  ->alias('a')
		  ->field('a.*, round(ui.account / 100, 2) as account')
		  ->where('a.status != -1 and a.id > 1')
		  ->where($map)
    	  ->join('adserver.userinfo_agent ui', 'a.id = ui.uid', 'left')
			->order($order . ' ' . $by)
		  ->paginate($per_page);

		$ids = [];
		foreach($page->items() as $v)
		{
			$ids[] = $v['id'];
		}
		/*$license_auth = $this->_get_current_license_auth($ids);
		$this->view->assign('license_auth', $license_auth);*/
		$this->view->assign('page', $page);
		$this->view->assign('list', $page->items());
		return $this->fetch();
	}

	public function _get_current_license_auth($ids)
	{
		$map['uid'] = ['in', $ids];
		$row = Db::table('license_auth_current')->field('uid, status')->where($map)->select();
		$license_auth = [];
		$license_data = [];
		foreach($row as $k => $v)
		{
			$license_auth[$v['uid']] = $v;
		}
		foreach($ids as $kk => $vv)
		{
			$license_data[$vv] = isset($license_auth[$vv]) ? $license_auth[$vv] : ['uid' => '', 'status' => ''];
		}

		unset($row);
		return $license_data;
	}

	// 启用/停用
	public function change_status()
	{
		// TODO: 参数值域安全检查
		$param = $this->request->param();
		if(!$param['id']){
			return $this->api_error('请选择数据');
		}
		$data = [
			'status' => $param['status'],
		];
		$result = Db::table('ad_admin_user_agent')->where(['id' => ['in',$param['id']]])->update($data);
		if(false === $result){
			// 操作失败 输出错误信息
			return $this->api_error();
		}
		return $this->api_success();
	}

	/**
	 * 充值
	 */
	public function charge($id = 0)
	{
		$this->view->assign('action',url('charge') . '?id=' . $id);
		if($this->request->isPost())
		{
			$account = abs(input('account'));
			$account = yuan2fen($account);
			$uid = input('id');
			if( ! $uid)
			{
				@ads_log('充值用户的id不能为空', 2);
				return $this->api_error('参数错误');
			}
			if( ! $account)
			{
				return $this->api_error('请填写充值金额');
			}

			$res = Db::table('userinfo_agent')->where(array('uid'=>$uid))->setInc('account',$account);
			if($res == 1)
			{
				$this->_record_charge(['money' => $account, 'user_id' => $uid]);
				return $this->api_success('充值成功');
			} else {
				return $this->api_error('充值失败');
			}
		}
		return $this->get_charge_model();
	}

	/**
	 * 退款
	 */
	public function refund($id = 0)
	{
		$this->view->assign('action',url('refund') . '?id=' . $id);
		if($this->request->isPost())
		{
			$account = abs(input('account'));
			$account = yuan2fen($account);
			$uid = input('id');
			if( ! $uid)
			{
				@ads_log('退款用户的id不能为空', 2);
				return $this->api_error('参数错误');
			}
			if( ! $account)
			{
				return $this->api_error('请填写退款金额');
			}

			$res = Db::table('userinfo_agent')->where(array('uid'=>$uid))->setDec('account',$account);
			if($res == 1)
			{
				$this->_refund_charge(['money' => $account, 'user_id' => $uid]);
				return $this->api_success('退款成功');
			} else {
				return $this->api_error('退款失败');
			}
		}
		return $this->get_refund_model();
	}

	protected function _record_charge($param)
	{
		$error_no = 0;
		do {
			$data['money'] = $param['money'];
			$data['user_id'] = $param['user_id'];
			$data['type'] = 1; // 充值
			$data['no'] = get_transaction_no();
			$data['backend_user_id'] = $this->backend['id'];
			$datetime = date('Y-m-d H:i:s', time());
			$data['operate_time'] = $datetime;
			$data['update_time'] = $datetime;
			$data['create_time'] = $datetime;
			try {
				$res = Db::table( 'transaction_flow_agent' )->insert( $data );
			}catch (Exception $e)
			{
				// TODO: 实际这里可以根据不同的 errno 记录日志以供分析参考 ( 考虑实现自己的构造器返回 errno )
				$err_msg = $e->getMessage();
				if(strpos($err_msg, '1062') !== false)
				{
					$error_no = 1062;
				}
			}
		} while ( $error_no == 1062 );
	}

	protected function _refund_charge($param)
	{
		$error_no = 0;
		do {
			$data['money'] = $param['money'];
			$data['user_id'] = $param['user_id'];
			$data['type'] = 2; // 退款
			$data['no'] = get_transaction_no();
			$data['backend_user_id'] = $this->backend['id'];
			$datetime = date('Y-m-d H:i:s', time());
			$data['operate_time'] = $datetime;
			$data['update_time'] = $datetime;
			$data['create_time'] = $datetime;
			try {
				$res = Db::table( 'transaction_flow_agent' )->insert( $data );
			}catch (Exception $e)
			{
				// TODO: 实际这里可以根据不同的 errno 记录日志以供分析参考 ( 考虑实现自己的构造器返回 errno )
				$err_msg = $e->getMessage();
				if(strpos($err_msg, '1062') !== false)
				{
					$error_no = 1062;
				}
			}
		} while ( $error_no == 1062 );
	}



	protected function get_charge_model()
	{
		$id = $this->request->get('id');
		$info = Db::table('ad_admin_user_agent')
			->field('a.*,ui.uid, round(ui.account/100, 2) account')
		          ->alias('a')
			->join('adserver.userinfo_agent ui', 'a.id = ui.uid', 'LEFT')
			->where('a.id=' . $id)
		          ->find();
		$this->view->assign('info',$info);
		$html = $this->fetch('model_charge_operate');
		return $this->api_success($html);
	}

	protected function get_refund_model()
	{
		$id = $this->request->get('id');
		$info = Db::table('ad_admin_user_agent')
			->field('a.*,ui.uid, round(ui.account/100, 2) account')
		          ->alias('a')
			->join('adserver.userinfo_agent ui', 'a.id = ui.uid', 'LEFT')
			->where('a.id=' . $id)
		          ->find();
		$this->view->assign('info',$info);
		$html = $this->fetch('model_refund_operate');
		return $this->api_success($html);
	}

	/**
	 * 添加框
	 */
	public function model_add(){
		$this->view->assign('action',url('operate_add'));
		$this->view->assign('add', 1);
		return $this->get_model();
	}

	/**
	 * 添加操作
	 */
	public function operate_add(){
		$param = $this->request->post();
		// 验证
		$result = $this->validate($param, 'Adagent');
		if($result !== true) {
			return $this->api_error($result);
		}
		$id = input('id');
		if(empty($id)){
			if ( Db::table( 'ad_admin_user_agent' )->where( [ 'username' => $param['username'] ] )->find() ) {
				return $this->api_error( '用户名已经存在' );
			}
		}
		$param['auth'] = '';
		$param['last_login_ip'] = $this->request->ip();
		if(trim($param['password']) == '')
		{
			return $this->api_error('请填写密码');
		}
		$row_auth = $this->_get_auth($param['role_id']);
		$param['auth'] = $row_auth['auth'];
		
		$param['current_price'] = Db::table('ad_admin_config')->where(['id'=>53])->value('value');
		$param['current_cpc'] = Db::table('ad_admin_config')->where(['id'=>57])->value('value');
		$param['iframe'] = 1;
		$param['ctime'] = time();
		$param['password'] = password_hash($param['password'], PASSWORD_DEFAULT);
		$result = Db::table('ad_admin_user_agent')->insertGetId($param);

		if(false === $result) {
			// 验证失败 输出错误信息
			return $this->api_error();
		}
		Db::startTrans();
		$res_account = $this->_add_account($result);
		if($result && $res_account)
		{
			Db::commit();
		} else {
			Db::rollback();
		}
		return $this->api_success();
	}

	/**
	 * 根据用户角色获取对应的权限
	 *
	 * @param $role_id
	 *
	 * @return array|false|\PDOStatement|string|\think\Model
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 */
	protected function _get_auth($role_id)
	{
		return Db::table('ad_admin_role_agent')->field('auth')->where(['id' => $role_id])->find();
	}

	/**
	 * 创建用户的账户
	 */
	protected function _add_account($uid)
	{
		if($uid)
		{
			$param = ['uid' => $uid, 'account' => 0];
			return Db::table('userinfo_agent')->insert($param);
		}
	}

	/**
	 * 修改框
	 */
	public function model_edit(){
		$this->view->assign('action',url('operate_edit'));
		return $this->get_model();
	}

	public function auth_view()
	{
		$this->view->assign('action', url('change_auth_status'));
		return $this->get_auth_model();
	}

	/**
	 * 显示认证视图
	 * @param int $id 数据ID
	 */
	public function get_auth_model(){
		$id = $this->request->get('id');
		if(empty($id))
		{
			return $this->api_error('请检查参数');
		}
		$info = Db::table('ad_admin_user_agent')
		          ->alias('a')
			->join('adserver.license_auth l', 'l.uid = a.id', 'left')
		    ->where('a.id = ' . $id)->find();
		if(! $info)
		{
//			return $this->api_error('没有找到');
		}
		$this->view->assign('info',$info);
		$html = $this->fetch('model_auth_operate');
		return $this->api_success($html);
	}


	/**
	 * 修改操作
	 */
	public function operate_edit(){
		$param = $this->request->post();
		// 验证
		$result = $this->validate($param, 'Adagent');
		if($result !== true) {
			return $this->api_error($result);
		}
		/*$row_auth = $this->_get_auth($param['role_id']);
		$param['auth'] = $row_auth['auth'];*/
		if ($param['password'] == '') {
			unset($param['password']);
		} else {
			$param['password'] = password_hash($param['password'], PASSWORD_DEFAULT);
		}
		$result = Db::table('ad_admin_user_agent')->update($param);
		if(false === $result){
			// 验证失败 输出错误信息
			return $this->api_error($this->model->getError());
		}
		return $this->api_success();
	}

	/**
	 * 显示
	 * @param int $id 数据ID
	 */
	public function get_model(){
		$id = $this->request->get('id');
		$info = Db::table('ad_admin_user_agent')->find($id);
		$this->view->assign('info',$info);
		$html = $this->fetch('model_operate');
		return $this->api_success($html);
	}

	/**
	 * 删除（回收站）'-1'
	 */
	public function operate_status_11(){
		$param = $this->request->param();
		if(!$param['id']){
			return $this->api_error('请选择数据');
		}
		$data['status'] = -1;
		$result = Db::table('ad_admin_user_agent')->where(['id' => ['in',$param['id']]])->update($data);
		if(false === $result){
			// 操作失败 输出错误信息
			return $this->api_error($this->model->getError());
		}
		return $this->api_success();
	}
}