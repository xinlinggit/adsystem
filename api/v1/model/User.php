<?php

namespace api\v1\model;

use think\exception\DbException;
use think\Exception\PDOException;
use think\Model;
use think\model\Merge;

class User extends Merge
{
    // 设置主表名
    protected $table = 'user';
    // 定义关联模型列表
    protected $relationModel = [
        // 给关联模型设置数据表
        'Userinfo'   =>  'userinfo',
    ];
    // 定义关联外键
    protected $fk = 'id';
    protected $mapFields = [
        // 为混淆字段定义映射
        'id'      =>  'User.id',
        'info_id' =>  'Userinfo.id',
    ];
    protected $hidden = ['password','wrong_times','create_time','last_login_time','info_id','withdrawal'];
    // 定义关联模型
    public function income()
    {
        // 一对一关联
        return $this->hasMany('Income');
        //->setEagerlyType(0);//V5.0.5+版本开始，默认使用IN查询方式，此处改为JOIN查询方式
    }
    public function getBankCardAttr($value)
    {
        return $value === null ? '' : $value;
    }
    public function getCardNameAttr($value)
    {
        return $value === null ? '' : $value;
    }
    public function getOpenBankAttr($value)
    {
        return $value === null ? '' : $value;
    }
    public function setTelAttr($tel)
    {
        return empty($tel)?$tel:mobileEncode($tel);
    }
    public function getTelAttr($value)
    {
        return empty($value)?$value:mobileDecode($value);
    }

    /**
     * @title 添加用户
     */
    public function adduser($data)
    {
        try {
            \db('user')->where('jpush_id',$data['jpush_id'])->update(['jpush_id'=>null]);
            $this->allowField('tel,password,username,nickname,email,avatar,usersig,jpush_id')->save($data);
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

    /**
     * @title 修改用户密码
     */
    public function resetpwd($pass, $tel=null)
    {
        try {
            return $this->isUpdate(true)->save(['password' => $pass, 'wrong_times' => 5], $tel?['tel' => $tel]:null);
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