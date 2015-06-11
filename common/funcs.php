<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * 
 * @copyright(c) 2013-11-19
 * @author msi
 * @version Id:funcs.php
 */


if (!function_exists('debug')) {

    function debug($obj)
    {
        header('Content-Type:text/html;charset=utf-8');
        echo '<pre />';
        var_dump($obj);exit;
    }

}

function is_url($url){  
    if (filter_var ($url, FILTER_VALIDATE_URL)) {  
        return true;  
    } else {  
        return false;  
    }  
}


/**
 * 解析文件名
 * @param string $source_image
 * @return array 文件名+后缀
 */
function explode_name($source_image)
{
    $ext = strrchr($source_image, '.');
    $name = ($ext === FALSE) ? $source_image : substr($source_image, 0, -strlen($ext));

    return array('ext' => $ext, 'name' => $name);
}



/**
 * 拼装图片的显示路径
 * @param string $type commodity/activity/firm
 * @param string $filename 文件名称根据MD5计算路径
 * @param bool  $isimg 是否返回完整图片地址
 * @param string $default_img_name 默认图片的名字
 * $is_wx  微信发送的用户消息
 * $opt array(高，宽)
 */
function get_img_url($filename, $type, $isimg = 0, $default_img_name = 'pic_005.png',$is_wx = 0,$opt=array())
{
    $host = 'http://' . $_SERVER['HTTP_HOST'];
    if (!empty($type) && !empty($filename)) {
        if(is_url($filename)){
            return $filename;
        }
        $dir = '';
        if ($is_wx) {
            $path = '/attachment/' . $type . '/';
        } else {
            $path = '/attachment/business/' . $type . '/';
        }
        $cdir = str_split(strtolower($filename), 1);
        $tmp = array_chunk($cdir, 4);
        if ($tmp[0]) {
            $dir = implode('/', $tmp[0]);
        }

        $new_file = $path . $dir. '/';
        $path .= $dir . '/' . $filename;

        $bd_path = DOCUMENT_ROOT . $path;
        $new_path = '';
        if($opt){
            $base_name = explode_name(basename($bd_path));
            $file_name = $base_name['name'].'-'.$opt[0].'x'.$opt[1].$base_name['ext'];
            $dir_name = dirname($bd_path).'/';
            $new_path = $dir_name . $file_name;
            $new_file .= $file_name;
        }
        if (file_exists($bd_path) && !$opt) {
            if ($isimg) {
                return $bd_path;
            }
            return $host . $path;
        }elseif (file_exists($new_path)) {
            if ($isimg) {
                return $new_path;
            }
            return $host . $new_file;
        }else{
            if($opt && file_exists($bd_path)){
                if(!file_exists($new_path)){
//                    $ci = &get_instance();
//                    $ci->load->model('picture_model', 'picture');
//                    //裁剪并生成缩略图
//                    $ci->picture->crop(array('file_path' => $bd_path, 'width' => array($opt[1]), 'height' =>  array($opt[0]), 'x' => 0, 'y' => 0),array($opt[0],$opt[1]));
                    //生成缩略图并填充白色背景
                    resize($bd_path,$opt[0],$opt[1]);
                }
                return $host . $new_file;
            }
            return $host . '/attachment/defaultimg/' . $default_img_name; //返回个默认图片的路径
        }
    }else{
        return $host . '/attachment/defaultimg/' . $default_img_name; //返回个默认图片的路径
    }
}

//过滤返回匹配表情
function replace_emoticons($content)
{
    $content = str_replace('/::)',
        '<img src="/biz/media/kindeditor/plugins/emoticons/images/0.gif" border="0" />',
        $content);
    $content = str_replace('/::~',
        '<img src="/biz/media/kindeditor/plugins/emoticons/images/1.gif" border="0" />',
        $content);
    $content = str_replace('/::B',
        '<img src="/biz/media/kindeditor/plugins/emoticons/images/2.gif" border="0" />',
        $content);
    $content = str_replace('/::|',
        '<img src="/biz/media/kindeditor/plugins/emoticons/images/3.gif" border="0" />',
        $content);
    $content = str_replace('/:8-)',
        '<img src="/biz/media/kindeditor/plugins/emoticons/images/4.gif" border="0" />',
        $content);
    $content = str_replace('/::<',
        '<img src="/biz/media/kindeditor/plugins/emoticons/images/5.gif" border="0" />',
        $content);
    return $content;
}


