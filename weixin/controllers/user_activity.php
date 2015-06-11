<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * @author zxx
 * @copyright 2014-05-23
 * @version 1.1
 * 个人中心（我的活动,个人资料）
 */
class User_activity extends WX_Controller
{
    //private $userid;//用户id
   // private $identity;//用户身份

    public function __construct()
    {
        parent::__construct();

        //$this->userid = $_SESSION['userid'];
        //$this->identity = $_SESSION['identity']?$_SESSION['identity']:'';

        $this->smarty->assign('bid', $this->bid);
        $this->smarty->assign('sid', $this->sid);
        $this->smarty->assign('userid', $this->userid);
        $this->smarty->assign('identity', $this->identity);
        $this->smarty->assign('bak_url', $this->bak_url);
        $this->load->model('community_model', 'community');
        $this->load->model('hipigouser_model', 'user');
    }
    
    /*
     * zxx
     * 获取我的活动信息
     */
    public function index()
    {
        $return = array();
        $uid = $this->input->get('userid');
        $id_join = $this->input->get('id_join');

//        $identity = $this->input->get('identity');
//        visitor
        $this->load->model('sysconfig_model','sysconfig');
        //查询个人中心背景图片
        $where = 'key_code = 2';
        $bg_image = $this->sysconfig->get_sysconfig($where);
        $return['bg_image'] = empty($bg_image[0]['key_value']) ? $this->url_prefix . 'img/banner04.jpg':get_img_url( $bg_image[0]['key_value'],'business_ad',0,'default-header.png');

        $this->load->model('hipigouser_model','hipigouser');
        $this->load->model('community_model','community');

        if($uid && ($this->identity == 'register'|| $this->identity == 'wechat')){
            //查询用户的个人资料
            $where_u = 'id_user = ' . $uid;
            $user_info = $this->hipigouser->get_hipigo_user_info($where_u);
            $return['user_info'] = $user_info[0];

    //        //查询用户的活动
            // $where_a = 'maj.id_user = ' . $uid;
            $where_a = '(maj.id_user = ' . $uid . ' OR ma.id_business = ' . $uid . ') and ma.state > 0';
            $activity_info = $this->community->get_community_member($where_a,0,25,'created desc',3);
            $return['activity_num'] = count($activity_info);
            
        }
        elseif($uid && $this->identity == 'visitor'){
//            //查询用户的个人资料
//            $where_u = 'id_user = ' . $uid;
//            $user_info = $this->hipigouser->get_hipigo_user_info($where_u);
//            $return['user_info'] = $user_info[0];

            //        //查询用户的活动
            $where_a = 'maj.cellphone = ' . $uid . ' and ma.state > 0';
            $activity_info = $this->community->get_community_member($where_a,0,25,'created desc',3);
            $return['activity_num'] = count($activity_info);
          
            if($this->userid == $activity_info[0]['cellphone']){
                $user_info['nick_name'] = $activity_info[0]['cellphone'];
            }else{
                $user_info['nick_name'] = substr($activity_info[0]['cellphone'],0,3).'******'.substr($activity_info[0]['cellphone'],8,3);;
            }
            $user_info['head_image_url'] = '';
//            $user_info['id_user'] = $activity_info[0]['cellphone'];
            $return['user_info'] = $user_info;
        }
        else {
            if($uid){
                //查询用户的个人资料
                $where_u = 'id_user = ' . $uid;
                $user_info = $this->hipigouser->get_hipigo_user_info($where_u);
                $return['user_info'] = $user_info[0];

                //        //查询用户的活动
                // $where_a = 'id_user = ' . $uid;
                $where_a = 'maj.id_user = ' . $uid . ' and ma.state > 0';
                $activity_info = $this->community->get_community_member($where_a,0,25,'created desc',3);
                $return['activity_num'] = count($activity_info);
            }
            else{
                //        //查询用户的活动
                $where_a = 'maj.id_join = ' . $id_join . ' and ma.state > 0';
                $activity_info = $this->community->get_community_member($where_a,0,25,'created desc',3);
                $return['activity_num'] = count($activity_info);

                if($this->userid == $activity_info[0]['cellphone']){
                    $user_info['nick_name'] = $activity_info[0]['cellphone'];
                }else{
                    $user_info['nick_name'] = substr($activity_info[0]['cellphone'],0,3).'******'.substr($activity_info[0]['cellphone'],8,3);;
                }

                $user_info['head_image_url'] = '';
//            $user_info['id_user'] = $phone;
                $return['user_info'] = $user_info;
                $uid = $activity_info[0]['cellphone'];
            }
        }

        if($return['user_info']['head_image_url']){
            $return['user_info']['head_image_url'] = get_img_url(str_replace('.jpg','-small.jpg',$return['user_info']['head_image_url']),'head',0,'default-header.png');
        }
        $this->load->model('expert_model','expert');
        $experCount = 'join';//如果是join则是准用户，则不会出现申请达人按钮
        if(!$id_join){
            //查询用户是否还未通过达人审核
            $where_e = 'state = 0 and id_user = ' . $return['user_info']['id_user'];
            $experCount = $this->expert->get_expert_count($where_e);
        }
        $this->smarty->assign('experCount', $experCount);

        //发布活动链接  By Jamai 2.0
        $this->smarty->assign('boolean', $return['user_info']['id_role'] == 3 ? true : false);
        
        $expertUrl = $this->url_prefix . '90/0/user_activity/expert';
        $this->smarty->assign('apply_for_expert', $expertUrl);
        
        $publishUrl = $this->url_prefix . '90/0/community/publish';
        $this->smarty->assign('publish_url', $publishUrl);

        $verify_url = $this->url_prefix . '90/0/user_activity/verify_ticket';
        $this->smarty->assign('verify_url', $verify_url);

        $return['uid'] = $uid;
        $return['num'] = rand(100,999);
        $return['bid'] = $this->bid;
        $return['sid'] = $this->sid;
        $return['id_join'] = $id_join;
        $return['id_user'] = $uid;
        $this->smarty->assign($return);
        
        if(TPL_DEFAULT == 'mobile') {
          //$this->media('layout_section.css', 'css');
          $this->media('user_by.js', 'js');
          $this->smarty->display('user_by.html');
        }
        else {
          $this->smarty->view('user_activity');
        }
    }
 
  
  /**
   * 加载我的钱包页面
   * @author sc By Jamai
   * @version 2.1  
   * @money 总资产  |  money_balance 可提金额  |  
   **/
  public function my_wallet()
  {
    if ( ! $this->userid)
      $this->redirect('/user/login');
    
    $num = $this->input->get('num')?$this->input->get('num'):1;
    $num = 10 * $num;
    
    $this->load->model('user_model','user_wallet');
    $result =  $this->user_wallet->get_my_wallet($this->userid,$num);
    $result_count = count($result);
    $money = 0;
    $money_balance = 0;
    
    //获取总金额
    $this->load->model('list_model', 'list');
    
    $where = "income_object_type = 'user' AND id_income_object = {$this->userid}";
    //$sum = $this->list->get_settlement_amount($w);
    //$money = $sum->total;
    
    //获取可结算金额
    $where = $where . " AND i.state > 0 AND t.state > 0 AND o.state = 1";
    $lists = $this->list->get_settlement_info($where);
    $result = array();
    // debug($lists);
    $this->load->model('community_model', 'community');
    $this->load->model('user_model', 'users');
    foreach ($lists as $key => $item) {
       //判断可结算金额
      if($item['i_state'] == 2 && $item['t_state'] == 2) {
        $money_balance = $money_balance + $item['amount'];
      }
      else if($item['i_state'] == 3) {
        //提现
        $outlay = $item['amount'];
      }
      $money = $money + $item['amount'];
      
      if($item['objectType'] == 'community') {
        //用户发布的活动
        $community = $this->community->byFieldSelect('name, id_activity', 
                array('id_activity' => $item['objectID'], 'object_type' => 'user'), true);
        $user = $this->users->byFieldSelect('id_user, nick_name, account_name',
                array('id_user' => $item['buyer']), true);
        
        $return = array(
          'community' => $community,
          'user'      => $user,
         'time_month' => date('m', strtotime($item['o_created'])), //处理时间显示
          'time_day'  => date('d', strtotime($item['o_created'])),
          'money'     => $item['amount'],
          'outlay'    => $outlay,
        );
      }
      
      $result[$key] = $return;
    }
    // debug($result);
    /*
    foreach($result as $k=>$v){
       $money = $money+$v['amount'];
       $state = $v['state'];
       $source_type = $v['source_type'];
       $id_source_type = $v['id_source_type'];
       $balance_date = $v['balance_date'];
       $balance_date = strtotime($balance_date);
       $time_month = date('m',$balance_date);
       $time_day = date('d',$balance_date);
       $time_month_day = date('md',$balance_date);

       $result[$k]['time_month'] = $time_month;
       $result[$k]['time_day'] =  $time_day;
       $result[$k]['time_month_day'] =  $time_month_day;
        
       if($state==3){
        $money_balance = $money_balance+$v['amount'];
       }

       if($source_type=='order'){

        $order =  $this->user_wallet->get_my_order($id_source_type);
        $order_type = $order[0]['object_type'];          

        if($order_type=='community'){
          $order_activity =  $this->user_wallet->get_my_source_activity($id_source_type);
          $order_name = $order_activity[0]['name'];  
          $id_business = $order_activity[0]['id_business'];  
          $id_activity = $order_activity[0]['id_activity'];  
        }

        if($order_type=='resource'){
          $order_resource =  $this->user_wallet->get_my_source_resource($id_source_type);
          $order_name = $order_activity[0]['resource_title'];
          $id_business = 90;  
          $id_activity = $order_activity[0]['id_resource'];  
        }

        $result[$k]['order_name'] = $order_name;
        $result[$k]['id_business'] = $id_business;
        $result[$k]['id_activity'] = $id_activity;
       }
    }
    */

    $this->media('wallet.js', 'js');
        $this->smarty->assign('result', $result);
        $this->smarty->assign('result_count', $result_count);
        $this->smarty->assign('money_balance', $money_balance);
        $this->smarty->assign('money', $money);
      $this->smarty->display('my_wallet.html');
    }

