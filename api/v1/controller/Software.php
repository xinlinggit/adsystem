<?php
namespace api\v1\controller;
use api\common\controller\Api;
use api\v1\model;
use think\Config;
use think\Db;
use think\Exception;
use think\Request;
use think\Cache;
load_trait('controller/Controller');

/**
 * Class Software
 * @title app package信息接口
 * @url /v1/software
 * @version 0.1
 * @desc  用户接口,该返回字段为每次请求的格式。对应接口的返回值仅为data中的内容
 * @return int errorno 请求成功标志
 * @return string message 错误提示信息
 * @return object data 返回数据
 * @readme
 * @var \think\File
 */
class Software extends Api
{
    use \app\v1\traits\controller\Controller;
    // 允许访问的请求类型
    public $restMethodList = 'get|post|put';

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
            'all' => [
            ],
            'getResponse' => [
                'platform' => ['name' => 'platform', 'type' => 'int', 'require' => 'true', 'desc' => '平台 1:android  2:ios', ],
            ],
            'postApplog' => [
                'platform' => ['name' => 'platform', 'type' => 'int', 'require' => 'true', 'desc' => '平台 1:android  2:ios', ],
                'userid' => ['name' => 'userid', 'type' => 'int', 'require' => 'false', 'desc' => '用户id', ],
                'version_number' => ['name' => 'version_number', 'type' => 'int', 'require' => 'true', 'desc' => '版本号', ],
                'version_name' => ['name' => 'version_name', 'type' => 'string', 'require' => 'true', 'desc' => '版本名', ],
                'network_type' => ['name' => 'network_type', 'type' => 'int', 'require' => 'true', 'desc' => '网络状态 1：wifi  2：移动网络', ],
                'brand_type' => ['name' => 'brand_type', 'type' => 'int', 'require' => 'false', 'desc' => '品牌型号', ],
                'os_kernel' => ['name' => 'os_kernel', 'type' => 'int', 'require' => 'false', 'desc' => '内核版本', ],
                'description' => ['name' => 'description', 'type' => 'int', 'require' => 'true', 'desc' => '描述', ],
            ],
        ];
        //可以合并公共参数
        return array_merge(parent::requestRules(), $rules);
    }

    public static function responseRules()
    {
        $rules = [
            'all' =>[],
            'getResponse' => [
                'platform' => ['name' => 'platform', 'type' => 'int', 'desc' => '平台 1:android  2:ios', ],
                'version_number' => ['name' => 'version_number', 'type' => 'int', 'desc' => '版本号', ],
                'version_name' => ['name' => 'version_name', 'type' => 'string', 'desc' => '版本名', ],
                'update_state' => ['name' => 'update_state', 'type' => 'string', 'desc' => '更新说明', ],
                'update_time' => ['name' => 'update_time', 'type' => 'string', 'desc' => '更新时间', ],
                'force_update' => ['name' => 'force_update', 'type' => 'int', 'desc' => '强制更新 1:是 0:否', ],
                'package_url' => ['name' => 'package_url', 'type' => 'string', 'desc' => '软件包下载地址', ],
                ]
        ];
        return array_merge(parent::responseRules(), $rules);
    }

    /**
     * @title 更新接口  【可用】
     * @url /v1/software/
     * @type get
     * @param Request $request
     * @desc 查询是否有更新包
     * @return object data json数据
     */
    public function getResponse(Request $request)
    {
        $platform = request()->param('platform');
        if (empty($platform))
            return $this->sendError(50010,'缺少参数',403);
        else {
            $model = new model\Software();
            $data = $model->where('platform',$platform)
                ->order('update_time','desc')
                ->find();
            return $this->sendSuccess($data);
        }
    }
    /**
     * @title 轮播图接口  【不可用】
     * @url /v1/software/slideshow
     * @type get
     * @param Request $request
     * @desc 查询轮播图接口
     * @return object data json数据
     */
    public function getSlideshow(Request $request)
    {
        $model = new model\Slideshow();
        $data = $model->where('status',1)->order('id','desc')->select();
        return $this->sendSuccess($data);
    }

    /**
     * @title 日志回收接口  【可用】
     * @url /v1/software/applog
     * @type post
     * @param Request $request
     * @desc 日志回收接口
     * @return object data json数据
     */
    public function postApplog(Request $request)
    {
        $model = new model\Applog();
        $data = $request->post();
        $model->allowField(true)->save($data);
        return $this->sendSuccess();
    }

    /**
     * @title iOS是否上线接口  【可用】
     * @url /v1/software/online
     * @type get
     * @param Request $request
     * @desc iOS是否上线接口
     * @return object data json数据
     */
    public function getOnline(Request $request)
    {
        // 上线之后 display = 2
        $data = ['display'=>1,'version'=>'1.0'];

        return $this->sendSuccess($data);
    }


}