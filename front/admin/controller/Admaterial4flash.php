<?php
/**
 * Created by PhpStorm.
 * User: hudy@cnfol.net
 * Date: 2018/3/14 0014
 * Time: 15:51
 */

namespace app\admin\controller;
use app\admin\model\Admaterial4flash as MaterialModel;
use app\admin\model\Admaterial4main as MainModel;
use app\admin\model\Admaterial;
use think\Exception;
use think\db;

/**
 * 广告素材库 - flash
 * @package app\admin\controller
 */

class Admaterial4flash extends Admaterialcommon
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
	 * 新增广告素材
	 * @return mixed
	 */
	public function addflash()
	{
		if($this->request->isPost())
		{
			Db::startTrans();
			$dataMain = $data = $this->request->post();
			if(empty($dataMain['image_url']))
			{
				$this->error("请上传 Flash 文件");
			}
			$data['image_url'] = $dataMain['image_url'];
			$data = $this->dropColumns($data);
			$res_flash = MaterialModel::create($data);
			unset($dataMain['flash_bgcolor']);
			$data['material_id'] = $res_flash['sid'];
			unset($data['material_type']);
			$material_id = $res_flash['sid'];
			$dataMain['material_id'] = $material_id;
			$dataMain['userid'] = ADMIN_ID;
			$dataMain['adsenseid'] = $this->_get_adsenseid($dataMain['adsenseid']);
			$res_main = $this->addAdMaterialMain($dataMain);
			if( $res_main && $res_flash)
			{
				Db::commit();
				return $this->success('添加成功', 'admin/admaterial/lists');
			} else {
				Db::rollback();
				return $this->error('添加失败');
			}

		}
		$this->assign('edit', 0);
		$this->assign('flashform', '');
		$adsite = $this->getSites();
		$this->assign('adsite', $adsite);
		return $this->fetch('flashform');
	}

	public function edit($id = 0, $material_id = 0)
	{
		$id = input('id');
		$this->check_ads_active($id);
		// 提交修改
		if($this->request->isPost())
		{
			Db::startTrans();
			$dataMain = $dataFlash = $this->request->post();
			if($_FILES['image']['size'] > 0)
			{
				$image = $this->upload( 'image' );
				$path = upload2fileserver($image);
				$dataFlash['image_url']      = $path;
			} else {
				$dataFlash['image_url'] = $dataFlash['image_url'];
			}
			$dataFlash = $this->dropColumns($dataFlash);
			unset($dataFlash['id']);
			unset($dataFlash['material_type']);
			$dataMain['adsenseid'] = $this->_get_adsenseid($dataMain['adsenseid']);
			$res_main = $this->updateMaterialMain($dataMain);
			$res_flash = MaterialModel::update($dataFlash, ['sid' => $material_id]);
			if($res_main && $res_flash)
			{
				Db::commit();
				return $this->success('修改成功', 'admin/admaterial/lists');
			} else {
				Db::rollback();
				return $this->error('修改失败');
			}
		}
		$row = Admaterial::findJoin($id, 'material_flash', 'f');
		$this->assign('edit', 1);
		$this->assign('flashform', $row);
		$adsite = $this->getSites();
		$this->assign('adsite', $adsite);
		return $this->fetch('flashform');
	}

	/**
	 * 预览
	 */
	public function preview()
	{
		$id = input('id');
		$row = Admaterial::findJoin($id, 'material_flash', 'i');
		$this->assign('image', $row);
		return $this->fetch('previewflash');
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