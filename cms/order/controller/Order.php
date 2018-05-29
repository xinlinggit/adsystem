<?php
namespace cms\order\controller;
use cnfol\unit\ArrayUnit;
use think\Db;
use think\Loader;
use think\Config;
use cms\common\model;

/**
 * 交易处理类
 */
class Order extends Base
{
	protected $table = 'order';

    /**
     * 交易记录
     * @return mixed
     */
    public function fetch_list(){
        
        /*设置数组*/
        $set = [];
        $set['order'] = 'id';
        $set['thead'] = ['id','create_time','unit_money','num','money','fee','total'];
        $this->view->assign('status',ArrayUnit::array_to_options([0=>'挂单中',1=>'已完成',2=>'已撤单',3=>'取消交易'],$this->request->param('status')));
        return $this->fetch_base($set);
    }

    /**
     * 取消交易
     */
    public function model_lock(){
        $id = $this->request->get('id');
        if(!$id){
            return $this->api_error('请选择数据');
        }

        $result = $this->model->where(array('id'=>$id))->setField('status',3);
        if (false === $result) {
            // 操作失败 输出错误信息
            return $this->api_error($model->getError());
        }
        return $this->api_success();

    }

    /**
     * 受理框
     */
    public function model_deal(){
        return  $this->model_base();
    }


    protected function model_base(){
        $id = $this->request->get('id');
        $cloud = Config::get('cloud');
        $backend_id = cookie($cloud['cookie']['backend_id']);
        $this->view->assign('userid',$backend_id);
        $this->model->with('User');
        $info = $this->model->find($id);
        if(empty($info)) {
            return $this->api_error('数据不存在');
        }
        $this->view->assign('info',$info);
        $action = $this->request->action();
        $html = $this->fetch($action);
        return $this->api_success($html);
    }

    /**
     * 受理操作
     */
    public function operate_deal(){
        $param = $this->request->post();
        $info = $this->model->find($param['id']);

        if(empty($info)) {
            return $this->api_error('数据不存在');
        }
        $param['money'] = $param['unit_money']*$info['num'];
        $param['fee'] = $param['money']*0.02;
        $param['total'] = $param['money']+$param['fee'];
        $param['status'] = 4;
        $param['deal_time'] = date('Y-m-d H:i:s');
        $this->model->save($param,array('id'=>$param['id']));
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
        unset($map['user_id']);
        $this->get_map_like($map,'deal_no');
        $this->get_map_tel($map,'tel','user','userid');

        /*默认值回调处理*/
        if (isset($set['map']) && is_callable($set['map'])) {
            $set['map']($map);
        };
        /*分页查询*/
        $page = $this->model->with('User')->where($map)->order($order . ' ' . $by)->paginate($per_page);
        $this->view->assign('page', $page);
        $this->view->assign('list', $page->items());
        $action = $this->request->action();
        return $this->fetch($action);
    }


    /*
    *导出excel
    */
    public function excel_out(){
        
        return $this->out();
    }


}

/* End of file User.php */
/* Location: ./app_cms/user/controller/User.php */