/**
 * 处理页面上手机号码的显示
 * @param string $str
 */
function view_phone($str)
{

    if (!$str || strlen($str) != 11) {
        return $str;
    } else {
        return substr($str, 0, 3) . '*****' . substr($str, 7);
    }
}


/**
 * 截取编辑器中文等字符串
 * @param unknown $str
 */
function truncate_utf8($str, $length = 20, $type = 1)
{
    $return = '';
    $str = html_entity_decode($str, ENT_COMPAT, 'utf-8');
    if ($type == 2)
        $str = strip_tags($str, '<img/>');
    else
        $str = strip_tags($str);

    if (function_exists('iconv')) {
        $return = mb_substr($str, 0, $length, 'utf-8');
    }
    return $return;

}


function csubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true)
{

    if (function_exists("mb_substr")) {

        if (mb_strlen($str, $charset) <= $length)
            return $str;

        $slice = mb_substr($str, $start, $length, $charset);

    } else {

        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";

        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";

        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";

        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";

        preg_match_all($re[$charset], $str, $match);

        if (count($match[0]) <= $length)
            return $str;

        $slice = join("", array_slice($match[0], $start, $length));

    }

    if ($suffix)
        return $slice . "…";

    return $slice;

}


/**
 * 生成卡号
 * @param number $min
 * @param number $max
 * @param number $count
 * @return string
 */
function get_member_card($min = 5, $max = 8)
{
    $length = rand($min, $max);
    $card = '';
    for ($i = 0; $i < $length; $i++) {
        $card .= rand(0, 9);
    }
    return $card;
}


function sprint_member_card($str, $length = 8)
{
    return str_pad($str, $length, '0', STR_PAD_LEFT);
}


function request_curl($url, $jsonData = '', $force = 0)
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    $ssl = substr($url, 0, 8) == "https://" ? true : false;
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
    if ($ssl && $force) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 检查证书中是否设置域名
    } else {
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //不输出内容
    if (!empty($jsonData)) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    }

    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}


function print_excel($filename, $data = '')
{
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/vnd.ms-execl; charset=UTF-8");
    header("Content-Type: application/force-download");
    header("Content-Type: application/download");
    header("Content-Disposition: attachment; filename=" . iconv('utf-8', 'gbk', $filename) .
        ".xls");
    header("Content-Transfer-Encoding: binary");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo iconv('utf-8', 'gbk', $data);
}


/**
 * 返回客户端信息通用函数
 * @param number $status 返回状态
 * @param string $data	包含的数据
 * @param string $msg	状态说明
 */
function return_client($status = 0, $data = null, $msg = null)
{

    global $starttime;
    $resp = array(
        'status' => $status,
        'data' => empty($data) ? null : $data,
        'msg' => empty($msg) ? null : $msg,
        'time' => microtime(true) - $starttime);
    $json = json_encode($resp);
    die($json);
}


/**
 * Get Smile Array
 *
 * Fetches the config/smile.php file
 *
 * @access	private
 * @return	mixed
 */
if (!function_exists('_get_smile_array')) {
    function _get_smile_array()
    {
        if (defined('ENVIRONMENT') and file_exists(APPPATH . 'config/' . ENVIRONMENT .
            '/smile.php')) {
            include (APPPATH . 'config/' . ENVIRONMENT . '/smile.php');
        } elseif (file_exists(APPPATH . 'config/smile.php')) {
            include (APPPATH . 'config/smile.php');
        }

        if (isset($smile) and is_array($smile)) {
            return $smile;
        }

        return false;
    }
}

/**
 * GET pay config
 * 
 */

if (!function_exists('_get_pay_config')) {
    function _get_pay_config()
    {
        if (defined('ENVIRONMENT') and file_exists(APPPATH . 'config/' . ENVIRONMENT .'/pay.php')) {
            include (APPPATH . 'config/' . ENVIRONMENT . '/pay.php');
        } elseif (file_exists(APPPATH . 'config/pay.php')) {
            include (APPPATH . 'config/pay.php');
        }

        if (isset($pay) and is_array($pay)) {
            $config = array();
            foreach($pay as $key=>$vals){
                if($key == ENVIRONMENT){
                    foreach($vals as $item=>$val){
                        $config[$item]=$val;
                    }
                }
            }
            return $config;
        }
        return false;
    }
}


