<?php
include_once '../../../../vendor/autoload.php';
use Aws\S3\S3Client;
/**
 * Created by JetBrains PhpStorm.
 * User: taoqili
 * Date: 12-7-18
 * Time: 上午11: 32
 * UEditor编辑器通用上传类
 */
class Uploader
{
    private $fileField; //文件域名
    private $file; //文件上传对象
    private $base64; //文件上传对象
    private $config; //配置信息
    private $oriName; //原始文件名
    private $fileName; //新文件名
    private $fullName; //完整文件名,即从当前配置目录开始的URL
    private $filePath; //完整文件名,即从当前配置目录开始的URL
    private $fileSize; //文件大小
    private $fileType; //文件类型
    private $stateInfo; //上传状态信息,
	private $url;
    private $stateMap = array( //上传状态映射表，国际化用户需考虑此处数据的国际化
        "SUCCESS", //上传成功标记，在UEditor中内不可改变，否则flash判断会出错
        "文件大小超出 upload_max_filesize 限制",
        "文件大小超出 MAX_FILE_SIZE 限制",
        "文件未被完整上传",
        "没有文件被上传",
        "上传文件为空",
        "ERROR_TMP_FILE" => "临时文件错误",
        "ERROR_TMP_FILE_NOT_FOUND" => "找不到临时文件",
        "ERROR_SIZE_EXCEED" => "文件大小超出网站限制",
        "ERROR_TYPE_NOT_ALLOWED" => "文件类型不允许",
        "ERROR_CREATE_DIR" => "目录创建失败",
        "ERROR_DIR_NOT_WRITEABLE" => "目录没有写权限",
        "ERROR_FILE_MOVE" => "文件保存时出错",
        "ERROR_FILE_NOT_FOUND" => "找不到上传文件",
        "ERROR_WRITE_CONTENT" => "写入文件内容错误",
        "ERROR_UNKNOWN" => "未知错误",
        "ERROR_DEAD_LINK" => "链接不可用",
        "ERROR_HTTP_LINK" => "链接不是http链接",
        "ERROR_HTTP_CONTENTTYPE" => "链接contentType不正确",
        "INVALID_URL" => "非法 URL",
        "INVALID_IP" => "非法 IP"
    );

    /**
     * 构造函数
     * @param string $fileField 表单名称
     * @param array $config 配置项
     * @param bool $base64 是否解析base64编码，可省略。若开启，则$fileField代表的是base64编码的字符串表单名
     */
    public function __construct($fileField, $config, $type = "upload")
    {
        $this->fileField = $fileField;
        $this->config = $config;
        $this->type = $type;
        if ($type == "remote") {
            $this->saveRemote();
        } else if($type == "base64") {
            $this->upBase64();
        } else {
            $this->upFile();
        }

        $this->stateMap['ERROR_TYPE_NOT_ALLOWED'] = iconv('unicode', 'utf-8', $this->stateMap['ERROR_TYPE_NOT_ALLOWED']);
    }

