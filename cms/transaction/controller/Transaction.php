<?php
/**
 * Created by PhpStorm.
 * User: hudy@cnfol.net
 * Date: 2018/4/28 0028
 * Time: 16:39
 */
namespace cms\transaction\controller;

use cms\adcustomer\controller\Base;
use think\Db;

class Transaction extends Base {

	/**
	 * 流水记录列表
	 */
	public function my_list(){
		/*设置数组*/
		$set = [];
		$set['order'] = 'id';
		$set['thead'] = ['id','create_time'];
		return $this->_my_base($set);
	}

	protected function _my_base($set = []){

		/*获取排序参数，默认按ID倒序*/
		$order = $this->request->param('order', isset($set['order'])?$set['order']:'id');
		$by = $this->request->param('by', isset($set['by'])?$set['by']:'desc');
		$this->get_thead(isset($set['thead'])?$set['thead']:[$order], $order, $by);

		/*分页设置，默认20，小于100*/
		$per_page = $this->request->param('num', 20);
		$per_page = min(100, $per_page);

		$value = trim($this->request->param('username'));
		$likeusername = "%".$value."%";
		if($value){
			$map = array('user.username'=>array('like',$likeusername));
		}else{
			$map = '';
		}
		
		/*分页查询*/
		$page = Db::table('transaction_flow')
				  ->alias('t')
		          ->field('t.id, user.username, t.no, t.type, t.user_id, t.backend_user_id, round(t.money / 100, 2) as money , t.create_time,  t.pid, b_user.real_name, user_agent.username as username_agent')
			      ->join('adserver.backend_user b_user', 't.backend_user_id = b_user.id', 'left')
				  ->join('adserver.ad_admin_user user', 't.user_id = user.id', 'left')
				  ->join('adserver.ad_admin_user_agent user_agent', 't.pid = user_agent.id', 'left')
		          ->where($map)
		          ->order($order . ' ' . $by)
		          ->paginate($per_page);

		$ids = [];
		foreach($page->items() as $v)
		{
			$ids[] = $v['id'];
		}
		$this->view->assign('page', $page);
		$this->view->assign('list', $page->items());
		return $this->fetch('list');
	}
}