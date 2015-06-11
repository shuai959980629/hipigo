<?php
/**
 * @copyright(c) 2014-04-21
 * @author vikie
 * 辅助类公共方法(此类所有方法都不操作数据库)
 *  
 */
class Helper_Model extends CI_Model
{ 
 
 
/**
 * 格式化日期时间 格式类似新浪微博
 *  @param	date   时间
 *  @return	string 时间字符串
 */
public function format_date($date)
{
	if(! $date)
		return FALSE;

	$time_now = time();
	$date_now = date('Y-m-d H:i:s', $time_now);
	$date_time = strtotime($date);
	$time_diff = $time_now - $date_time;
	$is_today = date('Y-m-d', $time_now) == date('Y-m-d', $date_time);
	
	if($time_diff < 10)
	{
		return '刚刚';
	}
	if($is_today && $time_diff/60 < 1)
	{
		return $time_diff .'秒前';
	}
	else if($is_today && $time_diff/3600 < 1)
	{
		return (int)($time_diff/60) .'分钟前';
	}
	else if($is_today)
	{
		return '今天 '.date('H:i', $date_time);
	}
	else if(date('Y', $time_now) == date('Y', $date_time))
	{
		return date('n月j日 H:i', $date_time);
	}
	else
	{
		return date('Y-m-d H:i', $date_time);
	}
}

/**
 * 加密
 * 原理：异或加密，将密钥和当前的时间戳经过md5后生成用于打乱密码本的字符串0-255之间，
 * 然后将需要加密的密码与打乱的密码本异或运算，得到密文文件
 *  @param	string 原文
 *  @return	string 密文
 */
public function token_encode($string, $key = '', $expiry = 0) {
    $ckey_length = 4;
    $key = md5($key ? $key : 'vikie');
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ?  substr(md5(microtime()), -$ckey_length) : '';
    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);
    $string = sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    
    return $keyc.str_replace('=', '', base64_encode($result));
	
 }


/**
 * 解密
 * 原理：密文文件中保存有用来打乱密码本的时间戳，取出后和密钥生成加密时候的密码本，
 * 然后将需要解密的密文与打乱的密码本异或运算，得到原密码
 * @param	string 密文
 * @return	string 原文
 */
function token_decode($string,$key = '', $expiry = 0) {
    $ckey_length = 4;
    $key = md5($key ? $key : 'vikie');
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? substr($string, 0, $ckey_length) : '';
    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);
    $string = base64_decode(substr($string, $ckey_length));
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    
  }

