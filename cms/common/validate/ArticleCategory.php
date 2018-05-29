<?php
namespace cms\common\validate;

/**
 * 文章分类验证器
 */
class ArticleCategory extends Base
{
	/**
	 * @var array 验证规则
	 */
	protected $rule = [
		['id', 'require|gt:0', 'ID必须为正整数|ID必须为正整数'],
		['title', 'require', '分类必须'],
		['sort', 'integer|egt:0', '排序值必须为正整数|排序值必须为正整数'],
	];

	/**
	 * @var array 验证场景
	 */
	protected $scene = [
		'add' => ['title','sort'],
		'update' => ['title','sort'],
	];
}

/* End of file ArticleCategory.php */
/* Location: ./app_cms/common/validate/ArticleCategory.php */