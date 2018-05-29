<?php
namespace api\common\controller;

use api\apilib\controller\BaseAuth;
use think\Controller;
use think\Config;
use think\exception\HttpResponseException;
use think\Hook;
use think\Request;
use think\Response;

/**
 * 基础API控制器
 * @package app\common\controller
 * @author qinxw
 * @update 2017-03-22
 * @var \think\File
 */
class Api extends Controller
{
    // 当前请求类型
    protected $method;
    // 当前资源类型
    protected $type;
    // 允许访问的请求类型
    public $restMethodList = 'get|post|put|delete|patch|head|options';
    //默认请求类型
    protected $restDefaultMethod = 'get';
    //允许响应的资源类型
    protected $restTypeList = 'html|xml|json|rss';
    //默认响应类型
    protected $restDefaultType = 'json';
    //默认错误提示语
    protected $restDefaultMessage = 'error';
    // REST允许输出的资源类型列表
    protected $restOutputType = [
        'xml' => 'application/xml',
        'json' => 'application/json',
        'html' => 'text/html',
    ];
    //响应数据
    public $errorno;
    public $message;
    public $data;
    //数据集合
    public $responseData;
    /**
     * 字段类型
     * @var array
     */
    public static $typeMaps = [
        'string' => '字符串',
        'int' => '整型',
        'float' => '浮点型',
        'boolean' => '布尔型',
        'date' => '日期',
        'array' => '数组',
        'fixed' => '固定值',
        'enum' => '枚举类型',
        'object' => '对象',
    ];
    
    // 重写构造函数
    protected function _initialize()
    {
        //判断是否开启权限验证
        $this->apiAuth = (config('api_auth')) ? $this->apiAuth : false;

        //前置钩子
        $request = Request::instance();
        Hook::listen('api_begin', $request);
        //  重写 rest 类
        // 资源类型检测
        $ext = $request->ext();
        if ('' == $ext) {
            // 自动检测资源类型
            $accept = $request->server('HTTP_ACCEPT');

            if (empty($accept) || stristr($accept, '*/*') ) {
                // 请求头中也未设置，使用默认值
                $this->type = $this->restDefaultType;
            }else{
                // 自动检测资源类型
                $this->type = $request->type();
            }
        } elseif (!preg_match('/(' . $this->restTypeList . ')$/i', $ext)) {
            // 资源类型非法 则用默认资源类型访问
            $this->type = $this->restDefaultType;
        } else {
            $this->type = $ext;
        }
        //设置响应类型
        $this->setRestType($this->type);

        //权限验证
        if ($this->apiAuth) $this->auth($request);
        // 请求方式检测
        $this->method = strtolower($request->method());
    }

    /**
     * 设置响应类型
     * @param $type
     * @return $this
     */
    public function setRestType($type = 'json')
    {
        $this->type = $type;
        return $this;
    }

    /**
     * 设置响应数据
     * @param int    $errorno
     * @param string $message
     * @param array  $data
     * @return $this
     */
    public function setResponseArr($errorno = 0, $message = '', $data = NULL)
    {
        empty($data) && $data=NULL;
        $this->errorno = $errorno;
        $this->restDefaultMessage = (empty($this->errMap[$errorno])) ? $this->restDefaultMessage : $this->errMap[$errorno];
        $this->message = !empty($message) ? $message : $this->restDefaultMessage;
        $this->data = $data;
        $this->responseData['errorno'] = $this->errorno;
        $this->responseData['message'] = $this->message;
        $this->responseData['data'] = $this->data;
        return $this;
    }

    /**
     * 获取响应数据
     * @return mixed
     */
    public function getResponseArr()
    {
        return $this->responseData;
    }

    /**
     * 非法操作响应
     * @return \think\Response
     */
    protected function notMethod()
    {
        return $this->response(['errorno' => 403, 'message' => 'not method!', 'data' => NULL], $this->type, 403);
    }

