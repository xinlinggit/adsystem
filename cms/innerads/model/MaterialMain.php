<?php
/**
 * Created by PhpStorm.
 * User: hudy@cnfol.net
 * Date: 2018/6/12 0012
 * Time: 10:08
 */

namespace cms\innerads\model;

use think\Model;

class MaterialMain extends Model {
	protected $field = true;

	protected $autoWriteTimestamp = 'datetime';

	protected $table = 'material_main';
}