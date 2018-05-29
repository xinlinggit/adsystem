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
		foreach($chart_data as $k => $v)
		{
			$x_data_temp .= "," . "'" . $v['time'] . "'";
			$y_data_temp .= "," . $v['sum'];
		}
		$x_data = '[' . trim($x_data_temp,',');
		$y_data = '[' . trim($y_data_temp,',');
		$x_data .= ']';
		$y_data .= ']';
		$this->assign('x_data', $x_data);
		$this->assign('y_data', $y_data);
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
		if(!empty($username)) {
			$userid = $this->_getUseridByUsername( $username );
			if($userid)
			{
				$where .= ' AND userid = "'. $userid.'"';
			}
		}
		$where .= ' AND count(*) > 0';
		$row = Db::table('record_day')
		         ->field('adsystemid,userid, advertisementid, materialid, time, sum(sum) as sum')
		         ->group('time')
		         ->having($where)
		         ->order('time')
		         ->select();
		return $row;
	}


	protected function _getUseridByUsername($username)
	{
		$userid = Db::table('ad_admin_user')->where("username = '$username'")->value('id');
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
		if(!empty($username)) {
			$userid = $this->_getUseridByUsername( $username );
			if($userid)
			{
				$where .= ' AND `r`.`userid`=' . "$userid";
			}
		}
		$row = Db::table('record_day')
		         ->alias('r')
		         ->field("`r`.`id`,
    `r`.`adsystemid`,
    `r`.`advertisementid`,
    `r`.`materialid`,
    `r`.`time`, title,`adv`.`time` as `adv_time`,
    `sum`,
    ROUND(cost / 100, 2) AS cost")
		         ->join('adserver.advertisement adv', 'r.advertisementid = adv.id', 'LEFT')
		         ->where($where)
			     ->order('adv.id desc')
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