<?php
/**
 * 
 * @copyright(c) 2014-04-03
 * @author Jamai
 * @version Id:Resource.php
 */

class Resource extends WX_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('resources_model', 'resource');
  }
  
  public function index()
  {
    $this->media('resource.js', 'js');
    $this->media('layout_section.css', 'css');
    $this->smarty->display('resources.html');
  }
  
  public function lists()
  {
    $kw = $this->input->post('kw') ? $this->input->post('kw') : '';
    $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;
    $source = $this->input->post('source') ? $this->input->post('source') : 'resource';
    
    $pager = 25;
    $this->load->library('resource_logic');
    
    if($source == 'resource_by') {
      $where = 'id_user = ' . $this->userid;
      
      if($kw)
        $where .= ' AND resource_title LIKE "%' . $kw . '%"';
      
      $result = $this->resource->get_by_resources($where, 'relate.created DESC', $offset, $pager);
      $result = $this->resource_logic->formatData($result);
      
      //读取消费码
      $this->load->model('eticket_item_model', 'eticket');
      
      foreach ($result as $key => $value) {
        $codeWhere = 'object_type = "resource" AND id_object=' . 
              $value['id_resource'] . ' AND id_customer=' . $this->userid;
        $codes = $this->eticket->getEticketItemCode($codeWhere);
        $result[$key]['codes'] = $codes;
      }
    }
    else {
      $where = 'num != 0 AND deleted = 1';
      
      if($kw) 
        $where .= ' AND resource_title LIKE "%' . $kw . '%"';
      
      $result = $this->resource->get_resources($where, 'created DESC', $offset, $pager);
      $result = $this->resource_logic->formatData($result);
    }
    
    $this->smarty->assign(
      array(
        'lists'  => $result,
        'source' => $source,
        'pay_buy' => PAY_BUY,//系统内增加固定价格
      ));
    echo $this->smarty->display('list-resource-activity.tpl');
    exit;
  }
  
  public function info()
  {
    $resourceID = intval($this->input->get('rid')) ? 
            intval($this->input->get('rid')) : null;
    
    $resourceInfo = $this->resource->get_resource_info($resourceID);
    
    if( ! $resourceInfo) {
      echo '该资源不存在或已关闭';
      exit;
    }
    
    $this->load->library('resource_logic');
    $resourceInfo = $this->resource_logic->formatData(array($resourceInfo));
    
    $this->smarty->assign('resourceInfo', $resourceInfo[0]);
    
    $this->media('layout_section.css', 'css');
    $this->smarty->display('resource_info.html');
  }
  
  public function bylist()
  {
    if( ! $this->userid)
      $this->redirect('/user/login');

    $this->media('resource_by.js', 'js');
    $this->media('layout_section.css', 'css');
    $this->smarty->display('resource_by.html');
  }
  
  public function demo() 
  {
    $this->smarty->display('demo.html');
  }
  
  public function buy()
  {
    $num    = $this->input->get('num');
    $rid    = $this->input->get('rid');
    $source = $this->input->get('source');
    
    if( ! $this->userid) {
      $this->redirect('/user/login');
    }
    
    switch ($source) {
      case 'resource':
      case 'resource_info':
        $url = '/resource/bylist';
        break;
      default:
        //兼容上一版本
        $url = '/community/publish';;
        break;
    }
    
    if( ! intval($rid)) 
      //$url = '/wapi/' . $this->bid . '/' . $this->sid . '/community/publish';
      //echo $this->returnJson(0, '非法操作', $url);
      $this->redirect($url);
    
    $this->load->model('resources_model', 'resources');
    $resource = $this->resources->get_resource_info($rid);

    if($resource['price'] && $resource['price'] != '0.00') { //收费
      $url = '/wapi/' . $this->bid . '/' . $this->sid . 
              '/order/pay?rid=' . $rid . '&num=' . $num . '&state=resource';
      
      $this->redirect($url);
      //echo $this->returnJson(1, '成功', $url); return;
    }
    
    /** 免费 */
    
    //减少 资源的数量
    $res_num=0;
    if($resource['num'] <= -1){
      $res_num = -1;
    }
    elseif($resource['num'] == 0){
      //$url = '/wapi/' . $this->bid . '/' . $this->sid . '/community/publish';
      //echo $this->returnJson(0, '资源没有数量了', $url);
      //return;
      $this->redirect($url);
    }
    else{
      $res_num = $resource['num'] - $num;
      if($res_num < 0){
        //$url = '/wapi/' . $this->bid . '/' . $this->sid . '/community/publish';
        //echo $this->returnJson(0, '资源数量不足', $url);
        //return;
        $this->redirect($url);
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
            $rid, $this->userID, $_SESSION['identity'], $this->bid, $this->sid, 'resource');
        }
      }
      $this->db->trans_commit();
      
      //$url = '/wapi/' . $this->bid . '/' . $this->sid . '/community/publish?rid=' . $rid;
      //echo $this->returnJson(2, '购买成功！', $url);
      //return;
      $this->redirect($url);
    }
    catch (Exception $e) {
      $this->db->trans_rollback();
    }
    /*
    $url = '/wapi/' . $this->bid . '/' . $this->sid . 
              '/resource/index';
    $this->redirect($url, 'js');
    */
  }
  
  
}