    /**
     * 响应
     * @access protected
     * @param mixed   $data 要返回的数据
     * @param String  $type 返回类型 JSON XML
     * @param integer $code HTTP状态码
     * @return Response
     */
    protected function response($data, $type = 'json', $code = 200)
    {
        $data = (empty($data)) ? $this->getResponseArr() : $data;
        $type = (empty($type)) ? $this->type : $type;
        return Response::create($data, $type, $code);
    }

    /**
     * 错误响应
     * @param int    $errorno
     * @param string $message
     * @param int    $code
     * @return Response
     */
    public function sendError($errorno = 0, $message = '', $code = 400)
    {
        if (Config::has('errorno_map.'.$errorno)){
            $errorno_map = Config::get('errorno_map.'.$errorno);
            if (preg_match('/[%u|%s]/',$errorno_map)){
                $message = sprintf($errorno_map,$message);
            }else{
                $message = $errorno_map;
            }
        }

        return $this->setResponseArr($errorno, $message)->response('', '', $code);
    }

    /**
     * 正确响应
     * @param     $data
     * @param int $code
     * @return Response
     */
    public function sendSuccess($data = NULL, $code = 200)
    {
        return $this->setResponseArr(0, 'success', $data)->response('', '', $code);
    }
    
    


    /**
     * 请求规则
     * @name 字段名称
     * @type 字段类型
     * @require 是否必须
     * @default 默认值
     * @desc 说明
     * @range 范围
     * @return array
     */
    public static function requestRules()
    {
        $rules = [];
        return $rules;
    }

    /**
     * 返回规则
     * @name 字段名称
     * @type 字段类型
     * @require 是否必须
     * @default 默认值
     * @desc 说明
     * @range 范围
     * @return array
     */
    public static function responseRules()
    {
        $rules = ['all' =>[]];
        return $rules;
    }

    /**
     * 返回码
     * @name 字段名称
     * @type 字段类型
     * @require 是否必须
     * @default 默认值
     * @desc 说明
     * @range 范围
     * @return array
     */
    public static function responseCodes()
    {
        $rules = ['all' =>[]];
        return $rules;
    }

    //是否权限验证
    public $apiAuth = false;

    //业务错误码的映射表
    public $errMap = [
        0 => 'success',//没有错误
        1001 => '参数错误',
        9999 => '自定义错误'//让程序给出的自定义错误
    ];


    /**
     * 具体执行
     * @param Request $request
     * @param         $fun
     * @return mixed
     * @throws \Exception
     */
    public function init(Request $request, $fun = '')
    {
        // 判断接口是否允许该方式接口
        if (false === stripos($this->restMethodList, $this->method)) {
            $this->setResponseArr(403, 'not method!');
            return $this->notMethod();
        }
        /* * * * * * * * * * * * * * * * * * * * * * * * * * *  * *
         以下注释部分为原框架的写法

        $action = $this->method . 'Response';
        return $this->$action($request);

         * * * * * * * * * * * * * * * * * * * * * * * * * * * * */


        /* 以下为修改后的写法，完美兼容原框架的各项功能(生成文档的功能已作出同步修改) */
        // 路由规则 => 'user/login/[:fun]' => ['demo/User/init',]
        // 注：需要在路由规则中添加可选参数 fun
        /* 修改版 start     */
        if ($fun === '') {
            $action = $this->method . 'Response';
        } else {
            $action = $this->method . $fun;
        }
        if (method_exists($this, $action)) {
            return $this->$action($request);
        } else {
            // 抛出异常
            // throw new \Exception('error action :' . $fun );
            return $this->notMethod();
        }
        /* 修改版 end    @edit by liuw */

    }

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
    public static function setRules()
    {
        return [
            'all' => [
                //全部
            ],
            'get' => [

            ],
            'post' => [

            ],
            'put' => [

            ],
            'delete' => [

            ],
            'patch' => [

            ],
            'head' => [

            ],
            'options' => [

            ],
        ];

    }

