<?php

return [
    /*腾讯云通信*/
    'SdkAppId'	    => 1400032339,
    //应用名称	香港APP
    //应用类型	理财
    //应用简介	香港APP
    //帐号名称	caishi-app
    'accountName'   =>  'caishi-hk',
    'accountType'	=>  11960,
    //账号管理员	admin
    'accountAdmin'  =>  'admin',
    'accountUsersig'  =>  "eJxl0FFPgzAUBeB3fkXD64zrpWNZTfYgSmSTEQnGzL00zVpIs9FhqQww-neVmUji83duTs79cBBC7nOcXfP9-vSuLbNdJV10g1zsXv1hVSnBuGXEiH8o20oZyXhupRnQ86mH8TiihNRW5eo3wEWp9IhrcWBDxaAw*z4mHiF0HFHFgJswvVtFD4sX03TlujhnMot3wTFYvfHk2PnQRvl0fRDxBKbBfSnDWxVE234bQkQfE0421avsk06fJ7t2kdpQNDDPnuq*LYyf0mK5HFVaVV4*AT6G*QxTSkbaSFOrk74MxuADAMU-q51P5wvscF0k",
    //生成userdig的工具路径
    'tool_path'     =>  ROOT_PATH . 'signature/linux-signature64',
    //私钥存放路径
    'private_key_path'=> ROOT_PATH . 'keys/private_key',
    //公钥存放路径
    'public_key_path'=> ROOT_PATH . 'keys/public_key',

    /*腾讯视频直播*/
    'appid'         =>  1253565257,
    'bizid'         =>  8810,
    //推流防盗链key :  9f6f1d37a2095070829a6eea1260311a
    'pushkey'       =>  '5a45a023ce97526c776f03c24c096075',
    //API鉴权key :  a13322ef10ecb3e865b65629666933a4  访问腾讯API接口时生成签名 sgin = MD5(key+t)
    'apikey'        =>  'd1adf39d760cdebe70497f9c162700b9',
    //回调URL :  
    'callback'      =>  'http://caishi.api.cnfol.hk/v1/callback/index',
];