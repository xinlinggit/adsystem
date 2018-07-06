<?php
require_once "/home/httpd/adsystem/create/config.php";
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
