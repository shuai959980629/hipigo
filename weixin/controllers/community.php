<?php
/**
 * 
 * @copyright(c) 2014-04-21
 * @author vikie
 * 
 */
class Community extends WX_Controller
{
    
  private $id_business ;//商家ID
  private $id_shop ;//门店ID
  private $id_activity;//活动id
  private $id_open ;//微信id;  
  private $userID;

  CONST PAGER = 1;
  public function __construct(){
    parent::__construct();
    $this->load->model('community_model','community');
    $this->id_business = $this->bid;
        $this->id_shop = $this->sid;
      $this->smarty->assign('bid', $this->bid);
      $this->smarty->assign('sid', $this->sid);

    $this->userID = $this->userid;

  }


    public function home2($number) {
//        $number=2;$b=1000;$scope = 1;
        $b = $this->input->get('b');
        //获取授权用户openid
        $id_open = $this->get_open_ids($number);
        if(!$id_open){
            $id_open = 0;
        }

//        $this->load->model('business_model','business');
//        $where = 'id_business = '.$b;
//        $merchant = $this->business->get_business_phone($where);

        $url = 'http://'.DOMAIN.'/wapi/'.$b.'/0/community/home?oid='.$id_open;//$merchant[0]['sld']
//        if($scope == 1){
//            $url = 'http://'.$merchant[0]['sld'].'/wapi/'.$b.'/0/community/home?oid='.$id_open['openid'];
////            $url .= '&scope='.$scope;
//            $this->load->model('hipigouser_model','hipigouser');
//            $where = 'id_open = \'' . $id_open['openid'] . '\'';
//            $is_r = $this->hipigouser->get_hipigo_user_info($where);
//            if(!$is_r){
//                $data = array(
//                    'id_open' => $id_open,
//                    'head_image_url' => $id_open['headimgurl'],
//                    'sex' => $id_open['sex'],
//                    'nick_name' => urlencode('嗨皮'.substr($id_open['nickname'],strlen($id_open['nickname'])-4,strlen($id_open['nickname']))),
//                    'created' => date('Y-m-d H:i:s', time()),
//                    'last_login_time' => date('Y-m-d H:i:s', time()),
//                    'id_business' => $this->bid
//                );
//                $this->hipigouser->insert_hipigo_user($data);//注册会员
//            }
//        }

        Header('Location:'.$url);
    }


    public function home3() {
        $b = $this->input->get('b');
        $scope = $this->input->get('scope')?$this->input->get('scope'):'';
        $this->get_open_ids(1,'community',$b,$scope);
    }


//社区活动首页
    public function home()
    {
        if(empty($_SERVER['HTTP_REFERER'])){
            $_SESSION['state'] = '';
        }
        if($_GET['state'] == 'exit'){
         $_SESSION['state'] = 'exit';
        }elseif($_SESSION['state'] != 'exit'){
            if($_COOKIE['userid'] == 'undefined'){
                setcookie('userid', '', time() -1, '/', '');
                setcookie('identity', '', time() -1, '/', '');
                header('location:'.$this->url_prefix . $this->bid .'/'.$this->sid.'/community/home?state=exit');
                die;
            }
            if($_COOKIE['userid']&&$_COOKIE['identity'])
            {
                if($_COOKIE['identity']!='visitor'){
                    $userid = $_COOKIE['userid'];
                }
            }
            else
            {
                //用户未登录
            }
            
            $this->load->model('hipigouser_model','hipigouser');

            $agent = $_SERVER["HTTP_USER_AGENT"];
            if(strpos($agent,"MicroMessenger")){//微信用户往下
                if(!$userid){//判断用户session是否存在，不存在往下
    //        session_start();
                    $id_open = $this->input->get('oid');//微信id;
                    if(!$id_open){//判断是否有openid。没有则往下读取用户openid
                        Header('Location:http://'.DOMAIN_URL.'/community/home3?b='.$this->bid);
                        die;
                    }

                    //如果存在openid。判断openid是否存在于bn_hipigo_user表中
                    $where = 'id_open = \'' . $id_open . '\'';
                    $is_r = $this->hipigouser->get_hipigo_user_info($where);
                    if(!$is_r){//若不存在表中则进入授权页面读取用户信息
                        //获取用户基本信息
                        Header('Location:http://'.DOMAIN_URL.'/community/home3?b='.$this->bid.'&scope=snsapi_userinfo');
                        die;
                    }
                    $userid = $is_r[0]['id_user'];
                }else{
                    //判断用户cookie是否存在，存在往下
                    //if($_COOKIE['identity'] != 'visitor'){//用户身份为准用户的往下
                        $data['last_login_time'] =  date('Y-m-d H:i:s', time());
                        $where = 'id_user = ' . $userid;
                        $this->hipigouser->update_hipigo_user($data,$where);//更新会员最后登录时间
                    //}
                    /*else{
                        $userid = $_COOKIE['userid'];
                    }*/
                }
                $_SESSION['userid']= $userid;
                $_SESSION['identity']='wechat';
                setcookie('userid', $userid, time() + (60 * 60 * 24 * 30), '/', '');
                setcookie('identity', 'wechat', time() + (60 * 60 * 24 * 30), '/', '');
            }
            else{//非微信用户往下
                if($userid){
                    //if($_COOKIE['identity'] != 'visitor'){
                        $data['last_login_time'] = date('Y-m-d H:i:s', time());
                        $where = 'id_user = ' . $userid;
                        $this->hipigouser->update_hipigo_user($data,$where);//更新会员最后登录时间
                    //}else{
                    //    $userid = $_COOKIE['userid'];
                    //}
                    
                    $_SESSION['userid']= $userid;
                    $_SESSION['identity']='register';
                    
                    setcookie('userid', $userid, time() + (60 * 60 * 24 * 30), '/', '');
                    setcookie('identity', 'register', time() + (60 * 60 * 24 * 30), '/', '');
                }
            }
            
            //判断用户的角色
            //BY 2.0 Jamai
            if( ! empty($userid)) {
              $where = 'id_user = ' . $userid . ' AND id_role = 3';
              $user_role = $this->hipigouser->get_hipigo_user_info($where);
              $boolen_role = ! empty($user_role) ? true : false;
              $publish_url = 'http://' . $_SERVER['HTTP_HOST'] . '/wapi/' . 
                  $this->bid . '/' . $this->sid . '/community/publish';
              //$publish_url = 'publish';
              
              $this->smarty->assign('boolen_role', $boolen_role);
              $this->smarty->assign('publish_url', $publish_url);
            }
        }

//      $_SESSION['oid'] =  $id_open;
    $bid=$this->bid;
        //判断当前商家是否为hipigo
        $user_state = ($this->bid == 90) ? '<em>嗨皮xx</em>' : '<em>精彩</em>';
        if($this->bid==90)
        {
         //查询用户头像
        if(!empty($userid) && $_SESSION['identity'] !='visitor'){
        $user_info = $this->community->new_user_header($userid);
    $user_header =   $user_info['head_image_url'];
    $nick_name = urldecode($user_info['nick_name']);
    $user_header = empty($user_header)  ? '/wapi/img/default-header.png' : $user_header;
    $nick_name = empty($nick_name) ? '嗨皮匿名' : $nick_name;
        $url = '/wapi/90/0/user_activity/index?userid='.$userid;  //进入个人中心页面
        $user_state = '<p class="user_message right"><a href="'.$url.'"><img src="'.$user_header.'"/><em>'.$nick_name.'</em></a></p>';
        }else{
     $url = '/wapi/90/0/user/login';  //进入登陆或注册页面
         $user_state = '<p class="other_message right"><a href="'.$url.'"><i class="icon-angle-right icon-large right"></i><span class="right">登<br>录</span></a></p>';  
        }
        
        }else{
         $url = '/wapi/90/0/community/home';
         $user_state = '<p class="other_message right"><a href="'.$url.'"><i class="icon-angle-right icon-large right"></i><span class="right">精<br>彩</span></a></p>';
        }
        
        //获取用户的角色 BY:2.0 Jamai
        //$user_role = $this->users->get_hipigo_user_info('id_role = 3');
        
        $user_url = ($this->bid == 90) ? '#': '#'; //这里需要配置地址
        //查询商家信息（商家logo和商家名称）
        $business = $this->community->logo($this->bid);
    
        $logo_url = $business['logo_url'];
        $title = $business['title'];
    //商家微官网地址
    $sld_url = $business['sld_url'];
    
        //查询商家活动总数
        $activity_num = $this->community->activity_num($this->bid);
        $activity_num = empty($activity_num) ? 0 : $activity_num;
        
        //查询商家是否开启广告
        $is_open_ad = $this->community->is_open_ad($bid);
        if(empty($is_open_ad) || $is_open_ad[0]['key_value']==1){
         //商家开启广告(先查询商家是否有自己的广告)
         $ad = $this->community->bn_ad_self($bid);
         $ad_status = empty($ad) ? 0 : 1;  //0,商家没有广告，
        }else{
         //商家关闭广告
         $ad_status =0;
        }

        $ad_num = count($ad)==1 ? 0 : 1;
    $state = empty($_SESSION['state']) ? '' : $_SESSION['state'];

    //判断访问者
    $agent = $_SERVER["HTTP_USER_AGENT"];
        if(strpos($agent,"MicroMessenger")){
        $anent =1;  
    }else{
    $anent =0;  
    }
    
    //$anent = isMobile()==true ? 1 : 0;
    //判断商家是否授权
    $merchant = $this->community->merchant($this->bid);
    $this->smarty->assign('merchant',$merchant);
    $this->smarty->assign('sld_url',$sld_url);
    $this->smarty->assign('anent',$anent);
    $this->smarty->assign('state',$state);
        $this->smarty->assign('title',$title);
        $this->smarty->assign('logo_url',$logo_url);
        $this->smarty->assign('ad_num',$ad_num);
        $this->smarty->assign('ad_status',$ad_status);
//        $this->smarty->assign('total',$total);
        $this->smarty->assign('activity_num',$activity_num);
//        $this->smarty->assign('current',$current);
//        $this->smarty->assign('current_state',$current_state);
        $this->smarty->assign('user_state',$user_state);
        $this->smarty->assign('user_url',$user_url);
        $this->smarty->assign('ad',$ad);
        $this->smarty->assign('bid',$this->bid);
        $this->smarty->assign('sid',$this->sid);
        $this->smarty->assign('userid',$userid);
        $this->smarty->assign('num',rand(100,999));
        
        $page = $this->input->get('page');
        $page = empty($page) ? 1 : $page;
        $pagesize = 25;
        $total = $pagesize * ($page > 0 ? ($page - 1) : 0);
        $bid = 90;
        $screen_width = $this->input->get('screenwidth');
        $data = $this->community->hipigo_community_list($total,$pagesize,$screen_width);
        $this->smarty->assign('data', $data);
        
		if(TPL_DEFAULT == 'default') 
		  $this->smarty->view('hipigo_community1');
		else {
		  $this->media('home.js', 'js');
		  $this->smarty->view('index');
		}

}

//社区活动首页
    public function kill()
    {
        if(empty($_SERVER['HTTP_REFERER'])){
            $_SESSION['state'] = '';
        }
        if($_GET['state'] == 'exit'){
         $_SESSION['state'] = 'exit';
        }elseif($_SESSION['state'] != 'exit'){
            if($_COOKIE['userid'] == 'undefined'){
                setcookie('userid', '', time() -1, '/', '');
                setcookie('identity', '', time() -1, '/', '');
                header('location:'.$this->url_prefix . $this->bid .'/'.$this->sid.'/community/home?state=exit');
                die;
            }
            if($_COOKIE['userid']&&$_COOKIE['identity'])
            {
                if($_COOKIE['identity']!='visitor'){
                    $userid = $_COOKIE['userid'];
                }
            }
            else
            {
                //用户未登录
            }
            
            $this->load->model('hipigouser_model','hipigouser');

            $agent = $_SERVER["HTTP_USER_AGENT"];
            if(strpos($agent,"MicroMessenger")){//微信用户往下
                if(!$userid){//判断用户session是否存在，不存在往下
    //        session_start();
                    $id_open = $this->input->get('oid');//微信id;
                    if(!$id_open){//判断是否有openid。没有则往下读取用户openid
                        Header('Location:http://'.DOMAIN_URL.'/community/home3?b='.$this->bid);
                        die;
                    }

                    //如果存在openid。判断openid是否存在于bn_hipigo_user表中
                    $where = 'id_open = \'' . $id_open . '\'';
                    $is_r = $this->hipigouser->get_hipigo_user_info($where);
                    if(!$is_r){//若不存在表中则进入授权页面读取用户信息
                        //获取用户基本信息
                        Header('Location:http://'.DOMAIN_URL.'/community/home3?b='.$this->bid.'&scope=snsapi_userinfo');
                        die;
                    }
                    $userid = $is_r[0]['id_user'];
                }else{
                    //判断用户cookie是否存在，存在往下
                    //if($_COOKIE['identity'] != 'visitor'){//用户身份为准用户的往下
                        $data['last_login_time'] =  date('Y-m-d H:i:s', time());
                        $where = 'id_user = ' . $userid;
                        $this->hipigouser->update_hipigo_user($data,$where);//更新会员最后登录时间
                    //}
                    /*else{
                        $userid = $_COOKIE['userid'];
                    }*/
                }
                $_SESSION['userid']= $userid;
                $_SESSION['identity']='wechat';
                setcookie('userid', $userid, time() + (60 * 60 * 24 * 30), '/', '');
                setcookie('identity', 'wechat', time() + (60 * 60 * 24 * 30), '/', '');
            }
            else{//非微信用户往下
                if($userid){
                    //if($_COOKIE['identity'] != 'visitor'){
                        $data['last_login_time'] = date('Y-m-d H:i:s', time());
                        $where = 'id_user = ' . $userid;
                        $this->hipigouser->update_hipigo_user($data,$where);//更新会员最后登录时间
                    //}else{
                    //    $userid = $_COOKIE['userid'];
                    //}
                    
                    $_SESSION['userid']= $userid;
                    $_SESSION['identity']='register';
                    
                    setcookie('userid', $userid, time() + (60 * 60 * 24 * 30), '/', '');
                    setcookie('identity', 'register', time() + (60 * 60 * 24 * 30), '/', '');
                }
            }
            
            //判断用户的角色
            //BY 2.0 Jamai
            if( ! empty($userid)) {
              $where = 'id_user = ' . $userid . ' AND id_role = 3';
              $user_role = $this->hipigouser->get_hipigo_user_info($where);
              $boolen_role = ! empty($user_role) ? true : false;
              $publish_url = 'http://' . $_SERVER['HTTP_HOST'] . '/wapi/' . 
                  $this->bid . '/' . $this->sid . '/community/publish';
              //$publish_url = 'publish';
              
              $this->smarty->assign('boolen_role', $boolen_role);
              $this->smarty->assign('publish_url', $publish_url);
            }
        }

//      $_SESSION['oid'] =  $id_open;
    $bid=$this->bid;
        //判断当前商家是否为hipigo
        $user_state = ($this->bid == 90) ? '<em>嗨皮xx</em>' : '<em>精彩</em>';
        if($this->bid==90)
        {
         //查询用户头像
        if(!empty($userid) && $_SESSION['identity'] !='visitor'){
        $user_info = $this->community->new_user_header($userid);
    $user_header =   $user_info['head_image_url'];
    $nick_name = urldecode($user_info['nick_name']);
    $user_header = empty($user_header)  ? '/wapi/img/default-header.png' : $user_header;
    $nick_name = empty($nick_name) ? '嗨皮匿名' : $nick_name;
        $url = '/wapi/90/0/user_activity/index?userid='.$userid;  //进入个人中心页面
        $user_state = '<p class="user_message right"><a href="'.$url.'"><img src="'.$user_header.'"/><em>'.$nick_name.'</em></a></p>';
        }else{
     $url = '/wapi/90/0/user/login';  //进入登陆或注册页面
         $user_state = '<p class="other_message right"><a href="'.$url.'"><i class="icon-angle-right icon-large right"></i><span class="right">登<br>录</span></a></p>';  
        }
        
        }else{
         $url = '/wapi/90/0/community/home';
         $user_state = '<p class="other_message right"><a href="'.$url.'"><i class="icon-angle-right icon-large right"></i><span class="right">精<br>彩</span></a></p>';
        }
        
        //获取用户的角色 BY:2.0 Jamai
        //$user_role = $this->users->get_hipigo_user_info('id_role = 3');
        
        $user_url = ($this->bid == 90) ? '#': '#'; //这里需要配置地址
        //查询商家信息（商家logo和商家名称）
        $business = $this->community->logo($this->bid);
    
        $logo_url = $business['logo_url'];
        $title = $business['title'];
    //商家微官网地址
    $sld_url = $business['sld_url'];
    
        //查询商家活动总数
        $activity_num = $this->community->activity_num($this->bid);
        $activity_num = empty($activity_num) ? 0 : $activity_num;
        
        //查询商家是否开启广告
        $is_open_ad = $this->community->is_open_ad($bid);
        if(empty($is_open_ad) || $is_open_ad[0]['key_value']==1){
         //商家开启广告(先查询商家是否有自己的广告)
         $ad = $this->community->bn_ad_self($bid);
         $ad_status = empty($ad) ? 0 : 1;  //0,商家没有广告，
        }else{
         //商家关闭广告
         $ad_status =0;
        }

        $ad_num = count($ad)==1 ? 0 : 1;
    $state = empty($_SESSION['state']) ? '' : $_SESSION['state'];

    //判断访问者
    $agent = $_SERVER["HTTP_USER_AGENT"];
        if(strpos($agent,"MicroMessenger")){
        $anent =1;  
    }else{
    $anent =0;  
    }
    
    //$anent = isMobile()==true ? 1 : 0;
    //判断商家是否授权
    $merchant = $this->community->merchant($this->bid);
    $this->smarty->assign('merchant',$merchant);
    $this->smarty->assign('sld_url',$sld_url);
    $this->smarty->assign('anent',$anent);
    $this->smarty->assign('state',$state);
        $this->smarty->assign('title',$title);
        $this->smarty->assign('logo_url',$logo_url);
        $this->smarty->assign('ad_num',$ad_num);
        $this->smarty->assign('ad_status',$ad_status);
//        $this->smarty->assign('total',$total);
        $this->smarty->assign('activity_num',$activity_num);
//        $this->smarty->assign('current',$current);
//        $this->smarty->assign('current_state',$current_state);
        $this->smarty->assign('user_state',$user_state);
        $this->smarty->assign('user_url',$user_url);
        $this->smarty->assign('ad',$ad);
        $this->smarty->assign('bid',$this->bid);
        $this->smarty->assign('sid',$this->sid);
        $this->smarty->assign('userid',$userid);
        $this->smarty->assign('num',rand(100,999));
        


        $this->smarty->assign('data', $data);

		if(TPL_DEFAULT == 'default') 
		  $this->smarty->view('hipigo_community1');
		else {
		  $this->media('details.css', 'css');
		  $this->media('kill.js', 'js');
		  $this->smarty->view('seckill_list');
		}

}


