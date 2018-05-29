<?php

namespace api\v1\traits\controller;

use think\exception\HttpResponseException;
use think\Request;

trait Controller
{
    /**
     * 接口解密
     * @return mixed
     */
    public function cracker(Request $request)
    {
        $imei = $request->param('imei','');
        $interface = $request->baseUrl();
        $t = $request->param('t','');
//        if ($t+10 < time()){
//            throw new HttpResponseException($this->sendError(10000,'非法请求',403));
//        }
        $keystr = $request->param('key','');
        if ($keystr !== md5(substr($imei,-5) . substr($t,-5) . md5(strtolower($interface)) ) ) {
            throw new HttpResponseException($this->sendError(10000,'非法请求',403));
        }
    }
}
