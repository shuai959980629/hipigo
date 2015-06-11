<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * 搜索活动或资源
 * @copyright(c) 2014-08-22
 * @author zxx
 * 
 */
class Search extends WX_Controller
{
  private $id_business ;//商家ID
  private $id_shop ;//门店ID
  private $userID;

  CONST PAGER = 1;
  public function __construct(){
    parent::__construct();
    $this->load->model('community_model','community');
    $this->id_business = $this->bid;
    $this->id_shop = $this->sid;
    $this->smarty->assign('bid', $this->bid);
    $this->smarty->assign('sid', $this->sid);

    if($_SESSION['userid'] || $_COOKIE['userid'])
      $this->userID = $_SESSION['userid'] ? $_SESSION['userid'] : $_COOKIE['userid'];
  }

    /*
     * zxx
     * 搜索页面
     */
    function index(){
        $type = $this->input->get('type')?$this->input->get('type'):'activity';//类型  activity:搜索活动页面  resource：搜索资源页面
        $this->load->model('tag_model','tag');
        $tag_info = $this->tag->get_tag('',1,12);
        $tag_color = array('blackish_green','red','orange_color','blue','grn','yellow');
        foreach($tag_info as $k=>$v){
            $tag_info[$k]['color'] = $tag_color[rand(0,5)];
        }
        $data['tag_info'] = $tag_info;
        //热门关键字
        $tag_info = $this->tag->get_tag('',1,2,'search_num desc');
        $data['tag'] = $tag_info;

        $data['type'] = $type;
        $this->smarty->assign($data);
        $this->media('layout_section.css','css');
        $this->smarty->view('search');
    }

    /*
     * zxx
     * 搜索列表
     */
    function search_lists(){
        $type = $this->input->get('type')?$this->input->get('type'):'activity';//activity 搜索活动  resource 搜索资源
        $offset = $this->input->post('offset');
        $offset = empty($offset) ? 1 : $offset;
        $pagesize = 25;
//        $total = $pagesize * ($offset > 0 ? ($offset - 1) : 0);

        $kw = $this->input->post('kw') ? $this->input->post('kw') : '';//筛选
        $data=array();
        $result = array();
        if($kw){
            if($type == 'activity'){
                $where = '(ca.name like \'%'.$kw.'%\' or t.tag_name like \'%'.$kw.'%\') and ca.state = 2';
                $result = $this->community->get_community_info($where,$offset, $pagesize, '',2);
                if($result){
                    $this->load->model('communitytag_model','communitytag');
//                    $this->load->model('hipigouser_model','hipigouser');
//                    $this->load->model('business_model','business');
                    foreach($result as $k=>$v){
                        $result[$k]['name'] = str_replace($kw, '<em class="blackish_green">'.$kw.'</em>', $result[$k]['name']);
                        $result[$k]['t_name'] = '';
                        $where_t = 'id_activity = ' . $v['id_activity'];
                        $tag_info = $this->communitytag->get_tag($where_t);
                        if($tag_info)
                            foreach($tag_info as $kt=>$vt){
                                if($vt['tag_name'] == $kw){
                                    $vt['tag_name'] = '<em class="blackish_green">'.$vt['tag_name'].'</em>';
                                }
                                $result[$k]['t_name'] .= $result[$k]['t_name'] == '' ? $vt['tag_name'] : ','.$vt['tag_name'];
                            }
                        $result[$k]['head_img'] = '';
                        if($v['object_type'] == 'user'){
                            $head_img = $this->showImage($v['id_business'],'user');
                            $result[$k]['head_img'] = $head_img;
//                            $where_u = 'id_user = ' . $v['id_business'];
//                            $user_info = $this->hipigouser->get_hipigo_user_info($where_u);
//                            if($user_info){
//                                $f_num = strpos($user_info[0]['head_image_url'],'http');
//                                if($f_num === false)
//                                    $result[$k]['head_img'] = get_img_url($user_info[0]['head_image_url'],'head',0,'default-header');
//                                else
//                                    $result[$k]['head_img'] = $user_info[0]['head_image_url'];
//                            }
                        }elseif($v['object_type'] == 'community'){
                            $head_img = $this->showImage($v['id_activity'],'community');
                            $result[$k]['head_img'] = $head_img;
//                            $where_u = 'id_business = ' . $v['id_business'] . ' and object_type = \'bn\'';
//                            $bus_info = $this->business->get_business_attachment($where_u);
//                            if($bus_info){
//                                $f_num = strpos($bus_info[0]['head_image_url'],'http');
//                                if($f_num === false)
//                                    $result[$k]['head_img'] = get_img_url($bus_info[0]['image_url'],'attachment');
//                                else
//                                    $result[$k]['head_img'] = $bus_info[0]['image_url'];
//                            }
                        }
                    }
                }
            }elseif($type == 'resource'){
                $this->load->model('resources_model','resources');
                $where = 'resource_title like "%'.$kw.'%"';
                $result = $this->resources->get_resources($where, 'created DESC' ,$offset, $pagesize);
                if($result){
                    foreach($result as $kr=>$vr){
                        //高亮显示搜索关键字
                        $result[$kr]['resource_title']=str_replace($kw, '<em class="blackish_green">'.$kw.'</em>', $vr['resource_title']);
                        $result[$kr]['logo'] = get_img_url($vr['logo'],'logo');
                    }
                }
            }
        }

//        var_dump($result);
        $data['list_info'] = $result;
        $data['page_type'] = 'search_'.$type;//lists.html 页面的判断类型

        $this->smarty->assign($data);
        echo $this->smarty->display('lists.html');
        exit;
    }


}