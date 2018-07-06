<?php

namespace app\admin\model;

use think\Model;
use app\admin\model\AdminMenu as MenuModel;
use app\admin\model\AdminRole as RoleModel;
use think\db;
/**
 * 后台用户模型
 * @package app\admin\model
 */
class Admaterial extends Model
{
	protected $table = 'material_text';

	protected $field = true;

	/**
	 * 查找一条关联记录
	 * @param int $id
	 * @param string $joinTable
	 * @param $table_alias
	 *
	 * @return mixed
	 */
	public static function findJoin($id = 0, $joinTable = '', $table_alias)
	{
		return Db::table('adserver.material_main')
		         ->alias('m')
		         ->where(['m.id' => $id])
		         ->join("adserver.$joinTable $table_alias", "m.material_id = $table_alias.sid", 'LEFT')
		         ->find();
	}
}