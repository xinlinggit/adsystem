<?php
namespace cnfol\api;
/**
 * 云平台相关接口
 */
class Cloud
{
	/**
	 * @param $name
	 * @param $arguments
	 *
	 * get_user_modList 获取用户云平台模块权限
	 * get_user_funList 获取用户云平台模块操作权限
	 * get_user_modList 获取用户云平台模块权限
	 * get_module_funList 获取模块操作权限列表
	 * get_function_info 获取用户操作权限信息
	 * get_userinfo 获取云平台用户信息
	 *
	 * @return array|bool|mixed
	 */
	static public function __callStatic($name, $arguments)
	{
		try{
			$soap = new \SoapClient(\think\Config::get('cloud.api_url'));
			switch (count($arguments)){
				case 0:
					$data = $soap->$name();
					break;
				case 1:
					$data = $soap->$name($arguments[0]);
					break;
				case 2:
					$data = $soap->$name($arguments[0],$arguments[1]);
					break;
				default:
					$data = [];
			}
			return $data;
		}catch(\SoapFault $e){
			$message = $e->getMessage();
			//error_log(date('Y-m-d H:i:s').'soap调用error:'.@print_r($message,true).PHP_EOL,3,'/data/tmp/log/soap_error_'.date('Ymd').'.log');
			return false;
		}
	}

}