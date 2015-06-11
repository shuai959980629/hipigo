<?php
/**
 * 
 * @copyright(c) 2014-08-20
 * @author sc
 * @
 */

class square extends WX_Controller
{
    
  private $id_business ;//商家ID
  private $id_shop ;//门店ID
  private $id_activity;//活动id
  private $id_open ;//微信id;  
  private $userID;

  CONST PAGER = 1;
  public function __construct(){

    parent::__construct();
    $this->load->model('square_model','square');
    $this->id_business = $this->bid;
    $this->id_shop = $this->sid;
    
    //if($_SESSION['userid'] || $_COOKIE['userid']){
    //  $this->userID = $_SESSION['userid'] ? $_SESSION['userid'] : $_COOKIE['userid'];
    //}
    $this->userID = $this->userid;

   }

  /**
   * 
   * @copyright(c) 2014-08-20
   * @author sc
   * @广场首页
   * @$is_content_update 是否有最新内容
   */
  public function square_index()
  {
    //判断当前用户是否为达人
    $this->load->model('expert_model', 'expert');
    $role = $this->expert->byFieldSelect('state', 
          array('id_user' => intval($this->userid) ? intval($this->userid) : null), array(), true);
    
    $this->smarty->assign('isRole', $role['state']);
    //获取数据
    //$this->square_list();
    $this->smarty->view('square');
    //<!--{include file="square_list.html"}-->
  }

  /**
   * 
   * @copyright(c) 2014-08-20
   * @author sc
   * @广场活动列表（猜你喜欢）
   * @$community_count 返回实际条数
   * @$result 查询结果集
   */
  public function square_list(){
    /*
    $select = 'id_activity, id_business,object_type,name,content,posters_url,join_price';
    $num = $this->input->get('pagemore')?$this->input->get('pagemore'):1;
    $is_spread = 1;
    $state = 2;
    $where = 'is_spread = '.$is_spread.' and state = '.$state;
    $options = array(
        'order' => 'created',
      'offset' =>0,
            'limit' =>$num*10
     );
    
    $result = $this->square->getSquareCommunities($select,$where,$options);
    $community_count = count($result);

    foreach($result as $k=>$v){

         if($v['object_type']=='user'){
          $result[$k]['bn_home'] = '/wapi/90/0/user_activity/index?userid='.$v['id_business'];
         }else{
          $result[$k]['bn_home'] = '/wapi/'.$v['id_business'].'/0/community/home?bid='.$v['id_business'];
         }

        $result[$k]['bn_img'] = 'Content-type:application/x-javascript';
        
//        if($v['object_type']=='user'){
//          $path = get_img_url('75d3495acd92c6abf47c24df31e41e8f.jpg','keyword_reply',1,'pic_005.jpg');
//        }

      }

    $this->smarty->assign('community_count',$community_count);
    $this->smarty->assign('result',$result);
    $this->smarty->view('square_list');
    */
    $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;
    $limit = 25;
    $this->load->library('community_logic', 'mobile');
    $where = 'is_spread = 1 and state = 2';
    //$where = "(id_business = {$this->bid} OR re.owner = {$this->bid} OR id_business_source = {$this->bid})";
    $data = $this->community_logic->getCommunities($where, $params = array('offset' => ($offset - 1) * $limit, 'limit' => $limit));
    // debug($data);
    
    $this->smarty->assign(array('lists' => $data));
    $this->smarty->view('home_list');
    //echo $this->smarty->display('home_list.html');
    //return;
    

  }

}