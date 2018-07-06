<?php
/**
 * Created by PhpStorm.
 * User: hudy@cnfol.net
 * Date: 2018/4/8 0008
 * Time: 11:07
 */

namespace cms\ads\controller;
use cnfol\unit\ArrayUnit;
use think\Db;
use think\Loader;
use think\Config;
use cms\common\model;


class Ads extends Base
{
	public function fetch_list(){
		/*设置数组*/
		$set = [];
		$set['order'] = 'id';
		$set['thead'] = ['id','create_time'];
		$sites = $this->_getSites();
		$sites_arr = [];
		foreach($sites as $k => $v)
		{
			$sites_arr[$v['id']] = $v['sitename'];
		}
		$this->view->assign('sites',ArrayUnit::array_to_options($sites_arr, $this->request->param('adsiteid')));
		$this->view->assign('statuses', ArrayUnit::array_to_options(['1' => '暂停', '2' => '即将投放', '3' => '投放中', '4' => '已投完'], $this->request->param('status')));
		$this->view->assign('preview', Config::get('review'));
		$this->view->assign('senses',ArrayUnit::array_to_options([],$this->request->param('adsenseid')));
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
		$this->get_map_like($map, 'username');
		/*默认值回调处理*/
		if (isset($set['map']) && is_callable($set['map'])) {
			$set['map']($map);
		};

		unset($map['id']);
		unset($map['.id']);
		$id = trim($this->request->param('id'));
		if( is_numeric($id))
		{
			$map['a.id'] = $id;
		}

		$adsiteid = trim($this->request->param('adsiteid'));
		if( is_numeric($adsiteid))
		{
			$map['a.adsiteid'] = $adsiteid;
		}
		$adsenseid = trim($this->request->param('adsenseid'));
		if( is_numeric($adsenseid))
		{
			$map['a.adsenseid'] = $adsenseid;
		}

		unset($map['status']);
		$status = trim($this->request->param('status'));
		if( is_numeric($status))
		{
			$map['a.status'] = $status;
		}


		/*分页查询*/
		$page = Db::table('adserver.advertisement')
		               ->field('a.*,site.sitename,sense.sensename,sense.width,sense.height,sense.sensetype,u.username')
		               ->alias('a')
		               ->join("adserver.adsite site", "a.adsiteid = site.id", 'LEFT')
		               ->join("adserver.adsense sense", "a.adsenseid = sense.id", 'LEFT')
			->join('adserver.ad_admin_user u', 'a.userid = u.id', 'LEFT')
						->where('a.status <> -1')
						->where('a.userid > 0')
		               ->where($map)
			->order($order . ' ' . $by)
		               ->paginate($per_page, false, ['query' => $this->request->param()]);

		$this->view->assign('selected_adsenseid', input('adsenseid'));
		$this->view->assign('page', $page);
		$this->view->assign('list', $page->items());

		return $this->fetch();
	}

	/**
	 * 根据条件选择素材
	 * @param int $material_id
	 *
	 * @return mixed
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 */
	public function getMaterialList($material_id = 0)
	{
		$material_id_choosed = input('materialid', 0);
		$set = [];
		$set['order'] = 'id';
		$set['thead'] = ['id','create_time'];
		/*获取排序参数，默认按ID倒序*/
		$order = $this->request->param('order', isset($set['order'])?$set['order']:'id');
		$by = $this->request->param('by', isset($set['by'])?$set['by']:'desc');
		$this->get_thead(isset($set['thead'])?$set['thead']:[$order], $order, $by);

		/*分页设置，默认20，小于100*/
		$per_page = $this->request->param('num', 20);
		$per_page = min(100, $per_page);

		/*全局筛选条件*/
		$map = $this->get_map();
		$this->get_map_like($map, 'username');
		/*默认值回调处理*/
		if (isset($set['map']) && is_callable($set['map'])) {
			$set['map']($map);
		};

		unset($map['id']);
		unset($map['.id']);
		$id = trim($this->request->param('id'));
		if( is_numeric($id))
		{
			$map['a.id'] = $id;
		}

		$adsiteid = trim($this->request->param('adsiteid'));
		if( is_numeric($adsiteid))
		{
			$map['a.adsiteid'] = $adsiteid;
		}

		unset($map['status']);
		$status = trim($this->request->param('status'));
		if( is_numeric($status))
		{
			$map['a.status'] = $status;
		}

		$material_id_choosed = input('material_id', 0);
		$adsensetype = input('sensetype');

		// 批量广告位的素材筛选条件 1: 尺寸  2： adsense.sensetype
		switch ($adsensetype)
		{   // 1: 文字表 2: 图片表 3: flash 4:couplet
			case 1:
				$where = ['material_type' => ['in', '1,2,3']];
				break;
			case 2:
				$where = ['material_type' => ['in', '2,3']];
				break;
			case 3:
				$where = ['material_type' => ['in', '2,3']];
				break;
			case 4:
//				$where = ['material_type' => ['in', '4']];
				break;
			case 5:
				$where = ['material_type' => ['in', '5']];
				break;
			case 6:
				$where = ['material_type' => ['in', '2']];
				break;
			case 7:
				$where = ['material_type' => ['in', '3']];
			default:
				break;
		}
		$where['width'] = input('width');
		$where['height'] = input('height');
		$where['status'] = 3; // 审核已经通过的
		$pages = Db::table('material_main')->where($where)->paginate(15, false, ['query' =>
			                                                                                 $this->request->param()]);
		// 分页
		$this->assign('extra_param', http_build_query($this->request->param()));
		$this->assign('material_id_choosed', $material_id_choosed);
		$this->assign('page', $pages);
		$this->assign('list', $pages->items());
		return  $this->fetch('materiallists');
	}

