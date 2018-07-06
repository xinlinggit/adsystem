<?php
/**
 * Created by PhpStorm.
 * User: hudy@cnfol.net
 * Date: 2018/3/16 0016
 * Time: 9:41
 */

namespace app\admin\controller;

use think\db;


class Authentication extends Admin {

	public function index()
	{
		if (cookie('hisi_iframe')) {
			$this->view->engine->layout(false);
			return $this->fetch('iframe');
		} else {
			return $this->fetch();
		}
	}

	public function upload($type = 'image'){
		// 获取表单上传文件 例如上传了001.jpg
		$file = request()->file($type);

		// 移动到框架应用根目录/public/uploads/material 目录下
		if($file){
			$info = $file->move(ROOT_PATH . 'public' . DS . 'upload' . DS . 'material');
			if($info){
				// 成功上传后 获取上传信息
				return 'upload' . DS . 'material' . DS . $info->getSaveName();
			}else{
				// TODO: 上传失败
			}
		}
	}

	/**
	 * 上传认证资料 - 图片
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
			return json_encode(array('url' => $status));
		}
	}

	/**
	 * 上传认证资料 - 资料存储路径
	 */
	public function add()
	{
		$data                = input();
		$row = $this->_get_authentication();
		if($row['status'] == 1 || $row['status'] == 2)
		{
			return '您有资质在审核或者审核已通过，请在认证状态查看。';
		} else if($row['status'] == -1) {
			if ( $this->request->isPost() ) {
				// 更新认证资料
				$this->_check_data($data);
				$data['uid']         = ADMIN_ID;
				$data['create_time'] = date( 'Y-m-d H:i:s', time() );
				$data['status']      = 2; // 等待审核
				unset( $data['image'] );
				$res = Db::table( 'license_auth' )->where('uid = ' . ADMIN_ID)->update($data);
				if ( $res !== false ) {
					$this->success( '上传成功');
				} else {
					$this->error( '上传失败' );
				}
			}
			return $this->fetch();
		} else {
			if ( $this->request->isPost() ) {
				$this->_check_data($data);
				$data['uid']         = ADMIN_ID;
				$data['create_time'] = date( 'Y-m-d H:i:s', time() );
				$data['status']      = 2; // 等待审核
				unset( $data['image'] );
				$res = Db::table( 'license_auth' )->insertGetId( $data );
				if ( $res !== false ) {
					$this->success( '上传成功');
				} else {
					$this->error( '上传失败' );
				}
			}
			return $this->fetch();
		}
	}

	protected function _check_data($data){
		if(empty($data['business_license_front_url']) || empty($data['ID_card_end_url']) || empty($data['ID_card_front_url']))
		{
			$this->error('请检查填写内容');
		}
	}

	private function _get_authentication()
	{
		$map['uid'] = ADMIN_ID;
		$row = Db::table('license_auth')->where($map)->find();
		return $row;
	}

	/**
	 * 列出请求记录
	 */
	public function lists()
	{
		$row = Db::table('license_auth')->where(['uid' => ADMIN_ID])->order('id desc')->paginate();
		$pages = $row->render();
		$this->assign('pages', $pages);
		$this->assign('data_list', $row);
		return $this->fetch();
	}

	public function check($id = 0)
	{
		$data = Db::table('license_auth')->where(['uid' => ADMIN_ID, 'id' => $id])->find();
		if(! $data)
		{
			$this->error('资料不存在');
		}
		$this->assign('data', $data);
		return $this->fetch();
	}

}