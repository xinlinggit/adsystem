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
		$redis->connect('172.30.2.132', 6379);
		$redis->flushDB();
		echo "缓存清除成功";exit;//先清除所有广告缓存
    }


    /*public function insert_sql(){
    	$redis = new \redis();  
		$redis->connect('127.0.0.1', 6379);
		//$redis->flushDB();exit;
		$keys_arr = $redis->keys('ad_*');
		//dump($keys_arr);exit;
		//print_r($redis->get('ad_1522744293'));exit;
		foreach ($keys_arr as $key => $value) {
			$new_arr[$key] = array_filter(explode("|",$redis->get($value)));
			//dump($new_arr[$key]);exit;
			foreach ($new_arr[$key] as $k => $v) {
				$new_arr[$key][$k] = array_filter(explode(",",$v));
				//获取数据start
				$data[$k]['adsystemid'] = isset($new_arr[$key][$k][0])?$new_arr[$key][$k][0]:'';
				$data[$k]['advertisementid'] = isset($new_arr[$key][$k][1])?$new_arr[$key][$k][1]:'';
				$data[$k]['materialid'] = isset($new_arr[$key][$k][2])?$new_arr[$key][$k][2]:'';
				$data[$k]['userid'] = isset($new_arr[$key][$k][3])?$new_arr[$key][$k][3]:'';
		    	$data[$k]['ip'] = isset($new_arr[$key][$k][4])?$new_arr[$key][$k][4]:'';
		    	$data[$k]['country'] = isset($new_arr[$key][$k][5])?$new_arr[$key][$k][5]:'';
		    	$data[$k]['province'] = isset($new_arr[$key][$k][6])?$new_arr[$key][$k][6]:'';
		    	$data[$k]['city'] = isset($new_arr[$key][$k][7])?$new_arr[$key][$k][7]:'';
		    	$data[$k]['server_name'] = isset($new_arr[$key][$k][8])?$new_arr[$key][$k][8]:'';
		    	$data[$k]['userAgent'] = isset($new_arr[$key][$k][9])?$new_arr[$key][$k][9]:'';
		    	$data[$k]['create_time'] =  isset($new_arr[$key][$k][10])?$new_arr[$key][$k][10]:'';
		    	//获取数据end
			}
			Db::table('record')->insertAll($data);
		}
		$redis->delete($keys_arr);// 删除键值 可以传入数组 array('key1','key2')删除多个键
		echo "数据插入成功";exit;

    }*/

    public function insert_sql(){

    	$redis = new \redis();  
		$redis->connect('172.30.2.132', 6379);
		$redis->renamenx('ad','ad_ready');//把要记录的redis key先重命名 然后方便删除
		$new_arr= array_filter(explode("/",$redis->get('ad_ready')));

		$i = 0;

		foreach ($new_arr as $k => $v) {
			
			$new_arr[$i] = array_filter(explode(",",$v));
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
	    	$i++;
	    	//获取数据end
		}
		if(!empty($data)){
			Db::table('record')->insertAll($data);
			$redis->delete('ad_ready');// 删除键值 可以传入数组 array('key1','key2')删除多个键
		}
		

		

    }

    /**
     * 定时任务------生成用户金额缓存   可以不用
     */

     public function create_userinfo(){
     	$redis = new \redis();  
		$redis->connect('172.30.2.132', 6379);
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




     /**
      * 定时任务------更新用户金额(移到create里去了)
      */
     
    public function update_userinfo(){
    	echo 111;exit;
     	$redis = new \redis();  
		$redis->connect('172.30.2.132', 6379);
		$key_data = 'userinfo_*';
		$keys_arr = $redis->keys($key_data);//查找获取该广告位所有的广告的 key

		foreach ($keys_arr as $key => $value) {
			$data[$key] = $redis->get($value);
			$uid[$key] = explode('_',$value);
			$result = Db::table('userinfo')->where(array('uid'=>$uid[$key][1]))->update(['account'=>$data[$key]]);
		}

		echo "用户资金更新成功";exit;
    }


    /**
     * 定时任务----处理 已过期            或者已删除的广告 针对用户删除的广告把status变成4 并且删除广告缓存 和 广告浏览量缓存 还没写完
     */
    
    public function operate_advertisements(){

    	$redis = new \redis();  
		$redis->connect('172.30.2.132', 6379);
		$map['status'] = array('in','2,3');
		$advertisements = Db::table('advertisement')->where($map)->select()->toarray();
    }


    /**
     * 定时任务------生成(对应广告位) 广告模型 暂时没用
     * 
     */
	public function update_advertisements(){
		echo 111;exit;
		$redis = new \redis();  
		$redis->connect('172.30.2.132', 6379);
		
		$advertisements_keys = $redis->keys('advertisement_*');
		
		if(!empty($advertisements_keys)){

			foreach ($advertisements_keys as $key => $value) {
				$advertisements_key[$key] = explode('_',$value);
				$map['id'] = $advertisements_key[$key][2];
				$map['status'] = array('in','2,3');
				$advertisements_data = Db::table('advertisement')->where($map)->find();

				$redis_key[$key] = 'advertisement_'.$advertisements_data['adsenseid'].'_'.$advertisements_data['id'];//该条广告的key

				$redis_key_view[$key] = 'view_advertisement_'.$advertisements_data['adsenseid'].'_'.$advertisements_data['id'];//该条广告展示量的key

				$begin_end_time = explode(",",$advertisements_data['time']);
				//dump($begin_end_time[0]);
				
				if(date('Y-m-d H:i:s') < $begin_end_time[0] || date('Y-m-d H:i:s') > $begin_end_time[1]){
					continue;
				}
				$redis_value[$key] = $advertisements_data['adsenseid'].','.$advertisements_data['id'].','.$advertisements_data['materialid'].','.$advertisements_data['spending'].','.($advertisements_data['price']/1000).','.$begin_end_time[0].','.$begin_end_time[1].','.$advertisements_data['orientation'].','.$advertisements_data['numlimit'].','.'0'.','.$advertisements_data['userid'].','.$advertisements_data['status'].','.$advertisements_data['running_status'];//0 广告位id 1 广告id 2广告素材id 3 模式 4每一次展示花费（分）5开始时间 6结束时间 7全站投放？ 8次数限制 9已展现次数 10用户id 11状态
				
				$redis->set($redis_key[$key],$redis_value[$key]);//设置缓存key value	

	
				if($advertisements_data['status'] == 2){
					$redis->set($redis_key_view[$key],'0');//设置缓存key value
				}
				

			}
			dump($redis_key);exit;
			
		}

		
	}


	/**
     * 定时任务------生成(对应广告位) 广告模型 在用(移到create里去了)
     * 
     */
	public function update_advertisements_empty(){
		echo 111;exit;
		$redis = new \redis();  
		$redis->connect('172.30.2.132', 6379);
		$map['status'] = array('in','2,3');
		$advertisements_data = Db::table('advertisement')->where($map)->select()->toarray();
		//dump($advertisements_data);exit;
		$i = 0;
		if(!empty($advertisements_data)){

			foreach ($advertisements_data as $key => $value) {

				$redis_key[$i] = 'advertisement_'.$value['adsenseid'].'_'.$value['id'];//该条广告的key

				$redis_key_view[$i] = 'view_advertisement_'.$value['adsenseid'].'_'.$value['id'];//该条广告展示量的key

				$begin_end_time = explode(",",$value['time']);
				//dump($begin_end_time[0]);
				
				if(date('Y-m-d H:i:s') < $begin_end_time[0] || date('Y-m-d H:i:s') > $begin_end_time[1]){
					continue;
				}
				$redis_value[$i] = $value['adsenseid'].','.$value['id'].','.$value['materialid'].','.$value['spending'].','.($value['price']/1000).','.$begin_end_time[0].','.$begin_end_time[1].','.$value['orientation'].','.$value['numlimit'].','.'0'.','.$value['userid'].','.$value['status'].','.$value['running_status'];//0 广告位id 1 广告id 2广告素材id 3 模式 4每一次展示花费（分）5开始时间 6结束时间 7全站投放？ 8次数限制 9已展现次数 10用户id 11状态
				$a[$i] = $redis->get($redis_key[$i]);

				if($value['status'] == 2){
					$redis->set($redis_key_view[$i],'0');//设置缓存key value
				}

				if($value['status'] == 3 && $a[$i] ===false){
					$redis->set($redis_key_view[$i],'0');//设置缓存key value
				}

				$redis->set($redis_key[$i],$redis_value[$i]);//设置缓存key value	

	
				
				$i++;

			}
			
			
		}

		
	}


    /**
     * [create_materials description]  暂时没用
     * 生成广告素材
     * @return [type] [null]
     */
    public function create_materials(){

    	$redis = new \redis();  
		$redis->connect('172.30.2.132', 6379);
		//echo $redis->get('material_*');exit;//获取缓存
		$materials = Db::table('material_main')->where(array('status'=>3))->select()->toarray();

		foreach ($materials as $key => $value) {

			$redis_key = 'material_'.$value['id'];

			switch($value['material_type']){
			  case 1:
			  //文字缓存
			  
			  	$material_detail = Db::table('material_text')->where(array('sid'=>$value['material_id']))->find();
			    $redis_value = '1,'.$value['width'].','.$value['height'].','.$value['userid'].','.$value['status'].','.$material_detail['material_content'].','.$material_detail['font_size'].','.$material_detail['font_color'].','.$material_detail['font_decoration'].','.$material_detail['font_weight'].','.$material_detail['font_style'].','.$material_detail['hover_font_color'].','.$material_detail['hover_font_decoration'].','.$material_detail['hover_font_weight'].','.$material_detail['hover_font_style'].','.$material_detail['click_url'].','.$material_detail['horizon_position'].','.$material_detail['margin'].','.$material_detail['open_target'];
			    break; // 跳出循环
			  case 2:
			  //图片缓存
			    $material_detail = Db::table('material_image')->where(array('sid'=>$value['material_id']))->find();
			    $redis_value = '2,'.$value['width'].','.$value['height'].','.$value['userid'].','.$value['status'].','.$material_detail['image_url'].','.$material_detail['click_url'].','.$material_detail['image_description'].','.$material_detail['open_target'].','.$material_detail['adaptation'];
			    break;// 跳出循环
			  /*case 3:
			  //flash缓存
			    $material_detail = Db::table('material_image')->where(array('sid'=>$value['material_id']))->find();
			    break;// 跳出循环
			  case 4:
			  //对联缓存
			    echo '对联';
			    break;// 跳出循环*/
			}

			$redis->setex($redis_key,1000,$redis_value);//设置缓存key value  存在时间1000秒
			echo "广告素材生成成功key:".$redis_key."-----value:".$redis_value."<br/>";
			
		}
		
		//echo $redis->get('material_1');//获取缓存
		//echo $redis->get('material_2');//获取缓存

    }


    /**
     * 定时任务------生成广告位模型  在用(移到create里去了)
     */
    
    public function create_adsense(){
    	echo 111;exit;
    	$redis = new \redis();  
		$redis->connect('172.30.2.132', 6379);
		$map['status'] = 1;
		$adsense_data = Db::table('adsense')->where($map)->select()->toarray();
		
		foreach ($adsense_data as $key => $value) {
			$adsense_key = 'adsense_'.$value['id'];
			$adsense_value = $value['width'].','.$value['height'].','.$value['status'].','.$value['sensemodel'].','.$value['sensetype'];
			$redis->setex($adsense_key,300,$adsense_value);//设置缓存key value  存在时间1000秒
		}
    }



}
