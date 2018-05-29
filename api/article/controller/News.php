<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | User: ShouKun Liu  |  Email:24147287@qq.com  | Time:2017/3/5 9:58
// +----------------------------------------------------------------------
// | TITLE: 用户接口
// +----------------------------------------------------------------------
namespace api\article\controller;

use api\article\model\Article;
use api\common\controller\Api;
use api\v1\model;
use think\Db;
use think\Log;
use think\Request;
use think\File;
use think\Exception;
use think\Cache;

/**
 * Class News
 * @title 文章资讯接口
 * @url /article/news
 * @desc 与任务相关接口
 * @version 0.1
 * @return int errorno 请求成功标志
 * @return string message 错误提示信息
 * @return object data 返回数据
 * @readme
 */
class News extends Api
{
    protected $cnfol_article_api = 'https://www.okcoin.com/api/v1/ticker.do?symbol=btc_usd';
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
            ],
        ];
        //可以合并公共参数
        return array_merge(parent::responseRules(), $rules);
    }

    public function getResponse(Request $request)
    {
        $article = new Article();
        $data = $article->top5();
        return $this->sendSuccess($data);
    }

}