  //活动列表及分页
  public function kill_list(){

        $page = $this->input->get('page');
        $page = empty($page) ? 1 : $page;
        $pagesize = 25;
        $total = $pagesize * ($page > 0 ? ($page - 1) : 0);
        $bid = 90;
        $screen_width = $this->input->get('screenwidth');
        $data = $this->community->hipigo_community_list_kill($total,$pagesize,$screen_width);
		$now_time = time();

		foreach($data as $k=>$v){

				$header_img = $this->showImage($v['id_activity'], 'community');
				$data[$k]['header_img'] = $header_img;
				$end_time = $v['end_time'];
				$start_time = $v['start_date'];

				if($now_time > $end_time){
					$data[$k]['kill_time'] = 0;
				}elseif($now_time < $end_time&&$now_time > $start_time){
					$data[$k]['kill_time'] = 1;
				}elseif($now_time < $start_time){
					$data[$k]['kill_time'] = date('Y/m/d H:i:s',$start_time);
				}

        }

        echo json_encode($data);

  }



  //活动列表及分页
  public function home_list()
  {
//$testTime['projectStart'] = microtime(true);
    if(TPL_DEFAULT == 'default') {
      $page = $this->input->get('page');
      $page = empty($page) ? 1 : $page;
      $pagesize = 25;
      $total = $pagesize * ($page > 0 ? ($page - 1) : 0);
      $bid = $this->input->get('bid');
      $screen_width = $this->input->get('screenwidth');
      if($bid==90)
      {
      //hipigo  
       $data = $this->community->hipigo_community_list($total,$pagesize,$screen_width);
      }else{
      //查询商家列表数据(普通商家)
       $data = $this->community->community_list($bid,$total,$pagesize,$screen_width);
      }
      if($data){
        $this->load->model('list_model','list');
        foreach($data as $k=>$v){
          if($v['join_price'] > 0){
            $where = 'object_type = "community" and state = 1 and id_object = ' . $v['id_activity'];
            $order_info = $this->list->get_order_info($where);
            $data[$k]['join_count'] = count($order_info);
          }
        }
      }

      $status = !empty($data) ? 1 : 0;
      $item = array('status'=>$status,'list'=>$data);
      echo json_encode($item);

    }
    else {
      $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;
      $source = $this->input->post('source') ? $this->input->post('source') : 'activity_list';
      $limit = 25;
      
      $this->load->library('community_logic', 'mobile');
      
      if($source != 'activity_list') {
        $where = 'is_spread = 1 and state = 2';
      }
      else {
        $where = "(id_business = {$this->bid} OR re.owner = {$this->bid} OR id_business_source = {$this->bid})";
      }
      
      $data = $this->community_logic->getCommunities($where, $params = array('offset' => ($offset - 1) * $limit, 'limit' => $limit));
      
      $this->smarty->assign(array('lists' => $data));
      $this->smarty->view('home_list');
    }

//$testTime['projectEnd'] = microtime(true);
//$testTime['projectTime'] = $testTime['projectEnd'] - $testTime['projectStart'];
//      var_dump($testTime['projectTime']);
  }


//首页搜索
public function search(){
  $title = $this->input->get('title'); 
  //$title='垃圾';
  //查询是有搜索结果
  if(empty($title)){
     $search_num = 0;
   }else{
      $search_num  = $this->community->search_num($title);
     $search_num = empty($search_num) ? 0 : $search_num;
  }
  $state = empty($_SESSION['state']) ? '' : $_SESSION['state'];
  $this->smarty->assign('search_num',$search_num);
  $this->smarty->assign('bid',$this->bid);
  $this->smarty->assign('sid',$this->sid);
  $this->smarty->assign('state',$state);
  $this->smarty->assign('title',$title);
  $this->smarty->assign('num',rand(100,999));
  $this->smarty->view('search');
}


//首页搜索
public function search_list(){
  
  $page = $this->input->get('page');
  $page = empty($page) ? 1 : $page;
  $pagesize = 25;
  $total = $pagesize * ($page > 0 ? ($page - 1) : 0);
  $title = $this->input->get('title');
  if(empty($title)){
  $item = array('status'=>0,'list'=>'');
  echo json_encode($item);
  exit;  
  }
  //$title='垃圾';
  $screen_width = $this->input->get('screenwidth');
$data =array();
  $data1 = $this->community->search_list($title,$total,$pagesize,$screen_width,1);
  $data2 = $this->community->search_list($title,$total,$pagesize,$screen_width,2);
    $data=array_merge($data1,$data2);
//    var_dump($data);
  $status = !empty($data) ? 1 : 0;
  $item = array('status'=>$status,'list'=>$data);
  echo json_encode($item);
}


//关注(这里关注为内部关注)
public function bn_att()
{
  $oid = $this->input->get('oid');
  $bid = $this->input->get('bid');
  $this->community->bn_att($bid,$oid);
}
//世界杯活动所有成员
public function world_cup(){
 
  $userid = $_SESSION['userid'];
  //$aid = $this->input->get('aid');
  
  //查询活动信息
  //$arr = $this->community->community_name($aid);
  $title  = '2014世界杯活动成员';
  $notice = '获得优惠劵所有成员';
 
  
  //查询是否有人获得世界杯活动优惠劵
  $att_user = $this->community->find_win_all();
  $att_user = empty($att_user) ? 0 : 1;
  //$num = $this->community->community_user($aid,$userid);
  $num=0;
  $num = !empty($num) ? 1 : 0; //1为管理员，0为普通成员
  $this->smarty->assign('notice',$notice);
  $this->smarty->assign('userid',$userid);
  $this->smarty->assign('att_user',$att_user);
  $this->smarty->assign('num',$num);    
  $this->smarty->assign('title',$title);
  $this->smarty->assign('cup',1);
  $this->smarty->view('community_leaguer1');  
 
}

//世界杯活动所有成员列表
public function user_win_all(){
  $page = $this->input->get('page');
  $userid = $_SESSION['userid'];
  $aid = $this->input->get('aid');
  $aid='';
  $page = empty($page) ? 1 : $page;
  $pagesize = 25;
  $total = $pagesize * ($page > 0 ? ($page - 1) : 0);
  if(!empty($userid)){
  if($page==1)
  {
    $where=array('id_customer'=>$userid,'object_type'=>'eticket');
  $where1=array('id_customer !='=>$userid,'object_type'=>'eticket');  
    $self = $this->community->get_win_list($aid,$where,1,1);
  //查询电子验证码
  if(!empty($self)){
   $code = $this->community->user_code($aid,$userid,$type='eticket');  
   $self[0]['code']=$code;  
  }
  
  $res = $this->community->get_win_list($aid,$where1,$pagesize,$total);
    $data = array_merge($self,$res);  
  }else{
    $data = $this->community->get_win_list($aid,$where='',$pagesize,$total);
  }
}else{
  
    $data = $this->community->get_win_list($aid,$where='',$pagesize,$total);
  
} 
 
 $status = !empty($data) ? 1 : 0;
 $item = array('status'=>$status,'list'=>$data);
 echo json_encode($item);  
}

//查询更多验证码
public function find_code_more(){
  $aid = $this->input->get('aid');
  $data = $this->community->find_code_mores($aid);
  $status = !empty($data) ? 1 : 0;
    $item = array('status'=>$status,'list'=>$data);
    echo json_encode($item);
}

//活动成员列表
public function user_att_list()
{
  $userid = $_SESSION['userid'];
  $aid = $this->input->get('aid');
  
  //查询活动信息
  $arr = $this->community->community_name($aid);
  $title = $arr['title'];
  $notice = !empty($arr['notice']) ? $arr['notice'] : 0;
  //查询活动的成员是否为空
  $att_user = $this->community->att_community_user($aid);
  //世界杯活动查询中奖者
  if($arr['type'] ==1){
  $att_user = $this->community->find_win_num($aid);  
  }
  
  $att_user = empty($att_user) ? 0 : 1;
  //查询当前用户是否为管理员
  if(!empty($userid) && $att_user != 0)
  {
    $num = $this->community->community_user($aid,$userid);
  $num = !empty($num) ? 1 : 0; //1为管理员，0为普通成员
  }
  
  $this->smarty->assign('notice',$notice);
  $this->smarty->assign('userid',$userid);
  $this->smarty->assign('att_user',$att_user);
  $this->smarty->assign('num',$num);    
  $this->smarty->assign('aid',$aid);
  $this->smarty->assign('title',$title);
  
  if($arr['type']==1){
  $this->smarty->view('community_leaguer1');  
  }else{
  $this->smarty->view('community_leaguer');  
  }
  
  
}

//世界杯获奖者
public function user_win_list_page()
{
  $page = $this->input->get('page');
  $userid = $_SESSION['userid'];
  
  $aid = $this->input->get('aid');
  $page = empty($page) ? 1 : $page;
  $pagesize = 25;
  $total = $pagesize * ($page > 0 ? ($page - 1) : 0);
if(!empty($userid)){
  if($page==1)
  {
    $where=array('id_customer'=>$userid,'object_type'=>'eticket');
  $where1=array('id_customer !='=>$userid,'object_type'=>'eticket');  
    $self = $this->community->get_win_list($aid,$where,1,1);
  //查询电子验证码
  if(!empty($self)){
   $code = $this->community->user_code($aid,$userid,$type='eticket');  
   $self[0]['code']=$code;  
  }
  
  $res = $this->community->get_win_list($aid,$where1,$pagesize,$total);
    $data = array_merge($self,$res);  
  }else{
    $data = $this->community->get_win_list($aid,$where='',$pagesize,$total);
  }
}else{
  
    $data = $this->community->get_win_list($aid,$where='',$pagesize,$total);
  
} 
 $status = !empty($data) ? 1 : 0;
 $item = array('status'=>$status,'list'=>$data);
 echo json_encode($item);
 
}



//活动成员列表分页数据
public function user_att_list_page()
{
  $page = $this->input->get('page');
  $userid = $_SESSION['userid'];
  //echo $_SESSION['identity'];
  //exit;
  $aid = $this->input->get('aid');
  $page = empty($page) ? 1 : $page;
  $pagesize = 25;
  $total = $pagesize * ($page > 0 ? ($page - 1) : 0);
if(!empty($userid)){
  if($page==1)
  {
    //查询当前用户是否已参加该活动
    if($_SESSION['identity'] == 'visitor'){
    $where=array('id_activity'=>$aid,'cellphone'=>$userid);  
    $where1=array('id_activity'=>$aid,'cellphone !='=>$userid);
    }else{
      
    $where=array('id_activity'=>$aid,'id_user'=>$userid);
  $where1=array('id_activity'=>$aid,'id_user !='=>$userid);  
    }
    $self = $this->community->get_act_list($aid,$where,1,1);

  //查询电子验证码
  if(!empty($self)){
        $object_type = '';
        foreach($self as $k=>$va){
            $where = 'id_activity = ' . $va['id_activity'];
            $commuint_info = $this->community->get_community_info($where);
            $object_type = $commuint_info[0]['object_type'];
        }

   $code = $this->community->user_code($aid, $userid, '',$object_type);//

   $self[0]['code']=$code;
  }
  
  //$where=array('id_activity'=>$aid,'id_user !='=>$userid);
  $res = $this->community->get_act_list($aid,$where1,$pagesize,$total);
    $data = array_merge($self,$res);
  }else{
    if($_SESSION['identity'] == 'visitor'){
    $where=array('id_activity'=>$aid,'cellphone !='=>$userid);  
    }else{
    $where=array('id_activity'=>$aid,'id_user !='=>$userid);  
    }
    $data = $this->community->get_act_list($aid,$where,$pagesize,$total);
  }
}else{
  $where=array('id_activity'=>$aid);
    $data = $this->community->get_act_list($aid,$where,$pagesize,$total);
  
}

 $status = !empty($data) ? 1 : 0;
 $item = array('status'=>$status,'list'=>$data);
 echo json_encode($item);
 //print_r($data);
  
}

//找回凭证
public function find_code(){
$id_join = $this->input->get('id_join');
$aid = $this->input->get('aid');
$new_phone = $this->input->get('phone');
$phone = $this->community->find_phones($id_join);
//先判断当前手机号码是否锁定
$is_lock = $this->community->is_lock($phone,$aid);
if($is_lock >= 3){
  $data=array('status'=>0); //10分钟限制
  echo json_encode($data);  
  exit;
}

if($new_phone == $phone){
  
  $_SESSION['userid'] = $phone;
  $_SESSION['identity'] = 'visitor';  
  $data=array('status'=>1);  //验证成功
  echo json_encode($data);  
  
}else{
  
  //记录验证
  $is_lock = $this->community->add_lock($phone,$aid);  
  $data=array('status'=>2);  //验证失败
  echo json_encode($data);  
  exit;  
  
}
  
}

//管理员删除参与成员
public function att_del()
{
  
  $aid  = $this->input->get('aid');
  $oid  = $this->input->get('oid');
  $id_join  = $this->input->get('id_join');
  $user_oid  = $this->input->get('user_oid');
  $data = $this->community->att_del($id_join,$aid);
  //活动记录表参与数减一
  $this->community->update_sub($aid);
  //删除对应电子卷
  $this->community->eticket_del($aid,$user_oid);
 
}

//订单列表
public function allorder_list()
{
      $oid = $this->input->get('oid');//微信id; 
      $oid = empty($oid) ? $_SESSION['oid'] : $oid;
        $where = array('object_type'=>'community','buyer'=>$oid);
        /**
         * @查询我的订单。并且分页
         */
         //查询当前用户头像
         $photo = $this->community->photo($oid);
         $this->load->model('list_model','list');
         //查询未支付总数 和 已支付总数
         $paid = $this->list->get_pay_count($where,1);
         $nonpay = $this->list->get_pay_count($where,0);
         $data = $this->list->get_order_list($where,1);
     $ordre_status =  empty($data) ? 0 : 1;
         $this->load->model('chance_model','chance');
         for($i=0;$i<count($data);$i++){
              //$data[$i]['img'] = $this->chance->parse_img_url($data[$i]['img']);
              $data[$i]['pay'] = $data[$i]['total_amount'] -$data[$i]['pay_amount']; 
              //已支付，查询返回的电子券验证码
              if($data[$i]['state']==1){
                   $data[$i]['ecode'] = $this->list->get_ecode($data[$i]['id_object'],$oid);
              }
          }
    $this->smarty->assign('paid',$paid);     //已支付
    $this->smarty->assign('nonpay',$nonpay); //未支付
    $this->smarty->assign('oid',$oid);
    $this->smarty->assign('ordre_status',$ordre_status);
    $this->smarty->assign('photo',$photo);
//        $this->smarty->assign('title',$title);
    $this->smarty->assign('data',$data);
        $this->smarty->view('allorder');

}

//订单列表
public function allorder_list_page()
{
        $this->id_open = $this->input->get('oid');//微信id; 
        $page = $this->input->get('page');
        $where = array('object_type'=>'community','buyer'=>$this->id_open);
        /**
         * @查询我的订单。并且分页
         */
         //查询当前用户头像
         $photo = $this->community->photo($this->id_open);
         $this->load->model('list_model','list');
         //查询未支付总数 和 已支付总数
         $paid = $this->list->get_pay_count($where,1);
         $nonpay = $this->list->get_pay_count($where,0);
         $data = $this->list->get_order_list($where,$page);
         if(empty($data)){
            parent::return_client(0,$data,'当前没有订单！');
         }
         $this->load->model('chance_model','chance');
         for($i=0;$i<count($data);$i++){
              $data[$i]['img'] = $this->chance->parse_img_url($data[$i]['img']);
              $data[$i]['pay'] = $data[$i]['total_amount'] -$data[$i]['pay_amount']; 
              //已支付，查询返回的电子券验证码
              if($data[$i]['state']==1){
                   $data[$i]['ecode'] = $this->list->get_ecode($data[$i]['id_object'],$this->id_open);
              }
          }

        $status = !empty($data) ? 1 : 0;
        $item = array('status'=>$status,'list'=>$data);
        echo json_encode($item);
  
}


