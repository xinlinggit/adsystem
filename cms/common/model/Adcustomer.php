<?php
namespace cms\common\model;
use think\Cache;
use think\Db;

/**
 * 广告主管理类
 */
class Adcustomer extends Base
{
	public function lists()
	{
		return Db::table('ad_admin_user')
		         ->alias('a')
				 ->field('a.*, l.status auth_status')
		         ->where(['a.status' => 1])
		         ->join("adserver.license_auth l", "a.id = l.uid", 'LEFT')
		         ->order('id asc')->select();
	}
}