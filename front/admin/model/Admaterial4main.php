<?php

namespace app\admin\model;

use think\Model;
use think\Db;

/**
 * 后台用户模型
 * @package app\admin\model
 */
class Admaterial4main extends Model
{
	protected $table = 'material_main';

	protected $filed = true;

	// 定义时间戳字段名
	protected $createTime = 'create_time';
	protected $updateTime = 'update_time';

	// 自动写入时间戳
	protected $autoWriteTimestamp = 'datetime';
}