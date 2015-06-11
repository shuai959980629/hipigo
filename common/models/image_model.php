<?php
/**
 * @copyright(c) 2014-04-21
 * @author vikie
 * 图像处理
 * 读取输出图像、创建图像、水印文字与图像、旋转、锐化、缩放图像。
 * 依赖PHP GD库 
 */
class Image_Model extends CI_Model
{
	// 源图像路径
	private $_src;
	
	// 源图像句柄
	private $_srcIm;
	
	// 源图像宽度
	private $_srcWidth;
	
	// 源图像高度
	private $_srcHeight;
	
	// 源图像类型
	private $_srcType;

	/**
	 * 加载图像
	 * 从源图创建新图像时，提供图像加载
	 * 加载成功返回true，并创建图像句柄、宽、高和图像类型，否则返回false
	 * 
	 * @qrcode
	 * $imgSrc = dirname(__FILE__) . '/img/test.jpg';
	 * //加载指定的源图像
	 * $this->load($imgSrc);
	 * $this->render();
	 * @endcode
	 * 
	 * @param	string 图像路径
	 * @return	bool
	 */
	public function load($imageSrc)
	{
		$this->_src = $imageSrc;
		
		// 从源文件创建图像
		$result = $this->createImgFromSourceFile($imageSrc);
		//print_r($result);
		if (!is_array($result) || empty($result))
			return false;
		list($this->_srcIm, $this->_srcWidth, $this->_srcHeight, $this->_srcType) = $result;
		unset($result);
		return true;
	}

