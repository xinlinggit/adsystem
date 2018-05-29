<?php
namespace app\apilib\controller;

use app\common\model;
use think\Request;
use think\Response;

/**
 * API的菜单管理
 * User: Administrator
 * Date: 2017/3/15
 * Time: 16:50
 */
class MenuApi extends Base
{

    /**
     * 列表页查询接口
     * @param Request $request 请求参数
     * @return Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function index(Request $request){
        $menu_api = new model\MenuApi();
        $map = ['state'=>1];
        $result = $menu_api->with('menu_api')->where($map)->field(true)->select();
        //$responseData = array_values($result);
        $list = [];
        foreach($result as &$row){
            //添加操作权限，0->无，1->添加，2->修改，-1->删除，3->查看
            $row['opt'] = '3,2,-1';
            //$row['pid_title'] = $responseData[$row['pid']];
        }
        //dump($result);
        return Response::create($result, 'json');
    }

    /**
     * 获取添加页面
     * @return \think\response\View
     */
    public function create(){
        $menu_api = new model\MenuApi();
        $map = ['state'=>1,'pid'=>0];
        $pids = $menu_api->where($map)->column('title','id');
        $responseData['pids'] = $pids;
        return view('add',$responseData);
    }

    /**
     * 添加数据
     * @param Request $request
     * @return \think\response\Json
     */
    public function save(Request $request){
        $param = $request->param();
        $menu_api = new model\MenuApi($param);
        $result = $menu_api->data($param)->save();
        if($result) {
            return json(['flag' => '10000', 'msg' => '添加成功']);
        }
        else{
            return json(['flag' => '10001', 'msg' => '添加失败']);
        }
    }

    /**
     * 获取查看页面
     * @param $id
     * @return \think\response\View
     */
    public function read($id){
        $menu_api = new model\MenuApi();
        $map = ['state'=>1];
        $info = $menu_api->with('menu_api')->where($map)->field(true)->find($id);
        $responseData['info'] = $info;
        return view('',$responseData);
    }

    /**
     * 获取更新页面
     * @param $id
     * @return \think\response\View
     */
    public function edit($id){
        $menu_api = new model\MenuApi();
        $map = ['state'=>1];
        $info = $menu_api->with('menu_api')->where($map)->find($id);
        $responseData['info'] = $info;
        $map = ['state'=>1,'pid'=>0];
        $pids = $menu_api->where($map)->column('title','id');
        $responseData['pids'] = $pids;
        return view('',$responseData);
    }

    /**
     * 更新数据
     * @param Request $request
     * @return \think\response\Json
     */
    public function update($id){
        $menu_api = new model\MenuApi();
        $param = Request::instance()->post();
        $result = $menu_api->save($param,['id'=>$id]);
        if($result) {
            return json(['flag' => '10000', 'msg' => '更新成功']);
        }
        else{
            return json(['flag' => '10001', 'msg' => '更新失败']);
        }
    }

    public function delete($id){
        $info = model\MenuApi::get($id);
        $result = $info->delete();
        if($result) {
            return json(['flag' => '10000', 'msg' => '删除成功']);
        }
        else{
            return json(['flag' => '10001', 'msg' => '删除失败']);
        }
    }
}