	public function getposition()
	{
		// 根据站点，获取对应的广告位
		$adsite = input('adsite');
		$where['adsite'] = $adsite;
		$row = Db::table('adsense')
			->field('id, sensename')
			->where($where)
			->group('sensename')
			->select();
		return json_encode($row);
	}

	// 启用/停用
	public function change_status()
	{
		// TODO: 启停状态值域的安全检查
		$param = $this->request->param();
		if(!$param['id']){
			return $this->api_error('请选择数据');
		}
		$data = [
			'running_status' => intval($param['running_status']),
		];
		$result = Db::table('advertisement')->where(['id' => ['in',$param['id']]])->update($data);
		if(false === $result){
			// 操作失败 输出错误信息
			return $this->api_error();
		}
		return $this->api_success();
	}

	/**
	 * 添加框
	 */
	public function model_add(){
		$this->getSites();
		$this->view->assign('action',url('getMaterialList') . '?material_id=' . input('material_id') . '&sensetype=' . input('sensetype') . '&width=' . input('width') . '&height=' . input('height'));
		$this->view->assign('add', 1);
		return $this->get_add_model();
	}

	/**
	 * 添加操作
	 */
	public function operate_add(){
		$param = $this->request->post();
		$id = input('id');
		if(empty($id)) {
			if ( Db::table( 'ad_admin_user' )->where( [ 'username' => $param['username'] ] )->find() ) {
				return $this->api_error( '用户名已经存在' );
			}
		}
		$param['auth'] = '';
		$param['last_login_ip'] = $this->request->ip();
		if(trim($param['password']) == '')
		{
			return $this->api_error('请填写密码');
		}
		$param['password'] = password_hash($param['password'], PASSWORD_DEFAULT);
		$result = Db::table('ad_admin_user')->insert($param);
		if(false === $result) {
			// 验证失败 输出错误信息
			return $this->api_error();
		}
		return $this->api_success();
	}

	/**
	 * 修改框
	 */
	public function model_edit(){
		$adsiteid = input('adsiteid');
		$this->getSites();
		$this->view->assign('action',url('getMaterialList') . '?material_id=' . input('material_id') . '&sensetype=' . input('sensetype') . '&width=' . input('width') . '&height=' . input('height'));
		return $this->get_model($adsiteid);
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
		$info = Db::table('ad_admin_user')
		          ->alias('a')
			->join('adserver.license_auth l', 'l.uid = a.id', 'left')
			->where('l.status = 2')
		          ->where('a.id = ' . $id)->find();
		$this->view->assign('info',$info);
		$html = $this->fetch('model_auth_operate');
		return $this->api_success($html);
	}


	/**
	 * 修改操作
	 */
	public function operate_edit(){
		$param = $this->request->post();
		$result = Db::table('ad_admin_user')->update($param);
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
	public function get_model($adsiteid = 0){
		$id = $this->request->get('id');
		$page = Db::table('adserver.advertisement')
		          ->field('a.*,site.sitename,sense.sensename,sense.width,sense.height,sense.sensetype,u.username,u.email,u.username,u.mobile')
		          ->alias('a')
		          ->join("adserver.adsite site", "a.adsiteid = site.id", 'LEFT')
		          ->join("adserver.adsense sense", "a.adsenseid = sense.id", 'LEFT')
		          ->join('adserver.ad_admin_user u', 'a.userid = u.id', 'LEFT')
		          ->where('a.id = ' + $id)
		          ->find();
		if($adsiteid)
		{
			$page['adPositions'] = $this->_getAdPosition($adsiteid);
		}
		$time_arr = explode(',', $page['time']);
		$page['price'] = fen2yuan($page['price']);
		$page['pricelimit'] = fen2yuan($page['pricelimit']);
		$page['btime'] = $time_arr[0];
		$page['etime'] = end($time_arr);
		$this->view->assign('info', $page);
		$html = $this->fetch('model_operate');
		return $this->api_success($html);
	}

	public function get_add_model(){
		$id = $this->request->get('id');
		$info = Db::table('ad_admin_user')->find($id);
		$this->view->assign('info',$info);
		$html = $this->fetch('model_operate');
		return $this->api_success($html);
	}

	/**
	 * 获取站点对应的广告位
	 * @return string
	 * @throws \think\exception\DbException
	 * @throws db\exception\DataNotFoundException
	 * @throws db\exception\ModelNotFoundException
	 */
	public function getAdPosition()
	{
		// 根据站点，获取对应的广告位
		$adsiteid = input('adsiteid');
		$adsense = $this->_getAdPosition($adsiteid);
		return json_encode($adsense);
	}

	protected function _getAdPosition($adsiteid = 0)
	{
		$where['status'] = 1;
		if($adsiteid)
		{
			$where['adsite'] = $adsiteid;
		}
		return $adsense = Db::table('adsense')->field('id, adsite, sensename, sensetype')->where($where)->select();
	}

	// 获取所有广告站点
	protected function getSites()
	{
		$adsite = $this->_getSites();
		$this->assign('adsite', $adsite);
	}

	protected function _getSites()
	{
		return Db::table('adsite')->field('id,sitename')->where(['status' => 1])->select();
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
		$result = Db::table('advertisement')->where(['id' => ['in',$param['id']]])->update($data);
		if(false === $result){
			// 操作失败 输出错误信息
			return $this->api_error($this->model->getError());
		}
		return $this->api_success();
	}
}