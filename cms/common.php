<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

if (!function_exists('fen2yuan')) {
	// 金额转换: 分 -> 元
	function fen2yuan ($fen = 0)
	{
		return $fen / 100;
	}
}

if (!function_exists('yuan2fen')) {
	// 金额转换: 元 -> 分
	function yuan2fen ($yuan = 0)
	{
		return $yuan * 100;
	}
}

if(!function_exists('get_transaction_no')) {
	/**
	 * 得到新订单号
	 * @return  string
	 */
	function get_transaction_no() {
		/* 选择一个随机的方案 */
		mt_srand( (double) microtime() * 1000000 );

		return date( 'Ymd' ) . str_pad( mt_rand( 1, 99999 ), 5, '0', STR_PAD_LEFT );
	}
}


/**
 * ftp 上传辅助函数
 */
if(!function_exists('ftp_mksubdirs')){
	// function
	function ftp_mksubdirs($ftpcon,$ftpbasedir,$ftpath){
		@ftp_chdir($ftpcon, $ftpbasedir); // /var/www/uploads
		$parts = explode(DS,$ftpath); // 2013/06/11/username
		foreach($parts as $part){
			if(!@ftp_chdir($ftpcon, $part)){
				ftp_mkdir($ftpcon, $part);
				ftp_chdir($ftpcon, $part);
				//ftp_chmod($ftpcon, 0777, $part);
			}
		}
	}
}

if(!function_exists('upload2fileserver'))
{
	/**
	 * 上传文件到图片服务器
	 * @param $path 本地图片的存储的绝对完整路径
	 * @param int $retry 上传失败的重试次数
	 *
	 * @return boolean|string   成功返回 url, 否则返回 false
	 */
	function upload2fileserver($path, $retry = 3, $sub = 'adpic')
	{
		try {
			// 将本地途径转换绝对路径
			$conn_retry = $login_retry = $upload_retry = $retry;
			$Cfg        = \think\Config::get( 'fileserver' );
			$path_parts = pathinfo( $path );
			$extName    = $path_parts['extension'] ?: $path_parts['filename'];
			$subdir     = $sub . DS . date( 'Y', time() ) . DS . date( 'm', time() ) . DS . date( 'd', time() );
			$filename   = time() . mt_rand( 1000000, 9999999 ) . '.' . $extName;
			// 1.连接
			$conn_id = ftp_connect( $Cfg['host'] );
			while ( ( $conn_id === false ) && $conn_retry -- ) {
				sleep( 1 );
				$conn_id = ftp_connect( $Cfg['host'] );
			}
			$login_result = ftp_login( $conn_id, $Cfg['username'], $Cfg['password'] );
			while ( ( $login_result === false ) && $login_retry -- ) {
				sleep( 1 );
				$login_result = ftp_login( $conn_id, $Cfg['username'], $Cfg['password'] );
			}
			// 2.新建目录
			ftp_mksubdirs( $conn_id, $Cfg['ftp_basedir'], $subdir );
			// 3.上传
			$res = ftp_put( $conn_id, $filename, $path, FTP_BINARY );
			while ( ( $upload_retry === false ) && $upload_retry -- ) {
				sleep( 1 );
				$res = ftp_put( $conn_id, $filename, $path, FTP_BINARY );
			}
			// 4.关闭连接
			ftp_close( $conn_id );
		}catch (Exception $e)
		{
			$msg = $e->getMessage();
			@error_log(date('Y-m-d H:i:s') . ' |-info-| ' . print_r($msg, true) . PHP_EOL, 3, LOG_PATH . '/upload2fileserver_' . date('Ymd') . '.log');
		}
		@unlink($path);
		if($res)
		{
			return ['code' => 1, 'msg' => '', 'path' => $Cfg['ftp_server_domain'] . DS . 'as' . DS . $subdir . DS . $filename];
		} else {
			return ['code' => 0, 'msg' => $msg, 'path' => ''];
		}
	}
}