  /**
   * 加载我的钱包列表页面
   * @author sc
   * @version 2.1  
   **/
  public function wallet_list()
  {

    $id_user = $this->input->get('id_user')?$this->input->get('id_user'):'';
    $num = $this->input->get('num')?$this->input->get('num'):1;
    $num = 10*$num;
        $this->load->model('user_model','user_wallet');
    $result =  $this->user_wallet->get_my_wallet($id_user,$num);
    $money = 0;
    $money_balance = 0;

    foreach($result as $k=>$v){

       $money = $money+$v['amount'];
       $state = $v['state'];
       $source_type = $v['source_type'];
       $id_source_type = $v['id_source_type'];
       $balance_date = $v['balance_date'];
       $balance_date = strtotime($balance_date);
       $time_month = date('m',$balance_date);
       $time_day = date('d',$balance_date);
       $time_month_day = date('md',$balance_date);

       $result[$k]['time_month'] = $time_month;
       $result[$k]['time_day'] =  $time_day;
       $result[$k]['time_month_day'] =  $time_month_day;
        
       if($state==3){
        $money_balance = $money_balance+$v['amount'];
       }

       if($source_type=='order'){

        $order =  $this->user_wallet->get_my_order($id_source_type);
        $order_type = $order[0]['object_type'];

        if($order_type=='community'){
          $order_activity =  $this->user_wallet->get_my_source_activity($id_source_type);
          $order_name = $order_activity[0]['name'];  
          $id_business = $order_activity[0]['id_business'];  
          $id_activity = $order_activity[0]['id_activity'];  
        }

        if($order_type=='resource'){
          $order_resource =  $this->user_wallet->get_my_source_resource($id_source_type);
          $order_name = $order_activity[0]['resource_title'];
          $id_business = 90;  
          $id_activity = $order_activity[0]['id_resource'];  
        }

        $result[$k]['order_name'] = $order_name;
        $result[$k]['id_business'] = $id_business;
        $result[$k]['id_activity'] = $id_activity;

       }

    }
    
    echo json_encode($result);return;
  }


