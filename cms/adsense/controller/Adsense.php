<?php
namespace cms\adsense\controller;
use cnfol\unit\ArrayUnit;
use think\Db;
use think\Loader;
use think\Config;
use cms\common\model;

/**
 * 广告位管理类
 */
class Adsense extends Base
{
    protected $table='adsense';
    /**
     * 广告位列表
     * @return mixed
     */
    public function fetch_list(){
        /*设置数组*/
        $set = [];
        $set['order'] = 'id';
        $set['thead'] = ['id','create_time'];
        
        $this->view->assign('status',ArrayUnit::array_to_options([0=>'停用',1=>'启用'],$this->request->param('status')));
        $this->view->assign('platform',ArrayUnit::array_to_options([1=>'PC',2=>'App',3=>'移动Web'],$this->request->param('platform')));
        $this->view->assign('sensetype',ArrayUnit::array_to_options([1=>'固定(PC或者移动Web)',2=>'漂浮(PC或者移动Web)',3=>'弹窗(PC或者移动Web)',5=>'信息流(APP)',6=>'轮播图(APP)',7=>'视频(APP)'],$this->request->param('sensetype')));
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
        $tag=model('Adsite')->get_category();
        $this->view->assign('tag',$tag);
        $this->view->assign('info',$info);
        $html = $this->fetch('model_operate');
        return $this->api_success($html);
    }


    /**
     * 锁定'0'
     */
    public function operate_status_0(){
        $param = $this->request->param();
        if(!$param['id']){
            return $this->api_error('请选择数据');
        }
        $data = [
            'status' => 0,
        ];
        $result = $this->model->isUpdate(true,['id' => ['in',$param['id']]])->save($data);
        if(false === $result){
            // 操作失败 输出错误信息
            return $this->api_error($this->model->getError());
        }
        return $this->api_success();
    }


    /**
     * 开启'0'
     */
    public function operate_status_1(){
        $param = $this->request->param();
        if(!$param['id']){
            return $this->api_error('请选择数据');
        }
        $data = [
            'status' => 1,
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
     * 广告位获取代码框
     */
    public function model_view(){
        $id = $this->request->get('id');
        $info = $this->model->find($id)->toArray();
        if(!isset($info['id'])) {
            return $this->api_error('数据不存在');
        }
		//新的代码
       	$adcode = <<<EOD
<!--广告系统广告位--><div id="as_{$info['id']}"><ins data-revive-zoneid="{$info['id']}"></ins></div><!--以下为广告系统引用js，放在页面底部，页面中如果有多个广告位只放一次--><script type="text/javascript" src="http://hsnew.cnfol.com/pc/Js/as/async3.js"></script>
EOD;
        //信息流代码
        $adcodes = <<<EOD
<!--信息流专用代码start-->http://as.cnfol.com/index/index/api_flow?as_id={$info['id']}<!--信息流专用代码end-->
EOD;

        $this->view->assign('adcode',$adcode);
        $this->view->assign('adcodes',$adcodes);
        $this->view->assign('info',$info);
        $html = $this->fetch();
        return $this->api_success($html);
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

        $this->get_map_like($map, 'sensename');
        $this->get_map_equal($map, 'status');
        $this->get_map_equal($map, 'platform');
        $this->get_map_equal($map, 'sensetype');

        /*默认值回调处理*/
        if (isset($set['map']) && is_callable($set['map'])) {
            $set['map']($map);
        };
        /*分页查询*/
        $page = $this->model
            ->alias('u')
            ->join('adsite i','u.adsite=i.id','INNER')
            ->field('u.*,i.sitename,i.platform')
            ->where('u.status','>',-1)
            ->where($map)
            ->order($order . ' ' . $by)
            ->paginate($per_page);
        $this->view->assign('page', $page);
        $sense_ids = [];
	    foreach($page->items() as $k => $v)
	    {
	    	$sense_ids[] = $v['id'];
	    }
	    $ads_cnt = $this->_cnt_ads_by_position($sense_ids);
	    $this->view->assign('sense_ids_cnt', $ads_cnt);
        $this->view->assign('list', $page->items());
        return $this->fetch();
    }

	/**
	 * 统计广告位对应的广告数量
	 */
    protected function _cnt_ads_by_position($sense_ids)
    {
    	$cnt = [];
    	foreach($sense_ids as $k => $v){
			$row = Db::table('advertisement')->where(['adsenseid' => $v, 'status' => 3, 'running_status' => 1])->count();
			$cnt[$v] = $row ? $row : 0;
		}
		return $cnt;
    }

    /**
     * 文件上传
     */
    public function upload()
    {
	    $image = $this->upload2('image');
	    $data = upload2fileserver($image);
	    if($data['code'] == 1)
	    {
		    echo(json_encode(['flag'=>1,'data'=> $data['path']],JSON_UNESCAPED_UNICODE));exit;
	    } else {
		    echo(json_encode(['flag'=>0,'data'=> $data['msg']],JSON_UNESCAPED_UNICODE));exit;
	    }
    }
}
/* End of file User.php */
/* Location: ./app_cms/user/controller/User.php */