    public function detail2($number){
        $b = $this->input->get('b');
        //获取授权用户openid
        $id_open = $this->get_open_ids($number);
        if(!$id_open){
            $id_open = 0;
        }
      
      setcookie('aid', $_COOKIE['aid'], time() + (60 * 60 * 24 * 30), '/', '.hipigo.cn');
//        $this->load->model('business_model','business');
//        $where = 'id_business = '.$b;
//        $merchant = $this->business->get_business_phone($where);

        $url = 'http://'.DOMAIN.'/wapi/'.$b.'/0/community/detail?oid='.$id_open;//$merchant[0]['sld']

        Header('Location:'.$url);

//        $scope = $this->input->get('t');
//        if($scope == 1){
////            $url .= '&scope='.$scope;
//            $url = 'http://'.$merchant[0]['sld'].'/wapi/'.$b.'/0/community/detail?oid='.$id_open['openid']. '&aid='.$aid;
//
//            $this->load->model('hipigouser_model','hipigouser');
//            $where = 'id_open = \'' . $id_open['openid'] . '\'';
//            $is_r = $this->hipigouser->get_hipigo_user_info($where);
//            if(!$is_r){
//                //注册用户
//                $data = array(
//                    'id_open' => $id_open,
//                    'head_image_url' => $id_open['headimgurl'],
//                    'sex' => $id_open['sex'],
//                    'nick_name' => urlencode('嗨皮'.substr($id_open['nickname'],strlen($id_open['nickname'])-4,strlen($id_open['nickname']))),
//                    'created' => date('Y-h-d H:i:s',time()),
//                    'last_login_time' => date('Y-h-d H:i:s',time()),
//                    'id_business' => $this->bid
//                );
//                $this->hipigouser->insert_hipigo_user($data);//注册会员
//            }
//        }

    }