   /**
     * 个人用户提现功能
     * @author sc
   * @沿用商户结算功能业务流程
     * @version 2.1  
     **/
  public function avail(){
    
      if ( ! $this->userid)
        $this->redirect('/user/login');

        $where_f = 'id_object = ' . $this->userid . ' and object_type = \'user\' and status = \'apply\'';
        $this->load->model('financeaccount_model', 'financeaccount');
        $f_info = $this->financeaccount->get_finance_account($where_f);

        if($f_info){
            $resp = array('status' => 0,'msg'=>'您还有未通过申请的提现,不能再次申请。');
            die(json_encode($resp));
        }

        $price = $this->input->get('price');
        $poundage = $this->input->get('poundage')?$this->input->get('poundage'):0;
        $type = $this->input->get('type');
        $card_account = $this->input->get('card_account');

        $data_i = array(
            'id_object'=>$this->userid,
            'object_type'=>'user',
            'type'=>$type,
            'status'=>'apply',
            'price'=>$price,
            'poundage'=>$poundage,
            'created'=>date('Y-m-d H:i:s',time())
        );
  
     $data_user = array(
            'type'=>$type,
            'card_account'=>$card_account
        );

        $r = $this->financeaccount->insert_finance_account($data_i);

        $this->load->model('user_model', 'user_info');
    $where = 'id_user ='.$this->userid;
    $this->user_info->update_user_info($data_user,$where);

        if($r){
            $resp = array('status' => 1,'msg'=>'申请成功！');

            die(json_encode($resp));
        }

  }

    /**
     * 加载我的活动列表页面
     * 抛开原来写的部分代码（因和资源用同一模板加载数据，则取消在此方法中获取数据）
     * @author Jamai
     * @version 2.1  
     *
     **/
    public function bylist()
    {
      if ( ! $this->userid)
        $this->redirect('/user/login');
      
      $this->media('activity_by.js', 'js');
      $this->media('layout_section.css', 'css');
      
      $this->smarty->display('activity_by.html');
    }
    
    public function disable()
    {
      if( ! $this->userid) 
        $this->redirect('/user/login');
      
      $idActivity = intval($this->input->post('id')) ? intval($this->input->post('id')) : null;
      
      $this->load->model('community_model', 'community');
      $count = $this->community->att_community_user($idActivity);
      if( ! $count) {
        $data = array('state' => 0);
        $where = 'id_activity = ' . $idActivity;
        $re = $this->community->update_community($data, $where);
        echo $this->returnJson(1, '关闭成功');
      }
      else 
        echo $this->returnJson(0, '已有成员参加活动，不能关闭');
      return;
    }
    
