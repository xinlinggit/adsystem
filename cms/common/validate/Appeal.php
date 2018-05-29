<?php
namespace cms\common\validate;

/**
 * 申诉验证器
 */
class Appeal extends Base
{
	/**
	 * @var array 验证场景
	 */
	protected $scene = [
		'refuse' => ['delete_rule','delete_reason'],
	];
}

/* End of file Appeal.php */
/* Location: ./app_cms/common/validate/Appeal.php */