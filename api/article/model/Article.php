<?php
namespace api\article\model;

use think\Exception;
use think\Model;
class Article extends Model
{

    // 设置当前模型对应的完整数据表名称
    protected $table = 'cnfol_content';

    // 设置当前模型的数据库连接
//    protected $connection = [
//        // 数据库类型
//        'type'        => 'mysql',
//        // 服务器地址
//        'hostname'    => '127.0.0.1',
//        // 数据库名
//        'database'    => 'thinkphp',
//        // 数据库用户名
//        'username'    => 'root',
//        // 数据库密码
//        'password'    => '',
//        // 数据库编码默认采用utf8
//        'charset'     => 'utf8',
//        // 数据库表前缀
//        'prefix'      => 'think_',
//        // 数据库调试模式
//        'debug'       => false,
//    ];
    protected $connection = 'mysql://dbuser_liuwei:s6pU&qegSrttzcas8@10.1.4.211:3306/cnfolCMS#utf8';
    public function top5()
    {
        return $this->where('CatId',3028)
            ->field('Title,Url')
            ->limit(5)
            ->order('ContId','desc')
            ->select();
    }


}