<?php
$method = array('add','del','edit');
if (! $action = $_REQUEST['m'])die('404 NOT FOUND');
if (!in_array($action,$method))die('404 NOT FOUND');
$lists_file = './transform.json';
header('content=application/json;charset=utf-8');
switch ($action){
    case 'add':add();break;
    case 'del':del();break;
    default:die('404 NOT FOUND');
}

function add(){
    global $lists_file;
    $tc = $_REQUEST['tc'];
    $sm = $_REQUEST['sm'];
    if (!$tc || !$sm){
        echo json_encode(array('code'=>1,'msg'=>'参数错误！','data'=>''));exit;
    }
    if (file_exists($lists_file)){
        $lists = file_get_contents($lists_file);
        if ($lists) {
            $lists = json_decode($lists,true);
            foreach ($lists as $v){
                if ($v['sm'] == $sm){
                    echo json_encode(array('code'=>1,'msg'=>'词组已存在！请勿重复添加！','data'=>''));exit;
                }
            }
            array_push($lists,array('sm'=>$sm,'tc'=>$tc,'addtime'=>date('Y-m-d H:i')));
            $lists = json_encode($lists);
            file_put_contents($lists_file,$lists,LOCK_EX);
        } else {
            $lists = array(array('sm'=>$sm,'tc'=>$tc,'addtime'=>date('Y-m-d H:i')));
            $lists = json_encode($lists);
            file_put_contents($lists_file,$lists,LOCK_EX);
        }
        echo json_encode(array('code'=>0,'msg'=>'添加成功！','data'=>''));exit;
    } else {
        $lists_file = fopen("transform.json", "w") or die("Unable to touch file!");
        $lists = array(array('sm'=>$sm,'tc'=>$tc,'addtime'=>date('Y-m-d H:i')));
        $lists = json_encode($lists);
        fwrite($lists_file, $lists);
        fclose($lists_file);
        //file_put_contents($lists_file,$lists,LOCK_EX);
        echo json_encode(array('code'=>0,'msg'=>'添加成功！','data'=>''));exit;
    }
}

function del(){
    global $lists_file;
    $id = (int)$_REQUEST['id'];
    $sm = $_REQUEST['sm'];
    if (!is_int($id) || !$sm ){
        echo json_encode(array('code'=>1,'msg'=>'参数错误！','data'=>''));exit;
    }
    $lists = file_get_contents($lists_file);
    $lists = json_decode($lists,true);
    $del_item = array_slice($lists,$id,1);
    if ($del_item[0] && $del_item[0]['sm'] == $sm){
        array_splice($lists,$id,1);
        $lists = json_encode($lists);
        file_put_contents($lists_file,$lists,LOCK_EX);
        echo json_encode(array('code'=>0,'msg'=>'删除成功！','data'=>''));exit;
    } else {
        echo json_encode(array('code'=>1,'msg'=>'要删除的词汇不存在！','data'=>''));exit;
    }
}