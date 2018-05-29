<?php

//------------------------
// 节点图验证器
//-------------------------

namespace cms\common\validate;

use think\Validate;
use think\Db;

class NodeMap extends Validate
{
    protected $rule = [
        "module|模块"      => "require",
        "controller|控制器" => "require",
        "action|方法"      => "require|checkMap:1",
        "method|请求方式"    => "require",
        "comment|备注"     => "require",
    ];

    protected function checkMap($value, $rule, $data)
    {
        if (isset($data['id']) && $data['id']) $where['id'] = ["neq", $data['id']];
        $where['module'] = $data['module'];
        $where['controller'] = $data['controller'];
        $where['action'] = $data['action'];
        $where['method'] = $data['method'];

        return Db::name("NodeMap")->where($where)->find() ? "该节点图已经存在" : true;
    }
}
