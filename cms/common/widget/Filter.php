<?php
namespace cms\common\widget;
use think\Request;
use think\Url;

/**
 * Widget筛选框处理
 * @package cms\common\widget
 */
class Filter extends Base
{
	/**
	 * 生成表单结构
	 *
	 * @param array $list
	 *
	 * @return string
	 */
	public function form($list = [])
	{
		array_unshift($list,widget('common/filter/input', ['编号', 'id']));
		$html = '<form class="form form-horizontal" action="">';
		/*行模版*/
		$column = '<div class="row cl">%html%</div>';
		/*临时内容*/
		$temp = '';
		/*列计数*/
		$col = 0;
		/*遍历结构数组*/
		foreach ($list as $row) {
			/*没有列标识就跳过*/
			if(!isset($row['col'])) continue;
			/*一行超过12列就换行*/
			if($col + $row['col'] > 12){
				$html .= str_replace('%html%',$temp,$column);
				$temp = '';
				$col = 0;
			}
			$col += $row['col'];
			$temp .= $row['html'];
		}
		/*剩余列 + 提交按钮布局*/
		switch(1){
			case $col == 0:
				$temp = $this->empty_col(10) . $this->submit();
				$html .= str_replace('%html%',$temp,$column);
				break;
			case $col <=10:
				$temp .= $this->empty_col(10 - $col) . $this->submit();
				$html .= str_replace('%html%',$temp,$column);
				break;
			default:
				$html .= str_replace('%html%',$temp,$column);
				$temp = $this->empty_col(10) . $this->submit();
				$html .= str_replace('%html%',$temp,$column);
		}
		$html .= '</div>';
		$html .= '</form>';
		return $html;

	}


	/**
	 * 生成表单结构
	 *
	 * @param array $list
	 *
	 * @return string
	 */
	public function formWithoutId($list = [])
	{
		$html = '<form class="form form-horizontal" action="">';
		/*行模版*/
		$column = '<div class="row cl">%html%</div>';
		/*临时内容*/
		$temp = '';
		/*列计数*/
		$col = 0;
		/*遍历结构数组*/
		foreach ($list as $row) {
			/*没有列标识就跳过*/
			if(!isset($row['col'])) continue;
			/*一行超过12列就换行*/
			if($col + $row['col'] > 12){
				$html .= str_replace('%html%',$temp,$column);
				$temp = '';
				$col = 0;
			}
			$col += $row['col'];
			$temp .= $row['html'];
		}
		/*剩余列 + 提交按钮布局*/
		switch(1){
			case $col == 0:
				$temp = $this->empty_col(10) . $this->submit();
				$html .= str_replace('%html%',$temp,$column);
				break;
			case $col <=10:
				$temp .= $this->empty_col(10 - $col) . $this->submit();
				$html .= str_replace('%html%',$temp,$column);
				break;
			default:
				$html .= str_replace('%html%',$temp,$column);
				$temp = $this->empty_col(10) . $this->submit();
				$html .= str_replace('%html%',$temp,$column);
		}
		$html .= '</div>';
		$html .= '</form>';
		return $html;

	}

	/**
	 * 空列
	 * @return string
	 */
	public function empty_col($col = 1){
		$html = '<div class="col-xs-' . $col . ' col-sm-' . $col . '"></div>';
		return $html;
	}

	/**
	 * 提交按钮
	 * @return string
	 */
	private function submit(){
		$html = '';
		$html .= '<div class="col-xs-1 col-sm-1 text-r">';
		$html .= '<button class="btn btn-success" type="submit"><i class="Hui-iconfont Hui-iconfont-search2"></i>查询</button>';
		$html .= '</div>';
		$html .= '<div class="col-xs-1 col-sm-1 text-c">';
		$html .= '<a href="' . Url::build('/'.Request::instance()->path()) . '" class="btn btn-default">重置</a>';
		$html .= '</div>';
		return $html;
	}

