<?php
namespace api\operation\controller;
use think\Db;
use think\Cache;
use think\Controller;
class Index extends Controller
{


    public function index()
    {	echo 'welcome';
    	//error_log('111111'.PHP_EOL,3,'/home/projects/adsystem/error_'.date('Ymd').'.log');exit;

    }


     /**
      * curl_get接口------用户充值
      */
     public function add_money($uid,$money){
     	header("Access-Control-Allow-Origin: *");//同源策略 跨域请求 头设置
    	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		header('Access-Control-Allow-Methods: GET, POST, PUT');
        header('Content-type: application/json');
     	$redis = new \redis();  
		$redis->connect('172.30.2.132', 6379);
		$uid = $_REQUEST['uid'];
		$money = $_REQUEST['money'];
		$user_info = Db::table('userinfo')->where(array('uid'=>$uid))->find();
		if(!$user_info){
			echo json_encode(0);exit;
		}
		$redis_key = "userinfo_".$uid;//用户资金缓存key
		$isset = $redis->get($redis_key);

		if(!$isset){
			
			$redis_value = $user_info['account']+$money;
			$back = $redis->set($redis_key,$redis_value);//设置缓存key value
			if($back){
				Db::table('userinfo')->where(array('uid'=>$uid))->update(['account'=>$redis_value]);
				echo json_encode(1);exit;
			}else{
				echo json_encode(0);exit;
			}
		}else{
			if($isset < 0){
				$isset = 0;
			}
			$back = $redis->set($redis_key,$isset+$money);//设置缓存key value Incrbyfloat($redis_key,$money);
			if($back){
				Db::table('userinfo')->where(array('uid'=>$uid))->update(['account'=>$isset+$money]);
				echo json_encode(1);exit;
			}else{
				echo json_encode(0);exit;
			}
		}	

     }


     /**
      * curl_get接口------用户余额更新
      */

     public function update_money(){
     	header("Access-Control-Allow-Origin: *");//同源策略 跨域请求 头设置
    	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		header('Access-Control-Allow-Methods: GET, POST, PUT');
        header('Content-type: application/json');
     	$redis = new \redis();  
		$redis->connect('172.30.2.132', 6379);
		$uid = $_REQUEST['uid'];
		$user_info = Db::table('userinfo')->where(array('uid'=>$uid))->find();
		if(!$user_info){
			echo json_encode(0);exit;
		}
		$redis_key = "userinfo_".$uid;//用户资金缓存key
		$isset = $redis->get($redis_key);

		if($isset){
			$result = Db::table('userinfo')->where(array('uid'=>$uid))->update(['account'=>$isset]);
		}
		echo json_encode(1);exit;

     }


     /**
      * curl_get接口-----更新广告位
      */
     
    public function update_adsense($adsenseid){
     	$redis = new \redis();  
		$redis->connect('172.30.2.132', 6379);
		$adsense_data = Db::table('adsense')->where(array('id'=>$adsenseid))->find();
		if(!$adsense_data){
			echo json_encode(0);exit;
		}
		
		$redis_key = 'adsense_'.$adsenseid;//广告位缓存key
		$isset = $redis->get($redis_key);
		if($isset){
			$redis->delete($redis_key);
		}

		echo json_encode(1);exit;

    }

    /**
     * curl_get接口-----更新广告数据
     */

    public function update_advertisement($advertisementid){
    	$redis = new \redis();  
		$redis->connect('172.30.2.132', 6379);
		$advertisement_data = Db::table('advertisement')->where(array('id'=>$advertisementid))->find();

		if(!$advertisement_data){
			echo json_encode(0);exit;
		}

		$redis_key = 'advertisement_*_'.$advertisement_data['id'];//该条广告的key

		$view_redis_key = 'view_advertisement_*_'.$advertisement_data['id'];//该条广告的key

		$isset = $redis->keys($redis_key);

		$view_isset = $redis->keys($view_redis_key);

		if($isset){
			$redis->delete($isset);
			$redis->delete($view_isset);
		}

		echo json_encode(1);exit;

    }

    public function update_advertisement_running_status($advertisementid,$running_status){
    	$redis = new \redis();  
		$redis->connect('172.30.2.132', 6379);
		$advertisement_data = Db::table('advertisement')->where(array('id'=>$advertisementid))->find();

		if(!$advertisement_data){
			echo json_encode(0);exit;
		}

		$redis_key = 'advertisement_'.$advertisement_data['adsenseid'].'_'.$advertisement_data['id'];//该条广告的key

		$isset = $redis->get($redis_key);

		if($isset){
			$value = explode(',',$isset);
			$value[12] = $running_status;
			$redis->set($redis_key,$value);

		}

		echo json_encode(1);exit;

    }


    /**
     * curl_get接口-----更新素材
     */
    
    /*public function update_material(){
    	$materialid = input('materialid');
    	$redis = new \redis();
		$redis->connect('172.30.2.132', 6379);
		$map['id'] = array('in',$materialid);
		$material_data = Db::table('material_main')->where($map)->field('id')->select()->toarray();
		foreach ($material_data as $key => $value) {
			$redis_key = 'material_'.$value['id'];

			$isset = $redis->get($redis_key);
			if($isset){
				$redis->delete($redis_key);
			}
		}
		echo json_encode(1);exit; 
    }*/
}
