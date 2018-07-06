<?php
namespace api\index\controller;
use think\Db;
use think\Cache;
use think\Controller;
class Index extends Controller
{


    public function index()
    {	echo 'welcome to use adsystem api!!!!';
    	error_log('111111'.PHP_EOL,3,'/home/httpd/adsystem/runtime/error_'.date('Ymd').'.log');exit;

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
		//$redis->connect('172.30.2.132', 6379);
		$redis->connect(config('redis.host'), config('redis.port'));
        $adsystem_id = input('as_id');//接受广告位id

        //判断该广告位是否是开启的

        $adsense_value = explode(',',$redis->get('adsense_'.$adsystem_id));
        

        if(empty($adsense_value[0]) || $adsense_value[2] != 1){

        	$return4 = $this->create_adsense($adsystem_id);
        	//$adsense_data['width'].','.$adsense_data['height'].','.$adsense_data['status'].','.$adsense_data['sensemodel'].','.$adsense_data['sensetype'].','.$adsense_data['freetype'].','.$adsense_data['materialmodel'].','.$adsense_data['imgurl'].','.$adsense_data['text'];

        	if($return4){

        		$adsense_value = explode(',',$redis->get('adsense_'.$adsystem_id));

        	}else{

        		echo json_encode(array('code'=>104,'msg'=>"广告位不存在或者广告位被禁止了"));exit;

        	}
        	
        }
        
        /*备份
		$key_data = 'advertisement_'.$adsystem_id.'_*';

		$keys_arr = $redis->keys($key_data);//查找获取该广告位所有的包时段广告的 key

		//如果该广告位没有包时段广告,就返回空，然后去触发接口寻找包时段广告
		if(empty($keys_arr)){

			$return1 = $this->create_advertisements($adsystem_id,1,$adsense_value[9]);

			$keys_arr = $redis->keys($key_data);//查找获取该广告位所有的包时段广告的 key

			if(empty($keys_arr) && $adsense_value[3] == 2){
				//如果没有包时段的广告位，再去找竞价的广告位(前提是广告位必须是开放竞价的)
				$key_data = 'advertisement_0'.'_*';

				$keys_arr = $redis->keys($key_data);//查找获取该广告位所有的广告的 key

				//如果该广告位没有竞价广告,就返回空，然后去触发接口寻找竞价广告
				if(empty($keys_arr)){

					$return2 = $this->create_advertisements($adsystem_id,2,$adsense_value[9]);

					if($return2){

						$keys_arr = $redis->keys($key_data);//查找获取该广告位所有的广告的 key

					}else if($adsense_value[5] != 2){

						echo json_encode(array('code'=>104,'msg'=>"该广告位没有广告"));exit;//没素材且freetype为3 就直接隐藏该广告位
					}
					
				}


			}else if($adsense_value[5] != 2){

				echo json_encode(array('code'=>104,'msg'=>"该广告位没有广告"));exit;//没素材且freetype为3 就直接隐藏该广告位
			}
			
		}*/
		$key_data = 'advertisement_'.$adsystem_id.'_*';

		$keys_arr = $redis->keys($key_data);//查找获取该广告位所有的包时段广告的 key

		if(empty($keys_arr) && $adsense_value[3] == 2){
			//如果没有包时段的广告位，再去找竞价的广告位(前提是广告位必须是开放竞价的)
			$key_data = 'advertisement_0'.'_*';

			$keys_arr = $redis->keys($key_data);//查找获取该广告位所有的广告的 key

			//如果该广告位没有竞价广告
			if(empty($keys_arr) && $adsense_value[5] != 2){

				echo json_encode(array('code'=>104,'msg'=>"该广告位没有广告"));exit;//没素材且freetype为3 就直接隐藏该广告位
						
			}else{

				$old_html = array();

			}

		}elseif(empty($keys_arr) && $adsense_value[3] != 2){

			echo json_encode(array('code'=>104,'msg'=>"该广告位没有广告"));exit;//没素材且freetype为3 就直接隐藏该广告位

		}else{

			$j = 0;
			foreach ($keys_arr as $key => $value) {
				$all_keys[$key] = explode(',',$redis->get($value));
				$isset_run[$key] = $all_keys[$key][12];
				if($isset_run[$key] == 1){
					$j++;
				}
			}
			if($j == 0 && $adsense_value[3] == 2){
				//如果包时段的广告位都暂停了，再去找竞价的广告位(前提是广告位必须是开放竞价的)
				$key_data = 'advertisement_0'.'_*';

				$keys_arr = $redis->keys($key_data);//查找获取该广告位所有的广告的 key

				//如果该广告位没有竞价广告
				if(empty($keys_arr) && $adsense_value[5] != 2){

					echo json_encode(array('code'=>104,'msg'=>"该广告位没有广告"));exit;//没素材且freetype为3 就直接隐藏该广告位
							
				}else{

					$old_html = array();

				}
			}else{
				$old_html = array();
			}
		}



		$i = 0;

		foreach ($keys_arr as $key => $value) {

			$value_arr[$key] = explode(',',$redis->get($value));//0 广告位id 1 广告id 2广告素材id 3 模式 4每一次展示花费（分）5开始时间 6结束时间 7全站投放？ 8次数限制 9已展现次数	10用户userid 11状态 12启用（停止）13adsiteid 14 15project_type

			//判断广告平台对不对start
			if($adsense_value[9] != $value_arr[$key][13]){
				continue;
			}
			//判断广告平台对不对end
			//判断广告的开启和暂停 start
			
			if($value_arr[$key][12] == 2){
				continue;
			}
			//判断广告的开启和暂停 end
			
			//判断时间是否合适 start
			if(date('Y-m-d H:i:s') > $value_arr[$key][6] || $value_arr[$key][11] == 4){	
				$value_arr[$key][11] = 4;
				$redis->set($value,implode(',',$value_arr[$key]));
				$this->update_advertisement($value_arr[$key][1],4);
				continue;
			}

			
			//判断时间是否合适 end
			
			//让广告开启
			if($value_arr[$key][11] != 3){
				$value_arr[$key][11] = 3;
				$redis->set($value,implode(',',$value_arr[$key]));
				$this->update_advertisement($value_arr[$key][1],3);
			}
			//让广告开启end
			
			//判断广告位类型和广告类型是否一致start
			/*if(($adsense_value[6] != $value_arr[$key][15] && $adsense_value[6] != 1) || ($adsense_value[6] ==1 && $value_arr[$key][15]==2)){
				continue;
			}*/
			if($adsense_value[6] != $value_arr[$key][15]){
				continue;
			}
			//判断广告位类型和广告类型是否一致end
			
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

			//$redis->watch('userinfo_'.$value_arr[$key][10]);//别删
			if(($money[$key] < $value_arr[$key][4] || $money[$key] <= 0 || !$money[$key]) && $value_arr[$key][3] != 1){
				$value_arr[$key][11] = 4;
				$redis->set($value,implode(',',$value_arr[$key]));
				$this->update_advertisement($value_arr[$key][1],4);
				continue;
			}


			//判断用户钱够不够 end
			
			//判断展示次数限制 start
			if($value_arr[$key][3] != 1) {
				
		       	$view[$key] = $redis->get('view_advertisement_0_'.$value_arr[$key][1]);

		    }else{
		    	
		    	$view[$key] = $redis->get('view_advertisement_'.$adsystem_id.'_'.$value_arr[$key][1]);

		    }
		    
			if($view[$key] >= $value_arr[$key][8] && $value_arr[$key][3] != 1){
				$value_arr[$key][11] = 4;
				$redis->set($value,implode(',',$value_arr[$key]));
				$this->update_advertisement($value_arr[$key][1],4);
				continue;
			}

			//判断展示次数限制 end

			if(strpos($value_arr[$key][2],'|')===false){
				
			 	$material_data[$key] = $redis->get('material_'.$value_arr[$key][2]);
				
				if($material_data[$key] === false){

					$return3 = $this->create_materials($value_arr[$key][2]);

					if($return3){
						
						$material_data[$key] = $redis->get('material_'.$value_arr[$key][2]);

					}else{

						$value_arr[$key][11] = 2;
						$redis->set($value,implode(',',$value_arr[$key]));
						$this->update_advertisement($value_arr[$key][1],2);		
						continue;//如果素材不存在就跳出 继续下一个循环
					}
					
				}

				$material_data_ex[$key] = explode(',',$material_data[$key]);


				if(($material_data_ex[$key][1] != $adsense_value[0] || $material_data_ex[$key][2] != $adsense_value[1]) && $adsense_value[6] != 1){

					continue;
				}

			}else{
				
			    $zzz = explode('|',$value_arr[$key][2]);
			    
			    foreach ($zzz as $k => $v) {
			    	$material_data[$k] = $redis->get('material_'.$v);
			
					if($material_data[$k] === false){
						
						$return3 = $this->create_materials($v);

						if($return3){
							
							$material_data[$k] = $redis->get('material_'.$v);

						}else{

							$value_arr[$key][11] = 2;
							$redis->set($value,implode(',',$value_arr[$key]));
							$this->update_advertisement($value_arr[$key][1],2);		
							continue;//如果素材不存在就跳出 继续下一个循环
						}
						
					}
					
					$material_datas[$k] = explode(',',$material_data[$k]);
					
					if(($material_datas[$k][1] != $adsense_value[0] || $material_datas[$k][2] != $adsense_value[1]) && $adsense_value[6] != 1){
						
						unset($zzz[$k]);
					}
			    }
			    shuffle($zzz);
			    if(empty($zzz)){
			    	continue;
			    }
			    $material_data[$key] = $redis->get('material_'.$zzz[0]);
			    $value_arr[$key][2] = $zzz[0];
			   
			}


			$material_arr[$key] = explode(',',$material_data[$key]);

			
			switch ($material_arr[$key][0]){
				case 1:
				  	$info = array('adsystem_id'=>'as_'.$adsystem_id,'image_description'=>$material_arr[$key][5],'font_size'=>14,'font_color'=>"#000000",'font_decoration'=>"none",'font_weight'=>"normal",'font_style'=>"normal",'hover_font_color'=>"#000000",'hover_font_decoration'=>"none",'hover_font_weight'=>"normal",'hover_font_style'=>"normal",'click_url'=>config('app_domain')."/operation/index/asClick?adsId=".$adsystem_id."&advId=".$value_arr[$key][1]."&userId=".$value_arr[$key][10]."&mId=".$value_arr[$key][2]."&spending=".$value_arr[$key][3]."&url=".$material_arr[$key][15],'horizon_position'=>"left",'margin'=>"0",'open_target'=>"_blank",'sensetype'=>$adsense_value[4]);
					$this->assign('info',$info);
					$html[$i] = $this->fetch('ad_txt');
					//$html[$i] = "aaaaaa";
				  break;  
				case 2:
				  	$info = array('adsystem_id'=>'as_'.$adsystem_id,'image_url'=>$material_arr[$key][5],'click_url'=>config('app_domain')."/operation/index/asClick?adsId=".$adsystem_id."&advId=".$value_arr[$key][1]."&userId=".$value_arr[$key][10]."&mId=".$value_arr[$key][2]."&spending=".$value_arr[$key][3]."&url=".$material_arr[$key][6],'open_target'=>$material_arr[$key][8],'image_description'=>$material_arr[$key][7],'sensetype'=>$adsense_value[4]);
					$this->assign('info',$info);
					$html[$i] = $this->fetch('ad_pic');
					//$html[$i] = "bbbbb";
				  break;
				case 3:
				  	$info = array('adsystem_id'=>'as_'.$adsystem_id,'image_url'=>$material_arr[$key][5],'width'=>$adsense_value[0],'height'=>$adsense_value[1],'sensetype'=>$adsense_value[4]);
					$this->assign('info',$info);
					$html[$i] = $this->fetch('ad_flash');
					//$html[$i] = "ccccc";
				  break;
				case 4:
				  	$info = array('adsystem_id'=>'as_'.$adsystem_id,'image_url'=>$material_arr[$key][5],'click_url'=>config('app_domain')."/operation/index/asClick?adsId=".$adsystem_id."&advId=".$value_arr[$key][1]."&userId=".$value_arr[$key][10]."&mId=".$value_arr[$key][2]."&spending=".$value_arr[$key][3]."&url=".$material_arr[$key][6],'open_target'=>$material_arr[$key][8],'image_description'=>$material_arr[$key][7],'sensetype'=>$adsense_value[4]);
					$this->assign('info',$info);
					$html[$i] = $this->fetch('ad_pic');
					//$html[$i] = "ddddd";
				  break;
				default:
				  $html[$i] = "";
				  break;
			}

			if(empty($html[$i])){
				unset($html[$i]);
				continue;
			}
			$bbb = array(array($value_arr[$key][1],$value_arr[$key][2],$value_arr[$key][4],$value_arr[$key][10],$value_arr[$key][3]),$html[$i]);//array 0 广告id 1广告素材id  2每次花费 3 用户id 4模式(1包时段 2CPM 3CPC)
			
			$old_html[$i] = array('title'=>$bbb,'weight'=>$value_arr[$key][4]);
			
			$i++;
		}



		if(empty($old_html) && $adsense_value[5] == 2){

			switch($adsense_value[6]){
				case 1:

				$info = array('adsystem_id'=>'as_'.$adsystem_id,'image_description'=>$adsense_value[8],'font_size'=>14,'font_color'=>"#000000",'font_decoration'=>"none",'font_weight'=>"normal",'font_style'=>"normal",'hover_font_color'=>"#000000",'hover_font_decoration'=>"none",'hover_font_weight'=>"normal",'hover_font_style'=>"normal",'click_url'=>config('app_domain')."/operation/index/asClick?adsId=".$adsystem_id."&advId=0&userId=0&mId=0&spending=0&url=".$adsense_value[10],'horizon_position'=>"left",'margin'=>"0",'open_target'=>"_blank",'sensetype'=>$adsense_value[4]);
					$this->assign('info',$info);
					$html = $this->fetch('ad_txt');
				  break;  
				case 2:

				$info = array('adsystem_id'=>'as_'.$adsystem_id,'image_url'=>$adsense_value[7],'click_url'=>config('app_domain')."/operation/index/asClick?adsId=".$adsystem_id."&advId=0&userId=0&mId=0&spending=0&url=".$adsense_value[10],'open_target'=>"_blank",'image_description'=>"",'sensetype'=>$adsense_value[4]);
					$this->assign('info',$info);
					$html = $this->fetch('ad_pic');
				  break;
				case 3:

				$info = array('adsystem_id'=>'as_'.$adsystem_id,'image_url'=>$adsense_value[7],'click_url'=>config('app_domain')."/operation/index/asClick?adsId=".$adsystem_id."&advId=0&userId=0&mId=0&spending=0&url=".$adsense_value[10],'open_target'=>"_blank",'image_description'=>$adsense_value[8],'sensetype'=>$adsense_value[4]);
					$this->assign('info',$info);
					$html = $this->fetch('ad_pic');
				  break;
				default:

					echo json_encode(array('code'=>104,'msg'=>"该广告位没有广告"));exit;//没素材且freetype为3 就直接隐藏该广告位
					break;
				}
			$old_html = array('0'=>array('title'=>array(array(0,0,0,0,0),$html),'weight'=>1));

		}else if(empty($old_html) && $adsense_value[5] != 2){
			echo json_encode(array('code'=>104,'msg'=>"没有广告"));exit;
		}

		switch ($adsense_value[3]){
				case 1:
					$new_html = $this->my_max($old_html);
					$ad = array(
						    'code'=>100,
							'html'=>$new_html[1],
							'width'=>$adsense_value[0],
							'height'=>$adsense_value[1],
						);
					$this->count($adsystem_id,$new_html[0][0],$new_html[0][1],$new_html[0][3],0,0);//1 广告位id  2广告id 3  素材id 4 用户id 5每次展示花费 6剩余余额
				  break;  
				case 2:
					$new_html = $this->roll($old_html);//随机权重函数

					$ad = array(
						    'code'=>100,
							'html'=>$new_html[1],
							'width'=>$adsense_value[0],
							'height'=>$adsense_value[1],
						);

					//前提 sensemodel 为2 (1关闭竞价 2开启竞价)
					//1.广告展示的是打底广告 ($new_html[0][4] == 0 && $adsense_value[5] == 2)
					//2.广告展示的是包时段广告 ($new_html[0][4] == 1)
					//3.广告展示的是cpc广告($new_html[0][4] == 3)
					if(($new_html[0][4] == 1) || ($new_html[0][4] == 0 && $adsense_value[5] == 2) || $new_html[0][4] == 3){
						$this->count($adsystem_id,$new_html[0][0],$new_html[0][1],$new_html[0][3],0,0);//1 广告位id  2广告id 3  素材id 4 用户id 5每次展示花费 6剩余余额
						break;
					}

					//给当前展示的广告展示次数加1 start


				    $redis->incr('view_advertisement_0_'.$new_html[0][0]);

					//给当前展示的广告展示次数加1 end

					//扣钱 扣缓存  start
					//
					/*$redis->get('userinfo_'.$new_html[0][3]);
					$redis->watch('userinfo_'.$new_html[0][3]);//别删
					$redis->multi();*/

					$remain = $redis->Incrbyfloat('userinfo_'.$new_html[0][3],'-'.$new_html[0][2]); //减该用户的缓存冻结金额


					/*$incr = $redis->exec();
					if(!$incr){
						$redis->decr('view_advertisement_'.$adsystem_id.'_'.$new_html[0][0]);
						echo json_encode($ad);exit;
					}*///别删

					//扣钱 扣缓存  end
					//增加基础访问数据
					$this->count($adsystem_id,$new_html[0][0],$new_html[0][1],$new_html[0][3],$new_html[0][2],$remain);//1 广告位id  2广告id 3  素材id 4 用户id 5每次展示花费6剩余余额
				  break;
				default:
					echo json_encode(array('code'=>104,'msg'=>"没有广告"));exit;
				  break;
			}

		

		echo json_encode($ad);exit;

    }

