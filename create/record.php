<?php
echo 222;exit;//暂时没用
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

//$redis->renamenx('ad','ad_ready');//把要记录的redis key先重命名 然后方便删除
$new_arr= array_filter(explode("/",$redis->get('ad')));
$i = 0;

foreach ($new_arr as $k => $v) {
	//var_dump($v);exit;
		$new_arr[$i] = array_filter(explode(",",$v));
		//获取数据start
		$data[$i]['adsystemid'] = (string)isset($new_arr[$i][0])?$new_arr[$i][0]:'';
		$data[$i]['advertisementid'] = (string)isset($new_arr[$i][1])?$new_arr[$i][1]:'';
		$data[$i]['materialid'] = (string)isset($new_arr[$i][2])?$new_arr[$i][2]:'';
		$data[$i]['userid'] = (string)isset($new_arr[$i][3])?$new_arr[$i][3]:'';
    	$data[$i]['ip'] = (string)isset($new_arr[$i][4])?$new_arr[$i][4]:'';
    	$data[$i]['server_name'] = (string)isset($new_arr[$i][5])?$new_arr[$i][5]:'';
    	$data[$i]['userAgent'] = (string)isset($new_arr[$i][6])?$new_arr[$i][6]:'';
    	$data[$i]['create_time'] =  (string)isset($new_arr[$i][7])?$new_arr[$i][7]:'';
    	$data[$i]['cost'] =  (string)isset($new_arr[$i][8])?$new_arr[$i][8]:'';
    	$data[$i]['remain'] =  (string)isset($new_arr[$i][9])?$new_arr[$i][9]:'';

    	$data[$i] = implode("','",$data[$i]);
    	var_dump($data[$i]);exit;
    	$data[$i] = '('.$v.')';
    	$i++;
    	//获取数据end
}



if(!empty($data)){
	$new_data =  implode(",",$data);
	//var_dump($new_data);exit;
	$sql = "insert into record_copy (adsystemid,advertisementid,materialid,userid,ip,server_name,userAgent,create_time,cost,remain) values {$new_data}";
	echo $sql;exit;
	mysqli_query($con,$sql);
	//$redis->delete('ad_ready');// 删除键值 可以传入数组 array('key1','key2')删除多个键
}

