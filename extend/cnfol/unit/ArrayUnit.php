<?php
namespace cnfol\unit;
/**
 * 数组处理类
 * Class ArrayUnit
 * @package cnfol\unit
 */

class ArrayUnit
{
	/**
	 * 数组转树形(无限级)
	 * @param array $data 输入数组，二维结构
	 * @param array $set  配置数组
	 * @example "[
	 *  'field'  => [ //字段设置
	 *     'self' => 当前记录KEY，默认'id'
	 *     'parent' => 父级标记KEY，默认'pid'
	 *   ],
	 *   'root' => 起始节点，默认'0'(最上层父级ID)
	 * ]"
	 * @return array 结果数组
	 * @time 2017-06-16
	 * @author 秦晓武
	 */
	static function array_to_tree(&$data,$set = array()){
		if(!is_array($data) || !is_array($set) || !count($data)){
			return array();
		}
		/* 初始化设置 */
		$default_set = array(
			'field' => array(
				'self' => 'id',
				'parent' => 'pid',
			),
			'function' => array(
				'format_data' => ''
			),
			'root' => '0',
			'root_show' => false,
			'level' => 0,
		);
		$set = self::array_overlay($default_set,$set);
		$field_self = $set['field']['self'];
		$field_parent = $set['field']['parent'];
		$f = $set['function']['format_data'];
		$tree = array();
		foreach($data as $key => $row){
			$temp_row = '';
			if(!isset($row[$field_parent]) || $row[$field_parent] == $set['root'] || ($row[$field_self] == $set['root'] && $set['root_show'])){
				if(is_callable($f)){
					$temp_row = call_user_func_array($f, array($row,$set));
				}
				$tree[$row[$field_self]]['data'] = $temp_row ? $temp_row : $row;
				unset($data[$key]);
			}
			if($set['root_show'] && ($row[$field_self] == $set['root'])){
				break;
			}
		}
		foreach($tree as $key => $value){
			$temp_set = $set;
			$temp_set['root'] = $key;
			$temp_set['level']++;
			$tree[$key]['child'] = self::array_to_tree($data,$temp_set);
			$tree[$key]['child_ids'] = [];
			foreach($tree[$key]['child'] as $k => $v){
				array_push($tree[$key]['child_ids'],$k);
				$tree[$key]['child_ids'] = array_merge($tree[$key]['child_ids'], $v['child_ids']);
			}
		}
		return $tree;
	}

	/**
	 * 树转数组
	 * @param  array $tree 树型数组（data为父级数据，child为子级数据）
	 * @return array 返回转换后数组
	 * @time 2015-06-06
	 * @author 秦晓武
	 */
	static function tree_to_array($tree = array()){
		$data = array();
		foreach($tree as $row){
			$data[$row['data']['id']] = $row['data'];
			if($row['child']){
				$data = $data + self::tree_to_array($row['child']);
			}
		}
		return $data;
	}

	/**
	 * 两个多维数组合并（后面值覆盖前面值）
	 * @param array $a1 多维数组
	 * @param array $a2 多维数组, 用"delete"标记删除的键值
	 * @return array 返回合并后数组
	 * @time 2015-06-06
	 * @author 秦晓武
	 */
	static function array_overlay($a1 = array(), $a2 = array())
	{
		$diff = array_diff_key($a2,$a1);
		foreach($diff as $k => $v){
			$a1[$k] = $v;
		}
		foreach($a1 as $k => $v) {
			if (isset($a2[$k]) && $a2[$k]==="delete"){
				unset($a1[$k]);
				continue;
			};
			if(!array_key_exists($k,$a2)) continue;
			if(is_array($v) && is_array($a2[$k])){
				$a1[$k] = self::array_overlay($v,$a2[$k]);
			}else{
				$a1[$k] = $a2[$k];
			}
		}
		return $a1;
	}

	/**
	 * 数组变成下拉框(无限级)
	 * @param	array	$data	原始数组（数据库查出的结果集）
	 * @param	int		$id		当前菜单ID
	 * @param	array	$set	配置数组(参照tree_to_show函数)
	 * @return	string	$result	下拉框的option
	 * @time 2017-06-16
	 * @author	秦晓武
	 **/
	static function array_to_select($data = array(),$id = '-2', $set = array()){
		$tree = self::array_to_tree($data,$set);
		return self::tree_to_select($tree,$id,$set);
	}

