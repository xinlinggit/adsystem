<?php
/**
 * Created by PhpStorm.
 * User: hudy@cnfol.net
 * Date: 2018/3/14 0014
 * Time: 15:51
 */

namespace app\admin\controller;
use think\db;
/**
 * 广告投放
 * @package app\admin\controller
 */

class Adserving extends Admin
{
	public function index()
	{
		if (cookie('hisi_iframe')) {
			$this->view->engine->layout(false);
			return $this->fetch('iframe');
		} else {
			return $this->fetch();
		}
	}

	/**
	 * 广告列表
	 */
	public function lists()
	{
		if($this->request->isGet()) {
			$data           = input();
			$status = isset( $data['status'] ) ? intval($data['status']) : "";
			$adposition = isset($data['adposition']) ? $data['adposition'] : '';
			$materialid = isset( $data['materialid'] ) ? $data['materialid'] : '';
			$ad_qstring        = isset( $data['q'] ) ? $data['q'] : '';
			$begin_time = isset($data['begin_time']) ? $data['begin_time'] : '';
			$end_time = isset($data['end_time']) ? $data['end_time'] : '';
			if($status) {
				$where['status'] = $status;
			}

			$where = ['a.status' => ['neq', -1]];
			if($ad_qstring) {
				$where['a.id|title'] = [ "like", "%$ad_qstring%" ];
			}
			if($adposition)
			{
				$where['sense.sensename'] = ["like", "%$adposition%"];
			}
			if($materialid) {
				$where['a.materialid'] = $materialid;
			}
			if($status)
			{
				$where['a.status'] = $status;
			}
			if(!empty($begin_time) && !empty($end_time))
			{
				$where['a.update_time'] = ['between', [$begin_time, $end_time]];
			}
		}
		$where['userid'] = ADMIN_ID;
		$data_list = Db::table('adserver.advertisement')
		               ->field('a.*,site.sitename,sense.sensename,sense.width,sense.height')
		               ->alias('a')
		               ->join("adserver.adsite site", "a.adsiteid = site.id", 'LEFT')
		               ->join("adserver.adsense sense", "a.adsenseid = sense.id", 'LEFT')
		               ->where($where)
					   ->order('a.id desc')
		               ->paginate(15, false, ['query' => $this->request->param()]);
		$pages = $data_list->render();
		$this->assign('review_url', \think\Config::get('review'));
		$this->assign('pages', $pages);
		$this->assign('data_list', $data_list);
		return $this->fetch();
	}

	/**
	 * 获取站点对应的广告位 - 硬广
	 * @return string
	 * @throws \think\exception\DbException
	 * @throws db\exception\DataNotFoundException
	 * @throws db\exception\ModelNotFoundException
	 */
	public function getAdPosition()
	{
		// 根据站点，获取对应的广告位
		$adsiteid = input('adsiteid');
		$adsense = $this->_getAdPosition($adsiteid, ['sensemodel' => ['exp', '<> 3']]);
		return json_encode($adsense);
	}

	/**
	 * 获取站点下所有广告位对应的尺寸 - 信息流
	 * @param int $adsiteid 站点 id
	 */
	public function getAdPositionSize()
	{
		$adsiteid = input('adsiteid');
		$where = ['status' => 1, 'adsite' => $adsiteid];
		// 竞价
		$where['sensemodel'] = 2;
		// 信息流
		$where['materialmodel'] = 3;
		$row = Db::table('adsense')->field('width, height')
		                           ->group('width, height')
		                           ->where($where)->select();
		return json_encode($row);
	}

	protected function _getAdPosition($adsiteid = 0, $ext_condition = [])
	{
		$where = ['status' => 1, 'adsite' => $adsiteid];
		// 普通用户只显示竞价的广告位
		if(ADMIN_ROLE == 4){
			$where['sensemodel'] = 2;
		}
		if(isset($ext_condition['sensemodel']))
		{
			$where['sensemodel'] = $ext_condition['sensemodel'];
		}
		$adsense = Db::table('adsense')->field('id, sensename, sensetype')->where($where)->select();
		return $adsense;
	}

	// 获取所有广告站点
	protected function getSites()
	{
		$adsite = Db::table('adsite')->field('id,sitename')->where(['status' => 1])->select();
		$this->assign('adsite', $adsite);
	}