    public function detail3(){
        $b = $this->input->get('b');
//        $parm = $this->input->get('parm');
        $scope = $this->input->get('scope')?$this->input->get('scope'):'';
        
        setcookie('aid', $_COOKIE['aid'], time() + (60 * 60 * 24 * 30), '/', '.hipigo.cn');
        $this->get_open_ids(1,'community_detail',$b,$scope);
    }

    
//活动详情
public function detail()
{
    if(empty($_SERVER['HTTP_REFERER'])){
        $_SESSION['state'] = '';
    }
    $agent = $_SERVER["HTTP_USER_AGENT"];
    $aid = $this->input->get('aid');

	//分享用户
    $suserid = $this->input->get('suserid');
    $type = $this->input->get('type')?$this->input->get('type'):'';
    $from = $this->input->get('from')?$this->input->get('from'):'';
    $this->load->model('hipigouser_model','hipigouser');
    if($_SESSION['is_share'] == 1 && strpos($_SERVER['HTTP_REFERER'],"alipay/return_pay") != false){
    }else{
        $_SESSION['is_share'] = 0;
        $_SESSION['share_aid'] = 0;
    }
  if(empty($_SERVER['HTTP_REFERER']) && strpos($agent,"MicroMessenger")){
        $_SESSION['is_share'] = 1;
        $_SESSION['share_aid'] = $aid;
        if(!empty($suserid)){
            $_SESSION['suserid'] = $suserid;
            $_SESSION['aid'] = $aid;
            $_SESSION['type'] = $type;
            $_SESSION['from_page'] = $from;
        }
    }else{
        $_SESSION['aid'] = 0;
    }

    if($aid) {
      $_COOKIE['aid'] = $aid;
      setcookie('aid', $aid, time() + (60 * 60 * 24 * 30), '/', '.hipigo.cn');
    }
    
    if($_SESSION['suserid'] != '' && empty($_SERVER['HTTP_REFERER'])){
      $aid = $_SESSION['aid'];
      $suserid = $_SESSION['suserid'];
      $type = $_SESSION['type'];
      $from = $_SESSION['from_page'];
    }

  if($_GET['state'] == 'exit'){
         $_SESSION['state'] = 'exit';
    }elseif($_SESSION['state'] != 'exit'){

        if($_COOKIE['userid'] == 'undefined'){
            setcookie('userid', '', time() -1, '/', '');
            setcookie('identity', '', time() -1, '/', '');
            
            header('location:'.$this->url_prefix . $this->bid .'/'.$this->sid.'/community/home?state=exit');
            die;
        }
        if($_COOKIE['userid']&&$_COOKIE['identity'])
        {
            if($_COOKIE['identity']!='visitor'){
                $userid = $_COOKIE['userid'];
            }
        }
        else
        {
            //用户未登录
        }
        //$userid = $_SESSION['userid'];
        $identity = $_SESSION['identity'];

        //$agent = $_SERVER["HTTP_USER_AGENT"];
        if(strpos($agent,"MicroMessenger")){//微信用户往下
            if(!$userid){//判断用户session是否存在，不存在往下
    //        session_start();
                $id_open = $this->input->get('oid');//微信id;
                if(!$id_open){//判断是否有openid。没有则往下读取用户openid
                    Header('Location:http://'.DOMAIN_URL.'/community/detail3?b='.$this->bid);
                    die;
                }
                //如果存在openid。判断openid是否存在于bn_hipigo_user表中
                $where = 'id_open = \'' . $id_open . '\'';
                $is_r = $this->hipigouser->get_hipigo_user_info($where);
                if(!$is_r){//若不存在表中则进入授权页面读取用户信息
                    //获取用户基本信息
                    Header('Location:http://'.DOMAIN_URL.'/community/detail3?b='.$this->bid.'&scope=snsapi_userinfo');
                    die;
                }
                $userid = $is_r[0]['id_user'];

            }else{
              //echo $userid."2";
                //判断用户cookie是否存在，存在往下
                //if($_COOKIE['identity'] != 'visitor'){//用户身份为准用户的往下
                    $data['last_login_time'] =  date('Y-m-d H:i:s', time());
                    $where = 'id_user = ' . $userid;
                    $this->hipigouser->update_hipigo_user($data,$where);//更新会员最后登录时间
                //}
                /*else{
                    $userid = $_COOKIE['userid'];
                }*/
            }
            $_SESSION['userid']= $userid;
            $_SESSION['identity']='wechat';
            setcookie('userid', $userid, time() + (60 * 60 * 24 * 30), '/', '');
            setcookie('identity', 'wechat', time() + (60 * 60 * 24 * 30), '/', '');
      //echo  $_SESSION['userid']."3";
        }else{//非微信用户往下
            if($userid){
                //if($_COOKIE['identity'] != 'visitor'){
                    $data['last_login_time'] = date('Y-m-d H:i:s', time());
                    $where = 'id_user = ' . $userid;
                    $this->hipigouser->update_hipigo_user($data,$where);//更新会员最后登录时间
                //}else{
                //    $userid = $_COOKIE['userid'];
                //}
                $_SESSION['userid']= $userid;
                $_SESSION['identity']='register';
                setcookie('userid', $userid, time() + (60 * 60 * 24 * 30), '/', '');
                setcookie('identity', 'register', time() + (60 * 60 * 24 * 30), '/', '');
            }
        }
    }

  //查询评论表情
   $smile = _get_smile_array();
     $row = array();
         foreach( $smile as $key =>$val){
            $row[$key] = '/smile/'.$val;
         }
     $row = array_slice($row,0,14);
    //更新浏览次数

//    if(!$aid){
//        die('访问出现错误，稍后再试。');
//    }
    if(!$aid){
        $aid = $_COOKIE['aid'];
    }
    $this->community->check_num_update($aid);

  //查询当前活动是否免费并且是否关闭
    $is_free = $this->community->is_att_free($aid);
    $free = $is_free[0]['state'] == 0 ? 0 : 1; //0关闭，1开启
    $data = $this->community->activity_detail($aid);

	$data['content'] = $this->community->replace_smile($data['content']);
	
	if($data['object_type']=='user'){

		$content_img = $this->community->query_spking_imgs($aid);
		$content_imgs ="";

		  foreach($content_img as $k=>$v){
				$img = $v['image_url'];
				$content_imgs = $content_imgs.'<img src="'.get_img_url($img,'community').'"/>';
		  }

		$data['content'] = $data['content'].'<br>'.$content_imgs;
	}

    $data['end_date'] = $data['end_date'] > 0 ? $data['end_date'] : time() + 60*60;
    $data['join_condetion'] = unserialize($data['join_condetion']);
  
  $row_p = explode('.', $data['posters_url']);
  $data['posters_url'] = $row_p[0].'-big.'.$row_p[1];
  //判断当前用户是否已经参加活动
  if(!empty($userid)){
  $is_att_add = $this->community->is_att_add($aid,$userid);
  $is_att_add = !empty($is_att_add) ? 1 : 0; //1为已参加，0为未参加

  if($data['id_business']==$userid){//自己不能参加自己的活动
	$is_att_add =  1;
  }

  }else{
  $is_att_add =  0; //1为已参加，0为未参加
  }
  //当参加人数设置为0时为不限制
  
  
  //判断页面访问来向
  //$host = empty($_SERVER['HTTP_REFERER']) ? 1 : 0; //0返回来源页面，1返回首页
  $host=1;
  $spread = 0;
  $xcode = 0;
  if(!empty($_SERVER['HTTP_REFERER']) && $type=='spread'){
    $host_url = $_SERVER['HTTP_REFERER'];
    $spread = (strpos($host_url,"activity/gifts") != false && $data['is_spread']==1) ? 1 : 0; // 1来之我的口袋的分享活动 ，0普通来源
    if($spread==1){
      //显示分享价格
      //$share_att = $this->community->share_att($aid);
      //$data['join_price'] = $share_att[0]['spread_price'];
    }
  }
  
  if(!empty($_SERVER['HTTP_REFERER']) && empty($type)){
    $host_url = $_SERVER['HTTP_REFERER'];
    $xcode = (strpos($host_url,"activity/gifts") != false) ? 2 : 0; // 2来自我的口袋的普通活动 ，0普通来源
  }
  
  
  //判断当前用户是否已赞过
  $good = $this->community->is_good($aid,$userid);
  $is_good = !empty($good) ? 1: 0; //1为已赞过，0为未赞过
  if(!empty($good)){
    $good ='<a href="javascript:;" class="gray" id="good_yes" data-good='.$data['appraise_count'].'><i class="iconfont icon iconfont_638">&#58884</i>赞'.$data['appraise_count'].'</a>';
    //$good = '<a class="good" id="good_yes" data-good='.$data['appraise_count'].'><i class="iconfont icon iconfont_638">&#58884</i>'.$data['appraise_count'].'</a>';
    $good1 ='<button class="payment" id="good_yes" data-good='.$data['appraise_count'].'><i class="iconfont icon iconfont_638">&#58884</i>已赞'.$data['appraise_count'].'</button>';
  }else{
    $good = '<a href="javascript:;" class="gray" id="goods" data-good='.$data['appraise_count'].'><i class="icon-thumbs-up gray"></i>赞'.$data['appraise_count'].'</a>';
    //$good='<a class="good" id="goods" data-good='.$data['appraise_count'].'><i class="icon-thumbs-up"></i>'.$data['appraise_count'].'</a>';
    $good1='<button class="payment" id="goods1" data-good='.$data['appraise_count'].'><i class="icon-thumbs-up"></i>赞'.$data['appraise_count'].'</button>';
  }
  //判断访问者来源
  if(isMobile())
    {
      $anent = 1; //手机用户
    }else{
      $anent = 0; //PC用户
    }
  //查询当前活动是否有评论
  $spking = $this->community->spking_count($aid);
  $this->smarty->assign('spking_count',$spking);
  $spking = empty($spking) ? 0 : 1; //0为没有评论，1为有评论
  //查询参与人数
  $join_num  = $this->community->find_join_num($aid);
  //世界杯活动查询中奖者
  if($data['type'] ==1){
  $win_num = $this->community->find_win_num($aid);
  $win_num  =  empty($win_num) ? 0 : $win_num;    
  }
  
  //echo $_SERVER['userid']."1<br>". $_SESSION['suserid']."<br>2";
  //exit;
  //分享用户价格加1
  //echo  $_SESSION['userid'].'4';

  /*
  if(((!empty($_SESSION['aid']) && $_SESSION['aid']==$data['id_activity']) ||  ($_SESSION['share_aid'] == $data['id_activity'])) && $data['join_price']>0 && (empty($_SERVER['HTTP_REFERER']) || $_SESSION['is_share'] == 1)){
   $pay_share =   $_SESSION['userid'] != $_SESSION['suserid'] && !empty($_SESSION['userid']) ? $data['join_price']+PAY_SHARE : $data['join_price'];
   //echo $_SESSION['userid']."5"."<br>".$_SESSION['suserid']."6";
   $data['join_price'] = $pay_share;
  }
  */
  //查询活动来自商家名称

  $business_name = '';
  if($data['object_type'] == 'community'){
      $business_name = $this->community->find_business_name($data['id_business']);
  }else{
      $where = 'id_user = ' . $data['id_business'];
      $user_info = $this->hipigouser->get_hipigo_user_info($where);
      if($user_info){
          $business_name = $user_info[0]['nick_name'];
      }
  }
  
  $now_time =time();
  $end_time = $data['end_date'];
  $start_time = $data['start_date'];

  if($now_time > $end_time){
		$data['kill_time'] = 0;
  }elseif($now_time < $end_time&&$now_time > $start_time){
		$data['kill_time'] = 1;
  }elseif($now_time < $start_time){
		$data['kill_time'] = date('Y/m/d H:i:s',$start_time);
  }

  $data['header_img'] = $this->showImage($aid,"community");

  $this->smarty->assign('title', $data['name']);
  $this->smarty->assign('business_name',$business_name);
  $this->smarty->assign('host',$host);
  $this->smarty->assign('type',$type);
  $this->smarty->assign('from',$from);//用来标示来自分享的详情 还是正常进入详情  timeline(singlemessage):分享进入 为空则正常进入
  $this->smarty->assign('xcode',$xcode); //来之我的口袋的普通活动
  $this->smarty->assign('spread',$spread);
  $this->smarty->assign('win_num',$win_num);
  $this->smarty->assign('join_num',$join_num);
  $this->smarty->assign('anent',$anent);
  $this->smarty->assign('userid',$userid);
  $this->smarty->assign('identity',$identity);
  $this->smarty->assign('free',$free);
  $this->smarty->assign('spking',$spking);
  $this->smarty->assign('is_good',$is_good);
	if($data['type']==2){
		$data['join_type_price']=$data['preferential_price'];
	}else{
		$data['join_type_price']=$data['join_price'];
	}
  $this->smarty->assign('data',$data);
  $this->smarty->assign('aid',$aid);
  $this->smarty->assign('bid',$this->bid);
  $this->smarty->assign('oid',$id_open);
  $this->smarty->assign('row',$row);
  $this->smarty->assign('good',$good);
  $this->smarty->assign('is_att_add',$is_att_add);
  $this->smarty->assign('good1',$good1);
  $this->smarty->assign('bn_img',$bn_img);
  $this->smarty->assign('num',rand(100,999));

  if(TPL_DEFAULT == 'default'){
      $this->smarty->view('community_detail1');
  } else {

    $n_start_date = date('Y/m/d',$data['start_date']);
    $n_end_date = date('Y/m/d',$data['end_date']);
    $n_id_resources = count(unserialize($data['id_resource']));
    
    $select = 'id_user';
    $where = 'id_object ='.$aid;
    $result = $this->community->getAppraises($select,$where);

    $n_appraises_counts = count($result);
    $this->load->model('user_model','user');
	$n_appraises_name = "";
    
    if($n_appraises_counts>1){

		  foreach($result as $k=>$v){

				$i=1;
				$id_user_app = $v['id_user'];
				
				if($i=3){break;}

				if($id_user_app!='0'){

					$appraises_name = $this->user->get_userinfo_details($id_user_app);
					$result[$k]['n_appraises_name'] = urldecode($appraises_name[0]['nick_name']);
					
					if($k==1){
						$n_appraises_name = '<a href="'.$url.'/user_activity/index?userid='.$v['id_user'].'">'.$result[$k]['n_appraises_name'].'</a>';
					}else{
						$n_appraises_name = $n_appraises_name."、".'<a href="'.$url.'/user_activity/index?userid='.$v['id_user'].'">'.$result[$k]['n_appraises_name'].'</a>';
					}

					$i = $i+1;

				}
		  }
    }

    $this->load->model('eticket_item_model','eticket_item');
    $et_page = $this->input->get('et_page')?$this->input->get('et_page'):1;
    $et_page = 25*$et_page;
    $join_users  = $this->community->find_join_users($aid,$et_page);

    foreach($join_users as $k=>$v){
      $id_user = $v['id_user'];
      
      $user_name = $this->user->get_userinfo_details($id_user);
      $eticket_users[$k]['name'] = urldecode($user_name[0]['nick_name']);
	  
	  if($this->userid == $id_user) {
      
      $where = 'id_object = ' . $v['id_activity'] . ' AND id_customer = '.$id_user;
		  $eticket_users[$k]['eticket_item'] = $this->eticket_item->getEticketItemCode($where);
		  $eticket_user = $eticket_users[$k]['eticket_item'];
		  $eticket_users[$k]['eticket_user_counts'] = count($eticket_user);
      
	  }

	  $header_img = $this->showImage($id_user,"user");
      $eticket_users[$k]['header_img'] =  $header_img;
      $eticket_users[$k]['id_user'] =  $id_user;
	  $eticket_users[$k]['eticket_type'] =  $eticket_user[0]['object_type'];  

    }

    $ct_page = $this->input->get('ct_page')?$this->input->get('ct_page'):1;
    $ct_page = 25*$ct_page;
    $spking_users = $this->community->spking_users($aid,$ct_page);
	
    foreach($spking_users as $k=>$v){

      $id_review = $v['id_review'];
	   
	  $review_img = $this->community->query_spking_imgs($id_review);
	  $review_imgs ="";

	  foreach($review_img as $ki=>$vi){

			$review_imgs = $review_imgs.'<img src="http://'.$_SERVER['HTTP_HOST'].$vi['image_url'].'"/>';

	  }

      $spking_users[$k]['review_imgs'] = $review_imgs;      
      $now_time = time();
      $review_time  = $v['created'];
	  $update_skping_time = $now_time-$review_time;

      if($update_skping_time>300){
        $spking_users[$k]['created'] = date('Y/m/d',$review_time);
      }else if($update_skping_time>1&&$update_skping_time<100){
        $spking_users[$k]['created'] = '刚刚';
      }else{
        $spking_users[$k]['created'] = '5分钟前';
      }

      $id_user = $v['id_customer'];

		if($id_user==null||$id_user==0){
		  $spking_users[$k]['name'] = '匿名';
		}else{
		  $user_name = $this->user->get_userinfo_details($id_user);
		  $spking_users[$k]['name'] = urldecode($user_name[0]['nick_name']);
		}


	  $header_img = $this->showImage($id_user,"user");
      $spking_users[$k]['header_img'] = $header_img;

      $rt_page = $this->input->get('rt_page')?$this->input->get('rt_page'):1;
      $rt_page = 3*$rt_page;
      $spking_users_review = $this->community->spking_users_review($id_review,$rt_page);
      $spking_users[$k]['spking_users_review_count'] = count($spking_users_review);

      foreach($spking_users_review as $ks=>$vs){
        
        $id_user = $vs['id_customer'];

		if($id_user==null||$id_user==0){
			$spking_users[$k]['review'][$ks]['name'] = '匿名';
		}else{
			$user_name = $this->user->get_userinfo_details($id_user);
			$spking_users[$k]['review'][$ks]['name'] = urldecode($user_name[0]['nick_name']);
		}

        $spking_users[$k]['review'][$ks]['content'] = $vs['content'];
		$now_time_r = time();
        $review_time_hf  = $vs['created'];  
	    $update_skping_time_r = $now_time_r-$review_time_hf;		

		if($update_skping_time_r>300){
			$spking_users[$k]['review'][$ks]['created'] =  date('Y/m/d',$review_time_hf);
		}else if($update_skping_time_r>1&&$update_skping_time_r<100){
			$spking_users[$k]['review'][$ks]['created'] = "刚刚";
		}else{
			$spking_users[$k]['review'][$ks]['created'] = "5分钟前";
		}

        $spking_users[$k]['review'][$ks]['id_parent'] =  $vs['id_parent'];

      }
      
    }

  if($data['object_type'] == 'user') {
    $header_img = $this->showImage($data['id_business'], "user",array(50,50));
  }
  else
    $header_img = $this->showImage($data['id_activity'], "community",array(50,50));

    $this->smarty->assign('header_img',$header_img);
    $this->smarty->assign('spking_users',$spking_users);
    $this->smarty->assign('spking_users_count',count($spking_users));
    $this->smarty->assign('eticket_users',$eticket_users);
    $this->smarty->assign('eticket_users_count',count($eticket_users));
    $this->smarty->assign('n_appraises_name',$n_appraises_name);
    $this->smarty->assign('n_id_resources',$n_id_resources);
    $this->smarty->assign('n_start_date',$n_start_date);
    $this->smarty->assign('n_end_date',$n_end_date);

	$tag = $this->community->get_activity_tag($aid);
	$tags = "";

	if(count($tag)>1){

		foreach($tag as $k=>$v){

			$tags = $tags." ".$v['tag_name'];

		}

	}else{

		$tags = $tag[0]['tag_name'];

	}

	$this->smarty->assign('tags',$tags);

	$resource_count = $this->community->get_activity_resource($aid);
	$this->smarty->assign('resource_count',count($resource_count));
	
	$is_new_activity = false;
	if($this->input->get('type')=='new'){
		$this->smarty->assign('is_new_activity',true);
	}



    $this->media('details.css', 'css');
    $this->media('details.js', 'js');  

    setcookie('aid', '', time() -1, '/', '');
	
	if($data['type']==2){
		$this->smarty->view('seckill_details');
	}else{
		$this->smarty->view('active_details');
	}

  }

}


//查询评论人数
public function spking_count(){

  $aid = $this->input->get('aid');
  $spking = $this->community->spking_count($aid);
  echo $spking;

}

//查询参加人数
public function find_join_num(){

  $aid = $this->input->get('aid');
  $join_num  = $this->community->find_join_num($aid);
  echo $join_num;

}


//活动详情参加用户列表
public function list_users(){

    $aid = $this->input->get('aid');
    $this->load->model('user_model','user');
    $this->load->model('eticket_item_model','eticket_item');
    $et_page = $this->input->get('et_page')?$this->input->get('et_page'):1;
    $et_page = 25*$et_page;
    $join_users  = $this->community->find_join_users($aid,$et_page);

    foreach($join_users as $k=>$v){
      $id_user = $v['id_user'];

      $user_name = $this->user->get_userinfo_details($id_user);
      $eticket_users[$k]['name'] = urldecode($user_name[0]['nick_name']);

	  if($this->userid == $id_user) {
		  $where = 'id_object = ' . $v['id_activity'] . ' AND id_customer = '.$id_user;
		  $eticket_users[$k]['eticket_item'] = $this->eticket_item->getEticketItemCode($where);
		  $eticket_user = $eticket_users[$k]['eticket_item'];
		  $eticket_users[$k]['eticket_user_counts'] = count($eticket_user);
	  }
	  $header_img = $this->showImage($id_user,"user");
      $eticket_users[$k]['header_img'] =  $header_img;
      $eticket_users[$k]['id_user'] =  $id_user;
	  $eticket_users[$k]['eticket_type'] =  $eticket_user[0]['object_type'];  

    }

    echo json_encode($eticket_users);  

}





//活动详情评论列表
public function list_spking(){

    $aid = $this->input->get('aid');
    $ct_page = $this->input->get('ct_page')?$this->input->get('ct_page'):1;
    $ct_page = 25*$ct_page;
    $this->load->model('user_model','user');
    $spking_users = $this->community->spking_users($aid,$ct_page);

    foreach($spking_users as $k=>$v){

      $id_review = $v['id_review'];

	  $review_img = $this->community->query_spking_imgs($id_review);
	  $review_imgs ="";

	  foreach($review_img as $ki=>$vi){

			$review_imgs = $review_imgs.'<img src="http://'.$_SERVER['HTTP_HOST'].$vi['image_url'].'"/>';

	  }

      $spking_users[$k]['review_imgs'] = $review_imgs;     
      
      $now_time = time();
      $review_time  = $v['created'];

	  $update_skping_time = $now_time-$review_time;

      if($update_skping_time>300){
        $spking_users[$k]['created'] = date('Y/m/d',$review_time);
      }else if($update_skping_time<100){
        $spking_users[$k]['created'] = '刚刚';
      }else{
        $spking_users[$k]['created'] = '5分钟前';
      }

      $id_user = $v['id_customer'];
		if($id_user==null||$id_user==0){
		  $spking_users[$k]['name'] = '匿名';
		}else{
		  $user_name = $this->user->get_userinfo_details($id_user);
		  $spking_users[$k]['name'] = urldecode($user_name[0]['nick_name']);
		}

	  $header_img = $this->showImage($id_user,"user");
      $spking_users[$k]['header_img'] = $header_img;

      $rt_page = $this->input->get('rt_page')?$this->input->get('rt_page'):1;
      $rt_page = 3*$rt_page;
      $spking_users_review = $this->community->spking_users_review($id_review,$rt_page);
      $spking_users[$k]['spking_users_review_count'] = count($spking_users_review);

      foreach($spking_users_review as $ks=>$vs){
        
			$id_user = $vs['id_customer'];
			if($id_user==null||$id_user==0){
				$spking_users[$k]['review'][$ks]['name'] = '匿名';
			}else{
				$user_name = $this->user->get_userinfo_details($id_user);
				$spking_users[$k]['review'][$ks]['name'] = urldecode($user_name[0]['nick_name']);
			}
			$spking_users[$k]['review'][$ks]['content'] = $vs['content'];
			$now_time_r = time();
			$review_time_hf  = $vs['created'];  
			
			$update_skping_time_r = $now_time_r-$review_time_hf;		

			if($update_skping_time_r>300){
				$spking_users[$k]['review'][$ks]['created'] =  date('Y/m/d',$review_time_hf);
			}else if($update_skping_time_r<100){
				$spking_users[$k]['review'][$ks]['created'] = "刚刚";
			}else{
				$spking_users[$k]['review'][$ks]['created'] = "5分钟前";
			}

			$spking_users[$k]['review'][$ks]['id_parent'] =  $vs['id_parent'];

      }
      
    }

    echo json_encode($spking_users);  

}


//活动详情回复列表
public function list_spking_review(){
	
	  $id_review = $this->input->get('id_review');
      $rt_page = $this->input->get('rt_page')?$this->input->get('rt_page'):1;
      $rt_page = 3*$rt_page;
      $rt_page_h = 3*$rt_page*2;
	  $this->load->model('user_model','user');
      $spking_users = $this->community->spking_users_review($id_review,$rt_page);
      $spking_users_h = $this->community->spking_users_review($id_review,$rt_page_h);
	  $spking_users_review_count_h = count($spking_users_h);
	  $spking_users_review_count = count($spking_users);

	  if($spking_users_review_count_h > $spking_users_review_count){
		  $is_spking_users_review = true;
	  }

      foreach($spking_users as $k=>$vs){
				
				$id_user = $vs['id_customer'];

				if($id_user==null||$id_user==0){
					$spking_users_review[$k]['name'] = '匿名';
				}else{
					$user_name = $this->user->get_userinfo_details($id_user);
					$spking_users_review[$k]['name'] = urldecode($user_name[0]['nick_name']);
				}

				$spking_users_review[$k]['content'] = $vs['content'];
				$now_time_r = time();
				$review_time_hf  = $vs['created'];  
				
				$update_skping_time_r = $now_time_r-$review_time_hf;		

				if($update_skping_time_r>300){
					$spking_users_review[$k]['created'] =  date('Y/m/d',$review_time_hf);
				}else if($update_skping_time_r<100){
					$spking_users_review[$k]['created'] = "刚刚";
				}else{
					$spking_users_review[$k]['created'] = "5分钟前";
				}

				$spking_users_review[$k]['id_parent'] =  $vs['id_parent'];
				$spking_users_review[$k]['num'] =  $rt_page;
				$spking_users_review[$k]['count'] =  $spking_users_review_count;
				$spking_users_review[$k]['is_review']	=	$is_spking_users_review;

			}


    echo json_encode($spking_users_review);  

}



//回复详情
public function reply_detail()
{
    $userid = $_SESSION['userid'];
  $identity = $_SESSION['identity'];
  $aid = $this->input->get('aid');
  $sk_id = $this->input->get('sk_id');
  $oid = $this->input->get('oid');
  //查询评论表情
   $smile = _get_smile_array();
     $row = array();
         foreach( $smile as $key =>$val){
            $row[$key] = '/smile/'.$val;
         }
     $row = array_slice($row,0,14);
  
  //查询当前活动是否免费并且是否关闭
    $is_free = $this->community->is_att_free($aid);
    $free = $is_free[0]['state'] == 0 ? 0 : 1; //0关闭，1开启
  //获取活动信息
  $data = $this->community->activity_detail($aid);
    $data['end_date'] = $data['end_date'] > 0 ? $data['end_date'] : time() + 60*60;
  //获取当前评论信息
  $sk = $this->community->spking_list($this->bid,$aid,$pagesize=0,$total=0,$sk_id);
  //获取回复总数
  $reply_num = $this->community->reply_num($sk_id);
    //查询参与人数
    $join_num  = $this->community->find_join_num($aid);
  
  //评论图文情况
  if(!empty($sk['image_url'])){
    $sk['content'] = $sk['content'].'<br>'.'<span id="show_big"><img src="'.$sk['image_url'].'" class="tip" class="imgcon"/></span>';
  }
  
  //判断当前用户是否已赞过
  $good = $this->community->is_good($aid,$userid);
  $is_good = !empty($good) ? 1: 0; //1为已赞过，0为未赞过
  if(!empty($good)){
    $good1 ='<button class="payment" id="good_yes" data-good='.$data['appraise_count'].'><i class="iconfont icon iconfont_638">&#58884</i>已赞'.$data['appraise_count'].'</button>';
  }else{
    $good1='<button class="payment" id="goods1" data-good='.$data['appraise_count'].'><i class="icon-thumbs-up"></i>赞'.$data['appraise_count'].'</button>';
  }

  //判断当前用户是否已经参加活动
  if(!empty($userid)){
  $is_att_add = $this->community->is_att_add($aid,$userid);
  $is_att_add = !empty($is_att_add) ? 1 : 0; //1为已参加，0为未参加  
  }else{
  $is_att_add =  0; //1为已参加，0为未参加    
  }
    $title= $data['name'];
  //分享用户价格加1
  if(!empty($_SESSION['aid']) && $_SESSION['aid']==$data['id_activity'] && $data['join_price']>0){
   $pay_share =   $_SESSION['userid'] != $_SESSION['suserid'] && !empty($_SESSION['userid']) ? $data['join_price']+PAY_SHARE : $data['join_price'];
   $data['join_price'] = $pay_share;
  }
  $this->smarty->assign('identity',$identity);
  $this->smarty->assign('userid',$userid);
  $this->smarty->assign('title',$title);
  $this->smarty->assign('free',$free);
  $this->smarty->assign('data',$data);
    $this->smarty->assign('join_num',$join_num);
  $this->smarty->assign('sk',$sk);
  $this->smarty->assign('reply_num',$reply_num);
  $this->smarty->assign('aid',$aid);
  $this->smarty->assign('oid',$oid);
  $this->smarty->assign('row',$row);
  $this->smarty->assign('good',$good);
  $this->smarty->assign('good1',$good1);
    $this->smarty->assign('is_att_add',$is_att_add);
  $this->smarty->assign('num',rand(100,999));
    $this->smarty->view('reply_detail');

}

//获取回复列表
public function reply_list()
{
$aid = $this->input->get('aid');
$sk_id = $this->input->get('sk_id');
$oid = $this->input->get('oid');
$page = $this->input->get('page');
$page = empty($page) ? 1 : $page;
$pagesize = 25;
$total = $pagesize * ($page > 0 ? ($page - 1) : 0);
$data = $this->community->reply_list_page($aid,$pagesize,$total,$sk_id);
if(!empty($data))
{
    $data=array('status'=>1,'list'=>$data);
    echo json_encode($data);
}
}

//获取评论和回复列表
public function spking_list()
{
  $bid=$this->bid;
  $sid=$this->sid;
  $aid = $this->input->get('aid');
    $page = $this->input->get('page');
  $page = empty($page) ? 1 : $page;
  $pagesize = 25;
  $total = $pagesize * ($page > 0 ? ($page - 1) : 0);
  //评论
    $data = $this->community->spking_list($bid,$aid,$pagesize,$total);
  //当前列表评论的回复
  $row = $this->community->reply_list($aid);
    $this->load->model('business_model','business');
    $this->load->model('hipigouser_model','hipigouser');
  foreach ($data as $key => $value) {
    foreach ($row as $k => $v) {
      if($value['id_review'] == $v['id_parent']){
        $data[$key]['reply'][]= $v;
      }
    }
    if(!isset($data[$key]['reply'])){
      $data[$key]['reply']='';
    }
        $where = 'object_type = "review" and id_object = ' . $value['id_review'];
        $att_result = $this->business->get_business_attachment($where);
        $data[$key]['image'] = '';
        if($att_result){
            $img = array();
            foreach($att_result as $ki=>$vi){
                $img[$ki] = get_img_url($vi['image_url'],'community');
            }
            $data[$key]['image'] = $img;
        }

        if($value['id_customer']){
            $where_user = 'id_user = ' . $value['id_customer'];
            $user_info = $this->hipigouser->get_hipigo_user_info($where_user);
            if($user_info){
                $data[$key]['name'] = urldecode($user_info[0]['nick_name']);
            }
        }
  }

  $data=array('status'=>1,'list'=>$data);
  echo json_encode($data);
  
  
}

//赞
public function good()
{
  $userid = $_SESSION['userid'];  
  $aid = $this->input->get('aid');
  //$userid = $this->input->get('userid');
  $userid = empty($userid) ? 0 : $userid;
  //更新赞+1
    $this->community->good($aid);
  //插入活动赞记录
  $this->community->good_add($aid,$userid);  

  if($this->input->get('count')=='true'){

	 $data = $this->community->activity_detail($aid);
	 echo json_encode($data);
  }

}


//取消赞
public function good_del()
{
  $userid = $_SESSION['userid'];
  $aid = $this->input->get('aid');
  $userid = empty($userid) ? 0 : $userid;
  //$oid = $this->input->get('oid');
  if($userid !=0){
  //更新赞-1
    $data1 = $this->community->good_sub($aid);
  //删除活动赞记录
  $data2 = $this->community->good_del($aid,$userid);
  }else{
    
  //更新赞-1
    $data1 = $this->community->good_sub($aid);
  //删除活动赞记录(只删除一条)
  $data2 = $this->community->good_del($aid,$userid);  
  }

  if($this->input->get('count')=='true'){

	 $data = $this->community->activity_detail($aid);
	  echo json_encode($data);
  }

}


//评论添加
public function spking()
{
  $userid = $_SESSION['userid'];  
  $bid=$this->bid;
  $sid=$this->sid;
  $aid = $this->input->get('aid');
  $sp_rp = $this->input->get('sp_rp');
  $content = $this->input->get('content');
  $user_name = $this->input->get('user_name');
  $imgs = $this->input->get('imgs');

  
  if(!empty($user_name) && $user_name != 'undefined')
  {
		$content = '回复  <font color="#f24949">'.$user_name.'</font>: '.$content;
  }else{
		$content = $content;  
  }
  //查询用户昵称
    if(!empty($userid) && $_SESSION['identity'] !='visitor'){
    $user_info = $this->community->new_user_header($userid,1);
    $name = urldecode($user_info['nick_name']);  
    }else{
     $name = '匿名';
    }
    
    //替换表情
  $content = $this->community->replace_smile($content);
  $arr = array('name'=>$name,'content'=>$content); 
  
  //插入评论或回复
  $spking_id = $this->community->spking_add($aid,$userid,$bid,$sid,$content,urlencode($name),$sp_rp);
    if($spking_id)
  {
    //插入评论图片  
    if(!empty($imgs) && $imgs != 'undefined')
      {
            $img_arr = explode(',', $imgs);
            foreach ($img_arr as $key => $value) {
             if(!empty($value)){
              $img_rs=array('id_business'=>$this->bid,
              'id_shop'=>$this->sid,
              'object_type'=>'review',
              'id_object'=>$spking_id,
              'attachment_type'=>'image',
              'image_url'=>$value,
              'size'=>0);
              $this->community->insert_imgs($img_rs);
             }
            }
      }
    echo json_encode($arr);
  }
  //更新最后讨论日期
  $this->community->update_user_spking($aid,$userid);
  //更新评论数
  if($sp_rp==0){
  $this->community->spking_update($aid);  
  }
}

//授权协议
public function user_agreement(){
$aid = $this->input->get('aid');
$suserid = $this->input->get('suserid');
$suserid = empty($suserid) ? '' : $suserid;
$this->smarty->assign('aid',$aid);
$this->smarty->assign('suserid',$suserid);

if(TPL_DEFAULT == 'default'){
	$this->smarty->view('user_agreement');  
}else{
    $this->media('layout_section.css', 'css');
	$this->smarty->view('user_agreement');  
}

}

//参加活动
public function att_activity()
{
  $userid = $_SESSION['userid'];  

  $bid=$this->bid;
  $sid=$this->sid;
  $aid = $this->input->get('aid');
  $phone = $this->input->get('phone');
  $oid = $this->input->get('oid');
  $ac_num = $this->input->get('ac_num');

  $from = $this->input->get('from');//标示来自分享 还是正常进入
  $spread = $this->input->get('type');
  $p_type = $this->input->get('p_type');
  //$p_type = !empty($p_type) ? $p_type : 0;
  $type = $this->input->get('type');
  $xcode = $this->input->get('xcode');
  $join_price_type = $this->input->get('join_price_type');
  
  //处理加入条件
  $condetion = $this->input->get('condetion');
  $join = array();
  if($condetion) {
    foreach ($condetion as $k => $v) { 
      $condition = explode('&@&', $v);
      $join[$condition[0]] = $condition[1];
    }
  }

  $phone = (empty($phone) || $phone=='undefined') ? '' : $phone;
  //查询当前活动是否免费并且是否关闭
    $is_free = $this->community->is_att_free($aid);
     //推广的免费活动
    $share_att = $this->community->share_att($aid);
  //活动剩余次数
  $ac_num = !empty($ac_num) ? $ac_num : 1;
  $total = $is_free[0]['total']-$is_free[0]['join_count']-$ac_num;
  if($total < 0 &&  $is_free[0]['total'] == 0){
  $data=array('status'=>5);  //活动剩余数不足
    echo json_encode($data);
  exit;
  }

  //来自我的口袋分享的免费活动
  if(!empty($type) && $is_free[0]['is_spread'] ==1 && $is_free[0]['join_price'] == 0 && ($share_att[0]['spread_price'] ==0 || $from)){
    $is_att_once =  $this->community->is_att_once($aid,$userid,$phone);
        if($is_att_once>0){
      $data=array('status'=>3);  //手机或用户已参加过改活动，免费活动只能才加一次
    echo json_encode($data);
      exit;  
    }
  }
  
  //来之我的口袋普通免费活动
  if($xcode==2 && $is_free[0]['join_price']==0){
    $is_att_once =  $this->community->is_att_once($aid,$userid,$phone);
        if($is_att_once>0){
      $data=array('status'=>3);  //手机或用户已参加过改活动，免费活动只能才加一次
    echo json_encode($data);
      exit;  
    }
  }
  
  //正常入口的免费活动只能参加一次
    if($type !='spread' && $is_free[0]['join_price']==0){
  $is_att_once =  $this->community->is_att_once($aid,$userid,$phone);
    if($is_att_once>0){
      $data=array('status'=>3);  //手机或用户已参加过改活动，免费活动只能才加一次
    echo json_encode($data);
      exit;  
  }
  }
  //准用户第二次参加收费活动
  if($_SESSION['identity'] =='visitor' || empty($userid)){
        $phone = !empty($phone) ? $phone : $userid;
        $is_att = $this->community->find_phone($aid,$phone);
        if($is_att>0){
            $data=array('status'=>4);  //准用户第二次参加，请登录
            echo json_encode($data);
            exit;
        }
  }

  //记录准会员(这里不管是否参加成功都会记录cookie)   //参加商家的活动的时候就会在活动成员列表出现准会员用户，。其实参加的是注册用户  zxx注释
//  if($is_free[0]['state'] != 0  && $phone !=''){
//    $_SESSION['userid'] = $phone;
//      $_SESSION['identity'] = 'visitor';
//  }

    /* 更新參加活動時間 start*/
    $where_j = 'id_activity = ' . $aid;
    if($phone){
        $where_j .= ' and cellphone = \''.$phone.'\'';
    }elseif($userid){
        $where_j .= ' and id_user = ' . $userid;
    }
    $data_u = array('update_time'=>date('Y-m-d H:i:s', time()));
    $this->community->update_community_join($data_u,$where_j);
    /* 更新參加活動時間 end*/

//    $this->load->model('helper_model','helper');

    $sendto = $phone;
    if(!$sendto){
        $sendto = $join['电话号码'];
    }

    $_SESSION['sendto'] = $sendto;//用于收费活动发送消费码问题
  //来自我的口袋的普通活动
    if($is_free[0]['join_price']==0 && $is_free[0]['state'] != 0 && ($xcode==2 || ($xcode==0 && $from)))
  {
     //产生一张电子卷
     $data = $this->community->set_eticket($aid,$userid,$bid,$sid,$phone, $join);  
     if($data)
     {
           //发送消费码至手机
           $this->sendcode($sendto,$data,'activity',$is_free);
//           $res = $this->helper->send_code($phone);
           $data=array('status'=>1); //免费活动参加成功
        echo json_encode($data);
        exit;
     }
  }
    if($is_free[0]['is_spread'] ==1 && $is_free[0]['state'] != 0 && $xcode==0 && $from == '' && $type == 'spread' && $share_att[0]['spread_price'] == 0){//我的口袋 推广活动
        $is_att_once =  $this->community->is_att_once($aid,$userid,$phone);
        if($is_att_once>0){
            $data=array('status'=>3);  //手机或用户已参加过改活动，免费活动只能才加一次
            echo json_encode($data);
            exit;
        }

        //产生一张电子卷
        $data = $this->community->set_eticket($aid,$userid,$bid,$sid,$phone, $join);
        if($data)
        {
            //发送消费码至手机
            $this->sendcode($sendto,$data,'activity',$is_free);
            $data=array('status'=>1); //免费活动参加成功
            echo json_encode($data);
            exit;
        }
    }


  //来之朋友圈的分享免费活动
  if(!empty($_SESSION['aid']) && !empty($_SESSION['userid']) && $is_free[0]['join_price']==0 && empty($type)){
     //产生一张电子卷
     $data = $this->community->set_eticket($aid,$userid,$bid,$sid,$phone, $join);  
     if($data)
     {
           //发送消费码至手机
           $this->sendcode($sendto,$data,'activity',$is_free);
          $data=array('status'=>1); //免费活动参加成功
        echo json_encode($data);
        exit;
     }
  }

    //普通活动
    if($is_free[0]['join_price']==0 && $is_free[0]['state'] != 0 && empty($type))//&& $is_free[0]['is_spread'] !=1
  {
     //产生一张电子卷
     $data = $this->community->set_eticket($aid,$userid,$bid,$sid,$phone, $join);
     if($data)
     {
           //发送消费码至手机
           $this->sendcode($sendto,$data,'activity',$is_free);
          $data=array('status'=>1); //免费活动参加成功
        echo json_encode($data);
        exit;
     }
    
   //活动关闭
  }else if($is_free[0]['state'] == 0){
      $data=array('status'=>2);               //活动已关闭
    echo json_encode($data);
      exit;
  
  }else if($_SESSION['aid'] == $aid && !empty($_SESSION['aid'])){
    //收费活动，进入支付流程（分享活动）
    $url = '/wapi/'.$bid.'/'.$sid.'/order/pay?aid='.$aid.'&ac_num='.$ac_num.'&type='.$join_price_type.'&phone='.$phone;
    $data=array('status'=>0,'url'=>$url);
    echo json_encode($data);
    exit;
  }else{
    //收费活动，进入支付流程(普通活动和推广活动)
    $url = '/wapi/'.$bid.'/'.$sid.'/order/pay?aid='.$aid.'&ac_num='.$ac_num.'&type='.$join_price_type.'&phone='.$phone;
    $data=array('status'=>0,'url'=>$url);
    echo json_encode($data);
    exit;
  }
  
}


//参加活动 我的口袋
//参加活动
public function att_activity_gifts()
{
  $userid = $_SESSION['userid'];  
  $bid=$this->bid;
  $sid=$this->sid;
  $aid = $this->input->get('aid');
  $phone = $this->input->get('phone');
  $oid = $this->input->get('oid');
  $ac_num = $this->input->get('ac_num');
  $spread = $this->input->get('type');
  $p_type = $this->input->get('p_type');
  $p_type = !empty($p_type) ? $p_type : 0;
  $type = $spread == 'spread' ? $spread : 0;

  $phone = (empty($phone) || $phone=='undefined') ? '' : $phone;
  //查询当前活动是否免费并且是否关闭
    $is_free = $this->community->is_att_free($aid);
  //查询分享活动
  $share_att = $this->community->share_att($aid);

    /* 更新參加活動時間 start*/
    $where_j = 'id_activity = ' . $aid;
    if($phone){
        $where_j .= ' and cellphone = \''.$phone.'\'';
    }elseif($userid){
        $where_j .= ' and id_user = ' . $userid;
    }
    $data_u = array('update_time'=>date('Y-m-d H:i:s', time()));
    $this->community->update_community_join($data_u,$where_j);
    /* 更新參加活動時間 end*/

  //免费活动只能参加一次
  if($share_att[0]['spread_price']==0){
  $is_att_once =  $this->community->is_att_once($aid,$userid,$phone);  
  
  if($is_att_once>0){
      $data=array('status'=>3,'msg'=>'免费活动只能参加一次哦！');  //手机或用户已参加过改活动，免费活动只能才加一次
    echo json_encode($data);
      exit;  
  }
  }

    if($share_att[0]['spread_price']==0)
  {
     //产生一张电子卷
     $data = $this->community->set_eticket($aid,$userid,$bid,$sid,$phone);  
     if($data)
     {
           //发送消费码至手机
           $this->sendcode($phone,$data,'activity',$is_free);
          $data=array('status'=>1,'msg'=>''); //免费活动参加成功
        echo json_encode($data);
        exit;
     }
    
  
  }else{
    //收费活动，进入支付流程
    $url = '/wapi/'.$bid.'/'.$sid.'/order/pay?aid='.$aid.'&ac_num='.$ac_num.'&type='.$type;
    $data=array('status'=>0,'url'=>$url);
    echo json_encode($data);
    exit;
  }
  
}

