<?php
namespace api\common\controller;
use think\Controller;
use think\Request;
use think\Response;

/**
 * 基础控制器
 * @package app\common\controller
 * @author qinxw
 * @update 2017-03-14
 *
 */
class Web extends Controller
{
    protected function _initialize()
    {
        $this->_base();
    }
    // 初始化
    protected function _base()
    {
    }

    public function index(){
    }

    public function create(){

    }

    public function save(){

    }

    public function read($id){

    }

    public function edit($id){

    }

    public function update($id){

    }

    public function delete($id){

    }

}
