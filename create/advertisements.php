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
			$redis->del($redis_key[$i]);
			$redis->del($redis_key_view[$i]);
			continue;
		}
		
		//只生成通过的素材的值
		$materialid_arr = explode('|',$row['materialid']);
		$j = 0;
    	$materialid = array();
    	foreach ($materialid_arr as $key => $value) {

    		$find_sql = "SELECT `status` FROM `material_main` WHERE `id` = '{$value}' LIMIT 1";
    		$returns = mysqli_query($con,$find_sql);
    		$is_pass[$key]=mysqli_fetch_array($returns);
    		if($is_pass[$key]['status'] == 3){
    			$materialid[$j] = $value;
    			$j++;

    		}
    	}
    	$materialid_new = implode('|',$materialid);

    	if(empty($materialid_new)){
    		$redis->del($redis_key[$i]);
    		$redis->del($redis_key_view[$i]);
    		continue;
    	}
    	//结束

		$redis_value[$i] = $row['adsenseid'].','.$row['id'].','.$materialid_new.','.$row['spending'].','.($row['price']/1000).','.$begin_end_time[0].','.$begin_end_time[1].','.$row['orientation'].','.$row['numlimit'].','.'0'.','.$row['userid'].','.$row['status'].','.$row['running_status'].','.$row['adsiteid'].','.$row['adv_type'].','.$row['project_type'];//0 广告位id 1 广告id 2广告素材id 3 模式 4每一次展示花费（分）5开始时间 6结束时间 7全站投放？ 8次数限制 9已展现次数 10用户id 11状态 12启用（停止）13站点 14 adv_type(0内部广告 1广告主广告) 15project_type(1.文字 2.图片3: 图文（信息流))
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

