<?php
/**
 * 
 * @copyright(c) 2013-12-2
 * @author msi
 * @version Id:att_controller.php
 */

class WX_Controller extends CI_Controller
{
    protected $bid;
    protected $sid;
    protected $userid;//用户id
    protected $identity;//用户身份
    protected $type = 1; //1：独占 0：共享
    protected $url;//页面url
    protected $template;//使用的模板
    protected $bak_url ;//REFERER 地址来源
    protected $url_prefix = '/wapi/';
//    protected $oauth_url = DOMAIN . '/wapi/90/0';

    protected $baseUrl;

    protected $pageParams = array(
        'community/home' => '社区',
        'square/square_index' => '广场',
        'user_activity/home' => '个人中心',
        'user/login' => '登录',
        'user/register' => '注册',
        'user/findpwd' => '找回密码',
        'user/edit_phone' => '修改手机',
        'user/bind_phone' => '绑定手机',
        'community/detail' => '活动详情',
        'community/coollife' => '精彩生活',
        'home/hotbusiness' => '热门商户',
        'user_activity/recommend_leader' => '推荐达人',
        'resource/index' => '资源库',
        'user_activity/expert' => '申请达人',
        'community/publish' => '发布活动',
        'search/index' => '搜索',
        'user_activity/user_info' => '编辑资料',
        'user_activity/code' => '验证消费码',
        'resource/bylist' => '我的资源',
        'user_activity/bylist' => '我的活动',
        'user_activity/my_wallet' => '我的钱包',
        'resource/info' => '资源详情',
        'user_activity/index' => '查看用户信息',
        'user_activity/agreement' => '用户协议',
        'user/changepwd' => '修改密码');
    
    protected $controller;
    protected $action;
    
    public function __construct()
    {
      parent::__construct();
      
      $this->load->library('static_resource');
      
      $this->bak_url = isset($_SERVER['HTTP_REFERER']) ? 
            $_SERVER['HTTP_REFERER'] : "javascript: history.go(-1);";
      
      $this->smarty->assign('bak_url', $this->bak_url);
      list($this->controller, $this->action) = array($this->router->fetch_class(), 
                $this->router->fetch_method());
      
      
      $this->init();
    }
    
    /**
     * 获取返回的URL 上级URL
     * @param $from 来自哪个网页面
     * @param $title false不获取标题， true 获取标题 default true;
     * @return 返回名称和URL array('title', 'url')
     * @author jamai 
     * @version 2.1
     */
    protected function backURL($from, $title = true)
    {
      //$_SERVER['HTTP_REFERER'];//通过A标签 跳转获取的地址
      //javascript: history.go(-1) //Js 返回上级一标识
      //获取标题
      if($from) {
        $urls = explode('/', $from);
        list($controller, $action) = array_slice($urls, 6);
        $actions = explode('?', $action);
        $url = $controller . '/' . $actions[0];

        foreach ($this->pageParams as $key => $value)
          if($url == $key) 
            if($title !== true) 
              //用户URL跳转容易导致死循环  直接利用JS的返回
              $return = array('backURL' => 'javascript: history.go(-1)');//$from);
            else 
              $return = array(
                'backURL'   => 'javascript: history.go(-1)',//$from,
                'backTitle' => $value,
              );
      }
      else {
        $from = $this->input->get('from');
        switch ($from) {
          case 'retpay/index'://活动支付返回
            $url = $this->baseUrl . '/community/coollife';
            $title = '精彩生活';
            break;
          case 'retpay/resource_buy': //资源支付返回
            $url = $this->baseUrl . '/resource/index';
            $title = '资源库';
            break;
          case 'community/publish'://发布活动成功
            $url = $this->baseUrl . '/user_activity/bylist';
            $title = '我的活动';
            break;
          case 'user_activity/expert': //申请达人
            $url = $this->baseUrl . '/user_activity/home';
            $title = '个人中心';
            break;
          default: 
            $url = $this->baseUrl . '/community/home';
            $title = '社区';
            break;
        }
        
        $return = array(
          'backURL'   => $url,
          'backTitle' => $title,
        );
      }
      return $return;
    }
    
    /**
     * 获取当前页面 title标题
     * @param $from 来自哪个网页面
     * @param $title false不获取标题， true 获取标题 default true;
     * @return 返回名称和URL array('title', 'url')
     * @author jamai 
     * @version 2.1
     */
    public function screenTitle()
    {
      $url = $this->controller . '/' . $this->action;
      
      if($this->pageParams[$url])
        return array('screenURL' => $url, 'screenTitle' => $this->pageParams[$url]);
    }
    
    protected function init()
    {
      $this->userid = $_SESSION['userid'] ? $_SESSION['userid'] : $_COOKIE['userid'] ? $_COOKIE['userid'] : null;
      
      $this->smarty->assign('userid', $this->userid);
      $this->smarty->assign('page', $this->router->fetch_class());
      
      $this->identity = $_SESSION['identity'];
      //检测相关缓存是否存在，商家缓存，商家的配置信息等
      $con = $this->router->fetch_class();

      //支付回调地址
      if($con != 'retpay') {
        $this->init_param();
        
        $this->init_business_info();
        $this->globalMedia();
        $this->SEOmedia();
        
        //获取根路径
        $this->baseUrl = $this->url_prefix . $this->bid . '/' . $this->sid;
        
        //返回地址和URL
        $this->smarty->assign($this->backURL($_SERVER['HTTP_REFERER']));
        
        //当前地址
        $this->smarty->assign($this->screenTitle());
        
        //获取最新消息 只要当前用户才可以看到最新消息
        $url = $this->controller . '/' . $this->action;
        if($this->userid)
          if($url == 'community/home' || $url == 'square/square_index' || 
                  $url == 'user_activity/home') 
            $this->smarty->assign('read', $this->is_new());
          else  //修改 最新阅读消息
            $this->readNew();
      }
    }

