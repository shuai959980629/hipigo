<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
@ ����PC���û����� ǿ����ת�� www.hipigo.cn
@ ua �ͻ��˱�ʶ | Loaction ��ת��ַ
@ sc 2014.09.05
*/


class url{

	private $hipigo = 'http://www.hipigo.cn';

	function filter() { 

			$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
			$urls = $_SERVER['REQUEST_URI'];
			//file_put_contents(DOCUMENT_ROOT.'/useragent.txt',var_export(time().": ".$urls."ua :".$ua."\n",TRUE),FILE_APPEND);

			$uachar = "/(micromessenger|nokia|sony|ericsson|mot|samsung|sgh|lg|philips|panasonic|alcatel|lenovo|cldc|midp|coolpad|k-touch|tcl|oppo|doov|amoi|bbk|cect|amoi|zte|huawei|iphone|ipad|android|smartphone)/i";

			if($ua == '' || (!preg_match($uachar, $ua) && strpos($urls,"wx_interaction")==false && strpos($urls,"tpicture")==false && strpos($urls,"class_list")==false && strpos($urls,"content")==false && strpos($urls,"pay_notify")==false  && strpos($urls,"subject_activity")==false && strpos($urls,"subject_share")==false && strpos($urls,"authorize")==false)) {

					$Loaction = $this->hipigo;			

					if (!empty($Loaction)){

						header("Location: $Loaction\n");
						exit;

					}

			}		

     } 
	
}


