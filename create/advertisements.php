<?php
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
		if($row['adv_type'] == 1){
			$row['adsenseid'] = 0;
		}
		$redis_key[$i] = 'advertisement_'.$row['adsenseid'].'_'.$row['id'];//该条广告的key

		$redis_key_view[$i] = 'view_advertisement_'.$row['adsenseid'].'_'.$row['id'];//该条广告展示量的key

		$begin_end_time = explode(",",$row['time']);
		
		if(date('Y-m-d H:i:s') < $begin_end_time[0] || date('Y-m-d H:i:s') > $begin_end_time[1]){
			continue;
		}
		
		$redis_value[$i] = $row['adsenseid'].','.$row['id'].','.$row['materialid'].','.$row['spending'].','.($row['price']/1000).','.$begin_end_time[0].','.$begin_end_time[1].','.$row['orientation'].','.$row['numlimit'].','.'0'.','.$row['userid'].','.$row['status'].','.$row['running_status'];//0 广告位id 1 广告id 2广告素材id 3 模式 4每一次展示花费（分）5开始时间 6结束时间 7全站投放？ 8次数限制 9已展现次数 10用户id 11状态 12启用（停止）
		$a[$i] = $redis->get($redis_key[$i]);
		
		if($row['status'] == 2){
			$redis->set($redis_key_view[$i],'0');//设置缓存key value
		}

		if($row['status'] == 3 && $a[$i] ===false){
			$redis->set($redis_key_view[$i],'0');//设置缓存key value
		}

		$redis->set($redis_key[$i],$redis_value[$i]);//设置缓存key value	
	$i++;
}
mysqli_close($con);

