<?php
/**
 * Created by PhpStorm.
 * User: hudy@cnfol.net
 * Date: 2018/3/14 0014
 * Time: 15:51
 */

namespace app\admin\controller;
use app\admin\model\Admaterial;
use app\admin\model\Admaterial4info as MaterialInfoModel;
use think\db;


/**
 * 信息流
 * @package app\admin\controller
 */

class Admaterial4info extends Admaterialcommon
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
	 * 添加信息流，弹窗中添加素材
	 * @return mixed
	 */
	public function addimage()
	{
		if($this->request->isPost())
		{
			Db::startTrans();
			$dataMain = $dataInfo = $this->request->post();
			if(empty($dataMain['image_url']))
			{
				$this->error("请添加图片文件");
			}
			$dataInfo['image_url'] = $dataMain['image_url'];
			unset($dataMain['adaptation']);
			unset($dataMain['image']);
			unset($dataInfo['image']);
			$dataMain['userid'] = ADMIN_ID;
			$dataInfo = $this->dropColumns($dataInfo);
			$res_img = MaterialInfoModel::create($dataInfo);
			$dataMain['material_id'] = $res_img['sid'];
			$dataMain['adsenseid'] = 0;
			$res_main = $this->addAdMaterialMain($dataMain);
			if($res_img && $res_main)
			{
				Db::commit();
				return json_encode(['code' => 1, 'msg' => '添加成功', 'data' => ['id' => $res_main['id'], 'material_title' => $res_main['material_title']]]);
			} else {
				Db::rollback();
				return json_encode(['code' => 0, 'msg' => '添加失败']);
			}
		}
		$this->assign('infoform', '');
		$adsite = $this->getSites();
		$this->assign('adsite', $adsite);
		return $this->fetch('infoform');
	}

	/**
	 * 处理图片的上传
	 * @return string
	 */
	public function doImg()
	{
		if($this->request->isPost())
		{
			$image = \think\Image::open($this->request->file('image'));
			$width = $image->width();
			$height = $image->height();
			$old_width = input('width');
			$old_height = input('height');
			if(($old_width != $width) || ($old_height != $height))
			{
				return json(array('code' => 0, 'msg' => '尺寸不符，请重新上传'));exit;
			}

			ini_set('max_execution_time','300');
			$path = $this->upload('image');
			$status = upload2fileserver($path);
			if($status == -1){
				error_log(0, '文件上传失败');
			} else {

			}
			return json(array('code' => 1, 'url' => $status));
		}
	}

	/**
	 * 信息流广告，添加素材弹出层
	 */
	public function pop()
	{
		$callback = input('param.callback/s');
		$width = input('width');
		$height = input('height');
		$adsite = input('adsite');
		$this->assign('callback', $callback);
		$this->assign('action', url('admin/admaterial4info/addimage'));
		$this->assign('width', $width);
		$this->assign('height', $height);
		$this->assign('adsite', $adsite);
		$this->view->engine->layout(false);
		$this->assign('materialform', '');
		return $this->fetch();
	}

	/**
	 * 编辑信息流素材
	 */
	public function pop_edit(){
		if($this->request->isPost())
		{
			Db::startTrans();
			$dataMain = $dataInfo = $this->request->post();
			if ( empty( $dataMain['image_url'] ) ) {
				$this->error( "请添加图片文件" );
			}
			$dataInfo['image_url'] = $dataMain['image_url'];
			unset( $dataMain['adaptation'] );
			unset( $dataMain['image'] );
			unset( $dataInfo['image'] );
			unset($dataInfo['id']);
			$dataMain['userid']      = ADMIN_ID;
			$dataInfo                = $this->dropColumns( $dataInfo );
			$old  = Db::table('material_info')->where('sid = '.$dataInfo['sid'])->find();
			$res_img                 = MaterialInfoModel::update( $dataInfo );
			$dataMain['material_id'] = $res_img['sid'];
			$dataMain['adsenseid']   = 0;
			$material_main = $this->loadData( $dataMain );

			// 仅当修改图片地址、文案和点击连接的时候需要重新审核
			if($old['image_url'] != $dataInfo['image_url'] || $old['image_description'] != $dataInfo['image_description'] || $old['click_url'] != $dataInfo['click_url']){
				$material_main['status'] = 5;
			}
			$res_main      = Db::table('material_main')->where('id = '.$dataMain['id'])->update( $material_main );
			$res = Db::table('material_main')->where('id =' . $dataMain['id'])->find();
			if ( $res_img !== false) {
				Db::commit();
				return json_encode( [
					'code' => 1,
					'msg'  => '添加成功',
					'data' => [
						'id'             => $res['id'],
						'material_title' => $res['material_title']
					]
				] );
			} else {
				Db::rollback();
				return json_encode( [ 'code' => 0, 'msg' => '添加失败' ] );
			}
		} else {
			$material_id = $this->request->param('material_id');
			$materialform = Db::table('material_main main')
			                  ->field('')
			                  ->join('adserver.material_info info', 'main.material_id = info.sid', 'left')
			                  ->where('main.id = ' . $material_id)
			                  ->find();
			$this->assign('callback', 'update_func');
			$this->assign('materialform', $materialform);
			return $this->fetch();
		}
	}

	/**
	 * @return mixed|void
	 */
	public function del($id = 0, $material_id = 0)
	{
		$this->check_ads_active($id);
		$res = $this->delAdMaterialMain($id);
		if ($res === false) {
			return $this->error('删除失败');
		}
		return $this->success('删除成功');
	}

	public function delall()
	{
		$ids   = input('param.ids/a') ? input('param.ids/a') : input('param.id/a');
		$this->check_ads_active($ids);
		$map = [];
		$map['id'] = ['in', $ids];
		$res = Admaterial4main::update(['status' => -1], $map);
		if ($res === false) {
			return $this->error('删除失败');
		}
		return $this->success('删除成功');
	}
}