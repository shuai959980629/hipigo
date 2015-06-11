<?php if (!defined('BASEPATH'))exit('No direct script access allowed');
/**
 *@支付配置文件
 *@author zhoushuai
 * define('app_token', 'bTdfe8dc'); //app_token
 * define('biz_token','V8dfe8dc');//商家对应的key
 * define('app_id', 1); //应用id
 * define('biz_id',1);
 * define('pay_geteway', 'http://pay.hipigo.cn/gateway'); //支付网关
 */
//本地开发环境
$pay['development']['app_token'] = 'bTdfe8dc';//app_token
$pay['development']['biz_token'] = 'V8dfe8dc';//商家对应的key
//$pay['development']['app_id'] = 1;//应用id
$pay['development']['app_id'] = array(
  'community' => 1,
  'resource_publish' => 2,
  'resource_buy' => 3,
);
$pay['development']['biz_id'] = 1;
$pay['development']['pay_geteway'] = 'http://pay.dev.hipigo.cn/gateway';
//$pay['development']['pay_geteway'] = 'http://localhost:8001/gateway';

//testing 测试环境
$pay['testing']['app_token'] = 'bTdfe8dc';//app_token
$pay['testing']['biz_token'] = 'V8dfe8dc';//商家对应的key
//$pay['testing']['app_id'] = 1;//应用id
$pay['testing']['app_id'] = array(
  'community' => 1,
  'resource_publish' => 2,
  'resource_buy' => 3,
);

$pay['testing']['biz_id'] = 1;
$pay['testing']['pay_geteway'] = 'http://pay.test.hipigo.cn/gateway';

//production  公网环境
$pay['production']['app_token'] = 'bTdfe8dc';//app_token
$pay['production']['biz_token'] = 'V8dfe8dc';//商家对应的key
//$pay['production']['app_id'] = 1;//应用id
$pay['production']['app_id'] = array(
  'community' => 1,
  'resource_publish' => 2,
  'resource_buy' => 3,
);
$pay['production']['biz_id'] = 1;
$pay['production']['pay_geteway'] = 'http://pay.hipigo.cn/gateway';//支付网关



?>