    /**
     * 上传文件的主处理方法
     * @return mixed
     */
    private function upFile()
    {
        $file = $this->file = $_FILES[$this->fileField];

        if (!$file) {
            $this->stateInfo = $this->getStateInfo("ERROR_FILE_NOT_FOUND");
            return;
        }
        if ($this->file['error']) {
            $this->stateInfo = $this->getStateInfo($file['error']);
            return;
        } else if (!file_exists($file['tmp_name'])) {
            $this->stateInfo = $this->getStateInfo("ERROR_TMP_FILE_NOT_FOUND");
            return;
        } else if (!is_uploaded_file($file['tmp_name'])) {
            $this->stateInfo = $this->getStateInfo("ERROR_TMPFILE");
            return;
        }

        $this->oriName = $file['name'];
        $this->fileSize = $file['size'];
        $this->fileType = $this->getFileExt();
        $this->fullName = $this->getFullName();
        $this->filePath = $this->getFilePath();
        $this->fileName = $this->getFileName();
        $dirname = dirname($this->filePath);

        //检查文件大小是否超出限制
        if (!$this->checkSize()) {
            $this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
            return;
        }

        //检查是否不允许的文件格式
        if (!$this->checkType()) {
            $this->stateInfo = $this->getStateInfo("ERROR_TYPE_NOT_ALLOWED");
            return;
        }
       
        //移动文件
        if (!(move_uploaded_file($file["tmp_name"], $this->filePath) && file_exists($this->filePath))) { //移动失败
            $this->stateInfo = $this->getStateInfo("ERROR_FILE_MOVE");
        } else { //移动成功
			$src='/data/media/media/'.$this->fullName;
			$str=isset($_COOKIE["cookie"]["mediaName"])?$_COOKIE["cookie"]["mediaName"]:''.'/中金在线';
			//文字水印
			$this->watermark($src,$str);
            $this->stateInfo = $this->stateMap[0];
			//上传到aws
			$imgurl = $this->UpPic($this->getFileName(),$this->getFilePath());
			$this->url = $imgurl;
        }
    }

    /**
     * 水印
     * @param  [type]  $img  [description]
     * @param  [type]  $text [description]
     * @param  integer $pos  [description]
     * @return [type]        [description]
     */
	private function watermark($img,$text,$pos=9){
        $name=isset($_COOKIE["cookie"]["mediaNickName"])?$_COOKIE["cookie"]["mediaNickName"]:'';		
        $text='财视号/'.$name;
		$water_img='';
		$img_info = getimagesize($img);
		$img_w = $img_info[0];
		$img_h = $img_info[1];
		$font='simhei.ttf';
		$size = 12;
		$color="#ffffff";
		$text_info = imagettfbbox($size, 0, $font, $text);
		$w_w = $text_info[2] - $text_info[6];
		$w_h = $text_info[3] - $text_info[7];
		//建立原图资源
		 
		switch ( $img_info[2] ){
		    case 1:
			    $res_img = imagecreatefromgif($img);
			break;
            case 2:
                $res_img = imagecreatefromjpeg($img);
			break;
            case 3:
                $res_img = imagecreatefrompng($img);
			break;
		}
		//确定水印的位置
		switch ( $pos ){
            case 1:
                $x = $y =25;
			break;
            case 2:
        		$x = ($img_w - $w_w)/2; 
        		$y = 25;
			break;
            case 3:
    			$x = $img_w - $w_w;
    			$y = 25;
			break;
		    case 4:
    			$x = 25;
    			$y = ($img_h - $w_h)/2;
			break;
		    case 5:
    			$x = ($img_w - $w_w)/2; 
    			$y = ($img_h - $w_h)/2;
			break;
            case 6:
    			$x = $img_w - $w_w;
    			$y = ($img_h - $w_h)/2;
			break;
		    case 7:
    			$x = 25;
    			$y = $img_h - $w_h;
			break;
		    case 8:
    			$x = ($img_w - $w_w)/2;
    			$y = $img_h - $w_h;
			break;
            case 9:
    			$x = $img_w - $w_w;
    			$y = $img_h - $w_h;
			break;
	        default :
    			$x = mt_rand(25, $img_w - $w_w);
    			$y = mt_rand(25, $img_h - $w_h);
		}
		//写入图片资源
		$r = hexdec(substr($color, 1,2));
		$g = hexdec(substr($color, 3,2));
		$b = hexdec(substr($color, 5,2));
		$color = imagecolorallocate($res_img, $r, $g, $b);
		imagettftext($res_img, $size, 0, $x, $y, $color, $font, $text);  
		//生成图片类型
		imagecreatefromstring(file_get_contents($img));
		switch ( $img_info[2] ){
			case 1:
			    imagecreatefromgif($res_img,$img);
			 break;
			case 2:
			    //imagecreatefromjpeg($res_img,$img);
			    imagejpeg($res_img,$img);
			    break;
			case 3:
			    imagejpeg($res_img,$img);
			    break;
		}
		if(isset($res_img)) imagedestroy ($res_img);
		return $res_img;
	}
	
