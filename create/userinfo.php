<?php
require_once "/home/httpd/adsystem/create/config.php";
require_once "/home/httpd/adsystem/vendor/autoload.php";
use Predis\Client;
$redis = new Client([
    'scheme' => 'tcp',
    'host'   => $createconfig['redis']['host'],
    'port'   => $createconfig['redis']['port'],
]);
$con = mysqli_connect($createconfig['hostname'],$createconfig['username'],$createconfig['password'],$createconfig['database']);
if (!$con){
  die('Could not connect: ' . mysql_error());
}
mysqli_query($con,'set names utf8');
$key_data = 'userinfo_*';
$keys_arr = $redis->keys($key_data);//查找获取该广告位所有的广告的 key
foreach ($keys_arr as $key => $value) {
	$data[$key] = $redis->get($value);
	$uid[$key] = explode('_',$value);
	$sql = "UPDATE userinfo SET account= '{$data[$key]}' WHERE uid='{$uid[$key][1]}'";
	mysqli_query($con,$sql);
}
mysqli_close($con);