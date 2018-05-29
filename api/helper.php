<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2017 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

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
 * 对ID加密
 * @param null|int $length
 * @param null|string $salt
 * @param null|string $alphabet
 * @return Hashids\Hashids static
 */
function hashids($length = null, $salt = null, $alphabet = null)
{
    return \Hashids\Hashids::instance($length, $salt, $alphabet);
}

/**
 * 一键导出Excel 2007格式
 * @param array $header Excel头部 ["COL1","COL2","COL3",...]
 * @param array $body 和头部长度相等字段查询出的数据就可以直接导出
 * @param null|string $name 文件名，不包含扩展名，为空默认为当前时间
 * @param string|int $version 版本 2007|2003|ods|pdf
 * @return string
 */
function export_excel($header, $body, $name = null, $version = '2007')
{
    return \Excel::export($header, $body, $name, $version);
}

/**
 * 获取七牛上传token
 * @return mixed
 */
function qiniu_token()
{
    return \Qiniu::token();
}

/**
 * 检查指定节点是否有权限
 * @param null $action
 * @param null $controller
 * @param null $module
 * @return bool
 */
function check_access($action = null, $controller = null, $module = null)
{
    return \Rbac::AccessCheck($action, $controller, $module);
}

/**
 * 文件下载
 * @param $file_path
 * @param string $file_name
 * @param string $file_size
 * @param string $ext
 * @return string
 */
function download($file_path, $file_name = '', $file_size = '', $ext = '')
{
    return \File::download($file_path, $file_name, $file_size, $ext);
}

// 用户自定义函数

/**
 * 使用命令行工具生成 UserSig
 * @param1 用户标识符
 * @param2 应用id
 * @param3 私钥存放路径 使用绝对路径
 * @param4 signature工具命令的路径
 * @return userSig
 */
function signature($identifier, $sdkappid = '', $private_key_path = '', $tool_path = '')
{
    $live_conf = Config::get('live_conf');
    empty($tool_path) ? $tool_path = $live_conf['tool_path'] : '';
    empty($private_key_path) ? $private_key_path = $live_conf['private_key_path'] : '';
    empty($sdkappid) ? $sdkappid = $live_conf['SdkAppId'] : '';

    # 这里需要写绝对路径，开发者根据自己的路径进行调整
    $command = $tool_path
        . ' ' . escapeshellarg($private_key_path)
        . ' ' . escapeshellarg($sdkappid)
        . ' ' . escapeshellarg($identifier);
    $ret = exec($command, $out, $status);
    if ($status == -1) {
        return null;
    }
    return $out[0];
}

/**
 * 使用原生php生成 UserSig
 * @param1 用户标识符
 * @param2 应用id
 * @param3 私钥存放路径 使用绝对路径
 * @param4 signature工具命令的路径
 * @return userSig
 */
function genSig($identifier, $expire = 180 * 24 * 3600, $sdkappid = '', $private_key_path = '')
{
    Loader::import('TLSSig', EXTEND_PATH);

    $api = new TLSSigAPI();
    $api->SetAppid(Config::get('live_conf.SdkAppId'));
    $private = file_get_contents(Config::get('live_conf.private_key_path'));
    $api->SetPrivateKey($private);
    $public = file_get_contents(Config::get('live_conf.public_key_path'));
    $api->SetPublicKey($public);
    $sig = $api->genSig($identifier, $expire);
    var_dump($sig);
    $result = $api->verifySig($sig, $identifier, $init_time, $expire_time, $error_msg);
    var_dump($result);
    var_dump($init_time);
    var_dump($expire_time);
    var_dump($error_msg);

//        $result = $api->verifySig($sig, 'user2', $init_time, $expire_time, $error_msg);
//        var_dump($result);
//        var_dump($init_time);
//        var_dump($expire_time);
//        var_dump($error_msg);
}

/**
 * 获取推流地址
 * 如果不传key和过期时间，将返回不含防盗链的url
 * @param $bizId 腾讯云分配到的bizid
 * @param $streamId 用来区别不通推流地址的唯一id
 * @param $key 安全密钥
 * @param $time 过期时间 sample 2016-11-12 12:00:00
 * @return String url
 */
function getPushUrl($bizId, $streamId, $key = null, $time = null)
{

    $livecode = $bizId . "_" . $streamId; //直播码
    if ($key && $time) {
        $txTime = strtoupper(base_convert(strtotime($time), 10, 16));
        //txSecret = MD5( KEY + livecode + txTime )
        //livecode = bizid+"_"+stream_id  如 8888_test123456
        $livecode = $bizId . "_" . $streamId; //直播码
        $txSecret = md5($key . $livecode . $txTime);
        $ext_str = "?" . http_build_query(array(
                "bizid" => $bizId,
                "txSecret" => $txSecret,
                "txTime" => $txTime
            ));
    }
    return "rtmp://" . $bizId . ".livepush.myqcloud.com/live/" . $livecode . (isset($ext_str) ? $ext_str : "");
}

/**
 * 获取播放地址
 * @param string $bizId 腾讯云分配到的bizid
 * @param string $streamId 用来区别不通推流地址的唯一id
 * @return array
 */
