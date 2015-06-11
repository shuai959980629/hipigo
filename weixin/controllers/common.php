<?php
/**
 * 
 * @copyright(c) 2014-04-03
 * @author Jamai
 * @version Id:Resource.php
 */

class Common extends WX_Controller
{
  
  public function __construct()
  {
    parent::__construct();
  }
  
  public function popup()
  {

    $source = $this->input->get('source');
    if($source == 'act_resource_by'){
        $source = 'resource_by';
    }
    switch ($source) {
      case 'resource':

      case 'resource_by':
        $resourceID = intval($this->input->get('rid')) ? 
            intval($this->input->get('rid')) : null;
        
        $this->load->model('resources_model', 'resource');
        $info = $this->resource->get_resource_info($resourceID);
        $this->load->library('resource_logic');
        
        $info = $this->resource_logic->formatData(array($info));
        
        $this->smarty->assign('info', $info[0]);
        echo $this->smarty->display('tips.tpl');
        return;

      case 'activity': //活动详情弹出层
		$aid = $this->input->get('rid');
		$this->load->model('community_model','community');
	    $info = $this->community->activity_detail($aid);
		$info['join_condetion'] = unserialize($info['join_condetion']);
        $this->smarty->assign('info', $info);
		echo $this->smarty->display('active_details_popup.tpl');
        return;

      case 'veriCode':
        
        echo $this->smarty->display('verfication_code.tpl');
        return;

      case 'wallet':
		echo $this->smarty->display('my_wallet.tpl');
        return;

      case 'logout':
		echo $this->smarty->display('logout.tpl');
        return;

      default:
        return;
    }
  }
  
  //中转支付页面 处理参数
  public function pay()
  {
    $num    = $this->input->post('num');
    $rid    = $this->input->post('rid');
    $source = $this->input->post('source');
    
    if( ! $this->userid) {
      $url = '/wapi/' . $this->bid . '/' . $this->sid . '/user/login';
      echo $this->returnJson(0, '未登录', $url);
      return;
    }
    
    switch ($source) {
      case 'resource':
      case 'resource_info':
        $url = '/wapi/' . $this->bid . '/' . $this->sid . '/resource/bylist';
        break;
      default:
        //兼容上一版本
        $url = '/wapi/' . $this->bid . '/' . $this->sid . '/community/publish';
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
      exit;
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
        'id_user' => $this->userid));
    
    $this->db->trans_begin();
    try {
      //增加记录为我的资源
      if($result) {//修改
        $this->resources->update_by_resource(
          array(
                 'num' => 'num + ' . $num,
             'updated' => '"' . date('Y-m-d H:i:s', time()) . '"'),
          array(
             'id_user' => $this->userid, 
         'id_resource' => $rid));
      }
      else { //添加
        $this->resources->insert_by_resource(array(
          'id_resource' => $rid,
                  'num' => $num,
              'id_user' => $this->userid));
      }
      
      //生成电子券
      $this->load->model('list_model', 'order');
      if (intval($num)) {
        for ($i = 0; $i < $num; $i++) {
          $this->order->create_eticket(
            $rid, $this->userid, $_SESSION['identity'], $this->bid, $this->sid, 'resource');
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
  
  public function codes()
  {
	if( ! $this->userid) 
       $this->redirect('/user/login');
    $source = $this->input->post('source');
    $id = $this->input->post('id');
    $this->load->model('eticket_item_model', 'eticket');

    
    $codes = array();
    
    if($source == 'resource_by') {//获取资源的消费码
      $codeWhere = 'object_type = "resource" AND id_object = ' .  $id . ' AND id_customer=' . $this->userid;
    }
    else if($source == 'activity_by') {//object_type = "community" AND
      $codeWhere = 'id_object = ' . $id . ' AND id_customer = ' . $this->userid;
    }
    
    $codes = $this->eticket->getEticketItemCode($codeWhere);
    foreach ($codes as $key => $value) {
      if($value['state'] == 2) {
        $codes[$key]['state'] = '已使用';
      }
      else {
        $codes[$key]['state'] = '';
      }
    }
    
    echo $this->returnJson(1, '', $codes);
    exit;
  }
  
  
}