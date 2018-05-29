<?php
/**
 * Created by PhpStorm.
 * User: hudy@cnfol.net
 * Date: 2018/3/14 0014
 * Time: 15:51
 */

namespace app\admin\controller;
use app\admin\model\Admaterial as MaterialTextModel;
use app\admin\model\Admaterial4main;
use think\db;

/**
 * 广告素材库
 * @package app\admin\controller
 */

class Admaterial extends Admaterialcommon
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

	protected function _loadFontStyle($data)
	{
		$data['font_weight'] = isset($data['font_weight']) ? $data['font_weight'] : 'normal';
		$data['font_style'] = isset($data['font_style']) ? $data['font_style'] : 'normal';
		$data['font_decoration'] = isset($data['font_decoration']) ? $data['font_decoration'] : 'none';
		$data['font_color'] = $data['font_color'] ?: '#000000';
		$data['hover_font_weight'] = isset($data['hover_font_weight']) ? $data['hover_font_weight'] : $data['font_weight'];
		$data['hover_font_style'] = isset($data['hover_font_style']) ? $data['hover_font_style'] : $data['font_style'];
		$data['hover_font_decoration'] = isset($data['hover_font_decoration']) ? $data['hover_font_decoration'] : $data['font_decoration'];
		$data['hover_font_color'] = isset($data['hover_font_color']) ? $data['hover_font_color'] : $data['font_color'];
		return $data;
	}

	/**
	 * 新增广告素材 - 文字
	 */
	public function add()
	{
		if ($this->request->isPost()) {
			Db::startTrans();
			$dataMain = $data = $this->request->post();
			$data = $this->_loadFontStyle($data);
			$data = $this->dropColumns($data);
			$res = MaterialTextModel::create($data);
			$dataMain['material_id'] = $res->sid;
			$dataMain['userid'] = ADMIN_ID;
			$dataMain['adsenseid'] = $this->_get_adsenseid($dataMain['adsenseid']);
			$main_status = $this->addAdMaterialMain($dataMain);
			if ($res && $main_status) {
				Db::commit();
				return $this->success('添加成功', 'admin/admaterial/lists');
			} else {
				Db::rollback();
				return $this->error('添加失败');
			}
		}

		$this->assign('edit', 0);
		$this->assign('textform', '');
		$this->assign('flashform', '');
		$this->assign('imageform', '');
		$this->assign('infoform', '');
		$adsite = $this->getSites();
		$this->assign('adsite', $adsite);
		return $this->fetch('frameform');
	}

	public function edit($id = 0, $material_id = 0)
	{
		$this->check_ads_active($id);
		// 提交修改
		if($this->request->isPost())
		{
			$dataMain = $dataText = $this->request->post();
			unset($dataText['sid']);
			$dataMain['adsenseid'] = $this->_get_adsenseid($dataMain['adsenseid']);
			$this->updateMaterialMain($dataMain);
			unset($dataText['id']);
			unset($dataText['material_type']);
			$dataText = $this->dropColumns($dataText);
			$dataText['font_decoration'] = isset($dataText['font_decoration']) ? $dataText['font_decoration'] : 'none';
			$dataText['hover_font_decoration'] = isset($dataText['hover_font_decoration']) ? $dataText['hover_font_decoration'] : 'none';
			$dataText['font_weight'] = isset($dataText['font_weight']) ? $dataText['font_weight'] : 'normal';
			$dataText['hover_font_weight'] = isset($dataText['hover_font_weight']) ? $dataText['hover_font_weight'] : 'normal';
			$dataText['font_style'] = isset($dataText['font_style']) ? $dataText['font_style'] : 'normal';
			$dataText['hover_font_style'] = isset($dataText['hover_font_style']) ? $dataText['hover_font_style'] : 'normal';
			$res = MaterialTextModel::where([ 'sid' => $material_id])->update($dataText);
			if( $res === false )
			{
				return $this->error('修改失败');
			}
			else{
				return $this->success('修改成功', 'lists');
			}
		}
		$row = Db::table('adserver.material_main')
			->alias('m')
			->where(['m.id' => $id])
			->join('adserver.material_text t', 'm.material_id = t.sid ', 'LEFT')
			->find();
		$this->assign('edit', 1);
		$this->assign('textform', $row);
		$adsite = $this->getSites();
		$this->assign('adsite', $adsite);
		return $this->fetch('textform');
	}

	/**
	 * 预览
	 */
	public function preview()
	{
		$id = input('id');
		$data = MaterialTextModel::get([ 'sid' => $id])->toArray();
		$this->assign('data', $data);
		return $this->fetch('previewtext');
	}

	/**
	 * @return mixed|void
	 */
	public function del($id = 0, $material_id = 0)
	{
		$this->check_ads_active($id);
		$id   = input('id');
		$res = $this->delAdMaterialMain($id);
		if ($res === false) {
			return $this->error('删除失败');
		}
		return $this->success('删除成功');
	}

	public function delall()
	{
		$ids   = input('param.ids/a') ? input('param.ids/a') : input('param.id/a');
		$map = [];
		$map['id'] = ['in', $ids];

		$this->check_ads_active($ids);

		$res = Admaterial4main::update(['status' => -1], $map);
		if ($res === false) {
			return $this->error('删除失败');
		}
		return $this->success('删除成功');
	}
}






