	/**
	 * 树型变成下拉框(无限级)
	 * @param	array	$tree	树型数组（data为父级数据，child为子级数据）
	 * @param	int		$id		当前菜单ID
	 * @param	array	$set	配置数组(参照tree_to_show函数)
	 * @return	string	$result	下拉框的option
	 * @time 2017-06-16
	 * @author	秦晓武
	 **/
	static function tree_to_select($tree = array(),$id = '-2', $set = array()){
		$set['now'] = is_null($id) ? array() : array($id);
		$set['function']['replace_self'] = 'self::select_self';
		$set['function']['replace_parent'] = 'self::select_parent';
		$result = self::tree_to_show($tree,$set);
		return $result;
	}

	/**
	 * 树形显示
	 * @param		array		$tree		输入数组
	 * @param		array		$set		array( //配置数组
	 * 		'show'				=>	显示字段
	 * 		'field'				=>	array( //字段设置
	 * 			'self'				=>	当前记录KEY，默认'id'
	 * 			'parent'			=>	父级标记KEY，默认'pid'
	 * 			'relation'		=>	外键KEY，用于下拉框级联操作，默认''
	 * 		),
	 * 		'function'		=>	array( //回调函数
	 * 			'replace_self'		=>	替换子级的回调函数
	 * 			'replace_parent'	=>	替换父级的回调函数
	 * 		),
	 * 		'node'				=>	起始节点，默认'0'（最上层父级ID）
	 * 		'level'				=>	层级，默认'-1'
	 * 		'limit'				=>	最大层级，默认'999'
	 * 		'prefix'			=>	显示前缀，默认'|- '
	 * 		'add_prefix'	=>	递增前缀，默认'&nbsp;&nbsp;&nbsp;'
	 * 	)
	 * @param		array		$pipe		array( //返回信息数组(待扩充)
	 * 		'return_line' =>	最终节点回归线(默认标记第一个最终节点的回归线)
	 * 		'parent'			=>	所有父级数据
	 * 		'child'				=>	所有子级数据（最后一级子集）
	 * )
	 * @return	string	$result		结果模版
	 * @time 2017-06-16
	 * @author	秦晓武
	 */
	static function tree_to_show($tree,$set=array(),&$pipe = array()){
		if(!is_array($tree) || !is_array($set) || !count($tree)){
			return '';
		}

		/* 初始化设置 */
		$default_set = array(
			'show' => 'title',
			'field' => array(
				'self' => 'id',
				'parent' => 'pid',
				'relation' => '',
			),
			'function' => array(
				'replace_self' => '',
				'replace_parent' => '',
			),
			'node' => '0',
			'level' => '-1',
			'limit' => '999',
			'disabled' => '-1',
			'prefix' => '|- ',
			'add_prefix' => '&nbsp;&nbsp;&nbsp;',
			'return_line' => array()
		);
		$set = self::array_overlay($default_set,$set);
		$default_pipe = array(
			'return_line' => array(
				'node' => -1,
				'data' => array(),
			),
			'parent' => array(),
			'child' => array(),
		);
		$pipe = self::array_overlay($default_pipe,$pipe);
		$field_self = $set['field']['self'];
		$field_parent = $set['field']['parent'];
		$result = '';
		/* 循环生成结构 */
		foreach($tree as $key => $row){
			$temp_set = $set;
			$temp_set['level'] += 1;
			if($temp_set['level']>$set['limit']) continue;
			if($temp_set['level']>0){
				$temp_set['prefix'] = $set['add_prefix'] . $set['prefix'];
			}
			if(!is_array($row['child']) || !count($row['child'])){
				$f = $set['function']['replace_self'];
				$pipe['child'][$row['data'][$field_self]] = $row['data'];
			}
			else{
				$temp_set['node'] = $key;
				$row['data']['tpl_child'] = self::tree_to_show($row['child'],$temp_set,$pipe);
				$f = $set['function']['replace_parent'];
				$pipe['parent'][$row['data'][$field_self]] = $row['data'];
			}
			switch($pipe['return_line']['node']){
				case -1:
				case $key:
					$pipe['return_line']['node'] = isset($row['data'][$field_parent])?$row['data'][$field_parent]:0;
					$pipe['return_line']['data'][] = $row['data'];
					$temp_set['return_line'][] = $key;
					$set['return_line'][] = $key;
					break;
				default:
					;
			}
			$result .= call_user_func_array($f,array($row['data'],$temp_set));
		}
		return $result;

	}