function getPlayUrl($bizId, $streamId)
{
    $livecode = $bizId . "_" . $streamId; //直播码
    return array(
        "rtmp://" . $bizId . ".liveplay.myqcloud.com/live/" . $livecode,
        "http://" . $bizId . ".liveplay.myqcloud.com/live/" . $livecode . ".flv",
        "http://" . $bizId . ".liveplay.myqcloud.com/live/" . $livecode . ".m3u8"
    );
}

/**
 * CURL请求
 *
 * @param string $url 请求地址
 * @param array $data 请求数据 key=>value 键值对
 * @param string $type 请求文本类型
 * @param integer $timeout 超时时间,单位秒
 * @return array
 */
function curl_post($url, $data, $type = '', $timeout = 5)
{
    $ishttp = substr($url, 0, 8) == "https://" ? TRUE : FALSE;

    $ch = curl_init();
    if (is_array($data)) {
        $data = http_build_query($data);
    }
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

    if ($ishttp) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    }
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    if (!empty($type)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data))
        );
    }
    $result['data'] = curl_exec($ch);
    $result['code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return $result;
}

/**
 * CURL请求
 *
 * @param string $url 请求地址
 * @param array $data 请求数据 key=>value 键值对
 * @param boolean $json 是否json格式
 * @return array
 */
function http($url, $data = NULL, $json = false)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    if (!empty($data)) {
        if ($json && is_array($data)) {
            $data = json_encode($data);
        }
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        if ($json) { //发送JSON数据
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_HTTPHEADER,
                array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length:' . strlen($data))
            );
        }
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $res = curl_exec($curl);
    $errorno = curl_errno($curl);
    if ($errorno) {
        return array('errorno' => false, 'errmsg' => $errorno);
    }
    curl_close($curl);
    return json_decode($res, true);
}

function addTagitem(&$v, $k, $userid)
{
    $v = ['userid' => $userid, 'tag' => $v];
}


/**
 *
 * 短信发送
 * @param array $info
 * @param integer $sysType
 * @return array
 */
function sendSmsAction($info, $sysType = 9)
{
    $sign = (isset($info['sign']) && $info['sign']) ? (int)$info['sign'] : 0;
    $sendChannel = (isset($info['channel']) && $info['channel']) ? (int)$info['channel'] : 6; //发送频道 9阿里，6漫道
    @error_log(date('Y-m-d H:i:s') . ' |-info-| ' . print_r($info, true) . PHP_EOL, 3, LOG_PATH . '/sendsmsaction_' . date('Ymd') . '.log');

    $info['content'] = urlencode($info['content']);
    $postParam = [
        'p.apiKey' => '7777772E636E666F6C2E636F6D',
        'p.can' => 1,
        'p.msg' => $info['content'],
        'p.priority' => 5,
        'p.receiver' => $info['mobile'],
        'p.sendChannel' => $sendChannel,
        'p.smstype' => 2,
        'p.sysType' => $sysType,
        'p.sign' => $sign
    ];
    $smsRs2 = curl_post('http://api.access.cnfol.com:8085/send.shtml?', $postParam);
    if ($smsRs2['code'] != 200) {
        return ['flag' => '50005', 'msg' => '短信服务器连接失败！'];
    }

    $smsRs = json_decode($smsRs2['data'], true);
    @error_log(date('Y-m-d H:i:s') . '|-postParam-|' . print_r($postParam, true) . '|-smsRs-|' . $smsRs['state'] . PHP_EOL, 3, LOG_PATH . '/sendsmsaction_' . date('Ymd') . '.log');
    if ($smsRs) {
        switch ($smsRs['state']) {
            case 0:
                return ['flag' => 0, 'msg' => '短信发送成功'];
                break;
            case -10:
                return ['flag' => 50001, 'msg' => '短信发送间隔不能少于3分钟'];
                break;
            case -9:
                return ['flag' => 50002, 'msg' => '短信发送太过频繁！'];
                break;
            case -8:
                return ['flag' => 50003, 'msg' => '短信发送异常！'];
                break;
            case -2:
                return ['flag' => 50007, 'msg' => '无匹配TTS模板！'];
                break;
            case -1:
                return ['flag' => 50008, 'msg' => '无SMS匹配模板！'];
                break;
            default:
                return ['flag' => 50004, 'msg' => '其他错误！'];
                break;
        }
    } else {
        return ['flag' => 50006, 'msg' => '短信服务器数据异常！'];
    }

}


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
function jpush_kick($jpushId,$id){
    $extra = jpush_extra_format(['userid'=>$id,'content'=>'用户已经在别处登录，此处强制退出'],2);
    $command = "cd " . SHELL_PATH . ";" . PHP_EXE
        . ' ' . escapeshellarg('cms.php')
        . ' ' . escapeshellarg('shell/jpush/kick')
        . ' ' . escapeshellarg('--jpushId='.$jpushId)
        . ' ' . escapeshellarg('--extra='.$extra)
        . ' ' . "> /dev/null &";
    exec($command);
}

/**
 *
 * 极光推送自定义消息格式化
 * @param array $data
 * @param int $type 1:任务消息 2:互踢消息
 * @return string jsonString
 */
function jpush_extra_format($data = [],$type = 1){
    return json_encode(['type'=>$type,'data'=>json_encode($data,JSON_UNESCAPED_UNICODE)]);
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


			
