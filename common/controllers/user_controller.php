<?php
/**
 * 
 * @copyright(c) 2013-11-20
 * @author msi
 * @version Id:user_controller.php
 */

class User_Controller extends CI_Controller{
	
	
	public function test(){
		
		//debug('这是一个测试类');
		$this->load->model('user_model','user');
		return $this->user->test();
		
	}
	
	
	
}



/* End of file user_controller.php */