    /**
     * 获取活动列表数据
     * 与第三方查看个人信息时 可调用此部分数据,  模板没有统一。新加入一个模板
     * @author Jamai
     * @version 2.1
     **/
    public function ajaxlist()
    {
      $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;
      $source = $this->input->post('source') ? $this->input->post('source') : 'activity_by';//我的活动
      
      if($source == 'activity_by') {// 查看我的活动
        if ( ! $this->userid)
          $this->redirect('/user/login');
          
        $kw = $this->input->post('kw') ? $this->input->post('kw') : '';
        
        //筛选
        $filter = $this->input->post('condition');
        if($filter == 'by' && $source == 'activity_by') {
          $where = "a.object_type = 'user' AND a.id_business = " . $this->userid;
        }
        else if($filter == 'jion' && $source == 'activity_by') {
          $where = "j.id_user = " . $this->userid;
        }
        else {
          $where = "(a.id_business = " . $this->userid . 
                  " AND a.object_type = 'user' OR j.id_user = " . $this->userid . 
                  ")";
        }
      } 
      else if($source == 'user_by') {// 查看他人活动
        //为了方法统一  这里kw 是获取用户ID
        $userID = intval($this->input->post('kw')) ? intval($this->input->post('kw')) : 'NULL';
        $where = "(a.id_business = " . $userID . 
                  " AND a.object_type = 'user' OR j.id_user = " . $userID . ")";
      }
      
      $where .= " AND a.state >= 0 AND (j.role != 3 OR j.role IS NULL)";
      if($kw)
        $where .= ' AND name LIKE "%' . $kw . '%"';
      
      $offset = ($offset - 1) * 25;//为了原来数据兼容  //state DESC 是否需要关闭的活动排列在最下面
      $result = $this->community->get_user_activity($where, $offset, 25, 'created DESC');
      
      if($source == 'activity_by') {
        $this->load->library('community_logic');
        $result = $this->community_logic->FormatData($result);
        //读取消费码
        $this->load->model('eticket_item_model', 'eticket_item');
        foreach ($result as $key => $value) {
          $codeWhere = 'id_object = ' . 
                $value['id_activity'] . ' AND id_customer=' . $this->userid;
          
          $codes = $this->eticket_item->getEticketItemCode($codeWhere);
          $result[$key]['codes'] = $codes;
          
          //创建者
          if($value['id_business'] == $this->userid && $value['object_type'] == 'user') {
            $result[$key]['creator'] = true;
          }
        }
        $this->smarty->assign(array(
          'lists' => $result,
          'source' => 'activity_by',
        ));
        echo $this->smarty->display('list-resource-activity.tpl');
        exit;
      }
      else if($source == 'user_by') {
        $i = 1;
        foreach ($result as $key => $value) {
          if($value['id_business'] == $userID && $value['object_type'] == 'user') {
            $result[$key]['creator'] = true;
          }
          else {
            if($value['object_type'] == 'user') {
              $result[$key]['img_url'] = $this->showImage($value['id_business'], 'user');
            }
            else 
              $result[$key]['img_url'] = $this->showImage($value['id_activity'], 'community');
          }
          
          if($value['created']) {
            $result[$key]['month'] = date('m', strtotime($value['created']));
            $result[$key]['day']   = date('d', strtotime($value['created']));
            
            if($result[$key]['day'] == $result[$key - $i]['day']) {
              $i++;
              $result[$key]['day'] = '';
            }
            else $i = 1;
          }
        }
        
        $this->smarty->assign(array(
          'lists' => $result,
        ));
        
        echo $this->smarty->display('list-user-by.tpl');
        exit;
      }
    }
    
