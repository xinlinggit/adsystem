<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | User: ShouKun Liu  |  Email:24147287@qq.com  | Time:2017/3/5 9:58
// +----------------------------------------------------------------------
// | TITLE: 用户接口
// +----------------------------------------------------------------------
namespace api\btc\controller;

use api\common\controller\Api;
use api\v1\model;
use think\Db;
use think\Log;
use think\Request;
use think\File;
use think\Exception;
use think\Cache;

/**
 * Class Hangqing
 * @title 比特币行情接口
 * @url /btc/hangqing
 * @desc 比特币行情接口
 * @version 0.1
 * @return int errorno 请求成功标志
 * @return string message 错误提示信息
 * @return object data 返回数据
 * @readme
 */
class Hangqing extends Api
{
    protected $okcoin_cache_key = 'www.okcoin.com';
    protected $okcoin_url = 'https://www.okcoin.com/api/v1/ticker.do?symbol=btc_usd';
    protected $ret_msg = [
        500 => 'baidu search converted_rate failed',
        501 => 'target server is not available',
    ];
    // 允许访问的请求类型
    public $restMethodList = 'get|post';

    /**
     * 参数规则
     * @name 字段名称
     * @type 类型
     * @require 是否必须
     * @default 默认值
     * @desc 说明
     * @range 范围
     * @return array
     */
    public static function requestRules()
    {
        $rules = [
            //共用参数
            'all' => [],
            'postDonetask' => [
                'userid' => ['name' => 'userid', 'type' => 'int', 'require' => 'true', 'desc' => '用户唯一标识',],
                'taskid' => ['name' => 'taskid', 'type' => 'int', 'require' => 'true', 'desc' => '任务唯一标识',],
                'result' => ['name' => 'result', 'type' => 'file', 'require' => 'true', 'desc' => '任务完成结果截图',],
            ],

        ];
        //可以合并公共参数
        return array_merge(parent::requestRules(), $rules);
    }

    public static function responseRules()
    {
        $rules = [
            //共用参数
            'all' => [],
            'postDonetask' => [],
            'getList' => [
                'id' => ['name' => 'id', 'type' => 'int', 'desc' => '任务id',],
                'title' => ['name' => 'title', 'type' => 'string', 'desc' => '任务标题',],
                'price' => ['name' => 'price', 'type' => 'string', 'desc' => '价格',],
                'start_time' => ['name' => 'start_time', 'type' => 'string', 'desc' => '开始时间',],
                'deadline' => ['name' => 'deadline', 'type' => 'string', 'desc' => '截止时间',],
                'status' => ['name' => 'status', 'type' => 'string', 'desc' => '状态：-1：未开始该任务   0：已提交，审核中  1：已提交，审核未通过',],
            ],
        ];
        //可以合并公共参数
        return array_merge(parent::responseRules(), $rules);
    }

    public function getResponse(Request $request)
    {
        $converted_rate = $this->convertCurrency('usd','cny');
        if (!$converted_rate){
            return $this->sendError($no = 500, $this->ret_msg[$no], $no);
        }
        $data = $this->req_okcoin();
        if (!$data) {
            return $this->sendError($no = 501, $this->ret_msg[$no], $no);
        }
        $res = $this->format($data,$converted_rate);

        return $this->sendSuccess([$data,$res]);

    }
    protected function format($data,$converted_rate)
    {
        /*
          {
	        "date":"1410431279",
	        "ticker":{
		        "buy":"33.15",
		        "high":"34.15",
		        "last":"33.15",
		        "low":"32.05",
		        "sell":"33.16",
		        "vol":"10532696.39199642"
	            }
            }
            date: 返回数据时服务器时间
            buy: 买一价
            high: 最高价
            last: 最新成交价
            low: 最低价
            sell: 卖一价
            vol: 成交量(最近的24小时)
         */
        $res = [];
        $res['last'] = [
            'USD' => ($data['ticker']['last'] + 0.00),
            'CNY' => ($data['ticker']['last'] * $converted_rate)
        ];
        $res['buy'] = [
            'USD' => ($data['ticker']['buy'] + 0.00),
            'CNY' => ($data['ticker']['buy'] * $converted_rate)
        ];
        $res['sell'] = [
            'USD' => ($data['ticker']['sell'] + 0.00),
            'CNY' => ($data['ticker']['sell'] * $converted_rate)
        ];
        $res['high'] = [
            'USD' => ($data['ticker']['high'] + 0.00),
            'CNY' => ($data['ticker']['high'] * $converted_rate)
        ];
        $res['low'] = [
            'USD' => ($data['ticker']['low'] + 0.00),
            'CNY' => ($data['ticker']['low'] * $converted_rate)
        ];
        $res['vol'] = ($data['ticker']['low'] + 0.00);
        $res['rate'] = $converted_rate * 1.0;
        return $res;
    }

    protected function req_okcoin()
    {
        if ($data = Cache::get($this->okcoin_cache_key)) {
            return $data;
        }
        $okcoin_source = $this->okcoin_url;
        $data = http($okcoin_source);
        if (isset($data['errmsg'])) {
            Log::write(var_export($data), 'alert');
            return false;
        }
        Cache::set($this->okcoin_cache_key, $data, 60);
        return $data;
    }

    protected function convertCurrency($from, $to)
    {
        if ($converted_rate = Cache::get($from.'-'.$to)){
            return $converted_rate;
        }
        $data = file_get_contents("http://www.baidu.com/s?wd={$from}%20{$to}");
        preg_match("/<div>1\D*=(\d*\.\d*)\D*<\/div>/", $data, $converted);
        $converted_rate = preg_replace("/[^0-9.]/", "", $converted[1]);
        if (!$converted_rate){
            Log::write(date('Y-m-d H:i:s').' 百度搜索查询汇兑费率出错', 'alert');
            return false;
        }
        Cache::set($from.'-'.$to, $converted_rate, 180);
        return $converted_rate;
        //return number_format(round($converted, 3), 1);
    }

}