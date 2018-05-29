<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | User: ShouKun Liu  |  Email:24147287@qq.com  | Time:2017/3/5 9:58
// +----------------------------------------------------------------------
// | TITLE: 用户接口
// +----------------------------------------------------------------------
namespace api\v1\controller;

use api\common\controller\Api;
use api\v1\model;
use think\Db;
use think\Request;
use think\File;
use think\Exception;
use think\Cache;

/**
 * Class Task
 * @title 任务接口
 * @url /v1/task
 * @desc 与任务相关接口
 * @version 0.1
 * @return int errorno 请求成功标志
 * @return string message 错误提示信息
 * @return object data 返回数据
 * @readme
 */
class Task extends Api
{
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
            'all'=>[],
            'postDonetask'=>[
                'userid' => ['name' => 'userid', 'type' => 'int', 'require' => 'true', 'desc' => '用户唯一标识',],
                'taskid' => ['name' => 'taskid', 'type' => 'int', 'require' => 'true', 'desc' => '任务唯一标识',],
                'result' => ['name' => 'result', 'type' => 'file', 'require' => 'true', 'desc' => '任务完成结果截图',],
            ],
            'getList'=>[
                'id' => ['name' => 'id', 'type' => 'int', 'require' => 'false', 'desc' => '上划时传最后一条任务id',],
                'userid' => ['name' => 'userid', 'type' => 'int', 'require' => 'false', 'desc' => '用户ID，登录后传',],
                'limit' => ['name' => 'limit', 'type' => 'int', 'require' => 'false', 'desc' => '请求条数，默认10条',],
            ],
            'getOne'=>[
                'id' => ['name' => 'id', 'type' => 'int', 'require' => 'true', 'desc' => '任务id',],
                'userid' => ['name' => 'userid', 'type' => 'int', 'require' => 'false', 'desc' => '用户ID，登录后传',],
            ],
            'getDonelist'=>[
                'userid' => ['name' => 'userid', 'type' => 'int', 'require' => 'true', 'desc' => '用户ID，必须',],
                'id' => ['name' => 'id', 'type' => 'int', 'require' => 'false', 'desc' => '上划时传最后一条已完成任务id',],
                'limit' => ['name' => 'limit', 'type' => 'int', 'require' => 'false', 'desc' => '请求条数，默认10条',],
            ],

        ];
        //可以合并公共参数
        return array_merge(parent::requestRules(),$rules);
    }

    public static function responseRules(){
        $rules = [
            //共用参数
            'all'=>[],
            'postDonetask'=>[],
            'getList' => [
                'id' => ['name' => 'id', 'type' => 'int', 'desc' => '任务id',],
                'title' => ['name' => 'title', 'type' => 'string', 'desc' => '任务标题',],
                'price' => ['name' => 'price', 'type' => 'string', 'desc' => '价格',],
                'start_time' => ['name' => 'start_time', 'type' => 'string', 'desc' => '开始时间',],
                'deadline' => ['name' => 'deadline', 'type' => 'string', 'desc' => '截止时间',],
                'status' => ['name' => 'status', 'type' => 'string', 'desc' => '状态：-1：未开始该任务   0：已提交，审核中  1：已提交，审核未通过',],
            ],
            'getOne' => [
                'id' => ['name' => 'id', 'type' => 'int', 'desc' => '任务id',],
                'title' => ['name' => 'title', 'type' => 'string', 'desc' => '任务标题',],
                'description' => ['name' => 'description', 'type' => 'string', 'desc' => '任务描述',],
                'image' => ['name' => 'image', 'type' => 'string', 'desc' => '任务图片',],
                'price' => ['name' => 'price', 'type' => 'string', 'desc' => '价格',],
                'start_time' => ['name' => 'start_time', 'type' => 'string', 'desc' => '开始时间',],
                'deadline' => ['name' => 'deadline', 'type' => 'string', 'desc' => '截止时间',],
                'status' => ['name' => 'status', 'type' => 'string', 'desc' => '状态：-1：未开始该任务   0：已提交，审核中  1：已提交，审核未通过',],
                'reason' => ['name' => 'reason', 'type' => 'string', 'desc' => '理由：status=-1时使用,已提交，审核未通过的理由',],
            ],
            'getDonelist' => [
                'sum' => ['name' => 'sum', 'type' => 'int', 'desc' => '已完成任务总数',],
                'list.id' => ['name' => 'list.id', 'type' => 'int', 'desc' => '已完成任务id',],
                'list.title' => ['name' => 'list.title', 'type' => 'string', 'desc' => '任务标题',],
                'list.price' => ['name' => 'list.price', 'type' => 'string', 'desc' => '价格',],
                'list.create_time' => ['name' => 'list.start_time', 'type' => 'string', 'desc' => '提交任务时间',],
                'list.check_time' => ['name' => 'list.start_time', 'type' => 'string', 'desc' => '审核任务时间',],
            ],
        ];
        //可以合并公共参数
        return array_merge(parent::responseRules(),$rules);
    }


    /**
     * @title 任务列表接口  【可用】
     * @url /v1/task/list
     * @type get
     * @desc 指定任务ID提交已完成的任务接口
     * @param Request $request
     * @return object
     */
    public function getList(Request $request)
    {
        $userid = $request->get('userid');
        $id = $request->get('id');
        $limit = $request->get('limit');
        $task = new model\Task();
        $data = $task->getList($id,$limit,$userid);
        return $this->sendSuccess($data);
    }

    /**
     * @title 单条任务详情接口  【可用】
     * @url /v1/task/one
     * @type get
     * @desc 指定任务ID提交已完成的任务接口
     * @param Request $request
     * @return object
     */
    public function getOne(Request $request)
    {
        $userid = $request->get('userid');
        $id = $request->get('id');
        if (empty((int)$id))
            return $this->sendError(30010,'缺少参数',403);
        $task = new model\Task();
        return $this->sendSuccess($task->getOne($id,$userid));
    }

    /**
     * @title 我的已完成任务列表  【可用】
     * @url /v1/task/donelist
     * @type get
     * @desc 指定任务ID提交已完成的任务接口
     * @param Request $request
     * @return object
     */
    public function getDonelist(Request $request)
    {
        $userid = $request->get('userid');
        if (empty((int)$userid))
            return $this->sendError(30030,'缺少参数',403);
        $id = $request->get('id');
        $limit = $request->get('limit');
        $task = new model\Donetask();
        $data['sum'] = $task->where(['userid'=>$userid,'status'=>2])->count('id');
        $data['list'] = [];
        if ($data['sum']>0){
            $data['list'] = $task->myDone($userid,$id,$limit);
        }
        return $this->sendSuccess($data);
    }
    /**
     * @title 提交完成任务接口  【可用】
     * @url /v1/task/donetask
     * @type post
     * @desc 指定任务ID提交已完成的任务接口
     * @param Request $request
     * @return object
     */
    public function postDonetask(Request $request)
    {
        $data = $request->post();
        $result[] = $request->file('result');
        //@error_log(date('Y-m-d H:i:s'). ' |-info-| '. print_r($result, true) .  PHP_EOL , 3, LOG_PATH.'/'.__FUNCTION__.'_'.date('Ymd').'.log');
        if (empty($data['taskid']) || empty($data['userid']) || empty($result) )
            return $this->sendError(30020,'缺少参数',403);
        $donetask = new model\Donetask();
        //dump($data);dump($result);die;
        if (!empty($donetask->where(['taskid'=>$data['taskid'],'userid'=>$data['userid']])->whereIn('status',[0,2],'and')->find()))
            return $this->sendError(30021,'您已提交过该任务，请勿重复提交',403);

        $image = [];
        try{
            foreach($result as $file){
                if (!($file instanceof File))
                    return $this->sendError(30025,'上传文件不合法(可能是头信息设置的不对哦)',403);
                // 移动到框架应用根目录/public/folder/uploads/ 目录下
                $file->validate(['size'=>5120000,'ext'=>'jpeg,png,jpg,gif']);
                $info = $file->move(FOLDER_PATH . 'uploads',(int)(10000*microtime(true)));
                if($info){
                    $image[] =  $info->getSavename();
                }else{
                    // 上传失败获取错误信息
                    !empty($image) && array_walk($image,function($v,$k){
                        @unlink(FOLDER_PATH . 'uploads' . DS .$v);
                    });
                    @error_log(date('Y-m-d H:i:s'). ' |-error-| '. print_r($file->getError(), true) .  PHP_EOL , 3, LOG_PATH.'/'.__FUNCTION__.'_'.date('Ymd').'.log');
                    return $this->sendError(30022,$file->getError(),403);
                }
            }
        }catch (\ErrorException $e){
            return $this->sendError(30023,$e->getMessage(),403);
        }

        $data['result'] = implode(';',array_map(function ($v) use ($request){
            return '/folder' . '/uploads' . DS .$v;
        },$image));

        $info = $donetask->submitTask($data);
        if (!$info)
            return $this->sendError(30024,$donetask->getError(),403);
        return $this->sendSuccess();
    }
    

}