	/**
	 * 数组变成下拉框，子级回调函数
	 * @param		array		$row		当前数据
	 * @param		array		$set		配置数组(参照tree_to_show函数)
	 * @return	string	$result	结果模版
	 * @time 2017-06-16
	 * @author	秦晓武
	 **/
	static function select_self($row,$set=array()){
		$tpl = '<option data-level="%level" data-parent="%parent" value="%id" %disabled %selected>%title</option>';
		if(in_array($row[$set['field']['self']],$set['now'])){
			$replace['%selected'] = 'selected="selected"';
		}
		else{
			$replace['%selected'] = '';
		}
		if($set['level']<$set['disabled']){
			$replace['%disabled'] = 'disabled="disabled"';
		}
		else{
			$replace['%disabled'] = '';
		}
		$replace['%id'] = $row[$set['field']['self']];
		$replace['%parent'] = '';
		if(isset($row[$set['field']['parent']])){
			$replace['%parent'] = $set['field']['relation']?$row[$set['field']['relation']]:$row[$set['field']['parent']];
		}
		$replace['%level'] = $set['level'];
		$replace['%title'] = $set['prefix'] . $row[$set['show']];
		$replace_a = [];
		$replace_b = [];
		foreach($replace as $k => $v){
			$replace_a[] = $k;
			$replace_b[] = $v;
		}
		return str_replace($replace_a,$replace_b,$tpl);
	}

	/**
	 * 数组变成下拉框，父级回调函数
	 * @param array $row 当前数据
	 * @param array $set 配置数组(@see tree_to_show)
	 * @return string 结果模版
	 * @time 2017-06-16
	 * @author 秦晓武
	 */
	static function select_parent($row,$set=array()){
		$tpl = '<option data-level="%level" data-parent="%parent" value="%id" %disabled %selected>%title</option>%tpl_child';
		if(in_array($row[$set['field']['self']],$set['now'])){
			$replace['%selected'] = 'selected="selected"';
		}
		else{
			$replace['%selected'] = '';
		}
		if($set['level']<$set['disabled']){
			$replace['%disabled'] = 'disabled="disabled"';
		}
		else{
			$replace['%disabled'] = '';
		}
		$replace['%id'] = $row[$set['field']['self']];
		$replace['%parent'] = $set['field']['relation']?$row[$set['field']['relation']]:$row[$set['field']['parent']];
		$replace['%level'] = $set['level'];
		$replace['%title'] = $set['prefix'] . $row[$set['show']];
		$replace['%tpl_child'] = $row['tpl_child'];
		$replace_a = [];
		$replace_b = [];
		foreach($replace as $k => $v){
			$replace_a[] = $k;
			$replace_b[] = $v;
		}
		return str_replace($replace_a,$replace_b,$tpl);
	}
	/**
	 * 数组变成面包屑(无限级)
	 * @param	array	$data	原始数组（数据库查出的结果集）
	 * @param	int		$id		当前菜单ID
	 * @param	array	$set	配置数组(参照tree_to_show函数)
	 * @return	string	$result	下拉框的option
	 * @time 2015-05-22
	 * @author	秦晓武
	 **/
	static function array_to_crumbs($data = array(),$id = '-2', $set = array()){
		$tree = self::array_to_tree($data, $set);
		$pipe['return_line']['node'] = $id;
		$set['function']['replace_self'] = 'self::crumbs_self';
		$set['function']['replace_parent'] = 'self::crumbs_parent';
		return self::tree_to_show($tree,$set,$pipe);
	}

	/**
	 * 生成子级结构(array_to_crumbs的回调函数)
	 * @param		array		$row		当前数据
	 * @param		array		$set		配置数组(参照tree_to_show函数)
	 * @return	string	$result	结果模版
	 * @time 2015-05-22
	 * @author	秦晓武
	 **/
	static function crumbs_self($row,$set= array()){
		if(in_array($row[$set['field']['self']],$set['return_line'])){
			return $row[$set['show']];
		}
	}

