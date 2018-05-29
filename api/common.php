<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * 批量删除文件夹及文件
 * @param string $path
 * @return bool|null
 */
function deldir($path = null){
    //给定的目录不是一个文件夹
    if(!is_dir($path)){
        return null;
    }

    $fh = opendir($path);
    while(($row = readdir($fh)) !== false){
        //过滤掉虚拟目录
        if($row == '.' || $row == '..'){
            continue;
        }

        if(!is_dir($path.'/'.$row)){
            unlink($path.'/'.$row);
        }
        deldir($path.'/'.$row);

    }
    //关闭目录句柄，否则出Permission denied
    closedir($fh);
    //删除文件之后再删除自身
    if(!rmdir($path)){
        echo $path.'无权限删除<br>';
    }
    return true;
}