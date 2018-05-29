<?php
namespace api\v1\model;

use think\Model;
use think\Exception\PDOException;
use think\Exception\DBException;
use think\Db;
class Donetask extends Model
{
    // 定义关联模型
    public function task()
    {
        // 一对一关联
        return $this->hasOne('Task','id','taskid',[],'INNER');
        //->setEagerlyType(0);//V5.0.5+版本开始，默认使用IN查询方式，此处改为JOIN查询方式
    }

    /*
     * 提交已完成任务
     */
    public function submitTask($data){
        try {
            $this->where('status',1)->where(['taskid'=>$data['taskid'],'userid'=>$data['userid']])->delete();
            $this->isUpdate(false)->allowField('taskid,userid,result')->save($data);
            return $this->getData('id');
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        } catch (DbException $e) {
            $this->error = $e->getMessage();
            return false;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /*
     * 我的已完成列表
     */
    public function myDone($userid, $id, $limit){
        $limit || $limit = 10;
        $query = $this->alias('d')->field('d.id,t.title,t.price,d.create_time,d.check_time');
        $query->join('task t', "t.id = d.taskid", 'inner')->where("d.userid = $userid  and d.status=2");
        if ($id) $query->where("d.id < $id");
        $query->order('d.id', 'desc')->limit($limit);
        $data = $query->select()->toArray();
        return $data;
    }
    
}