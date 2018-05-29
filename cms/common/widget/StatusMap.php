<?php
namespace cms\common\widget;

/**
 * Widget状态处理
 * @package cms\common\widget
 */
class StatusMap extends Base
{
	public function select($table = '',$field = '',$current = ''){
		$status_map = model('StatusMap')->get_all();
		$current = $current ? $current : $this->request->param($field);
		$html = '';
		if(!isset($status_map[$table])){
			return '';
		}
		foreach ($status_map[$table][$field] as $key => $value){
			$select = (strlen($current) && $key == $current) ? ' selected="selected" ' : '';
			$html .= '<option value="' . $key . '" ' . $select . '>' . $value['text'] . '</option>';
		}
		return $html;
	}

	public function simple_select($data,$current){

	}
}

/* End of file StatusMap.php */
/* Location: ./app_cms/common/controller/StatusMap.php */