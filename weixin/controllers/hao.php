<?php
/**
 * 
 * @copyright(c) 2014-04-03
 * @author vikie
 * @version Id:chance.php
 */

class Hao extends WX_Controller
{
	
  public function __construct(){
  	    session_start();
		parent::__construct();
	}
  
  public function index(){
  	//session_start();
	$_SESSION['name']=1;
	
  }
  
  public function ni(){
  	echo $_SESSION['name'];
  }
}	