	/**
	 * 创建图像
	 * 创建空白画布
	 * 
	 * @qrcode
	 * //创建一个指定背景色为黑色，宽:300px，高:200px的png类型的空白画布
	 * $this->create(300, 200, 'png', array(0, 0, 0));
	 * $this->render();
	 * @endcode
	 * 
	 * @param	integer 宽度
	 * @param	integer 高度
	 * @param	string  图片类型 [jpg|png|gif]
	 * @param	array   背景色,默认随机，其RGB数组格式如：array(255, 255, 255)
	 * @return	bool
	 */
	public function create($width, $height, $type = 'gif', array $color = array())
	{
		$this->_src = 'create_temp.' . $type;
		$this->_srcWidth = $width;
		$this->_srcHeight = $height;
		$this->_srcIm = $this->createTargetImgIdentifier($width, $height);
		
		if (empty($color))
		{
			$color = array(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
			
			imagefill($this->_srcIm, 0, 0, imagecolorallocate($this->_srcIm, $color[0], $color[1], $color[2]));
			
			for ($i = 0; $i < 3; $i++)
				imagerectangle($this->_srcIm, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), imagecolorallocate($this->_srcIm, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)));
			
			for ($i = 0; $i < 50; $i++)
				imagesetpixel($this->_srcIm, mt_rand(0, $width), mt_rand(0, $height), imagecolorallocate($this->_srcIm, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)));
		}
		else
		{
			imagefill($this->_srcIm, 0, 0, imagecolorallocate($this->_srcIm, $color[0], $color[1], $color[2]));
		}

		return true;
	}

	/**
	 * 通过源文件创建新图像
	 * 
	 * @param	string 图像路径
	 * @return	array 返回图像资源与图像信息
	 */
	private function createImgFromSourceFile($src)
	{
		if (!is_file($src) || !file_exists($src) || !is_readable($src))
		{
			
			trigger_error('无法读取源文件[' . $src . ']', E_USER_ERROR);
			return false;
		}
		elseif (!function_exists('imagecreatefromgif') || !function_exists('imagecreatefromjpeg') || !function_exists('imagecreatefrompng'))
		{   
			trigger_error('图像处理函数无法工作请检查GD库', E_USER_ERROR);
			return false;
		}
		elseif (!list($sourceImageWidth, $sourceImageHeight, $sourceImageType) = getimagesize($src))
		{
			  
			trigger_error('无法读取图像信息[' . $src . ']', E_USER_ERROR);
			return false;
		}
		else
		{
			// 检查扩展名
			switch ($sourceImageType)
			{
				// gif
				case 1 :
					
					// 获取GIF透明色
					$fp = fopen($src, "rb");
					$result = fread($fp, 13);
					$colorFlag = ord(substr($result, 10, 1)) >> 7;
					$background = ord(substr($result, 11));
					
					if ($colorFlag)
					{
						$tableSizeNeeded = ($background + 1) * 3;
						$result = fread($fp, $tableSizeNeeded);
						$this->transparentColorRed = ord(substr($result, $background * 3, 1));
						$this->transparentColorGreen = ord(substr($result, $background * 3 + 1, 1));
						$this->transparentColorBlue = ord(substr($result, $background * 3 + 2, 1));
					}
					
					fclose($fp);
					
					$sourceImageIdentifier = imagecreatefromgif($src);
					break;
				
				// jpg
				case 2 :
					$sourceImageIdentifier = imagecreatefromjpeg($src);
					break;
				
				// png
				case 3 :
					$sourceImageIdentifier = imagecreatefrompng($src);
					break;
				
				default :
					/*$error = "不支持的文件格式[$src]";
					$this->setError($error);
					return false;*/
					exit();
			}
		
		}
		
		// 返回图像资源与图像信息
		return array($sourceImageIdentifier, $sourceImageWidth, $sourceImageHeight, $sourceImageType);
	
	}

	/**
	 * 创建空白图像
	 *
	 */
	private function createTargetImgIdentifier($width, $height)
	{
		if (!function_exists('imagecreatetruecolor'))
			return $this->setError('Server not support image library');
		
		// 创建空白图像
		$targetImageIdentifier = imagecreatetruecolor((int)$width <= 0 ? 1 : (int)$width, (int)$height <= 0 ? 1 : (int)$height);
		
		// 返回图像标示符
		return $targetImageIdentifier;
	
	}

	/**
	 * 输出图像
	 * 
	 * @param	string  图像存储路径
	 * @param	integer 图像质量
	 * @param	bool    是否立即输出到浏览器
	 */
	private function outputTargetImg($target, $quality = 100, $render = false)
	{
		if (!is_resource($this->_srcIm))
		{
			/*$error = "未加载图像";
			$this->setError($error);
			return false;*/
			exit();
		}
		
		// 获取目标文件扩展名
		$type = strtolower(substr($target, strrpos($target, ".") + 1));
		
		if (!in_array($type, array('gif', 'jpg', 'jpeg', 'png')))
		{
			/*$error = "不支持的文件扩展名[$type]";
			$this->setError($error);
			return false;*/
			exit();
		}
		
		$type = ('jpg' == $type) ? 'jpeg' : $type;
		
		$imgFun = 'image' . $type;
		
		if (!function_exists($imgFun))
		{
			/*$error = "不支持的文件扩展名[$type]";
			$this->setError($error);
			return false;*/
			exit();
		}
		
		if (true === $render)
		{
			header('Content-type: image/' . $type);
			$imgFun($this->_srcIm);
		}
		else
		{
			$quality = min(100, max($quality, 1));
			//修正PNG图片压缩质量BUG
			if($imgFun=='imagepng'){
			$quality = ceil($quality/10);
			//echo $quality;	
			}
						$imgFun($this->_srcIm, $target, $quality);
			
		}
		
		// 设置文件读写权限
		//chmod($this->targetFile, intval(0755, 8));
		

		// 目标文件时间与源文件相同
		//if ($this->preserveSourceFileTime)
		//@touch($this->targetFile, $this->sourceFileTime);
		

		//imagedestroy($this->_srcIm);
		//$this->_srcIm = NULL;
		//echo $quality;
		
        $this->release();
		return true;
	}

	/**
	 * 图片水印
	 * 
	 * @param    source  源图片标示符
	 * @param    source  水印图片标示符
	 * @param    integer 原图片X坐标
	 * @param    integer 原图片Y坐标
	 * @param    integer 水印图片X坐标
	 * @param    integer 水印图片Y坐标
	 * @param    integer 水印图片宽度
	 * @param    integer 水印图片高度
	 */
	private function createImgFromIdentifier(&$SourceIdentifier, &$targetIdentifier, $x, $y, $s_x, $s_y, $sWidth, $sHeight)
	{
		// 复制图片
		return imagecopy($SourceIdentifier, $targetIdentifier, $x, $y, $s_x, $s_y, $sWidth, $sHeight);
	}

	/**
	 * 添加图片水印
	 * 为指定图片添加水印
	 * $this->create(300, 200, 'jpg', array(125,125,125));
	 * $srcImg = 'test.png';
	 * $this->drawImage($srcImg, 10, 10 , 20 , 20, 50, 50);
	 * $this->render();
	 * 
	 * @param    string 水印图片路径
	 * @param    integer 目标X坐标
	 * @param    integer 目标Y坐标
	 * @param    integer 源X坐标
	 * @param    integer 源Y坐标
	 * @param    integer 源宽度
	 * @param    integer 源高度
	 * @return    bool
	 */
	public function drawImage($imageSrc, $x, $y, $srcX, $srcY, $sWidth, $sHeight)
	{
		// 从源文件创建图像
		$result = $this->createImgFromSourceFile($imageSrc);
		
		if (!is_array($result) || empty($result))
		{
			/*$error = "读取水印图像错误";
			$this->setError($error);
			return false;*/
			exit();
		}
		
		list($srcIm, $srcWidth, $srcHeight) = $result;
		
		$this->createImgFromIdentifier($this->_srcIm, $srcIm, $x, $y, $srcX, $srcY, $sWidth, $sHeight);
		
		imagedestroy($srcIm);
		unset($srcIm);
		
		return true;
	}

	/**
	 * 添加文字水印
	 * 
	 * @qrcode
	 * $this->create(300, 200, 'jpg', array(125,125,125));
	 * $this->drawText('文字水印', 6, 2, 16);
	 * $this->render();
	 * @endcode
	 * 
	 * @param    string  文字
	 * @param    integer 目标X坐标
	 * @param    integer 目标Y坐标
	 * @param    integer 文字大小
	 * @param    array   文字颜色,默认随机，其RGB数组格式如：array(255, 255, 255)
	 */
	public function drawText($_fontFile, $string, $x, $y, $size, array $color = array())
	{
		// 设置颜色
		if (empty($color))
			$color = array(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
		
		$color = imagecolorallocate($this->_srcIm, $color[0], $color[1], $color[2]);
		
		// 写入文字
		imagettftext($this->_srcIm, $size, 0, $x, $y + $size + 4, $color, $_fontFile, $string);
		
		unset($color);
	}

	/**
	 * 锐化图片
	 * 
	 * 锐化计算速度较慢请谨用
	 * 
	 * @qrcode
	 * $this->create(300, 200, 'png', array(125,125,125));
	 * $this->drawText('文字水印', 6, 2, 16);
	 * $this->sharp(1);
	 * $this->render();
	 * @endcode
	 * 
	 * @param    integer 锐化度[0.1-1]
	 * @return    void
	 */
	public function sharp($degree)
	{
		if (!is_resource($this->_srcIm))
		{
			/*$error = "未加载图像";
			$this->setError($error);
			return false;*/
			exit();
		}
		
		$degree = min(1, max($degree, 0.1));
		
		$cnt = 0;
		
		for ($x = 1; $x < $this->_srcWidth; $x++)
			for ($y = 1; $y < $this->_srcHeight; $y++)
			{
				$src_clr1 = imagecolorsforindex($this->_srcIm, imagecolorat($this->_srcIm, $x - 1, $y - 1));
				$src_clr2 = imagecolorsforindex($this->_srcIm, imagecolorat($this->_srcIm, $x, $y));
				$r = intval($src_clr2["red"] + $degree * ($src_clr2["red"] - $src_clr1["red"]));
				$g = intval($src_clr2["green"] + $degree * ($src_clr2["green"] - $src_clr1["green"]));
				$b = intval($src_clr2["blue"] + $degree * ($src_clr2["blue"] - $src_clr1["blue"]));
				$r = min(255, max($r, 0));
				$g = min(255, max($g, 0));
				$b = min(255, max($b, 0));
				
				if (($dst_clr = imagecolorexact($this->_srcIm, $r, $g, $b)) == -1)
					$dst_clr = Imagecolorallocate($this->_srcIm, $r, $g, $b);
				
				$cnt++;
				
				imagesetpixel($this->_srcIm, $x, $y, $dst_clr);
			}
		
		unset($dst_clr);
	}

	/**
	 * 将图片转换为灰度
	 * 
	 * @qrcode
	 * $this->create(300, 200, 'gif', array(125,125,124));
	 * $this->drawText('文字水印', 6, 2, 16);
	 * $this->gray();
	 * $this->render();
	 * @endcode
	 * 
	 * @return    void
	 */
	public function gray()
	{
		if (!is_resource($this->_srcIm))
		{
			/*$error = "未加载图像";
			$this->setError($error);
			return false;*/
			exit();
		}
		
		for ($y = 0; $y < $this->_srcHeight; $y++)
		{
			for ($x = 0; $x < $this->_srcWidth; $x++)
			{
				$gray = (ImageColorAt($this->_srcIm, $x, $y) >> 8) & 0xFF;
				imagesetpixel($this->_srcIm, $x, $y, ImageColorAllocate($this->_srcIm, $gray, $gray, $gray));
			}
		}
	}

	/**
	 * 设置图像大小
	 * 
	 * @qrcode
	 * $this->create(300, 200, 'gif', array(125,125,125));
	 * $this->resize(true, 100); //修改图片宽、高
	 * $this->render();
	 * @endcode
	 * 
	 * @param    bool   是否保持宽高比 [true]
	 * @param    integer 目标宽度
	 * @param    integer 目标高度
	 * @param    integer 目标质量[100][1-100]
	 * @return    bool
	 */
	public function resize($isAspectRatio = true, $width = NULL, $height = NULL, $quality = 100)
	{
		if (!is_resource($this->_srcIm))
		{
			/*$error = "未加载图像";
			$this->setError($error);
			return false;*/
			exit();
		}
		
		$sourceImageWidth = $this->_srcWidth;
		$sourceImageHeight = $this->_srcHeight;
		
		// 是否保持宽高比
		if (true == $isAspectRatio)
		{
			// 计算图像高宽比
			$aspectRatio = $sourceImageWidth <= $sourceImageHeight ? $sourceImageHeight / $sourceImageWidth : $sourceImageWidth / $sourceImageHeight;
			
			$targetImageWidth = $sourceImageWidth;
			$targetImageHeight = $sourceImageHeight;
			
			// 指定宽度
			if (NULL != $width && $width >= 0)
				$lockedTargetImageWidth = $width;
			
			if ((NULL != $width && $width >= 0 && $targetImageWidth > $width) || (NULL != $width && $width >= 0 && $targetImageWidth < $width))
			{
				
				// 目标图像宽度
				$targetImageWidth = $width;
				$targetImageHeight = $sourceImageWidth <= $sourceImageHeight ? $targetImageWidth * $aspectRatio : $targetImageWidth / $aspectRatio;
				$lockedTargetImageWidth = $targetImageWidth;
			
			}
			
			if ((NULL != $height && $height >= 0 && $targetImageHeight > $height) || (NULL != $height && $height >= 0 && $targetImageHeight < $height))
			{
				$targetImageHeight = $height;
				$targetImageWidth = $sourceImageWidth <= $sourceImageHeight ? $targetImageHeight / $aspectRatio : $targetImageHeight * $aspectRatio;
				
				if (isset($lockedTargetImageWidth) && $targetImageWidth > $lockedTargetImageWidth)
				{
					while ( $targetImageWidth > $lockedTargetImageWidth )
					{
						$targetImageHeight--;
						$targetImageWidth = $sourceImageWidth <= $sourceImageHeight ? $targetImageHeight / $aspectRatio : $targetImageHeight * $aspectRatio;
					}
				}
			}
		}
		else
		{
			$targetImageWidth = ($width >= 0) ? $width : $sourceImageWidth;
			$targetImageHeight = ($height >= 0) ? $height : $sourceImageHeight;
		}
		
		// 创建目标图像标示符
		$targetImageIdentifier = $this->createTargetImgIdentifier($targetImageWidth, $targetImageHeight);
		
		// 调整图像大小
		if ($this->_srcType == 3)
		{
			imagealphablending($targetImageIdentifier, false);
			imagecopyresampled($targetImageIdentifier, $this->_srcIm, 0, 0, 0, 0, $targetImageWidth, $targetImageHeight, $sourceImageWidth, $sourceImageHeight);
			imagesavealpha($targetImageIdentifier, true);
		}
		else
		{
			imagecopyresampled($targetImageIdentifier, $this->_srcIm, 0, 0, 0, 0, $targetImageWidth, $targetImageHeight, $sourceImageWidth, $sourceImageHeight);
		}
		
		imagedestroy($this->_srcIm);
		$this->_srcIm = NULL;
		$this->_srcIm = & $targetImageIdentifier;
		
		return true;
	}

	/**
	 * 剪切图像
	 * 
	 * @qrcode
	 * $this->create(300, 200, 'gif', array(125,125,124));
	 * $this->drawText('文字水印', 6, 2, 16);
	 * $this->crop(10,10, 100, 100);
	 * $this->render();
	 * @endcode
	 * 
	 * @param    integer 源X坐标
	 * @param    integer 源Y坐标
	 * @param    integer 源宽度
	 * @param    integer 源高度
	 * @return    bool
	 */
	public function crop($srcX, $srcY, $dstX = NULL, $dstY = NULL)
	{
		if (!is_resource($this->_srcIm))
		{
			/*$error = "未加载图像";
			$this->setError($error);
			return false;*/
			exit();
		}
		
		$srcX = $srcX ? $srcX : 0;
		$srcY = $srcY ? $srcY : 0;
		$dstX = $dstX ? $dstX : $this->_srcWidth;
		$dstY = $dstY ? $dstY : $this->_srcHeight;
		
		// 创建目标图像
		$target = $this->createTargetImgIdentifier($dstX - $srcX, $dstY - $srcY);
		
		// 剪切图像
		imagecopyresampled($target, $this->_srcIm, 0, 0, $srcX, $srcY, $dstX - $srcX, $dstY - $srcY, $dstX - $srcX, $dstY - $srcY);
		
		imagedestroy($this->_srcIm);
		$this->_srcIm = NULL;
		$this->_srcIm = & $target;
		
		return true;
	}

	/**
	 * 旋转图像
	 * 
	 * @qrcode
	 * $this->create(300, 200, 'gif', array(125,125,124));
	 * $this->drawText('文字水印', 6, 2, 16);
	 * $this->rotate(30);
	 * $this->render();
	 * @endcode
	 * 
	 * @warning
	 * 该函数与其他函数混合使用时请将顺序滞后以免发生错误
	 * 
	 * @param    integer 旋转角度[0][0-360]
	 * @param    string  空白区域填充色[0xFFFFFF],旋转时产生的空白区域的颜色，十六进制RGB值。
	 * @return    void
	 */
	public function rotate($angle = 0, $bgColor = '0xFFFFFF')
	{
		if (!is_resource($this->_srcIm))
		{
			/*$error = "未加载图像";
			$this->setError($error);
			return false;*/
			exit();
		}
		
		// 旋转图像
		$target = imagerotate($this->_srcIm, $angle, $bgColor);
		
		imagedestroy($this->_srcIm);
		$this->_srcIm = NULL;
		$this->_srcIm = & $target;
	}

	/**
	 * 保存图像
	 * 
	 * @qrcode
	 * $this->create(300, 200, 'gif', array(125,125,124));
	 * $this->drawText('文字水印', 6, 2, 16);
	 * $this->save('./test.gif');
	 * @endcode
	 * 
	 * @param    string  图像路径
	 * @param    integer 图像质量[100][0-100]
	 * @return    bool
	 */
	public function save($target, $quality = 100)
	{
		  
		return $this->outputTargetImg($target, $quality, false);
	}

	/**
	 * 输出图像
	 * 
	 * @qrcode
	 * $this->create(300, 200, 'gif', array(125,125,124));
	 * $this->drawText('文字水印', 6, 2, 16);
	 * $this->render();
	 * @endcode
	 * 
	 * @param    integer 图像质量[100][0-100]
	 * @return    bool
	 */
	public function render($quality = 100)
	{
		if ($this->outputTargetImg($this->_src, $quality, true))
		{
			$this->release();
			return true;
		}
		
		return false;
	}

	/**
	 * 释放内存
	 */
	protected function release()
	{
		imagedestroy($this->_srcIm);
		$this->_srcIm = NULL;
	}
}