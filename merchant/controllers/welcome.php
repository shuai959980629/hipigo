<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends User_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$result = $this->test();
		//debug($result);
		$this->load->view('welcome_message');
	}
	
	
	public function smartyss(){
		$this->smarty->view('welcome_message.html');
	}
	
	
	public function cache_meun(){
		
	/* 	$menus = '[{"id_profile_right":"291","id_profile":"1","id_right":"1","name":"\u9996\u9875","id_root":"1","id_parent":"0","childs":"2","depths":"1","orders":"1","type":"2","menu_url":"","children":[{"id_profile_right":"292","id_profile":"1","id_right":"23","name":"\u7edf\u8ba1","id_root":"1","id_parent":"1","childs":"0","depths":"2","orders":"2","type":"2","menu_url":"index\/index"}]},{"id_profile_right":"293","id_profile":"1","id_right":"2","name":"\u6d3b\u52a8","id_root":"2","id_parent":"0","childs":"18","depths":"1","orders":"100","type":"2","menu_url":"","children":[{"id_profile_right":"294","id_profile":"1","id_right":"3","name":"\u6d3b\u52a8\u5217\u8868","id_root":"2","id_parent":"2","childs":"0","depths":"2","orders":"110","type":"2","menu_url":"activity\/actlist"},{"id_profile_right":"303","id_profile":"1","id_right":"4","name":"\u521b\u5efa\u6d3b\u52a8","id_root":"2","id_parent":"2","childs":"0","depths":"2","orders":"120","type":"2","menu_url":"activity\/add"}]},{"id_profile_right":"310","id_profile":"1","id_right":"5","name":"\u4f1a\u5458","id_root":"5","id_parent":"0","childs":"5","depths":"1","orders":"200","type":"2","menu_url":"","children":[{"id_profile_right":"312","id_profile":"1","id_right":"6","name":"\u4f1a\u5458\u5217\u8868","id_root":"5","id_parent":"5","childs":"0","depths":"2","orders":"210","type":"2","menu_url":"member\/memberlist"},{"id_profile_right":"313","id_profile":"1","id_right":"7","name":"\u6dfb\u52a0\u4f1a\u5458","id_root":"5","id_parent":"5","childs":"0","depths":"2","orders":"220","type":"2","menu_url":"member\/add"}]},{"id_profile_right":"317","id_profile":"1","id_right":"8","name":"\u6807\u7b7e","id_root":"8","id_parent":"0","childs":"2","depths":"1","orders":"300","type":"2","menu_url":"","children":[{"id_profile_right":"318","id_profile":"1","id_right":"9","name":"\u6807\u7b7e\u5217\u8868","id_root":"8","id_parent":"8","childs":"0","depths":"2","orders":"310","type":"2","menu_url":"tags\/index"},{"id_profile_right":"319","id_profile":"1","id_right":"10","name":"\u6dfb\u52a0\u6807\u7b7e","id_root":"8","id_parent":"8","childs":"0","depths":"2","orders":"320","type":"2","menu_url":"tags\/edit"}]},{"id_profile_right":"320","id_profile":"1","id_right":"11","name":"\u7cfb\u7edf","id_root":"11","id_parent":"0","childs":"4","depths":"1","orders":"400","type":"2","menu_url":"","children":[{"id_profile_right":"321","id_profile":"1","id_right":"12","name":"\u7ba1\u7406\u5458\u8bbe\u7f6e","id_root":"11","id_parent":"11","childs":"0","depths":"2","orders":"410","type":"2","menu_url":"admin\/index"},{"id_profile_right":"322","id_profile":"1","id_right":"13","name":"\u90ae\u4ef6\u8bbe\u7f6e","id_root":"11","id_parent":"11","childs":"0","depths":"2","orders":"420","type":"2","menu_url":null},{"id_profile_right":"323","id_profile":"1","id_right":"14","name":"APK\u7ba1\u7406","id_root":"11","id_parent":"11","childs":"0","depths":"2","orders":"430","type":"2","menu_url":null},{"id_profile_right":"324","id_profile":"1","id_right":"15","name":"\u7ba1\u7406\u5458\u6743\u9650\u8bbe\u7f6e","id_root":"11","id_parent":"11","childs":"2","depths":"2","orders":"440","type":"2","menu_url":null,"children":[{"id_profile_right":"325","id_profile":"1","id_right":"16","name":"\u89d2\u8272\u7ba1\u7406","id_root":"11","id_parent":"15","childs":"0","depths":"3","orders":"441","type":"2","menu_url":"admin\/profile"},{"id_profile_right":"326","id_profile":"1","id_right":"17","name":"\u6743\u9650\u7ba1\u7406","id_root":"11","id_parent":"15","childs":"0","depths":"3","orders":"442","type":"2","menu_url":"admin\/rightlist"}]}]},{"id_profile_right":"327","id_profile":"1","id_right":"39","name":"KTV\u6d3b\u52a8","id_root":"39","id_parent":"0","childs":"4","depths":"1","orders":"500","type":"2","menu_url":"","children":[{"id_profile_right":"328","id_profile":"1","id_right":"40","name":"\u6d3b\u52a8\u89c6\u9891","id_root":"39","id_parent":"39","childs":"0","depths":"2","orders":"510","type":"2","menu_url":"job\/video"},{"id_profile_right":"329","id_profile":"1","id_right":"41","name":"\u9884\u7ea6\u67e5\u8be2","id_root":"39","id_parent":"39","childs":"0","depths":"2","orders":"520","type":"2","menu_url":"activity\/subscribe"},{"id_profile_right":"330","id_profile":"1","id_right":"46","name":"\u5173\u8054\u6d3b\u52a8\u8bbe\u7f6e","id_root":"39","id_parent":"39","childs":"0","depths":"2","orders":"530","type":"2","menu_url":"activity\/connect\/aID\/1320"},{"id_profile_right":"331","id_profile":"1","id_right":"47","name":"\u6392\u884c\u699c\u8bbe\u7f6e","id_root":"39","id_parent":"39","childs":"0","depths":"2","orders":"540","type":"2","menu_url":"job\/ranking\/aID\/1342"}]}]';
		$menus = json_decode($menus,TRUE);
		$this->load->driver('cache');
		//$this->cache->file->save('menus',$menus,0);exit;
		$cache_menus = $this->cache->file->get('menus');
		debug($cache_menus); */

		
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */