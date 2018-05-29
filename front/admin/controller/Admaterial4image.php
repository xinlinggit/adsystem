<?php
/**
 * Created by PhpStorm.
 * User: hudy@cnfol.net
 * Date: 2018/3/14 0014
 * Time: 15:51
 */

namespace app\admin\controller;
use app\admin\model\Admaterial;
use app\admin\model\Admaterial4image as MaterialImageModel;
use app\admin\model\Admaterial4main as MainModel;
use think\db;


/**
 * 广告素材库 - 图片
 * @package app\admin\controller
 */

class Admaterial4image extends Admaterialcommon
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
	 * 新增广告素材 - 图片
	 * @return mixed
	 */
	public function addimage()
	{
		if($this->request->isPost())
		{
			Db::startTrans();
			$dataMain = $dataImage = $this->request->post();
			if(empty($dataMain['image_url']))
			{
				$this->error("请添加图片文件");
			}
			$dataImage['image_url'] = $dataMain['image_url'];
			unset($dataMain['adaptation']);
			unset($dataImage['material_type']);
			$dataMain['userid'] = ADMIN_ID;
			$dataImage = $this->dropColumns($dataImage);
			$res_img = MaterialImageModel::create($dataImage);
			$dataMain['material_id'] = $res_img['sid'];
			$dataMain['adsenseid'] = $this->_get_adsenseid($dataMain['adsenseid']);
			$res_main = $this->addAdMaterialMain($dataMain);
			if($res_img && $res_main)
			{
				Db::commit();
				return $this->success('添加成功', 'admin/admaterial/lists');
			} else {
				Db::rollback();
				return $this->error('添加失败');
			}
		}
		$this->assign('imageform', '');
		$adsite = $this->getSites();
		$this->assign('adsite', $adsite);
		return $this->fetch('imageform');
	}

	public function edit($id = 0, $material_id = 0)
	{
		$this->check_ads_active($id);
		// 提交修改
		if($this->request->isPost())
		{
			Db::startTrans();
			$dataMain = $dataImage = $this->request->post();
			if($_FILES['image']['size'] > 0)
			{
				$image  = $this->upload( 'image' );
				$path = upload2fileserver($image);
				$dataImage['image_url']      = $path;
			}
			$dataImage = $this->dropColumns($dataImage);
			unset($dataImage['id']);
			unset($dataImage['material_type']);
			$res_image = MaterialImageModel::update($dataImage, [ 'sid' => $material_id]);
			$dataMain['adsenseid'] = $this->_get_adsenseid($dataMain['adsenseid']);
			$res_main = $this->updateMaterialMain($dataMain);
			if($res_main && $res_image)
			{
				Db::commit();
				return $this->success('修改成功', 'admin/admaterial/lists');
			} else {
				Db::rollback();
				return $this->error('修改失败');
			}
		}
		$row = Admaterial::findJoin($id, 'material_image', 'i');
		$this->assign('edit', 1);
		$this->assign('imageform', $row);
		$adsite = $this->getSites();
		$this->assign('adsite', $adsite);
		return $this->fetch('imageform');
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

	/**
	 * 预览
	 */
	public function preview()
	{
		$id = input('id');
		$row = Admaterial::findJoin($id, 'material_image', 'i');
		$this->assign('data', $row);
		return $this->fetch('previewimage');
	}
}