/**
	 * 目录检测
	 * 测试目录是否存在
	 * //第二个参数为true则自动创建目录，默认为true。
	 * $this->dir('c:\myapp\temp', true)
	 * @endcode
	 * 
	 * @param	string 目录路径
	 * @return	bool
	 */
	public function dir($path, $make = true)
	{
		if (file_exists($path))
		{
			return true;
		}
		else if (false == $make)
		{
			return false;
		}
		
		if(!mkdir($path, 0777, true))
		{
			trigger_error('错误！目录不可写：' . $path, E_USER_ERROR);
			return false;
		}
		
		return true;
	}

	/**
	 * 写操作
	 * 
	 * 写入数据到文件中
	 * @qrcode
	 * $this->write('temp/users.txt', $data);
	 * @endcode
	 * 
	 * @param	string 文件路径
	 * @param	string 数据
	 * @return	bool
	 */
	public function write($file, $data)
	{
		// 检查目录是否存在
		if (!$this->dir(dirname($file)))
		{
			trigger_error('目录不存在', E_USER_ERROR);
			return false;
		}
		
		$fp = fopen($file, 'wb');
		
		// 独占锁
		if (!flock($fp, LOCK_EX | LOCK_NB))
		{
			trigger_error('文件正在被使用', E_USER_ERROR);
			return false;
		}
		
		fwrite($fp, $data);
		
		// 解锁
		flock($fp, LOCK_UN);
		
		// 关闭
		fclose($fp);
		
		// 设置权限
		@chmod($file, 0777);
		
		return true;
	} 

	/**
	 * 读操作
	 * 
	 * 读取文件内容并返回
	 * @qrcode
	 * $this->read('temp/users.txt');
	 * @endcode
	 * 
	 * @param	string 文件路径
	 * @return	mixed 文件内容
	 */
	public function read($file)
	{
		if (!is_file($file))
		{
			trigger_error('文件不存在', E_USER_ERROR);
			return false;
		}
		
		// 打开
		$fp = fopen($file, "rb");
		
		// 共享锁
		if (!flock($fp, LOCK_SH | LOCK_NB))
		{
			trigger_error('文件正在被使用', E_USER_ERROR);
			return false;
		}
		
		$str = fread($fp, filesize($file));
		
		// 解锁
		flock($fp, LOCK_UN);
		
		// 关闭
		fclose($fp);
		
		return $str;
	} 

	/**
	 * 变量写操作
	 * 
	 * 写入变量到文件中
	 * 此函数将使用json方式格式数据并以php注释方式存为yourfile.var.php文件
	 * @qrcode
	 * $this->writeVar('temp/config', array('title' => 'MyPage'));
	 * // save as temp/config.var.php
	 * @endcode
	 * 
	 * @param	string 文件路径
	 * @param	mixed 数据
	 * @return	bool
	 */
	public function writeVar($file, $data)
	{
		$var = null;
		$var .= '<?php /*';
		$var .= json_encode($data);
		$var .= '*/ ?>';
		$file .= '.var.php';
		unset($data);
		
		return $this->write($file, $var);
	} 

	/**
	 * 变量读操作
	 * 
	 * 读取文件内容并以变量返回
	 * @qrcode
	 * $this->readVar('temp/config');
	 * // read the file temp/config.var.php
	 * @endcode
	 * 
	 * @param	string 文件路径
	 * @return	array
	 */
	public function readVar($file)
	{
		$file .= '.var.php';
		$data = $this->read($file);
		$data = substr($data, 8, -5);
		return json_decode($data, true);
	}
	
	/**
	 * 检查变量文件是否存在
	 * 
	 * @qrcode
	 * $this->isVal('temp/config.var.php')
	 * @endcode
	 * 
	 * @param	string 文件路径（不包含扩展名[.var.php]）
	 * @return	bool
	 */
	public function isVar($file)
	{
		return is_file($file.'.var.php');
	}



//    /**
//     * zxx
//     * 请求地址方法
//     * @param $curlPost  请求的参数
//     * @param $url  发送的服务地址
//     * @return mixed
//     */
//    function Post($curlPost,$url){
//        $curl = curl_init();
//        curl_setopt($curl, CURLOPT_URL, $url);
//        curl_setopt($curl, CURLOPT_HEADER, false);
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($curl, CURLOPT_NOBODY, true);
//        curl_setopt($curl, CURLOPT_POST, true);
//        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
//        $return_str = curl_exec($curl);
//        curl_close($curl);
//        return $return_str;
//    }
//
//    /**
//     * zxx
//     * 过滤标签
//     * @param $xml 过滤的返回标签
//     * @return mixed
//     */
//    function xml_to_array($xml){
//        $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
//        if(preg_match_all($reg, $xml, $matches)){
//            $count = count($matches[0]);
//            for($i = 0; $i < $count; $i++){
//                $subxml= $matches[2][$i];
//                $key = $matches[1][$i];
//                if(preg_match( $reg, $subxml )){
//                    $arr[$key] = $this->xml_to_array( $subxml );
//                }else{
//                    $arr[$key] = $subxml;
//                }
//            }
//        }
//        return $arr;
//    }


    /**
     * zxx
     * 发送手机短信
     *  $phone   手机号码
     */
    function send_code($phone){
//        $mobile_code = $this->random(6,1);
//        $_SESSION['mobile_code'] = $mobile_code;
//        $target = "http://106.ihuyi.cn/webservice/sms.php?method=Submit";
//        $post_data = "account=cf_xrenwu&password=xrenwu123&mobile=".$phone."&content=".rawurlencode("您的验证码是：".$mobile_code."。请不要把验证码泄露给其他人。");
//        //密码可以使用明文密码或使用32位MD5加密
//        $gets =  $this->xml_to_array($this->Post($post_data, $target));
//
//        return $gets;
    }


}