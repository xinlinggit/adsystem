<?php
//定时任务------生成广告位模型  在用
require_once "/home/httpd/adsystem/create/config.php";
require_once "/home/httpd/adsystem/vendor/autoload.php";
use Predis\Client;
$redis = new Client([
    'scheme' => 'tcp',
    'host'   => '172.30.2.132',
    'port'   => 6379,
]);
$con = mysqli_connect($createconfig['hostname'],$createconfig['username'],$createconfig['password'],$createconfig['database']);
if (!$con){
  die('Could not connect: ' . mysql_error());
}
mysqli_query($con,'set names utf8');
$sql = "select id,width,height,status,sensemodel,sensetype,freetype,materialmodel,imgurl,text,adsite,linkurl from adsense where status='1'";

$return = mysqli_query($con,$sql);
while($row=mysqli_fetch_array($return)){
//返回根据从结果集取得的行生成的数组，如果没有更多行则返回 FALSE。
	$adsense_key = 'adsense_'.$row['id'];

	$adsense_value = $row['width'].','.$row['height'].','.$row['status'].','.$row['sensemodel'].','.$row['sensetype'].','.$row['freetype'].','.$row['materialmodel'].','.$row['imgurl'].','.$row['text'].','.$row['adsite'].','.$row['linkurl'];

	$redis->setex($adsense_key,300,$adsense_value);//设置缓存key value  存在时间1000秒
	
}
mysqli_close($con);
