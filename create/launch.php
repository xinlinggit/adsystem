<?php
//require_once "../vendor/autoload.php";
require_once "/home/httpd/adsystem/vendor/autoload.php";
use Predis\Client;
use think\Db;
$client = new Client([
    'scheme' => 'tcp',
    'host'   => '172.30.2.132',
    'port'   => 6379,
]);
//echo $client->get('adsense_15');exit;
//$client->del(['name']);
//$db = Db::connect($conf);
$map['status'] = 1;
$adsense_data = Db::table('adsense')->where($map)->select()->toarray();
dump($adsense_data);exit;
$adposition_id = [
    [
        'ad_id'=>1,
        'img'=>'http://www.baidu.com',
        'target'=>'http://www.google.com',
        'width'=>'320',
        'height'=>'240'
    ],
    [
        'ad_id'=>2,
        'img'=>'http://www.baidu.com',
        'target'=>'http://www.google.com',
        'width'=>'320',
        'height'=>'240'
    ],
    [
        'ad_id'=>1,
        'img'=>'http://www.baidu.com',
        'target'=>'http://www.google.com',
        'width'=>'320',
        'height'=>'240'
    ],
    [
        'ad_id'=>2,
        'img'=>'http://www.baidu.com',
        'target'=>'http://www.google.com',
        'width'=>'320',
        'height'=>'240'
    ],
    [
        'ad_id'=>1,
        'img'=>'http://www.baidu.com',
        'target'=>'http://www.google.com',
        'width'=>'320',
        'height'=>'240'
    ],
];
//$client->hmset('adposition-1',$adposition1);
var_dump($client->hmget('adposition-1',['img']));