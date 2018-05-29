<?php
namespace cms\index\controller;
use cms\common\controller\Common;

/**
 * 默认入口
 * @package cms\content\controller
 */
class Index extends Common
{
	public function index(){
		return $this->display('敬请期待');
	}
}

/* End of file Index.php */
/* Location: ./app_cms/index/controller/Index.php */