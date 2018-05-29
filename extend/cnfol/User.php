<?php
	namespace cnfol;
	/**
	* 常用工具类
	*/
	class User
	{
		
	    /**
	     * 根据用户中心id返回头像
	     * @param  [type] $u_id [description]
	     * @return [type]       [description]
	     */
	    public static function get_head_logo($u_id,$size=48)
	    {
	    	if(!is_numeric($u_id)){return '';}
	    	$head_url='http://head.cnfolimg.com';
	    	$id_encode = md5($u_id);
    		$folder_1  = substr($id_encode, 0, 2);
    		$folder_2  = substr($id_encode, 2, 2);
    		return $head_url.'/'.$folder_1.'/'.$folder_2.'/'.$u_id.'/'.'head.'.$u_id.'.'.$size;
	    }
	   
	}
	