    //获取用户社区活动
    function get_activity(){
        $page_type = $this->input->get('page_type');
        $uid = $this->input->get('uid');
        $pagesize = $this->input->get('pagesize')?$this->input->get('pagesize'):25;
        $offset = $this->input->get('page')?$this->input->get('page'):1;

        $type = $this->input->get('type');//0:我的活动 1：我的资源

//        $phone = $this->input->get('phone');
        $id_join = $this->input->get('id_join');

        if($type == 1){
            $offset = $offset == 1 ? 0 : $offset;
            $this->load->model('resources_model','resources');
            $where = 'id_user = ' . $uid . ' AND relate.num > 0 and deleted = 1';
            $order = 'relate.created DESC';
            $activity_info = $this->resources->get_by_resources($where,$order,$offset,$pagesize);
            if($activity_info){
                $this->load->model('ticket_model','ticket');
                foreach($activity_info as $k=>$v){
                    $where1 = 'ti.object_type = \'resource\' and ti.id_object = '.$v['id_resource'] . ' and ti.id_customer = ' . $uid;//ti.id_business = ' . $v['owner'] . ' and
                    $ticket_item = $this->ticket->get_ticket_item($where1,0,1,0);
                    $code = array();
                    if($ticket_item){
                        foreach($ticket_item as $vti){
                            array_push($code,$vti['code']);
                        }
                    }
                    $activity_info[$k]['code'] = $code;
                }
            }
        }else{
            $this->load->model('community_model','community');
            if($uid && ($this->identity == 'register'|| $this->identity == 'wechat')){
                if($uid != $_SESSION['userid']){
                    if($id_join == ''){
//                        $where_a = '(maj.id_user = ' . $uid . ' OR ma.id_business = ' . $uid . ') and ma.state > 0';
//                        // 'id_user = ' . $uid;
//                        $activity_info = $this->community->get_community_member($where_a,$offset,$pagesize,'created desc',3);

                        $offset_r = ($offset-1)*$pagesize;
                        $where_ua = "(a.id_business = ".$uid." AND a.object_type = 'user' OR j.id_user = ".$uid.") AND a.state > 0 AND (j.role != 3 OR j.role IS NULL)";
                        $activity_info = $this->community->get_user_activity($where_ua,$offset_r,$pagesize,'a.created desc');
                    }else{
                        //查询用户的活动
//                        $where_a = 'maj.id_join = ' . $id_join;
//                        $activity_info = $this->community->get_community_member($where_a,$offset,$pagesize,'maj.created desc',4);

                        $offset_r = ($offset-1)*$pagesize;
                        $where_ua = "j.id_join = " . $id_join ." AND (j.role != 3 OR j.role IS NULL)";
                        $activity_info = $this->community->get_user_activity($where_ua,$offset_r,$pagesize,'a.created desc');
                    }
//                //查询用户的活动
//                $where_a = 'maj.cellphone = ' . $uid;
//                $activity_info = $this->community->get_community_member($where_a,$offset,$pagesize,'maj.created desc',4);

                    if($page_type == 1){
                        return $activity_info;
                    }else{
                        $screenwidth = $this->input->get('screenwidth');
                        foreach($activity_info as $k=>$v){
                            $img_info = explode('.',$v['posters_url']);
                            $posters_url = $img_info[0].'-small.'.$img_info[1];
                            $activity_info[$k]['posters_url'] = get_img_url($posters_url,'community');
                            $len = 20;
                            if($screenwidth <= 320){
                                $len = 18;
                            }else if($screenwidth > 320 && $screenwidth <= 800){
                                $len = 20;
                            }else if($screenwidth > 800){
                                $len = 25;
                            }
                            $activity_info[$k]['title'] = truncate_utf8($v['title'],$len);
                            $activity_info[$k]['url_link'] = $this->url_prefix . $this->bid .'/'.$this->sid.'/community/detail?aid='.$v['id_activity'];
                        }
                    }
                }else{
                    //查询用户的活动
//                    $where_a = '(maj.id_user = ' . $uid . ' OR ma.id_business = ' . $uid . ') and ma.state > 0';
//                    //'id_user = ' . $uid;
//                    $activity_info = $this->community->get_community_member($where_a,$offset,$pagesize,'created desc',3);

                    $offset_r = ($offset-1)*$pagesize;
                    $where_ua = "(a.id_business = ".$uid." AND a.object_type = 'user' OR j.id_user = ".$uid.") AND a.state > 0 AND (j.role != 3 OR j.role IS NULL)";
                    $activity_info = $this->community->get_user_activity($where_ua,$offset_r,$pagesize,'a.created desc');

                    if($page_type == 1){
                        return $activity_info;
                    }else{
                        $screenwidth = $this->input->get('screenwidth');

                        foreach($activity_info as $k=>$v){
                            $img_info = explode('.',$v['posters_url']);
                            $posters_url = $img_info[0].'-small.'.$img_info[1];
                            $activity_info[$k]['posters_url'] = get_img_url($posters_url,'community');
                            $len = 20;
                            if($screenwidth <= 320){
                                $len = 18;
                            }else if($screenwidth > 320 && $screenwidth <= 800){
                                $len = 20;
                            }else if($screenwidth > 800){
                                $len = 25;
                            }
                            $activity_info[$k]['title'] = truncate_utf8($v['title'],$len);
                            $activity_info[$k]['url_link'] = $this->url_prefix . $this->bid .'/'.$this->sid.'/community/detail?aid='.$v['id_activity'];
                        }
                    }
                }
            }elseif($uid  && $this->identity == 'visitor' && $id_join == ''){
                //查询用户的活动
//                $where_a = 'maj.cellphone = ' . $uid;
//                $activity_info = $this->community->get_community_member($where_a,$offset,$pagesize,'maj.created desc',4);

                $offset_r = ($offset-1)*$pagesize;
                $where_ua = "j.cellphone = " . $uid ." AND (j.role != 3 OR j.role IS NULL)";
                $activity_info = $this->community->get_user_activity($where_ua,$offset_r,$pagesize,'a.created desc');

                if($page_type == 1){
                    return $activity_info;
                }else{
                    $screenwidth = $this->input->get('screenwidth');

                    foreach($activity_info as $k=>$v){
                        $img_info = explode('.',$v['posters_url']);
                        $posters_url = $img_info[0].'-small.'.$img_info[1];
                        $activity_info[$k]['posters_url'] = get_img_url($posters_url,'community');
                        $len = 20;
                        if($screenwidth <= 320){
                            $len = 18;
                        }else if($screenwidth > 320 && $screenwidth <= 800){
                            $len = 20;
                        }else if($screenwidth > 800){
                            $len = 25;
                        }
                        $activity_info[$k]['title'] = truncate_utf8($v['title'],$len);
                        $activity_info[$k]['url_link'] = $this->url_prefix . $this->bid .'/'.$this->sid.'/community/detail?aid='.$v['id_activity'];
                    }
                }
            }else{
                if(!$id_join && $uid){
                    //查询用户的活动
//                    $where_a = '(maj.id_user = ' . $uid . ' OR ma.id_business = ' . $uid . ') and ma.state > 0';
//                    //'id_user = ' . $uid;
//                    $activity_info = $this->community->get_community_member($where_a,$offset,$pagesize,'created desc',3);

                    $offset_r = ($offset-1)*$pagesize;
                    $where_ua = "(a.id_business = ".$uid." AND a.object_type = 'user' OR j.id_user = ".$uid.") AND a.state > 0 AND (j.role != 3 OR j.role IS NULL)";
                    $activity_info = $this->community->get_user_activity($where_ua,$offset_r,$pagesize,'a.created desc');
                    if($page_type == 1){
                        return $activity_info;
                    }else{
                        $screenwidth = $this->input->get('screenwidth');

                        foreach($activity_info as $k=>$v){
                            $img_info = explode('.',$v['posters_url']);
                            $posters_url = $img_info[0].'-small.'.$img_info[1];
                            $activity_info[$k]['posters_url'] = get_img_url($posters_url,'community');
                            $len = 20;
                            if($screenwidth <= 320){
                                $len = 18;
                            }else if($screenwidth > 320 && $screenwidth <= 800){
                                $len = 20;
                            }else if($screenwidth > 800){
                                $len = 25;
                            }
                            $activity_info[$k]['title'] = truncate_utf8($v['title'],$len);
                            $activity_info[$k]['url_link'] = $this->url_prefix . $this->bid .'/'.$this->sid.'/community/detail?aid='.$v['id_activity'];
                        }
                    }
                }else{
                    //查询用户的活动
//                    $where_a = 'maj.id_join = ' . $id_join;
//                    $activity_info = $this->community->get_community_member($where_a,$offset,$pagesize,'maj.created desc',4);

                    $offset_r = ($offset-1)*$pagesize;
                    $where_ua = "j.id_join = " . $id_join ." AND (j.role != 3 OR j.role IS NULL)";
                    $activity_info = $this->community->get_user_activity($where_ua,$offset_r,$pagesize,'a.created desc');

                    if($page_type == 1){
                        return $activity_info;
                    }else{
                        $screenwidth = $this->input->get('screenwidth');

                        foreach($activity_info as $k=>$v){
                            $img_info = explode('.',$v['posters_url']);
                            $posters_url = $img_info[0].'-small.'.$img_info[1];
                            $activity_info[$k]['posters_url'] = get_img_url($posters_url,'community');
                            $len = 20;
                            if($screenwidth <= 320){
                                $len = 18;
                            }else if($screenwidth > 320 && $screenwidth <= 800){
                                $len = 20;
                            }else if($screenwidth > 800){
                                $len = 25;
                            }
                            $activity_info[$k]['title'] = truncate_utf8($v['title'],$len);
                            $activity_info[$k]['url_link'] = $this->url_prefix . $this->bid .'/'.$this->sid.'/community/detail?aid='.$v['id_activity'];
                        }
                    }
                }
            }
        }

        $status = !empty($activity_info) ? 1 : 0;
        $return = array('status'=>$status,'type'=>$type,'list'=>$activity_info);
        echo json_encode($return);
    }
    
