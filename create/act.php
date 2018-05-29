<?php
//定时任务--数据归纳 凌晨0点过5分钟操作
$table_name = 'record_'.date('Ymd',strtotime("-1 day"));
//$table_name = "record_20180527";
$date = date('Y-m-d 00:00:00', time());
$to_date = date('Y-m-d',strtotime("-1 day"));
//$to_date = "20180527";
$con = mysqli_connect("172.30.2.82","adserver","|lcnfodCuxl8diFx6","adserver");
if (!$con){
  die('Could not connect: ' . mysql_error());
}

mysqli_query($con,'set names utf8');


$create_sql = "CREATE TABLE {$table_name} like record";
$insert_sql = "INSERT INTO {$table_name} select * from record  where create_time < '{$date}'";
$delete_sql = "delete from record  where create_time < '{$date}'";
mysqli_query($con,$create_sql);
mysqli_query($con,$insert_sql);
mysqli_query($con,$delete_sql);
//被注释的 ，之前写的可能导致数据差一些
//$sum_sql = "select adsystemid,advertisementid,materialid,userid, count(*) AS sum, sum(cost) AS cost from {$table_name} group by adsystemid,advertisementid,materialid,userid having count(*)>1";
//$sum_sql = "select adsystemid,advertisementid,materialid,userid, count(*) AS sum, sum(cost) AS cost from {$table_name}  where cost > 0 group by adsystemid,advertisementid,materialid,userid";
$sum_sql = "SELECT `a`.`adsystemid`,`a`.`advertisementid`,`a`.`materialid`,`a`.`userid`,count(*) AS sum,sum(cost) AS cost,`c`.`platform` FROM {$table_name} `a` LEFT JOIN `adserver`.`adsense` `b` ON `a`.`adsystemid`=`b`.`id` LEFT JOIN `adserver`.`adsite` `c` ON `b`.`adsite`=`c`.`id` GROUP BY adsystemid,advertisementid,materialid,userid";

$return = mysqli_query($con,$sum_sql);
while($row=mysqli_fetch_array($return)){

//返回根据从结果集取得的行生成的数组，如果没有更多行则返回 FALSE。 
   $inser_day_sql = "insert into record_day (adsystemid,advertisementid,materialid,userid,sum,cost,time,platform) values({$row['adsystemid']},{$row['advertisementid']},{$row['materialid']},{$row['userid']},{$row['sum']},{$row['cost']},'{$to_date}',{$row['platform']})";

   mysqli_query($con,$inser_day_sql);
}

mysqli_close($con);