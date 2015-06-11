<?php
/**
 * 
 * @copyright(c) 2013-11-19
 * @author msi
 * @version Id:index.php
 */
 
 error_reporting(E_ALL);
ini_set('session.cookie_domain','.hipigo.cn');
 
$GLOBALS['time']['start'] = microtime(true);
//定义当前项目的目录名称
$application_folder = 'weixin';

//引入公用的入口文件
require_once '../../common/index.php';

//定义当前项目的路径
define('APPPATH',ROOT_PATH.$application_folder.'/');
//定义当前静态文件路径 Jamai
define('MEDIAPATH', dirname(__FILE__) . '/');

//加载框架

require_once BASEPATH.'core/CodeIgniter.php';
// file_put_contents('/test.md', var_export($_SERVER, true), FILE_APPEND);
/*
$GLOBALS['time']['end'] = microtime(true) - $GLOBALS['time']['start'];
file_put_contents('test.md', var_export($GLOBALS['time'], true), FILE_APPEND);
*/ 
/* End of file index.php */