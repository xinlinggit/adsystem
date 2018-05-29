<?php
namespace api\v1\controller;

use think\Config;
use think\Db;
use think\Log;
use think\Request;
use think\Loader;
use api\v1\model;

/**
 * Class Callback
 * @package app\demo\controller
 * @title 腾讯回调接口
 * @url /demo/callback
 * @desc  腾讯回调接口
 * @version beta
 * @return int errorno 请求成功标志
 * @return string message 错误提示信息
 * @return object data 返回数据
 * @readme
 */
class Callback
{
    public function index(Request $request)
    {
        $data = $request->post();
        if (!(isset($data['t']) && isset($data['sign']) && isset($data['event_type']) && isset($data['stream_id']))) {
            exit(500);
        }
        // 验证是否来自腾讯的回调
        $live_conf = Config::get('live_conf');
        $key = $live_conf['apikey'];
        if ($data['sign'] !== md5($key . $data['t'])) {
            exit(500);
        }
        
        $stream_id = $data['stream_id'];
        $event_type = $data['event_type'];
        $live = model\Live::get(['stream_id'=>$stream_id]);
        empty($live) && exit(500);

        if ( $event_type == 0 ) {
            $live->status=0;
            $live->end_time = date('Y-m-d H:i:s');
            $live->save();
//            Db::name('live')->where(['status'=>0,'check_status'=>0])
//                ->whereNotNull('end_time')->delete();
            if (empty( $live->getError() ))
                return json_encode(array('code' => 0));
        }
        elseif ($event_type == 1){
            $live->status=1;
            $live->start_time = date('Y-m-d H:i:s');
            $live->save();
            if (empty( $live->getError() ))
                return json_encode(array('code' => 0));
        }
        elseif ( $event_type == 100) {
            $duration = $data['end_time'] - $data['start_time'];
            $data['end_time'] = date('Y-m-d H:i:s',$data['end_time']);
            $data['start_time'] = date('Y-m-d H:i:s',$data['start_time']);
            if ($duration > 10) {
                $logconf = [
                    // 日志记录方式，内置 file socket 支持扩展
                    'type' => 'File',
                    // 日志保存目录
                    'path' => RUNTIME_PATH,
                    // 日志记录级别
                    'level' => [],
                ];
                Log::init($logconf);
                Log::write($data,'log');
                return json_encode(array('code' => 0));
            }
            else {
                //录制时长小于60s的不存储记录
                exit(500);
            }
        }
    }
    public function imindex(Request $request){
        
    }

}