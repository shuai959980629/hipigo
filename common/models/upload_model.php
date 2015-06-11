<?php
/**
 * @copyright(c) 2014-04-21
 * @author vikie
 * 文件上传类
 *  
 */
class Upload_Model extends CI_Model
{ 
 
	// 实例
	static protected $_instance;
	
	private $_path = array();
    
	/**
	 * 上传处理
	 * 
	 * 将上传后的文件按照规则处理
	 * @qrcode
	 * $this->move($_FILES, 'temp/uploadfiles/','date','jpg',1024*1024)
	 * @endcode
	 *
	 * @param	array 上传文件$_FILES信息数组
	 * @param	string 存储目录
	 * @param	string 命名规则[null][md5|date]，默认不更改名字。
	 * @param	string 允许扩展名[jpg,gif,png]，默认不限制。
	 * @param	integer 文件大小上限，以KB为单位，默认不限制。
	 * @return	bool
	 */
	public function move($data, $savePath, $nameRules = null, $extension = null, $maxSize = null)
	{
		$this->_path = array();
		
		if (empty($data) || !is_array($data))
		{
			trigger_error('没有获得$_FILES数据', E_USER_ERROR);
			return false;
		}
		
		$data = $this->dealFiles($data);
		
		foreach ( $data as $file )
		{
			// 跳过无效上传
			if (empty($file['name']))
			{
				trigger_error('文件名无效[' . $file['name'] . ']', E_USER_ERROR);
				return false;
			}
			
		    // 上传文件的扩展信息
			$file['extension'] = $this->getExtension($file['name']);
			$file['savepath'] = $savePath;
			$file['savename'] = $this->getSaveName($file['name'], $nameRules);
			
			// 检查文件大小
			if (!$this->checkSize($file['size'], $maxSize))
			{
				@unlink($file['tmp_name']);
				return false;
			}
			
			// 检查文件类型
			if (!$this->checkExt($file['extension'], $extension))
			{
				@unlink($file['tmp_name']);
				return false;
			}
			
			// 检查是否合法上传
			if (!$this->checkUpload($file['tmp_name']))
			{
				@unlink($file['tmp_name']);
				return false;
			}
			
			// 上传文件
			$_path = $this->save($file);
			
			if (false === $_path)
				return false;
			
			$this->_path[] = $_path;
			unset($_path);
		}
		
		return true;
	}

	/**
	 * 存储
	 *
	 * @param	string 文件保存路径
	 * @return	bool
	 */
	private function save($file)
	{
		// 生成存储路径
		$file['savepath'] = $this->getDirectory($file['savepath']);
		$file_path = $file['savepath'] . $file['savename'] . '.' . $file['extension'];
		
		if (!move_uploaded_file($file['tmp_name'], $file_path))
		{
			trigger_error('文件保存时发生错误', E_USER_ERROR);
			return false;
		}
		
		@chmod($file_path, 0777);
		
		return $file_path;
	}

	/**
	 * 格式化FIELS数组
	 *
	 * @param	array FILES数组
	 * @return	array 格式化后的数组
	 */
	private function dealFiles($files)
	{
		$fileArray = array();
		
		if (isset($files['name']) && is_string($files['name']))
		{
			$_files[] = $files;
			$files = $_files;
		}
		
		foreach ( $files as $file )
		{
			if (is_array($file['name']))
			{
				$keys = array_keys($file);
				$count = count($file['name']);
				
				for ($i = 0; $i < $count; $i++)
					foreach ( $keys as $key )
						$fileArray[$i][$key] = $file[$key][$i];
			}
			else
			{
				$fileArray = $files;
			}
		}
		
		return $fileArray;
	}

	/**
	 * 检查扩展名
	 *
	 * @param	string 文件扩展名
	 * @param	string 允许的扩展名列表
	 * 列表格式为'jpg,gif,exe'
	 * @return	bool
	 */
	private function checkExt($ext, $extension)
	{
		if (!empty($extension))
		{
			$extension = str_replace(' ', '', $extension);
			$extension = explode(',', $extension);
			
			if (!in_array($ext, $extension))
			{
				trigger_error('不允许上传[' . $ext . ']格式文件', E_USER_ERROR);
				return $this->setError('不允许上传[' . $ext . ']格式文件');
			}
		}
		
		return true;
	}

