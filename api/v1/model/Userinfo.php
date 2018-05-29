<?php
namespace api\v1\model;

use think\Exception;
use think\exception\DbException;
use think\Exception\PDOException;
use think\Model;

class Userinfo extends Model
{
    protected $readonly = ['id','account','finished','withdrawal'];

    protected $table = 'userinfo';
    public function getBankCardAttr($value)
    {
        return $value === null ? '' : $value;
    }
    public function getCardNameAttr($value)
    {
        return $value === null ? '' : $value;
    }
    /**
     * @title 用户绑定银行卡
     */
    public function setbank($data, $id=null)
    {
        try {
            return $this->isUpdate(true)->save(['bank_card' => $data['bank_card'], 'card_name' => $data['card_name'],'open_bank'=>$data['open_bank']], $id?['id' => $id]:null);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        } catch (DbException $e) {
            $this->error = $e->getMessage();
            return false;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}