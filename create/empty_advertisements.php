<?php
//没用
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

$sql = "select * from advertisement where status in (2,3)";
$return = mysqli_query($con,$sql);

$i = 0;
while($row=mysqli_fetch_array($return)){
		$redis_key[$i] = 'advertisement_'.$row['adsenseid'].'_'.$row['id'];//该条广告的key

		$redis_key_view[$i] = 'view_advertisement_'.$row['adsenseid'].'_'.$row['id'];//该条广告展示量的key

		$begin_end_time = explode(",",$row['time']);
		
		if(date('Y-m-d H:i:s') > $begin_end_time[1]){
			var_dump($redis_key[$i]);
		}
		
	$i++;
}
mysqli_close($con);