    /**
     * 用户个人中心
     * 
     * @author Jamai
     * @version 2.1
     **/
    public function home() 
    {
      //用户未登录 登录后方可进入个人中心
      if ( ! $this->userid)
        $this->redirect('/user/login');
      
      //查询用户的个人资料
      $where = array('id_user ' => intval($this->userid) ? intval($this->userid) : null);
      $userInfo = $this->user->get_hipigo_user_info($where);
      
      $userInfo = $userInfo[0];
      if($userInfo) {
        $userInfo['head_image_url'] = 
            get_img_url(str_replace('.jpg', '-small.jpg', $userInfo['head_image_url']), 
            'head', 0, 'default-header.png');
      }
      
      //判断是否为达人
      $this->load->model('expert_model', 'expert');
      $role = $this->expert->byFieldSelect('state', 
            array('id_user' => intval($this->userid) ? intval($this->userid) : null), array(), true);
      $userInfo['isRole'] = $role['state'];
      
      $this->smarty->assign($userInfo);
      $this->smarty->display('user_home.html');
    }
    
    /**
     * 验证消费码 
     * 
     * @author Jamai
     * @version 2.1
     **/
    public function code() 
    {
      if( ! $this->userid)
        $this->redirect('/user/login');
      
      $where = 'id_verify_business = ' . $this->userid . 
          ' AND state = 2';
      
      $this->load->model('eticket_item_model', 'eticket');
      $result = $this->eticket->getEticketItemCode($where);
      
      if($result) {
      foreach ($result as $key => $value) {
        $users = $this->user->get_hipigo_user_info('id_user = ' . $value['id_customer']);
        $result[$key]['nick_name'] = urldecode($users[0]['nick_name']);
        unset($users);//销毁
      }
      }
      
      $this->media('layout_section.css', 'css');
      $this->media('validate_code.js', 'js');
      
      $this->smarty->assign('codes', $result);
      $this->smarty->display('validate_code.html');
    }
    
    /**
     * 验证消费码,
     *     由于现在是ajax加载来验证 
     * 
     * @author Jamai
     * @version 2.1
     **/
    public function veri_code()
    {
      if( ! $this->userid)
        $this->redirect('/user/login');
      
      $code = $this->input->post('code') ? $this->input->post('code') : 0;
      $this->load->model('eticket_item_model', 'eticket');
      
      // 存在问题  1.验证type应为user 兼容原数据 采用community
      $where = 'id_business =' .
          $this->userid . ' AND code=' . $code . ' AND state = 1';
      
      $result = $this->eticket->getEticketItemCode($where);
      
      if($result) {
        foreach ($result as $key => $value) {
          $users = $this->user->get_hipigo_user_info('id_user = ' . $value['id_customer']);
          $result[$key]['nick_name'] = urldecode($users[0]['nick_name']);
          unset($users);//销毁
        }
        echo $this->returnJson(1, '消费未使用', $result);
      }
      else 
        echo $this->returnJson(0, '消费为 <span style="color: red">' . $code . '</span> 不存在或者已被使用', array());
      
      return;
    }
    
    /**
     * 电子券验证、使用
     * @author Jamai
     * @version 2.1
     */
    public function code_confirm()
    {
      $this->load->model('ticket_model', 'ticket');
      $codes = $this->input->post('codes');
      
      $this->db->trans_begin();
      try{
        if($codes) {
          $where = 'id_item IN ( ' . implode(',', $codes) . ')';
          $data = array(
       'id_verify_business' => $this->userid,
           'id_verify_shop' => '0',
            'use_time'      => date('Y-m-d H:i:s', time()),
            'state'         => 2,
          );
          
          //更新电子券使用状态
          $this->ticket->update_ticket_item($data, $where);
          
          //读取电子券ID 来修改明细状态
          $items = $this->ticket->getEticketItemCode($where);
          $itemIDs = array();
          foreach ($items as $key => $value) {
            $itemIDs[] = $value['id_item'];
          }
          
          //更新收入明细状态
          $this->load->model('list_model', 'order');
          $this->order->modify_income_item(array('state' => 2), 
                'id_eticket IN (' . implode(',', $itemIDs) . ')');
          
          $this->db->trans_commit();
          echo $this->returnJson(1, '验证成功', array());
          exit;
        }
      }
      catch(Exception $e) {
        $this->db->trans_rollback();
        echo $this->returnJson(0, '验证失败', array());
        exit;
      }
    }
    
