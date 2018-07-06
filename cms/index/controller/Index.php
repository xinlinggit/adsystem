<?php
namespace cms\index\controller;
use cms\common\controller\Common;

/**
 * 默认入口
 * @package cms\content\controller
 */
class Index extends Common
{
	public function index(){
		return $this->display('敬请期待');
	}

	public function redisinfo(){
		return $this->fetch();
	}

	public function getRedisinfo(){
    	$redis = new \redis();  
		$redis->connect('172.30.2.132', 6379);
		dump($redis->info());exit;
    }

    public function getAllkeys(){
    	$redis = new \redis();  
		$redis->connect('172.30.2.132', 6379);
		dump($redis->keys('*'));exit;
    }

    public function getUserinfo(){
    	$redis = new \redis();  
		$redis->connect('172.30.2.132', 6379);
		$data = $redis->keys('userinfo_*');
		sort($data);
		foreach ($data as $key => $value) {
			$new_data[$value] = $redis->get($value);
		}
		dump($new_data);exit;
    }
    public function getAdsense(){
    	$redis = new \redis();  
		$redis->connect('172.30.2.132', 6379);
		$data = $redis->keys('adsense_*');
		sort($data);
		foreach ($data as $key => $value) {
			$new_data[$value] = $redis->get($value);
		}
		dump($new_data);exit;
    }

    public function getAdvertisement(){
    	$redis = new \redis();  
		$redis->connect('172.30.2.132', 6379);
		$data = $redis->keys('advertisement_*');
		sort($data);
		foreach ($data as $key => $value) {
			$new_data[$value] = $redis->get($value);
		}
		dump($new_data);exit;
    }

    public function getMaterial(){
    	$redis = new \redis();  
		$redis->connect('172.30.2.132', 6379);
		$data = $redis->keys('material_*');
		sort($data);
		foreach ($data as $key => $value) {
			$new_data[$value] = $redis->get($value);
		}
		dump($new_data);exit;
    }
}

/* End of file Index.php */
/* Location: ./app_cms/index/controller/Index.php */