<?php
namespace cms\common\widget;

/**
 * Widget表单处理
 * @package cms\common\widget
 */
class Form extends Base
{
	public function radio_simple($field, $title, $value)
	{
		$html = '';
		$html .= '<label class="form-label col-xs-2 col-sm-2 text-r">' . $title . '：</label>';
		$html .= '<div class="col-xs-4 col-sm-4">';
		$html .= '<div class="radio-box">';
		$html .= '<input id="radio_' . $field . '_1" type="radio" ' . ($value ? 'checked="checked"' : '') . ' title="' . $title . '" class="input-text" name="' . $field . '" value="1" />';
		$html .= '<label for="radio_' . $field . '_1">是</label>';
		$html .= '</div>';
		$html .= '<div class="radio-box">';
		$html .= '<input id="radio_' . $field . '_0" type="radio" ' . (!$value ? 'checked="checked"' : '') . ' title="' . $title . '" class="input-text" name="' . $field . '" value="0" />';
		$html .= '<label for="radio_' . $field . '_0">否</label>';
		$html .= '</div>';
		$html .= '</div>';
		return $html;
	}
}

/* End of file Form.php */
/* Location: ./app_cms/common/controller/Form.php */