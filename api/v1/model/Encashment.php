<?php
namespace api\v1\model;

use think\Exception;
use think\Exception\PDOException;
use think\Exception\DBException;
use think\Model;
class Encashment extends Model
{
    protected $visible = ['id','userid','money','status','create_time','is_success','remarks'];
    public function getIsSuccessAttr($value)
    {
        return $value === null ? -1 : $value;
    }
    public function getRemarksAttr($value)
    {
        return $value === null ? '' : $value;
    }
    /*
     * 申请提现
     */
    public function apply($data){
        try {
            $this->isUpdate(false)->allowField('userid,money,bank_card,open_bank,name')->save($data);
            return $this->getData('id');
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