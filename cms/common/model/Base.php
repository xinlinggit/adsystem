<?php
namespace cms\common\model;
use InvalidArgumentException;
use think\Cache;
use think\Config;
use think\Exception;
use think\Loader;
use think\model\Merge;
use think\Session;

/**
 * 模型基类
 */

class Base extends \think\Model
{
	/**
	 * 状态常量定义，用于代替数字
	 */
	//const STATUS = [];注释的
	/**
	 * 删除类型常量定义，用于代替数字
	 */
	//const DELETE_TYPE = [];注释的
	//use \traits\model\SoftDelete;
	/**
	 * @var string 时间类型
	 */
	protected $autoWriteTimestamp = 'datetime';
	/**
	 * @var string 时间格式化
	 */
	protected $dateFormat = 'Y-m-d H:i:s';
	/**
	 * @var string 设置返回数据集的对象名
	 */
	protected $resultSetType = 'collection';
	protected $auto = ['operate_time', 'backend_user_id'];

	/**
	 * @var array 待入库数据
	 */
	protected $list = [];
	/**
	 * 注册事件
	 */
	protected static function init(){
		self::afterWrite(function($_this){
			Cache::clear($_this->name);
		});
	}



	protected function setOperateTimeAttr($value) {
		return date('Y-m-d H:i:s');
	}

	protected function setBackendUserIdAttr($value)
	{
		return Session::get('backend_user')['id'];
	}


	/**
	 * 字段转换
	 * @param null $value 默认是NULL
	 * @param array $data 查询结果数据
	 *
	 * @return string 转换后值
	 */
	public function getTypeTextAttr($value,$data){
		if(isset($data['type'])) {
			return $this->getText('type', $data['type']);
		}
	}
	/**
	 * 字段转换
	 * @param null $value 默认是NULL
	 * @param array $data 查询结果数据
	 *
	 * @return string 转换后值
	 */
	public function getStatusTextAttr($value,$data){
		if(isset($data['status'])) {
			return $this->getText('status', $data['status']);
		}
	}
	/**
	 * 关联后台用户表
	 * @return $this
	 */
	public function backendUser()
	{
		return $this->hasOne('backend_user','id','backend_user_id');
	}

	/**
	 * 获取所有未删除数据（带缓存）
	 * @return array
	 */
	public function get_no_del(){
		return Cache::tag($this->name)->remember($this->name . '_' . __FUNCTION__, function() {
			$result = $this->where(['status'=>['>',0]])->order('id desc')->select();
			$format_array = [];
			foreach ($result as $row){
				$format_array[$row->id] = $row->toArray();
			}
			return $format_array;
		});
	}

	/**
	 * 保证返回数据永远有字段
	 * @param null $data
	 *
	 * @return array|false|\PDOStatement|string|\think\Collection
	 */
	/*public function select($data = null)
	{
		$result = parent::select($data);
		if(!$result){
			$result[] = array_fill_keys(array_values($this->getQuery()->getTableInfo('', 'fields')), '');
		}
		return $result;
	}*/

	/**
	 * 字段值转换
	 * @param string $field 字段
	 * @param string $value 值
	 *
	 * @return string 转换后结果
	 */
	public function getText($field = '',$value = ''){
		$status_map = Loader::model('status_map')->get_all();
		$table = Loader::parseName($this->name);
		if(!isset($status_map[$table])){
			return '表不存在';
		}
		if(!isset($status_map[$table][$field])){
			return '字段不存在';
		}
		if(!isset($status_map[$table][$field][$value])){
			return '值不合法';
		}
		return $status_map[$table][$field][$value]['text'];
	}

	/**
	 * 捕获所有字段不存在的异常，返回空值
	 * @param string $name
	 *
	 * @return mixed|string
	 */
	public function getAttr($name){
		$value = '';
		try{
			$value = parent::getAttr($name);
		}catch (InvalidArgumentException $e){
			return '';
		}catch (Exception $e){

		}
		return $value;
	}

	/**
	 * 快捷修改字段值
	 *
	 * @param array  $id 数据ID
	 * @param string $field 对应字段，默认status
	 * @param int    $value 字段对应值
	 *
	 * @return false|int
	 *
	 */
	public function change_value($id=[0],$field='status',$value=0){
		$data = [
			$field => $value,
		];
		$result = $this->isUpdate(true,['id' => ['in',$id]])->save($data);
		return $result;
	}

	/**
	 * 批量保存数据
	 */
	public function save_data(){
		if(!$this->list){
			return '';
		}
		return $this->saveAll($this->list);
	}
	/*用于子类调用代码提示*/
	public function get_all(){}
	public function send(){}
	public function get_category(){}
	public function get_recommend(){}
	/**
	 * @param array $data
	 */
	public function add_data($data = [])
	{
		$this->list[] = $data;
	}

}

/* End of file Base.php */
/* Location: ./app_cms/common/model/Base.php */