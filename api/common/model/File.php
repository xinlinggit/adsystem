<?php
namespace app\common\model;

use think\Model;

class File extends Model
{
    /**
     * 写入文件上传记录
     * @param $data
     * @param $cate
     * @return mixed
     */
    public function insertRecord($data, $cate)
    {
        return $this->insert(
            [
                "cate"     => $cate,
                "name"     => $data['key'],
                "original" => isset($data['name']) ? $data['name'] : '',
                "type"     => isset($data['type']) ? $data['type'] : '',
                "size"     => isset($data['size']) ? $data['size'] : 0,
                "mtime"    => time(),
            ]
        );
    }
}