  /**
   * 发起活动
   * @version 2.0
   * @author Jamai
   */
  public function publish()
  {
    /*
     1.判断用户登录
     2.判断用户是否是授权发布活动用户
     2.呈现页面
    */
    if( ! $this->userID) //用户未登录，跳转到登录页面后，返回到当前页面
      $this->redirect('/user/login');
    
    $data_page = array();
    $this->load->model('resources_model', 'resources');
    $this->load->model('hipigouser_model', 'user');
    $this->load->model('tag_model', 'tag');
    
    //可修改为 role 为SESSION，目前按照以前的方式
    $isRole = $this->user->is_role($this->userID);
    if( ! $isRole)  //不是达人(未授权发活动)，跳转到个人中心页面，进行申请
      $this->redirect('/user_activity/home');
    
    if($this->input->post() !== false) {
      $title = $this->input->post('title');
      if(empty($title) || $title == '')
        $this->returnJson(0, '标题不能为空');
      
      $num = $this->input->post('num');
      if(empty($num) || is_int($num))
        $this->returnJson(0, '数量填写不正确');
      
      $desc = $this->input->post('desc');
      if(empty($desc) || strlen($desc) < 10)
        $this->returnJson(0, '请输入10字以上的内容');
        
      $resourceBy = $this->input->post('resourceBy');
      //$resourceBy = explode(',', $resourceBy);
      
      //$resources = $this->input->post('resource');
      $price = $this->input->post('price');
      $date = $this->input->post('date');
      $date1 = $this->input->post('date1');
      $addr = $this->input->post('addr');
//      $join_condetion = $this->input->post('join_condetion');
        $is_edit = $this->input->post('is_edit'); //add:发布活动 edit：编辑活动
        $have_resource_by = $this->input->post('have_resource_by');//编辑活动的时候读取的信息

        $aid = $this->input->post('aid');
        $img_url = $this->input->post('img_url');
        $delete_id = $this->input->post('delete_id');

      try {
        $this->db->trans_begin();
        //判断点击进入的资源是否属于我的资源，yes:改变资源数量，No: 退出，不保存
          $have_rid = array();
          $have_rnum = array();
          if($have_resource_by) {
              foreach ($have_resource_by as $hv) {
                  $have_v = explode('&@&', $hv); //$v[0] sourceID  $v[1] 数量   //用户修改操作之前的信息
                  array_push($have_rid,$have_v[0]);
                  array_push($have_rnum,$have_v[1]);
              }
          }

          if($is_edit == 'edit'){
              if($num == ''){
                  $num = -1;
              }
              //替换表情
//              $content = $this->community->replace_smile($desc);
              //组织添加活动数组
              $activityData = array(
                  'name' => $title,
                  'content' => $desc,
                  'join_price' => $price,
                  'start_date'  => strtotime($date),
                  'end_date'    => strtotime($date1),
                  'addr'        => $addr,
                  'total' => $num
              );

              $where_cu = 'id_activity = ' . $aid . ' and id_business = ' . $this->userID;
              $this->community->update_community($activityData,$where_cu);

              //处理发布活动的时候使用的资源
              if($resourceBy) {
                  foreach ($resourceBy as $value) {
                      $v = explode('&@&', $value); //$v[0] sourceID  $v[1] 数量   //用户操作的信息
                      $resource_by = $this->resources->get_by_resources(
                          'relate.id_resource = ' . $v[0] . ' AND id_user = ' . $this->userID);

                      $update_edit = array();
                      $is_comm = 0;//判断当前rid下的num和修改前的num是否相同
                      $h_rnum = 0;
                      if(in_array($v[0],$have_rid)){
                          foreach($have_rid as $hrk=>$hri){
                              if($hri == $v[0]){
                                  $is_comm = 1;
                                  $h_rnum =$have_rnum[$hrk];
                              }
                          }
                      }
                      $num_update = intval($resource_by[0]['num']);
                      if($h_rnum != $v[1]){//判断传来的id_resource对应的num 和 操作前的num比较 若不一样则更新num
                          if(intval($h_rnum) > intval($v[1])){
                              $num_update = intval($resource_by[0]['num']) + (intval($h_rnum) - $v[1]);
                          }elseif(intval($h_rnum) < intval($v[1])){
                              $num_update = intval($resource_by[0]['num']) - ($v[1] - intval($h_rnum));
                          }
                      }
                      $num_update = $num_update>=0?$num_update:0;

                      $update_edit['num'] = $num_update;
                      $update_edit['updated'] = date('Y-m-d H:i:s',time());
                      if($num_update != intval($resource_by[0]['num'])){
                          $where_ue = 'id_resource = ' . $v[0] . ' and id_user = ' . $this->userID;
                          $this->resources->update_resource_by($update_edit,$where_ue);

                          //更新发布活动的时候使用的我的资源数量
                          $data_cr['num'] = intval($v[1]);
                          $where_cr = 'id_activity = ' .$aid .' and id_resource = ' . $v[0];
                          $this->community->update_community_resource($data_cr,$where_cr);
                      }
                  }
              }
              //处理发布用户活动的资源   end

              //删除活动附加图片
              $d_id = explode( ',',$delete_id);
              if($d_id){
                  foreach($d_id as $d){
                      if($d != ''){
                          $where_d = 'id_attachment = ' . $d;
                          $this->business->delete_synopsis_image($where_d);
                      }
                  }
              }

              //添加活动附加
              $src_ = explode( ',',$img_url);
              if($src_){
                  foreach($src_ as $ci){
                      if($ci != ''){
                          $business_attachment = array(
                              'id_business' => 0,//涂确认
                              'id_shop' =>  0,
                              'object_type' => 'community',
                              'id_object' => $aid,
                              'attachment_type' => 'image',
                              'image_url' => $ci,
                              'size' => 0,
                          );
                          $this->business->insert_business_attachment($business_attachment);
                      }
                  }
              }

              //添加标签
              $tag = $this->input->post('tag');
              if($tag) {
                  $tags = explode(' ', $tag);
                  $tagIDs = array();
                  foreach ($tags as $value) {
                      if($value) {
                          $where_t = 'tag_name = \'' .trim($value). '\'';
                          $tag_info = $this->tag->get_tag($where_t);
                          //判断该标签是否已存在于标签表中
                          if(!$tag_info){
                              $data = array(
                                  'tag_name' => trim($value),
                              );
                              $tagIDs[] = $this->tag->insert_tag($data);
                          }else{
                              $tagIDs[] = $tag_info[0]['id_tag'];
                          }
                      }
                  }
                  if($tagIDs){
                      $this->load->model('communitytag_model', 'communitytag');
                      //处理tag插入bn_community_activity_tag
                      $where_t = 'id_activity = ' . $aid;
                      $this->communitytag->delete_activity_tag($where_t);
                      foreach($tagIDs as $ti){
                          $da_t = array(
                              'id_activity'=>$aid,
                              'id_tag'=>$ti
                          );
                          $this->communitytag->insert_activity_tag($da_t);
                      }
                  }
              }
              $this->db->trans_commit();
              echo $this->returnJson(1, '编辑成功！！', $aid);
          }else{
              if($num == ''){
                  $num = -1;
              }
              //替换表情
//              $content = $this->community->replace_smile($desc);
            //组织添加活动数组
            $activityData = array(
              'id_business' => $this->userID,
              'id_business_source' => $this->bid,
              'object_type' => 'user',
                     'name' => $title,
                  'content' => $desc,
               'join_price' => $price,
                    'total' => $num,
                    'state' => 2,
                'start_date'  => strtotime($date),
                'end_date'    => strtotime($date1),
                'addr'        => $addr
            );
            $id_activity = $this->community->insert_community($activityData);
            //处理发布用户活动的时候的资源  start
          if($resourceBy) {
              foreach ($resourceBy as $value) {
                  $v = explode('&@&', $value); //$v[0] sourceID  $v[1] 数量   //用户操作的信息
                  $resource_by = $this->resources->get_by_resources(
                      'relate.id_resource = ' . $v[0] . ' AND id_user = ' . $this->userID);

                  if($resource_by){
                      foreach ($resource_by as $k => $re) {
                          if ($re['id_resource'] === $v[0]) {
                              //获取资源By真实ID //扣除资源的数量 $v[1]
                              $nums = $re['num'] - $v[1];
                              if($nums >= 0) {
                                  $for_i = 1;
                                  $data = array('num' => 'num - ' . $v[1]);
                                  $this->resources->update_by_resource($data,
                                      array('id_resource_by' => $re['id_resource_by']));

                                  $data_cr = array(
                                      'id_activity'=>$id_activity,
                                      'id_resource'=>$v[0],
                                      'num'=>$v[1]
                                  );
                                  $this->community->insert_community_resource($data_cr);//插入发布活动的资源表
                              }
                              else {
                                  $this->returnJson(0, '资源数量不足！');
                              }
                          }
                      }
                  }
                  else //当前ID不存在系统中
                      $this->returnJson(0, '提交资源中有不属于您的资源！');
              }
          }
            //处理发布用户活动的时候的资源  end

            //添加活动附加
            $src_ = explode( ',',$img_url);
            if($src_){
              foreach($src_ as $ci){
                  if($ci != ''){
                      $business_attachment = array(
                          'id_business' => 0,//涂确认
                          'id_shop' =>  0,
                          'object_type' => 'community',
                          'id_object' => $id_activity,
                          'attachment_type' => 'image',
                          'image_url' => $ci,
                          'size' => 0,
                      );
                      $this->business->insert_business_attachment($business_attachment);
                  }
              }
            }
            //添加标签
            $tag = $this->input->post('tag');
            if($tag) {
              $tagIDs = array();
              $tags = explode(' ', $tag);
              foreach ($tags as $key => $value) {
                if($value) {
                    $where_t = 'tag_name = \'' .trim($value). '\'';
                    $tag_info = $this->tag->get_tag($where_t);
                    if(!$tag_info){
                        $data = array(
                            'tag_name' => trim($value),
                        );
                        $tagIDs[] = $this->tag->insert_tag($data);
                    }else{
                        $tagIDs[] = $tag_info[0]['id_tag'];
                    }
                }
              }
              if($tagIDs){
                $this->load->model('communitytag_model', 'communitytag');
                //处理tag插入bn_community_activity_tag
                $where_t = 'id_activity = ' . $id_activity;
                $this->communitytag->delete_activity_tag($where_t);
                foreach($tagIDs as $ti){
                    $da_t = array(
                        'id_activity'=>$id_activity,
                        'id_tag'=>$ti
                    );
                    $this->communitytag->insert_activity_tag($da_t);
                }
              }
            }
            $this->db->trans_commit();
            echo $this->returnJson(2, '发布成功！！', $id_activity);
          }

//          if($resourceBy) {
//              foreach ($resourceBy as $value) {
//                  $v = explode('&@&', $value); //$v[0] sourceID  $v[1] 数量   //用户操作的信息
//                  $resource_by = $this->resources->get_by_resources(
//                      'relate.id_resource = ' . $v[0] . ' AND id_user = ' . $this->userID);
//
//                  if($is_edit == 'edit'){
//                      $update_edit = array();
//                      $is_comm = 0;//判断当前rid下的num和修改前的num是否相同
//                      $h_rnum = 0;
//                      if(in_array($v[0],$have_rid)){
//                          foreach($have_rid as $hrk=>$hri){
//                              if($hri == $v[0]){
//                                  $is_comm = 1;
//                                  $h_rnum =$have_rnum[$hrk];
//                              }
//                          }
//                      }
//                      $num_update = intval($resource_by[0]['num']);
//                      if($h_rnum != $v[1]){//判断传来的id_resource对应的num 和 操作前的num比较 若不一样则更新num
//                          if(intval($h_rnum) > intval($v[1])){
//                              $num_update = intval($resource_by[0]['num']) + (intval($h_rnum) - $v[1]);
//                          }elseif(intval($h_rnum) < intval($v[1])){
//                              $num_update = intval($resource_by[0]['num']) - ($v[1] - intval($h_rnum));
//                          }
//                      }
//                      $num_update = $num_update>=0?$num_update:0;
//                      $update_edit['num'] = $num_update;
//                      $update_edit['updated'] = date('Y-m-d H:i:s',time());
//                      if($num_update != intval($resource_by[0]['num'])){
//                          $where_ue = 'id_resource = ' . $v[0] . ' and id_user = ' . $this->userID;
//                          $this->resources->update_by_resource($update_edit,$where_ue);
//                      }
//                  }else{
//                      if($resource_by){
//                          foreach ($resource_by as $k => $re) {
//                              if ($re['id_resource'] === $v[0]) {
//                                  //获取资源By真实ID //扣除资源的数量 $v[1]
//                                  $nums = $re['num'] - $v[1];
//                                  if($nums >= 0) {
//                                      $for_i = 1;
//                                      $data = array('num' => 'num - ' . $v[1]);
//                                      $this->resources->update_by_resource($data,
//                                          array('id_resource_by' => $re['id_resource_by']));
//
//                                      $data_cr = array(
//                                          'id_activity'=>1,
//                                          'id_resource'=>$v[0],
//                                          'num'=>$v[1]
//                                      );
//
//                                      $this->resources->update_by_resource($update_edit,$where_ue);
//
//                                  }
//                                  else {
//                                      $this->returnJson(0, '资源数量不足！');
//                                  }
//                              }
//                          }
//                      }
//                      else //当前ID不存在系统中
//                          $this->returnJson(0, '提交资源中有不属于您的资源！');
//                  }
//              }
//          }
      }
      catch(Exception $ex) {
        echo $this->returnJson(0, '发布失败！！', '');
        $this->db->trans_rollback();
      }
      return;
    }

      $data_page['is_edit'] = 'add';
      $aid = $_GET['aid'];
      //进入编辑模式 zxx
      if($aid > 0){
          $data_page['is_edit'] = 'edit';
          $data_page['aid'] = $aid;
          //获取活动信息
          $community_info = $this->community->activity_detail($aid);
          if($community_info){
              $this->load->model('business_model', 'business');
              $where_a = 'id_object = ' . $community_info['id_activity'] . ' and object_type = \'community\'';
              $att_info = $this->business->get_business_attachment($where_a);
              if($att_info){
                  foreach($att_info as $ka=>$va){
                      $community_info['image_url'][$ka] = get_img_url($va['image_url'],'community');
                      $community_info['id_attachment'][$ka] = $va['id_attachment'];
                      $community_info['img_url'].= $community_info['img_url']?','.$va['image_url']:$va['image_url'];
                  }
              }

              $where_r = 'id_activity = ' . $aid;
              $resource_info = $this->community->get_community_resource($where_r);
              if($resource_info){
                  foreach($resource_info as $kr=>$vr){
                      $community_info['resources'][$kr][$vr['id_resource']] = $vr['num'];
                  }
              }
          }
          if($community_info['id_tag']){
              //标签
              $id_tags = explode(',',$community_info['id_tag']);//unserialize($community_info['id_tag']);
              $community_info['tags'] = '';
              foreach($id_tags as $ti){
                  $where_tag = 'id_tag = ' . $ti;
                $tag_info = $this->tag->get_tag($where_tag);
                  if($community_info['tags'] == ''){
                      $community_info['tags'] = $tag_info[0]['tag_name'];
                  }else{
                      $community_info['tags'] .= ' '.$tag_info[0]['tag_name'];
                  }
              }
              //参与条件
              $join_condetions = unserialize($community_info['join_condetion']);
              $community_info['condetions'] = $join_condetions;
//              //资源id
//              $id_resources = unserialize($community_info['id_resource']);
//              $community_info['resources'] = $id_resources;

          }
          if(empty($community_info['start_date'])){
              $community_info['start_date'] = time();
              $community_info['end_date'] = strtotime("+1 day");
          }
          $data_page['community_info'] = $community_info;
      }else{
          $community_info['start_date'] = time();
          $community_info['end_date'] = strtotime("+1 day");
          $data_page['community_info'] = $community_info;
      }

    if($this->input->get('rid'))
      $data_page['rid'] = $this->input->get('rid');
    
    $data_page['url_action'] = 'community/publish';
//    print_r($data_page);
//      die;
    $this->smarty->assign($data_page);
      if(TPL_DEFAULT == 'mobile'){
          //查询表情
          $smile = _get_smile_array();
          $row = array();
          foreach( $smile as $key =>$val){
              $row[$key] = '/smile/'.$val;
          }
          $row = array_slice($row,0,14);

          $this->smarty->assign('row',$row);

          $this->media('layout_section.css', 'css');
          $this->media('mobiscroll.custom-2.6.2.min.css', 'css');
          $this->media("jquery.cookie.min.js","js");
          $this->media('jquery.mobiscroll.js','js');
          $this->media('jquery.mobiscroll.app.js','js');
          $this->media('publish_activity.js','js');
          $this->media('common.js','js');
          $this->media("ajaxupload.js","js");
      }
    $this->smarty->view('publish_activity');
  }
  
