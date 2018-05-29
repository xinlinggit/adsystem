<?php
namespace cms\common\model;
use think\Cache;

/**
 * 广告站点管理类
 */
class Adsite extends Base
{
    /**
	 * 获取所有正常广告站点
	 * @return array
	 */
	public function get_category()
	{
		//return Cache::tag($this->name)->remember($this->name . '_' . __FUNCTION__, function() {
			$result = $this->where(['status'=>['>',0]])->order('id asc')->select();
			$format_array = [];
			foreach ($result as $row){
				$format_array[$row['id']] = $row;
			}
			return $format_array;
		//});
	}

	public function get_categorys()
	{
			$result = $this->where(['status'=>['>',0]])->field('id,sitename')->order('id asc')->select()->toArray();
			foreach ($result as $key => $value) {
				$new[$value['id']] = $value['sitename'];
			}
			return $new;
	}


}

/* End of file User.php */
/* Location: ./app_cms/common/model/User.php */