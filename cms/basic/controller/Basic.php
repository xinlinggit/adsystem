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
			$upload_image_size = $this->request->param('upload_image_size');
			$upload_image_ext = $this->request->param('upload_image_ext');
			Db::table('ad_admin_config')->where(['id'=>54])->update(['value'=>$qq]);
			Db::table('ad_admin_config')->where(['id'=>55])->update(['value'=>$email]);
			Db::table('ad_admin_config')->where(['id'=>56])->update(['value'=>$tel]);
			Db::table('ad_admin_config')->where(['id'=>14])->update(['value'=>$upload_image_size]);
			Db::table('ad_admin_config')->where(['id'=>15])->update(['value'=>$upload_image_ext]);
		}else{
			$data = Db::table('ad_admin_config')->where(array('id'=>array('in','14,15,54,55,56')))->field('value')->select();
			$upload_image_size = $data[0]['value'];
			$upload_image_ext = $data[1]['value'];
			$qq = $data[2]['value'];
			$email = $data[3]['value'];
			$tel = $data[4]['value'];
			
		}
		

		$this->assign('qq',$qq);
		$this->assign('email',$email);
		$this->assign('tel',$tel);
		$this->assign('upload_image_size',$upload_image_size);
		$this->assign('upload_image_ext',$upload_image_ext);
		return $this->fetch();
	}

	public function baseprice(){
		if ($this->request->isPost()){
			$baseprice = $this->request->param('baseprice');
			$basecpc = $this->request->param('basecpc');
			Db::table('ad_admin_config')->where(['id'=>53])->update(['value'=>$baseprice]);
			Db::table('ad_admin_config')->where(['id'=>57])->update(['value'=>$basecpc]);
		}else{
			$baseprice = Db::table('ad_admin_config')->where(array('id'=>53))->value('value');
			$basecpc= Db::table('ad_admin_config')->where(array('id'=>57))->value('value');
		}
		
		$this->assign('baseprice',$baseprice);
		$this->assign('basecpc',$basecpc);
		return $this->fetch();
	}

}

/* End of file Index.php */
/* Location: ./app_cms/index/controller/Index.php */