    /*
    * 正常接口api
     **/
    public function api_flow(){
    	header("Access-Control-Allow-Origin: *");//同源策略 跨域请求 头设置
    	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		header('Access-Control-Allow-Methods: GET, POST, PUT');
        header('Content-type: application/json');

        $redis = new \redis();  
		//$redis->connect('172.30.2.132', 6379);
        $redis->connect(config('redis.host'), config('redis.port'));
        $adsystem_id = input('as_id');//接受广告位id

        //判断该广告位是否是开启的

        $adsense_value = explode(',',$redis->get('adsense_'.$adsystem_id));

        
        if(empty($adsense_value[0]) || $adsense_value[2] != 1){

        	$return4 = $this->create_adsense($adsystem_id);

        	if($return4){

        		$adsense_value = explode(',',$redis->get('adsense_'.$adsystem_id));

        	}else{

        		echo json_encode(array('code'=>104,'msg'=>"广告位不存在或者广告位被禁止了"));exit;

        	}
        	
        }

        /*备份
		$key_data = 'advertisement_'.$adsystem_id.'_*';

		$keys_arr = $redis->keys($key_data);//查找获取该广告位所有的包时段广告的 key

		//如果该广告位没有包时段广告,就返回空，然后去触发接口寻找包时段广告
		if(empty($keys_arr)){

			$return1 = $this->create_advertisements($adsystem_id,1,$adsense_value[9]);

			$keys_arr = $redis->keys($key_data);//查找获取该广告位所有的包时段广告的 key

			if(empty($keys_arr) && $adsense_value[3] == 2){
				//如果没有包时段的广告位，再去找竞价的广告位(前提是广告位必须是开放竞价的)
				$key_data = 'advertisement_0'.'_*';

				$keys_arr = $redis->keys($key_data);//查找获取该广告位所有的广告的 key

				//如果该广告位没有竞价广告,就返回空，然后去触发接口寻找竞价广告
				if(empty($keys_arr)){

					$return2 = $this->create_advertisements($adsystem_id,2,$adsense_value[9]);

					if($return2){

						$keys_arr = $redis->keys($key_data);//查找获取该广告位所有的广告的 key

					}else if($adsense_value[5] != 2){

						echo json_encode(array('code'=>104,'msg'=>"该广告位没有广告"));exit;//没素材且freetype为3 就直接隐藏该广告位
					}
					
				}


			}else if($adsense_value[5] != 2){

				echo json_encode(array('code'=>104,'msg'=>"该广告位没有广告"));exit;//没素材且freetype为3 就直接隐藏该广告位
			}
			
		}*/
		$key_data = 'advertisement_'.$adsystem_id.'_*';

		$keys_arr = $redis->keys($key_data);//查找获取该广告位所有的包时段广告的 key

		if(empty($keys_arr) && $adsense_value[3] == 2){
			//如果没有包时段的广告位，再去找竞价的广告位(前提是广告位必须是开放竞价的)
			$key_data = 'advertisement_0'.'_*';

			$keys_arr = $redis->keys($key_data);//查找获取该广告位所有的广告的 key

			//如果该广告位没有竞价广告
			if(empty($keys_arr) && $adsense_value[5] != 2){

				echo json_encode(array('code'=>104,'msg'=>"该广告位没有广告"));exit;//没素材且freetype为3 就直接隐藏该广告位
						
			}else{

				$old_html = array();

			}

		}elseif(empty($keys_arr) && $adsense_value[3] != 2){

			echo json_encode(array('code'=>104,'msg'=>"该广告位没有广告"));exit;//没素材且freetype为3 就直接隐藏该广告位

		}else{

			$j = 0;
			foreach ($keys_arr as $key => $value) {
				$all_keys[$key] = explode(',',$redis->get($value));
				$isset_run[$key] = $all_keys[$key][12];
				if($isset_run[$key] == 1){
					$j++;
				}
			}
			if($j == 0 && $adsense_value[3] == 2){
				//如果包时段的广告位都暂停了，再去找竞价的广告位(前提是广告位必须是开放竞价的)
				$key_data = 'advertisement_0'.'_*';

				$keys_arr = $redis->keys($key_data);//查找获取该广告位所有的广告的 key

				//如果该广告位没有竞价广告
				if(empty($keys_arr) && $adsense_value[5] != 2){

					echo json_encode(array('code'=>104,'msg'=>"该广告位没有广告"));exit;//没素材且freetype为3 就直接隐藏该广告位
							
				}else{

					$old_html = array();

				}
			}else{
				$old_html = array();
			}
		}

	
		

		$i = 0;

		foreach ($keys_arr as $key => $value) {

			$value_arr[$key] = explode(',',$redis->get($value));//0 广告位id 1 广告id 2广告素材id 3 模式 4每一次展示花费（分）5开始时间 6结束时间 7全站投放？ 8次数限制 9已展现次数	10用户userid 11状态 12启用（停止）13adsiteid 14 15project_type

			//判断广告平台对不对start
			if($adsense_value[9] != $value_arr[$key][13]){
				continue;
			}
			//判断广告平台对不对end
			//判断广告的开启和暂停 start
			
			if($value_arr[$key][12] == 2){
				continue;
			}

			//判断广告的开启和暂停 end
			
			//判断时间是否合适 start
			if(date('Y-m-d H:i:s') > $value_arr[$key][6] || $value_arr[$key][11] == 4){	
				$value_arr[$key][11] = 4;
				$redis->set($value,implode(',',$value_arr[$key]));
				$this->update_advertisement($value_arr[$key][1],4);
				continue;
			}

			
			//判断时间是否合适 end
			
			//让广告开启
			if($value_arr[$key][11] != 3){
				$value_arr[$key][11] = 3;
				$redis->set($value,implode(',',$value_arr[$key]));
				$this->update_advertisement($value_arr[$key][1],3);
			}
			//让广告开启end
			
			//判断广告位类型和广告类型是否一致start
			/*if(($adsense_value[6] != $value_arr[$key][15] && $adsense_value[6] != 1) || ($adsense_value[6] ==1 && $value_arr[$key][15]==2)){
				continue;
			}*/
			if($adsense_value[6] != $value_arr[$key][15]){
				continue;
			}
			//判断广告位类型和广告类型是否一致end
			
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
			//$redis->watch('userinfo_'.$value_arr[$key][10]);//别删
			if(($money[$key] < $value_arr[$key][4] || $money[$key] <= 0 || !$money[$key]) && $value_arr[$key][3] != 1){
				$value_arr[$key][11] = 4;
				$redis->set($value,implode(',',$value_arr[$key]));
				$this->update_advertisement($value_arr[$key][1],4);
				continue;
			}


			//判断用户钱够不够 end
			
			//判断展示次数限制 start
			if($value_arr[$key][3] != 1) {
				
		       	$view[$key] = $redis->get('view_advertisement_0_'.$value_arr[$key][1]);

		    }else{
		    	
		    	$view[$key] = $redis->get('view_advertisement_'.$adsystem_id.'_'.$value_arr[$key][1]);

		    }

			if($view[$key] >= $value_arr[$key][8] && $value_arr[$key][3] != 1){
				$value_arr[$key][11] = 4;
				$redis->set($value,implode(',',$value_arr[$key]));
				$this->update_advertisement($value_arr[$key][1],4);
				continue;
			}

			//判断展示次数限制 end
			

			if(strpos($value_arr[$key][2],'|')===false){

			 	$material_data[$key] = $redis->get('material_'.$value_arr[$key][2]);
				
				if($material_data[$key] === false){

					$return3 = $this->create_materials($value_arr[$key][2]);

					if($return3){
						
						$material_data[$key] = $redis->get('material_'.$value_arr[$key][2]);

					}else{

						$value_arr[$key][11] = 2;
						$redis->set($value,implode(',',$value_arr[$key]));
						$this->update_advertisement($value_arr[$key][1],2);		
						continue;//如果素材不存在就跳出 继续下一个循环
					}
					
				}

				$material_data_ex[$key] = explode(',',$material_data[$key]);

				if(($material_data_ex[$key][1] != $adsense_value[0] || $material_data_ex[$key][2] != $adsense_value[1]) && $adsense_value[6] != 1){

					continue;
				}

			}else{
				
			    $zzz = explode('|',$value_arr[$key][2]);
			   
			    foreach ($zzz as $k => $v) {
			    	
			    	$material_data[$k] = $redis->get('material_'.$v);
			
					if($material_data[$k] === false){
						
						$return3 = $this->create_materials($v);

						if($return3){
							
							$material_data[$k] = $redis->get('material_'.$v);

						}else{

							$value_arr[$key][11] = 2;
							$redis->set($value,implode(',',$value_arr[$key]));
							$this->update_advertisement($value_arr[$key][1],2);		
							continue;//如果素材不存在就跳出 继续下一个循环
						}
						
					}
					$material_datas[$k] = explode(',',$material_data[$k]);
					
					if(($material_datas[$k][1] != $adsense_value[0] || $material_datas[$k][2] != $adsense_value[1]) && $adsense_value[6] != 1){
						
						unset($zzz[$k]);
					}
					
					
					
			    }

			    shuffle($zzz);
			    if(empty($zzz)){
			    	continue;
			    }
			    $material_data[$key] = $redis->get('material_'.$zzz[0]);
			    $value_arr[$key][2] = $zzz[0];
			   
			}


			$material_arr[$key] = explode(',',$material_data[$key]);
		
			
			switch ($material_arr[$key][0]){
				case 1:
				  	$info = array('adsystem_id'=>'as_'.$adsystem_id,'image_url'=>'','click_url'=>config('app_domain')."/operation/index/asClick?adsId=".$adsystem_id."&advId=".$value_arr[$key][1]."&userId=".$value_arr[$key][10]."&mId=".$value_arr[$key][2]."&spending=".$value_arr[$key][3]."&url=".$material_arr[$key][15],'open_target'=>'_blank','image_description'=>$material_arr[$key][5],'sensetype'=>$adsense_value[4]);
					//$this->assign('info',$info);
					$html[$i] = $info;
					//$html[$i] = "aaaaaa";
				  break;  
				case 2:
				  	$info = array('adsystem_id'=>'as_'.$adsystem_id,'image_url'=>$material_arr[$key][5],'click_url'=>config('app_domain')."/operation/index/asClick?adsId=".$adsystem_id."&advId=".$value_arr[$key][1]."&userId=".$value_arr[$key][10]."&mId=".$value_arr[$key][2]."&spending=".$value_arr[$key][3]."&url=".$material_arr[$key][6],'open_target'=>$material_arr[$key][8],'image_description'=>$material_arr[$key][7],'sensetype'=>$adsense_value[4]);
					//$this->assign('info',$info);
					$html[$i] = $info;
					//$html[$i] = "bbbbb";
				  break;
				case 3:
				  	$info = array('adsystem_id'=>'as_'.$adsystem_id,'image_url'=>$material_arr[$key][5],'width'=>$adsense_value[0],'height'=>$adsense_value[1],'sensetype'=>$adsense_value[4]);
					//$this->assign('info',$info);
					$html[$i] = $info;
					//$html[$i] = "ccccc";
				  break;
				case 4:
				  	$info = array('adsystem_id'=>'as_'.$adsystem_id,'image_url'=>$material_arr[$key][5],'click_url'=>config('app_domain')."/operation/index/asClick?adsId=".$adsystem_id."&advId=".$value_arr[$key][1]."&userId=".$value_arr[$key][10]."&mId=".$value_arr[$key][2]."&spending=".$value_arr[$key][3]."&url=".$material_arr[$key][6],'open_target'=>$material_arr[$key][8],'image_description'=>$material_arr[$key][7],'sensetype'=>$adsense_value[4]);
					//$this->assign('info',$info);
					$html[$i] = $info;
					//$html[$i] = "ddddd";
				  break;
				default:
				  $html[$i] = "";
				  break;
			}

			if(empty($html[$i])){
				unset($html[$i]);
				continue;
			}

			$bbb = array(array($value_arr[$key][1],$value_arr[$key][2],$value_arr[$key][4],$value_arr[$key][10],$value_arr[$key][3]),$html[$i]);//array 0 广告id 1广告素材id  2每次花费 3 用户id 4模式(1包时段 2CPM 3CPC)
			
			$old_html[$i] = array('title'=>$bbb,'weight'=>$value_arr[$key][4]);
			
			$i++;
		}
		
		
		if(empty($old_html) && $adsense_value[5] == 2){

			switch($adsense_value[6]){
				case 1:

				$info = array('adsystem_id'=>'as_'.$adsystem_id,'image_description'=>$adsense_value[8],'font_size'=>14,'font_color'=>"#000000",'font_decoration'=>"none",'font_weight'=>"normal",'font_style'=>"normal",'hover_font_color'=>"#000000",'hover_font_decoration'=>"none",'hover_font_weight'=>"normal",'hover_font_style'=>"normal",'click_url'=>config('app_domain')."/operation/index/asClick?adsId=".$adsystem_id."&advId=0&userId=0&mId=0&spending=0&url=".$adsense_value[10],'horizon_position'=>"left",'margin'=>"0",'open_target'=>"_blank",'sensetype'=>$adsense_value[4]);
					$html = $info;
					//$this->assign('info',$info);
					//$html = $this->fetch('ad_txt');
				  break;  
				case 2:

				$info = array('adsystem_id'=>'as_'.$adsystem_id,'image_url'=>$adsense_value[7],'click_url'=>config('app_domain')."/operation/index/asClick?adsId=".$adsystem_id."&advId=0&userId=0&mId=0&spending=0&url=".$adsense_value[10],'open_target'=>"_blank",'image_description'=>"",'sensetype'=>$adsense_value[4]);
				$html = $info;
					//$this->assign('info',$info);
					//$html = $this->fetch('ad_pic');
				  break;
				  case 3:

				$info = array('adsystem_id'=>'as_'.$adsystem_id,'image_url'=>$adsense_value[7],'click_url'=>config('app_domain')."/operation/index/asClick?adsId=".$adsystem_id."&advId=0&userId=0&mId=0&spending=0&url=".$adsense_value[10],'open_target'=>"_blank",'image_description'=>$adsense_value[8],'sensetype'=>$adsense_value[4]);
				$html = $info;
					//$this->assign('info',$info);
					//$html = $this->fetch('ad_pic');
				  break;
				default:
					echo json_encode(array('code'=>104,'msg'=>"该广告位没有广告"));exit;//没素材且freetype为3 就直接隐藏该广告位
					break;
				}
			$old_html = array('0'=>array('title'=>array(array(0,0,0,0,0),$html),'weight'=>1));

		}else if(empty($old_html) && $adsense_value[5] != 2){
			echo json_encode(array('code'=>104,'msg'=>"没有广告"));exit;
		}
		
		switch ($adsense_value[3]){

				case 1:
					$new_html = $this->my_max($old_html);

					$ad = array(
						    'code'=>100,
							'html'=>$new_html[1],
							'width'=>$adsense_value[0],
							'height'=>$adsense_value[1],
						);
					
					$this->count($adsystem_id,$new_html[0][0],$new_html[0][1],$new_html[0][3],0,0);//1 广告位id  2广告id 3  素材id 4 用户id 5每次展示花费 6剩余余额
				  break;  
				case 2:
					$new_html = $this->roll($old_html);//随机权重函数
					
					$ad = array(
						    'code'=>100,
							'html'=>$new_html[1],
							'width'=>$adsense_value[0],
							'height'=>$adsense_value[1],
						);

					//前提 sensemodel 为2 (1关闭竞价 2开启竞价)
					//1.广告展示的是打底广告 ($new_html[0][4] == 0 && $adsense_value[5] == 2)
					//2.广告展示的是包时段广告 ($new_html[0][4] == 1)
					//3.广告展示的是cpc广告($new_html[0][4] == 3)
					if(($new_html[0][4] == 1) || ($new_html[0][4] == 0 && $adsense_value[5] == 2) || $new_html[0][4] == 3){
						$this->count($adsystem_id,$new_html[0][0],$new_html[0][1],$new_html[0][3],0,0);//1 广告位id  2广告id 3  素材id 4 用户id 5每次展示花费 6剩余余额
						break;
					}

					//给当前展示的广告展示次数加1 start

				    $redis->incr('view_advertisement_0_'.$new_html[0][0]);

					
					//给当前展示的广告展示次数加1 end

					//扣钱 扣缓存  start
					//
					/*$redis->get('userinfo_'.$new_html[0][3]);
					$redis->watch('userinfo_'.$new_html[0][3]);//别删
					$redis->multi();*/

					$remain = $redis->Incrbyfloat('userinfo_'.$new_html[0][3],'-'.$new_html[0][2]); //减该用户的缓存冻结金额


					/*$incr = $redis->exec();
					if(!$incr){
						$redis->decr('view_advertisement_'.$adsystem_id.'_'.$new_html[0][0]);
						echo json_encode($ad);exit;
					}*///别删

					//扣钱 扣缓存  end
					//增加基础访问数据
					
					$this->count($adsystem_id,$new_html[0][0],$new_html[0][1],$new_html[0][3],$new_html[0][2],$remain);//1 广告位id  2广告id 3  素材id 4 用户id 5每次展示花费6剩余余额
				  break;
				default:
					echo json_encode(array('code'=>104,'msg'=>"没有广告"));exit;
				  break;
			}

		

		echo json_encode($ad);exit;

    }


