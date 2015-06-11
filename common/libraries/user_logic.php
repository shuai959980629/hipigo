<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class User_Logic
{

  private $_CI;
  
  public function __construct()
  {
    $this->_CI = &get_instance();
  }

	/**
	 * 
	 * @copyright(c) 2014-08-20
	 * @author sc
	 * @��ȡ�û��Ķ�״̬
	 * @$is_content_update �Ƿ�����������
	 */
  public function isContentUpdate($id_user){

		$is_content_update = false;

		if(empty($id_user)){return $is_content_update;}

		$this->load->model('square_model','square');

		$select = 'UNIX_TIMESTAMP(created) as created';
		$state = 2;
		$where = 'state = '.$state;
		$options = array('order' => 'created desc','offset' =>0,'limit' =>1);
		
		$result = $this->square->getSquareCommunities($select,$where,$options);
		$last_created_time = $result[0]['created'];

		$select = 'UNIX_TIMESTAMP(last_login_time) as last_login_time';
		$where = 'id_user = 1';//.$is_spread;
		$result = $this->square->getUserInformation($select,$where);
		$last_login_time = $result[0]['last_login_time'];
		
		if($last_login_time < $last_created_time){$is_content_update = true;}

		return $is_content_update;

  }
  
  /**
	 * ��ȡ�û��Ķ�״̬ 
   *      �޸�֮ǰisContentUpdate ����
	 * @copyright(c) 2014-08-20
	 * @author Jamai
	 * @version 2.1
	 * @boolean  true: ����������  false ����������
	 */
  public function isNew ($userID, $from = 'life')
  {
    $return = false;
    
    $this->_CI->load->model('hipigouser_model', 'users');
    $lastTime = $this->_CI->users->getUserActionLogs(
                'UNIX_TIMESTAMP(dateline) AS dateline',
                array('id_user' => intval($userID) ? intval($userID) : null, 
                      'action_type' => $from), true);
    $time = array();
    switch ($from) {
      case 'resource':
        $this->_CI->load->model('resources_model', 'resource');
        $time = $this->_CI->resource->byFieldSelect('UNIX_TIMESTAMP(created) AS created', '',
                    array('order' => 'created DESC', 'limit' => 1), true);
        break;
      
      case 'business':
        $this->_CI->load->model('shop_model', 'shop');
        $time = $this->_CI->shop->byFieldSelect('UNIX_TIMESTAMP(updated) AS created', '', 
                    array('order' => 'updated DESC', 'limit' => 1), true);
        break;
      
      case 'expert':
        $this->_CI->load->model('expert_model', 'expert');
        $time = $this->_CI->expert->byFieldSelect('UNIX_TIMESTAMP(updated) AS created', '', 
                    array('order' => 'updated DESC', 'limit' => 1), true);
        break;
      
      case 'resource_by':
        $this->_CI->load->model('resources_model', 'resource');
        $time = $this->_CI->resource->byFieldSelect('UNIX_TIMESTAMP(created) AS created', '',
                    array('order' => 'created DESC', 'limit' => 1), true);
        break;
      //�����
      case 'activity_publish':
        $this->_CI->load->model('community_model', 'community');
        $time = $this->_CI->community->byFieldSelect('UNIX_TIMESTAMP(created) AS created', 
                    array('id_business' => $userID, 'object_type' => 'user'), 
                    array('order' => 'created DESC', 'limit' => 1), true);
        break;
      //�μӻ
      case 'activity_by':
        $this->_CI->load->model('communityjoin_model', 'communityJoin');
        $time = $this->_CI->communityJoin->byFieldSelect('UNIX_TIMESTAMP(created) AS created', 
                    array('id_user' => $userID),
                    array('order' => 'update_time DESC', 'limit' => 1), true);
        break;
      case 'wallet':
        $time = array();
        break;
      case 'msg': //��δʵ��
        break;
      
      case 'life': //life
        $this->_CI->load->model('community_model', 'community');
        $time = $this->_CI->community->byFieldSelect('UNIX_TIMESTAMP(created) AS created', '', 
                    array('order' => 'created DESC', 'limit' => 1), true);
        break;
    }
    
    if($lastTime['dateline'] <= $time['created'])
      $return = true;
    
    return $return;
  }
  
  public function readNew($userID, $page = 'community/coollife')
  {
    $actionType = '';
    switch ($page) {
      case 'community/coollife': //life
        $actionType = 'life';
        break;
      case 'resource/index':
        $actionType = 'resource';
        break;
      case 'home/hotbusiness':
        $actionType = 'business';
        break;
      case 'user_activity/recommend_leader':
        $actionType = 'expert';
        break;
      case 'resource/bylist':
        $actionType = 'resource_by';
        break;
      case 'user_activity/bylist':
        $actionType = 'activity_by';
        break;
      case 'user_activity/my_wallet':
        $actionType = 'wallet';
        break;
      case 'msg': //��δʵ��
        $actionType = 'msg';
        break;
      
    }
    
    if($actionType) {
      $this->_CI->load->model('hipigouser_model', 'users');
      
      $actionLogs = $this->_CI->users->getUserActionLogs('*',
                array('id_user' => intval($userID) ? intval($userID) : null, 
                      'action_type' => $actionType), true);
      
      if($actionLogs) {
        $this->_CI->users->updateActionLogs(
            array('dateline' => date('Y-m-d H:i:s', time())), 
            array('id_user' => $actionLogs['id_user'], 
                'action_type' => $actionLogs['action_type']));
      }
      else {
        $this->_CI->users->insertActionLogs(
            array('id_user' => $userID, 
                'action_type' => $actionType, 
                'dateline' => date('Y-m-d H:i:s', time())));
      }
    }
    
  }
  
}


/* End of file user_logic.php */
/* Location: ./user_logic.php */