  /**
   * 使用Ajax获取资源库资源
   * @param $by    = true 获取我的资源列表 如果$by为false时，获取是资源库列表
   * @param $level = true 获取推荐资源 false且不获取
   *
   * @version 2.0
   * @author Jamai
   */
  public function resources()
  {
    $by    = $this->input->post('by');
    $level = $this->input->post('level');

    $this->load->model('resources_model', 'resources');

//      $search_key = '';
    if($by) {//获取我的资源
      $where = 'id_user = ' . $this->userID;// . ' AND relate.num > 0';
      $key = trim($this->input->post('search_key'));
      if($key){
//          $search_key = $key;
        $where .= ' AND resource_title LIKE "%' . $key . '%"';
      }
        
      //当前页码
      $pager = 5;
      $page  = ! $this->input->post('page') ? 1 : $this->input->post('page');
      
      //获取总页数
      $count = $this->resources->count_by_resources($where);
      $total = ceil($count / $pager);
      
      //从第几条数据开始
      $offset = $page;//$pager * ($page > 0 ? ($page - 1) : 0);
      $resources = $this->resources->get_by_resources($where, 
             'relate.created DESC', $offset, $pager);

      if($resources)
        $resources[0]['total'] += $total;
    }
    else if($level) { //获取资源库推荐资源
      $resources = $this->resources->get_level(10);
    }
    else {//获取资源库资源   more
      $where = '';
      $search = $this->input->post('search_key');
      
      $where = 'num !=0 AND deleted = 1';
      if($search){
//          $search_key = $search;
          $where .= ' AND resource_title LIKE "%' . trim($search) . '%"';
      }
      
      //当前页码
      $pager = 25;
      $page = ! $this->input->post('page') ? 1 : $this->input->post('page');
      
      //获取总页数
      $count = $this->resources->count_resources($where);
      $total = ceil($count / $pager);
      
      //从第几条数据开始
//      $offset = $pager * ($page > 0 ? ($page - 1) : 0);
      $resources = $this->resources->get_resources($where, 'created DESC', $page, $pager);
    }
    echo $this->returnJson(1, '加载成功！', $resources, array('total' => $total));//,'search_key'=>$search_key
    return;
  }
  