    /**
     * 缓存临时数据
     */
    protected function save_cache_data($key,$data,$replace = false){
        $this->load->driver('cache');
        if($this->cache->memcached->is_supported() === TRUE) {
            if(!$replace) {
                $this->cache->memcached->save($key,$data,5*60);
            }
            else {
                $this->cache->memcached->replace($key,$data,5*60);
            }
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 获取缓存数据
     */
    protected function get_cache_data($key){
        $this->load->driver('cache');
        $return = array();
        if($this->cache->memcached->is_supported() === TRUE){
            $cache = $this->cache->memcached->get($key);
            if(!empty($cache[0])){
                //$return = json_decode($cache[0],TRUE);
                return $cache[0];
            }
        }
        return $return;
    }

    //查询数组中企业的全部信息
    public function init_business_info(){
      
        //获取缓存数据
        $cache_data = $this->get_cache_data(md5('business'.$this->bid.$this->sid));
        //$this->save_cache_data(md5('business'.$this->bid.$this->sid),null);
        if(empty($cache_data)){
            $this->load->model('business_model','business');
            $business_number = $this->business->get_business_info($this->bid);

            $data = array();
            if($business_number){
                $data = $business_number[0];
                $where = 'id_business = '.$this->bid;
                //if($business_number[0]['visit_mode'] == 'exclusive'){
                    $where .= ' and id_shop = '.$this->sid;
               //}

                $this->load->model('businessconfig_model','businessconfig');
                $business_configs = $this->businessconfig->get_business_config($where);

//                if($business_number[0]['visit_mode'] == 'exclusive'){//独占
//                    $this->type = 1;
//                }else{
//                    $this->type = 0;
//                }

                if($business_configs){
//                    $data['visit_mode'] = $business_number[0]['visit_mode'];//更改该商家的共享独占
                    $data['id_shop'] = $business_configs[0]['id_shop'];
                    $data['appid'] = $business_configs[0]['appid'];
                    $data['appsecret'] = $business_configs[0]['appsecret'];
                    $data['token'] = $business_configs[0]['token'];
                    //缓存临时数据
                    $this->save_cache_data(md5('business'.$this->bid.$this->sid),$data);
                }
            }
        }
    }
    
    
    public function init_param(){
    	$this->smarty->assign('url_prefix',$this->url_prefix);
    	$param_tmp = $_SERVER['REQUEST_URI'];
    	$param_tmp = explode('/', $param_tmp);
    
    	//通过URL路径来取得商家信息
    	if($param_tmp[2] && isset($param_tmp[3])){
    		$this->bid = $param_tmp[2];
    		$this->sid = $param_tmp[3];
    		$this->url = '/wapi/'.$this->bid.'/'.$this->sid;
    		$this->smarty->assign('url',$this->url);
    	}else{
    		//URL没有商家信息，对域名做映射取得商家信息
    		//缓存商家信息
    	}
    	//查询商家的其他相关配置，是否是共享方式$this->type

      $this->load->model('business_model','business');
      $business_number = $this->business->get_business_info($this->bid);
      if($business_number[0]['visit_mode'] == 'exclusive'){//独占
          $this->type = 1;
      }else{
          $this->type = 0;
      }
      if(TPL_DEFAULT !== 'default') 
        $TPL_DEFAULT = '/' . TPL_DEFAULT;
      else 
        $TPL_DEFAULT = '';
      $this->smarty->assign('TPL_DEFAULT', $TPL_DEFAULT);
      
    }
    
    
    /**
     * 取得微信信息
     */
    public function get_weixin_info($openid){
    	 
    	$this->load->model('wxreply_model','wxreply');
   	
    	$token = $this->get_access_token();
    	if($token){
    		$url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$token.'&openid='.$openid.'&lang=zh_CN';
    		$result = request_curl($url);
//            file_put_contents(DOCUMENT_ROOT.'/att.txt',var_export($result,TRUE));
    		if(!empty($result)){
    			$result = json_decode($result,1);
    		}
    		if($result['openid']){
                $this->load->model('business_model','business');
    			//删除之前的重新添加新的
    			$where['id_business'] = $this->bid;
    			$where['id_shop'] = $this->sid;
    			$where['id_open'] = strval($openid);
    			$count = $this->business->get_subscribe_count($where);
                if($count){
                    $where['id_business'] = $this->bid;
                    $where['id_shop'] = $this->sid;
                    $where['id_open'] = strval($openid);
                    $where['state'] = 'unsubscribe';

                    $update['state'] = 'subscribe';
                    $update['created'] = date('Y-m-d H:i:s', time());//候总确认
                    $this->wxreply->edit_wx_info($update,$where);
                }else{
                    $add_data['id_business'] = $this->bid;
                    $add_data['id_shop'] = $this->sid;
                    $add_data['id_open'] = $result['openid'];
                    $add_data['nick_name'] = urlencode($result['nickname'] == '' ? '嗨皮'.substr($openid,strlen($openid)-4,strlen($openid)):$result['nickname']);
                    $add_data['sex'] = $result['sex'];
                    $add_data['city'] = $result['city'];
                    $add_data['head_image_url'] = $result['headimgurl'];
                    $this->load->model('wxreply_model','wxreply');
                    $this->wxreply->add_wx_info($add_data);
                }
    		}else{
    			$add_data['id_business'] = $this->bid;
    			$add_data['id_shop'] = $this->sid;
    			$add_data['id_open'] = $openid;
                $add_data['nick_name'] = urlencode($result['nickname'] == '' ? '嗨皮'.substr($openid,strlen($openid)-4,strlen($openid)):$result['nickname']);
                $add_data['sex'] = $result['sex'];
                $add_data['city'] = $result['city'];
                $add_data['head_image_url'] = $result['headimgurl'];
    			$this->wxreply->add_wx_info($add_data);
    		}
    	}

    }
    
    
    /**
     * 取得微信信息
     */
    public function get_weixin_info2(){
    exit(0);
    	$this->load->model('wxreply_model','wxreply');
    	$test1 = $this->wxreply->get_null_openid();
		//$test1 = array(array('id_open'=>'o5tjjt4_Mydqx_UvzaDzcWHkrkjE'));
    	$token = $this->get_access_token();
    	//debug($test1);
    	foreach ($test1 as $k => $v){
    		/* if($v['nick_name']){
    			$update = array();
    			//$update['nick_name'] = urlencode($v['nick_name']);
    			debug(urldecode($v['nick_name']));
    			//urldecode($str)
    			//$ff = $this->wxreply->edit_wx_info($update,array('id_open'=>$v['id_open']));
    			//debug(base64_decode($v['nick_name']));
    		} */
    		if($token){
    			$url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$token.'&openid='.$v['id_open'].'&lang=zh_CN';
    			//echo $url.'<br />';
    			$result = request_curl($url);
    			$result = json_decode($result,1);
    			
    			if($result['nickname']){
    				$update['nick_name'] = urlencode($result['nickname']);
    				debug($update);
    				$ff = $this->wxreply->edit_wx_info($update,array('id_open'=>$v['id_open']));
    				//var_dump($ff);
    			}
    		}
    	}
    	/* $openid = 'o5tjjt1yMUutweFsepat18W3PglY';
    	$token = $this->get_access_token();
    	if($token){
    		//
    		
    		
    		$url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$token.'&openid='.$openid.'&lang=zh_CN';
    		$result = request_curl($url);
    		$result = json_decode($result,1);
    		debug($result);
    	} */
    
    
    }
    
    
    //获取token
    public function get_access_token(){
    	 
    	$this->load->model('business_model','business');
    	$where['id_business'] = $this->bid;
    	$where['id_shop'] = $this->sid;
    	$bizconf = $this->business->get_biz_config($where);
    	if($bizconf['id_app'] && $bizconf['app_secret']){
    		$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$bizconf['id_app'].'&secret='.$bizconf['app_secret'];
    		$token_tmp = request_curl($url);
    		$token = json_decode($token_tmp,1);
    		if($token['access_token']){
    			return $token['access_token'];
    		}
    	} 
    	return false;	 
    }

    protected function save_analisys($type,$id,$event){
    	$add_data = array(
    		'id_business' => $this->bid,
    		'id_shop' => $this->sid,
    		'object_type' => $type,
    		'id_object' => $id,
    		'event' => $event
    	);
    	
    	$this->load->model('statlog_model','statlog');
    	$this->statlog->insert_log($add_data);
    }



    /**
     * 拼装商家微官网首页。连接页面地址
     * @param string $type list(列表页)/eitity(文章详情)/link(外部连接。直接使用)
     * @param string $object_type	info/activity/commodity		功能模块
     * @param int    $id_object	功能对应的分类编号或者实体编号
     * @param string $url	链接地址
     * @param int $id_business 商家ID
     * @param int $id_shop 门店ID
     * http://www.hipigo.com/wapi/1/0/home/class_list?r=info&c=7
     */
    function get_link_url($type,$object_type,$id_object,&$url,$id_business='',$id_shop=''){
        if($id_shop == ''){
            $this->load->model('shop_model','shop');
            $where = 'id_business = ' . $id_business;
            $shopInfo = $this->shop->get_shop_introduction($where);
            $id_shop = $shopInfo[0]['id_shop'];
        }
        if($this->type == 0){
            $id_shop = 0;
        }
        $host = 'http://'.$_SERVER['HTTP_HOST'].'/wapi/'.$id_business.'/'.$id_shop.'/home/';
        switch($type){
            case 'list':
                $host.='class_list?t='.$object_type.'&id='.$id_object;
                $url = $host;
                break;
            case 'eitity':
                $host.='content/'.$id_object;
                $url = $host;
                break;
        }
        return $url;
    }

    //计算时间差
    function hours_min($start_time,$end_time){
        if (strtotime($start_time) > strtotime($end_time)) list($start_time, $end_time) = array($end_time, $start_time);
        $sec = $start_time - $end_time;
        $sec = round($sec/60);
        $min = str_pad($sec%60, 2, 0, STR_PAD_LEFT);
        $hours_min = floor($sec/60);
        $min != 0 && $hours_min .= ':'.$min;
        return $hours_min;
    }



    //获取图片文件的本地路径+名字
    function file_url_name($path,$filename,$type=1){
        if($type == 0){
            $path = '/attachment/business/'.$path.'/';
        }else{
            $path = BASE_PATH. '/../attachment/business/'.$path.'/';
        }
        $cdir = str_split(strtolower($filename),1);
        $tmp = array_chunk($cdir, 4);
        if($tmp[0]){
            $dir = implode('/', $tmp[0]);
        }
        $path .= $dir.'/'.$filename;
        return $path;
    }

    /*
     * zxx 上传附件到微信 （图。 音，视频）
     * $type 上传附件类型
     * $url  上传附件地址
     * */
    function upload_wx_attachment($type,$access_token,$file_name,$path_name='reply'){
//        $access_token = $this->get_access_token($this->users['id_business'],$this->users['id_shop']);
        $path = get_img_url($file_name,'keyword_reply',1,'bg_mr.png');
        $post_data['media'] = '@'.$path;
        if($access_token){
            if($type == 'audio'){
                $type = 'voice';
            }
            $url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=". $access_token."&type=".$type;
            $ch = curl_init($url) ;
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch) ;
            if (curl_errno($ch)) {
                return curl_error($ch);
            }
            curl_close($ch);
            return $result;
        }
        return 0;
    }


    /**事件获奖判断
     * @param $type 事件类型(注册，关注，评论)
     * @param $id_open 用户微信号
     * @return string
     */
    public function event_chance($type,$id_open,$id_object='')
    {
//        file_put_contents(DOCUMENT_ROOT.'/aht.txt',var_export($type,TRUE));
        $id_business = $this->bid;
        $id_shop =  $this->sid;
        if($this->type == 0){
            $id_shop = 0;
        }
        if(empty($type) || empty($id_open))
        {
            $item = array('error'=>'事件类型或微信ID为空');
            echo json_encode($item);
            exit;
        }
        $this->load->model('chance_model','chance');
        //查询商家是否存在事件活动
        $this->load->model('activity_model','activity');
        $where = 'ma.id_business = ' . $id_business . ' and ma.id_shop = ' . $id_shop . ' and ma.type = "event" and ma.state = 1';
        $arr = $this->activity->get_event_activity($where);

        if(!empty($arr))
        {
//            $item = array('status'=>0,'error'=>'该商家没有事件活动');
//            echo json_encode($item);
//            exit;
            //商家对应type的活动配置
            $activity_info = array();
            foreach($arr as $va){
                if($va['requirement'] != null){
                    $require = json_decode($va['requirement'],true);
                    if(count($require['event']) > 0){
                        foreach($require['event'] as $re){
                            if($re['action'] == $type){
                                array_push($activity_info,$va);
                            }
                        }
                    }
                }
            }
            $ticket = array();

            $this->load->model('customer_model','customer');
            $this->load->model('business_model','business');
            $this->load->model('review_model','review');
            foreach($activity_info as $kai=>$vai){
                //获取活动绑定的电子卷和参与条件
                $requirement=json_decode($vai['requirement']);
                $req['id_activity'] = $vai['id_activity'];
                $req['created'] = $vai['created'];
                $req['ticket'] = $requirement->eticket;
                $req['event'] = $requirement->event;
//            if($type == 'review'){
                foreach($requirement->event as $ke=>$re1){
                    $where_re =  'id_business = ' . $id_business . ' and id_shop = ' . $id_shop . ' and id_open = \''.$id_open.'\'';
                    if($re1->action == 'register'){
                        $reg_count = $this->customer->get_customer_count($where_re);
                        $req['event'][$ke]->is_register = $reg_count > 0 ? 1 : 0;
                    }
                    if($re1->action == 'subscribe'){
                        $reg_count = $this->business->get_subscribe_count($where_re);
                        $req['event'][$ke]->is_subscribe = $reg_count > 0 ? 1 : 0;
                    }
                }
//            }
                array_push($ticket,$req);
            }

            //如果是注册和关注，就直接查询商家可用电子卷
            $data = array();
            $is_join = 0; //判断是否参加活动获得奖励（电子券）
            $join_info = $ticket;
            $get_ticket_num = 0;//获取电子券次数
            if($type=='register' || $type=='subscribe')
            {
                foreach ($ticket as $k1=>$v_ticket)
                {
                    foreach($v_ticket['ticket'] as $v){
                        $rand = rand(1, 1000);
                        //获得该活动中配置的每个电子卷的名称和剩余量
                        $row = $this->chance->find($v->eticketId);
                        $gift_num = $row[0]['quantity'] - $row[0]['get_quantity'];
                        //获得当前用户在该活动中获得此电子卷的数量
                        $num = $this->chance->find_num_win($v->eticketId,$id_open,$id_business,$id_shop);
                        $num = empty($num) ? 0 : $num;
                        //如果还剩余电子卷，这返回给用户(可以是多张电子卷)

                        if($num<$v->getMaxNumber && $gift_num>0)
                        {
                            $is_join = 1;
                            if($type=='subscribe'){
                                foreach($v_ticket['event'] as $ve1){
                                    if($ve1->action == 'register' && $ve1->is_register == 0){
                                        $join_info[$k1]['is_winner'] = 0;
                                        break;
                                    }elseif($ve1->action == 'review'){
                                        $join_info[$k1]['is_winner'] = 0;
                                        break;
                                    }
                                    $join_info[$k1]['is_winner'] = 1;
                                }
                            }else{
                                foreach($v_ticket['event'] as $ver2){
                                    if($ver2->action == 'subscribe' && $ver2->is_subscribe == 0){
                                        $join_info[$k1]['is_winner'] = 0;
                                        break;
                                    }elseif($ver2->action == 'review'){
                                        $where_r = 'r.id_business = ' . $id_business . ' and r.id_shop = ' . $id_shop . ' and r.id_open = \''.$id_open.'\'';
                                        if($ver2->reviewObject != ''){
                                            $where_r .= ' and r.object_type = \'' . $ver2->reviewObject . '\'';
                                        }
                                        if($ver2->obejctId != ''){
                                            $where_r .= ' and r.id_object = ' . $ver2->obejctId;
                                        }

                                        $where_r .=  ' and r.created > \'' . $v_ticket['created'] . '\'';
                                        $count_review = $this->review->get_review_count($where_r);

                                        if($count_review >= $ver2->count && ($count_review%$ver2->count) == 0 && ($v->getMaxNumber-$num) != 0){
                                            $join_info[$k1]['is_winner'] = 1;
                                        }else{
                                            $join_info[$k1]['is_winner'] = 0;
                                            break;
                                        }
                                    }
                                    $join_info[$k1]['is_winner'] = 1;
                                }
                            }

                            if($join_info[$k1]['is_winner'] == 1){
                                $code = substr(str_shuffle((time().$rand)),0,10);
                                $eticket_info['code']=$code;
                                $eticket_info['id_eticket'] = $row[0]['id_eticket'];
                                $eticket_info['name'] = $row[0]['name'];
                                $new_eticket=$this->set_eticket($id_open,$eticket_info,$v_ticket['id_activity']);
                                if(!empty($new_eticket))
                                    array_push($data,$new_eticket);
                            }
                        }else{
                            if($join_info[$k1]['is_winner'] == 1){}
                            else
                                $join_info[$k1]['is_winner'] = 0;
                        }
                    }
                }
            }else{
                //评论事件
                foreach ($ticket as $k2=>$v_r)
                {
                    foreach($v_r['ticket'] as $v){
                        $rand = rand(1, 1000);
                        //获得该活动中配置的每个电子卷的名称和剩余量
                        $row = $this->chance->find($v->eticketId);
                        $gift_num = $row[0]['quantity'] - $row[0]['get_quantity'];
                        //获得当前用户在该活动中获得此电子卷的数量
                        $num = $this->chance->find_num_win($v->eticketId,$id_open,$id_business,$id_shop);
                        $num = empty($num) ? 0 : $num;
                        //如果还剩余电子卷，这返回给用户(可以是多张电子卷)
                        if($num<$v->getMaxNumber && $gift_num>0)
                        {
                            if($type == 'review'){
                                foreach($v_r['event'] as $ver){
                                    if($ver->action == 'register'){
                                        if($ver->is_register == 0){
                                            $join_info[$k2]['is_winner'] = 0;
                                            break;
                                        }else{
                                            $join_info[$k2]['is_winner'] = 1;
                                        }
                                    }elseif($ver->action == 'subscribe'){
                                        if($ver->is_subscribe == 0){
                                            $join_info[$k2]['is_winner'] = 0;
                                            break;
                                        }else{
                                            $join_info[$k2]['is_winner'] = 1;
                                        }
                                    }elseif($ver->action == 'review'){
                                        if($ver->obejctId != ""){
                                            if($ver->obejctId != $id_object){
                                                $join_info[$k2]['is_winner'] = 0;
                                                break;
                                            }
                                        }
                                        $where_r = 'r.id_business = ' . $id_business . ' and r.id_shop = ' . $id_shop . ' and r.id_open = \''.$id_open.'\'';
                                        if($ver){
                                            if($ver->reviewObject != ''){
                                                $where_r .= ' and r.object_type = \'' . $ver->reviewObject . '\'';
                                            }
                                            if($ver->obejctId != ''){
                                                $where_r .= ' and r.id_object = ' . $ver->obejctId;
                                            }
                                        }
                                        $where_r .=  ' and r.created > \'' . $v_r['created'] . '\'';
                                        //查询评论条数
                                        $count_review = $this->review->get_review_count($where_r);

                                        if($count_review >= $ver->count && ($count_review%$ver->count) == 0 && ($v->getMaxNumber-$num) != 0){
                                            $is_join = 1;
                                            $join_info[$k2]['is_winner'] = 1;
                                        }else{
                                            $join_info[$k2]['is_winner'] = 0;
                                            break;
                                        }
                                    }
                                }
                                if($join_info[$k2]['is_winner'] == 1){
                                    $code = substr(str_shuffle((time().$rand)),0,10);
                                    $eticket_info['code']=$code;
                                    $eticket_info['id_eticket'] = $row[0]['id_eticket'];
                                    $eticket_info['name'] = $row[0]['name'];
                                    $new_eticket=$this->set_eticket($id_open,$eticket_info,$v_r['id_activity']);
                                    if(!empty($new_eticket))
                                        array_push($data,$new_eticket);
                                }
                            }
                        }
                    }
                }
            }
            if($is_join == 0){
                foreach ($join_info as $vt)
                {
                    //活动参与表插入数据
                    $activity_join = array(
                        'id_activity'=>$vt['id_activity'],
                        'id_open' => $id_open,
                        'id_customer' => 0,
                        'created' => date('Y-m-d H:i:s', time()),
                        'is_winner' => $vt['is_winner']==null?0:$vt['is_winner']
                    );
                    $res3 = $this->activity->insert_activity_join($activity_join);
                }
                //输出电子卷，可以是多张
                $item = array(
                    'status'=>0,
                    'msg'=>'',
                    'data'=>''
                );
                return json_encode($item);
            }else{
                foreach ($join_info as $vt1)
                {
                    //活动参与表插入数据
                    $activity_join = array(
                        'id_activity'=>$vt1['id_activity'],
                        'id_open' => $id_open,
                        'id_customer' => 0,
                        'created' => date('Y-m-d H:i:s', time()),
                        'is_winner' => $vt1['is_winner']==null?0:$vt1['is_winner']
                    );
                    $res4 = $this->activity->insert_activity_join($activity_join);
                }
                //输出电子卷，可以是多张
                $item = array(
                    'status'=>1,
                    'msg'=>'恭喜你成功获得了',
                    'data'=>$data
                );
                return json_encode($item);
            }
        }else{
            //输出电子卷，可以是多张
            $item = array(
                'status'=>0,
                'msg'=>'',
                'data'=>''
            );
            return json_encode($item);
        }
    }

//创建电子卷
    private function set_eticket($id_open,$eticket_info,$id_activity)
    {
        $this->load->model('chance_model','chance');
        $bn_id = $this->bid;
        $id_shop =  $this->sid;
        //创建电子卷
        $data = array(
            'id_business' =>$bn_id,                                 //商家编号
            'id_shop' =>$id_shop,                                   //门店编号
            'object_type' =>'eticket',                              //电子卷类型
            'id_object' =>$eticket_info['id_eticket'],              //电子卷的类型
            'id_open' =>$id_open,                                   //用户微信号
            'id_customer' =>0,                                      //用户注册编码
            'code' => $eticket_info['code'],                        //电子卷验证码
            'get_time' =>date('Y-m-d H:i:s'),                       //用户获得时间
            'use_time' =>0,                                         //用户使用时间
            'state' =>1,                                            //电子卷状态，1已获得
            'id_activity' =>$id_activity                         //活动id
        );

        if($this->chance->set_eticket($eticket_info['id_eticket'],$data))
        {
            $data=array(
                'code'=>$eticket_info['code'],
                'name'=>$eticket_info['name']
            );
            return $data;
        }
        return $data='';
    }


    /**
     * zxx
     * 根据网页授权的code获取评论用户的openid
     * $num 步骤数 1：第一步获取code 2：第二步获取id_open
     */
    function get_open_ids($number,$path='foodcontent',$id_commodity=1,$commodity_type='',$type=''){
        $openid = '';
//        if(!isset($_SESSION['openid'])){
        $this->load->model('businessconfig_model','businessconfig');
//            $where = 'id_business = 1000 and id_shop = 1';//候总确认
        $where = 'id_business = '.$this->bid.' and id_shop = '.$this->sid;//候总确认
        $config= $this->businessconfig->get_business_config($where);
        if($config){
            if($number == 1){
                $state =$config[0]['appid'].'_'.$config[0]['appsecret'];
                if($path == 'mallcontent'){
                    $uri = 'http://'.$_SERVER ['HTTP_HOST'].$this->url.'/home/mallcontent2/2/'.$id_commodity.'/'.$commodity_type;
                }elseif($path == 'foodcontent'){
                    $type1 = 0;
                    if($type == ''){$type1 = 1;}
                    $uri = 'http://'.$_SERVER ['HTTP_HOST'].$this->url.'/home/foodcontent2/2/'.$id_commodity.'/'.$commodity_type.'/'.$type1;
                }elseif($path == 'community'){
                    $scope = $commodity_type==""?'snsapi_base':$commodity_type;
                    $uri = 'http://'.DOMAIN;
                    if($scope == 'snsapi_userinfo'){
                        $uri.='/v1'.$id_commodity;
                        $uri = urlencode($uri);
                    }else{
                        $uri = 'http://'.DOMAIN_URL.'/community/home2/2?b='.$id_commodity;
                    }

//                        http://wx.test.hipigo.cn/wapi/1000/1/community/home2/2?b=1000&t=1   wx96698ecdd069130c
                    //https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxf0e81c3bee622d60&redirect_uri=http%3A%2F%2Fnba.bluewebgame.com%2Foauth_response.php&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect
//                               http%3A%2F%2Fwx.test.hipigo.cn%2Fwapi%2F1000%2F1%2Fcommunity%2Fhome2%2F2%3Fb%3D1000%26t%3D1    wapi%2F1000%2F1%2Fcommunity%2Fhome2
//                    $uri = 'http%3A%2F%2F'.$this->oauth_url.'%2Fv';//urlencode($uri);
//                        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$config[0]['appid'].'&redirect_uri='.$uri.'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';

                    $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$config[0]['appid'].'&redirect_uri='.$uri.'&response_type=code&scope='.$scope.'&state=STATE#wechat_redirect';
//$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxf0e81c3bee622d60&redirect_uri=http%3A%2F%2Fnba.bluewebgame.com%2Foauth_response.php&response_type=code&scope=snsapi_userinfo&state=STATE';

                    header('location:'.$url);
                    die;
                }elseif($path == 'gift'){
                    $scope = $commodity_type==""?'snsapi_base':$commodity_type;

                    $uri = 'http://'.DOMAIN;
                    if($scope == 'snsapi_userinfo'){
//                        header("Pragma: no-cache");
                        $uri.='/v2'.$id_commodity;
                        $uri = urlencode($uri);
                    }else{
//                        $uri.='%2Fv';
                        $uri = 'http://'.DOMAIN_URL.'/activity/gifts2/2?b='.$id_commodity;
                    }

                    $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$config[0]['appid'].'&redirect_uri='.$uri.'&response_type=code&scope='.$scope.'&state=STATE#wechat_redirect';
//                    $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxf0e81c3bee622d60&redirect_uri=http%3A%2F%2Fnba.bluewebgame.com%2Foauth_response.php&response_type=code&scope=snsapi_userinfo&state=STATE';

                    header('location:'.$url);
                    die;
                }elseif($path == 'wgw_index'){
                    $uri = 'http://'.DOMAIN_URL.'/home/wgw_index2/2?b='.$id_commodity;
                }elseif($path == 'community_detail'){
//                    s(1,'community_detail',$b,$parm,$scope);
                    setcookie('aid', $_COOKIE['aid'], time() + (60 * 60 * 24 * 30), '/', '.hipigo.cn');
                    $scope = $commodity_type==""?'snsapi_base':$commodity_type;
                    $uri = 'http://'.DOMAIN;

                    if($scope == 'snsapi_userinfo'){
                        $uri.='/v3'.$id_commodity;
                        $uri = urlencode($uri);
                    }else{
                        $uri = 'http://'.DOMAIN_URL.'/community/detail2/2?b='.$id_commodity;

//                        $parm_array = explode('_',$type);
//                        if($parm_array){
//                            foreach($parm_array as $kpa=>$vpa){
//                                if(count($parm_array) >= 2){
//                                    if($vpa && $kpa == 0){
//                                        $uri .= '&aid='.$vpa;
//                                    }elseif($vpa && $kpa == 1){
//                                        $uri .= '&suserid='.$vpa;
//                                    }elseif($vpa && $kpa == 2){
//                                        $uri .= '&type='.$vpa;
//                                    }
//                                }
//                            }
//                        }
                    }

                    $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$config[0]['appid'].'&redirect_uri='.$uri.'&response_type=code&scope='.$scope.'&state=STATE#wechat_redirect';

                    header('location:'.$url);
                    die;
                }elseif($path == 'subject'){
                    $uri = 'http://'.DOMAIN_URL.'/subject/subject_2/2';
                    $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$config[0]['appid'].'&redirect_uri='.$uri.'&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect';
                    header('location:'.$url);
                    die;
                }elseif($path == 'subject_share'){
                    $uri = 'http://'.DOMAIN_URL.'/subject/subject_share_2/2?id='.$id_commodity;
                    $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$config[0]['appid'].'&redirect_uri='.$uri.'&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect';
                    header('location:'.$url);
                    die;
                }

                $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$config[0]['appid'].'&redirect_uri='.$uri.'&response_type=code&scope=snsapi_base&state='.$state.'#wechat_redirect';

                header('location:'.$url);

            }elseif($number == 2){
            
                setcookie('aid', $_COOKIE['aid'], time() + (60 * 60 * 24 * 30), '/', '.hipigo.cn');
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
                if(($path == 'community' || $path == 'community_detail') && ($commodity_type == 'snsapi_userinfo' || $type == 'snsapi_userinfo')){
                
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
        }
        return $openid;
    }
    
    /**
     * 返回客户端信息通用函数
     * @param number $status 返回状态
     * @param string $data	包含的数据
     * @param string $msg	状态说明
     */
    protected function return_client($status = 0, $data = null, $msg = null)
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
     * @zhoushuai
     * @错误提示页面
     */
    protected function show_tip($tip="",$url=""){   
        $url=urlencode($url);
        $tip=urlencode($tip);
		header("Location: /wapi/{$this->bid}/{$this->sid}/home/error?tip={$tip}&url={$url}");  
    }


    /*
     * zxx
     * 根据openid获取用户昵称
     */
    function get_customer_name($where){
        $this->load->model('business_model','business');
        $name_info = $this->business->get_business_sub($where,$type=1);
        $name = '匿名';
        if($name_info){
            foreach($name_info as $ni){
                if($ni['nick_name']){
                    $name = urldecode($ni['nick_name']);
                    break;
                }
            }
        }
        return $name;
    }


    /**
     * zxx
     * 获取随机字符串
     * @param int $length 长度
     * @param int $numeric
     * @param int $type 0：数字字母混搭 1：纯数字 2：纯字母
     * @return string
     */
    function random($length = 6 , $numeric = 0,$type=0) {
        PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
        if($numeric) {
            $hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
        } else {
            $hash = '';
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
            if($type == 1)
                $chars = '0123456789';
            elseif($type == 2)
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

//            $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
            $max = strlen($chars) - 1;
            for($i = 0; $i < $length; $i++) {
                $hash .= $chars[mt_rand(0, $max)];
            }
        }
        return $hash;
    }


    //zxx
    //发送手机消费码
    //$msg_type  activity 参加活动后发送消费码  register 绑定手机和找回密码的验证码
    function sendcode($phone,$code,$msg_type='activity',$activity_info=array()){
        $username = "SDK-IT008-0051";
        $password = 'xrenwuit008';
        $sendto = $phone;
//        $message =urlencode("验证码:123456") ;//内容解码
        $msg = '';
        if($msg_type == "activity"){
            $str = '';
            if(!empty($activity_info) && $activity_info['object_type'] == 'community'){
                $this->load->model('business_model', 'business');
                $where = 'id_business = ' . $activity_info['id_business'];
                $business_info = $this->business->get_business_phone($where);
                if($business_info){
                    $str.='['.$business_info[0]['name'].']';
                }
            }
            $msg = "您的消费码为:".$code.",请妥善保管.".$str;
        }elseif($msg_type == "register"){
            $msg = "您的验证码是:".$code.",请不要把验证码泄露给其他人.";
        }
        if($msg){
            $message =urlencode($msg) ;//内容解码

            $url="http://124.173.70.59:8081/SmsAndMms/mt?";
            $curlPost = 'Sn='.$username.'&Pwd='.$password.'&mobile='.$sendto.'&content='.$message.'';

            $ch = curl_init();//初始化curl
            curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页
            curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  //允许curl提交后,网页重定向
            curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
            curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
            $data = curl_exec($ch);//运行curl
            curl_close($ch);
            return $data;//输出结果
        }else
            return '';
    }

    
  //加载 模块里的样式和脚本文件
  protected function media($file, $type, $domain = 'module') 
  {
    if($file && $type)
      $this->static_resource->add($file, $type, $domain);
    
    $this->smarty->assign('JS',  $this->static_resource->output('js'));
    $this->smarty->assign('CSS', $this->static_resource->output('css'));
  }
  
  //加载公共样式、脚本文件
  protected function globalMedia()
  {
    $options['css'] = array('Jingle.css', 'reset.css', 'index.css');//
    $options['js']  = array(
        // 'jquery.min.js',
        'jquery-1.8.3.js',
        //'zepto.js', 
        //'iscroll.js', 
        //'template.min.js', 
        //'Jingle.debug.js', 
        //'zepto.touch2mouse.js',
        'common.js',
        );
    
    foreach ($options as $key => $value) {
      foreach ($value as $k => $v) {
        $this->media($v, $key, 'module');
      }
    }
  }
  
  /** SEO 功能部分 */
  private function SEOmedia()
  {
    $this->load->model('seometa_model', 'seometa');
    
    list($controller, $action) = array($this->router->fetch_class(), 
            $this->router->fetch_method());
      
    $this->smarty->assign (array(
             'meta_title' => '',
          'meta_keywords' => '',
       'meta_description' => '',
        ));
    
    if($action) {
      $meta = $this->seometa->getSEOMedia($controller . '/' . $action);
      
      if($meta) {
        $this->smarty->assign (array(
          'meta_title'    => $meta->title,
          'meta_keywords' => $meta->keywords,
       'meta_description' => $meta->description,
        ));
      }
    }
  }
  
  /**
   * 处理JSON返回给前端页面（ajax）
   * @param code 返回代码  0：失败 1：成功
   * @param msg 提示语
   * @param data 返回页面数据
   * 
   * @author Jamai 
   * @version 2.0
   */
  protected function returnJson($code, $msg, $data = array(), $param = array()) 
  {
    $result = array(
      'code' => $code,
       'msg' => $msg,
   'success' => '',
   'options' => $param
    );
    
    if($data)
      $result['success'] = $data;
    
    return json_encode($result);
  }
  
  /**
   * 重定向
   * @param $url 跳转地址 controller/action
   * @param $way 跳转方式
   * @param $options Array
   *          business 指定商户ID
   *          shop     指定门店ID
   *
   * @author Jamai
   * @version 2.1
   **/
  protected function redirect($url, $way = 'default', $options = array())
  {
    //兼容之前写的
    if($options['business'] && $options['shop']) {
      $uri = '/wapi/' . $options['business'] . '/' . $options['shop'];
    }
    else if($options['business']) {
      $uri = '/wapi/' . $options['business'] . '/0';
    }
    else {
        $uri = $this->baseUrl;
    }

    $uri .= $url;

    if($way == 'js') {
      echo '<script>
        window.location.href = "http://' . $_SERVER['HTTP_HOST'] . $uri . '";
      </script>';
    }
    else {
      header('Location: http://' . $_SERVER['HTTP_HOST'] . $uri);
    }
    return;
  }

    //$opt   array(宽度，高度)  传入需要压缩的宽度和高度
  protected function showImage($objectID, $type = 'user',$opt=array())
  {
    $this->load->library('community_logic', 'mobile');
    return $this->community_logic->showImage($objectID, $type,$opt);
  }
  
  public function is_new()
  {
    $this->load->library('user_logic');
    //精彩生活
    $data['life']     = $this->user_logic->isNew($this->userid, 'life');
    //资源
    $data['resource'] = $this->user_logic->isNew($this->userid, 'resource');
    //商户
    $data['business'] = $this->user_logic->isNew($this->userid, 'business');
    //达人
    $data['expert']   = $this->user_logic->isNew($this->userid, 'expert');
    
    //我的资源
    $data['resource_by'] = $this->user_logic->isNew($this->userid, 'resource_by');
    //我的活动
    $data['activity_publish'] = $this->user_logic->isNew($this->userid, 'activity_publish');
    $data['activity_by'] = $this->user_logic->isNew($this->userid, 'activity_by');
    //我的消息
    //我的钱包
    $data['wallet'] = $this->user_logic->isNew($this->userid, 'wallet');
    
    return $data;
  }
  
  public function readNew()
  {
    $this->load->library('user_logic');
    $this->user_logic->readNew($this->userid, $this->controller . '/' . $this->action);
  }
  
}



/* End of file att_controller.php */