    /**
	 * 按比例分配随机数发生器 - 生产环境使用
	 * !!!请确保数组的所有权重是正整数
	 * @param array $arr
	 */
	private function roll($arr = [])
	{
		$high = 0;
		foreach ($arr as $key => $val)
		{
			$new_weight = $val['weight'] * 100;
			$arr[$key]['weight'] = $new_weight;
			$high += $new_weight;
		}
		if($high != 0)
		{
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
		} else {

			$pick = array_rand($arr);
			return $arr[$pick]['title'];
		}
	}

	private function my_max($arr = []){

		if(!empty($arr))
		{
			if(count($arr) == 1)
			{
				return $arr[0]['title'];
			}

			$max = 0;
			shuffle($arr);
			foreach($arr as $v)
			{

				if(bccomp($v['weight'], $max['weight'], 6) != -1)
				{
					$max = $v;
				}
			}


			return $max['title'];
		} else {
			return [];
		}
	}




    private function count($adsystemid,$advertisementid,$materialid,$userid,$cost,$remain){

    	$ip = $this->request->ip();
    	$userAgent = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'';
    	$userAgent_base64 = base64_encode($userAgent);
    	$ips = array();
    	//$ips = $this->GetIpLookup($ip);
    	$redis = new \redis();  
		//$redis->connect('172.30.2.132', 6379);
		$redis->connect(config('redis.host'), config('redis.port'));
		//$redis->flushDB();exit;
		//$redis_key = 'ad_'.$adsystemid;
		$redis_key = 'ad';
		//$redis_key = 'ad_'.time();
		$redis_value = ';'.$adsystemid.','.$advertisementid.','.$materialid.','.$userid.','.$ip.','.''.','.''.','.''.','.$_SERVER['HTTP_HOST'].','.$userAgent_base64.','.date("Y-m-d H:i:s").','.$cost.','.$remain;
		error_log($redis_value.PHP_EOL,3,'/home/httpd/adsystem/runtime/ads_'.date('Ymd').'.log');
		$redis->append($redis_key,$redis_value);

    }


 
      
