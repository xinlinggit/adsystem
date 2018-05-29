<?php
namespace api\index\controller;
use think\Db;
use think\Cache;
use think\Controller;
class Index extends Controller
{


    public function index()
    {	echo 'welcome to use adsystem api!!!!';
    	//error_log('111111'.PHP_EOL,3,'/home/projects/adsystem/error_'.date('Ymd').'.log');exit;

    }

    /*
    * js接口api
     **/
    public function api(){
    	header("Access-Control-Allow-Origin: *");//同源策略 跨域请求 头设置
    	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		header('Access-Control-Allow-Methods: GET, POST, PUT');
        header('Content-type: application/json');
        $redis = new \redis();  
		$redis->connect('172.30.2.132', 6379);

        $adsystem_id = $_REQUEST['adsystem_id'];//接受广告位id

        //判断该广告位是否是开启的

        $adsense_value = explode(',',$redis->get('adsense_'.$adsystem_id));

       
        if(empty($adsense_value[0]) || $adsense_value[2] != 1){

        	$return4 = $this->create_adsense($adsystem_id);

        	if($return4){

        		$adsense_value = explode(',',$redis->get('adsense_'.$adsystem_id));

        	}else{

        		echo json_encode("广告位不存在或者广告位被禁止了");exit;

        	}
        	
        }
        

		$key_data = 'advertisement_'.$adsystem_id.'_*';
		$keys_arr = $redis->keys($key_data);//查找获取该广告位所有的广告的 key
		
		//如果该广告位没有广告,就返回空
		if(empty($keys_arr)){

			$return1 = $this->create_advertisements($adsystem_id);

			if($return1){

				$keys_arr = $redis->keys($key_data);//查找获取该广告位所有的广告的 key

			}else{

				echo json_encode(array('code'=>103,'msg'=>"广告位不存在或者被禁用或者该广告位没有广告"));exit;
			}
			
		}


		$i = 0;

		foreach ($keys_arr as $key => $value) {

			$value_arr[$key] = explode(',',$redis->get($value));//0 广告位id 1 广告id 2广告素材id 3 模式 4每一次展示花费（分）5开始时间 6结束时间 7全站投放？ 8次数限制 9已展现次数	10用户userid 11状态 12启用（停止）
			if($value_arr[$key][12] == 2){
				continue;
			}
			//判断时间是否合适 start

			if(date('Y-m-d H:i:s') > $value_arr[$key][6] || $value_arr[$key][11] == 4){	
				$value_arr[$key][11] = 4;
				$redis->set($value,implode(',',$value_arr[$key]));
				$this->update_advertisement($value_arr[$key][1],4);
				continue;
			}else{
				$value_arr[$key][11] = 3;
				$redis->set($value,implode(',',$value_arr[$key]));
				$this->update_advertisement($value_arr[$key][1],3);
			}
			//判断时间是否合适 end
			
			//判断用户钱够不够 start
			$money[$key] = $redis->get('userinfo_'.$value_arr[$key][10]);
			
			if($money[$key] === false){
				
				$return2 = $this->create_userinfo($value_arr[$key][10]);

				if($return2){
					$money[$key] = $redis->get('userinfo_'.$value_arr[$key][10]);				
				}else{
					$value_arr[$key][11] = 4;
					$redis->set($value,implode(',',$value_arr[$key]));
					$this->update_advertisement($value_arr[$key][1],4);
					continue;
					//echo json_encode(array('code'=>103,'msg'=>"用户不存在或者被禁用"));exit;
				}

				
			}
			//$redis->watch('userinfo_'.$value_arr[$key][10]);
			if($money[$key] < $value_arr[$key][4] || $money[$key] <= 0 || !$money[$key]){

				$value_arr[$key][11] = 4;
				$redis->set($value,implode(',',$value_arr[$key]));
				$this->update_advertisement($value_arr[$key][1],4);
				continue;
			}
			//判断用户钱够不够 end
			
			//判断展示次数限制 start
			$view[$key] = $redis->get('view_advertisement_'.$adsystem_id.'_'.$value_arr[$key][1]);
			if($view[$key] >= $value_arr[$key][8]){

				$value_arr[$key][11] = 4;
				$redis->set($value,implode(',',$value_arr[$key]));
				$this->update_advertisement($value_arr[$key][1],4);
				continue;
			}
			//判断展示次数限制 end
			
			$material_data[$key] = $redis->get('material_'.$value_arr[$key][2]);
			
			if($material_data[$key] === false){
				$return3 = $this->create_materials($value_arr[$key][2]);

				if($return3){
					$material_data[$key] = $redis->get('material_'.$value_arr[$key][2]);
				}else{

					$value_arr[$key][11] = 4;
					$redis->set($value,implode(',',$value_arr[$key]));
					$this->update_advertisement($value_arr[$key][1],4);				
					continue;//如果素材不存在就跳出 继续下一个循环
				}
				
			}

			$material_arr[$key] = explode(',',$material_data[$key]);
			
			switch ($material_arr[$key][0]){
				case 1:
				  	$info = array('adsystem_id'=>'adsystem_'.$adsystem_id,'material_content'=>$material_arr[$key][5],'font_size'=>$material_arr[$key][6],'font_color'=>$material_arr[$key][7],'font_decoration'=>$material_arr[$key][8],'font_weight'=>$material_arr[$key][9],'font_style'=>$material_arr[$key][10],'hover_font_color'=>$material_arr[$key][11],'hover_font_decoration'=>$material_arr[$key][12],'hover_font_weight'=>$material_arr[$key][13],'hover_font_style'=>$material_arr[$key][14],'click_url'=>$material_arr[$key][15],'horizon_position'=>$material_arr[$key][16],'margin'=>$material_arr[$key][17],'open_target'=>$material_arr[$key][18]);
					$this->assign('info',$info);
					$html[$i] = $this->fetch('ad_txt');
					//$html[$i] = "aaaaaa";
				  break;  
				case 2:
				  	$info = array('adsystem_id'=>'adsystem_'.$adsystem_id,'image_url'=>'http://ad.api.dev.cnfol.wh/'.$material_arr[$key][5],'click_url'=>$material_arr[$key][6],'open_target'=>$material_arr[$key][8],'image_description'=>$material_arr[$key][7]);
					$this->assign('info',$info);
					$html[$i] = $this->fetch('ad_pic');
					//$html[$i] = "bbbbb";
				  break;
				default:
				  $html[$i] = "";
				  break;
			}

			if(empty($html[$i])){
				unset($html[$i]);
				continue;
			}
			$bbb = array(array($value_arr[$key][1],$value_arr[$key][2],$value_arr[$key][4],$value_arr[$key][10]),$html[$i]);//array 0 广告id 1广告素材id  2每次花费 3 用户id
			
			$old_html[$i] = array('title'=>$bbb,'weight'=>$value_arr[$key][3]);
			
			$i++;
		}


		
		if(empty($old_html)){

			echo json_encode("没有广告");exit;
		}

		$new_html = $this->roll($old_html);//随机权重函数

		$ad = array(
				'html'=>$new_html[1],
				'width'=>$adsense_value[0],
				'height'=>$adsense_value[1],
			);

		//给当前展示的广告展示次数加1 start
		$redis->incr('view_advertisement_'.$adsystem_id.'_'.$new_html[0][0]);
		//给当前展示的广告展示次数加1 end
		
		//扣钱 扣缓存  start
		//
		/*$redis->get('userinfo_'.$new_html[0][3]);
		$redis->watch('userinfo_'.$new_html[0][3]);
		$redis->multi();*/
		$redis->decrBy('userinfo_'.$new_html[0][3],$new_html[0][2]); //减该用户的缓存冻结金额
		/*$incr = $redis->exec();
		if(!$incr){
			$redis->decr('view_advertisement_'.$adsystem_id.'_'.$new_html[0][0]);
			echo json_encode($ad);exit;
		}*/

		//扣钱 扣缓存  end
		//增加基础访问数据
		$this->count($adsystem_id,$new_html[0][0],$new_html[0][1],$new_html[0][3],$new_html[0][2]);//1 广告位id  2广告id 3  素材id 4 用户id 5每次展示花费
		echo json_encode($ad);exit;

    }


