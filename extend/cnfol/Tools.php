<?php
	namespace cnfol;
	/**
	* 常用工具类
	*/
	class Tools
	{
		/**
		 * 错误日志
		 * @return [type] [description]
		 */
		public static function err_log($data=array(),$mark='全局',$errtype='E_ALL',$filepath='errlog',$filename='errlog.txt')
		{
			$request=request();
			$filepath=RUNTIME_PATH.$filepath.'/'.date('Y-m-d');
			if(!is_dir($filepath)){
				mkdir($filepath,0777,true);
			}
			$myfile=fopen($filepath.'/'.$filename, 'a');
			
			$string="";
			$string.='[TIME]：'.date('Y-m-d H:i:s')."\r\n";
			$string.="[URL]：".$request->url(true)."\r\n";
			$string.='[MARK]：'.$mark."\r\n";
			
			$string.='[DATA]：'.var_export($data,true)."\r\n";
			$string.='[ERROR_TYPE]：'.$errtype."\r\n";
			$string.='-------------------------------------'."\r\n";
			fwrite($myfile,$string);
			fclose($myfile);
		}

		/**
		 * 错误日志,html格式
		 * @return [type] [description]
		 */
		public static function err_html($data='',$filepath='404')
		{
			$request=request();
			$filepath=RUNTIME_PATH.$filepath.'/'.date('Y-m-d');
			if(!is_dir($filepath)){
				mkdir($filepath,0777,true);
			}
			$myfile=fopen($filepath.'/'.date('YmdHis').'.html', 'a');
			fwrite($myfile,$data);
			fclose($myfile);
		}

	 	/**
	     * 发送邮箱
	     * @param  $content 邮件内容
	     * @return [type] [description]
	     */
	    public static function send_email($content,$email)
	    {
			$url = "http://mail.api.cnfol.net/index.php";
		   	$data = array(
				'key' => 'da2f00b38ed9273b974f254b7ba27571',
				'mailto' => $email,//接收邮箱
				'subject' => '中金在线-财经号',
				'content' => $content,
				'charset' => 'UTF-8',
				'smtpID' => rand(24,26),
				'Original' => 'passport',
				'FromUser' => 'no_reply@cnfol.com'//发送邮箱
			);
			return self::curl_post($url,$data);
	    }

		/**
		 * 发送短信
		 * @param $content  短信内容
		 * @param $phone  接收手机号
		 * @return [flag] [info]
		 */
		public static function send_mobile($content,$phone)
		{
			$url = "http://api.access.cnfol.com:8085/send.shtml";
			$data = array(
				'p.receiver' => $phone,//接收手机
				'p.priority' => 5,
				'p.sendChannel' => 9,
				'p.sign' => 0,
				'p.msg' => $content,
				'p.apiKey' => '7777772E636E666F6C2E636F6D',
				'p.can'=>1,
				'p.sysType'=>9,
			);
			
			$msg=self::curl_get($url.'?'.http_build_query($data));
			$info=json_decode($msg);
			switch ($info->state) {
				case '0':
					$msg="发送成功！";
					break;
				case '-1':
					$msg="请求参数为空！";
					break;
				case '-2':
					$msg="短信内容为空！";
					break;
				case '-3':
					$msg="接收者号码为空！";
					break;
				case '-4':
					$msg="接收者号码不能超过1000个！";
					break;
				case '-5':
					$msg="没有权限！";
					break;
				case '-6':
					$msg="接收者号码格式错误！";
					break;
				case '-7':
					$msg="系统类型不能为空！";
					break;
				case '-8':
					$msg="异常！";
					break;
				case '-9':
					$msg="已经被禁用！";
					break;
				case '-10':
					$msg="频率太快！";
					break;
				case '-11':
					$msg="超过一天！";
					break;
				default:
					$msg="未知错误！";
					break;
			}

			return array('status'=>$info->state,'msg'=>$msg);
		}

		/**
		 * alert弹出信息
		 * @param  string $msg [description]
		 * @param  string $url [description]
		 * @return [type]      [description]
		 */
		public static function alert($msg='异常错误！',$url='/')
		{
			echo "<script>alert('".$msg."');</script>";
			echo "<script>location.href='http://".$_SERVER['HTTP_HOST'].url($url)."'</script>";
		}


		/**
		 * 把对象数组的某个字段替换成键值
		 * @return [type] [description]
		 */
		public static function value_to_key($data=array(),$field='id')
		{
			$_data=array();
			foreach ($data as $key => $value) {
				$_data[$value->$field]=$value;
			}
			return $_data;
		}

	
		/**
		 * curl-post请求
		 * @return [type] [description]
		 */
		public  static function  curl_post($url,$data=array())
		{
			if($url=='')return '';
			$ch = curl_init ();
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//跳过证书检测，可以https请求
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);//跳过证书检测，可以https请求
			curl_setopt ( $ch, CURLOPT_URL, $url);
			curl_setopt ( $ch, CURLOPT_POST, 1 );
			curl_setopt ( $ch, CURLOPT_HEADER, 0 );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);//超时时间
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data);
			$return = curl_exec ( $ch );
			curl_close ( $ch );
			return   $return;
		}

		/**
		 * curl -get请求
		 * @return [type] [description]
		 */
		public static function curl_get($url)
		{
			if($url=='')return '';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    // 要求结果为字符串且输出到屏幕上
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置请求时长
			$output = curl_exec($ch);
			curl_close($ch);
			return $output;
		}


		/**
		 * 图片
		 * @return [type] [description]
		 */
		public static function upload($field='image',$filepath='',$validate=array()){
			$_validate=array(
				'size'=> 1024*1024*5,
				'ext' => 'jpg,png,gif,jpeg',
			);
			$validate=array_merge($_validate,$validate);

			$filepath=!empty($filepath)?$filepath:'uploads/';

		    // 获取表单上传文件 例如上传了001.jpg
		    $file = request()->file($field);
		    // 移动到框架应用根目录/public/uploads/ 目录下
		    $info = $file->validate($validate)->move($filepath);

		    $array=array();
		    if($info){
		        // 成功上传后 获取上传信息
		        // echo $info->getExtension();// 输出 jpg
		        // echo $info->getSaveName();// 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
		        // echo $info->getFilename(); // 输出 42a79759f284b767dfcb2a0197904287.jpg
		        $array['status']=1;
		        $array['filepath']=$filepath.str_replace('\\', '/', $info->getSaveName());
		        $array['filename']=$info->getFilename();
		    }else{
		        $array['status']=0;
		        $array['msg']=$file->getError();
		    }
		   
		    return $array;
		}

		/**
	     * 调整尺寸图片大小
	     * @param  [type]  $source_path   [description]
	     * @param  integer $target_width  [description]
	     * @param  integer $target_height [description]
	     * @param  string  $fixed_orig    [description]
	     * @return [type]                 [description]
	     */
	    public	static function myImageResize($source_path, $target_width =364, $target_height =204, $fixed_orig = '')
		{
		  	$source_info = getimagesize($source_path);
		  	$source_width = $source_info[0];
		  	$source_height = $source_info[1];
		  	$source_mime = $source_info['mime'];
		  	$ratio_orig = $source_width / $source_height;
		  	if ($fixed_orig == 'width'){
				//宽度固定
				$target_height = $target_width / $ratio_orig;
		  	}elseif ($fixed_orig == 'height'){
				//高度固定
				$target_width = $target_height * $ratio_orig;
		  	}else{
				//最大宽或最大高
				if ($target_width / $target_height > $ratio_orig){
				  	$target_width = $target_height * $ratio_orig;
				}else{
				  	$target_height = $target_width / $ratio_orig;
				}
		  	}
		  	switch ($source_mime){
				case 'image/gif':
				  	$source_image = imagecreatefromgif($source_path);
				  	break;
				case 'image/jpeg':
				  	$source_image = imagecreatefromjpeg($source_path);
				  	break;
				 
				case 'image/png':
				  	$source_image = imagecreatefrompng($source_path);
				  	break;
				default:
				  	return false;
				  	break;
		  	}
		  	$target_image = imagecreatetruecolor($target_width, $target_height);
		  	imagecopyresampled($target_image, $source_image, 0, 0, 0, 0, $target_width, $target_height, $source_width, $source_height);
			$imgArr = explode('.', $source_path);
			$target_path = $imgArr[0] . '.' . $imgArr[1];
			imagejpeg($target_image, $target_path, 100);
		}

		/*
		 * $filename 新文件名
		 * $file 要上传的文件路径(本地)
		 * $key  自定义目录
		 */
		public static function UpPic($filename,$file,$key='mediaimg'){
			$bucket = 'images.shichai.cnfol.com';
			$client = \Aws\S3\S3Client::factory(array(
				'credentials' => array(
					'key' => 'AKIAPPUXZCQIWYGUEQ5A',
					'secret'  => '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',
				),
				'region'=>'cn-north-1',
				'version'=>'latest',
			));

			$key = $key.'/'.date('Ym', time()).'/'.date('d');
			$res = $client -> putObject(
				array(
					'Bucket'=>$bucket,
					'Key'=>$key.'/'.$filename,
					'SourceFile'=>$file,
					'ACL' => 'public-read',
				));
			if($res){
				$path = 'http://'.$bucket.'/'.$key.'/'.$filename;
				return array('status'=>1,'path'=>$path);
			}else{
				return array('status'=>0,'msg'=>'上传失败！');
			}
		}

		/**
		 * 文件下载
		 * @return [type] [description]
		 */
		public static function down_load($filepath)
		{
			//首先要判断给定的文件存在与否 
			if(!file_exists($filepath)){ 
				return "没有该文件文件"; 
			}
			$ext=pathinfo($filepath,PATHINFO_EXTENSION);
			$filename=md5(time()."中金在线").'.'.$ext;
			$fp=fopen($filepath,"r"); 
			$file_size=filesize($filepath); 
			//下载文件需要用到的头 
			Header("Content-type: application/octet-stream"); 
			Header("Accept-Ranges: bytes"); 
			Header("Accept-Length:".$file_size); 
			Header("Content-Disposition: attachment; filename=".$filename); 
			$buffer=1024; 
			$file_count=0; 
			//向浏览器返回数据 
			while(!feof($fp) && $file_count<$file_size){ 
				$file_con=fread($fp,$buffer); 
				$file_count+=$buffer; 
				echo $file_con; 
			} 
			fclose($fp); 
		}

		/**
		 * jsonp解密
		 * @param  [type]  $jsonp [description]
		 * @param  boolean $assoc [description]
		 * @return [type]         [description]
		 */
		public static function jsonp_decode($jsonp, $assoc = false) 
		{
	        if($jsonp[0] !== '[' && $jsonp[0] !== '{') {
	            $jsonp = substr($jsonp, strpos($jsonp, '('));
	        }
	        return json_decode(trim($jsonp,'();'), $assoc);
	    }

	    /**
	     * 根据数组的某个值进行排序
	     * @param  [type] $array [description]
	     * @param  [type] $field [description]
	     * @param  string $sort  [description]
	     * @return [type]        [description]
	     */
	    public static function sort_array($array, $field, $sort = 'SORT_DESC')
		{
		    if (empty($array)) return array();
		    $arrSort = array();
		    foreach ($array as $uniqid => $row) {
		        foreach ($row as $key => $value) {
		            $arrSort[$key][$uniqid] = $value;
		        }
		    }
		    array_multisort($arrSort[$field], constant($sort), $array);
		    return $array;
		}
	   
	}
	
