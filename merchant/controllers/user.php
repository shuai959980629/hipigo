<?php
/**
 * 
 * @copyright(c) 2013-11-20
 * @author msi
 * @version Id:user.php
 */

class User extends Admin_Controller
{	
	
	/**
	 * 商家登陆
	 */
	public function login(){
		$this->load->helper(array('form','url'));
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<div class="alert alert-error"><span>','</span></div>');
		if($this->input->post('username')){
			$this->form_validation->set_rules('username','用户名','required|min_length[4]');
			$this->form_validation->set_rules('password','密码','required');
			if(FALSE !== $this->form_validation->run()){
                $username = $this->input->post('username');
                $username = str_replace("'","",$username);

				$merchent = $this->merchent_login($username, $this->input->post('password'));
				if($merchent === FALSE){
					$this->returen_status(0, $this->errors);
				}else{
                    //登录成功更新用户最后一次登录时间
                    $id_user = $merchent['id_user'];
                    $this->load->model('user_model','user');
                    $data = array('last_time'=>date('Y-m-d H:i:s', time()));
                    $where = 'id_user = ' . $id_user;
                    $this->user->update_merchant_user($data,$where);

                    if($_COOKIE['last_page']){
                        header('location:'.$_COOKIE['last_page']);
                        setcookie('last_page', '', time() -1, '/', '');
                    }else{
                        header('location:'.$this->url);
                    }
					exit;
				}
			}else{
				$this->returen_status(0, $this->form_validation->error_string());
			}
		}else{
			
			$host = $_SERVER['HTTP_HOST'];
			//查询商家的logo
			//if(preg_match('/^[^test|bn][a-zA-Z]+\.[hipigo|att]+\.[cn|com]+$/',$host)){
				$this->load->model('user_model');
				$logo_tmp = $this->user_model->get_logo_by_host($host);
				if($logo_tmp['logo']){
					$this->smarty->assign('logo',$logo_tmp['logo']);
				}
			//}
			$this->smarty->view('login');
		}
		
	}
	
	
	protected function check_login(){
		
	}
	
	
	/**
	 * 检测验证码是否正确
	 * @param string $str
	 * @return boolean
	 */
	public function check_captcha($str){
		
		$this->load->library('session');
		$scap = $this->session->userdata('captcha');
		if(!empty($scap) && $str == $scap){
			return TRUE;
		}
		return FALSE;
		
	}
	
	
	public function get_captcha($rand = 0){
		
		$img = parent::get_captcha();
		$url = 'http://'.$_SERVER['HTTP_HOST'].'/temp/'.$img.'.jpg';;
		if(!empty($rand)){
			die($url);
		}else{
			return $url;
		}
	}
	
	 public function logout(){
	    $this->load->driver('cache');
		$return = array();
		if($this->cache->memcached->is_supported() === TRUE){
			$this->cache->memcached->delete($this->session_id);
		}
		header('location:'.$this->url.'user/login');
	}


//    /**
//     * zxx
//     * 发送手机短信
//     *  $phone   手机号码
//     */
//    function send_code(){
//        $phone = $this->input->post('phone');
//
//        $mobile_code = $this->random(6,1);
//        $target = "http://106.ihuyi.cn/webservice/sms.php?method=Submit";
//        $post_data = "account=cf_xrenwu&password=xrenwu123&mobile=".$phone."&content=".rawurlencode("您的验证码是：".$mobile_code."。请不要把验证码泄露给其他人。");
//        //密码可以使用明文密码或使用32位MD5加密
//        $gets =  $this->xml_to_array($this->Post($post_data, $target));
//
//        var_dump($gets);
//    }
	
}



/* End of file user.php */