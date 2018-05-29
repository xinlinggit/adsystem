<?php
namespace cms\common\widget;

use think\Loader;
use think\Request;
use think\Url;

/**
 * Widget列表处理
 * @package cms\common\widget
 */
class Table extends Base
{
	const operate_config = [
		'default' => [
			'title' => '',//标题
			'color' => '',//颜色
			'icon' => '',//图标
			'field' => '',//对应字段，默认status
		],
		'recommend' => [
			'title' => '推荐',
			'color' => 'btn-secondary',
			'icon' => 'Hui-iconfont-arrow1-top',
			'field' => 'recommend',
		],
		'unrecommend' => [
			'title' => '取消推荐',
			'color' => 'btn-warning',
			'icon' => 'Hui-iconfont-arrow1-bottom',
			'field' => 'recommend',
		],
		'off' => [
			'title' => '下架',
			'color' => 'btn-warning',
			'icon' => 'Hui-iconfont-xiajia',
		],
		'restore' => [
			'title' => '还原',
			'color' => 'btn-success',
			'icon' => 'Hui-iconfont-shangjia',
		],
		'audit' => [
			'title' => '审核通过',
			'color' => 'btn-success',
			'icon' => 'Hui-iconfont-key',
		],
		'refuse' => [
			'title' => '审核不通过',
			'color' => 'btn-warning',
			'icon' => 'Hui-iconfont-suoding',
		],
		'success' => [
			'title' => '成功',
			'color' => 'btn-success',
			'icon' => 'Hui-iconfont-xuanze',
		],
		'fail' => [
			'title' => '失败',
			'color' => 'btn-warning',
			'icon' => 'Hui-iconfont-close',
		],
		'pause' => [
			'title' => '暂停',
			'color' => 'btn-warning',
			'icon' => 'Hui-iconfont-shenhe-tingyong',
		],
		'open' => [
			'title' => '开启',
			'color' => 'btn-success',
			'icon' => 'Hui-iconfont-xuanze',
		],
		'delete' => [
			'title' => '删除',
			'color' => 'btn-danger',
			'icon' => 'Hui-iconfont-del3',
		],
		'destroy' => [
			'title' => '删除',
			'color' => 'btn-warning',
			'icon' => 'Hui-iconfont-del',
		],
		'edit' => [
			'title' => '编辑',
			'color' => 'btn-secondary',
			'icon' => 'Hui-iconfont-edit',
		],
	];

	/**
	 * 勾叉效果
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function switch_1($field, $row)
	{
		$html = '';
		if ($row->$field) {
			$button = '<button value="0" data-url="' . Url::build('operate_change_status', ['field' => $field]) . '" data-id="' . $row->id . '" data-confirm="1" class="js_operate" data-title="关闭此功能" title="点击关闭">%i%</button>';
			$icon = '<i class="f-20 btn-success Hui-iconfont Hui-iconfont-xuanze"></i>';
		} else {
			$button = '<button value="1" data-url="' . Url::build('operate_change_status', ['field' => $field]) . '" data-id="' . $row->id . '" data-confirm="1" class="js_operate" data-title="开启此功能" title="点击开启">%i%</button>';
			$icon = '<i class="f-20 btn-danger Hui-iconfont Hui-iconfont-close"></i>';
		}
		if ($field) {
			$html = str_replace('%i%', $icon, $button);
		} else {
			$html = $icon;
		}
		return $html;
	}

	/**
	 * 单条操作
	 *
	 * @param string $row
	 * @param string $field
	 * @param string $value
	 * @param string $title
	 * @param string $color
	 * @param string $icon
	 *
	 * @return string
	 * @internal param $value
	 */
	public function switch_2($row = '', $field = '', $value = '', $title = '', $color = '', $icon = '')
	{
		$html = '<button value="' . $value . '" class="btn size-MINI radius js_operate ' . $color . ' " data-id="' . $row->id . '" title="' . $title . '" data-title="' . $title . '" data-url="' . Url::build('operate_change_status', ['field' => $field]) . '" data-confirm="1">';
		$html .= '<i class=" Hui-iconfont ' . $icon . '"></i></button>';

		return $html;
	}

	/**
	 * 生成单条操作按钮
	 *
	 * @param string $id
	 *
	 * @param string $value
	 * @param string $type
	 *
	 * @return string
	 *
	 */
	public function single_button($id = '', $value = '', $type = '')
	{
		$config = self::operate_config[$value];
		$config['id'] = $id;
		switch ($type) {
			case 'model':
				$config['callback'] = 'layer_model';
				$config['url'] = Url::build(
					$this->request->module() . '/' . Loader::parseName($this->request->controller()) . '/' . 'model',
					['operate'=>$value]);
				break;
			case 'confirm':
				$config['callback'] = 'layer_confirm';
				$config['url'] = Url::build(
					$this->request->module() . '/' . Loader::parseName($this->request->controller()) . '/' . 'operate',
					['field'=>isset($config['field']) ? $config['field'] : 'status','value'=>$value]
				);
				break;
			default:
				return '';
		}
		$template = '<button value="" class="btn size-MINI js_operate %color%-outline " data-id="%id%"';
		$template .= ' title="%title%" data-title="%title%" data-url="%url%" data-callback="%callback%">';
		$template .= ' <i class=" Hui-iconfont %icon%"></i>';
		$template .= '</button>';
		foreach ($config as $k => $v){
			$template = str_replace('%' . $k . '%', $v, $template);
		}
		return $template;
	}

	/**
	 * 生成批量操作按钮
	 *
	 * @param string $value
	 *
	 * @param string $type
	 *
	 * @return string
	 *
	 */
	public function batch_button($value = '', $type = '')
	{
		$config = self::operate_config[$value];
		$config['id'] = '.js_super_table .js_checkbox:checked';
		switch ($type) {
			case 'model':
				$config['callback'] = 'layer_model';
				$config['url'] = Url::build(
					$this->request->module() . '/' . Loader::parseName($this->request->controller()) . '/' . 'model',
					['operate'=>$value]);
				break;
			case 'confirm':
				$config['callback'] = 'layer_confirm';
				$config['url'] = Url::build(
					$this->request->module() . '/' . Loader::parseName($this->request->controller()) . '/' . 'operate',
					['field'=>isset($config['field']) ? $config['field'] : 'status','value'=>$value]
				);
				break;
			default:
				return '';
		}
		$template = '<button value="" class="btn js_operate %color% " data-ids="%id%"';
		$template .= ' title="%title%" data-title="%title%" data-url="%url%" data-callback="%callback%">';
		$template .= ' <i class=" Hui-iconfont %icon%"></i>%title%';
		$template .= '</button>';
		foreach ($config as $k => $v){
			$template = str_replace('%' . $k . '%', $v, $template);
		}
		return $template;
	}

	/**
	 * 批量操作
	 *
	 * @param $field
	 * @param $value
	 * @param $title
	 * @param $color
	 * @param $icon
	 *
	 * @return string
	 * @internal param $value
	 */
	public function batch_switch_1($field = '', $value = '', $title = '', $color = '', $icon = '')
	{
		$html = '<button value="' . $value . '" class="btn ' . $color . ' js_operate" data-ids=".js_super_table .js_checkbox:checked" title="' . $title . '" data-title="' . $title . '" data-url="' . Url::build('operate_change_status', ['field' => $field]) . '" data-confirm="1">';
		$html .= '<i class="Hui-iconfont ' . $icon . '"></i> ' . $title . '</button>';

		return $html;
	}

}

/* End of file Table.php */
/* Location: ./app_cms/common/controller/Table.php */