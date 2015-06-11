<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * @author zhoushuai
 * @copyright 2014-04-01
 * @version 1.0
 * 活动
 */
class Activity extends WX_Controller {

    private $id_business ;//商家ID
    private $id_shop ;//门店ID
    private $id_activity;//活动id
    private $id_open ;//微信id; 

    public function __construct()
    {
        parent::__construct();
        $this->id_business = $this->bid;
        $this->id_shop = $this->sid;
        
    }
	
	public function sjb(){
   	$this->smarty->view('jqqd');
   }
    
    /**
     * 砸金蛋。。。
     */ 
    public function egg(){
    	
        $this->id_activity = $this->input->get('aid'); //活动id
        $this->id_open = $this->input->get('oid');//微信id;
        //查询今天参与次数是否大于活动设置
        $this->load->model('chance_model','chance');
		$activity = $this->chance->find_activity_config($this->id_activity);
		//获取用户当天参与活动次数
		$l_date = date('Y-m-d 00:00:00');
		$m_date = date('Y-m-d 00:00:00',strtotime('1 days'));
	    $num_user_time = $this->chance->find_activity_time($this->id_activity,$this->id_open,$l_date,$m_date);
		//每人每天能参与的次数
		$time = $activity[0]['join_number_day'];      //每人每天参与次数
		$max_num = $activity[0]['join_number_total']; //每人总共参与次数 
		$time = !empty($time) ? $time:$max_num;
		//判断用户当天的参与次数是否达到上限
		$times_num = $num_user_time >= $time ? 0 : 1;
        $this->smarty->assign('times_num',$times_num);
		$this->smarty->assign('aid',$this->id_activity);
        $this->smarty->assign('oid',$this->id_open);
        $this->smarty->assign('title','游戏,砸金蛋活动');
        $this->smarty->view('crush_egg');
        
    }
    
    /**
     * 一站到底活动
     */
     public function one_stop(){
     	$this->id_activity = $this->input->get('aid'); //活动id
        $this->id_open = $this->input->get('oid');//微信id;
        $this->smarty->assign('title','互动游戏,一站到底，首页');
        $this->load->model('chance_model','chance');
        $arr = $this->chance->get_question_info($this->id_activity);
	    shuffle($arr);
	    //获取活动需要的时间，
        $time = $this->chance->get_activity_time($this->id_activity);
	    //组合题目和答案,并保存权重排序
	    $len = count($arr);
	    for($i=0;$i<$len;$i++)
	    {  //只有一个答案的情况
		   if(!empty($arr[$i]['id_options']))
			{
			$i_id_options = $arr[$i]['id_options'];   
		    $arr[$i]['result'][$i_id_options.'k']=$arr[$i]['description'];
			}
	  	  for($j=$i+1;$j<$len;$j++)
		    {
		    	if($arr[$i]['id_subject'] === $arr[$j]['id_subject'] && !empty($arr[$j]['id_subject']))
			    {
				  $j_id_options = $arr[$j]['id_options'];
				  $arr[$i]['result'][$j_id_options.'k']=$arr[$j]['description'];
				  unset($arr[$j]);
			    } 
		    }
	    }
		rsort($arr);
		//print_r($arr);
		$tips = count($arr);
	    $data=array('status'=>'1','times'=>$time,'item'=>$arr);
		$data = json_encode($data);
		//查询今天参与次数是否大于活动设置
		$activity = $this->chance->find_activity_config($this->id_activity);
		//获取用户当天参与活动次数
		$l_date = date('Y-m-d 00:00:00');
		$m_date = date('Y-m-d 00:00:00',strtotime('1 days'));
	    $num_user_time = $this->chance->find_activity_time($this->id_activity,$this->id_open,$l_date,$m_date);
		//每人每天能参与的次数
		$times = $activity[0]['join_number_day'];      //每人每天参与次数
		$max_num = $activity[0]['join_number_total']; //每人总共参与次数 
		//判断用户当天的参与次数是否达到上限
		$times = !empty($times) ? $times:$max_num;
		$times_num = $num_user_time >= $times ? 0 : 1;
		$activity_status = empty($activity) ? 0 : 1;
        $this->smarty->assign('aid',$this->id_activity);
        $this->smarty->assign('oid',$this->id_open);
		$this->smarty->assign('activity_status',$activity_status);
		$this->smarty->assign('times_num',$times_num);
        $this->smarty->assign('tip',$tips);
        $this->smarty->assign('time',$time);
		$this->smarty->assign('datas',$data);
        $this->smarty->view('one_stop');
     }

