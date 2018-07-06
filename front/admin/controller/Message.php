<?php
namespace app\admin\controller;
use think\Db;
use think\Request;

/**
 * 系统通知
 * @package app\admin\controller
 */
class Message extends Admin
{
    /**
     * 系统通知
     */
    public function index(Request $rq)
    {
        if ($this->request->isGet()) {
            $map = array();
            // 处理查询时间的字符串
            $time = $rq->get('time');
            $time = preg_replace('# #', '', $time);
            if ($time) {
                $start_time = substr($time, 0, 10);
                $end_time = substr($time, 11, 10);
                $time = [];
                array_push($time, $start_time);
                array_push($time, $end_time);

                $map['operate_time'] = array('between time', $time);
            }
        }
        // 查询所有通知
        $messages = Db::table('information')->where($map)->order('create_time desc')
            ->paginate(10);
        $page = $messages->render();
//        return ['row' => $row1, 'pages' => $pages1];
        $this->assign('messages', $messages);
        $this->assign('page', $page);
        $time = $rq->get('time');
        $this->assign('time', $time); // 保留搜索条件
        return $this->fetch();
    }

    public function readMsg(Request $rq)
    {
        $msg_id = $rq->get('id');
        $info = Db::table('information')->where('id', '=', $msg_id)->find();
        return json($info);
        $this->assign('info', $info);
    }
}