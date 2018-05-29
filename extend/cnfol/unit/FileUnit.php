<?php
namespace cnfol\unit;
/**
 * 文件处理类
 * Class FileUnit
 * @package cnfol\unit
 */

class FileUnit
{
	/**
	 * 创建目录
	 * @param $_path string 目录
	 * @param $_mode string 权限
	 *
	 * @return bool
	 */
	static function mark_dir($_path, $_mode)
	{

		$fullpath   = '';
		$_path      = explode('/', $_path);

		while(list(,$v) = each($_path))
		{
			$fullpath .= "$v/";

			if(is_dir($fullpath) == false)
			{
				$oldmask = umask(0);

				if(mkdir($fullpath, $_mode) == false)
					return false;

				umask($oldmask);
			}
		}
		return true;
	}
}