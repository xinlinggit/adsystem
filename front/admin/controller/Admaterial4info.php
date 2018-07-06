<?php
/**
 * Created by PhpStorm.
 * User: hudy@cnfol.net
 * Date: 2018/3/14 0014
 * Time: 15:51
 */

namespace app\admin\controller;
use app\admin\model\Admaterial as MaterialText;
use app\admin\model\Admaterial4info as MaterialInfoModel;
use app\admin\model\Admaterial4main;
use app\admin\model\Admaterial4image;
use think\db;
use think\Request;


/**
 * 信息流
 * @package app\admin\controller
 */

class Admaterial4info extends Admaterialcommon
{

	protected $title_len = 16;
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
			$dataMain = $dataInfo = $this->request->post();

			if(mb_strlen($dataMain['image_description'], 'UTF-8') > $this->title_len)
			{
				return json_encode(['code' => 0, 'msg' => '素材标题请控制在 ' . $this->title_len . ' 字之内']);exit;
			}
			Db::startTrans();
			if(empty($dataMain['image_url']))
			{
				return $this->error("请添加图片文件");
			}
			$dataInfo['image_url'] = $dataMain['image_url'];
			$dataMain['userid'] = ADMIN_ID;
			$dataInfo = $this->dropColumns($dataInfo);
			$res_img = MaterialInfoModel::create($dataInfo, true);
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

	protected function check_file_extension(){
		$file = $this->request->file('image');
		$fileinfo = $file->getInfo();
		$allow_file_extension = Db::name('admin_config')->where('id = 15')->value('value');
		$filename_arr = explode('.', $fileinfo['name']);
		$extension = end($filename_arr);
		$allow_file_extension_arr = explode(',', $allow_file_extension);
		if(! in_array($extension, $allow_file_extension_arr)) {
			return $this->api_error('请检查上传文件的格式');
		}
	}

	/**
	 * 处理图片的上传
	 * @return string
	 */
	public function doImg()
	{
		if($this->request->isPost())
		{
			$this->check_file_extension();
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
			$res = $this->upload('image');
			if($res['code'] == 1){
				$path = $res['data']['path'];
			} else if($res['code'] == 0)
			{
				return json_encode(['code' => 0, 'msg' => $res['msg']]);
			}
			$status = upload2fileserver($path);
			if($status == -1){
				error_log(0, '文件上传失败');
			} else {

			}
			return json(array('code' => 1, 'url' => $status));
		}
	}

	/**
	 * 弹窗 - 添加图片
	 */
	public function pop_img()
	{
		$callback = input('param.callback/s');
		$width = input('width');
		$height = input('height');
		$adsite = input('adsite');
		$this->assign('callback', $callback);
		$this->assign('action', url('admin/admaterial4info/addimg'));
		$this->assign('width', $width);
		$this->assign('height', $height);
		$this->assign('adsite', $adsite);
		$this->view->engine->layout(false);
		return $this->fetch();
	}

	/**
	 * 弹窗 - 编辑图片
	 * @return mixed|string
	 * @throws \think\exception\DbException
	 * @throws db\exception\DataNotFoundException
	 * @throws db\exception\ModelNotFoundException
	 */
	public function pop_edit_img(){
		if($this->request->isPost())
		{
			$dataMain = $dataImg = $this->request->post();
			Db::startTrans();
			$dataMain['userid'] = ADMIN_ID;
			$MaterialImg = new Admaterial4image();
			unset($dataImg['type']);
			unset($dataImg['image']);
			$old  = Db::table('material_image')->where('sid = '.$dataImg['sid'])->find();
			$res_img = $MaterialImg->allowField(true)->isUpdate(true)->save($dataImg);
			$dataMain['material_id'] = $res_img['sid'];
			$dataMain['adsenseid'] = 0;
			$Admaterial4main = new Admaterial4main();
			unset($dataMain['material_content']);
			unset($dataMain['click_url']);
			unset($dataMain['open_target']);
			unset($dataMain['sid']);
			unset($dataMain['image']);
			unset($dataMain['image_url']);
			$dataMain['material_id'] = $dataImg['sid'];
			// 仅当修改图片地址、点击连接的时候需要重新审核
			if($old['image_url'] != $dataImg['image_url'] || $old['click_url'] != $dataImg['click_url']){
				$dataMain['status'] = 5;
			}
			$res_main      = $Admaterial4main->allowField(true)->where('id = '.$dataMain['id'])->update( $dataMain );
			$res = Db::table('material_main')->where('id =' . $dataMain['id'])->find();
			if($res_img !== false && $res_main !== false && $res !== false)
			{
				Db::commit();
				return json_encode(['code' => 1, 'msg' => '修改成功', 'data' => ['id' => $res['id'], 'material_title' => $res['material_title']]]);
			} else {
				Db::rollback();
				return json_encode(['code' => 0, 'msg' => '修改失败']);
			}
		}
		$material_id = $this->request->param('material_id');
		$materialform = Db::table('material_main main')
		                  ->join('adserver.material_image image', 'main.material_id = image.sid', 'left')
		                  ->where('main.id = ' . $material_id)
		                  ->find();
		$this->assign('callback', 'update_func');
		$this->assign('adsite', '');
		$this->assign('materialform', $materialform);
		return $this->fetch('pop_img_edit');
	}

