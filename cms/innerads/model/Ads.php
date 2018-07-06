<?php
/**
 * Created by PhpStorm.
 * User: hudy@cnfol.net
 * Date: 2018/4/9 0009
 * Time: 16:14
 */

namespace cms\adcustomer\model;

use think\Model;

class Ads extends Model
{
	// 开启自动写入时间戳字段
	protected $autoWriteTimestamp = 'int';

	//自动完成
	protected $auto = ['password'];

	protected function setPasswordAttr($value)
	{
		return password_hash_tp($value);
	}

	/**
	 * 修改密码
	 */
	public function updatePassword($uid, $password)
	{
		return $this->where("id", $uid)->update(['password' => password_hash_tp($password)]);
	}
}