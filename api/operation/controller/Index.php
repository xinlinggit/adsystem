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
     public function add_money($uid,$money,$key){
     	header("Access-Control-Allow-Origin: *");//同源策略 跨域请求 头设置
    	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		header('Access-Control-Allow-Methods: GET, POST, PUT');
        header('Content-type: application/json');
        $key = $_REQUEST['key'];
        if(empty($key) || $key != config('pay_keys')){
        	echo json_encode(0);exit;
        }
     	$redis = new \redis();  
		//$redis->connect('172.30.2.132', 6379);
		$redis->connect(config('redis.host'), config('redis.port'));
		$uid = $_REQUEST['uid'];
		$money = $_REQUEST['money'];
		$user_info = Db::table('userinfo')->where(array('uid'=>$uid))->find();
		if(!$user_info){
			echo json_encode(0);exit;
		}
		$redis_key = "userinfo_".$uid;//用户资金缓存key
		$isset = $redis->get($redis_key);
		/*$info = array('uid'=>$uid,'money'=>$money);
		error_log($info.PHP_EOL,3,'/home/httpd/adsystem/runtime/charge_'.date('Ymd').'.log');*/
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
		//$redis->connect('172.30.2.132', 6379);
		$redis->connect(config('redis.host'), config('redis.port'));
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
		//$redis->connect('172.30.2.132', 6379);
		$redis->connect(config('redis.host'), config('redis.port'));
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
		//$redis->connect('172.30.2.132', 6379);
		$redis->connect(config('redis.host'), config('redis.port'));
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
		//$redis->connect('172.30.2.132', 6379);
		$redis->connect(config('redis.host'), config('redis.port'));
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


public function asClick(){
		$redis = new \redis();
		$redis->connect(config('redis.host'), config('redis.port'));

    	$adsId = input('adsId');
    	if(empty($adsId)){
    		$this->redirect("http://www.cnfol.com/");exit;
    	}
    	$advId = input('advId');
    	if(empty($redis->keys('advertisement_*_'.$advId)) && $advId >0 ){
    		$this->redirect("http://www.cnfol.com/");exit;
    	}
    	$userId = input('userId');
    	$ip = $this->request->ip();
    	$mId = input('mId');
    	$spending = input('spending');
    	$cost = 0;

    	if($spending == 3){
    		$ad_key = 'advertisement_0_'.$advId;
    		$ad_view = $redis->get('view_advertisement_0_'.$advId);

    		$ad_value = $redis->get($ad_key);
    		if($ad_value){
    			$ads_value = explode(',',$ad_value);
    			$user_money = $redis->get('userinfo_'.$ads_value[10]);
    			
    			if($user_money && $ads_value[11] == 3 && $ads_value[8] > $ad_view){
    				if($user_money >= $ads_value[4]){
    					$redis->incr('view_advertisement_0_'.$advId);
    					$redis->Incrbyfloat('userinfo_'.$ads_value[10],'-'.$ads_value[4]); //减该用户的缓存冻结金额
    					$cost = $ads_value[4];
    				}
    				
    			}

    		}

    	}
    	$the_host =  isset($_SERVER["HTTP_REFERER"])?$_SERVER["HTTP_REFERER"]:'';

    	$redis_key = 'adclick';
    	$redis_value = ';'.$adsId.','.$advId.','.$mId.','.$userId.','.$ip.','.$the_host.','.date("Y-m-d H:i:s").','.$cost;

    	$redis->append($redis_key,$redis_value);

    	$url = input('url');
    	error_log($the_host.PHP_EOL,3,'/home/httpd/adsystem/runtime/click_'.date('Ymd').'.log');
    	$this->redirect($url);
    }

    public function getClick(){
    	$redis = new \redis();
		$redis->connect(config('redis.host'), config('redis.port'));
		dump($redis->get('adclick'));exit;
    }
}
