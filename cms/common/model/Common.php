<?php
namespace cms\common\model;

use think\Model;

/**
 * 基础模型类
 * @package app\common\model
 * @author qinxw
 * @update 2017-03-14
 */
class Common extends Model
{
    //use \traits\model\SoftDelete;
    protected $autoWriteTimestamp = 'datetime';
    protected $dateFormat = 'Y-m-d H:i:s';
    /*protected $type = [
        'delete_time' => 'datetime:Y-m-d H:i:s',
        'delete_time' => 'datetime:Y-m-d H:i:s',
        'delete_time' => 'datetime:Y-m-d H:i:s',
    ];
    protected $deleteTime = 'delete_time';*/
    protected $insert = ['state' => 1];
    protected $field = true;

    /**
     * 根据表单生成查询条件
     * 进行列表过滤
     *
     * 过滤条件
     * $map['_table']       可强制设置表名前缀
     * $map['_relation']    可强制设置关联模型预载入(需在模型里定义)
     * $map['_field']       可强制设置字段
     * $map['_order_by']    可强制设置排序字段(field asc|desc[,filed2 asc|desc...]或者false)
     * $map['_func']        匿名函数，可以给模型设置属性，比如关联，alias，function ($model) {$model->alias('table')->join(...)}
     *
     * @param array    $map      过滤条件
     * @param string   $field    查询的字段
     * @param string   $order   排序
     * @param boolean  $by      是否正序
     *
     * @return array 查询结果
     */
    public function getPageList($map, $field = [], $order = 'id', $by = 'desc')
    {
        $request = \think\Request::instance();
        // 排序字段
        $order = $request->param('_order') ?: $order;
        // 排序方式
        $by = $request->param('_sort') ?: $by;
        // 私有字段，指定特殊条件，查询时要删除
        $protectField = ['_table', '_relation', '_field', '_order_by', '_paginate', '_model', '_func'];


        if (isset($map['_func']) && ($map['_func'] instanceof \Closure)) {
            call_user_func_array($map['_func'], [$this]);
        }


        // 设置关联预载入
        if (isset($map['_relation'])) {
            $this->with($map['_relation']);
        }

        // 设置字段
        if (isset($map['_field'])) {
            $field = $map['_field'];
        }

        // 设置有$map['_table']表示存在关联模型
        if (isset($map['_table'])) {
            // 给排序字段强制加上表名前缀
            if (strpos($order, ".") === false) {
                $order = $map['_table'] . "." . $order;
            }
            // 给字段强制加上表名前缀
            $_field = is_array($field) ? $field : explode(",", $field);
            foreach ($_field as &$v) {
                if (strpos($v, ".") === false) {
                    $v = preg_replace("/([^\s\(\)]*[a-z0-9\*])/", $map['_table'] . '.$1', $v, 1);
                }
            }
            $field = implode(",", $_field);
            // 给查询条件强制加上表名前缀
            foreach ($map as $k => $v) {
                if (!in_array($k, $protectField) && strpos($k, ".") === false) {
                    $map[$map['_table'] . '.' . $k] = $v;
                    unset($map[$k]);
                }
            }
        }

        // 设置排序字段 防止表无主键报错
        $order_by = isset($map['_order_by']) ? $map['_order_by'] : ($order . ' ' . $by);


        // 删除设置属性的字段
        foreach ($protectField as $v) {
            unset($map[$v]);
        }
        $this->field($field)->where($map)->order($order_by);

        $list = $this->paginate($request->param('numPerPage'), false, ['query' => $request->get()]);

        // 返回值
        return $list;
    }
    /**
     * 查询默认不包含软删除数据（原方法有问题）
     * @access protected
     * @param \think\db\Query $query 查询对象
     * @return void
     */
    protected function base(\think\db\Query $query)
    {
        if(!$query->getOptions('where')) {
            $field = $this->getDeleteTimeField(true);
            $query->whereNull($field);
        }
    }
}

/* End of file Common.php */
/* Location: ./application/common/model/Common.php */