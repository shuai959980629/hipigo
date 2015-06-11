<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Smarty Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Smarty
 * @author		Kepler Gelotte
 * @link		http://www.coolphptools.com/codeigniter-smarty
 */
require_once( BASEPATH.'third_party/smarty/libs/Smarty.class.php' );

class CI_Smarty extends Smarty {

	function __construct()
	{
		parent::__construct();

        if(strpos($_SERVER['REQUEST_URI'], 'biz') !== false ) { //后台
            if($_GET['tpl'] != 'admin') { //默认 default
                $this->compile_dir = APPPATH . "views/templates_c";
                $this->template_dir = APPPATH . "views/templates";
            }
            else {
                $this->compile_dir = APPPATH . "views".DOC_PATH."/templates_c";
                $this->template_dir = APPPATH . "views".DOC_PATH."/templates";
            }
        }
        else if(strpos($_SERVER['REQUEST_URI'], 'wapi') !== false) { //前端
          
            if(TPL_DEFAULT === 'default') {
                $this->compile_dir  = APPPATH . "views/templates_c";
                $this->template_dir = APPPATH . "views/templates";
            }
            else {
                $this->compile_dir  = APPPATH . "views/" . TPL_DEFAULT . "_c";
                $this->template_dir = APPPATH . "views/" . TPL_DEFAULT;
            }
        }else{
                $this->compile_dir  = APPPATH . "views/templates_c";
                $this->template_dir = APPPATH . "views/templates";
		}


		$this->left_delimiter = '<!--{';
		$this->right_delimiter = '}-->';
		$this->assign( 'APPPATH', APPPATH );
		$this->assign( 'BASEPATH', BASEPATH );
    //注册rewirte方法
    $this->smarty->registerPlugin('function', "rewrite", "rewrite" );

		log_message('debug', "Smarty Class Initialized");

	}


	/**
	 *  Parse a template using the Smarty engine
	 *
	 * This is a convenience method that combines assign() and
	 * display() into one step. 
	 *
	 * Values to assign are passed in an associative array of
	 * name => value pairs.
	 *
	 * If the output is to be returned as a string to the caller
	 * instead of being output, pass true as the third parameter.
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @param	bool
	 * @return	string
	 */
	function view($template, $data = array(), $return = FALSE)
	{
		$template .= '.html';
		foreach ($data as $key => $val)
		{
			$this->assign($key, $val);
		}
		
		if ($return == FALSE)
		{
			$CI =& get_instance();
			if (method_exists( $CI->output, 'set_output' ))
			{
				$CI->output->set_output( $this->fetch($template) );
			}
			else
			{
				$CI->output->final_output = $this->fetch($template);
			}
			return;
		}
		else
		{
			return $this->fetch($template);
		}
	}
}
// END Smarty Class
