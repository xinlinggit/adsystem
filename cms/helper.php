<?php
//------------------------
// App 自定义助手函数
//-------------------------

use think\Cache;
use think\Config;
use think\Cookie;
use think\Db;
use think\Debug;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\Lang;
use think\Loader;
use think\Log;
use think\Model;
use think\Request;
use think\Response;
use think\Session;
use think\Url;
use think\View;


/**
 *
 * 极光根据别名推送
 * @param array|string $alia
 * @param string $msg
 * @param string $extra jsonString
 * @return void
 */
function jpush_alias($alia,$msg,$extra = ''){

    $command = "cd " . SHELL_PATH . ";" . PHP_EXE
        . ' ' . escapeshellarg('cms.php')
        . ' ' . escapeshellarg('shell/jpush/alias')
        . ' ' . escapeshellarg('--alia='.$alia)
        . ' ' . escapeshellarg('--msg='.$msg);
    if (!empty($extra)){
        //$extra = json_encode($extra,JSON_UNESCAPED_UNICODE);
        $command .= ' ' . escapeshellarg('--extra='.$extra);
    }
    $command .= ' ' . "> /dev/null &";
    exec($command);
}

/**
 *
 * 极光根据注册Id推送
 * @param array|string $jpushId
 * @param string $msg
 * @param string $extra jsonString
 * @return void
 */
function jpush_id($jpushId,$msg,$extra = ''){

    $command = "cd " . SHELL_PATH . ";" . PHP_EXE
        . ' ' . escapeshellarg('cms.php')
        . ' ' . escapeshellarg('shell/jpush/jpushId')
        . ' ' . escapeshellarg('--jpushId='.$jpushId)
        . ' ' . escapeshellarg('--msg='.$msg);
    if (!empty($extra)){
        $command .= ' ' . escapeshellarg('--extra='.$extra);
    }
    $command .= ' ' . "> /dev/null &";
    exec($command);
}

/**
 *
 * 极光广播推送
 * @param string $msg
 * @return void
 */
function jpush_broadcast($msg){
    $command = "cd " . SHELL_PATH . ";" . PHP_EXE
        . ' ' . escapeshellarg('cms.php')
        . ' ' . escapeshellarg('shell/jpush/broadcast')
        . ' ' . escapeshellarg('--msg='.$msg)
        . ' ' . "> /dev/null &";
    exec($command);
}

/**
 *
 * 极光根据别名推送历史消息
 * @param array|string $alia
 */
function jpush_history($alia){
    $command = "cd " . SHELL_PATH . ";" . PHP_EXE
        . ' ' . escapeshellarg('cms.php')
        . ' ' . escapeshellarg('shell/jpush/history')
        . ' ' . escapeshellarg('--alia='.$alia)
        . ' ' . "> /dev/null &";
    exec($command);
}

/**
 *
 * 根据极光唯一ID推送,进行互踢
 * @param string $jpush_id
 * @param string $id
 */
function jpush_kick($jpush_id,$id){
    $extra = jpush_extra_format(['userid'=>$id,'content'=>'用户已经在别处登录，此处强制退出'],2);
    $command = "cd " . SHELL_PATH . ";" . PHP_EXE
        . ' ' . escapeshellarg('cms.php')
        . ' ' . escapeshellarg('shell/jpush/kick')
        . ' ' . escapeshellarg('--jpushId='.$jpush_id)
        . ' ' . escapeshellarg('--extra='.$extra)
        . ' ' . "> /dev/null &";
    exec($command);
}
/**
 *
 * 极光推送自定义消息格式化
 * @param array $data
 * @param int $type 1:任务消息 2:互踢消息  3:提现消息
 * @return string jsonString
 */
function jpush_extra_format($data = [],$type = 1){
    return json_encode(['type'=>$type,'data'=>json_encode($data,JSON_UNESCAPED_UNICODE)]);
}

/**
 *
 * 加密手机号中间4位
 * @param $tel
 * @return string
 */
function encode_tel($tel){
    return substr_replace($tel,'****',3,4);
}

/***************** 手机号入库加密程序 start ***********************/
function mobileDecode($key, $sys = 10) {
    $kStr = 9396;
    $str = urlsafe_b64decode($key)/2.2;
    $str = $str - 1935 * (int)$sys - $kStr;
    return $str;
}

function mobileEncode($id, $sys = 10) {
    $str=9396;
    $str2=1935 * (int)$sys;
    $str=$str+$id+$str2;
    return urlsafe_b64encode($str*2.2);
}

function urlsafe_b64encode($string) {
    $data = base64_encode($string);
    $data = str_replace(array('+','/','='),array('-','_',''),$data);
    return $data;
}

function urlsafe_b64decode($string) {
    $data = str_replace(array('-','_'),array('+','/'),$string);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    return base64_decode($data);
}
/***************** 手机号入库加密程序 end ***********************/
