<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Community_Logic
{
  
  private $_ci;//赋值对象，调用相关函数
  private $from;
  
  /**
   * 初始化函数
   * @param $from String 来自位置 default mobile
   *      mobile:手机
   *      web:前端 
   *      user:用户管理中心
   *      manage:商家管理中心 
   *      admin:后台
   * @author Jamai
   * @version 2.1
   **/
  public function __construct($from = 'mobile')
  {
    $this->_ci  = &get_instance();
    $this->from = $from;
  }
  
  /**
   * 社区活动列表
   * 
   * @param 
   * @author Jamai
   * @version 2.1
   **/
  public function getCommunities($where = '', $params = array(), $count = false)
  {
    $this->_ci->load->model('community_model', 'community');
    $limit = 25;
    //做数据查看使用
    $select = '*';
    
    if($count)
      $select = 'COUNT(*)';
    
    if( ! $params['order'])
      $params['order'] = 'id_activity DESC';
    
    if( ! $params['limit'])
      $params['limit'] = $limit;
    
    switch ($this->from) {
      case 'admin':
        //$select = '';
        break;
      case 'web':
        break;
      case 'user':
        break;
      case 'manage':
        break;
      default ://default mobile
        $select = 'activity.id_activity, name, id_business, object_type, join_price, content, posters_url, review_count, appraise_count';
        $condition = 'state != -1';
        
        //获取活动列表
        if($where)
          $condition .= ' AND ' . $where;
        $result = $this->_ci->community->getCommunities($select, $condition, $params);
        
        $this->_ci->load->model('review_model', 'review');
        $this->_ci->load->model('hipigouser_model', 'user');

        foreach ($result as $key => $value) {
          if($value['object_type'] == 'user')
            $result[$key]['img_path'] = $this->showImage($value['id_business'], 'user');
          else
            $result[$key]['img_path'] = $this->showImage($value['id_activity'], 'community');//,array(80,80)
          $result[$key]['content'] = strip_tags($value['content']);
            //替换表情
            $result[$key]['content'] = $this->_ci->community->replace_smile($value['content']);

          //获取最新一条评论
          $reviewWhere = "id_object = {$value['id_activity']} AND object_type='community'";
          $reviews = (Array) $this->_ci->review->byNewReview($reviewWhere);
          //获取用户名称
          $user_r = $this->_ci->user->byFieldSelect(array('id_user', 'nick_name', 'account_name'), 
                                  array('id_user' => $reviews['id_customer']), true);
          $reviews['id_customer'] = $user_r;
          
          $joins = $this->_ci->community->get_community_join_count("id_activity = {$value['id_activity']} AND id_user > 0", 1, 'j.updated DESC');
          foreach ($joins as $j => $join) {
            if($join['id_user']) {
              $user_j = $this->_ci->user->byFieldSelect(array('id_user', 'nick_name', 'account_name'), 
                                  array('id_user' => $join['id_user']), true);
              break;
            }
          }
          
          $user_j['count_join'] = count($joins);
          $result[$key]['join'] = $user_j;
          $result[$key]['reviews'] = $reviews;
        }
        
        break;
    }
    
    return $result;
  }
  
  /**
   * 处理显示头像和图片
   * @param objectID  对象ID   
   *     如type==user 则为用户ID  type==community  则为商户ID
   * @param type user community
   * @author Jamai
   * @version 2.1
   **/
  public function showImage($objectID, $type = 'user',$opt=array())
  {
    if($type == 'community') {//处理商户
      $this->_ci->load->model('community_model', 'community');
      $community = $this->_ci->community->byFieldSelect('id_activity, id_business, content, posters_url', 
                  array('id_activity' => $objectID), true);
      $posterUrl = get_img_url( $community['posters_url'], 'community', false, 'default-activity.png',0,$opt);  //zxx
//        $posterUrl = get_img_url( $community['posters_url'], 'community', false, 'default-activity');

      if($posterUrl == 'http://' . $_SERVER['HTTP_HOST'] . '/attachment/defaultimg/default-activity.png') {
        //默认海报图
        $preg = "/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i";
        preg_match($preg, $community['content'], $match);
        if($match)
          if(file_exists(DOCUMENT_ROOT . $match[2])) {
            return 'http://' . $_SERVER['HTTP_HOST'] . $match[2];
          }
        return $posterUrl;
      }
      else {
        return $posterUrl;
      }
    }
    else {//处理用户
      
      $this->_ci->load->model('hipigouser_model', 'user_hipigo');
      $field = 'id_user, head_image_url';
      $where = array('id_user' => intval($objectID) ? intval($objectID) : null);
      
      $user = $this->_ci->user_hipigo->byFieldSelect($field, $where, true);
      
      $return = get_img_url(str_replace('.jpg', '-middle.jpg', 
                  $user['head_image_url']), 'head', false, 'default-header.png');
      
      return $return;
    }
  }
  
  public function FormatData($result)
  {
    foreach ($result as $key => $value) {
      if( ! $value['join_price'] || $value['join_price'] == '0.00') {
        $result[$key]['join_price'] = '免费';
      }
      if($value['total'] <= -1) {
        $result[$key]['total'] = '充足';
      }
      else if($value['total'] == 0) {
        $result[$key]['total'] = '售罄';
      }
      
    }
    return $result;
  }
  
}


/* End of file community_logic.php */
/* Location: ./community_logic.php */