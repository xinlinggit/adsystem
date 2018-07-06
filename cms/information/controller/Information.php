<?php
namespace cms\information\controller;
use cnfol\unit\ArrayUnit;
use think\Db;
use think\Loader;
use think\Config;
use cms\common\model;


class Information extends Base
{
	protected $table='information';
    /**
     * 广告位列表
     * @return mixed
     */
    public function fetch_list(){
        /*设置数组*/
        $set = [];
        $set['order'] = 'id';
        $set['thead'] = ['id','create_time'];
       
        return $this->fetch_base($set);
    }

    /**
     * 添加框
     */
    public function model_add(){
        $this->view->assign('action',url('operate_add'));
        
        return $this->get_model();
    }

    /**
     * 修改框
     */
    public function model_edit(){
        $this->view->assign('action',url('operate_edit'));
        return $this->get_model();
    }

    /**
     * 添加操作
     */
    public function operate_add(){
        $param = $this->request->post();
        $result = $this->model->isUpdate(false)->save($param);
        if(false === $result) {
            // 验证失败 输出错误信息
            return $this->api_error($this->model->getError());
        }
        return $this->api_success();
    }

    /**
     * 修改操作
     */
    public function operate_edit(){

        $param = $this->request->post();
        $result = $this->model->isUpdate(true)->save($param);
        if(false === $result){
            // 验证失败 输出错误信息
            return $this->api_error($this->model->getError());
        }
        return $this->api_success();
    }

    /**
     * 显示
     * @param int $id 数据ID
     */
    public function get_model(){
        $id = $this->request->get('id');

        $info = $this->model->find($id);
        $this->view->assign('info',$info);
        $html = $this->fetch('model_operate');
        return $this->api_success($html);
    }


    /**
     * 删除（回收站）'-1'
     */
    public function operate_status_11(){
        $param = $this->request->param();
        if(!$param['id']){
            return $this->api_error('请选择数据1');
        }
        $data = [
            'status' => -1,
        ];
        $result = $this->model->isUpdate(true,['id' => ['in',$param['id']]])->save($data);
        if(false === $result){
            // 操作失败 输出错误信息
            return $this->api_error($this->model->getError());
        }
        return $this->api_success();
    }

     /**
     * 页面渲染基础方法
     *
     * @param array $set 配置数组
     *
     * @return mixed
     */
    protected function fetch_base($set = [])
    {
        /*获取排序参数，默认按ID倒序*/
        $order = $this->request->param('order', isset($set['order'])?$set['order']:'id');
        $by = $this->request->param('by', isset($set['by'])?$set['by']:'desc');
        $this->get_thead(isset($set['thead'])?$set['thead']:[$order], $order, $by);

        /*分页设置，默认20，小于100*/
        $per_page = $this->request->param('num', 20);
        $per_page = min(100, $per_page);

        /*全局筛选条件*/
        $map = $this->get_map();

        unset($map['id']);

        $this->get_map_like($map, 'title');

        /*默认值回调处理*/
        if (isset($set['map']) && is_callable($set['map'])) {
            $set['map']($map);
        };
        /*分页查询*/
        $page = $this->model
            ->alias('u')
            ->join('backend_user i','u.backend_user_id=i.id','INNER')
            ->field('u.*,i.real_name')
            ->where('u.status','>',-1)
            ->where($map)
            ->order($order . ' ' . $by)
            ->paginate($per_page);
        $this->view->assign('page', $page);
        $this->view->assign('list', $page->items());
        return $this->fetch();
    }
}