    /**
     * 验证
     * @param Request $request
     * @return bool
     */
    public function auth(Request $request)
    {
        $BaseAuth = new BaseAuth();
        if ($BaseAuth->auth($request) == false) {
            $this->errCode = $BaseAuth->error;
            throw new HttpResponseException($this->setResponseArr($this->errCode, 'authentication Failed')->response('', '', 403));
        } else {
            return true;
        }
    }


    public function __destruct()
    {
        $request = Request::instance();
        Hook::listen('api_end', $request);
    }

    // |====================================
    // |具体响应子类重写
    // |====================================

    /**
     * @title GET的响应
     * @desc GET的描述
     * @type get
     * @readme /doc/md/method.md
     * @param Request $request
     * @return string message 错误信息
     * @return int errCode 错误号
     */
    public function getResponse(Request $request)
    {
        return $this->setResponseArr(0, 'Default  GET Response!')->response('', '', 403);
    }

    /**
     * @title POST的响应
     * @desc POST的描述
     * @type post
     * @readme /doc/md/method.md
     * @param Request $request
     * @return string message 错误信息
     * @return int errCode 错误号
     */
    public function postResponse(Request $request)
    {
        return $this->setResponseArr(0, 'Default  POST Response!')->response('', '', 403);
    }

    /**
     * @title PUT的响应
     * @desc PUT的描述
     * @readme /doc/md/method.md
     * @param Request $request
     * @return string message 错误信息
     * @return int errCode 错误号
     */
    public function putResponse(Request $request)
    {
        return $this->setResponseArr(0, 'Default  PUT Response!')->response('', '', 403);
    }

    /**
     * @title DELETE的响应
     * @desc DELETE的描述
     * @readme /doc/md/method.md
     * @param Request $request
     * @return string message 错误信息
     * @return int errCode 错误号
     */
    public function deleteResponse(Request $request)
    {
        return $this->setResponseArr(0, 'Default  DELETE Response!')->response('', '', 403);
    }

    /**
     * @title PATCH的响应
     * @desc PATCH的描述
     * @readme /doc/md/method.md
     * @param Request $request
     * @return string message 错误信息
     * @return int errCode 错误号
     */
    public function patchResponse(Request $request)
    {
        return $this->setResponseArr(0, 'Default  PATH Response!')->response('', '', 403);
    }

    /**
     * @title HEAD的响应
     * @desc HEAD的描述
     * @readme /doc/md/method.md
     * @param Request $request
     * @return string message 错误信息
     * @return int errCode 错误号
     */
    public function headResponse(Request $request)
    {
        return $this->setResponseArr(0, 'Default  HEAD Response!')->response('', '', 403);
    }

    /**
     * @title OPTIONS的响应
     * @desc OPTIONS的描述
     * @readme /doc/md/method.md
     * @param Request $request
     * @return string message 错误信息
     * @return int errCode 错误号
     */
    public function optionsResponse(Request $request)
    {
        return $this->setResponseArr(0, 'Default  OPTIONS Response!')->response('', '', 403);
    }

    /**
     * RESTFUL 接口调用
     * @access public
     * @param string $action 方法名
     * @return mixed
     * @throws \Exception
     */
    public function _empty($action)
    {
        // 判断接口是否允许该方式接口
        if (false === stripos($this->restMethodList, $this->method)) {
            return $this->notMethod();
        }

        $default_action = Config::get('default_action');
        $default_action = Config::get('url_convert') ? strtolower($default_action) : $default_action;

        if ($action == '' || $action == $default_action) {
            $action = $this->method . 'Response';
        } else {
            $action = $this->method . $action;
        }

        if (method_exists($this, $action)) {
            return call_user_func([$this, $action], Request::instance());
        } else {
            // 抛出异常
            // throw new \Exception('error action :' . $fun );
            return $this->notMethod();
        }
    }
}