	// 信息流站点 - 有对应信息流广告位的
	protected function getSitesInfo()
	{
		$adsite = Db::table('adsite')
		            ->alias('as')
		            ->field('as.id,as.sitename,se.id as sid')
					->join('adserver.adsense se', 'as.id = se.adsite', 'left')
		            ->where(['as.status' => 1, 'se.id' =>['exp', 'is not null'], 'se.materialmodel' => 3, 'se.status' => ['exp', '<> -1']])
		            ->group('as.sitename')
					->order('as.id')
		            ->select();
		$this->assign('adsite', $adsite);
	}

	/**
	 * 获取余额 - 被前端调用
	 * @return string
	 */
	public function get_blance(){
		$data = input('post.');
		$price = floatval($data['price']);
		$row = Db::table('userinfo')->where('uid =' . ADMIN_ID)->find();
		$account = $row['account'] / 100;
		$res = (adv_bccomp($price, $account) == 1);
		return json_encode(['price' => $price, 'account' => $account, 'result' => $res]);
	}

	/**
	 * 添加广告
	 * @return mixed|void
	 */
	public function  add()
	{
		if($this->request->isPost())
		{
			if(! input('materialid'))
			{
				$this->error('没有选择素材, 添加失败', 'admin/adserving/add');
			}
			$param_arr = $this->_loadAddUpdateData();
			$param_arr['userid'] = ADMIN_ID;
			$param_arr['update_time'] = date('Y-m-d H:i:s', time());
			$param_arr['create_time'] = date('Y-m-d H:i:s', time());
			$res = Db::table('advertisement')->insert($param_arr);
			if($res)
			{
				return $this->success('添加成功', 'admin/adserving/lists');
			} else {
				return $this->error('添加失败');
			}
		}

		$this->getSites();
		$this->assign('edit', 0);
		$this->assign('role_id', ADMIN_ROLE);
		return $this->fetch('form');
	}

	/**
	 * 更新信息流状态为 5: 待审核状态
	 * $param materialid 字符拼接的素材id
	 */
	protected function _updateInfoMaterialStatus($materialid = ''){
		$materialid_arr = '';
		if(strpos($materialid, '|') !== false)
		{
			$materialid_arr = explode('|', $materialid);
			$where['id'] = ['in', $materialid_arr];
		} else {
			$materialid_arr = $materialid;
			$where['id'] = $materialid;
		}
		return Db::table('material_main')->where($where)->setField(['status' => 5]);
	}

	/**
	 * 添加信息流
	 * @return mixed|void
	 */
	public function  addinfo()
	{
		if($this->request->isPost())
		{
			$param = $this->request->param();

			if(! input('materialid'))
			{
				$this->error('没有选择素材, 添加失败', 'admin/adserving/addinfo');
			}
			$matrial_update = $this->_updateInfoMaterialStatus($param['materialid']);
			$param_arr = $this->_loadAddUpdateDataInfo($param);
			$param_arr['userid'] = ADMIN_ID;
			$param_arr['update_time'] = date('Y-m-d H:i:s', time());
			$param_arr['create_time'] = date('Y-m-d H:i:s', time());

			// 全地段投放
			$param_arr['orientation'] = 1;
			$param_arr['adv_type'] = 1;
			$res = Db::table('advertisement')->insert($param_arr);
			if($res)
			{
				return $this->success('添加成功', 'admin/adserving/lists');
			} else {
				return $this->error('添加失败');
			}
		}

		$this->getSitesInfo();
		$this->assign('edit', 0);
		$this->assign('role_id', ADMIN_ROLE);
		$this->assign('action', url('admin/Adserving/addinfo'));
		return $this->fetch('infoform');
	}

	protected function _loadAddUpdateData()
	{
		$material_id = input('materialid');
		$extra_param = str_replace('&amp;', '&', input('extra_param'));
		parse_str($extra_param, $param_arr);
		$param_arr['materialid'] = $material_id;
		$time = $this->joinTime($param_arr['time']);
		$param_arr['time'] = $time;
		$param_arr['status'] = 2; // 状态: 即将投放
		$param_arr['price'] = yuan2fen($param_arr['price']);
		$param_arr['pricelimit'] = yuan2fen($param_arr['pricelimit']);
		$param_arr['numlimit'] = $param_arr['pricelimit'] ? (($param_arr['pricelimit'] / $param_arr['price'] ) * 1000) : 0;
		unset($param_arr['sensetype']);
		return $param_arr;
	}

