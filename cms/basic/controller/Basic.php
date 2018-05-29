<?php
namespace cms\basic\controller;
use cms\common\controller\Common;
use cnfol\Tools;
use cnfol\unit\ArrayUnit;
use think\Db;
use think\Exception;
use think\Loader;
use think\Config;
use cms\common\model;
/**
 * 默认入口
 * @package cms\content\controller
 */
class basic extends Common
{
	public function index(){
		if ($this->request->isPost()){

			$qq = $this->request->param('qq');
			$email = $this->request->param('email');
			$tel = $this->request->param('tel');

			Db::table('ad_admin_config')->where(['id'=>54])->update(['value'=>$qq]);
			Db::table('ad_admin_config')->where(['id'=>55])->update(['value'=>$email]);
			Db::table('ad_admin_config')->where(['id'=>56])->update(['value'=>$tel]);
		}else{
			$data = Db::table('ad_admin_config')->where(array('id'=>array('in','53,54,55,56')))->field('value')->select();

			$qq = $data[1]['value'];
			$email = $data[2]['value'];
			$tel = $data[3]['value'];
		}
		

		$this->assign('qq',$qq);
		$this->assign('email',$email);
		$this->assign('tel',$tel);

		return $this->fetch();
	}

	public function baseprice(){
		if ($this->request->isPost()){
			$baseprice = $this->request->param('baseprice');
			Db::table('ad_admin_config')->where(['id'=>53])->update(['value'=>$baseprice]);
		}else{
			$baseprice = Db::table('ad_admin_config')->where(array('id'=>53))->value('value');
		}
		
		$this->assign('baseprice',$baseprice);

		return $this->fetch();
	}

}

/* End of file Index.php */
/* Location: ./app_cms/index/controller/Index.php */