    /*
     * zxx  By Jamai
     * 个人资料信息
     */
    public function user_info()
    {

      if ( ! $this->userid)
        $this->redirect('/user/login');

        $this->load->model('hipigouser_model','hipigouser');
        if($_POST){
            $userid = $_POST['userid'];
            $sex = $_POST['sex'];
            $birthday = $_POST['birthday'];
            $account_name = trim($_POST['account_name']);
            $nick_name = trim($_POST['nick_name']);
            $sign = $_POST['sign']?$_POST['sign']:'';
            $data = array('sex'=>$sex,'birthday'=>$birthday,'account_name'=>$account_name,'nick_name'=>urlencode($nick_name),'sign'=>$sign);
            $where = 'id_user = ' . $userid;

            $this->hipigouser->update_hipigo_user($data,$where);
            $return = array('status'=>1,'msg'=>'更新成功');
            echo json_encode($return);
            die;
        }
        $return = array();
    $is_wechat_user = true;
        //查询用户的个人资料
        $where_u = 'id_user = ' . $this->userid;
        $user_info = $this->hipigouser->get_hipigo_user_info($where_u);
        if($user_info[0]){
            $user_info[0]['head_image_url'] = get_img_url(str_replace('.jpg','-small.jpg',$user_info[0]['head_image_url']),'head',0,'default-header.png');
        }
        $return['user_info'] = $user_info[0];

        $return['bind_phone_url'] = $this->baseUrl . '/user/bind_phone';
        $return['edit_phone_url'] = $this->baseUrl . '/user/edit_phone';
        $return['change_pwd_url'] = $this->baseUrl . '/user/changepwd';

        $this->smarty->assign($return);
        $this->smarty->assign('userid', $this->userid);
        
    if(TPL_DEFAULT == 'default') {
      $this->smarty->view('user_info');
    }else{

      $agent = $_SERVER["HTTP_USER_AGENT"];

            if(!strpos($agent,"MicroMessenger")){
          $is_wechat_user = false;
      }

      $this->smarty->assign('is_wechat_user', $is_wechat_user);
       $this->media('layout_section.css', 'css');
       $this->media('mobiscroll.custom-2.6.2.min.css', 'css');
       $this->media('jquery.mobiscroll.js', 'js');
       $this->media('jquery.mobiscroll.app.js', 'js');
       $this->media('user_edit.js', 'js');
      $this->smarty->view('edit_info');

    }
    }
    
    /*
     * zxx
     * 保存头像
     */
    function save_img(){
        $userid = $_GET['userid'];
        $image_url = $_GET['image_url'];

        $this->load->model('hipigouser_model','hipigouser');

        $data = array('head_image_url'=>$image_url);
        $where = 'id_user = ' . $userid;
        $this->hipigouser->update_hipigo_user($data,$where);

        $return = array('status'=>1,'msg'=>'头像修改成功');
        echo json_encode($return);
        die;
    }


    /*
     * zxx
     * 退出登录
     */
    function exit_login(){
        $_SESSION['userid']='';
        $_SESSION['identity']='';
        setcookie('userid', '', time() - (60 * 60 * 24 * 30), '/', '');
        setcookie('identity', '', time() - (60 * 60 * 24 * 30), '/', '');

        header('location:'.$this->url_prefix . $this->bid .'/'.$this->sid.'/community/home?state=exit');
    }
    
  /**
   * 申请达人
   *
   * @version 2.0
   * @author Jamai
   */
  public function expert()
  {
    if($_SESSION['userid'] == ''){
      header('location:' . $this->url_prefix . $this->bid .'/' . $this->sid . '/user/login');
      exit;
    }
    
    $this->load->model('expert_model', 'expert');
    if($this->input->post()) {
      $nickname = $this->input->post('nickname');
      $addr = $this->input->post('addr');
      $card = $this->input->post('card');
      $phone = $this->input->post('phone');
      $pic = $this->input->post('pic');//证件照正面
        $con = $this->input->post('con');//证件照背面
        $select_p = $this->input->post('select_p');//省份
        $select_c = $this->input->post('select_c');//地区

      $this->load->model('expert_model', 'expert');

        $msg = '';
        $where = 'nickname = \''. $nickname.'\'';
        $return = $this->expert->get_expert_count($where);
        if($return == 1){
            $msg .= '[真实姓名]';
        }else{
            $where1 = 'card = \''. $card .'\'';
            $return1 = $this->expert->get_expert_count($where1);
            if($return1 == 1){
                $msg .= '[证件号码]';
            }else{
                $where2 = 'phone = \''. $phone .'\'';
                $return2 = $this->expert->get_expert_count($where2);
                if($return2 == 1){
                    $msg .= '[电话号码]';
                }
            }
        }
        if($return || $return1 || $return2){
            $msg .= '你已申请过,不能提交!';
            $url = $this->url_prefix . $this->bid . '/' . $this->sid . '/user_activity/expert?from=' . 
                  $this->controller . '/' . $this->action;
            
            $return = array('status'=>0,'url'=>$url,'msg'=>$msg);
            echo json_encode($return);
            die;
        }

        if(TPL_DEFAULT == 'mobile'){ //zxx
            $addr = $select_p.'省'.$select_c.'市';
        }
        $data = array(
          'nickname' => $nickname,
           'id_user' => $_SESSION['userid'],
              'card' => $card,
             'phone' => $phone,
              'addr' => $addr,
      'card_img_url' => $pic,
      'card_other_side' => $con,
        'state' => 2,
        );
        
        $this->expert->insert_expert($data);
//      header('location:' . $this->url_prefix . $this->bid . '/' .
//            $this->sid . '/user_activity/index?userid=' . $_SESSION['userid']);
//      return;
        $url = $this->url_prefix . $this->bid . '/' . $this->sid  . '/user_activity/home?from' . 
                $this->controller . '/' . $this->action;
        //$return = array('status' => 1, 'msg' => '', 'url' => $url);
        echo $this->returnJson(1, '申请成功！', array(), array('url' => $url));
        return;
    }
    
    /*
    //判断之前有申请过不再申请
    if($this->expert->expertBy('id_user = ' . $_SESSION['userid'])) {
      
      parent::show_tip('正在审核当中，请勿重复提交！', $this->url_prefix . $this->bid . '/' . 
            $this->sid . '/user_activity/index?userid=' . $_SESSION['userid']);
      exit;
    }
    */
    
    $pageData['url_action'] = $this->url_prefix . $this->bid . '/' . 
          $this->sid . '/user_activity/expert';
    
    $this->smarty->assign($pageData);
      if(TPL_DEFAULT == 'mobile'){//zxx
          $this->media("jquery.cookie.min.js","js");
          $this->media("applyleader.js","js");
          $this->media("address_select.js","js");
          $this->media("ajaxupload.js","js");
          $this->media('layout_section.css','css');
          $this->smarty->view('apply_leader');
      }else
        $this->smarty->view('expert');
  }

