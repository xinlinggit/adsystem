<?php
/**
 * Created by PhpStorm.
 * User: hudy@cnfol.net
 * Date: 2018/3/14 0014
 * Time: 15:51
 */

namespace app\admin\controller;
use app\admin\model\Admaterial as MaterialModel;
use app\admin\model\Admaterial4main as MainModel;
use app\admin\model\Admaterial4main;
use think\exception\HttpResponseException;
use think\Response;
use think\db;
/**
 * 广告素材库 - 公共
 * @package app\admin\controller
 */

class Admaterialcommon extends Admin
{

	public function api_success($data = [], $msg = '操作成功') {
		$data = ['code' => 1, 'msg' => $msg, 'data' => $data];
		$response =  Response::create($data,'json');
		throw new HttpResponseException($response);
	}

	public function api_error($msg = '操作失败') {
		$data = ['code' => 0, 'msg' => $msg];
		$response = Response::create($data,'json');
		throw new HttpResponseException($response);
	}

	/**
	 * 素材列表
	 */
	public function lists()
	{
		if($this->request->isGet()) {
			$data           = input();
			$material_type  = isset( $data['material_type'] ) ? $data['material_type'] : 0;
			$qstring        = isset( $data['q'] ) ? $data['q'] : '';
			if($material_type) {
				$where['material_type'] = $material_type;
			}
			if($qstring) {
				$where['id|material_title'] = [ "like", "%$qstring%" ];
			}
		}
		$status = input('status');
		if( ! empty($status))
		{
			$where['status'] = $status;
		} else {
			$where['status'] = ['neq', '-1'];
		}
		$where['userid'] = ADMIN_ID;

		// 1: 文字表 2: 图片表 3: flash
		// 根据不用的 material_type 选择不同的控制器
		$material_type = array('1' => 'admaterial', '2' => 'admaterial4image', '3' => 'admaterial4flash', '4' => 'admaterial4info');
		$data_list = Admaterial4main::where($where)->field('id,remark, material_id,material_title,status,width,height,material_type')
		                            ->order('id desc')
		                            ->paginate(15, false, ['query' =>$this->request->param()]);

		// 分页
		$pages = $data_list->render();
		$this->assign('material_type_selected', input('material_type', ''));
		$this->assign('status_selected', input('status', ''));
		$this->assign('material_type_action', $material_type);
		$this->assign('data_list', $data_list);
		$this->assign('pages', $pages);
		$this->assign('preview_url', \think\Config::get('review'));
		$res = $this->fetch();
		return $res;
	}

	// 获取正确的 adsenseid
	protected function _get_adsenseid($str){
		$str_arr = explode('|', $str);
		return end($str_arr);
	}

	/**
	 * 处理图片的上传
	 * @return string
	 */
	public function doImg()
	{
		if($this->request->isPost())
		{
			ini_set('max_execution_time','300');
			$path = $this->upload('image');
			$status = upload2fileserver($path);
			if($status == -1){
				error_log(0, '文件上传失败');
			} else {

			}
			return json(array('url' => $status));
		}
	}

	protected function loadData($data)
	{
		$material_main['material_type'] = $data['material_type'];
		$material_main['material_title'] = isset($data['material_title']) ? $data['material_title'] : '';
		$material_main['material_id'] = $data['material_id'];
		$material_main['width'] = isset($data['width']) ? $data['width'] : 0;
		$material_main['height'] = isset($data['height']) ? $data['height'] : 0;
		$material_main['userid'] = ADMIN_ID;
		$material_main['adsenseid'] = isset($data['adsenseid']) ? $data['adsenseid'] :0 ;
		$material_main['adsiteid'] = $data['adsiteid'];
		return $material_main;
	}

	/**
	 * 获取站点对应的广告位 ajax
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

	/**
	 * 获取站点下所有广告位对应的尺寸  - 信息流
	 * @param int $adsiteid 站点 id
	 */
	public function getAdPositionSize($adsiteid = 0)
	{
		$where = ['status' => 1, 'adsite' => $adsiteid];
		// 竞价
		$where['sensemodel'] = 2;
		// 信息流
		$where['materialmodel'] = 3;

		$row = Db::table('adsense')->field('width, height')->where($where)->select();
		return json_encode($row);
	}

