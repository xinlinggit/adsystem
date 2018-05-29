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
 * Class User
 * @title 用户接口
 * @url /v1/user
 * @version 0.1
 * @desc  用户接口,该返回字段为每次请求的格式。对应接口的返回值仅为data中的内容<br>请求参数中加密参数 imie=序列号,t=时间戳,key=md5(imie后5位+t后5位+md5(uri:eg."/v1/user/income" 当前URL地址without query 小写))
 * @return int errorno 请求成功标志
 * @return string message 错误提示信息
 * @return object data 返回数据
 * @readme
 * @var \think\File
 */
class User extends Api
{
    use \api\v1\traits\controller\Controller;
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
            'getSmsverify' => [
                'tel' => ['name' => 'tel', 'type' => 'string', 'require' => 'true', 'desc' => '接收验证码的手机号',],
                'action' => ['name' => 'action', 'type' => 'int', 'require' => 'false', 'desc' => '本次验证要进行的行为，0:注册(默认) 1:重置密码',],
            ],
            'postRegister' => [
                'tel' => ['name' => 'tel', 'type' => 'string', 'require' => 'true', 'desc' => '用户电话',],
                'verifycode' => ['name' => 'verifycode', 'type' => 'int', 'require' => 'true', 'desc' => '验证码',],
                'password' => ['name' => 'password', 'type' => 'string', 'require' => 'true', 'desc' => '密码',],
                'nickname' => ['name' => 'nickname', 'type' => 'string', 'require' => 'true', 'desc' => '用户昵称', ],
                'jpush_id' => ['name' => 'jpush_id', 'type' => 'string', 'require' => 'true', 'desc' => '极光推送sdk注册id',],
            ],
            'postLogin' => [
                'tel' => ['name' => 'tel', 'type' => 'string', 'require' => 'true', 'desc' => '用户电话', ],
                'password' => ['name' => 'password', 'type' => 'string', 'require' => 'true','desc' => '密码',],
                'jpush_id' => ['name' => 'jpush_id', 'type' => 'string', 'require' => 'true', 'desc' => '极光推送sdk注册id',],
            ],
            'postLogout' => [
                'id' => ['name' => 'id', 'type' => 'int', 'require' => 'true', 'desc' => '用户id',],
            ],
//            'postOtherLogin' => [
//                'uid' => ['name' => 'uid', 'type' => 'string', 'require' => 'true', 'desc' => '友盟生成的用户唯一标识', ],
//                'username' => ['name' => 'username', 'type' => 'string', 'require' => 'true', 'desc' => '用户昵称', ],
//                'avatar' => ['name' => 'avatar', 'type' => 'string', 'require' => 'true', 'desc' => '用户头像URL', ],
//            ],
            'postResetpwd' => [
                'tel' => ['name' => 'tel', 'type' => 'string', 'require' => 'true', 'desc' => '用户电话', ],
                'verifycode' => ['name' => 'verifycode', 'type' => 'int', 'require' => 'true', 'desc' => '验证码',],
                'password' => ['name' => 'password', 'type' => 'string', 'require' => 'true','desc' => '密码',],
                'jpush_id' => ['name' => 'jpush_id', 'type' => 'string', 'require' => 'true', 'desc' => '极光推送sdk注册id',],
            ],
            'getIncome' => [
                'id' => ['name' => 'id', 'type' => 'int', 'require' => 'true', 'desc' => '用户id',],
            ],
            'getProfile' => [
                'id' => ['name' => 'id', 'type' => 'int', 'require' => 'true', 'desc' => '用户id',],
            ],
            'postSetbank' => [
                'id' => ['name' => 'id', 'type' => 'int', 'require' => 'true', 'desc' => '用户id',],
                'card' => ['name' => 'card', 'type' => 'string', 'require' => 'true', 'desc' => '银行卡',],
                'name' => ['name' => 'name', 'type' => 'string', 'require' => 'true', 'desc' => '账户名',],
                'open_bank' => ['name' => 'open_bank', 'type' => 'string', 'require' => 'true', 'desc' => '开户行',],
            ],
            'postEncashment' => [
                'id' => ['name' => 'id', 'type' => 'int', 'require' => 'true', 'desc' => '用户id',],
                'card' => ['name' => 'card', 'type' => 'string', 'require' => 'true', 'desc' => '银行卡',],
                'money' => ['name' => 'money', 'type' => 'string', 'require' => 'true', 'desc' => '提现金额',],
                'name' => ['name' => 'name', 'type' => 'string', 'require' => 'true', 'desc' => '账户名',],
                'open_bank' => ['name' => 'open_bank', 'type' => 'string', 'require' => 'true', 'desc' => '开户行',],
            ],
            'getEncashment' => [
                'id' => ['name' => 'id', 'type' => 'int', 'require' => 'true', 'desc' => '用户id',],
            ],
        ];
        //可以合并公共参数
        return array_merge(parent::requestRules(), $rules);
    }

    public static function responseRules()
    {
        $rules = [
            'all' =>[],
            'getSmsverify' => [],
            'postRegister' => [
                'id' => ['name' => 'id','type' => 'int','desc' => '用户ID(唯一标识)',],
                'tel' => ['name' => 'tel', 'type' => 'string', 'desc' => '手机',],
                'nickname' => ['name' => 'nickname', 'type' => 'string', 'desc' => '用户昵称',],
                'username' => ['name' => 'username', 'type' => 'string', 'desc' => '用户名(暂时无用)',],
                'email' => ['name' => 'email', 'type' => 'string', 'desc' => '用户邮箱',],
                'avatar' => ['name' => 'avatar', 'type' => 'string', 'desc' => '用户头像',],
                'alipay' => ['name' => 'alipay', 'type' => 'string', 'desc' => '支付宝',],
                'bank_card' => ['name' => 'bank_card', 'type' => 'string', 'desc' => '银行卡',],
                'open_bank' => ['name' => 'open_bank', 'type' => 'string', 'desc' => '银行卡开户行',],
                'card_name' => ['name' => 'card_name', 'type' => 'string', 'desc' => '银行卡姓名',],
                'account' => ['name' => 'account', 'type' => 'string', 'desc' => '账户余额',],
                'finished' => ['name' => 'finished', 'type' => 'int', 'desc' => '完成任务数',],
            ],
            'postLogin' => [
                'id' => ['name' => 'id','type' => 'int','desc' => '用户ID(唯一标识)',],
                'tel' => ['name' => 'tel', 'type' => 'string', 'desc' => '手机',],
                'nickname' => ['name' => 'nickname', 'type' => 'string', 'desc' => '用户昵称',],
                'username' => ['name' => 'username', 'type' => 'string', 'desc' => '用户名(暂时无用)',],
                'email' => ['name' => 'email', 'type' => 'string', 'desc' => '用户邮箱',],
                'avatar' => ['name' => 'avatar', 'type' => 'string', 'desc' => '用户头像',],
                'alipay' => ['name' => 'alipay', 'type' => 'string', 'desc' => '支付宝',],
                'bank_card' => ['name' => 'bank_card', 'type' => 'string', 'desc' => '银行卡',],
                'open_bank' => ['name' => 'open_bank', 'type' => 'string', 'desc' => '银行卡开户行',],
                'card_name' => ['name' => 'card_name', 'type' => 'string', 'desc' => '银行卡姓名',],
                'account' => ['name' => 'account', 'type' => 'string', 'desc' => '账户余额',],
                'finished' => ['name' => 'finished', 'type' => 'int', 'desc' => '完成任务数',],
            ],
            'postResetpwd' => [
                'id' => ['name' => 'id','type' => 'int','desc' => '用户ID(唯一标识)',],
                'tel' => ['name' => 'tel', 'type' => 'string', 'desc' => '手机',],
                'nickname' => ['name' => 'nickname', 'type' => 'string', 'desc' => '用户昵称',],
                'username' => ['name' => 'username', 'type' => 'string', 'desc' => '用户名(暂时无用)',],
                'email' => ['name' => 'email', 'type' => 'string', 'desc' => '用户邮箱',],
                'avatar' => ['name' => 'avatar', 'type' => 'string', 'desc' => '用户头像',],
                'alipay' => ['name' => 'alipay', 'type' => 'string', 'desc' => '支付宝',],
                'bank_card' => ['name' => 'bank_card', 'type' => 'string', 'desc' => '银行卡',],
                'open_bank' => ['name' => 'open_bank', 'type' => 'string', 'desc' => '银行卡开户行',],
                'card_name' => ['name' => 'card_name', 'type' => 'string', 'desc' => '银行卡姓名',],
                'account' => ['name' => 'account', 'type' => 'string', 'desc' => '账户余额',],
                'finished' => ['name' => 'finished', 'type' => 'int', 'desc' => '完成任务数',],
            ],
//            'postOtherLogin' => [
//                'uid' => ['name' => 'uid', 'type' => 'string', 'require' => 'true', 'desc' => '友盟生成的用户唯一标识', ],
//                'username' => ['name' => 'username', 'type' => 'string', 'require' => 'true', 'desc' => '用户昵称', ],
//                'avatar' => ['name' => 'avatar', 'type' => 'string', 'require' => 'true', 'desc' => '用户头像URL', ],
//            ],
            'postLogout' => [],
            'getIncome' => [
                'money' => ['name' => 'money', 'type' => 'string', 'desc' => '金额',],
                'plus_or_minus' => ['name' => 'plus_or_minus', 'type' => 'int', 'desc' => '收入or支出',],
                'description' => ['name' => 'description', 'type' => 'string', 'desc' => '说明',],
                'create_time' => ['name' => 'create_time', 'type' => 'string', 'desc' => '交易时间',],
            ],
            'getProfile' => [
                'account' => ['name' => 'account', 'type' => 'string', 'desc' => '账户余额',],
                'finished' => ['name' => 'finished', 'type' => 'int', 'desc' => '完成任务数',],
            ],
            'postSetbank' => [],
            'postEncashment' => [],
            'getEncashment' => [
                'status' => ['name' => 'status', 'type' => 'int', 'desc' => '状态  0：请等待   1：已受理  2：处理完成  3：可开始新一次提现',],
                'is_success' => ['name' => 'is_success', 'type' => 'int', 'desc' => '是否成功   -1:无用字段   1：失败  2：成功',],
                'remarks' => ['name' => 'remarks', 'type' => 'string', 'desc' => '提现结果说明',],
            ],
        ];
        return array_merge(parent::responseRules(), $rules);
    }


    /**
     * @title 短信验证码接口 【可用】
     * @url /v1/user/smsverify
     * @type get
     * @desc 获取短信验证码 允许每个手机号3分钟获取一次
     * @param Request $request
     * @return int verifycode 短信验证码
     */
    public function getSmsverify(Request $request)
    {
        $tel = $request->get('tel');
        if (Cache::has('verifycode_'.$tel))
            return $this->sendError(20010,'验证码获取间隔不少于3分钟',403);
        $action = $request->get('action/d',0);//action=>0:注册 1:重置密码
        $telPatten1 = '/^(1(([34578][0-9])|(47)|[8][0126789]))\d{8}$/';//国内手机号
        $telPatten2 = '/^([5|6|8|9])\d{7}$/';//香港手机号
        if ( preg_match($telPatten1,$tel) )
            $info['mobile'] = $tel;
        elseif ( preg_match($telPatten2,$tel) )
            $info['mobile'] = '00852' . $tel;
        else
            return $this->sendError(20011,'请输入正确手机号',403);
        
        if ($action){
            if (empty( model\User::get(['tel'=>mobileEncode($tel)]) ))
                return $this->sendError(20012,'这个手机号没有注册过,立即注册吧',403);
        }else{
            if (!empty( model\User::get(['tel'=>mobileEncode($tel)]) ))
                return $this->sendError(20013,'这个手机号已经注册过,换一个吧',403);
        }

        $code = rand(100000,999999);
        // todo 此处发送验证码到对应手机号
        $info['content'] = $code . "(动态验证码)，此验证码只用于任务宝验证，请在5分钟内输入，切勿告诉他人，防止账号被盗。";
        $res = sendSmsAction($info);
        if ($res['flag']==0){
            Cache::set('verifycode_'.$tel,$code,160);
            return $this->sendSuccess();
        }
        else{
            // 短信服务器返回的错误码 错误信息
            return $this->sendError((int)$res['flag'],$res['msg'],403);
        }

    }


    /**
     * @title 用户注册接口 【可用】【加密请求】
     * @url /v1/user/register
     * @type post
     * @desc 注册新用户
     * @readme
     * @param Request $request
     * @return object
     */
    public function postRegister(Request $request){
        $this->cracker($request);
        $verifycode = $request->post('verifycode');
        $tel = $request->post('tel');
        $nickname = $request->post('nickname');
        $password = $request->post('password');
        $jpush_id = $request->post('jpush_id');

        if ($verifycode === NULL || $tel === NULL || $password === NULL || $nickname === NULL || $jpush_id === NULL)
            return $this->sendError(20020,'缺少参数',403);
        //todo 检验手机短信验证码
        if (!Cache::has('verifycode_'.$tel))
            return $this->sendError(20021,'验证码过期',403);
        elseif ( $verifycode != Cache::get('verifycode_'.$tel))
            return $this->sendError(20022,'验证码不正确,再看看验证码短信啦',403);

        $telPatten1 = '/^(1(([34578][0-9])|(47)|[8][0126789]))\d{8}$/';//国内手机号 13655044261
        $telPatten2 = '/^([5|6|8|9])\d{7}$/';//香港手机号
        if ( ! preg_match($telPatten1,$tel) && ! preg_match($telPatten2,$tel) )
            return $this->sendError(20023,'请输入正确手机号',403);
        elseif (! empty(model\User::get(['tel'=>$tel])))
            return $this->sendError(20024,'这个手机号已经注册过,换一个吧',403);

        $usernmPatten = '/^[a-zA-Z0-9\x{4e00}-\x{9fa5}]{1,14}$/u';
        if (! preg_match($usernmPatten,$nickname))
            return $this->sendError(20025,'请输入规范的昵称哦',403);

        // 验证通过，开始写库
        $user = new model\User();
        $info = $user->adduser(['tel'=>$tel,'password'=>$password,'nickname'=>$nickname,'jpush_id'=>$jpush_id]);
        if (!$info)
            return $this->sendError(20027,$user->getError(),403);
        return $this->sendSuccess($user->get($info));
    }

    /**
     * @title 用户登录接口 【可用】【加密请求】
     * @url /v1/user/login
     * @type post
     * @desc 用户登录
     * @readme
     * @param Request $request
     * @return object
     */
    public function postLogin(Request $request)
    {
        $this->cracker($request);
        $data['tel'] = $request->post('tel');
        $data['password'] = $request->post('password');
        $jpush_id = $request->post('jpush_id');
        $user = model\User::get(['tel'=>mobileEncode($data['tel'])]);
        if (empty($user))
            return $this->sendError(20030,'这个手机号没有注册过，立即注册吧',201);
        elseif ($user->getData('password') !== $data['password']){
            if ($user->getData('wrong_times') === 0)
                return $this->sendError(20031,'错误次数超过最大限制,建议重置密码',201);
            else{
                $user->dec('wrong_times');
                $user->isUpdate(true)->save();
                return $this->sendError(20032,"密码有误，您还有".$user->getData('wrong_times')."次机会",201);
            }
        }
        else{
            $user->wrong_times = 5;
            $user->last_login_time = date('Y-m-d H:i:s');
            $user->is_logout = 0;
            if (!empty($user->jpush_id) && $user->jpush_id != $jpush_id){
                //执行互踢推送
                @jpush_kick($user->jpush_id,$user->id);
            }
            \db('user')->where('id<>'.$user->id)->where('jpush_id',$jpush_id)->update(['jpush_id'=>null]);
            $user->jpush_id = $jpush_id;
            $user->isUpdate(true)->save();
            @jpush_history($user->id);
            return $this->sendSuccess($user);
        }
    }

    /**
     * @title 找回(重置)密码 【可用】【加密请求】
     * @url /v1/user/resetpwd
     * @type post
     * @desc 修改用户资料接口
     * @param \think\Request $request
     * @return object
     */
    public function postResetpwd(Request $request) {
        $this->cracker($request);
        $verifycode = $request->post('verifycode');
        $tel = $request->post('tel');
        $password = $request->post('password');
        $jpush_id = $request->post('jpush_id');
        if ($verifycode == NULL || $tel == NULL || $password == NULL || $jpush_id === NULL){
            return $this->sendError(20050,'缺少参数',403);
        }

        // 检验手机短信验证码
        if (!Cache::has('verifycode_'.$tel)){
            return $this->sendError(20051,'验证码过期',403);
        }
        elseif ( $verifycode != Cache::get('verifycode_'.$tel)){
            return $this->sendError(20052,'验证码不正确,再看看验证码短信啦',403);
        }

        $telPatten1 = '/^(1(([34578][0-9])|(47)|[8][0126789]))\d{8}$/';//国内手机号
        $telPatten2 = '/^([5|6|8|9])\d{7}$/';//香港手机号
        if ( ! preg_match($telPatten1,$tel) && ! preg_match($telPatten2,$tel) )
            return $this->sendError(20053,'请输入正确手机号',403);
        $user = model\User::get(['tel'=>mobileEncode($tel)]);
        if ( empty($user) )
            return $this->sendError(20054,'这个手机号没有注册过，立即注册吧',403);

        if (false !== $user->resetpwd($password)){
            $user->wrong_times = 5;
            $user->last_login_time = date('Y-m-d H:i:s');
            $user->is_logout = 0;
            if (!empty($user->jpush_id) && $user->jpush_id != $jpush_id){
                //执行互踢推送
                @jpush_kick($user->jpush_id,$user->id);
            }
            \db('user')->where('id<>'.$user->id)->where('jpush_id',$jpush_id)->update(['jpush_id'=>null]);
            $user->jpush_id = $jpush_id;
            $user->isUpdate(true)->save();
            @jpush_history($user->id);
            return $this->sendSuccess($user);
        }
        return $this->sendError(20055,$user->getError(),403);

    }

    /**
     * @title 获取用户明细接口 【可用】【加密请求】
     * @url /v1/user/income
     * @type get
     * @desc 获取用户明细接口
     * @param Request $request
     * @return object
     */
    public function getIncome(Request $request){
        $this->cracker($request);
        $id = $request->get('id');
        $income = new model\Income();
        return $this->sendSuccess($income->where(['userid'=>$id])->order('id desc')->select());
    }

    /**
     * @title 获取用户余额任务数量接口 【可用】【加密请求】
     * @url /v1/user/profile
     * @type get
     * @desc 获取用户余额任务数量接口
     * @param Request $request
     * @return object
     */
    public function getProfile(Request $request){
        $this->cracker($request);
        $id = $request->get('id');
        $user = new model\Userinfo();
        $res = $user->field('account,finished')->where(['id'=>$id])->find();
        return $this->sendSuccess($res);
    }

    /**
     * @title 用户退出接口 【可用】
     * @url /v1/user/logout
     * @type post
     * @desc 用户退出接口，修改状态，不在推送
     * @param Request $request
     * @return object
     */
    public function postLogout(Request $request){
        $id = $request->post('id');
        $user = model\User::get(['User.id'=>$id]);
        if (empty($user)){
            return $this->sendError(20061,'此用户不存在',403);
        }
        $user->is_logout = 1;
        $user->isUpdate(true)->save();
        return $this->sendSuccess();
    }

    /**
     * @title 用户设置银行卡信息 【可用】
     * @url /v1/user/setbank
     * @type post
     * @desc 用设置银行卡信息:银行卡号,卡名
     * @param Request $request
     * @return object
     */
    public function postSetbank(Request $request){
        $this->cracker($request);
        $id = $request->post('id');
        $bank_card = $request->post('card');
        $open_bank = $request->post('open_bank');
        $name = $request->post('name');
        if ($id == NULL || $bank_card == NULL || $name === NULL){
            return $this->sendError(20062,'缺少参数',403);
        }
        $user = model\User::get(['User.id'=>$id]);
        if (empty($user)){
            return $this->sendError(20061,'此用户不存在',403);
        }
        if (!preg_match('/[1-9]{1}(\d{14,18})/',$bank_card)){
            return $this->sendError(20063,'银行卡号无效',403);
        }
        if (!preg_match('/[\x{4e00}-\x{9fa5}]{4,30}/u',$open_bank)){
            return $this->sendError(20064,'银行卡开户行称无效',403);
        }
        if (!preg_match('/[\x{4e00}-\x{9fa5}]{2,6}/u',$name)){
            return $this->sendError(20065,'银行卡账户名称无效',403);
        }
        $userinfo = new model\Userinfo();
        $rt = $userinfo->setbank(['bank_card'=>$bank_card,'card_name'=>$name,'open_bank'=>$open_bank],$id);
        if ($rt || 0 === $rt){
            return $this->sendSuccess();
        }
        return $this->sendError(20065,$userinfo->getError(),403);
    }

    /**
     * @title 用户提现接口 【可用】
     * @url /v1/user/encashment
     * @type post
     * @desc 用户提现接口，接收用户ID,提现金额,银行卡号,卡名
     * @param Request $request
     * @return object
     */
    public function postEncashment(Request $request){
        $this->cracker($request);
        $id = $request->post('id');
        $money = $request->post('money');
        $bank_card = $request->post('card');
        $name = $request->post('name');
        $open_bank = $request->post('open_bank');
        if ($id == NULL || $money == NULL || $bank_card == NULL || $name === NULL || $open_bank === NULL){
            return $this->sendError(20070,'缺少参数',403);
        }
        $user = model\User::get(['User.id'=>$id]);
        if (empty($user)){
            return $this->sendError(20071,'此用户不存在',403);
        }
        if ($money < 100 || $user->account < $money ){
        //if ($user->account < $money ){
            return $this->sendError(20072,'提现金额有误',403);
        }
        if (!preg_match('/[1-9]{1}(\d{14,18})/',$bank_card)){
            return $this->sendError(20073,'银行卡号无效',403);
        }
        if (!preg_match('/[\x{4e00}-\x{9fa5}]{2,6}/u',$name)){
            return $this->sendError(20074,'银行卡账户名称无效',403);
        }
        if (!preg_match('/[\x{4e00}-\x{9fa5}]{4,30}/u',$open_bank)){
            return $this->sendError(20075,'银行卡开户行称无效',403);
        }
        if ($bank_card != $user->bank_card || $name != $user->card_name){
            return $this->sendError(20076,'银行卡账户与当前设置不匹配',403);
        }
        $encashment = new model\Encashment();
        if ($encashment->where('userid',$id)->whereNull('is_success')->find()){
            return $this->sendError(20077,'当前有进行中的提现申请，无法提交',403);
        }
        $data = ['userid'=>$id,'bank_card'=>$bank_card,'open_bank'=>$open_bank,'money'=>$money,'name'=>$name];
        $info = $encashment->apply($data);
        if (!$info)
            return $this->sendError(20078,$encashment->getError(),403);
        return $this->sendSuccess();
    }

    /**
     * @title 用户查询提现状态 【可用】
     * @url /v1/user/encashment
     * @type get
     * @desc 用户提现查询接口
     * @param Request $request
     * @return object
     */
    public function getEncashment(Request $request){
        $this->cracker($request);
        $id = $request->get('id');
        $encashment = new model\Encashment();
        $info = $encashment->where(['userid'=>$id])->field('id,money,status,is_success,remarks')->order('id desc')->find();
        if (!empty($info) && 2 === $info->status){
            $encashment->update(['id'=>$info->id,'status'=>3]);
        }
        return $this->sendSuccess($info);
    }

    /**
     * @title 修改用户资料接口 【不可用】
     * @url /v1/user/edit
     * @type post
     * @desc 修改用户资料接口
     * @param Request $request
     * @return object
     */
    public function postEdit(Request $request)
    {
        $userid = $request->post('userid/d');
        $data = $request->except('userid');
        if ($userid === null && empty($data))
            return $this->sendError(20040,'缺少参数',403);
        
        $userinfo = model\UserInfo::get($userid);
        if (empty($userinfo))
            return $this->sendError(20041,'用户不存在',403);

        if (!empty($request->file())){
            $file = $request->file('avatar');
            is_array($file) && $file = $file[0];
            $old_avatar = $userinfo->getData('avatar');
            // 验证并移动到框架应用根目录/public/avatar/ 目录下
            $file->validate(['size'=>512000,'ext'=>'jpeg,png,jpg,gif']);
            //$info = $file->move(ROOT_PATH . 'public' . DS . 'avatar',$file->hash('md5'),true);
            $info = $file->move(FOLDER_PATH . 'avatar',$file->hash('md5'),true);
            if($info){
                $data['avatar'] = 'avatar' . DS . $info->getSavename();
                // 启动事务
                Db::startTrans();
                try{
                    $userinfo->isUpdate(true)->allowField(true)->save($data);
                    if (isset($data['tag']) && !empty($data['tag'])){
                        array_walk($data['tag'],'addTagitem',$userid);
                        $userinfo->usertag()->delete();
                        $userinfo->usertag()->saveAll($data['tag']);
                    }
                    // 提交事务
                    Db::commit();
                    if ($old_avatar != 'avatar/avatar.png' && $old_avatar != $data['avatar']){
                        is_file(FOLDER_PATH .$old_avatar) && unlink(FOLDER_PATH .$old_avatar);
                    }
                    $user = new model\User();
                    $res = $user->search(['userid'=>$userid]);
                    $res['dynamic_count'] = model\Dynamic::where('userid',$userid)->count();
                    return $this->sendSuccess($res);
                } catch (Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    unlink(FOLDER_PATH .$data['avatar']);
                    return $this->sendError(20042,$e->getMessage(),403);
                }
            }
            else{
                // 上传失败获取错误信息
                return $this->sendError(20043,$file->getError(),403);
            }
        }
        else{
            // 启动事务
            Db::startTrans();
            try{
                $userinfo->isUpdate(true)->allowField(true)->save($data);
                if (isset($data['tag']) && !empty($data['tag'])){
                    array_walk($data['tag'],'addTagitem',$userid);
                    $userinfo->usertag()->delete();
                    $userinfo->usertag()
                        ->saveAll($data['tag']);
                }
                // 提交事务
                Db::commit();
                $user =  new model\User();
                $res = $user->search(['userid'=>$userid]);
                $res['dynamic_count'] = model\Dynamic::where('userid',$userid)->count();
                return $this->sendSuccess($res);
            } catch (Exception $e) {
                // 回滚事务
                Db::rollback();
                return $this->sendError(20044,$e->getMessage(),403);
            }
        }
    }



    /**
     * @title 第三方用户登录接口   【不可用】
     * @url /demo/user/otherLogin
     * @type post
     * @desc 友盟登录 放第二版做
     * @readme
     * @param \think\Request $request
     * @return object
     */
    public function postOtherLogin(Request $request)
    {
        $uid = $request->post('uid',null);
        if(empty($uid))return $this->sendError(4003,'Missing Arguments',403);
        $res = Db::name('user')->where('uid',$uid)->field('userid')->find();
        if ( empty($res) ){
            $data = $request->post();
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['identifier'] = md5($data['created_at']);
            $data['usersig'] = signature($data['identifier']);
            Db::name('user')->insert($data);
        } else {
            $data = $request->post();
            unset($data['uid']);
            $data['updated_at'] = date('Y-m-d H:i:s');
            Db::name('user')->where($res)->update($data);
        }
        $userinfo = Db::name('user')
            ->where(['uid'=>$uid])
            ->field('userid,username,tel,avatar,email,created_at,updated_at,identifier,usersig')
            ->find();
        return $this->sendSuccess($userinfo);
    }

    public function edittel(){
        $users = db('user')->field('id,tel')->select()->toArray();
        //exit(json_encode($users));
        foreach ($users as $item){
            db('user')->where('id',$item['id'])->update(['tel'=>mobileEncode($item['tel'])]);
        }

    }
}