  /**
   * 更多资源
   *
   * @version 2.0
   * @author Jamai
   */
  public function more_resource()
  {
    $this->smarty->view('more_resource');
  }
  
  /**
   * ajax 加载资源详情
   * 
   * @version 2.0
   * @author Jamai
   */
  public function info()
  {
    $id_resource = $this->input->post('id');
    
    if( ! empty($id_resource)) {
      $this->load->model('resources_model', 'resources');
      $resource = $this->resources->get_resource_info($id_resource);
      
      $this->load->model('business_model', 'business');
      $where = 'object_type = \'resource\' and id_object = ' . $id_resource;
      $resource_atta = $this->business->get_business_attachment($where);
      
      $this->smarty->assign('resource_atta', $resource_atta);
      $this->smarty->assign('resource', $resource);
      $this->smarty->assign('source', $this->input->post('source'));
      $this->smarty->assign('pay', PAY_BUY);
      echo $this->smarty->view('resource_info');
      return;
    }
  }
  
  //中转支付页面 处理参数
  public function pay()
  {
    $num    = $this->input->post('num');
    $rid    = $this->input->post('rid');
    $source = $this->input->post('source');
    
    if( ! $this->userID) {
      $url = $this->baseUrl . '/user/login';
      echo $this->returnJson(0, '未登录', $url);
      return;
    }
    
    switch ($source) {
      case 'resource':
        $url = $this->baseUrl . '/resource/index';
        break;
      default:
        //兼容上一版本
        $url = $this->baseUrl . '/community/publish';
        break;
    }
    
    if( ! intval($rid)) {
      //$url = '/wapi/' . $this->bid . '/' . $this->sid . '/community/publish';
      echo $this->returnJson(0, '非法操作', $url);
      return;
    }
    
    $this->load->model('resources_model', 'resources');
    $resource = $this->resources->get_resource_info($rid);

    if($resource['price'] && $resource['price'] != '0.00') {
      $url = $this->baseUrl . '/order/pay?rid=' . $rid . '&num=' . $num . '&state=resource';
      echo $this->returnJson(1, '成功', $url);
      return;
    }
    
    /** 免费 */
    
    //减少 资源的数量
    $res_num=0;
    if($resource['num'] <= -1){
      $res_num = -1;
    }
    elseif($resource['num'] == 0){
      //$url = '/wapi/' . $this->bid . '/' . $this->sid . '/community/publish';
      echo $this->returnJson(0, '资源没有数量了', $url);
      return;
    }
    else{
      $res_num = $resource['num'] - $num;
      if($res_num < 0){
        //$url = '/wapi/' . $this->bid . '/' . $this->sid . '/community/publish';
        echo $this->returnJson(0, '资源数量不足', $url);
        return;
      }
    }
    
    $this->resources->update_resource(
      array('num' => $res_num),
      'id_resource = ' . $rid);
    
    //查看我的资源里是否已有该资源
    $result = $this->resources->get_by_resources(array(
        'relate.id_resource' => $rid,
        'id_user' => $this->userID));
    
    $this->db->trans_begin();
    try {
      //增加记录为我的资源
      if($result) {//修改
        $this->resources->update_by_resource(
          array(
                 'num' => 'num + ' . $num,
             'updated' => '"' . date('Y-m-d H:i:s', time()) . '"'),
          array(
             'id_user' => $this->userID, 
         'id_resource' => $rid));
      }
      else { //添加
        $this->resources->insert_by_resource(array(
          'id_resource' => $rid,
                  'num' => $num,
              'id_user' => $this->userID));
      }
      
      //生成电子券
      $this->load->model('list_model', 'order');
      if (intval($num)) {
        for ($i = 0; $i < $num; $i++) {
          $this->order->create_eticket(
            $rid, $this->userID, $_SESSION['identity'], $result['owner'], 0, 'resource');
        }
      }
      $this->db->trans_commit();
      
      //$url = '/wapi/' . $this->bid . '/' . $this->sid . '/community/publish?rid=' . $rid;
      echo $this->returnJson(2, '购买成功！', $url);
      return;
    }
    catch (Exception $e) {
      $this->db->trans_rollback();
    }
  }
  