	 //开始答题次数减一,微信返回情况下判断答题次数
    public function minus_one()
	{
		$this->load->model('chance_model','chance');   
		$this->id_activity = $this->input->get('aid'); //活动id
        $this->id_open = $this->input->get('oid');//微信id; 
        //查询今天参与次数是否大于活动设置
		$activity = $this->chance->find_activity_config($this->id_activity);
		//获取用户当天参与活动次数
		$l_date = date('Y-m-d 00:00:00');
		$m_date = date('Y-m-d 00:00:00',strtotime('1 days'));
	    $num_user_time = $this->chance->find_activity_time($this->id_activity,$this->id_open,$l_date,$m_date);
		//每人每天能参与的次数
		$times = $activity[0]['join_number_day'];      //每人每天参与次数
		$max_num = $activity[0]['join_number_total']; //每人总共参与次数 
		//判断用户当天的参与次数是否达到上限
		$times = !empty($times) ? $times:$max_num;
		$times_num = $num_user_time >= $times ? 0 : 1;
        $data1 = array(
			'id_activity' =>$this->id_activity,                                 //活动编号
			'id_open' =>$this->id_open,                                         //用户微信号
			'id_customer' =>1,                                                  //用户注册编码
			'created' =>date('Y-m-d H:i:s'),                                    //参与活动时间
			'is_winner' =>0                                                     //是否获奖
			 );
		if($times_num)
		{
			$jion_id = $this->chance->set_activity($data1);
		}
		$jion_id = !empty($jion_id) ? $jion_id : 0;
		$data=array('status'=>$times_num,'jion_id'=>$jion_id);
		echo json_encode($data);
	}
//
//        public function gifts()
//    {
//        $this->smarty->assign('title', '我的口袋');
//        $this->load->model('chance_model', 'chance');
//        //o5tjjt4L_RGB_gLcnVbeShz33G-g  测试的open_id
//        $this->id_open = $this->input->get('oid'); //微信id;
//        if (!$this->id_open) {
//           $this->id_open = $this->wx_authorize(1);
//        }
//        $gift_list = $this->chance->get_tickets_list($this->id_open);
//
//        if ($gift_list) {
//            //debug($gift_list);
//            $this->smarty->assign('gift', $gift_list);
//            $this->smarty->view('gifts');
//        } else {
//            $this->smarty->view('no_gifts');
//        }
//
//    }
    
    public function authorize($num){
        $this->id_open = $this->wx_authorize($num);
        Header('Location: '.$this->url.'/activity/gifts?oid='.$this->id_open);
    }


