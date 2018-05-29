<?php
namespace api\v1\model;

use think\Exception;
use think\Model;
class Income extends Model
{
    protected $readonly = ['userid','money','plus_or_minus','description','create_time'];
    protected $visible = ['money','plus_or_minus','description','create_time'];
}