	/**
	 * 生成输入框结构
	 *
	 * @param string $title
	 * @param string $field
	 * @param string $current
	 *
	 * @return array ['col'=>列数,'html'=>内容]
	 */
	public function input($title = '', $field = '', $current = '')
	{
		$current = $current ? $current : $this->request->param($field);
		$html = '';
		$html .= '<label class="form-label col-xs-1 col-sm-1">' . $title . '：</label>';
		$html .= '<div class="col-xs-2 col-sm-2">';
		$html .= '<input type="text" class="input-text" name="' . $field . '" value="' . $current . '" placeholder="' . $title . '" />';
		$html .= '</div>';
		return ['col'=>3,'html'=>$html];
	}

	/**
	 * 生成下拉框结构
	 *
	 * @param string $title
	 * @param string $field
	 * @param string $option
	 *
	 * @return array ['col'=>列数,'html'=>内容]
	 */
	public function select($title = '', $field = '', $option = '')
	{
		$html = '<label class="form-label col-xs-1 col-sm-1">' . $title . '：</label>';
		$html .= '<div class="col-xs-2 col-sm-2">';
		$html .= '<span class="select-box">';
		$html .= '<select class="select" name="' . $field . '">';
		$html .= '<option value="">全部</option>';
		$html .= $option;
		$html .= '</select>';
		$html .= '</span>';
		$html .= '</div>';
		return ['col'=>3,'html'=>$html];
	}

	/**
	 * 生成下拉框结构
	 *
	 * @param string $title 标题
	 * @param string $field 域
	 * @param string $option <option> 内容
	 * @param string $id id 用于操作 dom
	 * @param string $ev 事件监听
	 *
	 * @return array ['col'=>列数,'html'=>内容]
	 */
	public function ex_select($title = '', $field = '', $option = '', $id = '', $ev = '')
	{
		$html = '<label class="form-label col-xs-1 col-sm-1">' . $title . '：</label>';
		$html .= '<div class="col-xs-2 col-sm-2">';
		$html .= '<span class="select-box">';
		$html .= '<select id="'. $id .'" class="select" name="' . $field . '"'." $ev ".'>';
		$html .= '<option value="">全部</option>';
		$html .= $option;
		$html .= '</select>';
		$html .= '</span>';
		$html .= '</div>';
		return ['col'=>3,'html'=>$html];
	}

	/**
	 * 生成日期框结构
	 *
	 * @param string $title
	 * @param string $field
	 *
	 * @return array ['col'=>列数,'html'=>内容]
	 */
	public function date_range($title = '', $field = '')
	{
		$field_start = $field . '_start';
		$field_end = $field . '_end';
		$start = $this->request->param($field . '_start');
		$end = $this->request->param($field . '_end');
		if (is_array($title)) {
			$title_0 = $title[0];
			$title_1 = $title[1];
		} else {
			$title_0 = $title;
			$title_1 = '至';
		}
		$html = '';
		$html .= '<div class="col-xs-4 col-sm-4">';
		$html .= '<label class="form-label col-xs-3 col-sm-3">' . $title_0 . '：</label>';
		$html .= '<div class="col-xs-4 col-sm-4">';
		$html .= '<input type="text" name="' . $field_start . '" id="' . $field_start . '" class="input-text Wdate" value="' . $start . '"';
		//$html .= ' onfocus="WdatePicker({ maxDate:\'#F{ $dp.$D(\\\'' . $field_end . '\\\')}\', dateFmt:\'yyyy-MM-dd\' })">';
		$html .= ' onclick="showCalendar(this)">';
		//<input type="text" class="InpDate" onclick="showCalendar(this)" name="start_time" id="start_time" value="">
		$html .= '</div>';
		$html .= '<label class="form-label col-xs-1 col-sm-1">' . $title_1 . '：</label>';
		$html .= '<div class="col-xs-4 col-sm-4">';
		$html .= '<input type="text" name="' . $field_end . '" id="' . $field_end . '" class="input-text Wdate" value="' . $end . '"';
		//$html .= ' onfocus="WdatePicker({ minDate:\'#F{ $dp.$D(\\\'' . $field_start . '\\\')}\', dateFmt:\'yyyy-MM-dd\' })">';
		$html .= ' onclick="showCalendar(this)">';
		$html .= '</div>';
		$html .= '</div>';
		return ['col'=>4,'html'=>$html];
	}

