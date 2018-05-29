<?php
/**
 * 应用基类
 */
namespace cms\common\controller;

use cnfol\api\Cloud;
use think\Cache;
use think\Config;
use think\Controller;
use think\exception\HttpResponseException;
use think\Loader;
use think\Response;
use think\Session;

class Common extends Controller
{
	/**
	 * 允许访问的请求类型
	 * @var string
	 */
	protected $rest_method_list = 'get|post|put|delete|patch|head|options';
	/**
	 * 模型对象
	 * @var \cms\common\model\Base
	 */
	protected $model = '';
	/**
	 * 表名
	 * @var string
	 */
	protected $table = '';
	/**
	 * 后台用户信息
	 * @var string
	 */
	protected $backend = [];

	/**
	 * 记录日志
	 * @var array
	 */
	protected $log = [];

	/**
	 * 初始化
	 */
	protected function _initialize()
	{
		$this->init_user();
		if ($this->table) {
			$this->model = Loader::model($this->table);
			$this->log['data_table'] = $this->table;
		}
		$this->view->assign('param', $this->request->param());
		$this->view->assign('list', []);
		$this->_init();
		/*if(isset($this->request->header()['referer']) && false !== strpos($this->request->header()['referer'], 'http://cloudtest.cnfol.com')){
			$html = '<!DOCTYPE html>';
			$html .= '<html>';
			$html .= '<head>';
			$html .= '<meta charset="utf-8">';
			$html .= '</head>';
			$html .= '<body style="min-height: 120px;">';
			$html .= '<pre>为了更加的用户体验，请在新打开的窗口进行操作~</pre>';
			$html .= '<a target="_blank" href="' . $this->request->url() . '">如果页面没有自动跳转，请点击这里~</a>';
			$html .= '<script>';
			$html .= 'document.domain = "cnfol.com";';
			$html .= 'top.open("' . url( $this->request->url() ,'','',true) . '");';
			$html .= '</script>';
			$html .= '</body>';
			$html .= '</html>';
			echo $this->display($html);
			exit;
		};*/
	}

	/**
	 * 处理用户信息
	 */
	private function init_user()
	{
		/*获取云平台配置*/
		$cloud = Config::get('cloud');

		$backend_id = cookie($cloud['cookie']['backend_id']);
		
		if (!$backend_id) {
			echo '请先登录';
			exit;
		}
		//dump(Cloud::get_user_funList(cookie($cloud['cookie']['backend_id']),250));
		$info = Cloud::get_userinfo($backend_id);

		if (isset($info['Usr_ID'])) {
			$data['id'] = $info['Usr_ID'];
			$data['real_name'] = $info['Usr_RealName'];
			$data['email'] = $info['Usr_Email'];
			$data['telephone'] = $info['Usr_Tel'];
			$data['status'] = $info['Usr_State'];
			$data['role_id'] = $info['Usr_RoleID'];
			$data['department_id'] = $info['Usr_DptID'];
			$data['department'] = $info['Usr_Dpt'];
			$data['company_id'] = $info['Usr_ComID'];
			$data['company'] = $info['Usr_Com'];
			$this->backend = $data;
			Session::set('backend_user', $data);
			//每次登录更新一次用户信息
			if (!cookie('user_update_' . $info['Usr_ID'])) {
				$model_backend_user = Loader::model('backend_user');
				if (!$model_backend_user->find($info['Usr_ID'])) {
					$model_backend_user->isUpdate(false)->save($data);
				} else {
					$model_backend_user->isUpdate(true)->save($data, ['id' => $info['Usr_ID']]);
				}
				cookie('user_update_' . $info['Usr_ID'], 1);
			}
		} else {
			echo '用户不存在';
			exit;
		}
	}

	/**
	 * 控制器初始化
	 */
	protected function _init()
	{

	}

	protected function api_success($data = '', $msg = '')
	{	//$this->add_log();
		$result = [
			'code' => 1,
			'msg' => $msg,
			'data' => $data,
		];
		$response = Response::create($result, 'jsonp');
		throw new HttpResponseException($response);
	}

	protected function api_error($msg = '', $code = '0', $url = '')
	{
		$result = [
			'code' => $code,
			'msg' => $msg,
			'data' => '',
			'url' => $url,
		];
		$response = Response::create($result, 'jsonp');
		throw new HttpResponseException($response);
	}

	/**
	 * 生成表头排序功能
	 *
	 * @param array  $fields 需排序字段数组
	 * @param string $order 排序字段
	 * @param string $by 排序方式
	 */
	protected function get_thead($fields = [], $order = '', $by = '')
	{
		$thead = array_fill_keys($fields, ['class' => ' js_th_sorting sorting ']);
		foreach ($fields as $value) {
			$thead[$value]['url'] = url('', array_merge($this->request->param(), ['order' => $value, 'by' => 'desc']));
			if ($order == $value) {
				if ($by == 'asc') {
					$thead[$value]['class'] .= ' sorting_asc ';
					$by = 'desc';
				} else {
					$thead[$value]['class'] .= ' sorting_desc ';
					$by = 'asc';
				}
				$thead[$value]['url'] = url('', array_merge($this->request->param(), ['order' => $value, 'by' => $by]));
			}
		}
		$this->assign('thead', $thead);
	}

