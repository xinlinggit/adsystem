<?php
namespace cms\adsense\controller;
use think\response;
/**
 * 广告位管理基类
 */
class Base extends \cms\common\controller\Common
{
	/**
	 * 初始化函数
	 */
	protected function _init()
	{
	}

	public function upload2($type = 'image'){

		// 获取表单上传文件 例如上传了001.jpg
		$file = request()->file($type);

		// 移动到框架应用根目录/public/uploads/material 目录下
		if($file){
			$info = $file->move(ROOT_PATH . 'public' . DS . 'upload' . DS . 'adpic');
			if($info){
				// 成功上传后 获取上传信息
				return 'upload' . DS . 'adpic' . DS . $info->getSaveName();
			}else{
				// TODO: 上传失败
			}
		}
	}
}

/* End of file Base.php */
/* Location: ./app_cms/user/controller/Base.php */