    /**
	 * 按比例分配随机数发生器 - 生产环境使用
	 * !!!请确保数组的所有权重是正整数
	 * @param array $arr
	 */
	public function roll($arr = [])
	{
		$high = 0;
		foreach ($arr as $key => $val)
		{
			$new_weight = $val['weight'] * 100;
			$arr[$key]['weight'] = $new_weight;
			$high += $new_weight;
		}
		$dice = mt_rand(1, $high);
		$sum = 0;
		foreach($arr as $kk => $vv)
		{
			$sum_low = $sum;
			$sum += $vv['weight'];
			$sum_high = $sum;

			// 从0开始的区间段，前开后闭
			if($sum_low < $dice && $dice <= $sum_high)
			{
				return $vv['title'];
			}
		}
	}




    public function count($adsystemid,$advertisementid,$materialid,$userid,$cost){
    	$ip = $this->request->ip();
    	$userAgent = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'';
    	$userAgent_base64 = base64_encode($userAgent);
    	$ips = array();
    	//$ips = $this->GetIpLookup($ip);
    	$redis = new \redis();  
		$redis->connect('172.30.2.132', 6379);
		//$redis->flushDB();exit;
		//$redis_key = 'ad_'.$adsystemid;
		$redis_key = 'ad';
		//$redis_key = 'ad_'.time();
		$redis_value = '|'.$adsystemid.','.$advertisementid.','.$materialid.','.$userid.','.$ip.','.''.','.''.','.''.','.$_SERVER['SERVER_NAME'].','.$userAgent_base64.','.date("Y-m-d H:i:s").','.$cost;

		if(empty($redis->get($redis_key))){
			$redis->set($redis_key,$redis_value);//设置缓存key value
		}else{
			$redis->append($redis_key,$redis_value);
		}
		//echo $redis->get($redis_key);//获取缓存

    }


 
      