	protected function _loadAddUpdateDataInfo($param_arr)
	{
		$time = $param_arr['time'];
		$time = $this->joinTime($time);
		$param_arr['time'] = $time;
		$param_arr['status'] = 2; // 状态: 即将投放
		$param_arr['price'] = yuan2fen($param_arr['price']);
		$param_arr['pricelimit'] = yuan2fen($param_arr['pricelimit']);
		$param_arr['numlimit'] = $param_arr['pricelimit'] ? (($param_arr['pricelimit'] / $param_arr['price'] ) * 1000) : 0;
		unset($param_arr['sensetype']);
		return $param_arr;
	}

	/**
	 *
	 */
	protected function _splitTime($time)
	{
		$time_arr = explode('|', $time);
		$section_arr = [];
		foreach($time_arr as $k => $v)
		{
			$tt = explode(',', $v);
			$index = 't' . ($k + 1);
			$section_arr[$index]['btime'] = $tt[0];
			$section_arr[$index]['etime'] = end($tt);
		}
		return $section_arr;
	}

	protected function _isObjExist($obj)
	{
		if( ! $obj )
		{
//			$this->error('广告不存在');
		}
	}

	/**
	 * 编辑广告 - 硬广
	 * @param int $id
	 * @param int $adsiteid
	 *
	 * @return mixed
	 * @throws \think\Exception
	 * @throws \think\exception\DbException
	 * @throws \think\exception\PDOException
	 * @throws db\exception\DataNotFoundException
	 * @throws db\exception\ModelNotFoundException
	 */
	public function edit($id = 0, $adsiteid = 0)
	{
		$this->_check_ads_active($id);
		$this->_check_ads_finish($id);
		if($this->request->isPost())
		{
			$param_arr = $this->_loadAddUpdateData();
			$param_arr['id'] = $param_arr['adid'];
			$param_arr['userid'] = ADMIN_ID;
			unset($param_arr['adid']);
			$param_arr['update_time'] = date('Y-m-d H:i:s', time());
			$res = Db::table('advertisement')->update($param_arr);
			$this->_isObjExist($res);
			if($res === false)
			{
				return $this->error('修改失败');
			} else {
				return $this->success('修改成功', 'admin/adserving/lists');
			}
		}

		$this->getSites();
		$map['a.id'] = $id;
		$map['a.userid'] = ADMIN_ID;
		$row = Db::table('advertisement')
				->field('a.*,sense.sensetype,sense.sensemodel')
				->alias('a')
				->join("adserver.adsense sense", "a.adsenseid = sense.id", "LEFT")
				->where($map)->find();
		$this->_isObjExist($row);
		$row['adPositions'] = $this->_getAdPosition($adsiteid, ['sensemodel' => ['exp', '<> 3']]);
		$time_arr = explode(',', $row['time']);
		$row['price'] = fen2yuan($row['price']);
		$row['pricelimit'] = fen2yuan($row['pricelimit']);
		$row['btime'] = $time_arr[0];
		$row['etime'] = end($time_arr);
		$this->assign('role_id', ADMIN_ROLE);
		$this->assign('edit', 1);
		$this->assign('data_info', $row);
		return $this->fetch('form');
	}

