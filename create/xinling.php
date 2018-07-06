<?php
exit;
ini_set('memory_limit','5120M');
require_once "/home/httpd/adsystem/vendor/autoload.php";
use Predis\Client;
$redis = new Client([
    'scheme' => 'tcp',
    'host'   => '172.30.2.132',
    'port'   => 6379,
]);
$con = mysqli_connect("172.30.2.82","adserver","|lcnfodCuxl8diFx6","adserver");
if (!$con){
  die('Could not connect: ' . mysql_error());
}
mysqli_query($con,'set names utf8');
$redis->renamenx('ad','ad_ready');//把要记录的redis key先重命名 然后方便删除
$new_arr = array_filter(explode(";",$redis->get('ad')));


$i = 0;

foreach ($new_arr as $k => $v) {
	
	$new_arr[$i] = explode(",",$v);
	$data[$i]['adsystemid'] = isset($new_arr[$i][0])?$new_arr[$i][0]:'';
	$data[$i]['advertisementid'] = isset($new_arr[$i][1])?$new_arr[$i][1]:'';
	$data[$i]['materialid'] = isset($new_arr[$i][2])?$new_arr[$i][2]:'';
	$data[$i]['userid'] = isset($new_arr[$i][3])?$new_arr[$i][3]:'';
	$data[$i]['ip'] = isset($new_arr[$i][4])?$new_arr[$i][4]:'';
	$data[$i]['country'] = isset($new_arr[$i][5])?$new_arr[$i][5]:'';
	$data[$i]['province'] = isset($new_arr[$i][6])?$new_arr[$i][6]:'';
	$data[$i]['city'] = isset($new_arr[$i][7])?$new_arr[$i][7]:'';
	$data[$i]['server_name'] = isset($new_arr[$i][8])?$new_arr[$i][8]:'';
	$data[$i]['userAgent'] = isset($new_arr[$i][9])?$new_arr[$i][9]:'';
	$data[$i]['create_time'] =  isset($new_arr[$i][10])?$new_arr[$i][10]:'';
	$data[$i]['cost'] =  isset($new_arr[$i][11])?$new_arr[$i][11]:'';
	$data[$i]['remain'] =  isset($new_arr[$i][12])?$new_arr[$i][12]:'';
	if($data[$i]['remain'] < 0){
		$data[$i]['remain'] = 0;
	}
	$i++;

}
foreach ($data as $key => $value) {

	$sql = "insert into record_copy (adsystemid,advertisementid,materialid,userid,ip,country,province,city,server_name,userAgent,create_time,cost,remain) values('{$value['adsystemid']}','{$value['advertisementid']}','{$value['materialid']}','{$value['userid']}','{$value['ip']}','{$value['country']}','{$value['province']}','{$value['city']}','{$value['server_name']}','{$value['userAgent']}','{$value['create_time']}','{$value['cost']}','{$value['remain']}')";

	$return = mysqli_query($con,$sql);
}


mysqli_close($con);