	/**
	 * 筛选条件处理
	 * @return array 查询数组
	 */
	protected function get_map()
	{
		//$map = ['status' => ['<', 99]];
		/*id*/
		$this->get_map_equal($map,'id');
		$this->get_map_equal($map,'userid');
		/*like筛选*/
		$this->get_map_like($map, 'title');

		$this->get_map_like($map, 'content');
		/*值筛选*/
		$this->get_map_equal($map, 'status');

		/*关联筛选*/
		$this->get_map_like($map, 'nickname', 'user');
		/*时间筛选*/
		$this->get_map_time($map, 'create');
		$this->get_map_time($map, 'update');
		$this->get_map_time($map, 'operate');
		$this->get_map_time($map, 'audit');
		return $map;
	}
	
	/**
	 * 添加操作日志
	 *
	 * @param array $set
	 *
	 * @return string
	 */
	protected function add_log($set = [])
	{
		$url = '/'.$this->request->path();

		$menu = Loader::model('backend_menu')->get_no_del();

		$current = [];
		if(false !== strpos($this->request->action(),'model')){
			return '';
		}

		foreach ($menu as $key => $value) {
			/*只记录功能*/
			if($value['type'] != 2){
				continue;
			}

			/*URL不匹配就跳过*/
			if ($value['url'] !== $url) {
				continue;
			}

			$referer = $this->request->header()['referer'];
			$p_url = url($menu[$value['pid']]['url'], '', '', true);
			/*父级不匹配就跳过*/
			if ($value['pid'] && ($referer !== $p_url) && (strstr($referer,'.html',true) !== $p_url) && (strstr($referer,'?',true) !== $p_url)) {
				continue;
			}
			$current = $value;
		}

		/*没有对应菜单就记录个空的*/
		if (!$current) {
			$current['pid'] = 0;
			$current['type'] = 2;
			$current['title'] = '待记录：' . $this->request->header()['referer'] . '|' . $url;
		}
		$id = isset($this->log['data_ids']) ? $this->log['data_ids'] : $this->request->param('id');
		$ids = explode(',', $id);
		$log = [
			'backend_user_id' => $this->backend['id'],
			//如果是功能，就存储父级ID
			'menu_id' => $current['type'] == 2 ? $current['pid'] : $current['id'],
			'operate' => $current['title'],
			'data_table' => isset($this->log['data_table']) ? $this->log['data_table'] : 0,
			'status_map_id' => isset($this->log['status_map_id']) ? $this->log['status_map_id'] : 0,
			'remark' => isset($this->log['remark']) ? $this->log['remark'] : '',
			'ip' => request()->ip(),
			'create_time' => date('Y-m-d H:i:s'),
			'update_time' => date('Y-m-d H:i:s'),
		];
		$log_list = [];
		/*多个ID批量插入*/
		foreach ($ids as $i) {
			if(!$i) continue;
			$log['data_ids'] = $i;
			$log_list[] = $log;
		}

		if ($log_list) {
			Loader::model('backend_log')->insertAll($log_list);
		}
	}

	/**
	 * 生成时间字段的过滤条件
	 *
	 * @param array  $map 条件数组
	 * @param string $field 字段名，去掉后缀_time
	 * @param string $table 关联表名
	 * @param string $foreign 关联字段，默认表名_id
	 *
	 * @return array 条件数组
	 */
	protected function get_map_time(&$map, $field = '', $table = '', $foreign = '')
	{
		$begin = trim($this->request->param($field . '_start'));
		$end = trim($this->request->param($field . '_end'));
        if ($begin) {
            $begin = $begin . ' 00:00:00';
        }
		if ($end) {
			$end = $end . ' 23:59:59';
		}
		if ($begin) {
			if ($end) {
				$where = ['between', [$begin, $end]];
			} else {
				$where = ['>=', $begin];
			}
		} else {
			if ($end) {
				$where = ['<=', $end];
			} else {
				return '';
			}
		}
		$this->make_map($where, $map, $field . '_time', $table, $foreign);
	}

	/**
	 * 生成模糊查询字段的过滤条件
	 *
	 * @param array  $map 条件数组
	 * @param string $field 字段名
	 * @param string $table 关联表名
	 * @param string $foreign 关联字段，默认表名_id
	 *
	 * @return array 条件数组
	 */
	protected function get_map_like(&$map, $field = '', $table = '', $foreign = '')
	{
		$value = trim($this->request->param($field));
		if (!strlen($value)) {
			return '';
		}
		$where = ['like', '%' . $value . '%'];
		$this->make_map($where, $map, $field, $table, $foreign);
	}