	/**
	 * 编辑信息流 - 信息流
	 * @param int $id
	 * @param int $adsiteid
	 *
	 * @return mixed
	 * @throws \think\Exception
	 * @throws \think\exception\DbException
	 * @throws \think\exception\PDOException
	 * @throws db\exception\DataNotFoundException
	 * @throws db\exception\ModelNotFoundException
	 */
	public function editinfo($id = 0, $adsiteid = 0)
	{
		$this->_check_ads_active($id);
		$this->_check_ads_finish($id);
		if($this->request->isPost())
		{
			$param = $this->request->param();
			$matrial_update = $this->_updateInfoMaterialStatus($param['materialid']);
			$param_arr = $this->_loadAddUpdateDataInfo($param);
			$param_arr['userid'] = ADMIN_ID;
			unset($param_arr['adid']);
			$param_arr['update_time'] = date('Y-m-d H:i:s', time());
			// 全地段投放
			$param_arr['orientation'] = 1;
			$param_arr['adv_type'] = 1;
			$res = Db::table('advertisement')->update($param_arr);
			$this->_isObjExist($res);
			if($res === false)
			{
				return $this->error('修改失败');
			} else {
				return $this->success('修改成功', 'admin/adserving/lists');
			}
		}

		$this->getSitesInfo();
		$map['a.id'] = $id;
		$map['a.userid'] = ADMIN_ID;
		$row = Db::table('advertisement')
		         ->field('a.*,sense.sensetype,sense.sensemodel')
		         ->alias('a')
		         ->join("adserver.adsense sense", "a.adsenseid = sense.id", "LEFT")
		         ->where($map)->find();
		$this->_isObjExist($row);
		$row['adPositions'] = $this->_getAdPosition($adsiteid);
		$time_arr = explode(',', $row['time']);
		$row['price'] = fen2yuan($row['price']);
		$row['pricelimit'] = fen2yuan($row['pricelimit']);
		$row['btime'] = $time_arr[0];
		$row['etime'] = end($time_arr);
		// 拼接选中的素材列表信息
		$choosed_material_data = '';
		// 数据库中 materialid 字段是以 |（竖线）分隔的素材id
		$mids = explode('|', $row['materialid']);
		$m_row = Db::table('material_main')
			->field('id, material_title')
			->where('id', 'in', $mids)
			->select();
		// 将选中的素材id 和名称处理为这种形式方便前端处理: 素材id,素材1|素材id2,素材2
		foreach($m_row as $k => $v)
		{
			$span = $v['id'] . ',' . $v['material_title'];
			$choosed_material_data .= '|' . $span;
		}
		$choosed_material_data = trim($choosed_material_data, '|');
		$this->assign('choosed_material_data', $choosed_material_data);
		$this->assign('role_id', ADMIN_ROLE);
		$this->assign('action', url('admin/Adserving/editinfo'));
		$this->assign('edit', 1);
		$this->assign('data_info', $row);
		return $this->fetch('infoform');
	}

	/**
	 * @param $aids 素材id material_main 的主键
	 * 检查素材的修改是否安全（改素材没有被任何正在投放的广告使用，则为安全）
	 */
	protected function _check_ads_active($aids = [])
	{
		$map['userid'] = ADMIN_ID;
		// 广告停止条件 status = 3 && running_status = 1
		$map['status'] = 3; // 投放中
		$map['running_status'] = 1;
		if( ! is_array($aids))
		{
			$aids = [$aids];
		}
		$map['id'] = ['in', $aids];
		$row = Db::table( 'adserver.advertisement' )->field( 'id' )->where( $map )->select();
		if($row)
		{
			$active_ids = [];
			foreach ( $row as $k => $v ) {
				$active_ids[] = $v['id'];
			}
			$str_ids = implode(',', array_values($active_ids));
			return $this->error( 'ID 为 ' . $str_ids . '广告正在投放，操作前请先停止 !' );
		}
	}

	/**
	 * @param $aid 素材id material_main 的主键
	 * 检查素材的修改是否安全（改素材没有被任何正在投放的广告使用，则为安全）
	 */
	protected function _check_ads_finish($aids = [])
	{
		$map['userid'] = ADMIN_ID;
		$map['status'] = 4; // 已投完
		if( ! is_array($aids))
		{
			$aids = [$aids];
		}
		$map['id'] = ['in', $aids];
		$row = Db::table( 'adserver.advertisement' )->field( 'id' )->where( $map )->select();
		if($row)
		{
			$finish_ids = [];
			foreach ( $row as $k => $v ) {
				$finish_ids[] = $v['id'];
			}
			return $this->error( '该广告已经投完完毕，不支持操作 !' );
		}
	}

	/**
	 * 删除广告
	 * @return mixed|void
	 */
	public function del($id = 0, $material_id = 0)
	{
		$this->_check_ads_active($id);
		$id   = input('id');
		$map['id'] = $id;
		$map['userid'] = ADMIN_ID;
		$res = Db::table('advertisement')->where($map)->update(['status' => -1]);
		$this->_isObjExist($res);
		if ($res === false) {
			return $this->error('删除失败');
		}
		return $this->success('删除成功');
	}

	/**
	 * 批量删除广告
	 * @throws \think\Exception
	 * @throws \think\exception\PDOException
	 */
	public function delall()
	{
		$ids   = input('param.ids/a') ? input('param.ids/a') : input('param.id/a');
		$this->_check_ads_active($ids);
		$map = [];
		$map['id'] = ['in', $ids];
		$map['userid'] = ADMIN_ID;
		$res = Db::table('advertisement')->where($map)->update(['status' => -1]);
		if ($res === false) {
			return $this->error('删除失败');
		}
		return $this->success('删除成功');
	}

