<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Static_Resource
{
  
  /**
   * 
   * 
   **/
  protected static $_GLOBAL = array();
  protected static $_MODULE = array();
  const CSS = 'css';
  const JS = 'js';

  const GLOBALS = 'global';
  const MODULE = 'module';
  
  /**
   * 初始化添加需要的静态文件
   * @param $options = array()  多个参数请包装为二级数组
   *        $file   文件名
   *        $type   文件类型
   *        $domain 文件所属范围  global全局 module模块
   * @author Jamai
   * @version 2.1
   **/
  public function __construct()
  {
  }
  
  /**
   * $domain 参数是指这个资源是属于全局（每个页面都要用到）,
   * 局部（只有1个或几个页面会用）,
   * 模块（必须被压缩或者复制到压缩文件夹以便访问）
   *
   * 全局资源被压缩和缓存有利于加载速度，
   * 但是如果局部资源被压缩成一个文件，
   * 就意味着每个页面的静态资源文件都是不一样的，这样反而会更慢。
   */
  public static function add($file, $type, $domain = 'module')
  {
    if( ! in_array($type, array(self::CSS, self::JS)))
      return false;
    
    if($domain == self::GLOBALS && ! isset(self::$_GLOBAL[$type][md5($file)]))
      self::$_GLOBAL[$type][md5($file)] = $file;
    elseif($domain == self::MODULE && !isset(self::$_MODULE[$type][md5($file)]))
      self::$_MODULE[$type][md5($file)] = $file;
  }

  public function getAll($type)
  {
    return array(
      'module' => self::$_MODULE[$type],
      'global' => self::$_GLOBAL[$type]
    );
  }
  
  public function output($type)
  {
    if( ! in_array($type, array(self::CSS, self::JS)))
      return false;
    
    $files = self::getAll($type);
    
    $resourceDir = MEDIAPATH . $type . '/' . TPL_DEFAULT . '/';
    
    $CI = &get_instance();
    $metia = $CI->config->item('metia_c');
    $mediaModule = $mediaGlobal = array();
    if($files['global']) {
      foreach($files['global'] as $file)
        if(substr($file, 0, 4) == 'http' || substr($file, 0, 2) == '//' )
          $output[] = ($type == self::CSS) ?
                '<link type="text/css" rel="stylesheet" href="' . $file . '" />' :
                '<script type="text/javascript" src="' . $file . '"></script>';
        else
          if(file_exists($resourceDir . $file))
            $mediaGlobal[] = $file;
    }
    
    //所以在这里就处理content
    if($files['module'])
      foreach($files['module'] as $file)
        if(file_exists($resourceDir . $file))
          $mediaModule[] = $file;
    $cacheTime = 0;//time();
    
    if($mediaGlobal) {
      foreach ($mediaGlobal as $file)
        if(file_exists($resourceDir . $file)) {
          $output[] = ($type == self::CSS) ?
            '<link rel="stylesheet" type="text/css" href="/wapi/' . $type . 
                  '/'. TPL_DEFAULT . '/' . $file . '?' . $cacheTime . '" />' :
            '<script type="text/javascript" language="javascript" src="/wapi/' . $type . 
                  '/'. TPL_DEFAULT . '/' . $file . '?' . $cacheTime . '"></script>';
        }
    }
    
    if($metia[$type] == 1) {
      if( ! file_exists(MEDIAPATH . $cache_dir) OR time() - filemtime(MEDIAPATH . $cache_dir) > 0) {
        $content = '';
        if($mediaModule) { //处理 module
          $cacheFile = $type . '/' . TPL_DEFAULT . "/" . basename($file, $type) . 'min.' . $type;
          foreach ($mediaModule as $file) {
            if($fileContent = @file_get_contents($resourceDir . $file))
              $content .= $fileContent;
          }
          if($type == self::CSS) {
            require_once('cssmin-v3.0.1-minified.php');
            $content = CssMin::minify($content);
          }
          else {
            require_once('JSMin.php');
            $content = JSMin::minify($content);
          }
          
          file_put_contents(MEDIAPATH . $cacheFile, $content);
          
          $output[] = $type == self::CSS ?
          '<link rel="stylesheet" type="text/css" href="/wapi/' . $cacheFile . '?' . $cacheTime . '" />' :
          '<script type="text/javascript" language="javascript" src="/wapi/' . $cacheFile . '?' . $cacheTime . '"></script>';
        }
      }
    }
    else {
      if($files['module'])
        foreach ($files['module'] as $file) {
          $output[] =  ($type == self::CSS) ?
            '<link rel="stylesheet" type="text/css" href="/wapi/' . $type . '/' . 
                  TPL_DEFAULT . '/' . $file . '?' . $cacheTime . '" />' :
            '<script type="text/javascript" language="javascript" src="/wapi/' . $type . '/' . 
                  TPL_DEFAULT . '/' . $file . '?' . $cacheTime . '"></script>';
        }
    }
    if($output)
      return implode("\n", $output);
    else 
      return null;
  }
}

/* End of file static_resrouce.php */
/* Location: ./weixin/controllers/static_resrouce.php */