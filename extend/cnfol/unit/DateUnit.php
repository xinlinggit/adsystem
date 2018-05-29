<?php
namespace cnfol\unit;
/**
 * 时间处理类
 * Class DateUnit
 * @package cnfol\unit
 */

class DateUnit
{
	/**
	 * 时间转换成标签
	 *
	 * @param string $time
	 *
	 * @return bool|string
	 */
	static public function time_tag($time = '')
	{
		$time_tag = '';
		if ($time) {
			$time = strtotime($time);
			$n = time() - $time;
			if ($n > 0 && $n < 60) {
				$time_tag = '刚刚';//0<N<1分钟， 刚刚
			} elseif ($n >= 60 && $n < 3600) {    //1分钟<N<60分钟，m分钟前
				$time_tag = floor($n / 60) . '分钟前';
			} elseif ($n >= 3600 && $n < 86400) {
				$time_tag = floor($n / 3600) . '小时前';//1小时<N<24小时，m小时前
			} else {
				$time_tag = date('m-d H:m', $time);
			}
		} else {
			$time_tag = '发布时间未知';
		}

		return $time_tag;
	}
}