<?php
require_once "/home/httpd/adsystem/create/config.php";
//定时任务--数据归纳当天点击数据 每5分钟操作
$table_name = "record_click";
$to_date = date('Y-m-d',strtotime("now"));
$con = mysqli_connect($createconfig['hostname'],$createconfig['username'],$createconfig['password'],$createconfig['database']);
if (!$con){
  die('Could not connect: ' . mysql_error());
}

mysqli_query($con,'set names utf8');


$delete_sql = "delete from record_click_day  where time = '{$to_date}'";

mysqli_query($con,$delete_sql);

$sum_sql = "SELECT `a`.`adsystemid`,`a`.`advertisementid`,`a`.`materialid`,`a`.`userid`,count(*) AS sum,sum(cost) AS cost,`c`.`platform` FROM {$table_name} `a` LEFT JOIN `adserver`.`adsense` `b` ON `a`.`adsystemid`=`b`.`id` LEFT JOIN `adserver`.`adsite` `c` ON `b`.`adsite`=`c`.`id` GROUP BY adsystemid,advertisementid,materialid,userid";

$return = mysqli_query($con,$sum_sql);
while($row=mysqli_fetch_array($return)){

//返回根据从结果集取得的行生成的数组，如果没有更多行则返回 FALSE。 
   $inser_day_sql = "insert into record_click_day (adsystemid,advertisementid,materialid,userid,sum,cost,time,platform) values({$row['adsystemid']},{$row['advertisementid']},{$row['materialid']},{$row['userid']},{$row['sum']},{$row['cost']},'{$to_date}',{$row['platform']})";

   mysqli_query($con,$inser_day_sql);
}

mysqli_close($con);