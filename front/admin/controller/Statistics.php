<?php
namespace app\admin\controller;
use think\db;

/**
 * 统计管理
 * @package app\admin\controller
 */
class statistics extends Admin{

	public function index()
	{
		if (cookie('hisi_iframe')) {
			$this->view->engine->layout(false);
			return $this->fetch('iframe');
		} else {
			return $this->fetch();
		}
	}

	// 获取本人投过的所有站点
	protected function _getSite(){
		$row = Db::table('advertisement')
		         ->alias('a')
		         ->join('adserver.adsite s', 'a.adsiteid = s.id', 'LEFT')
		         ->field('a.adsiteid, s.sitename')
		         ->where('userid = ' . ADMIN_ID)
		         ->group('adsiteid')
		         ->select();
		return $row;
	}

	// 获取本人所有投放过广告的广告位
	protected function _getPosition(){
		$row = Db::table('advertisement')
			->alias('a')
			->join('adserver.adsense s', 'a.adsenseid = s.id', 'LEFT')
			->field('a.adsenseid, s.sensename')
			->where('userid = ' . ADMIN_ID)
			->group('adsenseid')
			->select();
		return $row;
	}

	protected function _getMaterials()
	{
		return Db::table('material_main')->field('id, material_title')
		                                 ->where('userid = ' . ADMIN_ID)->select();
	}

	/**
	 * 基本统计
	 */
	public function general()
	{
		if($this->request->isGet()) {
			$where = '1 ';
			$platform = [];
			$param_platform = input('param.platform/a');
			if($param_platform)
			{
				if(in_array('pc', $param_platform))
				{
					$platform[] = 1;
				}
				if(in_array('app', $param_platform))
				{
					$platform[] = 2;
				}
				if(in_array('web', $param_platform))
				{
					$platform[] = 3;
				}
				if(count($platform) > 0)
				{
					$where .= ' AND platform in (' . implode(',', $platform) .')';
				}
			}

		} else {
			$where = '';
		}
		$data = $this->_getAdsGaneral($where);
		$this->assign('data_list', $data['row']);
		$this->assign('pages', $data['pages']);
		$chart_data = $this->_getChartsDataGeneral($where);
		$x_data_temp = '';
		$y_data_temp ='';
		$y_data_temp_click ='';
		foreach($chart_data as $k => $v)
		{
			$x_data_temp .= "," . "'" . $v['time'] . "'";
			$y_data_temp .= "," . $v['my_sum'];
			$click_sum = $v['click_sum'] ?: 0;
			$y_data_temp_click .= "," . $click_sum;
		}
		$x_data = '[' . trim($x_data_temp,',');
		$y_data = '[' . trim($y_data_temp,',');
		$y_data_click = '[' . trim($y_data_temp_click,',');
		$x_data .= ']';
		$y_data .= ']';
		$y_data_click .= ']';

		$this->assign('platform', $param_platform ?: []);
		$this->assign('x_data', $x_data);
		$this->assign('y_data', $y_data);
		$this->assign('y_data_click', $y_data_click);
		return $this->fetch('general');
	}

	/**
	 * 广告统计
	 * @return mixed
	 */
	public function show(){
		if($this->request->isGet()) {
			$where = '1 ';
			$para = $this->request->param();
			if(!empty($para['platform']))
			{
				$where .= ' AND r.platform = ' . $para['platform'];
			}
			if(!empty($para['adv']))
			{
				$where .= ' AND advertisementid = ' . $para['adv'];
			}
		} else {
			$where = '';
		}
		$data = $this->_getAds($where);
		$this->assign('data_list', $data['row']);
		$this->assign('pages', $data['pages']);
		$chart_data = $this->_getChartsData($where);
		$x_data_temp = '';
		$y_data_temp ='';
		$y_data_temp_click ='';
		foreach($chart_data as $k => $v)
		{
			$x_data_temp .= "," . "'" . $v['time'] . "'";
			$y_data_temp .= "," . $v['my_sum'];
			$click_sum = $v['click_sum']?:0;
			$y_data_temp_click .= "," . $click_sum;
		}
		$x_data = '[' . trim($x_data_temp,',');
		$y_data = '[' . trim($y_data_temp,',');
		$y_data_click = '[' . trim($y_data_temp_click,',');
		$x_data .= ']';
		$y_data .= ']';
		$y_data_click .= ']';
		$this->assign('selected_platform', input('platform') ?: 0);
		$this->assign('selected_adv', input('adv') ?: 0);
		$this->assign('x_data', $x_data);
		$this->assign('y_data', $y_data);
		$this->assign('y_data_click', $y_data_click);
		return $this->fetch('show');
	}