  public function filtercondition()
  {
    $width = $this->input->post('width') ? $this->input->post('width') : 480;
    
    //30 为li标签的高
    $limit = floor($width / 30);
    
    $this->load->model('merchanttype_model', 'merchantType');
    $fields = 'id_type_merchant, name, id_parent';
    $whrere = array('id_parent' => 0);
    $types = $this->merchantType->byFieldSelect($fields, $whrere, array('limit' => $limit), false);
    
    echo $this->returnJson(1, '', $types);
    return;
  }
  
    /*
     * zxx
     * 精彩生活
     */
    public function coollife(){
        $this->media('layout_section.css','css');
        $this->smarty->view('cool_life');
    }
    /*
     * zxx
     * 精彩生活列表
     */
    public function lists()
    {
      $offset = $this->input->post('offset');
      $offset = empty($offset) ? 1 : $offset;
      $pagesize = 25;
      $total = $pagesize * ($offset > 0 ? ($offset - 1) : 0);
      $condition = $this->input->post('condition') ? $this->input->post('condition') : 'all';//筛选
      if($condition == 'all') {
        $data = $this->community->hipigo_community_list($total,$pagesize,0,2);
      }
      else {
        $where = 'b.id_type_merchant = ' . $condition;
        $options = array('limit' => $pagesize, 'offset' => $total, 'order' => 'c.created DESC');
        $data = $this->community->byBuinessTypeCommunity($where, $options);
      }
      
      if($data) {
          $this->load->model('list_model','list');
          $this->load->model('communitytag_model','communitytag');
          $this->load->model('hipigouser_model', 'hipigouser');
          foreach($data as $k=>$v){
              if($v['join_price'] > 0){
                  $where = 'object_type = "community" and state = 1 and id_object = ' . $v['id_activity'];
                  $order_info = $this->list->get_order_info($where);
                  $data[$k]['join_count'] = count($order_info);
              }
              if($v['object_type'] == 'user'){
                  $head_img = $this->showImage($v['id_business'],'user');
                  $data[$k]['imgs'] = $head_img;
//                    $where_u = 'id_user = ' . $v['id_business'];
//                    $hipigouser_info = $this->hipigouser->get_hipigo_user_info($where_u);
//                    if($hipigouser_info){
//                        $head_image_url = $hipigouser_info[0]['head_image_url'];
//                        $f_num = strpos($head_image_url,'http');
//                        if($f_num === false){
//                            $data[$k]['imgs'] = get_img_url($head_image_url,'head',0,'default-header');
//                        }else{
//                            $data[$k]['imgs'] = $head_image_url;
//                        }
//                    }
              }elseif($v['object_type'] == 'community'){
//                    $data[$k]['imgs'] = get_img_url($v['posters_url'],'community');
                  $head_img = $this->showImage($v['id_activity'],'community');
                  $data[$k]['imgs'] = $head_img;
              }else{
                  $data[$k]['imgs'] = 'http://' . $_SERVER['HTTP_HOST'] . '/attachment/defaultimg/default-activity.png';
              }
              //获取参加用户及头像
              $where_j = 'id_activity = ' . $v['id_activity'] . ' and identity != \'visitor\'';//涂确认，准用户头像不被显示
              $join_info = $this->community->get_community_join_count($where_j,0);
              $join_ = array();
              if($join_info){
                  foreach($join_info as $kj=>$vj){
                      $head_img = $this->showImage($vj['id_user'],'user');
                      $join_[$kj][0] = $head_img;
                      $join_[$kj][1] = $join_info[$kj]['id_user'];

//                      $head_image_url = $join_info[$kj]['head_image_url'];
//                      $h_num = strpos($head_image_url,'http');
//                      if($h_num === false){
//                          $join_[$kj][0] = get_img_url($head_image_url,'head',0,'default-header');
//                          $join_[$kj][1] = $join_info[$kj]['id_user'];
//                      }else{
//                          $join_[$kj][0] = $head_image_url;
//                          $join_[$kj][1] = $join_info[$kj]['id_user'];
//                      }
                  }
              }
              $data[$k]['join_head_img'] = $join_;
              $data[$k]['tag'] = '';
              $where_t = 'id_activity = ' . $v['id_activity'];
              $tag_info = $this->communitytag->get_tag($where_t);
              if($tag_info)
                  foreach($tag_info as $vt){
                      $data[$k]['tag'] .= $data[$k]['tag'] == '' ? $vt['tag_name'] : ','.$vt['tag_name'];
                  }
          }
      }
//        print_r($data);
//        die;
      $page_types = 'coollife';
      $this->smarty->assign('page_type', $page_types);
      $this->smarty->assign('data', $data);
      echo $this->smarty->display('lists.html');
      exit;
    }


}