<?php
namespace cms\user\controller;
use cnfol\unit\ArrayUnit;
use think\Db;
use think\Loader;
use think\Config;
use cms\common\model;

/**
 * 用户管理类
 */
class User extends Base
{
	protected $table='user';
	/**
	 * 用户列表
	 * @return mixed
	 */
	public function fetch_list(){
		/*设置数组*/
		$set = [];
		$set['order'] = 'id';
		$set['thead'] = ['id','freezing_account','account','create_time','last_login_time'];
        $this->view->assign('status',ArrayUnit::array_to_options([0=>'正常',1=>'锁定'],$this->request->param('status')));
		return $this->fetch_base($set);
	}

    /**
     * 用户详情框
     */
    public function model_view(){
        $id = $this->request->get('id');
        $info = $this->model->with('Userinfo')->find($id)->toArray();
        if(!isset($info['id'])) {
            return $this->api_error('数据不存在');
        }
        $this->view->assign('info',$info);
        $html = $this->fetch();
        return $this->api_success($html);
    }

    /**
     * 用户交易记录框
     */
    public function order_view(){
        $id = $this->request->get('id');
        $list = Db::table('order')->where(array('userid'=>$id,'status'=>1))->order('id DESC')->select();
        if(empty($list)) {
            return $this->api_error('没有交易记录');
        }
        $this->view->assign('list',$list);
        $html = $this->fetch();
        return $this->api_success($html);
    }

    /**
     * 解锁
     */
    public function model_open(){
        $id = $this->request->get('id');
        if(!$id){
            return $this->api_error('请选择数据');
        }

        $result = $this->model->where(array('id'=>$id))->setField('status',0);
        if (false === $result) {
            // 操作失败 输出错误信息
            return $this->api_error($model->getError());
        }
        return $this->api_success();

    }
    /**
     * 锁定
     */
    public function model_lock(){
        $id = $this->request->get('id');
        if(!$id){
            return $this->api_error('请选择数据');
        }

        $result = $this->model->where(array('id'=>$id))->setField('status',1);
        if (false === $result) {
            // 操作失败 输出错误信息
            return $this->api_error($model->getError());
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
        unset($map['user_id']);

        $this->get_map_tel($map, 'tel');
        $this->get_map_like($map, 'nickname');
        $this->get_map_equal($map, 'status');

        /*默认值回调处理*/
        if (isset($set['map']) && is_callable($set['map'])) {
            $set['map']($map);
        };
        /*分页查询*/
        $page = $this->model
            ->alias('u')
            ->join('userinfo i','u.id=i.id','INNER')
            ->field('u.*,i.account,i.freezing_account')
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