<?php
namespace cms\common\widget;
/**
 * Widget分页类
 * @package cms\common\widget
 */
class Page extends Base
{
	public function render($page,$param = []){
		$this->view->assign('param',$param);
		$this->view->assign('page',$page);
		return $this->fetch('common@widget/page/render');
	}
}

/* End of file Page.php */
/* Location: ./app_cms/common/controller/Page.php */