	/**
	 * 检查时间区间是否交叉 - ajax 校验
	 * 每一个时间段的开始时间不应该落在以后的闭合区间内，结束时间大于开始时间
	 */
	public function checkTime($old_time)
	{
		return $old_time;
	}

	/**
	 * 改变广告启用/停止状态
	 */
	public function changerunningstatus($id = 0, $running_status = 0)
	{
		$this->_check_material_status($id);
		$this->_check_ads_finish($id);
		$running_status = ($running_status == 1) ? 2 : 1;

		// 暂时没有使用到事务，注释
//		Db::startTrans();
		$res = Db::table('advertisement')->where(['id' => $id])->update(['running_status' => $running_status]);
		if(($res !== false))
		{
//			Db::commit();
			if($running_status == 1)
			{
				return $this->success('修改成功, 将在十分钟左右生效');
			} else {
				return $this->success('修改成功');
			}
		} else {
//			Db::rollback();
			return $this->error('修改失败');
		}
	}

	/**
	 * 检查包含的素材的审核状态
	 */
	protected function _check_material_status($id = 0)
	{
		$mids_str = Db::table('advertisement')->where('id =' . $id)->field('materialid')->find();
		$materialid = $mids_str['materialid'];
		if(strpos($materialid, '|') !== false)
		{
			$mids_arr = explode('|', $materialid);
		} else {
			$mids_arr = [$materialid];
		}
		$row = Db::table('material_main')->field('id, status')->where('id', 'in', $mids_arr)->select();
		$arr = [];
		foreach($row as $k => $v)
		{
			if($v['status'] != 3)
			{
				$arr[] = $v['id'];
			}
		}
		if(!empty($arr))
		{
			$id_str = implode(',', $arr);
			$id_str = trim($id_str, ',');
			return $this->error('请检查广告素材id:'. $id_str .'的审核状态 !');
		}
	}

	/**
	 * 将时间区间拼接成 time 字段的字符串
	 */
	protected function joinTime($old_time)
	{
		$old_time = $this->checkTime($old_time);
		$time = '';
		foreach($old_time as $k => $v)
		{
			$time_single = implode(',', $v);
			$time .= '|' . $time_single;
		}
		$time = trim(trim($time, ','), '|');
		return $time;
	}

	/**
	 * 根据广告位动态的获取竞价方式 - 被前端调用
	 * @return mixed
	 * @throws \think\exception\DbException
	 * @throws db\exception\DataNotFoundException
	 * @throws db\exception\ModelNotFoundException
	 */
	public function sensemodel()
	{
		$adsenseid = input('adsenseid');
		$row = Db::table('adsense')->field('sensemodel')->where('id = ' . $adsenseid)->find();
		return $row['sensemodel'];
	}

	/**
	 * 根据条件选择素材
	 * @param materialid 选中素材的 id [编辑广告时用户选中对应的素材 radio]
	 * @param adsenseid 广告位 id
	 * @param sensetype
	 */
	public function getMaterialList($materialid = 0)
	{
		$material_id_choosed = input('materialid', 0);
		$adsenseid = input('adsenseid');
		$adsensetype = input('sensetype');
		$adsense = Db::table('adsense')->field('width, height, materialmodel')->where(['id' => $adsenseid])->find();

		// 批量广告位的素材筛选条件 1: 尺寸  2： adsense.sensetype
		switch ($adsensetype)
		{   // 1: 文字表 2: 图片表 3: flash 4: couplet
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
		switch ($adsense['materialmodel'])
		{
			case 1:
				$where['material_type'] = 1;
				break;
			case 2:
			case 3:
				$where['material_type'] = ['in', [2, 3]];
				break;
			default:
				// Nothing
		}
		$where['width'] = $adsense['width'];
		$where['height'] = $adsense['height'];
		$where['userid'] = ADMIN_ID;
		$where['status'] = 3; // 审核已经通过的
		$material_list = Db::table('material_main')->where($where)->paginate(15, false, ['query' =>
		$this->request->param()]);

		// 分页
		$pages = $material_list->render();
		$this->assign('data_list', $material_list);
		$this->assign('extra_param', http_build_query($this->request->param()));
		$this->assign('material_id_choosed', $material_id_choosed);
		$this->assign('pages', $pages);
		$this->assign('edit', 0);
		$this->assign('preview_url', \think\Config::get('review'));
		return $this->fetch('materiallists');
	}
}