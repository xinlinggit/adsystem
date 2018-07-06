<?php
//在用
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

$sql = "select * from advertisement where status in (-1,2,3,4)";

$return = mysqli_query($con,$sql);

$i = 0;
while($row=mysqli_fetch_array($return)){
		if($row['adv_type'] == 1){
			$row['adsenseid'] = 0;
		}
		
		$redis_key[$i] = 'advertisement_'.$row['adsenseid'].'_'.$row['id'];//该条广告的key

		$redis_key_view[$i] = 'view_advertisement_'.$row['adsenseid'].'_'.$row['id'];//该条广告展示量的key

		$begin_end_time = explode(",",$row['time']);	
		if($row['status'] == -1){
			$redis->del($redis_key[$i]);
			$redis->del($redis_key_view[$i]);
			continue;
		}
		if(date('Y-m-d H:i:s') > $begin_end_time[1] || $row['status'] == 4){
			$redis->del($redis_key[$i]);
			$redis->del($redis_key_view[$i]);
			$update_sql = "update advertisement set status = '4' where id = {$row['id']}";
			mysqli_query($con,$update_sql);
		}

		
	$i++;
}
mysqli_close($con);

