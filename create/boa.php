<?php
//定时任务--数据归纳 对账专用
exit;
//$table_name = 'record_'.date('Ymd',strtotime("-1 day"));
$table_name = "record_20180604";
//$to_date = date('Y-m-d',strtotime("-1 day"));
$to_date = "20180604";
$con = mysqli_connect("172.30.2.82","adserver","|lcnfodCuxl8diFx6","adserver");
if (!$con){
  die('Could not connect: ' . mysql_error());
}

mysqli_query($con,'set names utf8');
/*先删除昨天acts每分钟（后面可能是1个小时）增加的数据*/
$old_delete_sql = "delete from record_day_copy  where time = '{$to_date}'";
mysqli_query($con,$old_delete_sql);

$sum_sql = "SELECT `a`.`adsystemid`,`a`.`advertisementid`,`a`.`materialid`,`a`.`userid`,count(*) AS sum,sum(cost) AS cost,`c`.`platform` FROM {$table_name} `a` LEFT JOIN `adserver`.`adsense` `b` ON `a`.`adsystemid`=`b`.`id` LEFT JOIN `adserver`.`adsite` `c` ON `b`.`adsite`=`c`.`id` GROUP BY adsystemid,advertisementid,materialid,userid";

$return = mysqli_query($con,$sum_sql);
while($row=mysqli_fetch_array($return)){

//返回根据从结果集取得的行生成的数组，如果没有更多行则返回 FALSE。 
   $inser_day_sql = "insert into record_day_copy (adsystemid,advertisementid,materialid,userid,sum,cost,time,platform) values({$row['adsystemid']},{$row['advertisementid']},{$row['materialid']},{$row['userid']},{$row['sum']},{$row['cost']},'{$to_date}',{$row['platform']})";

   mysqli_query($con,$inser_day_sql);
}

mysqli_close($con);