    public function GetIpLookup($ip = ''){ 

        if(empty($ip)){  
            $ip = $this->GetIp();  
        }  
        $res = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' . $ip); 
        if(empty($res)){ return false; }  
        $jsonMatches = array();  
        preg_match('#\{.+?\}#', $res, $jsonMatches);  
        if(!isset($jsonMatches[0])){ return false; }  
        $json = json_decode($jsonMatches[0], true);  
        if(isset($json['ret']) && $json['ret'] == 1){  
            $json['ip'] = $ip;  
            unset($json['ret']);  
        }else{  
            return false;  
        }  
        return $json; 

    }

    /**
     * 生成用户金额缓存
     */

     public function create_userinfo($userid){
     	$redis = new \redis();  
		$redis->connect('172.30.2.132', 6379);
		$user_info = Db::table('userinfo')->where(array('uid'=>$userid))->find();

			$redis_key = "userinfo_".$user_info['uid'];
			$redis_value = $user_info['account'];
			$back = $redis->set($redis_key,$redis_value);//设置缓存key value
			if($back){
				return true;
			}else{
				return false;
			}

		
     }


    /**
     * 生成(对应广告位) 广告模型
     * 
     */
	public function create_advertisements($adsystem_id){
		$redis = new \redis();  
		$redis->connect('172.30.2.132', 6379);
		$map['status'] = 2;
		$map['running_status'] = 1;
		$map['adsenseid'] = $adsystem_id;
		$advertisements = Db::table('advertisement')->where($map)->select()->toarray();
		//dump($advertisements);exit;
		if(empty($advertisements)){
			return false;
		}
		
		foreach ($advertisements as $key => $value) {
			
			$redis_key[$key] = 'advertisement_'.$value['adsenseid'].'_'.$value['id'];//该条广告的key

			$redis_key_view[$key] = 'view_advertisement_'.$value['adsenseid'].'_'.$value['id'];//该条广告展示量的key

			$begin_end_time = explode(",",$value['time']);
			//dump($begin_end_time[0]);
			
			if(date('Y-m-d H:i:s') < $begin_end_time[0] || date('Y-m-d H:i:s') > $begin_end_time[1]){
				continue;
			}
			$redis_value[$key] = $value['adsenseid'].','.$value['id'].','.$value['materialid'].','.$value['spending'].','.($value['price']/1000).','.$begin_end_time[0].','.$begin_end_time[1].','.$value['orientation'].','.$value['numlimit'].','.'0'.','.$value['userid'].','.$value['status'].','.$value['running_status'];//0 广告位id 1 广告id 2广告素材id 3 模式 4每一次展示花费（分）5开始时间 6结束时间 7全站投放？ 8次数限制 9已展现次数 10用户id 11状态

			$redis->set($redis_key[$key],$redis_value[$key]);//设置缓存key value

			$redis->set($redis_key_view[$key],'0');//设置缓存key value

					
		}
		//echo 11;exit;
		return true;
		
	}