	/**
	 * 检查文件大小
	 *
	 * @param	integer 文件大小
	 * @param	integer 文件大小上限值
	 * @return	bool
	 */
	private function checkSize($size, $maxSize)
	{
		if (!empty($maxSize))
		{
			if ($size > $maxSize)
			{
				trigger_error('文件大小不允许超过' . ceil($maxSize / 1024) . ' KB', E_USER_ERROR);
				return $this->setError('文件大小不允许超过' . ceil($maxSize / 1024) . ' KB');
			}
		}
		
		return true;
	}

	/**
	 * 检查非法提交
	 *
	 * @param	string 文件名
	 * @return	bool
	 */
	private function checkUpload($filename)
	{
		if (!is_uploaded_file($filename))
		{
			trigger_error('不正确的上传方式', E_USER_ERROR);
			return $this->setError('不正确的上传方式');
		}
		
		return true;
	}

	/**
	 * 获取上传后的文件名
	 *
	 * @param	string 文件名
	 * @param	string 命名规则
	 * @return string 新文件名
	 */
	private function getSaveName($fileName, $fileNameRules)
	{
		if(!empty($fileNameRules))
		{
			switch (strtolower($fileNameRules))
			{
				case 'date' :
					$fileName = date('YmdHis') . '_' . rand(100, 999);
					break;
				case 'md5' :
					$fileName = md5(rand(100, 999) . time());
					break;
				default :
					if (!empty($fileNameRules))
						$fileName = $fileNameRules;
					break;
			}
		}else{
			$fileName = substr($fileName, 0, strrpos($fileName, '.'));
		}
		return $fileName;
	}

	/**
	 * 获取上传文件后缀
	 *
	 * @param	string 文件名
	 * @return string 后缀
	 */
	private function getExtension($filename)
	{
		$pathinfo = pathinfo($filename);
		return $pathinfo['extension'];
	}

	/**
	 * 获取存储路径
	 *
	 * @param	string 存储路径
	 * @return string 新的存储路径
	 */
	private function getDirectory($savePath)
	{
		if (!is_dir($savePath))
		{
			if (!mkdir($savePath, 0777, true))
			{
				trigger_error('目录' . $savePath . '不存在且无法创建', E_USER_ERROR);
				return $this->setError('目录' . $savePath . '不存在且无法创建');
			}
		}
		
		if (!is_writable($savePath))
		{
			trigger_error('目录' . $savePath . '无法写入', E_USER_ERROR);
			return $this->setError('目录' . $savePath . '无法写入');
		}
		
		if (substr($savePath, -1) != "/")
		{
			$savePath .= "/";
		}
		
		return $savePath;
	}

	/**
	 * 返回错误代码说明
	 *
	 * @param	integer 错误代码
	 * @return	bool
	 */
	private function getErrorCode($errorNo)
	{
		switch ($errorNo)
		{
			case 0 :
				return true;
				break;
			case 1 :
				trigger_error('上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值', E_USER_ERROR);
				return $this->setError('上传的文件超过了限制的大小无法上传');
				break;
			case 2 :
				trigger_error('上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值', E_USER_ERROR);
				return $this->setError('上传的文件超过了限制的大小无法上传');
				break;
			case 3 :
				trigger_error('文件只有部分被上传', E_USER_ERROR);
				return $this->setError('文件只有部分被上传');
				break;
			case 4 :
				trigger_error('没有文件被上传', E_USER_ERROR);
				return $this->setError('没有文件被上传');
				break;
			case 6 :
				trigger_error('找不到临时文件夹', E_USER_ERROR);
				return $this->setError('找不到临时文件夹');
				break;
			case 7 :
				trigger_error('文件写入失败', E_USER_ERROR);
				return $this->setError('文件写入失败');
				break;
			default :
				trigger_error('未知上传错误', E_USER_ERROR);
				return $this->setError('未知上传错误');
		}
		
		return true;
	}

	/**
	 * 返回路径
	 * 
	 * 返回上传后的文件路径信息
	 *
	 * @return	array
	 */
	public function getPath()
	{
		return count($this->_path) == 1 ? $this->_path[0] : $this->_path;
	}
}