	/**
	 * 生成父级结构(array_to_crumbs的回调函数)
	 * @param array $row 当前数据
	 * @param array $set 配置数组(参照tree_to_show函数)
	 * @return string $result 结果模版
	 * @time 2015-05-22
	 * @author 秦晓武
	 **/
	static function crumbs_parent($row,$set= array()){
		if(in_array($row[$set['field']['self']],$set['return_line'])){
			return $row[$set['show']] . ' &gt; ' . $row['tpl_child'];
		}
	}

	/**
	 * 获取所有子级数据ID
	 * @需求：通过ID获取所有子级数据
	 * @流程：
	 * 1.获取所有数据
	 * 2.转换成树型
	 * 3.在树内搜索
	 * 4.转换子节点为数组
	 * 5.取出子节点ID
	 * @param  array  $all     数据
	 * @param  string  $id     节点ID
	 * @param  boolean $self   是否包含自身
	 * @return string  $result 结果数组
	 * @time 2015-05-22
	 * @author 秦晓武
	 * @example 传入0，返回[0,1,2,3]
	 */
	static function get_all_child_ids($all= array(), $id = 0,$self=true){
		/*转换成树型*/
		$tree = self::array_to_tree($all);
		/*在树内搜索*/
		$sub_tree = self::search_in_tree($tree,$id);
		/*转换子节点为数组*/
		$temp_tree = $self ? array($sub_tree) : $sub_tree['child'];
		$child = self::tree_to_array($temp_tree);
		/*取出子节点ID*/
		$child_ids = array_keys($child);
		return $child_ids;
	}

	/**
	 * 树内搜索
	 * @param array $tree 树型数组（data为父级数据，child为子级数据）
	 * @param string $key 数据
	 * @return array 返回整个节点数据
	 * @time 2015-06-06
	 * @author 秦晓武
	 */
	static function search_in_tree($tree,$key = ''){
		if(!is_array($tree)) return '';
		if(isset($tree[$key])) return $tree[$key];
		foreach($tree as $value){
			$row = self::search_in_tree($value,$key);
			if($row) return $row;
		}
	}

	/**
	 * xml转数组
	 * @param \SimpleXMLElement $xml
	 * @param bool $root
	 *
	 * @return array|string
	 */
	static function xml_to_arr (\SimpleXMLElement $xml, $root = false) {

		if (!$xml->children()) {
			return (string) $xml;
		}
		$array = array();
		foreach ($xml->children() as $element => $node) {
			$totalElement = count($xml->{$element});
			if (!isset($array[$element])) {
				$array[$element] = "";
			}
			// Has attributes
			if ($attributes = $node->attributes()) {
				$data = array(
					'attributes' => array(),
					'value' => (count($node) > 0) ? self::xml_to_arr($node, false) : (string) $node
				);
				foreach ($attributes as $attr => $value) {
					$data['attributes'][$attr] = (string) $value;
				}
				if ($totalElement > 1) {
					$array[$element][] = $data;
				} else {
					$array[$element] = $data;
				}
			// Just a value
			} else {
				if ($totalElement > 1) {
					$array[$element][] = self::xml_to_arr($node, false);
				} else {
					$array[$element] = self::xml_to_arr($node, false);
				}
			}
		}
		if ($root) {
			return array($xml->getName() => $array);
		} else {
			return $array;
		}

	}

    /**
     * 数组变成option(单级)
     * @param	array	$data	原始数组（数据库查出的结果集）
     * @param	int		$id		当前菜单ID
     * @return	string	$result	下拉框的option
     * @time 2017-10-17
     * @author	liuw
     **/
    static function array_to_options($data = array(),$id = null){
        $op = '';
        foreach ($data as $k => $v){
            if ( $k == $id && $id !== null && $id !== ''){
                $op .= '<option selected="selected" value="'. $k .'">'.$v.'</option>';
            }else{
                $op .= '<option value="'. $k .'">'.$v.'</option>';
            }
        }
        return $op;
    }
}