    /**
     * [create_materials description]
     * 生成广告素材
     * @return [type] [null]
     */
    public function create_materials($materialid){

    	$redis = new \redis();  
		$redis->connect('172.30.2.132', 6379);
		//echo $redis->get('material_*');exit;//获取缓存
		$materials = Db::table('material_main')->where(array('status'=>3,'id'=>$materialid))->find();
		if(empty($materials)){
			return false;
		}
		$redis_key = 'material_'.$materials['id'];

			switch($materials['material_type']){
			  case 1:
			  //文字缓存
			  
			  	$material_detail = Db::table('material_text')->where(array('sid'=>$materials['material_id']))->find();
			    $redis_value = '1,'.$materials['width'].','.$materials['height'].','.$materials['userid'].','.$materials['status'].','.$material_detail['material_content'].','.$material_detail['font_size'].','.$material_detail['font_color'].','.$material_detail['font_decoration'].','.$material_detail['font_weight'].','.$material_detail['font_style'].','.$material_detail['hover_font_color'].','.$material_detail['hover_font_decoration'].','.$material_detail['hover_font_weight'].','.$material_detail['hover_font_style'].','.$material_detail['click_url'].','.$material_detail['horizon_position'].','.$material_detail['margin'].','.$material_detail['open_target'];
			    break; // 跳出循环
			  case 2:
			  //图片缓存
			    $material_detail = Db::table('material_image')->where(array('sid'=>$materials['material_id']))->find();
			    $redis_value = '2,'.$materials['width'].','.$materials['height'].','.$materials['userid'].','.$materials['status'].','.$material_detail['image_url'].','.$material_detail['click_url'].','.$material_detail['image_description'].','.$material_detail['open_target'].','.$material_detail['adaptation'];
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

		$back = $redis->set($redis_key,$redis_value);//设置缓存key value

		if($back){
			return true;
		}else{
			return false;
		}
			

		
		
		//echo $redis->get('material_1');//获取缓存
		//echo $redis->get('material_2');//获取缓存

    }


    /**
     * 生成广告位模型缓存
     */
    
    public function create_adsense($adsystem_id){

    	$redis = new \redis();  
		$redis->connect('172.30.2.132', 6379);
		$map['id'] = $adsystem_id;
		$map['status'] = 1;

		$adsense_data = Db::table('adsense')->where($map)->find();

		if(!$adsense_data){
			return false;
		}

		$adsense_key = 'adsense_'.$adsense_data['id'];
		$adsense_value = $adsense_data['width'].','.$adsense_data['height'].','.$adsense_data['status'];
		$back = $redis->set($adsense_key,$adsense_value);//设置缓存key value
		if($back){
			return true;	
		}else{
			return false;
		}


    }



    public function update_advertisement($advertisementid,$status){
    	$back = Db::table('advertisement')->where(array('id'=>$advertisementid))->setField('status',$status);

    }





    public function preview(){
        
        $material_id = $_REQUEST['material_id'];
        $this->assign('material_id',$material_id);
        return $this->fetch();

    }

    public function api_preview(){
        header("Access-Control-Allow-Origin: *");//同源策略 跨域请求 头设置
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header('Access-Control-Allow-Methods: GET, POST, PUT');
        header('Content-type: application/json');
        $material_id = $_REQUEST['material_id'];
        $material_data = Db::table('material_main')->where(array('id'=>$material_id))->find();

        switch($material_data['material_type']){
              case 1:
              //文字素材
                $info =  Db::table('material_text')->where(array('sid'=>$material_data['material_id']))->find();
                $info['adsystem_id'] = "material_id_".$material_data['material_id'];           
                $this->assign('info',$info);
                $html = $this->fetch('ad_txt');
                $material = array(
                    'html'=>$html,
                    'width'=>$material_data['width'],
                    'height'=>$material_data['height'],
            	);
                echo json_encode($material);
                break; // 跳出循环
              case 2:
              //图片素材
                //echo '图片';
                $info =  Db::table('material_image')->where(array('sid'=>$material_data['material_id']))->find();
                $info['adsystem_id'] = "material_id_".$material_data['material_id'];
                $info['image_url'] = 'http://ad.api.dev.cnfol.wh/'.$info['image_url'];
                $this->assign('info',$info);
                $html = $this->fetch('ad_pic');
                $material = array(
                    'html'=>$html,
                    'width'=>$material_data['width'],
                    'height'=>$material_data['height'],
            	);
                echo json_encode($material);
                break;// 跳出循环
              case 3:
              //flash素材
                echo 'flash';
                break;// 跳出循环
              case 4:
              //对联素材
                echo '对联';
                break;// 跳出循环*/
            }

    }

}
