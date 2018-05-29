<?php

namespace api\common\behavior;

use think\Request;
use cnfol\Trans;

/**
 * 检测网站语言行为类
 *
 * Class CheckLang
 * @package api\common\behavior
 */
class CheckLang
{
    /**
     * @var string Lang 语言 0:简体 1:繁体
     */
    protected static $Lang = 0;



    public function __construct()
    {
        // 加载配置文件
        self::$Lang = (int) Request::instance()->cookie('lang',0);
    }

    /**
     * 输出渲染前
     *
     * @param $param
     */
    public function fetch_begin(&$param)
    {
        if (self::$Lang){
            $go = new Trans();
            $param = $go->translation($param);
        }
    }

    /**
     * 输出渲染前，ThinkPHP5.0.4默默的将行为规范为psr-4标准
     *
     * @param $param
     */
    public function fetchBegin(&$param)
    {
        if (self::$Lang){
            $go = new Trans();
            $param = ($go->translation($param));
        }
    }
}