	protected function _getAdPosition($adsiteid = 0)
	{
		$material_type = input('material_type');
		$where = ['adsense.status' => 1, 'adsense.adsite' => $adsiteid];
		switch ($material_type)
		{
			case "text":
				$where['adsense.materialmodel'] = 1;
				break;
			// 图片和 Flash 公用模式 2
			case "image":
			case "flash":
				$where['adsense.materialmodel'] = 2;
				break;
			default:
				// Nothing
		}
		// 普通用户只显示竞价的广告位
		if(ADMIN_ROLE == 4){
			$where['adsense.sensemodel'] = 2;
		}
		$where['material_main.id'] = ['exp', 'is not null'];
		$row = Db::table('adsense')->field('material_main.id as mid, adsense.id, adsense.width, adsense.height, adsense.sensename, adsense.sensetype')
		         ->join('adserver.material_main', 'adsense.id = material_main.adsenseid', 'left')
		         ->where($where)
		         ->group('adsense.sensename')
		         ->select();
		return $row;
	}

	// 获取所有广告站点
	protected function getSites()
	{
		$where['ad.status'] = 1;
		$where['m.id'] = ['exp', 'is not null'];
		$row = Db::table('adsite')
		         ->alias('ad')
		         ->field('ad.id,ad.sitename,m.id as mid')
		         ->join('adserver.material_main m', 'ad.id = m.adsiteid', 'left')
		         ->where($where)
		         ->group('ad.sitename')
		         ->select();
		return $row;
	}

	/**
	 * @param $mid 素材id material_main 的主键
	 * 检查素材的修改是否安全(该素材没有被任何正在投放的广告使用)
	 */
	public function check_ads_active($mids = [])
	{
		if($mids) {
			$map['userid'] = ADMIN_ID;
			// 投放中
			$map['status'] = 3;
			$map['running_status'] = 1;
			if( ! is_array($mids))
			{
				$mids = [$mids];
			}
			$map['materialid'] = ['in', $mids];
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
	}

	public function upload($type = 'image'){
		// 获取表单上传文件 例如上传了001.jpg
		$file = request()->file($type);
		$fileinfo = $file->getInfo();
		$fileSize = $fileinfo['size'];
		$allow_max_size = Db::name('admin_config')->where('id = 14')->value('value');
		$allow_extension = Db::name('admin_config')->where('id = 15')->value('value');
		if($fileSize > intval($allow_max_size) * 1024){
			return ['code' => 0, 'msg' => '文件大小超过' . $allow_max_size . ' kb' ];
		}
		// 移动到框架应用根目录/public/uploads/material 目录下
		if($file){
			$info = $file->validate(['ext'=>$allow_extension])->move(ROOT_PATH . 'public' . DS . 'upload' . DS . 'material');
			if($info){
				// 成功上传后 获取上传信息
				return ['code' => 1, 'data' => ['path' => 'upload' . DS . 'material' . DS . $info->getSaveName()]];
			}else{
				return ['code' => 0, 'msg' => $this->error($file->getError())];
			}
		}
	}

	/**
	 * 插入记录到 material_main 表
	 */
	public function addAdMaterialMain($data = [])
	{
		$material_main = $this->loadData($data);
		$data =  MainModel::create($material_main);
		return $data;
	}

	public function audit($id)
	{
		$res = MainModel::update(['status' => 5], ['id' => $id, 'status' => 0]);
		if($res)
		{
			return $this->success('修改成功');
		} else {
			return $this->error('修改失败');
		}
	}

	public function delAdMaterialMain($id = 0)
	{
		$data = MainModel::get(['id' => $id]);
		$data->status = -1;
		return $data->save();
	}

	public function updateMaterialMain($data)
	{
		$material_main = $this->loadData($data);
		$material_main['status'] = 5; // 将状态修改为等待审核
		return Admaterial4main::update($material_main, ['id' => $data['id']]);
	}

	/**
	 * 剔除字段仅存在于 material_main 表中的字段
	 */
	protected function dropColumns($data)
	{
		unset($data['material_title']);
		unset($data['width']);
		unset($data['height']);
		unset($data['material_id']);
		unset($data['material_type']);
		unset($data['create_time']);
		unset($data['update_time']);
		unset($data['adsenseid']);
		unset($data['adsiteid']);
		return $data;
	}

	/**
	 * 改变启用状态
	 */
	public function changerunnintstatus()
	{
		$data = input();
		$id = $data['id'];
		$status = $data['status'] ? 0 : 1;
		if( ! MainModel::where('id', $id)->update(['status' => $status]))
		{
			return $this->error('修改失败');
		} else {
			return $this->success('修改成功');
		}
	}
}