    /**
     * 
     * 用户打开授权页面
     * $num 步骤数 1：第一步获取code 2：第二步获取id_open
     * 
     */
     public function wx_authorize($num){
        $this->load->model('businessconfig_model','businessconfig');
        $where = 'id_business = ' . $this->bid . ' and id_shop = ' . $this->sid;
        $config= $this->businessconfig->get_business_config($where);
        $openid = '';
        if($config){
             if($num == 1){
//                 file_put_contents(DOCUMENT_ROOT.'/acon.txt',var_export($config,TRUE));
                /**
                 * @第一步：用户同意授权，获取code
                 */
                 //授权后重定向的回调链接地址
                $uri = 'http://'.$_SERVER ['HTTP_HOST'].$this->url.'/activity/authorize/2';
                $uri = urlencode($uri);
                $state =$config[0]['appid'].'_'.$config[0]['appsecret']; 
                //scope等于snsapi_userinfo时的授权页面（scope等于snsapi_base（不弹出授权页面，直接跳转，只能获取用户openid）授权连接）：
                $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $config[0]['appid'] .'&redirect_uri=' . $uri .'&response_type=code&scope=snsapi_base&state='.$state.'#wechat_redirect';

                 Header('Location:' . $url);
             }else{
                /**
                 * @第二步：通过code换取网页授权access_token
                 */
                $code = $this->input->get('code');
                $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$config[0]['appid'].'&secret='.$config[0]['appsecret'].'&code='.$code.'&grant_type=authorization_code';
                $ch = curl_init();
                curl_setopt($ch,CURLOPT_URL,$url);
                curl_setopt($ch,CURLOPT_HEADER,0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                $res = curl_exec($ch);
                curl_close($ch);
                $json_obj = json_decode($res,true);
                $openid = $json_obj['openid'];  
             }         
        }
        return $openid; 
     }




    /*
     * zxx
     * start
     */


    public function gifts2($number){
//        $number=2;$b=1000;$scope = 1;
        $b = $this->input->get('b');
        //获取授权用户openid
        $id_open = $this->get_open_ids($number);
        if(!$id_open){
            $id_open = 0;
        }

        $this->load->model('business_model','business');
        $where = 'id_business = '.$b;
        $merchant = $this->business->get_business_phone($where);

        $url = 'http://'.$merchant[0]['sld'].'/wapi/'.$b.'/0/activity/gifts?hoid='.$id_open;
        Header('Location:'.$url);
    }


    public function gifts3(){
        $b = $this->input->get('b');
        $scope = $this->input->get('scope')?$this->input->get('scope'):'';

        $this->get_open_ids(1,'gift',$b,$scope);
    }


    /*
     * 我的口袋
     */
    public function gifts()
    {
        $userid = $_SESSION['userid'];

        $ooid = $this->input->get('ooid');//微信id;
        if($ooid){
            $_SESSION['ooid'] = $ooid;
        }

        $this->load->model('hipigouser_model','hipigouser');

        $agent = $_SERVER["HTTP_USER_AGENT"];
        if(strpos($agent,"MicroMessenger")){//微信用户往下
//            if(!$userid){//判断用户session是否存在，不存在往下
                $hoid = $this->input->get('hoid');//微信id;
                if(!$hoid){
                    Header('Location:http://'.DOMAIN_URL.'/activity/gifts3?b='.$this->bid);
                    die;
                }

                //如果存在openid。判断openid是否存在于bn_hipigo_user表中
                $where = 'id_open = \'' . $hoid . '\'';
                $is_r = $this->hipigouser->get_hipigo_user_info($where);
                if(!$is_r){//若不存在表中则进入授权页面读取用户信息
                    //获取用户基本信息
                    Header('Location:http://'.DOMAIN_URL.'/activity/gifts3?b='.$this->bid.'&scope=snsapi_userinfo');
                    die;
                }else{
                    $userid = $is_r[0]['id_user'];
                    $data['last_login_time'] =  date('Y-m-d H:i:s', time());
                    $where = 'id_user = ' . $userid;
                    $this->hipigouser->update_hipigo_user($data,$where);//更新会员最后登录时间
                }

//            }else{
//                //判断用户cookie是否存在，存在往下
//                //if($_COOKIE['identity'] != 'visitor'){//用户身份为准用户的往下
//                $data['last_login_time'] =  date('Y-m-d H:i:s', time());
//                $where = 'id_user = ' . $userid;
//                $this->hipigouser->update_hipigo_user($data,$where);//更新会员最后登录时间
//                //}
//                /*else{
//                    $userid = $_COOKIE['userid'];
//                }*/
//            }
            $_SESSION['userid']= $userid;
            $_SESSION['identity']='wechat';
//            setcookie('userid', $userid, time() + (60 * 60 * 24 * 30), '/', '');
//            setcookie('identity', 'wechat', time() + (60 * 60 * 24 * 30), '/', '');
        }else{//非微信用户往下
            if($userid){
                if($_SESSION['identity'] != 'visitor'){
                    $data['last_login_time'] = date('Y-m-d H:i:s', time());
                    $where = 'id_user = ' . $userid;
                    $this->hipigouser->update_hipigo_user($data,$where);//更新会员最后登录时间

                    $_SESSION['userid']= $userid;
                    $_SESSION['identity']='register';
                }
//                setcookie('userid', $userid, time() + (60 * 60 * 24 * 30), '/', '');
//                setcookie('identity', 'register', time() + (60 * 60 * 24 * 30), '/', '');
            }else{
                header('location:'.$this->url_prefix . $this->bid .'/'.$this->sid.'/user/login');
                die;
            }
        }

        $this->load->model('ticket_model','ticket');
        $this->load->model('community_model','community');
        $this->load->model('business_model','business');
        $data_array = array();
        if($_SESSION['ooid']){
            $where_s = 'id_open = \''.$_SESSION['ooid'].'\'';
            $hipigo_user = $this->hipigouser->get_hipigo_user_info($where_s);
            if($hipigo_user){
                $data_array['nick_name'] = $hipigo_user[0]['nick_name'];
            }else{
                $business_user = $this->business->get_business_sub($where_s);
                if($business_user){
                    $data_array['nick_name'] = $business_user[0]['nick_name'];
                }
            }
            $where = 'ti.object_type != \'order\' and (ti.id_open = \''.$_SESSION['ooid'].'\' or ti.id_customer = '.$userid.') and ti.state = 1';
        }else{
            $where_h = 'id_user = '.$userid;
            $hipigo_user = $this->hipigouser->get_hipigo_user_info($where_h);
            if($hipigo_user){
                $data_array['nick_name'] = $hipigo_user[0]['nick_name'];
            }
            $where = 'ti.object_type != \'order\' and ti.id_customer='.$userid.' and ti.state = 1';
        }

//        $where .= ' and t.valid_end >= \'' . date('Y-m-d H:m:s',time()) . '\'';
        $ticket_info = $this->ticket->get_ticket_item($where,0,20,2, 'ti.get_time desc');
        if($ticket_info){
            foreach($ticket_info as $kt=>$vt){
                $ticket_info[$kt]['title'] = '';
                $ticket_info[$kt]['notice'] = '';
                $ticket_info[$kt]['link'] = '';
                $ticket_info[$kt]['ticket_status'] = 1;//是否被显示
                if($vt['object_type'] == 'community'){
                    $ticket_info[$kt]['image_url'] = '';

                    $where_c = 'id_activity = ' . $vt['id_object'];
                    $comm_info = $this->community->get_community_info($where_c);
                    if($comm_info){
                        $ticket_info[$kt]['title'] = $comm_info[0]['name'];
                        $ticket_info[$kt]['notice'] = $comm_info[0]['notice'];
                    }

                    $ticket_info[$kt]['link'] = 'http://'.DOMAIN_URL .'/community/detail?aid='.$vt['id_object'].'&suserid='.$userid;
                }elseif($vt['object_type'] == 'eticket'){
                    $where_t = 'id_eticket = ' . $vt['id_object'] .' and valid_end >= \'' . date('Y-m-d H:m:s',time()) . '\'';
                    $ti_info = $this->ticket->get_ticket_introduction($where_t);

                    $ticket_info[$kt]['image_url'] = get_img_url($ti_info[0]['image'],'ticket');
                    $ticket_info[$kt]['title'] = '';
                    if($ti_info){
                        $ticket_info[$kt]['title'] = $ti_info[0]['name'];
                        $ticket_info[$kt]['notice'] = $ti_info[0]['address'];
                    }else{
                        $ticket_info[$kt]['ticket_status'] = 0;
                    }

                    $ticket_info[$kt]['valid_begin'] = $ti_info[0]['valid_begin'];
                    $ticket_info[$kt]['valid_end'] = $ti_info[0]['valid_end'];
                    $ticket_info[$kt]['link'] = 'http://'.DOMAIN_URL .'/home/ticket_content/'.$vt['id_object'];
                }
            }
        }

        $this->load->model('community_model','community');
        $where_spread = 'ms.id_business = ' . $this->bid . ' and (ma.state = 1 or ma.state = 2)';
        $comm_spr = $this->community->get_community_spread($where_spread);
        foreach($comm_spr as $kcs=>$vcs){
            $comm_spr[$kcs]['link'] = 'http://'.DOMAIN_URL .'/community/detail?aid='.$vcs['id_activity'] . '&type=spread&suserid='.$userid;
        }

        $data_array['ticket_info'] = $ticket_info;
        $data_array['community_spread'] = $comm_spr;
		$this->smarty->assign($data_array);

        $this->smarty->view('gifts');

    }

  function test(){
        $this->smarty->view('templet_index01');
  }

  
    /*
     * zxx
     * end
     */


}