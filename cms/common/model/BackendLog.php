<?php
namespace cms\common\model;
use think\Cache;
use think\Loader;

/**
 * 后台日志模型
 * Class Backend
 * @package cms\common\model
 */
class BackendLog extends Base
{
    protected $auto = ['ip'];


	protected function setIpAttr()
    {
        return request()->ip();
    }

	/**
	 * 关联状态表
	 * @return $this
	 */
	public function statusMap()
	{
		return $this->hasOne('status_map','id','status_map_id');
	}
	/**
	 * 关联后台菜单表
	 * @return $this
	 */
	public function backendMenu()
	{
		return $this->hasOne('backend_menu','id','menu_id');
	}

	/**
	 * 字段转换
	 * @param null $value 默认是NULL
	 * @param array $data 查询结果数据
	 *
	 * @return string 转换后值
	 */
	public function getMenuCrumbTextAttr($value,$data){
		if(isset($data['menu_id'])) {
			$menu = Loader::model('backend_menu')->get_no_del();
			$crumb = '';
			$crumb = function ($row) use (&$crumb, $menu) {
				if ($row['pid']) {
					return $crumb($menu[$row['pid']]) . '->' . $row['title'];
				}
				return $row['title'];
			};
			if(!isset($menu[$data['menu_id']])){
				return '';
			}
			return $crumb($menu[$data['menu_id']]);
		}
		return '';
	}

}

/* End of file BackendLog.php */
/* Location: ./app_api/common/model/BackendLog.php */