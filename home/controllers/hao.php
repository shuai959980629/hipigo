<?php

class Hao extends CI_Controller
{
	public function __construct(){
		parent::__construct();
	}
	//
	public function index(){
	 $this->smarty->view('index2');
	}
}