<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Wx_Menu{

//    CONST AppId = 'wx96698ecdd069130c';
//    CONST AppSecret = '380e0b18b7638a8c74092b6544e4d50d';
    //目前自定义菜单最多包括3个一级菜单，
    //每个一级菜单最多包含5个二级菜单。
    //一级菜单最多4个汉字，
    //二级菜单最多7个汉字，
    //多出来的部分将会以“...”代替。

    protected $AppId;
    protected $AppSecret;
    public function __construct($config){
        $this->AppId = $config['appid'];
        $this->AppSecret = $config['appsecret'];
    }

    public function index()
    {
    }

    /*
     * zxx 显示菜单
     * **/
	public function create_menu($sj_data,$bid,$sid)
	{
        $accesstoken = $this->getAccessToken();//'sjSADp9f7bRw-kDWug1sdXhwc3T8G_NLUqjudS88cwTsbR5uuZAouc9Gah_zVpbPclwtW_axl8x1Fj9X1x9jatKepJ6_08TX-ldgDjtgt7BelpFu-zxraZ0qCKxwPzyLPRJZo9M-GzxjttTDCRmCrQ';
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$accesstoken;
        $data = '{"button":[
        {"type":"click","name":"ATT家园","sub_button":[
        {"type":"view","name":"企业介绍","url":"http://'.$_SERVER['HTTP_HOST'].'/wapi/'.$bid.'/'.$sid.'/home/about"},
        {"type":"click","name":"门店展示","key":"v01_01"},
        {"type":"click","name":"一键预订","key":"v01_02"},
        {"type":"view","name":"我的位置","url":"http://'.$_SERVER['HTTP_HOST'].'/wapi/'.$bid.'/'.$sid.'/home/map"}]},	
        		
        {"type":"click","name":"开发使用","sub_button":[
        {"type":"click","name":"会员卡","key":"v03_01"},
        {"type":"view","name":"瀑布流","url":"http://'.$_SERVER['HTTP_HOST'].'/wapi/'.$bid.'/'.$sid.'/home/test"}]},
        		
        {"type":"click","name":"为您推荐","sub_button":[
        {"type":"click","name":"最新活动","key":"v02_01"},
        {"type":"view","name":"积分商城","url":"http://'.$_SERVER['HTTP_HOST'].'/wapi/'.$bid.'/'.$sid.'/home/consume/1"},
        {"type":"view","name":"超值套餐","url":"http://'.$_SERVER['HTTP_HOST'].'/wapi/'.$bid.'/'.$sid.'/home/setmeal"},
        {"type":"view","name":"美食/饮品","url":"http://'.$_SERVER['HTTP_HOST'].'/wapi/'.$bid.'/'.$sid.'/home/foods/1/2"},
        {"type":"view","name":"新歌推荐/排行","url":"http://'.$_SERVER['HTTP_HOST'].'/wapi/'.$bid.'/'.$sid.'/home/music/1"}]}]}';
        
        $return = $this->getCurl($url,$data);
        return $return;
    }
	
	
	/*
	*zxx get https的内容
	*/
	public function getCurl($url, $jsonData=''){
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//不输出内容
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		if($jsonData != ''){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS,$jsonData);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		}
		
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	
	/*
	*zxx 获取access_token
	*/
	public function getAccessToken()
	{
	   // $AppId = "wx96698ecdd069130c";
	   // $AppSecret = "380e0b18b7638a8c74092b6544e4d50d";
	   $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->AppId."&secret=".$this->AppSecret;
	   $data = $this->getCurl($url);//通过自定义函数getCurl得到https的内容
	   $resultArr = json_decode($data, true);//转为数组
	   return $resultArr["access_token"];
	}


}

/* End of file menu.php */
/* Location: ./application/controllers/menu.php */