    /*
     * zxx
     * 验证电子券页面
     */
    function verify_ticket(){
        $ispage = $this->input->post('ispage');
        $this->load->model('ticket_model','ticket');
        if($ispage == 1){
            $search_key = $this->input->post('search_key') ? $this->input->post('search_key') : '';//搜索关键字
            $where = '';
            if($search_key != ''){
                $where = 'ti.code = \'' .$search_key . '\' and ti.object_type = \'user\'';
            }
            $verify_ticket = $this->ticket->get_ticket_verify($where);
            $return = array('status'=>1,'data'=>$verify_ticket);
            echo json_encode($return);
            die;
        }else{
            $this->load->model('community_model','community');
            $this->load->model('hipigouser_model','hipigouser');
            $where = 'ti.object_type = \'user\' and ti.state = 2';
            $verify_ticket = $this->ticket->get_ticket_verify($where);
            if($verify_ticket){
                foreach($verify_ticket as $k=>$v){
//                    $where_r = 'id_resource = ' . $v['id_object'];
//                    $resource_info = $this->resources->get_resources($where_r);
//                    $verify_ticket[$k]['title'] = $resource_info[0]['resource_title'];
                    $where_a = 'id_activity = ' . $v['id_object'];
                    $comm_info = $this->community->get_community_info($where_a);
                    $verify_ticket[$k]['title'] = $comm_info[0]['name'];

                    $where_hu = 'id_user = ' . $v['id_customer'];
                    $hipigo_user = $this->hipigouser->get_hipigo_user_info($where_hu);
                    if($hipigo_user){
                        $verify_ticket[$k]['nick_name'] = $hipigo_user[0]['nick_name'];
                    }
                }
            }
            $this->smarty->assign('verify_ticket',$verify_ticket);
            $this->smarty->view('verify_ticket');
        }
    }

    /**
     * zxx
     * 电子券使用
     */
    function change_ticket_state(){
        $this->load->model('ticket_model','ticket');
        $id_item = $this->input->post('id_item');
        $where_item = 'id_item = ' . $id_item;
        $update_date = array(
            'id_verify_business'=>$this->bid,
            'id_verify_shop'=>$this->sid,
            'use_time'=>date('Y-m-d H:i:s', time()),
            'state'=>2
        );
        //更新电子券使用状态
        $this->ticket->update_ticket_item($update_date,$where_item);
        $resp = array(
            'status' => 1,
            'msg' => '',
            'data' => ''
        );
        die(json_encode($resp));
    }


    /*
     * zxx
     * 推荐达人
     */
    function recommend_leader(){
        $this->media('layout_section.css','css');
        $this->smarty->view('recommend_leader');
    }

    /**
     * zxx
     * 推荐商户列表
     */
    function leader_lists(){
        $offset = $this->input->post('offset');
        $offset = empty($offset) ? 1 : $offset;
        $pagesize = 25;
        $kw = $this->input->post('kw') ? $this->input->post('kw') : '';//筛选
        $this->load->model('expert_model','expert');
        $where = 'is_recommend = 1';
        if($kw){
            $where .= ' and nickname like "%'.$kw.'%"';
        }
        $expert_info = $this->expert->get_expert_info($where,$offset,$pagesize);
        if($expert_info){
            foreach($expert_info as $k=>$v){
                $head_image_url = $v['head_image_url'];
                $expert_info[$k]['head_image_url'] = get_img_url(str_replace('.jpg','-small.jpg',$head_image_url),'head',0,'default-header.png');
//                $f_num = strpos($head_image_url,'http');
//                if($f_num === false){
//                    $expert_info[$k]['head_image_url'] = get_img_url($head_image_url,'head',0,'default-header');
//                }else{
//                    $expert_info[$k]['head_image_url'] = $head_image_url;
//                }
            }
        }
        $page_types = 'recommendleader';
        $this->smarty->assign('page_type', $page_types);
        $this->smarty->assign('expert_info', $expert_info);
        echo $this->smarty->display('lists.html');
        exit;
    }

    /*
     * zxx
     * 用户使用协议页面
     */
    function agreement(){
        $this->media('layout_section.css','css');
        $this->smarty->view('agreement');
    }

}
