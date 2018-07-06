<?php
namespace api\shell\controller;
use think\Db;
use think\Cache;
use think\Controller;
class Index extends Controller
{


    public function index()
    {	echo 'welcome to use adsystem api!!!!';
    	//error_log('111111'.PHP_EOL,3,'/home/projects/adsystem/error_'.date('Ymd').'.log');exit;

    }

    public function clean_redis(){
    	echo 111;exit;
    	$redis = new \redis();  
		//$redis->connect('172.30.2.132', 6379);
		$redis->connect(config('redis.host'), config('redis.port'));
		$redis->flushDB();
		echo "缓存清除成功";exit;//先清除所有广告缓存
    }


    public function insert_sql(){
    	ini_set('memory_limit','1024M');
    	$redis = new \redis();  
		//$redis->connect('172.30.2.132', 6379);
		$redis->connect(config('redis.host'), config('redis.port'));
		$redis->renamenx('ad','ad_ready');//把要记录的redis key先重命名 然后方便删除
		$new_arr= array_filter(explode(";",$redis->get('ad_ready')));
		//error_log(print_r($new_arr,1),3,'/home/httpd/adsystem/runtime/recorddata_'.date('Ymd').'.log');
		$i = 0;

		foreach ($new_arr as $k => $v) {
			
			$new_arr[$i] = explode(",",$v);
			//获取数据start
			$data[$i]['adsystemid'] = isset($new_arr[$i][0])?$new_arr[$i][0]:'';
			$data[$i]['advertisementid'] = isset($new_arr[$i][1])?$new_arr[$i][1]:'';
			$data[$i]['materialid'] = isset($new_arr[$i][2])?$new_arr[$i][2]:'';
			$data[$i]['userid'] = isset($new_arr[$i][3])?$new_arr[$i][3]:'';
	    	$data[$i]['ip'] = isset($new_arr[$i][4])?$new_arr[$i][4]:'';
	    	$data[$i]['country'] = isset($new_arr[$i][5])?$new_arr[$i][5]:'';
	    	$data[$i]['province'] = isset($new_arr[$i][6])?$new_arr[$i][6]:'';
	    	$data[$i]['city'] = isset($new_arr[$i][7])?$new_arr[$i][7]:'';
	    	$data[$i]['server_name'] = isset($new_arr[$i][8])?$new_arr[$i][8]:'';
	    	$data[$i]['userAgent'] = isset($new_arr[$i][9])?$new_arr[$i][9]:'';
	    	$data[$i]['create_time'] =  isset($new_arr[$i][10])?$new_arr[$i][10]:'';
	    	$data[$i]['cost'] =  isset($new_arr[$i][11])?$new_arr[$i][11]:'';
	    	$data[$i]['remain'] =  isset($new_arr[$i][12])?$new_arr[$i][12]:'';
	    	if($data[$i]['remain'] < 0){
	    		$data[$i]['remain'] = 0;
	    	}
	    	$i++;
	    	//获取数据end
		}
		if(!empty($data)){
			Db::table('record')->insertAll($data);
			$redis->delete('ad_ready');// 删除键值 可以传入数组 array('key1','key2')删除多个键
			//error_log(print_r($data,1),3,'/home/httpd/adsystem/runtime/recorddatas_'.date('Ymd').'.log');
		}
		

    }

    public function insert_click(){
    	$redis = new \redis();  
		$redis->connect(config('redis.host'), config('redis.port'));
		$redis->renamenx('adclick','adclick_ready');//把要记录的redis key先重命名 然后方便删除
		$new_arr= array_filter(explode(";",$redis->get('adclick_ready')));
		$i = 0;

		foreach ($new_arr as $k => $v) {
			
			$new_arr[$i] = explode(",",$v);
			//获取数据start
			$data[$i]['adsystemid'] = isset($new_arr[$i][0])?$new_arr[$i][0]:'';
			$data[$i]['advertisementid'] = isset($new_arr[$i][1])?$new_arr[$i][1]:'';
			$data[$i]['materialid'] = isset($new_arr[$i][2])?$new_arr[$i][2]:'';
			$data[$i]['userid'] = isset($new_arr[$i][3])?$new_arr[$i][3]:'';
	    	$data[$i]['ip'] = isset($new_arr[$i][4])?$new_arr[$i][4]:'';
	    	$data[$i]['server_name'] = isset($new_arr[$i][5])?$new_arr[$i][5]:'';
	    	$data[$i]['create_time'] =  isset($new_arr[$i][6])?$new_arr[$i][6]:'';
	    	$data[$i]['cost'] =  isset($new_arr[$i][7])?$new_arr[$i][7]:'';
	    	$i++;
	    	//获取数据end
		}
		if(!empty($data)){
			Db::table('record_click')->insertAll($data);
			$redis->delete('adclick_ready');// 删除键值 可以传入数组 array('key1','key2')删除多个键
		}	

    }

    /**
     * 定时任务------生成用户金额缓存   可以不用
     */

     public function create_userinfo(){
     	$redis = new \redis();  
		//$redis->connect('172.30.2.132', 6379);
		$redis->connect(config('redis.host'), config('redis.port'));
		$user_infos = Db::table('userinfo')->select()->toarray();
		if(!$user_infos){
			return false;
		}
		foreach ($user_infos as $key => $value) {
			$redis_key = "userinfo_".$value['uid'];//用户资金缓存key
			$isset = $redis->get($redis_key);
			//已经存在的不需要重新生成（只对那些没有缓存的）
			if(!$isset){
				$redis_value = $value['account'];
				$redis->set($redis_key,$redis_value);//设置缓存key value
			}
			
		}
		
     }


}