	/**
	 * 生成模糊查询字段的过滤条件
	 *
	 * @param array  $map 条件数组
	 * @param string $field 字段名
	 * @param string $table 关联表名
	 * @param string $foreign 关联字段，默认表名_id
	 *
	 * @return array 条件数组
	 */
	protected function get_map_equal(&$map, $field = '', $table = '', $foreign = '')
	{
		$value = trim($this->request->param($field));
		if (!strlen($value)) {
			return '';
		}
		$where = $value;
		$this->make_map($where, $map, $field, $table, $foreign);
	}

    /**
     * 生成模糊查询字段的过滤条件
     *
     * @param array  $map 条件数组
     * @param string $field 字段名
     * @param string $table 关联表名
     * @param string $foreign 关联字段，默认表名_id
     *
     * @return array 条件数组
     */
    protected function get_map_tel(&$map, $field = 'tel', $table = '', $foreign = '')
    {
        $value = trim($this->request->param($field));
        if (!strlen($value)) {
            return '';
        }
        $where = mobileEncode($value);
        $this->make_map($where, $map, $field, $table, $foreign);
    }

	private function make_map($where, &$map, $field = '', $table = '', $foreign = '')
	{
	    if ($field == 'id') {
	        $field = $this->table . '.id';
        }
		if (!$table) {
			$map[$field] = $where;
			return '';
		}
		$foreign = $foreign ? $foreign : $table . '_id';
		$id = Loader::model($table)->where([$field => $where])->column('id');
		unset($map[$field]);
		$map[$foreign] = ['in', $id];
		return '';
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
		$order = $this->request->param('order', isset($set['order']) ? $set['order'] : 'id');
		$by = $this->request->param('by', isset($set['by']) ? $set['by'] : 'desc');
		$this->get_thead(isset($set['thead']) ? $set['thead'] : [$order], $order, $by);

		/*分页设置，默认20，小于100*/
		$per_page = $this->request->param('num', 20);
		$per_page = min(100, $per_page);

		/*全局筛选条件*/
		$map = $this->get_map();
		/*默认值回调处理*/
		if (is_callable($set['map'])) {
			$set['map']($map);
		};
		/*分页查询*/
		$page = $this->model->where($map)->order($order . ' ' . $by)->paginate($per_page);
		$this->view->assign('page', $page);
		$this->view->assign('list', $page->items());

		return $this->fetch(isset($set['template']) ? $set['template'] : '');
	}

	/**
	 * 修改状态
	 */
	public function operate_change_status()
	{
		// TODO: 参数值域安全检查
		$param = $this->request->param();
		if (!$param['id']) {
			return $this->api_error('请选择数据');
		}
		$field = $this->request->param('field');
		$value = $this->request->param('value');
		/*简单的安全过滤*/
		if (!in_array($field, ['is_chat', 'is_close', 'sort', 'status'])) {
			return $this->api_error('操作非法');
		}
		$result = $this->model->isUpdate(true, ['id' => ['in', $param['id']]])->save([$field => $value]);

		if (false === $result) {
			// 操作失败 输出错误信息
			return $this->api_error($this->model->getError());
		}
		return $this->api_success();
	}

	/**
	 * 修改值
	 *
	 * @param string $field
	 * @param string $value
	 */
	public function operate($field = '', $value = '')
	{
	}


	/*
	*导入excel功能
	*
	*/
	public function out(){
    	//文件名称
		$Excel['fileName']="PHPExcel示例".date('Y年m月d日-His',time());//or $xlsTitle
		$Excel['cellName']=['A','B','C','D'];
		$Excel['H'] = ['A'=>22,'B'=>26,'C'=>30,'D'=>30];//横向水平宽度
		$Excel['V'] = ['1'=>40,'2'=>26];//纵向垂直高度
		$Excel['sheetTitle']="PHPExcel示例";//大标题，自定义
		//$Excel['xlsCell']=Data::head();
		$Excel['xlsCell'] = [['autoid','序号'],['school','学校'],['addr','所在地'],['type','类型']];
		//$data=Data::data();
		$data=[
			['autoid'=>'1','school'=>'云南大学','addr'=>'云南省','type'=>'综合'],
			['autoid'=>'2','school'=>'云南财经大学','addr'=>'云南省','type'=>'财经'],
			['autoid'=>'3','school'=>'云南民族大学','addr'=>'云南省','type'=>'综合'],
			['autoid'=>'4','school'=>'云南师范大学','addr'=>'云南省','type'=>'师范'],
			['autoid'=>'5','school'=>'云南旅游大学','addr'=>'云南省','type'=>'综合'],
			['autoid'=>'6','school'=>'贵州大学','addr'=>'贵州省','type'=>'综合'],
			['autoid'=>'7','school'=>'贵州财经大学','addr'=>'贵州省','type'=>'财经'],
			['autoid'=>'7','school'=>'贵州师范大学','addr'=>'贵州省','type'=>'师范']
			];
		\cms\common\model\PHPExcel::excelPut($Excel,$data);
    }
}

/* End of file Common.php */
/* Location: ./app_cms/common/controller/Common.php */