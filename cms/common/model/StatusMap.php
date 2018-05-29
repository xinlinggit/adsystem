<?php
namespace cms\common\model;
use think\Cache;

/**
 * 状态表模型
 * @package cms\common\model
 */
class StatusMap extends Base
{
	/**
	 * 注册事件
	 */
	protected static function init(){
		parent::init();
		self::afterWrite(function($_this){
			Cache::rm('status_map_tree');
		});
	}
	/**	 *
	 * @return array
	 * [
	 *      "article" => [ //表名
	 *          "delete_type" => [ //字段名
	 *              '1' => [ //字段值
	 *                  "text" => "后台删除", //显示
	 *                  "remark" => "", //备注
	 *                  "id" => 10, //数据库ID
	 *                  "r_child" => [ //子级
	 *                      '1' => [
	 *                          "text" => "文章含有微信号、QQ号、二维码、网搜名片等个人联系方式",
	 *                          "remark" => "",
	 *                          "id" => 23,
	 *                      ],
	 *                      '2' => [
	 *                          "text" => "文章含有广告内容、外链等",
	 *                          "remark" => "",
	 *                          "id" => 23,
	 *                      ],
	 *                  ],
	 *              '2' => [
	 *                  "text" => "用户删除",
	 *                  "remark" => "",
	 *                  "id" => 10,
	 *              ],
	 *          ]
	 *      ]
	 * ]
	 */
	public function get_all(){
		return Cache::remember('status_map_tree',function(){
			$tree = [];
			foreach($this->select()->toArray() as $row){
				//创建表键名
				if(!isset($tree[$row['r_table']])){
					$tree[$row['r_table']] = [];
				}
				//创建字段键名
				if(!isset($tree[$row['r_table']][$row['r_field']])){
					$tree[$row['r_table']][$row['r_field']] = [];
				}
				//有父级就进行挂载
				if($row['r_pid']){
					//创建值键名
					if(!isset($tree[$row['r_table']][$row['r_field']][$row['r_pid']])){
						$tree[$row['r_table']][$row['r_field']][$row['r_pid']] = [];
					}
					//创建挂载键名
					if(!isset($tree[$row['r_table']][$row['r_field']][$row['r_pid']]['r_child'] )) {
						$tree[$row['r_table']][$row['r_field']][$row['r_pid']]['r_child'] = [];
					}
					//创建挂载键名
					if(!isset($tree[$row['r_table']][$row['r_field']][$row['r_pid']]['r_child'][$row['r_value']] )) {
						$tree[$row['r_table']][$row['r_field']][$row['r_pid']]['r_child'][$row['r_value']] = [];
					}
					$tree[$row['r_table']][$row['r_field']][$row['r_pid']]['r_child'][$row['r_value']]['text'] = $row['r_text'];
					$tree[$row['r_table']][$row['r_field']][$row['r_pid']]['r_child'][$row['r_value']]['remark'] = $row['remark'];
					$tree[$row['r_table']][$row['r_field']][$row['r_pid']]['r_child'][$row['r_value']]['id'] = $row['id'];
				}else {
					//创建值键名
					if(!isset($tree[$row['r_table']][$row['r_field']][$row['r_value']])){
						$tree[$row['r_table']][$row['r_field']][$row['r_value']] = [];
					}
					$tree[$row['r_table']][$row['r_field']][$row['r_value']]['text'] = $row['r_text'];
					$tree[$row['r_table']][$row['r_field']][$row['r_value']]['remark'] = $row['remark'];
					$tree[$row['r_table']][$row['r_field']][$row['r_value']]['id'] = $row['id'];
				}
			};
			return $tree;
		});
	}
}

/* End of file StatusMap.php */
/* Location: ./app_cms/common/model/StatusMap.php */