	/**
	 * 生成日期框结构
	 *
	 * @param string $title
	 * @param string $field
	 *
	 * @return array ['col'=>列数,'html'=>内容]
	 */
	public function ex_date_range($title = '', $field = '')
	{
		$field_start = 'b' . $field;
		$field_end = 'e' . $field;
		$start = $this->request->param($field_start);
		$end = $this->request->param($field_end);
		if (is_array($title)) {
			$title_0 = $title[0];
			$title_1 = $title[1];
		} else {
			$title_0 = $title;
			$title_1 = '至';
		}
		$html = '';
		$html .= '<div class="col-xs-4 col-sm-4">';
		$html .= '<label class="form-label col-xs-3 col-sm-3">' . $title_0 . '：</label>';
		$html .= '<div class="col-xs-4 col-sm-4">';
		$html .= '<input type="text" name="' . $field_start . '" id="' . $field_start . '" class="input-text Wdate" value="' . $start . '"';
		//$html .= ' onfocus="WdatePicker({ maxDate:\'#F{ $dp.$D(\\\'' . $field_end . '\\\')}\', dateFmt:\'yyyy-MM-dd\' })">';
		$html .= ' onclick="showCalendar(this)">';
		//<input type="text" class="InpDate" onclick="showCalendar(this)" name="start_time" id="start_time" value="">
		$html .= '</div>';
		$html .= '<label class="form-label col-xs-1 col-sm-1">' . $title_1 . '：</label>';
		$html .= '<div class="col-xs-4 col-sm-4">';
		$html .= '<input type="text" name="' . $field_end . '" id="' . $field_end . '" class="input-text Wdate" value="' . $end . '"';
		//$html .= ' onfocus="WdatePicker({ minDate:\'#F{ $dp.$D(\\\'' . $field_start . '\\\')}\', dateFmt:\'yyyy-MM-dd\' })">';
		$html .= ' onclick="showCalendar(this)">';
		$html .= '</div>';
		$html .= '</div>';
		return ['col'=>4,'html'=>$html];
	}

	public function date_range1($title = '', $field = '')
	{
		$field_start = $field . '_start';
		$field_end = $field . '_end';
		$start = $this->request->param($field . '_start');
		$end = $this->request->param($field . '_end');
		if (is_array($title)) {
			$title_0 = $title[0];
			$title_1 = $title[1];
		} else {
			$title_0 = $title;
			$title_1 = '至';
		}
		$html = '<label class="form-label col-xs-1 col-sm-1">' . $title_0 . '：</label>';
		$html .= '<div class="col-xs-2 col-sm-2">';
		$html .= '<input type="text" name="' . $field_start . '" id="' . $field_start . '" class="input-text Wdate" value="' . $start . '"';
		$html .= ' onfocus="WdatePicker({ maxDate:\'#F{ $dp.$D(\\\'' . $field_end . '\\\')}\', dateFmt:\'yyyy-MM-dd\' })">';
		$html .= '</div>';
		$html .= '<label class="form-label col-xs-1 col-sm-1">' . $title_1 . '：</label>';
		$html .= '<div class="col-xs-2 col-sm-2">';
		$html .= '<input type="text" name="' . $field_end . '" id="' . $field_end . '" class="input-text Wdate" value="' . $end . '"';
		$html .= ' onfocus="WdatePicker({ minDate:\'#F{ $dp.$D(\\\'' . $field_start . '\\\')}\', dateFmt:\'yyyy-MM-dd\' })">';
		$html .= '</div>';
		return $html;
	}
}

/* End of file Filter.php */
/* Location: ./app_cms/common/controller/Filter.php */