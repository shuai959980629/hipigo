<?php
/**
 * 
 * @copyright(c) 2013-12-24
 * @author msi
 * @version Id:acl.php
 */


class Acl {

	private $pre_access;
	private $CI;
	private $users;
	private $session_id;
	 
	function Acl()
	{
		$this->CI = & get_instance();
		$this->CI->load->library('session');
		$this->pre_access = BIZ_PATH.$this->CI->uri->segment(1).'/'.$this->CI->uri->segment(2);
	}
	 
	function auth()
	{

		if(in_array($this->CI->uri->segment(1), array('user','files'))){
			return TRUE;
		}

		//检查是否登陆
		if($this->init_login() == FALSE){
            setcookie('last_page', 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], time() + (60 * 60 * 24), '/', '');
			$url = 'http://'.$_SERVER['HTTP_HOST'].BIZ_PATH."user/login";

			echo '<script type="text/javascript">window.location.href="'.$url.'";</script>';
			exit;
		}else{
			if($this->check_access() == FALSE){
				//权限验证没通过
				$this->returen_status(0, '没有操作权限');
			}
		}
		
	}
	
	
	/**
	 * 返回操作结果
	 * @param int $status 操作状态0失败，1成功
	 * @param string $msg 提示信息
	 * @param array $data 其他信息
	 */
	public function returen_status($status,$msg,$data = array()){
	
		$resp = array(
				'status' => $status,
				'msg' => $msg,
				'data' => $data
		);
	
		if($this->CI->input->is_ajax_request()){
			die(json_encode($resp));
		}else{
			$this->CI->smarty->assign('resp',$resp);
			$this->CI->smarty->display('success.html');
		}
		exit(0);
	
	}
	
	
	private function init_login(){
		
		$sess = $this->CI->session->all_userdata();
		$this->session_id = $sess['session_id'];
		
		$merchent = $this->get_session_data($this->session_id);
		if(!empty($merchent['id_user'])){
			$this->users = $merchent;
			return TRUE;
		}
		return FALSE;
	}
	
	
	//检测权限
	private function check_access(){
		
		$this->CI->load->model('user_model');
		$skip_arr = $this->CI->user_model->check_menu_exist_by_uri($this->pre_access);
		if(empty($skip_arr)){
			return TRUE;
		}else{
			$menu = $this->get_menu($this->users['id_user']);
			$access = $this->get_accesslist($menu);
			if(!empty($this->pre_access) && !empty($access) && in_array($this->pre_access, $access)){
				return TRUE;
			}else{
				return FALSE;
			}
		}
	}
	
	private function get_session_data($key){
	
		$this->CI->load->driver('cache');
		$return = array();
		if($this->CI->cache->memcached->is_supported() === TRUE){
			$cache = $this->CI->cache->memcached->get($key);
			if(!empty($cache[0])){
				return $cache[0];
			}
		}
		return $return;
	
	}
	
	
	/**
	 * 获取用户菜单、权限
	 * @param $userID 用户ID
	 * @param $type 类型(0：权限，2：菜单)
	 */
	private function get_menu($uid)
	{
		$menu = array();
		if(empty($uid)){
			return $menu;
		}
		$this->CI->load->model('right_model','right');
	
		$where = 'mu.id_user = '.$uid;
		$arr = $this->CI->right->get_user_right($where);
		return $arr;
	}
	
	
	/**
	 * 获得权限列表
	 * @param array $arr
	 * @return array 具有的权限列表
	 */
	private function get_accesslist($arr){
	
		$access = array();
		if(is_array($arr)){
			foreach ($arr as $key => $acc){
				if(!empty($acc['menu_url'])){
					$access[] = $acc['menu_url'];
				}
			}
		}
		return $access;
	
	}
	
	
}


/* End of file acl.php */