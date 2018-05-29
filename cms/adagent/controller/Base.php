<?php
namespace cms\adagent\controller;
use think\response;
use think\Db;
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

	/**
	 * 修改认证的状态
	 */
	public function change_auth_status()
	{
		$param = $this->request->param();
		if(!$param['id'] && ! in_array($param['status'], [-1, 1, 2])){
			return $this->api_error('请检查参数');
		}
		Db::startTrans();
		$result = Db::table('license_auth')->where(['id' => $param['id']])->update(['status' =>$param['status']]);
		$result_current = Db::table('license_auth_current')->where(['pid' => $param['id']])->update(['status' =>$param['status']]);
		if(($result !== false ) && ($result_current !== false))
		{
			Db::commit();
			return $this->api_success();
		} else {
			Db::rollback();
			return $this->api_error($this->model->getError());
		}
	}
}

/* End of file Base.php */
/* Location: ./app_cms/user/controller/Base.php */