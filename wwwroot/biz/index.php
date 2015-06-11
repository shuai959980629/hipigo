<?php
/**
 * 
 * @copyright(c) 2013-11-19
 * @author msi
 * @version Id:index.php
 */
$starttime = microtime(true);
//定义当前项目的目录名称
$application_folder = 'merchant';
define('APPNAME', 'merchant');

//引入公用的入口文件
require_once '../../common/index.php';
define('BASE_PATH', realpath(dirname(__FILE__)));
//定义当前项目的路径
define('APPPATH',ROOT_PATH.$application_folder.'/');

//加载框架
require_once BASEPATH.'core/CodeIgniter.php';

/* End of file index.php */