/**
 * 获取上n周的开始和结束，每周从周一开始，周日结束日期
 * @param int $ts 时间戳
 * @param int $n 你懂的(前多少周)
 * @param string $format 默认为'%Y-%m-%d',比如"2012-12-18"
 * @return array 第一个元素为开始日期，第二个元素为结束日期
 */
function lastNWeek($currentTime, $n, $format = '%Y-%m-%d')
{
    $ts = intval($currentTime);
    $n = abs(intval($n));

    // 周一到周日分别为1-7
    $dayOfWeek = date('w', $currentTime);
    if (0 == $dayOfWeek) {
        $dayOfWeek = 7;
    }

    $lastNMonday = 7 * $n + $dayOfWeek - 1;
    $lastNSunday = 7 * ($n - 1) + $dayOfWeek;
    return array('mon' => strftime($format, strtotime("-{$lastNMonday} day", $currentTime)),
            'sun' => strftime($format, strtotime("-{$lastNSunday} day", $currentTime)));
}

/**
 * 生成订单号码
 */

function create_order_id()
{
    $d = date('ymd', time());
    //return $length = rand(1000000000, 9999999999);
    $rand='';
    for ($i = 0; $i < 10; $i++) {
        $rand .= mt_rand(0, 9);
    }
    return $d . $rand;
}

/**
 * guid
 */
function create_guid()
{
    $charid = strtoupper(md5(uniqid(mt_rand(), true)));
    $hyphen = chr(45); // "-"
    $uuid = chr(123) // "{"
        . substr($charid, 0, 8) . $hyphen . substr($charid, 8, 4) . $hyphen . substr($charid,
        12, 4) . $hyphen . substr($charid, 16, 4) . $hyphen . substr($charid, 20, 12) .
        chr(125); // "}"
    return $uuid;
}


function isMobile()
{
    $useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] :'';
    $useragent_commentsblock = preg_match('|\(.*?\)|', $useragent, $matches) > 0 ? $matches[0] :'';
    $mobile_os_list = array(
        'Google Wireless Transcoder',
        'Windows CE',
        'WindowsCE',
        'Symbian',
        'Android',
        'armv6l',
        'armv5',
        'Mobile',
        'CentOS',
        'mowser',
        'AvantGo',
        'Opera Mobi',
        'J2ME/MIDP',
        'Smartphone',
        'Go.Web',
        'Palm',
        'iPAQ');
    $mobile_token_list = array(
        'Profile/MIDP',
        'Configuration/CLDC-',
        '160×160',
        '176×220',
        '240×240',
        '240×320',
        '320×240',
        'UP.Browser',
        'UP.Link',
        'SymbianOS',
        'PalmOS',
        'PocketPC',
        'SonyEricsson',
        'Nokia',
        'BlackBerry',
        'Vodafone',
        'BenQ',
        'Novarra-Vision',
        'Iris',
        'NetFront',
        'HTC_',
        'Xda_',
        'SAMSUNG-SGH',
        'Wapaka',
        'DoCoMo',
        'iPhone',
        'iPod');

    $found_mobile = CheckSubstrs($mobile_os_list, $useragent_commentsblock) || CheckSubstrs($mobile_token_list, $useragent);
    if ($found_mobile) {
        return true;
    } else {
        return false;
    }
}
/**
 * GET pay mode
 * @支付类型
 */

if (!function_exists('_get_pay_mode')) {
    function _get_pay_mode()
    {
        //PC端支付
        $payMode = 'alipay';//支付方式
        if(isMobile()){
            //手机wap支付
            $payMode = 'wapalipay';//支付方式
        }
        return $payMode;
    }
}



function CheckSubstrs($substrs, $text)
{
    foreach ($substrs as $substr)
        if (false !== strpos($text, $substr)) {
            return true;
        }
    return false;
}