    private function GetIpLookup($ip = ''){ 

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

     private function create_userinfo($userid){
     	$redis = new \redis();  
		//$redis->connect('172.30.2.132', 6379);
		$redis->connect(config('redis.host'), config('redis.port'));
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
	private function create_advertisements($adsystem_id,$sensemodel,$adsiteid){
		$redis = new \redis();  
		//$redis->connect('172.30.2.132', 6379);
		$redis->connect(config('redis.host'), config('redis.port'));

		if($sensemodel == 2){
			$map['status'] = array('in','2,3');
			$map['running_status'] = 1;
			$map['adsenseid'] = 0;
			$map['adsiteid'] = $adsiteid;
			$advertisements = Db::table('advertisement')->where($map)->select()->toarray();
			$adsystem_id = 0;

			
		}else{
			$map['status'] = 2;
			$map['running_status'] = 1;
			$map['adsenseid'] = $adsystem_id;
			$advertisements = Db::table('advertisement')->where($map)->select()->toarray();
		}
		
		if(empty($advertisements)){
			return false;
		}
		
		foreach ($advertisements as $key => $value) {

			$redis_key[$key] = 'advertisement_'.$adsystem_id.'_'.$value['id'];//该条广告的key

			$redis_key_view[$key] = 'view_advertisement_'.$adsystem_id.'_'.$value['id'];//该条广告展示量的key
			
			$begin_end_time = explode(",",$value['time']);
			

			if(date('Y-m-d H:i:s') < $begin_end_time[0] || date('Y-m-d H:i:s') > $begin_end_time[1]){
				continue;
			}

			$redis_value[$key] = $adsystem_id.','.$value['id'].','.$value['materialid'].','.$value['spending'].','.($value['price']/1000).','.$begin_end_time[0].','.$begin_end_time[1].','.$value['orientation'].','.$value['numlimit'].','.'0'.','.$value['userid'].','.$value['status'].','.$value['running_status'].','.$value['adsiteid'].','.$value['adv_type'].','.$value['project_type'];//0 广告位id 1 广告id 2广告素材id 3 模式 4每一次展示花费（分）5开始时间 6结束时间 7全站投放？ 8次数限制 9已展现次数 10用户id 11状态 12运行状态
			
			$redis->set($redis_key[$key],$redis_value[$key]);//设置缓存key value 

			$redis->set($redis_key_view[$key],'0');//设置缓存key value

					
		}

		return true;
		
	}


    /**
     * [create_materials description]
     * 生成广告素材
     * @return [type] [null]
     */
    private function create_materials($materialid){
    	
    	$redis = new \redis();  
		//$redis->connect('172.30.2.132', 6379);
		$redis->connect(config('redis.host'), config('redis.port'));
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
			  case 3:
			  //flash缓存
			    $material_detail = Db::table('material_flash')->where(array('sid'=>$materials['material_id']))->find();

			    $redis_value = '3,'.$materials['width'].','.$materials['height'].','.$materials['userid'].','.$materials['status'].','.$material_detail['image_url'];
			    break;// 跳出循环
			  case 4:
			  //对联缓存
			    //echo '对联';
			    $material_detail = Db::table('material_info')->where(array('sid'=>$materials['material_id']))->find();
			    $redis_value = '4,'.$materials['width'].','.$materials['height'].','.$materials['userid'].','.$materials['status'].','.$material_detail['image_url'].','.$material_detail['click_url'].','.$material_detail['image_description'].','.$material_detail['open_target'].','.$material_detail['adaptation'];
			    break;// 跳出循环*/
			}

		$back = $redis->setex($redis_key,300,$redis_value);//设置缓存key value 设置素材过期时间为5分钟  

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
    
    private function create_adsense($adsystem_id){

    	$redis = new \redis();  
		//$redis->connect('172.30.2.132', 6379);
		$redis->connect(config('redis.host'), config('redis.port'));
		$map['id'] = $adsystem_id;
		$map['status'] = 1;

		$adsense_data = Db::table('adsense')->where($map)->find();

		if(!$adsense_data){
			return false;
		}

		$adsense_key = 'adsense_'.$adsense_data['id'];
		$adsense_value = $adsense_data['width'].','.$adsense_data['height'].','.$adsense_data['status'].','.$adsense_data['sensemodel'].','.$adsense_data['sensetype'].','.$adsense_data['freetype'].','.$adsense_data['materialmodel'].','.$adsense_data['imgurl'].','.$adsense_data['text'].','.$adsense_data['adsite'].','.$adsense_data['linkurl'];
		$back = $redis->setex($adsense_key,300,$adsense_value);//设置缓存key value
		if($back){
			return true;	
		}else{
			return false;
		}


    }



    private function update_advertisement($advertisementid,$status){
    	$back = Db::table('advertisement')->where(array('id'=>$advertisementid))->setField('status',$status);

    }





    public function preview(){
        
        $material_id = $_REQUEST['material_id'];
        /*$material_ids = explode('|',$material_id);
        shuffle($material_ids);*/
        if(strpos($material_id,'|') !== false){ 
        	$material_ids = explode('|',$material_id);
        	foreach ($material_ids as $key => $value) {
        		$info[$key] = Db::table('material_main')->where(array('id'=>$value))->find();
        	}
        	$this->assign('info',$info);
			return $this->fetch('many_preview');
		}
        $data = Db::table('material_main')->where(array('id'=>$material_id))->find();

        if($data['material_type'] == 4){
        	$info =  Db::table('material_info')->where(array('sid'=>$data['material_id']))->find();

            $this->assign('info',$info);
            $this->assign('width',$data['width']);
        	$this->assign('height',$data['height']);
        	$this->assign('material_id',$material_id);
        	return $this->fetch('preview_info');
        }elseif($data['material_type'] == 1){
        	$this->assign('width','300');
       		$this->assign('height','30');
       		$this->assign('material_id',$material_id);
       		return $this->fetch();
        }else{
        	$this->assign('width',$data['width']);
        	$this->assign('height',$data['height']);
        	$this->assign('material_id',$material_id);
        	return $this->fetch();
        }
        

        

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
                $info['adsystem_id'] = "as_".$material_id;
                $info['image_description'] = $info['material_content'];         
                $this->assign('info',$info);
                $html = $this->fetch('ad_txt');
                $material = array(
                	'code'=>100,
                    'html'=>$html,
                    'width'=>1000,
                    'height'=>30,
            	);
                echo json_encode($material);
                break; // 跳出循环
              case 2:
              //图片素材
                //echo '图片';
                $info =  Db::table('material_image')->where(array('sid'=>$material_data['material_id']))->find();
                $info['adsystem_id'] = "as_".$material_id;
                $info['sensetype'] = 0;
                $this->assign('info',$info);
                $html = $this->fetch('ad_pic');
                $material = array(
                	'code'=>100,
                    'html'=>$html,
                    'width'=>$material_data['width'],
                    'height'=>$material_data['height'],
            	);
                echo json_encode($material);
                break;// 跳出循环
              case 3:
              //flash素材
                $info =  Db::table('material_flash')->where(array('sid'=>$material_data['material_id']))->find();
                $info['adsystem_id'] = "material_id_".$material_data['material_id'];
                $info['sensetype'] = 0;
                $info['width'] = $material_data['width'];
                $info['height'] = $material_data['height'];
                $this->assign('info',$info);
                $html = $this->fetch('ad_flash');
                $material = array(
                    'html'=>$html,
                    'width'=>$material_data['width'],
                    'height'=>$material_data['height'],
            	);
                echo json_encode($material);
                break;// 跳出循环
              case 4:
              //信息流素材
                echo "信息流素材";
                break;// 跳出循环
            }

    }




}
