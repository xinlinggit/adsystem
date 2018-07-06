<?php
/**
 * Created by PhpStorm.
 * User: hudy@cnfol.net
 * Date: 2018/4/8 0008
 * Time: 11:07
 */

namespace cms\innerads\controller;
use cnfol\unit\ArrayUnit;
use think\Db;
use think\Loader;
use think\Config;
use cms\common\model;
use cms\innerads\model\MaterialText;
use cms\innerads\model\MaterialImage;
use cms\innerads\model\MaterialInfo;
use cms\innerads\model\MaterialMain;
use cms\innerads\model\Advertisement;
use think\exception\HttpResponseException;
use think\Response;


class Innerads extends Base
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
		               ->field('a.*,site.sitename,sense.sensename,sense.width,sense.height,sense.sensetype,u.username,bu.real_name')
		               ->alias('a')
		               ->join("adserver.adsite site", "a.adsiteid = site.id", 'LEFT')
		               ->join("adserver.adsense sense", "a.adsenseid = sense.id", 'LEFT')
			->join('adserver.ad_admin_user u', 'a.userid = u.id', 'LEFT')
			->join('adserver.backend_user bu', 'a.backend_user_id = bu.id', 'LEFT')
						->where('a.status <> -1 and a.is_inner = 1')
		               ->where($map)
			->order($order . ' ' . $by)
		               ->paginate($per_page, false, ['query' => $this->request->param()]);
		$raw_data = $page->items();
		$this->view->assign('selected_adsenseid', input('adsenseid'));
		$this->view->assign('page', $page);
		$this->view->assign('list', $raw_data);

		return $this->fetch();
	}

	// 根据广告获取素材审核状态
	protected function _get_material_status($id)
	{
		$materialid_str = Db::table('advertisement')->where('id = ' . $id)->value('materialid');
		$materialid_ids = explode('|', $materialid_str);
		$status = Db::table('material_main')->where(['id' => ['in', $materialid_ids]])->field('id, status')->select();
		/**
		 * ['状态' => [当前素材id 的一维或多维数组]]
		 */
		$status_merge = [];
		foreach($status as $k => $v)
		{
			$current_status = $v['status'];
			$current_id = $v['id'];
			if( ! isset($status_merge[$current_status])) {
				$status_merge[ $current_status ] = $current_id;
			} else {
				$new_id[] = $status_merge[$current_status];
				$new_id[] = $current_id;
				$status_merge[$current_status] = $new_id;
			}
		}
		return $status_merge;
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
		$set = [];
		$set['order'] = 'id';
		$set['thead'] = ['id','create_time'];
		/*获取排序参数，默认按ID倒序*/
		$order = $this->request->param('order', isset($set['order'])?$set['order']:'id');
		$by = $this->request->param('by', isset($set['by'])?$set['by']:'desc');
		$this->get_thead(isset($set['thead'])?$set['thead']:[$order], $order, $by);

		/*分页设置，默认15，小于100*/
		$per_page = $this->request->param('num', 8);
		$per_page = min(100, $per_page);
		$where['material_type'] = input('project_type');
		$where['adsiteid'] = input('adsiteid');
		$where['adsenseid'] = input('adsenseid');
		$where['backend_user_id'] = $this->backend['id'];
		$where['status'] = 3; // 审核已经通过的
		$pages = Db::table('material_main')->where($where)->paginate($per_page, false, 	['query' => $this->request->param()]);

		// 分页
		$this->assign('extra_param', http_build_query($this->request->param()));
		$material_id_choosed = input('material_id', 0);
		$this->assign('material_id_choosed', $material_id_choosed);
		$this->assign('callback', input("callback"));
		$this->assign('page', $pages);
		$this->assign('action', url('getMaterialList'));
		$this->assign('list', $pages->items());
		return  $this->fetch('materiallists');
	}

	public function getposition()
	{
		// 根据站点，获取对应的广告位
		$adsite = input('adsite');
		$materialmodel = input('project_type');
		$where['adsite'] = $adsite;
		$where['materialmodel'] = $materialmodel;
		$row = Db::table('adsense')
			->field('id, sensename')
			->where($where)
			->group('sensename')
			->select();
		return json_encode($row);
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
			return $this->api_error( '请检查广告素材id:'. $id_str .'的审核状态 !' );
		}
	}

	// 启用/停用
	public function change_status()
	{
		// TODO: 启停状态值域的安全检查
		$param = $this->request->param();
		$this->_check_material_status($param['id']);
		$this->_check_ads_finish($param['id']);
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
		$this->view->assign('action',url('getMaterialList') . '?material_id=' . input('material_id') . '&callback=func_list');
		$this->view->assign('add', 1);
		return $this->get_add_model();
	}

	public function add_text_material() {
		if($this->request->isPost())
		{
			$dataMain = $param = $this->request->param();
			$dataMain['userid'] = 0;
			Db::startTrans();
			$res = MaterialText::create($param, true);
			$dataMain['material_id'] = $res['sid'];
			$dataMain['adsenseid'] = $param['adsenseid'];
			$dataMain['adsiteid'] = $param['adsiteid'];
			$res_main = $this->addAdMaterialMain($dataMain);
			if($res && $res_main){
				Db::commit();
				$data = ['code' => 1, 'msg' => '操作成功', 'data' => ['id' => $res_main['id'], 'material_title' => $res_main['material_title']]];
				$response =  Response::create($data,'json');
				throw new HttpResponseException($response);
			} else {
				Db::rollback();
				$data = ['code' => 0, 'msg' => '操作失败'];
				$response =  Response::create($data,'json');
				throw new HttpResponseException($response);
			}
		}
		$this->assign('info', '');
		$this->assign('adsiteid', input('adsiteid'));
		$this->assign('callback', input('callback'));
		$this->assign('adsenseid', input('adsenseid'));
		$this->assign('action', url('add_text_material'));
		$this->assign('edit', 0);
		return  $this->fetch('text_material');
	}

	public function edit_text_material() {
		$material_id = input('material_id');
		if($this->request->isPost())
		{
			$dataText = $dataMain = $param = $this->request->param();
			$dataMain['userid'] = 0;
			Db::startTrans();
			unset($dataText['material_title']);
			unset($dataText['adsenseid']);
			unset($dataText['adsiteid']);
			unset($dataText['material_type']);
			unset($dataText['id']);
			$res = MaterialText::update($dataText, ['sid' => $param['sid'], true]);
			$dataMain['material_id'] = $res['sid'];
			$dataMain['adsenseid'] = $param['adsenseid'];
			$dataMain['adsiteid'] = $param['adsiteid'];
			$res_main = $this->updateAdMaterialMain($dataMain);
			if($res && $res_main){
				Db::commit();
				$data = ['code' => 1, 'msg' => '操作成功', 'data' => ['id' => $res_main['id'], 'material_title' => $res_main['material_title']]];
				$response =  Response::create($data,'json');
				throw new HttpResponseException($response);
			} else {
				Db::rollback();
				$data = ['code' => 0, 'msg' => '操作失败'];
				$response =  Response::create($data,'json');
				throw new HttpResponseException($response);
			}
		}
		$row = Db::table('material_main')
			->alias('main')
			->join('adserver.material_text text', 'main.material_id = text.sid', 'left')
			->where('main.id = ' . $material_id)
			->find();
		$this->assign('action', url('edit_text_material'));
		$this->assign('material', $row);
		$this->assign('edit', 1);
		$this->assign('callback', input('callback'));
		return $this->fetch('text_material');
	}

	/**
	 * 文件上传
	 */
	public function upload()
	{
		$image = $this->upload2('image');
		// debug
		@error_log(date('Y-m-d H:i:s') . ' |-info-| ' . print_r($image, true) . PHP_EOL, 3, LOG_PATH . '/upload_' . date('Ymd') . '.log');
		$data = upload2fileserver($image);
		if($data['code'] == 1)
		{
			echo(json_encode(['flag'=>1,'data'=> $data['path']],JSON_UNESCAPED_UNICODE));exit;
		} else {
			echo(json_encode(['flag'=>0,'data'=> $data['msg']],JSON_UNESCAPED_UNICODE));exit;
		}
	}

	/**
	 * @param $aids 素材id material_main 的主键
	 * 检查素材的修改是否安全（改素材没有被任何正在投放的广告使用，则为安全）
	 */
	protected function _check_ads_active($aids = [])
	{
		$map['userid'] = 0;
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
			return $this->api_error( 'ID 为 ' . $str_ids . '广告正在投放，操作前请先停止 !' );
		}
	}

	/**
	 * @param $aid 素材id material_main 的主键
	 * 检查素材的修改是否安全（改素材没有被任何正在投放的广告使用，则为安全）
	 */
	protected function _check_ads_finish($aids = [])
	{
		$map['userid'] = 0;
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
			return $this->api_error('该广告已经投完完毕，不支持操作 !');
		}
	}

	public function add_image_material() {
		if($this->request->isPost())
		{
			$dataMain = $param = $this->request->param();
			$dataMain['userid'] = 0;
			Db::startTrans();
			$res = MaterialImage::create($param, true);
			$dataMain['material_id'] = $res['sid'];
			$dataMain['adsenseid'] = $param['adsenseid'];
			$dataMain['adsiteid'] = $param['adsiteid'];
			$res_main = $this->addAdMaterialMain($dataMain);
			if($res && $res_main){
				Db::commit();
				$data = ['code' => 1, 'msg' => '操作成功', 'data' => ['id' => $res_main['id'], 'material_title' => $res_main['material_title']]];
				$response =  Response::create($data,'json');
				throw new HttpResponseException($response);
			} else {
				Db::rollback();
				$data = ['code' => 0, 'msg' => '操作失败'];
				$response =  Response::create($data,'json');
				throw new HttpResponseException($response);
			}
		}
		$this->assign('info', '');
		$this->assign('adsiteid', input('adsiteid'));
		$this->assign('callback', input('callback'));
		$this->assign('adsenseid', input('adsenseid'));
		$this->assign('action', url('add_image_material'));
		$this->assign('edit', 0);
		return  $this->fetch('image_material');
	}

	public function edit_image_material() {
		$material_id  = input('material_id');
		if($this->request->isPost())
		{
			$dataInfo = $dataMain = $param = $this->request->param();
			$dataMain['userid'] = 0;
//			Db::startTrans();
			unset($dataInfo['material_title']);
			unset($dataInfo['adsenseid']);
			unset($dataInfo['adsiteid']);
			unset($dataInfo['material_type']);
			unset($dataInfo['id']);
			$res = MaterialImage::update($dataInfo, ['sid' => $param['sid'], true]);
			$dataMain['material_id'] = $res['sid'];
			$dataMain['adsenseid'] = $param['adsenseid'];
			$dataMain['adsiteid'] = $param['adsiteid'];
			$res_main = $this->updateAdMaterialMain($dataMain);
			if($res && $res_main){
//				Db::commit();
				$data = ['code' => 1, 'msg' => '操作成功', 'data' => ['id' => $res_main['id'], 'material_title' => $res_main['material_title']]];
				$response =  Response::create($data,'json');
				throw new HttpResponseException($response);
			} else {
//				Db::rollback();
				$data = ['code' => 0, 'msg' => '操作失败'];
				$response =  Response::create($data,'json');
				throw new HttpResponseException($response);
			}
		}

		$row = Db::table('material_main')
		         ->alias('main')
		         ->join('adserver.material_image image', 'main.material_id = image.sid', 'left')
			->where('main.id = ' . $material_id)
		         ->find();
		$this->assign('action', url('edit_image_material'));
		$this->assign('material', $row);
		$this->assign('info', '');
		$this->assign('edit', 1);
		$this->assign('callback', input('callback'));
		return $this->fetch('image_material');
	}

	public function add_info_material() {
		if($this->request->isPost())
		{
			$dataMain = $param = $this->request->param();
			$dataMain['userid'] = 0;
			Db::startTrans();
			$res = MaterialInfo::create($param, true);
			$dataMain['material_id'] = $res['sid'];
			$dataMain['adsenseid'] = $param['adsenseid'];
			$dataMain['adsiteid'] = $param['adsiteid'];
			$res_main = $this->addAdMaterialMain($dataMain);
			if($res && $res_main){
				Db::commit();
				$data = ['code' => 1, 'msg' => '操作成功', 'data' => ['id' => $res_main['id'], 'material_title' => $res_main['material_title']]];
				$response =  Response::create($data,'json');
				throw new HttpResponseException($response);
			} else {
				Db::rollback();
				$data = ['code' => 0, 'msg' => '操作失败'];
				$response =  Response::create($data,'json');
				throw new HttpResponseException($response);
			}
		}
		$this->assign('info', '');
		$this->assign('adsiteid', input('adsiteid'));
		$this->assign('callback', input('callback'));
		$this->assign('adsenseid', input('adsenseid'));
		$this->assign('action', url('add_info_material'));
		$this->assign('edit', 0);
		return  $this->fetch('info_material');
	}

	public function edit_info_material() {
		$material_id = input('material_id');
		if($this->request->isPost())
		{
			$dataInfo = $dataMain = $param = $this->request->param();
			$dataMain['userid'] = 0;
//			Db::startTrans();
			unset($dataInfo['material_title']);
			unset($dataInfo['adsenseid']);
			unset($dataInfo['adsiteid']);
			unset($dataInfo['material_type']);
			unset($dataInfo['id']);
			$res = MaterialInfo::update($dataInfo, ['sid' => $param['sid'], true]);
			$dataMain['material_id'] = $res['sid'];
			$dataMain['adsenseid'] = $param['adsenseid'];
			$dataMain['adsiteid'] = $param['adsiteid'];
			$res_main = $this->updateAdMaterialMain($dataMain);
			if($res && $res_main){
//				Db::commit();
				$data = ['code' => 1, 'msg' => '操作成功', 'data' => ['id' => $res_main['id'], 'material_title' => $res_main['material_title']]];
				$response =  Response::create($data,'json');
				throw new HttpResponseException($response);
			} else {
//				Db::rollback();
				$data = ['code' => 0, 'msg' => '操作失败'];
				$response =  Response::create($data,'json');
				throw new HttpResponseException($response);
			}
		}
		$row = Db::table('material_main')
		         ->alias('main')
		         ->join('adserver.material_info info', 'main.material_id = info.sid', 'left')
			->where('main.id = ' . $material_id)
		         ->find();
		$this->assign('action', url('edit_info_material'));
		$this->assign('material', $row);
		$this->assign('info', '');
		$this->assign('edit', 1);
		$this->assign('callback', input('callback'));
		return $this->fetch('info_material');
	}

	/**
	 * 添加广告
	 */
	public function add(){
		$param = $this->request->param();
		if(strtotime($param['etime']) < strtotime($param['btime']))
		{
			$this->error('结束时间不能早于开始时间');
		}
		// 即将投放
		$param['status'] = 2;
		$param['userid'] = 0;
		$param['backend_user_id'] = $this->backend['id'];
		$param['is_inner'] = 1;
		$param['time'] = $param['btime'] . ',' . $param['etime'];
		$param['update_time'] = date('Y-m-d H:i:s', time());
		$param['create_time'] = date('Y-m-d H:i:s', time());
		$matrial_update = $this->_updateInfoMaterialStatus($param['materialid']);
		$res = Advertisement::create($param, true);
		if($res !== false)
		{
			$data = ['code' => 1, 'msg' => '操作成功'];
			$response =  Response::create($data,'json');
			throw new HttpResponseException($response);
		} else {
			$data = ['code' => 0, 'msg' => '操作失败'];
			$response =  Response::create($data,'json');
			throw new HttpResponseException($response);
		}
	}

	/**
	 * 更新信息流状态为 3: 审核通过
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
		return Db::table('material_main')->where($where)->setField(['status' => 3]);
	}

	/**
	 * 编辑广告
	 */
	public function edit() {
		if($this->request->isPost())
		{
			$param = $this->request->param();
			// 即将投放
			$param['status'] = 2;
			$param['userid'] = 0;
			$param['time'] = $param['btime'] . ',' . $param['etime'];
			$param['update_time'] = date('Y-m-d H:i:s', time());
			$param['create_time'] = date('Y-m-d H:i:s', time());
			$res = Advertisement::update($param, ['id' => $param['id']], true);
			if($res !== false)
			{
				$data = ['code' => 1, 'msg' => '操作成功'];
				$response =  Response::create($data,'json');
				throw new HttpResponseException($response);
			} else {
				$data = ['code' => 0, 'msg' => '操作失败'];
				$response =  Response::create($data,'json');
				throw new HttpResponseException($response);
			}
		}
		$id = $this->request->param('aid');
		$this->_check_ads_active($id);
		$this->_check_ads_finish($id);
		$row = Db::table('advertisement')->where('id = ' . $id)->find();
		list($row['btime'], $row['etime']) = explode(',', $row['time']);
		$adsite = $this->_getSites();
		$adsense = $this->getAdPosition($row['adsiteid']);
		// 拼接选中的素材列表信息
		$choosed_material_data = '';
		// 数据库中 materialid 字段是以 |（竖线）分隔的素材id
		if(strpos($row['materialid'], '|') !== false) {
			$mids = explode('|', $row['materialid']);
		} else {
			$mids = [$row['materialid']];
		}
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
		$this->assign('adsite', $adsite);
		$this->assign('adsense', $adsense);
		$this->assign('choosed_adsite', $row['adsiteid']);
		$this->assign('choosed_adsense', $row['adsenseid']);
		$this->view->assign('get_materiallist_action',url('getMaterialList') . '?material_id=' . input('material_id') . '&callback=func_list');
		$this->view->assign('info', $row);
		$this->assign('edit', 1);
		$html = $this->fetch('model_operate');
		return $this->api_success($html);
	}

	/**
	 * 插入记录到 material_main 表
	 */
	public function addAdMaterialMain($data = [])
	{
		$material_main = $this->loadData($data);
		$data =  MaterialMain::create($material_main, true);
		return $data;
	}

	/**
	 * 插入记录到 material_main 表
	 */
	public function updateAdMaterialMain($data = [])
	{
		$material_main = $this->loadData4Edit($data);
		$material_main['id'] = $data['id'];
		$data =  MaterialMain::update($material_main, ['id' => $material_main['id']], true);
		return $data;
	}

	protected function loadData4Edit($data)
	{
		$material_main['material_type'] = $data['material_type'];
		$material_main['material_title'] = isset($data['material_title']) ? $data['material_title'] : '';
		$material_main['material_id'] = $data['material_id'];
		$material_main['userid'] = 0;
		$material_main['backend_user_id'] = $this->backend['id'];
		$material_main['adsenseid'] = isset($data['adsenseid']) ? $data['adsenseid'] :0 ;
		$material_main['adsiteid'] = $data['adsiteid'];
		if($data['material_type'] !== 1)
		{
			$row = Db::table('adsense')->field('width, height')->where('id =' . $material_main['adsenseid'])->find();
			$material_main['width'] = $row['width'];
			$material_main['height'] = $row['height'];
		} else {
			$material_main['width'] = 0;
			$material_main['height'] = 0;
		}
		$material_main['status'] = 3;
		return $material_main;
	}

	protected function loadData($data)
	{
		$material_main['material_type'] = $data['material_type'];
		$material_main['material_title'] = isset($data['material_title']) ? $data['material_title'] : '';
		$material_main['material_id'] = $data['material_id'];
		$material_main['userid'] = 0;
		$material_main['backend_user_id'] = $this->backend['id'];
		$material_main['adsenseid'] = isset($data['adsenseid']) ? $data['adsenseid'] :0 ;
		$material_main['adsiteid'] = $data['adsiteid'];
		if($data['material_type'] !== 1)
		{
			$row = Db::table('adsense')->field('width, height')->where('id =' . $material_main['adsenseid'])->find();
			$material_main['width'] = $row['width'];
			$material_main['height'] = $row['height'];
		} else {
			$material_main['width'] = 0;
			$material_main['height'] = 0;
		}
		$material_main['status'] = 0;
		return $material_main;
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
		$this->view->assign('edit', 0);
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
	public function getAdPosition($adsiteid = '')
	{
		// 根据站点，获取对应的广告位
		$adsiteid = input('adsiteid') ?: $adsiteid;
		$project_type = input('project_type');
		$adsense = $this->_getAdPosition($adsiteid, $project_type);
		return json_encode($adsense);
	}

	protected function _getAdPosition($adsiteid = 0, $project_type = '')
	{
		$where['status'] = 1;
//		$where['sensemodel'] = 1;
		if($adsiteid)
		{
			$where['adsite'] = $adsiteid;
		}
		if($project_type)
		{
			$where['materialmodel'] = $project_type;
		}
		$adsense = Db::table('adsense')->field('id, adsite, sensename, sensetype')->where($where)->select();
		return $adsense;
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
		$this->_check_ads_active($param['id']);
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