    /**
     * 处理base64编码的图片上传
     * @return mixed
     */
    private function upBase64()
    {
        $base64Data = $_POST[$this->fileField];
        $img = base64_decode($base64Data);

        $this->oriName = $this->config['oriName'];
        $this->fileSize = strlen($img);
        $this->fileType = $this->getFileExt();
        $this->fullName = $this->getFullName();
        $this->filePath = $this->getFilePath();
        $this->fileName = $this->getFileName();
        $dirname = dirname($this->filePath);

        //检查文件大小是否超出限制
        if (!$this->checkSize()) {
            $this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
            return;
        }

        //创建目录失败
        if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
            $this->stateInfo = $this->getStateInfo("ERROR_CREATE_DIR");
            return;
        } else if (!is_writeable($dirname)) {
            $this->stateInfo = $this->getStateInfo("ERROR_DIR_NOT_WRITEABLE");
            return;
        }

        //移动文件
        if (!(file_put_contents($this->filePath, $img) && file_exists($this->filePath))) { //移动失败
            $this->stateInfo = $this->getStateInfo("ERROR_WRITE_CONTENT");
        } else { //移动成功
            $this->stateInfo = $this->stateMap[0];
        }

    }

    /**
     * 拉取远程图片
     * @return mixed
     */
    private function saveRemote()
    {
        $imgUrl = htmlspecialchars($this->fileField);
        $imgUrl = str_replace("&amp;", "&", $imgUrl);

        //http开头验证
        if (strpos($imgUrl, "http") !== 0) {
            $this->stateInfo = $this->getStateInfo("ERROR_HTTP_LINK");
            return;
        }

        preg_match('/(^https*:\/\/[^:\/]+)/', $imgUrl, $matches);
        $host_with_protocol = count($matches) > 1 ? $matches[1] : '';

        // 判断是否是合法 url
        if (!filter_var($host_with_protocol, FILTER_VALIDATE_URL)) {
            $this->stateInfo = $this->getStateInfo("INVALID_URL");
            return;
        }

        preg_match('/^https*:\/\/(.+)/', $host_with_protocol, $matches);
        $host_without_protocol = count($matches) > 1 ? $matches[1] : '';

        // 此时提取出来的可能是 ip 也有可能是域名，先获取 ip
        $ip = gethostbyname($host_without_protocol);
        // 判断是否是私有 ip
        if(!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
            $this->stateInfo = $this->getStateInfo("INVALID_IP");
            return;
        }

        //获取请求头并检测死链
        $heads = get_headers($imgUrl, 1);
        if (!(stristr($heads[0], "200") && stristr($heads[0], "OK"))) {
            $this->stateInfo = $this->getStateInfo("ERROR_DEAD_LINK");
            return;
        }
        //格式验证(扩展名验证和Content-Type验证)
        $fileType = strtolower(strrchr($imgUrl, '.'));
        if (!in_array($fileType, $this->config['allowFiles']) || !isset($heads['Content-Type']) || !stristr($heads['Content-Type'], "image")) {
            $this->stateInfo = $this->getStateInfo("ERROR_HTTP_CONTENTTYPE");
            return;
        }

        //打开输出缓冲区并获取远程图片
        ob_start();
        $context = stream_context_create(
            array('http' => array(
                'follow_location' => false // don't follow redirects
            ))
        );
        readfile($imgUrl, false, $context);
        $img = ob_get_contents();
        ob_end_clean();
        preg_match("/[\/]([^\/]*)[\.]?[^\.\/]*$/", $imgUrl, $m);

        $this->oriName = $m ? $m[1]:"";
        $this->fileSize = strlen($img);
        $this->fileType = $this->getFileExt();
        $this->fullName = $this->getFullName();
        $this->filePath = $this->getFilePath();
        $this->fileName = $this->getFileName();
        $dirname = dirname($this->filePath);

        //检查文件大小是否超出限制
        if (!$this->checkSize()) {
            $this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
            return;
        }

        //创建目录失败
        if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
            $this->stateInfo = $this->getStateInfo("ERROR_CREATE_DIR");
            return;
        } else if (!is_writeable($dirname)) {
            $this->stateInfo = $this->getStateInfo("ERROR_DIR_NOT_WRITEABLE");
            return;
        }

        //移动文件
        if (!(file_put_contents($this->filePath, $img) && file_exists($this->filePath))) { //移动失败
            $this->stateInfo = $this->getStateInfo("ERROR_WRITE_CONTENT");
        } else { //移动成功
            $this->stateInfo = $this->stateMap[0];
			$imgurl = $this->UpPic($this->getFileName(),$this->getFilePath());
			$this->url = $imgurl;
        }

    }

    /**
     * 上传错误检查
     * @param $errCode
     * @return string
     */
    private function getStateInfo($errCode)
    {
        return !$this->stateMap[$errCode] ? $this->stateMap["ERROR_UNKNOWN"] : $this->stateMap[$errCode];
    }

    /**
     * 获取文件扩展名
     * @return string
     */
    private function getFileExt()
    {
        return strtolower(strrchr($this->oriName, '.'));
    }

    /**
     * 重命名文件
     * @return string
     */
    private function getFullName()
    {
		$format = date("YmdHis", time()) . "" . rand(0, 1000);
        $ext = $this->getFileExt();
        return $format . $ext;
    }

    /**
     * 获取文件名
     * @return string
     */
    private function getFileName () {
        return substr($this->filePath, strrpos($this->filePath, '/') + 1);
    }

    /**
     * 获取文件完整路径
     * @return string
     */
    private function getFilePath()
    {
        $fullname = $this->fullName;
		$rootPath = '../../../uploads/ueditor/img/';

        if(!is_dir($rootPath)){
            mkdir($rootPath,0777,true);
        }
        if (substr($fullname, 0, 1) != '/') {
            $fullname = '/' . $fullname;
        }
        return $rootPath . $fullname;
    }

    /**
     * 文件类型检测
     * @return bool
     */
    private function checkType()
    {
        return in_array($this->getFileExt(), $this->config["allowFiles"]);
    }

    /**
     * 文件大小检测
     * @return bool
     */
    private function  checkSize()
    {
        return $this->fileSize <= ($this->config["maxSize"]);
    }

    /**
     * 获取当前上传成功文件的各项信息
     * @return array
     */
    public function getFileInfo()
    {
        return array(
            "state" => $this->stateInfo,
            //"url" => "http://mp.test.cnfol.com/".$this->fullName,
			"url" => $this->url,
            "title" => $this->fileName,
            "original" => $this->oriName,
            "type" => $this->fileType,
            "size" => $this->fileSize,
			"filepath" =>$this->getFilePath()
        );
    }
	private function UpPic($FileName,$File,$Key='ueditor'){
		$Bucket = 'images.shichai.cnfol.com';

		$client = \Aws\S3\S3Client::factory(array(
            'credentials' => array(
                'key' => 'AKIAPPUXZCQIWYGUEQ5A',
                'secret'  => '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',
            ),
            'region'=>'cn-north-1',
            'version'=>'latest',
        ));

		$Key = $Key.'/'.date('Ym', time()).'/'.date('d');
		$res = $client -> putObject(
			array(
				'Bucket'=>$Bucket,
				'Key'=>$Key.'/'.$FileName,
				'SourceFile'=>$File,
			));

		if($res){
			return 'http://'.$Bucket.'/'.$Key.'/'.$FileName;
		}else{
			return "";
		}
	}
}