	// 获取绘图数据-广告统计
	protected function _getChartsData($where)
	{
		$materialid = input('materialid');
		$btime = input('btime');
		$etime = input('etime');
		// TODO：限制时间跨度为 30 天
		if(!empty($materialid))
		{
			$where .= ' AND materialid = ' . $materialid;
		}
		if((!empty($btime)) && (!empty($etime)))
		{
			$having_where = " time between '$btime' AND '$etime'";
		} else {
			$having_where = '';
		}
		$where .= ' AND userid = '. ADMIN_ID;
		$row = Db::table('view_statistics')
			->alias('r')
		         ->field('adsystemid,userid, advertisementid, materialid, time,sum(`click_sum`) as click_sum, sum(`record_sum`) as my_sum')
				 ->where($where)
		         ->group('time')
		         ->having($having_where)
		         ->select();
		return $row;
	}

	/**
	 * 获取指定站点(PC, app, 或者 web 下，当前用户的所有广告)
	 */
	public function getMyAdsAjax(){
		$platform_id = input('platform');
		if($platform_id)
		{
			$row = Db::table('advertisement')
				   ->alias('adv')
				   ->join('adserver.adsite ads', 'adv.adsiteid = ads.id', 'left')
				   ->where(['ads.platform' => $platform_id])->field('adv.id, adv.title')->where('userid = ' . ADMIN_ID)->select();
			return json_encode($row);
		} else {
			return [];
		}
	}

	// 获取绘图数据 - 基本统计
	protected function _getChartsDataGeneral($where)
	{
		$btime = input('btime');
		$etime = input('etime');
		// TODO：限制时间跨度为 30 天
		if((!empty($btime)) && (!empty($etime)))
		{
			$having_where = " time between '$btime' AND '$etime'";
		} else {
			$having_where = '';
		}
		$where .= ' AND userid = '. ADMIN_ID;
		$row = Db::table('view_statistics')
		         ->field('adsystemid,userid, advertisementid, materialid, time, sum(`record_sum`) as my_sum, sum(`click_sum`) as click_sum, round(sum(`record_cost`) / 100 , 2) as cost_sum')
		         ->where($where)
		         ->group('time')
		         ->having($having_where)
		         ->select();
		return $row;
	}

	// 广告统计 - 获取列表数据
	protected function _getAds($where){
		$materialid = input('materialid');
		$btime = input('btime');
		$etime = input('etime');
		if(!empty($materialid))
		{
			$where .= ' AND `r`.`materialid` = ' . $materialid;
		}
		if((!empty($btime)) && (!empty($etime)))
		{
			$where .= " AND `r`.`time` between '$btime' AND '$etime'";
		}
		$where .= ' AND `r`.`userid`=' . ADMIN_ID;
		$row = Db::table('view_statistics')
		         ->alias('r')
		         ->field("`r`.`platform`,`r`.`advertisementid`,
    `r`.`adsystemid`,
    `r`.`advertisementid`,
    `r`.`materialid`,
    `r`.`time`, title,`adv`.`time` as `adv_time`, sitename,
    sum(`record_sum`) as my_sum,sum(`click_sum`) as click_sum,
    round(sum(`record_click_cost`) / 100 , 2) as cost_sum_click,
    ROUND(sum(record_cost) / 100, 2) AS cost")
		         ->join('adserver.advertisement adv', 'r.advertisementid = adv.id', 'LEFT')
		         ->join('adserver.adsite as', 'adv.adsiteid = as.id')
		         ->where($where)
			->order('time desc')
			->group('r.advertisementid, r.time')
		         ->paginate(10, false, ['query' => $this->request->param()]);
		$pages = $row->render();
		return ['row' => $row, 'pages' => $pages];
	}

	// 获取列表数据 - 基本统计
	protected function _getAdsGaneral($where){
		$btime = input('btime');
		$etime = input('etime');
		// TODO：限制时间跨度为 30 天
		if((!empty($btime)) && (!empty($etime)))
		{
			$having_where = " time between '$btime' AND '$etime'";
		} else {
			$having_where = '';
		}
		$where .= ' AND userid = '. ADMIN_ID;
		$row = Db::table('view_statistics')
		         ->field('platform,adsystemid,userid, advertisementid, materialid, time, sum(`record_sum`) as my_sum, round(sum(`record_click_cost`) / 100 , 2) as cost_sum_click, round(sum(`record_cost`) / 100 , 2) as cost_sum, sum(`click_sum`) as click_sum')
		         ->where($where)
			     ->order('time desc')
		         ->group('time')
		         ->having($having_where)
			->paginate(10, false, ['query' => $this->request->param()]);
		$pages = $row->render();
		return ['row' => $row, 'pages' => $pages];
	}

	/**
	 * 获取广告广告 - 被前台 ajax 调用
	 */
	public function getAd()
	{
		// 根据站点，获取对应的广告位
		$platform = input('platform');
		$where['platform'] = $platform;
		$where['userid'] = ADMIN_ID;
		$row = Db::table('record_day')->field('advertisementid')->where($where)->group('advertisementid')->select();
		return json_encode($row);
	}
}