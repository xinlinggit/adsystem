<?php
namespace cms\common\validate;

/**
 * 菜单验证器
 */
class Menu extends Base
{
	/**
	 * @var array 验证规则
	 */
	protected $rule = [
		['id', 'require|gt:0', 'ID必须为正整数|ID必须为正整数'],
		['title', 'require', '名称必须'],
		['pid', 'require|egt:0', '父级必须为正整数|父级必须为正整数'],
		['sort', 'integer|egt:0', '排序值必须为正整数|排序值必须为正整数'],
		['delete_type', 'integer|egt:0', '删除类型必须为正整数|删除类型必须为正整数'],
		['delete_rule', 'integer|egt:0', '删除规则必须为正整数|删除规则必须为正整数'],
		['delete_reason', 'require', '请填写删除原因'],
	];

	/**
	 * @var array 验证场景
	 */
	protected $scene = [
		'add' => ['pid','title','sort'],
		'edit' => ['pid','title','sort'],
		'update' => ['id','title','sort'],
		'delete' => ['delete_type','delete_rule','delete_reason'],
	];
}

/* End of file Article.php */
/* Location: ./app_cms/common/validate/Article.php */