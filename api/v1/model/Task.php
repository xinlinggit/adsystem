<?php

namespace api\v1\model;

use think\Model;
use think\Db;
use think\Request;

class Task extends Model
{
    public function getDeleteTimeAttr($value)
    {
        return $value === null ? 0 : 1;
    }
    public function getStatusAttr($value)
    {
        return $value === null ? -1 : $value;
    }
    public function getImageAttr($value)
    {
        //return str_replace('taskbao.cms.cnfol.com','taskbao.dev.cnfol.wh',$value);
        return config('app_domain') . $value;
    }

    /*
     * 查询任务列表
     */
    public function getList($id, $limit, $userid)
    {
        $limit || $limit = 10;
        $query = $this->alias('t')->field('t.id,title,price,start_time,deadline,d.status')->where('delete_time is null')->where('deadline >= \''.date('Y-m-d').'\'')->where('start_time <= \''.date('Y-m-d').'\'');
        if ($id) $query->where("t.id < $id");
        if ($userid) {
            $query->join('donetask d', "task.id = d.taskid and d.userid = $userid", 'left')->where('d.status<2 or status is null');
        } else {
            $query->join('donetask d', "d.userid is null", 'left');
        }
        $query->order('task.id', 'desc')->limit($limit);
        $data = $query->select()->toArray();
        return $data;
    }
    /*
     * 获取一条任务详情
     */
    public function getOne($id,$userid){
        $query = $this->alias('t')->field('t.id,title,description,image,price,start_time,deadline,d.status,d.reason')->where('t.id',$id);
        if ($userid) {
            $query->join('donetask d', "t.id = d.taskid and d.userid = $userid", 'left');
        } else {
            $query->join('donetask d', "d.userid is null", 'left');
        }
        $data = $query->find();
        return empty($data) ? null : $data->toArray();
    }




}