	/**
	 * 添加图片广告素材
	 * @return mixed|string|void
	 */
	public function addimg()
	{
		if($this->request->isPost())
		{
			$dataMain = $dataInfo = $this->request->post();
			Db::startTrans();
			if(empty($dataMain['image_url']))
			{
				return $this->error("请添加图片文件");
			}
			$dataInfo['image_url'] = $dataMain['image_url'];
			unset($dataMain['adaptation']);
			unset($dataMain['image']);
			unset($dataInfo['image']);
			$dataMain['userid'] = ADMIN_ID;
			$dataInfo = $this->dropColumns($dataInfo);
			$res_img = Admaterial4image::create($dataInfo);
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
	 * 弹窗 - 添加文字
	 */
	public function text_pop()
	{
		$callback = input('param.callback/s');
		$adsite = input('adsite');
		$this->assign('callback', $callback);
		$this->assign('action', url('admin/admaterial4info/addimage'));
		$this->assign('adsite', $adsite);
		$this->view->engine->layout(false);
		return $this->fetch();
	}

	/**
	 * action - 添加文字素材
	 */
	public function addtext()
	{
		if($this->request->isPost())
		{
			$dataMain = $dataInfo = $this->request->post();

			if(mb_strlen($dataMain['material_content'], 'UTF-8') > $this->title_len)
			{
				return json_encode(['code' => 0, 'msg' => '素材标题请控制在 ' . $this->title_len . ' 字之内']);exit;
			}
			Db::startTrans();
			$dataMain['userid'] = ADMIN_ID;
			$res_img = MaterialText::create($dataInfo);
			$dataMain['material_id'] = $res_img['sid'];
			$dataMain['adsenseid'] = 0;
			unset($dataMain['image']);
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
	}

	/**
	 * action - 编辑更新文字素材
	 * @return mixed|string
	 */
	public function pop_edit_text()
	{
		if($this->request->isPost())
		{
			$dataMain = $dataText = $this->request->post();
			if(mb_strlen($dataMain['material_content'], 'UTF-8') > $this->title_len)
			{
				return json_encode(['code' => 0, 'msg' => '素材标题请控制在 ' . $this->title_len . ' 字之内']);exit;
			}
			Db::startTrans();
			$dataMain['userid'] = ADMIN_ID;
			$MaterialText = new MaterialText();
			unset($dataText['type']);
			$old  = Db::table('material_text')->where('sid = ' . $dataText['sid'])->find();
			$res_img = $MaterialText->allowField(true)->isUpdate(true)->save($dataText);
			$dataMain['material_id'] = $res_img['sid'];
			$dataMain['adsenseid'] = 0;
			$Admaterial4main = new Admaterial4main();
			unset($dataMain['material_content']);
			unset($dataMain['click_url']);
			unset($dataMain['open_target']);
			unset($dataMain['sid']);
			unset($dataMain['image']);
			$dataMain['material_id'] = $dataText['sid'];
			// 仅当修改文案和点击连接的时候需要重新审核
			if($old['material_content'] != $dataText['material_content'] || $old['click_url'] != $dataText['click_url']){
				$dataMain['status'] = 5;
			}
			$res_main      = $Admaterial4main->allowField(true)->where('id = '.$dataMain['id'])->update( $dataMain );
			$res = Db::table('material_main')->where('id =' . $dataMain['id'])->find();
			if($res_img !== false && $res_main !== false && $res !== false)
			{
				Db::commit();
				return json_encode(['code' => 1, 'msg' => '修改成功', 'data' => ['id' => $res['id'], 'material_title' => $res['material_title']]]);
			} else {
				Db::rollback();
				return json_encode(['code' => 0, 'msg' => '修改失败']);
			}
		}
		$material_id = $this->request->param('material_id');
		$materialform = Db::table('material_main main')
		                  ->join('adserver.material_text text', 'main.material_id = text.sid', 'left')
		                  ->where('main.id = ' . $material_id)
		                  ->find();
		$this->assign('callback', 'update_func');
		$this->assign('adsite', '');
		$this->assign('materialform', $materialform);
		return $this->fetch('edit_text_pop');
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
			if(mb_strlen($dataMain['image_description'], 'UTF-8') > $this->title_len)
			{
				return json_encode(['code' => 0, 'msg' => '素材标题请控制在 ' . $this->title_len . ' 字之内']);exit;
			}
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
			if ( $res_img !== false && $res_main !== false && $res !== false) {
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