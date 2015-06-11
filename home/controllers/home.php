<?php

class Home extends CI_Controller
{
    protected $business_id; //商家ID
    protected $business_name; //商家名称
//    protected $oauth_url = DOMAIN;//'v.hipigo.cn';
    public function __construct()
    {
        parent::__construct();
        $this->init();
    }
    
    
    public function init(){
        $url = $_SERVER ['HTTP_HOST'];
        $arr = explode('.',$url); 
        if($arr[0] =='www'){
            header('location:http://'.DOMAIN_URL.'/community/home');
            die;
        }else{
            //$url = 'wx.dev.hipigo.cn';
            $url_prefix = '/wapi/';
            $this->load->model('business_model', 'business');
            if($url == 'wap.showdf.net'){
                $this->business_id = 1009;
                $this->smarty->assign('title', '秀丽东方');
            }else{
                $bs_inf = $this->business->get_bs_bid($url);
                $this->business_id = $bs_inf['id_business'];
                $this->smarty->assign('title', $bs_inf['name']);
            }
            $this->business->get_power($this->business_id,86);
            $this->smarty->assign('url_prefix', $url_prefix);
        }
    }
    

    /**
     * zxx
     * 根据网页授权的code获取评论用户的openid和个人资料
     * $num 步骤数 1：第一步获取code(wx_controller) 2：第二步获取id_open 3.根据openid获取用户信息
     */
    function get_open_ids11($number,$path='foodcontent',$id_commodity=1,$commodity_type='',$type=1){
        $openid = '';
//        if(!isset($_SESSION['openid'])){
        $this->load->model('businessconfig_model','businessconfig');
//            $where = 'id_business = 1000 and id_shop = 1';//候总确认
        $where = 'id_business = 90 and id_shop = 0';//候总确认
        $config= $this->businessconfig->get_business_config($where);
        if($config){
            if($number == 2){
                $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$config[0]['appid'].'&secret='.$config[0]['appsecret'].'&code='.$_GET['code'].'&grant_type=authorization_code';

                $ch = curl_init();
                curl_setopt($ch,CURLOPT_URL,$url);
                curl_setopt($ch,CURLOPT_HEADER,0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                $res = curl_exec($ch);
                curl_close($ch);
                $json_obj = json_decode($res,true);
                $openid = $json_obj['openid'];
                if(($path == 'community' || $path == 'gift' || $path == 'community_detail') && ($commodity_type == 'snsapi_userinfo' || $type == 'snsapi_userinfo')){
                    return $json_obj;
                }
            }else{
                $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$path.'&openid='.$id_commodity.'&lang=zh_CN';

                $ch = curl_init();
                curl_setopt($ch,CURLOPT_URL,$url);
                curl_setopt($ch,CURLOPT_HEADER,0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                $res = curl_exec($ch);
                curl_close($ch);
                $json_obj = json_decode($res,true);
                return $json_obj;
            }
        }else{
            return $openid;
        }

    }



    /**
     * @微官网首页
     */
    public function index($name='')
    {
        $this->load->model('business_model', 'business');
        if($name=='zj'){
             $this->smarty->view('index3');
        }else{ 
            if($name == 'v'){
//            $id_open = $this->get_open_ids11(2,'community',1,'snsapi_base');
            $where = 'sld = \'' . $_SERVER ['HTTP_HOST'] . '\'';//' . $_SERVER ['HTTP_HOST'] . '
            $bs_info = $this->business->get_business_phone($where);
            if($bs_info){
                    $agent = $_SERVER["HTTP_USER_AGENT"];
                    if(strpos($agent,"MicroMessenger")){
                        Header('Location:http://'.DOMAIN_URL.'/home/wgw_index1?b='.$bs_info[0]['id_business']);
//                        Header('Location:http://'.$_SERVER ['HTTP_HOST'].'/wapi/'.$this->business_id.'/0/community/home');
                    }else{
                        Header('Location:http://'.$_SERVER ['HTTP_HOST'].'/wapi/'.$this->business_id.'/0/community/home');
                    }
                    exit();
                }else{
                    echo '无法获取商家id!';
                    die;
                }
            }elseif(!(strpos($name, 'v1') === false)){
                $b = explode('v1',$name);
                if(strlen($_GET['code']) < 32){
                    header('location:http://'.DOMAIN_URL.'/community/home?state=exit');
                    die;
                }
                $id_open = $this->get_open_ids11(2,'community',1,'snsapi_userinfo');
                $user_info = $this->get_open_ids11(3,$id_open['access_token'],$id_open['openid']);
    
    //            $where = 'id_business = '.$this->business_id;
    //            $merchant = $this->business->get_business_phone($where);
    
                $url = 'http://'.DOMAIN.'/wapi/'.$b[1].'/0/community/home?oid='.$user_info['openid'];
    
                $this->load->model('hipigouser_model','hipigouser');
                $where = 'id_open = \'' . $user_info['openid'] . '\'';
                $is_r = $this->hipigouser->get_hipigo_user_info($where);
    
                if(!$is_r && $user_info['openid']){
                    $data = array(
                        'id_open' => $user_info['openid'],
                        'head_image_url' => $user_info['headimgurl'],
                        'sex' => $user_info['sex'],
                        'nick_name' => $user_info['nickname'] == ''? urlencode('嗨皮'.substr($user_info['openid'],strlen($user_info['openid'])-4,strlen($user_info['openid']))) : urlencode($user_info['nickname']),
                        'created' => date('Y-m-d H:i:s', time()),
                        'last_login_time' => date('Y-m-d H:i:s', time()),
                        'id_business' => $b[1] ? $b[1] : '90'
                    );
                    $uid = $this->hipigouser->insert_hipigo_user($data);//注册会员
                }
                Header('Location:'.$url);
                die;
            }elseif(!(strpos($name, 'v2') === false)){
                $b = explode('v2',$name);
                if(strlen($_GET['code']) < 32){
                    header('location:http://'.DOMAIN_URL.'/user/login');
                    die;
                }
                $id_open = $this->get_open_ids11(2,'gift',1,'snsapi_userinfo');
                $user_info = $this->get_open_ids11(3,$id_open['access_token'],$id_open['openid']);

                $url = 'http://'.DOMAIN.'/wapi/'.$b[1].'/0/activity/gifts?hoid='.$user_info['openid'];

                $this->load->model('hipigouser_model','hipigouser');
                $where = 'id_open = \'' . $user_info['openid'] . '\'';
                $is_r = $this->hipigouser->get_hipigo_user_info($where);

                if(!$is_r && $user_info['openid']){
                    $data = array(
                        'id_open' => $user_info['openid'],
                        'head_image_url' => $user_info['headimgurl'],
                        'sex' => $user_info['sex'],
                        'nick_name' => $user_info['nickname'] == ''? urlencode('嗨皮'.substr($user_info['openid'],strlen($user_info['openid'])-4,strlen($user_info['openid']))) : urlencode($user_info['nickname']),
                        'created' => date('Y-m-d H:i:s', time()),
                        'last_login_time' => date('Y-m-d H:i:s', time()),
                        'id_business' => $b[1] ? $b[1] : '90'
                    );
                    $uid = $this->hipigouser->insert_hipigo_user($data);//注册会员
                }
                Header('Location:'.$url);
                die;
            }elseif(!(strpos($name, 'v3') === false)){
                $b = explode('v3',$name);
                if(strlen($_GET['code']) < 32){
                    header('location:http://'.DOMAIN_URL.'/community/home?state=exit');
                    die;
                }
                $id_open = $this->get_open_ids11(2,'community_detail',1,'snsapi_userinfo');
                $user_info = $this->get_open_ids11(3,$id_open['access_token'],$id_open['openid']);
                $url = 'http://'.DOMAIN.'/wapi/'.$b[1].'/0/community/detail?oid='.$user_info['openid'];

                $this->load->model('hipigouser_model','hipigouser');
                $where = 'id_open = \'' . $user_info['openid'] . '\'';
                $is_r = $this->hipigouser->get_hipigo_user_info($where);

                if(!$is_r && $user_info['openid']){
                    $data = array(
                        'id_open' => $user_info['openid'],
                        'head_image_url' => $user_info['headimgurl'],
                        'sex' => $user_info['sex'],
                        'nick_name' => $user_info['nickname'] == ''? urlencode('嗨皮'.substr($user_info['openid'],strlen($user_info['openid'])-4,strlen($user_info['openid']))) : urlencode($user_info['nickname']),
                        'created' => date('Y-m-d H:i:s', time()),
                        'last_login_time' => date('Y-m-d H:i:s', time()),
                        'id_business' => $b[1] ? $b[1] : '90'
                    );
                    $uid = $this->hipigouser->insert_hipigo_user($data);//注册会员
                }

                Header('Location:'.$url);
                die;
            }


            $business_id = $this->business_id;
            //获取商家信息
            $where = 'ba.object_type = \'bn\' and ba.id_object = 0';
            $merchant = $this->business->get_business_info($business_id, $where);
            $images = explode(',', $merchant[0]['image_url']);
            foreach ($images as $value) {
                $path[] = get_img_url($value, 'attachment', 0, 'bg_mr.png');
            }
    
            $url = $_SERVER ['HTTP_HOST'];
            $url = mysql_escape_string($url);

            if($url == 'wap.showdf.net'){
                $url = 'showdf.hipigo.cn';
            }
            $where = 'b.sld = \''.$url . '\' and c.id_column = 0';
            $return = $this->business->get_bs_home_inf($where);
//            for($i=0;$i<count($return);$i++){
//                 $return[$i]['image'] = get_img_url($return[$i]['image'], 'home', 0, 'bg_mr.png');
//                 $this->get_link_url($return[$i]['type'],$return[$i]['object_type'],$return[$i]['id_object'],$return[$i]['url'],$return[$i]['visit_mode'],$return[$i]['id_business'],$return[$i]['template']);
//            }
            for($i=0;$i<count($return);$i++){
                $template = $return[$i]['template'];
                $return[$i]['image'] = get_img_url($return[$i]['image'], 'home', 0, 'bg_mr.png');
                $this->get_link_url($return[$i]['type'],$return[$i]['object_type'],$return[$i]['id_object'],$return[$i]['url'],$return[$i]['visit_mode'],$return[$i]['id_business'],'',$template);
            }

            $this->smarty->assign("information", $return);
            $this->smarty->assign('count',count($path));
            $this->smarty->assign('images', $path);
            $this->smarty->assign('introduction', html_entity_decode($merchant[0]['introduction'],ENT_COMPAT, 'utf-8'));
            $this->smarty->view('index');
        }
    }


    public function v(){
        
        $this->smarty->view('index2');
    }


    /**
     * 拼装商家微官网首页。连接页面地址
     * @param string $type list(列表页)/eitity(文章详情)/link(外部连接。直接使用)
     * @param string $object_type	info/activity/commodity		功能模块
     * @param int    $id_object	功能对应的分类编号或者实体编号
     * @param string $url	链接地址
     * @param int $id_business 商家ID
     * @param int $id_shop 门店ID
     * template 模板id
     * http://www.hipigo.com/wapi/1/0/home/class_list?r=info&c=7
     */
    function get_link_url($type,$object_type,$id_object,&$url,$model,$id_business='',$id_shop='',$template=1){
        if($model=='share'){
            $id_shop = 0;
        }else{
            if($id_shop == ''){
                $this->load->model('shop_model','shop');
                $where = 'id_business = ' . $id_business;
                $shopInfo = $this->shop->get_shop_introduction($where);
                $id_shop = $shopInfo[0]['id_shop'];
            }
        }
        $host = 'http://'.$_SERVER['HTTP_HOST'].'/wapi/'.$id_business.'/'.$id_shop.'/home/';
        switch($type){
            case 'list':
                $host.='class_list?t='.$object_type.'&id='.$id_object.'&template='.$template;
                $url = $host;
                break;
            case 'eitity':
                $host.='content/'.$id_object;
                $url = $host;
                break;
        }
        return $url;
    }


}
