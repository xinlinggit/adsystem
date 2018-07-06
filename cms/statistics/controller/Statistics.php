<?php
namespace cms\statistics\controller;
use cnfol\unit\ArrayUnit;
use think\Db;
use think\Loader;
use think\Config;
use cms\common\model;

class statistics extends \cms\common\controller\Common{

	// 获取所有广告位
	protected function _getPosition(){
		$userid = input('userid');
		if(!empty($userid))
		{
			$where['userid'] = "$userid";
		} else {
			$where = [];
		}
		$row = Db::table('advertisement')
		         ->alias('a')
		         ->join('adserver.adsense s', 'a.adsenseid = s.id', 'LEFT')
		         ->field('a.adsenseid, s.sensename')
		         ->where($where)
		         ->group('adsenseid')
		         ->select();
		return $row;
	}

	protected function _getMaterials()
	{
		$userid = input('userid');
		if(!empty($userid))
		{
			$where['userid'] = "$userid";
		} else {
			$where = [];
		}
		return Db::table('material_main')->field('id, material_title')
		         ->where($where)->select();
	}

	public function show(){
		if($this->request->isGet()) {
			$where = '1 ';
			$para = $this->request->param();
			if(!empty($para['adsenseid']))
			{
				$where .= ' AND adsystemid = ' . $para['adsenseid'];
			}
			if(!empty($para['adv']))
			{
				$where .= ' AND advertisementid = ' . $para['adv'];
			}
		} else {
			$where = '';
		}
		$materials = $this->_getMaterials();
		$positions = $this->_getPosition();
		$materials_arr = $positions_arr = [];
		foreach($positions as $k => $v)
		{
			$positions_arr[$v['adsenseid']] = $v['sensename'];
		}
		foreach($materials as $k => $v)
		{
			$materials_arr[$v['id']] = $v['material_title'];
		}
		$this->view->assign('ad_position',ArrayUnit::array_to_options($positions_arr, $this->request->param('adsenseid')));
		$this->view->assign('materials',ArrayUnit::array_to_options($materials_arr, $this->request->param('id')));
		$data = $this->_getAds($where);
		$this->assign('list', $data['list']);
		$this->assign('page', $data['page']);
		$chart_data = $this->_getChartsData($where);
		$x_data_temp = '';
		$y_data_temp ='';
		$y_data_temp_click ='';
		foreach($chart_data as $k => $v)
		{
			$x_data_temp .= "," . "'" . $v['time'] . "'";
			$y_data_temp .= "," . $v['sum']?:0;
			$click_sum = $v['click_sum'] ?: 0;
			$y_data_temp_click .= "," . $click_sum;
		}
		$x_data = '[' . trim($x_data_temp,',');
		$y_data = '[' . trim($y_data_temp,',');
		$y_data_click = '[' . trim($y_data_temp_click,',');
		$x_data .= ']';
		$y_data .= ']';
		$y_data_click .= ']';
		$this->assign('x_data', $x_data);
		$this->assign('y_data', $y_data);
		$this->assign('y_data_click', $y_data_click);
		return $this->fetch('show');
	}

	protected function _getSites()
	{
		return Db::table('adsite')->field('id,sitename')->where(['status' => 1])->select();
	}

	protected function _getChartsData($where)
	{
		$materialid = input('materialid');
		$btime = input('btime');
		$etime = input('etime');
		$username = input('username');
		if(!empty($materialid))
		{
			$where .= ' AND materialid = ' . $materialid;
		}
		if((!empty($btime)) && (!empty($etime)))
		{
			$where .= " AND time between '$btime' AND '$etime'";
		}
		if(!$btime && !$etime)
		{
			$now = time();
			$etime = date('Y-m-d', $now);
			$btime = date('Y-m-d', $now - 15 * 3600 * 24);
			$where .= " AND `time` between '$btime' AND '$etime'";
		}
		if(!empty($username)) {
			$userid = $this->_getUseridByUsername( $username );
			if($userid)
			{
				$where .= ' AND userid = "'. intval($userid).'"';
			} else {
				// 用户不存在返回 []
				return [];
			}
		}
//		$where .= ' AND count(*) > 0';
		$row = Db::table('view_statistics')
		         ->field('adsystemid,userid, advertisementid, materialid, time, sum(click_sum) as click_sum, sum(record_sum) as sum')
		         ->group('time')
		         ->where($where)
		         ->order('time')
		         ->select();
		return $row;
	}

	protected function _getUseridByUsername($username)
	{
		$username = trim($username);
		$userid = Db::table('ad_admin_user')->field('id')->where("username = '$username'")->value('id');
		if($userid)
		{
			return $userid;
		} else {
			return null;
		}
	}

	/**
	 * 获取表格的数据
	 * @param $where
	 *
	 * @return array
	 * @throws \think\exception\DbException
	 */
	protected function _getAds($where){
		$materialid = input('materialid');
		$btime = input('btime');
		$etime = input('etime');
		$username = input('username');
		if(!empty($materialid))
		{
			$where .= ' AND `r`.`materialid` = ' . $materialid;
		}
		if((!empty($btime)) && (!empty($etime)))
		{
			$where .= " AND `r`.`time` between '$btime' AND '$etime'";
		}
		// 如果没有选择时间，则显示最近15天的统计数据
		if(!$btime && !$etime)
		{
			$now = time();
			$etime = date('Y-m-d', $now);
			$btime = date('Y-m-d', $now - 15 * 3600 * 24);
			$where .= " AND `r`.`time` between '$btime' AND '$etime'";
		}
		if(!empty($username)) {
			$userid = $this->_getUseridByUsername( $username );
			if($userid)
			{
				$where .= ' AND `r`.`userid`=' . "$userid";
			} else {
				$where .= ' AND `r`.`userid`=null';
			}
		}
		$row = Db::table('view_statistics')
		         ->alias('r')
		         ->field("`r`.`id`,adv.id as adv_id,
    `r`.`adsystemid`,
    `r`.`advertisementid`,
    `r`.`materialid`,
    `r`.`time`, title,`adv`.`time` as `adv_time`, adv.create_time, adsense.sensename,
    `record_sum` as sum,click_sum,ROUND(record_click_cost / 100, 2) AS record_click_cost,
    ROUND(record_cost / 100, 2) AS cost")
		         ->join('adserver.advertisement adv', 'r.advertisementid = adv.id', 'LEFT')
			     ->join('adserver.adsense', 'r.adsystemid = adsense.id', 'LEFT')
		         ->where($where)
			     ->order('adv.create_time desc')
		         ->paginate(15, false, ['query' => $this->request->param()]);
		return ['list' => $row->items(), 'page' => $row];
	}

	/**
	 * 获取广告位对应的广告 - 被前台 ajax 调用
	 */
	public function getAd()
	{
		// 根据站点，获取对应的广告位
		$adsenseid = input('adsenseid');
		$adsense = $this->_getAd($adsenseid);
		return json_encode($adsense);
	}

	protected function _getAd($adsenseid = 0)
	{
		return Db::table('advertisement')->field('id, title')->where(['adsenseid' => $adsenseid])->select();
	}
}