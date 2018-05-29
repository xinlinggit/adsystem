<?php
namespace cnfol\api;
use think\Config;
use think\Log;

/**
 * 博客相关接口
 */

class Blog
{
	var $configs;
	var $timeout    = 30;
	var $socket_key = '                                ';

	public function __construct()
	{
		$this->configs=Config::get('blog');
	}

	/**
	 * 发送请求
	 * @param $type string 类型
	 * @param $data array 数据
	 *
	 * @return string
	 */
	public function send_data($type = '', $data = [])
	{

		$socket = fsockopen($this->configs['host'],$this->configs['port'], $error_no, $error_str,$this->timeout);

		if(!$socket)
		{
			Log::record("$error_str ($error_no)",'blog');
			return '';
		}
		$strSend = $this->format_data($type, $data);

		if(fwrite($socket, $strSend) === false)
		{
			Log::record("写socket失败",'blog');
			return '';
		}
		$strHead  = fread($socket, '12');//获取12位头信息

		if($strHead === false)
		{
			Log::record("获取12位头信息失败",'blog');
			return '';
		}

		$strLen = substr($strHead, '4') * 1;//取前4位的数值，并去除前导0

		$response = '';

		while($strLen > 0)
		{
			$content    = fread($socket, $strLen);
			$response  .= $content;
			$len        = strlen($content);
			$strLen    -= $len;
		}

		fclose($socket);

		return $response;
	}

	/**
	 * 数据格式化
	 * @param $type string 类型
	 * @param $data array 数据
	 *
	 * @return string
	 */
	public function format_data($type, $data)
	{

		$temp       = '';
		$strData    = '<CNFOLGW><Parameters>';

		if(is_array($data) && !empty($data))
		{
			foreach($data as $k=>$v)
			{
				$temp .= '<'.$k.'>'.$v.'</'.$k.'>';
			}
			unset($data);
		}
		$strData .= $temp.'</Parameters></CNFOLGW>';
		$strLen   = str_pad(strlen($strData),8,'0',STR_PAD_LEFT);//计算长度并左边补零
		$strData  = $type.$this->socket_key.$strLen.$strData;//连接数据

		return $strData ;
	}
}