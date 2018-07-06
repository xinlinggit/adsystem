<?php
namespace cms\material\controller;
use cnfol\unit\ArrayUnit;
use think\Db;
use think\Loader;
use think\Config;
use cms\common\model;

/**
 * 素材管理类
 */
class Material extends Base
{
    protected $table='material_main';
    /**
     * 素材列表
     * @return mixed
     */
    public function fetch_list(){
        /*设置数组*/
        $set = [];
        $set['order'] = 'id';
        $set['thead'] = ['id','create_time'];
	    /*获取排序参数，默认按ID倒序*/
	    $order = $this->request->param('order', isset($set['order'])?$set['order']:'id');
	    $by = $this->request->param('by', isset($set['by'])?$set['by']:'desc');
	    $this->get_thead(isset($set['thead'])?$set['thead']:[$order], $order, $by);

        $this->view->assign('status',ArrayUnit::array_to_options([0=>'等待提交审核',3=>'审核已通过',4=>'审核未通过',5=>'待审核'],$this->request->param('status')));
        $this->view->assign('material_type',ArrayUnit::array_to_options([1=>'文字广告',2=>'图片广告',3=>'flash广告',4=>'信息流广告'],$this->request->param('material_type')));
        return $this->fetch_base($set);
    }


    /**
     * 修改框
     */
    public function model_edit(){
        $this->view->assign('action',url('operate_edit'));
        return $this->get_model();
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
     * 审核通过'3'
     */
    public function operate_status_3(){
        $param = $this->request->param();
        if(!$param['id']){
            return $this->api_error('请选择数据');
        }
        $data = [
            'status' => 3,
            'remark' =>''
        ];
        $result = $this->model->isUpdate(true,['id' => ['in',$param['id']]])->save($data);
        if(false === $result){
            // 操作失败 输出错误信息
            return $this->api_error($this->model->getError());
        }
        return $this->api_success();
    }

    /**
     * 审核不通过'4'
     */
    public function operate_status_4(){
        $param = $this->request->param();
        if(!$param['id']){
            return $this->api_error('请选择数据');
        }
        $data = [
            'status' => 4,
        ];
        $result = $this->model->isUpdate(true,['id' => ['in',$param['id']]])->save($data);
        if(false === $result){
            // 操作失败 输出错误信息
            return $this->api_error($this->model->getError());
        }
        return $this->api_success();
    }

    /**
     * 删除（回收站）'-1'
     */
    public function operate_status_11(){
        $param = $this->request->param();
        if(!$param['id']){
            return $this->api_error('请选择数据');
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

        $this->get_map_equal($map, 'material_title');
        $this->get_map_equal($map, 'material_type');
        unset($map['status']);
        $status = trim($this->request->param('status'));
        if( is_numeric($status))
        {
	        $map['u.status'] = $status;
        }

        /*默认值回调处理*/
        if (isset($set['map']) && is_callable($set['map'])) {
            $set['map']($map);
        };
        /*分页查询*/
	    $page = $this->model
		    ->alias('u')
		    ->join('ad_admin_user i','u.userid=i.id','left')
		    ->join('adsense as', 'u.adsenseid = as.id', 'left')
		    ->join('adsite ad', 'u.adsiteid = ad.id', 'left')
		    ->field('u.*,i.username,as.sensename,ad.sitename')
		    ->where('u.status','>',0)
		    ->where($map)
		    ->order($order . ' ' . $by)
		    ->paginate($per_page);

        $this->view->assign('page', $page);
        $this->view->assign('list', $page->items());

        return $this->fetch();
    }


}

/* End of file User.php */
/* Location: ./app_cms/user/controller/User.php */