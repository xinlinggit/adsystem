<?php
namespace cms\encash\controller;
use cnfol\unit\ArrayUnit;
use think\Db;
use think\exception\DbException;
use think\Loader;
use think\Config;
use cms\common\model;

/**
 * 转账管理类
 */
class Encashment extends Base
{
	protected $table = 'encashment';


    /**
     * 受理中转账列表
     * @return mixed
     */
    public function fetch_handling(){

        /*设置数组*/
        $set = [];
        $set['order'] = 'id';
        $set['thead'] = ['id','create_time'];
        $this->view->assign('status',ArrayUnit::array_to_options([0=>'待处理',1=>'已成功',2=>'已拒绝'],$this->request->param('status')));
        return $this->fetch_base($set);
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


    /**
     * 审核框
     */
    public function model_result(){
        return  $this->model_base();
    }

    /**
     * 查看框
     */
    public function model_view(){
        $this->model->with('DealUser')->with('ResultUser');
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
        $id = $this->request->post('id');
        $deal_no = $this->request->post('deal_no');
        $deal_user = $this->request->post('deal_user');
        $info = $this->model->find($id);
        if(empty($info)) {
            return $this->api_error('数据不存在');
        }
        $info->deal_no = $deal_no;
        $info->deal_user = $deal_user;
        $info->status = 1;
        $info->deal_time = date('Y-m-d H:i:s');
        $info->save();
        return $this->api_success();
    }

    /**
     * 提交受理结果操作
     */
    public function operate_result(){
        $id = $this->request->post('id');
        $image = $this->request->file('result_image');
        $info = $this->model->find($id);
        if(empty($info)) {
            return $this->api_error('数据不存在');
        }
        $info->result_time = date('Y-m-d H:i:s');
        if (!empty($image)){
            $image->validate(['size'=>5120000,'ext'=>'jpeg,png,jpg,gif']);
            $img_info = $image->move(FOLDER_PATH . 'uploads',(int)(10000*microtime(true)));
            if($img_info){
                $img_name =  $img_info->getSavename();
                $info->status = 1;
                $info->save();
                $info->userinfo->freezing_account -= $info->num ;
                $info->userinfo->save();
            }else{
                return $this->api_error($image->getError());
            }
        }
    }


    /**
     * 拒绝操作
     */
    public function operate_refuse(){
        $param = $this->request->post();
        $image = $this->request->file('result_image');
        if (!isset($param['id']) || !isset($param['remarks']) || empty($param['remarks']) || empty($param['id'])){
            return $this->api_error('缺少参数');
        }
        $info = $this->model->find($param['id']);
        if(empty($info)) {
            return $this->api_error('数据不存在');
        }
        if (isset($param['deal_user'])){
            $param['deal_time'] = date('Y-m-d H:i:s');
        }
        if (isset($param['result_user'])){
            $param['result_time'] = date('Y-m-d H:i:s');
        }
        if (!empty($image)){
            $image->validate(['size'=>5120000,'ext'=>'jpeg,png,jpg,gif']);
            $info = $image->move(FOLDER_PATH . 'uploads',(int)(10000*microtime(true)));
            if($info){
                $img_name =  $info->getSavename();
                $param['result_image'] = '/folder' . '/uploads' . DS . $img_name;
            }else{
                @error_log(date('Y-m-d H:i:s'). ' |-error-| '. print_r($image->getError(), true) .  PHP_EOL , 3, LOG_PATH.'/'.__FUNCTION__.'_'.date('Ymd').'.log');
                return $this->api_error($image->getError());
            }
        }
        try{
            Db::startTrans();
            $param['status'] = 2;
            $param['is_success'] = 1;
            $info->isUpdate(true)->save($param);
            Db::commit();
            $extra = jpush_extra_format(['id'=>$info->id,'money'=>$info->money,'status'=>$info->status,'is_success'=>$info->is_success,'remarks'=>$info->remarks],3);
            @jpush_id($info->userid,"您提交的提现申请{$info->money}元,有新的进度啦！",$extra);
            return $this->api_success();
        }catch (DbException $e){
            Db::rollback();
            return $this->api_error($e->getMessage());
        }

    }


}

/* End of file User.php */
/* Location: ./app_cms/user/controller/User.php */