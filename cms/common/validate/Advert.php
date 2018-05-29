<?php
namespace cms\common\validate;

/**
 * 广告验证器
 */
class Advert extends Base
{
	/**
	 * @var array 验证规则
	 */
	protected $rule = [
		['id', 'require|gt:0', 'ID必须为正整数|ID必须为正整数'],
		['title', 'require', '分类必须'],
		['sort', 'integer|egt:0', '排序值必须为正整数|排序值必须为正整数'],
		['delete_type', 'integer|egt:0', '类型必须为正整数|类型必须为正整数'],
		['delete_rule', 'integer|egt:0', '规则必须为正整数|规则必须为正整数'],
		['delete_reason', 'require', '请填写原因'],
	];

	/**
	 * @var array 验证场景
	 */
	protected $scene = [
		'delete' => ['delete_type','delete_rule','delete_reason'],
	];
}

/* End of file Advert.php */
/* Location: ./app_cms/common/validate/Advert.php */