function createimg($length=4,$width=80,$height=34){
	$chars='ABCDEFGHIJKMNPQRSTUVWXYZ23456789';
    $str = '';
	for($i=0;$i<$length;$i++){
	  $str.= mb_substr($chars, floor(mt_rand(0,mb_strlen($chars)-1)),1);
	}
	$randval = $str;
	$_SESSION['hipigo_verify']= md5(strtolower($randval));
    //setcookie('hipigo_verify', strtolower($randval), time() + (60), '/', '');
	$width = ($length*10+10)>$width?$length*10+10:$width;
	if (function_exists('imagecreatetruecolor')) {
		$im = @imagecreatetruecolor($width,$height);
	}else {
		$im = @imagecreate($width,$height);
	}
	$r = Array(225,255,255);
	$g = Array(225,255,255,0);
	$b = Array(225,236,166,125);
	$key = mt_rand(0,3);
	
	$backColor = imagecolorallocate($im, 244, 244, 244);
	$pointColor = imagecolorallocate($im,244, 244, 244);
	@imagefilledrectangle($im, 0, 0, $width - 1, $height - 1, $backColor);
	@imagerectangle($im, 0, 0, $width-1, $height-1);
	$stringColor = imagecolorallocate($im,mt_rand(0,200),mt_rand(0,120),mt_rand(0,120));
	for($i=0;$i<10;$i++){
		$fontcolor=imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
		imagearc($im,mt_rand(-10,$width),mt_rand(-10,$height),mt_rand(80,300),mt_rand(80,200),55,44,$fontcolor);
	}
    
	for($i=0;$i<25;$i++){
		$fontcolor=imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
		imagesetpixel($im,mt_rand(0,$width),mt_rand(0,$height),$pointColor);
	}
	for($i=0;$i<$length;$i++) {
		imagestring($im,5,$i*10+20,mt_rand(5,15),$randval{$i}, $stringColor);
	}
	header("Content-type: image/png");
	ImagePNG($im);
	ImageDestroy($im);
}


//
function unique_arr($array2D,$stkeep=false,$ndformat=true)
{
    // 判断是否保留一级数组键 (一级数组键可以为非数字)
    if($stkeep) $stArr = array_keys($array2D);

    // 判断是否保留二级数组键 (所有二级数组键必须相同)
    if($ndformat) $ndArr = array_keys(end($array2D));

    //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
    foreach ($array2D as $v){
        $v = join(",",$v);
        $temp[] = $v;
    }
    //去掉重复的字符串,也就是重复的一维数组
    $temp = array_unique($temp);

    //再将拆开的数组重新组装
    foreach ($temp as $k => $v)
    {
        if($stkeep) $k = $stArr[$k];
        if($ndformat)
        {
            $tempArr = explode(",",$v);
            foreach($tempArr as $ndkey => $ndval)
            {
                $output[$k][$ndArr[$ndkey]] = $ndval;
            }
        }
        else {
            $output[$k] = explode(",",$v);
        }
    }

    return $output;
}

/**
 * 重写URL
 *
 * 使用方法 <!--{rewrite url='community/home'}-->
 *
 * @author Jamai
 * @version 2.1
 *
 **/
function rewrite($params, $sysParams) {

  $url = explode('/', $params['url']);
  list($controller, $method) = $url;
}



function getImageInfo($src)
{
    return getimagesize($src);
}
/**
 * 创建图片，返回资源类型
 * @param string $src 图片路径
 * @return resource $im 返回资源类型
 * **/
function create($src)
{
    $info=getImageInfo($src);
    switch ($info[2])
    {
        case 1:
            $im=imagecreatefromgif($src);
            break;
        case 2:
            $im=imagecreatefromjpeg($src);
            break;
        case 3:
            $im=imagecreatefrompng($src);
            break;
    }
    return $im;
}
/**
 * 缩略图主函数
 * @param string $src 图片路径
 * @param int $w 缩略图宽度
 * @param int $h 缩略图高度
 * $is_cut  是否裁减  0：压缩  1：裁减
 * $img_t  图片分类  （big,small）
 * @return mixed 返回缩略图路径
 * **/

function resize($src,$w,$h,$is_cut=0,$img_t='big')
{
    $temp=pathinfo($src);
    $name=$temp["filename"];//文件名
    $dir=$temp["dirname"];//文件所在的文件夹
    $extension=$temp["extension"];//文件扩展名
    $savepath="{$dir}/{$name}-{$w}x{$h}.{$extension}";//缩略图保存路径,新的文件名为*.thumb.jpg

    //获取图片的基本信息
    $info=getImageInfo($src);
    $width=$info[0];//获取图片宽度
    $height=$info[1];//获取图片高度

    if($is_cut){
//        $ci = &get_instance();
//        $ci->load->model('picture_model', 'picture');
//        //裁剪并生成缩略图
//        $ci->picture->crop(array('file_path' => $src, 'width' => array($w), 'height' =>  array($h), 'x' => 0, 'y' => 0),array($w,$h));
        $save_path = "{$dir}/{$name}-$img_t.{$extension}";
        cut_img($src,$h,$w,$save_path);
    }else{
        $per1=round($width/$height,2);//计算原图长宽比
        $per2=round($w/$h,2);//计算缩略图长宽比

        //计算缩放比例
        if($per1>$per2||$per1==$per2)
        {
            //原图长宽比大于或者等于缩略图长宽比，则按照宽度优先
            $per=$w/$width;
        }
        if($per1<$per2)
        {
            //原图长宽比小于缩略图长宽比，则按照高度优先
            $per=$h/$height;
        }
        $temp_w=intval($width*$per);//计算原图缩放后的宽度
        $temp_h=intval($height*$per);//计算原图缩放后的高度
        $temp_img=imagecreatetruecolor($temp_w,$temp_h);//创建画布
        $im=create($src);
        imagecopyresampled($temp_img,$im,0,0,0,0,$temp_w,$temp_h,$width,$height);
        if($per1>$per2)
        {
            imagejpeg($temp_img,$savepath, 100);
            imagedestroy($im);
            return addBg($savepath,$w,$h,"w");
            //宽度优先，在缩放之后高度不足的情况下补上背景
        }
        if($per1==$per2)
        {
            imagejpeg($temp_img,$savepath, 100);
            imagedestroy($im);
            return $savepath;
            //等比缩放
        }
        if($per1<$per2)
        {
            imagejpeg($temp_img,$savepath, 100);
            imagedestroy($im);
            return addBg($savepath,$w,$h,"h");
            //高度优先，在缩放之后宽度不足的情况下补上背景
        }
    }
}
/**
 * 添加背景
 * @param string $src 图片路径
 * @param int $w 背景图像宽度
 * @param int $h 背景图像高度
 * @param String $first 决定图像最终位置的，w 宽度优先 h 高度优先 wh:等比
 * @return 返回加上背景的图片
 * **/
function addBg($src,$w,$h,$first="w")
{
    $bg=imagecreatetruecolor($w,$h);
    $white = imagecolorallocate($bg,255,255,255);
    imagefill($bg,0,0,$white);//填充背景

    //获取目标图片信息
    $info=getImageInfo($src);
    $width=$info[0];//目标图片宽度
    $height=$info[1];//目标图片高度
    $img=create($src);
    if($first=="wh"){
        //等比缩放
        return $src;
    }
    else {
        if($first=="w")
        {
            $x=0;
            $y=($h-$height)/2;//垂直居中
        }
        if($first=="h")
        {
            $x=($w-$width)/2;//水平居中
            $y=0;
        }
        imagecopymerge($bg,$img,$x,$y,0,0,$width,$height,100);
        imagejpeg($bg,$src,100);
        imagedestroy($bg);
        imagedestroy($img);
        return $src;
    }
}


/**
 * zxx
 * 裁减图片主函数
 * $src_img  图片路径+名字
 * $dst_h  图片高度
 * $dst_w  图片宽度
 * $save_path 保存路径及文件名字
 * */
function cut_img($src_img,$dst_h,$dst_w,$save_path=''){
    $info=getImageInfo($src_img);// 获取原图尺寸
    $src_w=$info[0];//获取图片宽度
    $src_h=$info[1];//获取图片高度
    $dst_scale = $dst_h/$dst_w; //目标图像长宽比
    $src_scale = $src_h/$src_w; // 原图长宽比

    if($src_scale>=$dst_scale)
    {
        // 过高
        $w = intval($src_w);
        $h = intval($dst_scale*$w);
        $x = 0;
        $y = ($src_h - $h)/3;
    }
    else
    {
        // 过宽
        $h = intval($src_h);
        $w = intval($h/$dst_scale);
        $x = ($src_w - $w)/2;
        $y = 0;
    }
    // 剪裁
//    $source=imagecreatefromjpeg($src_img);
    $source=create($src_img);
    $croped=imagecreatetruecolor($w, $h);
    imagecopy($croped,$source,0,0,$x,$y,$src_w,$src_h);
    // 缩放
    $scale = $dst_w/$w;
    $target = imagecreatetruecolor($dst_w, $dst_h);
    $final_w = intval($w*$scale);
    $final_h = intval($h*$scale);
    imagecopyresampled($target,$croped,0,0,0,0,$final_w,$final_h,$w,$h);
    // 保存
//    $timestamp = time();
    imagejpeg($target, $save_path);
    imagedestroy($target);
}
/* End of file funcs.php */
