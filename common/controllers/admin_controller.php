<?php
/**
 * 
 * @copyright(c) 2013-11-20
 * @author msi
 * @version Id:admin_controller.php
 */

class Admin_Controller extends CI_Controller
{
	protected $users;//用户登陆信息
	protected $errors;//错误信息
	protected $params;//初始化页面参数
	protected $page_data;//初始化页面数据
	protected $current;
	protected $rewrite_prefix = '';//入口文件
	protected $session_id;//session_id
    protected $shop_name;
    protected $url = BIZ_PATH; //URL路径，模板中使用
    protected $doc_tpl = DOC_PATH; //存储的模板js,css,img信息
    protected $tpl = array(); //模板信息
    protected $cm_ = array(); //权限信息


    //分页参数
    static $offset = 0;

    private $image;                //图片句柄
    private $waterImg;             //水印图片句柄
    private $x;                    //水印X坐标
    private $y;                    //水印y坐标

	public function __construct(){
		parent::__construct();
		$this->lang->load('common');
		$this->init();
	}

    /**
	 * 初始化后端服务
	 */
	protected function init(){
//        $this->load->driver('cache');
//        $cache1 = $this->cache->memcached->get('userId');
//        var_dump($cache1);
//        die;
//        $this->cache->memcached->save('diudiu','diudiu',7200);
//        $cache = $this->cache->memcached->get('diudiu');
//        var_dump($cache);
//        die;
        $this->ini_user();
		$this->get_shop_list();
		//初始化页面菜单
		$this->init_menu();
		$this->get_nav();
		$this->form_hash();
		//加载页面公用信息
		$this->load_page();
	}


    //修改企业信息和门店信息后刷新页面
    public function refresh_page(){
    	if($this->users['id_user'] && $this->users['id_shop']){
    		$this->load->model('business_model');
    		$return = $this->business_model->get_biz_shop_info_by_uid($this->users['id_user'],$this->users['id_shop']);
    		if($return){
    			if($return['visit_mode'] == 'share'){
					$return['biz_mod'] = 0;//共享模式
					$return['id_shop'] = 0;
				}else{
					$return['biz_mod'] = 1;//独占模式
				}
    			$this->cache_data($this->session_id, $return,true);
    		}
    	}
    }

    protected function load_page(){
        //debug($this->page_data);exit;
		$this->smarty->assign('data',$this->page_data);
		$this->smarty->assign('caction',$this->uri->rsegments[1]);
		$this->smarty->assign('url_prefix',$this->url);
        $this->smarty->assign('doc_tpl',$this->doc_tpl);
//        $tpl = array();
        if($this->users['id_business'] == 3 && ($_POST['tpl'] || $_GET['tpl'])){
            $this->tpl[0] = '?tpl=admin';
            $this->tpl[1] = '&tpl=admin';
        }else{
            $this->tpl[0] = '';
            $this->tpl[1] = '';
        }
        $this->cm_ = $_GET['cm'];
        $this->smarty->assign('cm',$this->cm_);
        $this->smarty->assign('tpl',$this->tpl);
		$this->smarty->assign('site_url','http://'.$_SERVER['HTTP_HOST']);
		
	}
	
	
	/**
	 * 返回操作结果
	 * @param int $status 操作状态0失败，1成功
	 * @param string $msg 提示信息
	 * @param array $data 其他信息
	 */
	public function returen_status($status,$msg,$data = array()){
        if(empty($data['is_refresh']) && $data['is_refresh'] !== 0 && $data['is_refresh'] !== 2){
            $data['is_refresh'] = 1;//为1是可刷新  为0是不刷新页面
        }
		$resp = array(
            'status' => $status,
            'msg' => $msg,
            'data' => $data
		);
		if($this->input->is_ajax_request() && $data['page'] != 'resource'){
			die(json_encode($resp));
		}else{
			$this->smarty->assign('resp',$resp);
			$this->smarty->display('success.html');
		}
		exit(0);
	}
	
	
	/**
	 * 获取面包屑导航
	 */
	public function get_nav(){

		//后面可以把这个数据缓存起来
		$menu = $this->get_menu($this->users['id_user'],0);
		foreach ($menu as $k => $v){
			$this->current[$v['id_menu']] = $v['name'];
		}
		//$this->current = $this->get_current_map($menus);
		//debug($this->current);
	    $cm = intval($_GET['cm']);
   	    if($cm){
			//递归查出父类id
			$p_tmp = $this->get_parent_id($cm);
			$str = '';
			for ($i = (count($p_tmp)-1);$i >= 0;$i--){
				if (isset($this->current[$p_tmp[$i]])) {
					$str .= '<li>'.$this->current[$p_tmp[$i]].'<i class="icon-angle-right"></i></li>';
				}
			}
			//存入session
			$this->cache_data($this->session_id.'_ct', array('cm'=>$cm,'carr'=>$p_tmp,'nav'=>$str));
		}else{
			//session中取
           if(strpos($_SERVER['REQUEST_URI'],'/biz/') == true){
            	$cm_tmp = $this->get_session_data($this->session_id.'_ct');
    			$cm = $cm_tmp['cm'];
    			$p_tmp = $cm_tmp['carr'];
    			$str = $cm_tmp['nav'];
           }
		}	
		$current = array();
		$m = $a = '';
		if(!empty($this->uri->rsegments[1])){
			$m = $this->uri->rsegments[1];
		}
		if(!empty($this->uri->rsegments[2])){
			$a = $this->uri->rsegments[2];
		}

		$current['last'] = isset($this->current[$p_tmp[0]]) ? $this->current[$p_tmp[0]] : '';
		$current['carr'] = $p_tmp;
        $current['business_name'] = $this->users['biz_name'];

		$this->smarty->assign('nav',$str);
		$this->smarty->assign('current',$current);
	}
	
	
	private function get_parent_id($id_menu){
		$this->load->model('right_model','right');
		$where = array('id_menu'=>$id_menu,'id_business'=>$this->users['id_business']);
		$c_tmp = $this->right->get_menu_by_id($where);
		$return = array();
		if($c_tmp){
			$return[] = $c_tmp['id_menu'];
			if($c_tmp['id_parent'] != 0){
				$return2 = $this->get_parent_id($c_tmp['id_parent']);
				$return = array_merge($return,$return2);
			}
		}
		return $return;
	}
	
	
	/**
	 * 初始化用户菜单
	 */
	protected function init_menu(){
        if($this->users['id_user'] > 0){
			$this->page_data['menu'] = $this->get_menu($this->users['id_user']);
            if(in_array($this->users['id_business'],$this->config->item('BUSINESS_ID')) && $this->users['is_shop'] == 1){
                $this->page_data['menu'] = $this->any_menu($this->page_data['menu']);
            }
		}
	}

	
	/**
	 * 获取用户菜单、权限
	 * @param $userID 用户ID
	 * @param $type 类型(0：权限，2：菜单)
	 */
	public function get_menu($uid,$type = 2)
	{
		$menu = array();
		if(empty($uid)){
			return $menu;			
		}
		$this->load->model('right_model','right');
	
		$where = 'mu.id_user = '.$uid . ' and mu.id_business = ' . $this->users['id_business'];
		if( $type > 0 ){
			//$where .=' AND r.type = '.$type;
		}
		$arr = $this->right->get_user_menu($where);
		if($type == 2){
			$menu = $this->list_to_tree($arr, 'id_menu', 'id_parent');
		}else{
			$menu = $arr;
		}
		return $menu;
	}

    /**
     * zxx
     * @param $menu  所有menu
     * @return array  只显示的企业设置和会员管理
     */
    function any_menu($menu){
        $menu_info = array();
        foreach($menu as $m){
            if($m['name'] == '企业设置' || $m['name'] == '会员管理'){array_push($menu_info,$m);}
        }
        return $menu_info;
    }
	
	
	/**
	 * 把返回的数据转换成Tree
	 * @author rjy
	 * @param array $list 要转换的数据
	 * @param array $pk 数据的id
	 * @param string $pid parent标记字段
	 * @return array
	 */
	protected  function list_to_tree($list, $pk='id_right',$pid = 'id_parent',$child = 'children',$root=0) {
		// 创建Tree
		$tree = array();
		if(is_array($list)) {
			// 创建基于主键的数组引用
			$refer = array();
			foreach ($list as $key => $data) {
				$refer[$data["$pk"]] =& $list["$key"];
			}
			foreach ($list as $key => $data) {
				// 判断是否存在parent
				$parentId = $data["$pid"];
				if ($root == $parentId) {
					$tree[] =& $list["$key"];
				}else{
					if (isset($refer[$parentId])) {
						$parent =& $refer["$parentId"];
						$parent["$child"][] =& $list["$key"];
					}
				}
			}
		}
		return $tree;
	}
	
	
	/**
	 * 获得权限列表
	 * @param array $arr
	 * @return array 具有的权限列表
	 */
	protected function get_accesslist($arr){
		
		$access = array();
		if(is_array($arr)){
			foreach ($arr as $key => $acc){
				if(!empty($acc['menu_url'])){
					$access[] = $acc['menu_url'];
				}
			}
		}
		return $access;
		
	}
	
	
	/**
	 * 拼装
	 * @param string $arr
	 * @return array
	 */
	private function get_current_map($arr,$count=0){
	
		//debug($arr);
		
		$i = 0;
		$return = $return2 = array();
		static $count;
		$str = '';
		if(is_array($arr)){
			foreach ($arr as $k => $v){
				if($v['id_parent'] == 0){
					$str = $v['name'];
				}
				/* if(!empty($v['id_menu'])){
					$return[$v['id_menu']] = $v['name'];
					$str .= $v['name'].'>>';
				}
				if(is_array($v['children'])){
					$return2 = $this->get_current_map($v['children'],$count);
					$return = array_merge($return,$return2);
				} */
			}
			$count++;
		}
		debug($str);
		return $return;
	
	}
	
	
	/**
	 * hipigo用户账号加密算法
	 * @param string $pwd 原始密码
	 * @param string $hash
	 * @return string
	 */
	protected function md5pwd($pwd, $hash){
		
		$newpwd =  md5(substr(md5(md5($pwd)), 5, 10).$hash);
		return $newpwd;
		
	}
	
	
	/**
	 * 商家登陆
	 * @param string $username
	 * @param string $password
	 */
	protected function merchent_login($username,$password){
		$this->load->model('user_model');
		$user_tmp = $this->user_model->get_userinfo_by_username_or_email_or_phone($username);
		$user = array();
		foreach ($user_tmp as $k => $v){
			if($v['cell_phone'] == $username){
				$user = $v;
			}
		}
		if(empty($user)){
			foreach ($user_tmp as $k => $v){
				if($v['mail'] == $username){
					$user = $v;
				}
			}
		}
		if(empty($user)){
			foreach ($user_tmp as $k => $v){
				if($v['name'] == $username){
					$user = $v;
				}
			}
		}

		if(!empty($user) && !empty($this->session_id)){
			
			/* if($user['status'] != 2){
				//账号未激活或无效
				$this->errors = '';
				return FALSE;
			} */
			
			$m_hash = $user['pass_hash'];
			$p_pass = $this->md5pwd($password, $m_hash);

            //zxx start
            $this->load->model('business_model','business');
            $url = $_SERVER ['HTTP_HOST'];
            $business_info = $this->business->get_bs_bid($url);
            $businessID = $business_info['id_business'];
            if(!$businessID){
                //商户不存在
                $this->errors = $this->lang->line('u_not_biz_account');
                return FALSE;
            }
            //zxx end

			if($user['pass'] == $p_pass){
				//账号和密码正确，以下查出商家信息
                $where = 'a.id_user = ' . $user['id_user'];
                if($businessID){$where .= ' and a.id_business = ' . $businessID;}//zxx
				$merchent = $this->user_model->get_merchentinfo_by_uid($where);

				if(!empty($merchent)){
					if($merchent['visit_mode'] == 'share'){
						$merchent['biz_mod'] = 0;//共享模式
						$merchent['id_shop'] = 0;
					}else{
						$merchent['biz_mod'] = 1;//独占模式
                        $where_s['id_business'] = $merchent['id_business'];
						if(empty($merchent['id_shop'])){
							$shop_arr = $this->user_model->get_shop_list($where_s);
							if(empty($shop_arr[0]['id_shop'])){
								$this->returen_status(0, $this->lang->line('have_no_oter_shop'));
							}
							$merchent['id_shop'] = $shop_arr[0]['id_shop'];
                            $id_shop_ = array();
                            foreach($shop_arr as $sa){
                                array_push($id_shop_,$sa['id_shop']);
                            }
                            $merchent['id_shop_array'] = $id_shop_;
                            $merchent['is_shop'] = 0;//用来判读是否是门店账号 0：总店
							$merchent['shop_mgt'] = TRUE;
						}else{
                            $where_s['id_shop'] = $merchent['id_shop'];
                            $shop_arr = $this->user_model->get_shop_list($where_s);
                            if(empty($shop_arr[0]['id_shop'])){
                                $this->returen_status(0, $this->lang->line('have_no_oter_shop'));
                            }
                            $merchent['id_shop'] = $shop_arr[0]['id_shop'];
                            $merchent['is_shop'] = 1;//用来判读是否是门店账号 0：总店
                        }
					}
					$shop_detal = $this->user_model->get_shop_by_shopid($merchent['id_shop']);
					$merchent = array_merge($user,$merchent,$shop_detal);
					$this->cache_data($this->session_id, $merchent);
					return $merchent;
				}else{
					//商户不存在
					$this->errors = $this->lang->line('u_not_biz_account');
					return FALSE;
				}
			}else{
				$this->errors = $this->lang->line('u_account_passwd_error');
				return FALSE;
			}
		}else{
			//账号不存在
			$this->errors = $this->lang->line('u_account_not_exists');
			return FALSE;
		}
		
		return FALSE;
		
	}

	
	/**
	 * 缓存临时数据(session)/后期考虑用内存优化
	 * @param string $key
	 * @param array $data
	 */
	protected function cache_data($key,$data,$replace = false){
		
		$this->load->driver('cache');
		if($this->cache->memcached->is_supported() === TRUE){
			//debug($data);
			if(!$replace){
				$this->cache->memcached->save($key,$data,7200);
			}else{
				$this->cache->memcached->replace($key,$data,7200);
			}
			
			return TRUE;
		}
		return FALSE;
	}
	
	
	protected function get_session_data($key){
		
		$this->load->driver('cache');
		$return = array();
		if($this->cache->memcached->is_supported() === TRUE){
			$cache = $this->cache->memcached->get($key);
			if(!empty($cache[0])){
				//$return = json_decode($cache[0],TRUE);
				return $cache[0];
			}
		}
		return $return;
	}
	
	
	/**
	 * 生成随机的数字
	 */
	protected function get_random($length=6){
	
		return rand(pow(10, $length-1), pow(10, $length)-1);
	
	}
	

    /**
     * 获取页码
     * @static
     * @param int $total 总数量
     * @param int $offset 当前页码
     * @param string $function_name 方法名
     * @param string $click_type 传来的$function_name 类型。 url :链接地址  method ： 方法名
     * @param int $pager 分页数量
     * @return string
     */
    function get_page($total, $offset = 0, $function_name='',$click_type='method', $pager=0)
    {
        //当前页码
        $offset = $offset ? $offset : self::$offset;
        //分页数量
        $pager = $pager ? $pager : ($this->config->item('page'));
        //链接文字显示
        $text_ary = array('<<', '>>');
        //页码左右显示数量
        $show_number = 2;

        //计算总页数开始
        if( $total < $pager )
            return '';
        else if( $total % $pager )
            $page_count = (int)($total / $pager) + 1;
        else
            $page_count = $total / $pager;
        //计算总页数结束

        //判断输入的分页码在有效分页范围内
        if ( $offset <= 0 )
            $offset = 1;
        else if ( $offset >= $page_count)
            $offset = $page_count;

        //计算上一页
        $page_str = '';
        if ( $offset <= 1 )
            $page_str .= '<li><a href="javascript:void(0);" class="shop" title="上一页">'.$text_ary[0].'</a></li>';
        else{
            if($click_type == 'url')
                $page_str .= '<li><a href="'.$function_name.'/'.($offset - 1).'" class="up" title="上一页">'.$text_ary[0].'</a></li>';
            else
                $page_str .= '<li><a onclick="'.$function_name.'('.($offset - 1).')" class="up" title="上一页">'.$text_ary[0].'</a></li>';
        }

        //计算页码数字
        if ( $offset <= ($show_number + 1) ) {
            for ($i = 1; $i <= ($page_count < ($show_number * 2 + 1) ? $page_count : ($show_number * 2 + 1)); $i++ )
            {
                if ( $i == $offset )
                    $page_str .= '<li class="active"><a href="javascript:void(0);"><b>'.$i.'</b></a></li>';
                else{
                    if($click_type == 'url')
                        $page_str .= '<li><a href="'.$function_name.'/'.$i.'">'.$i.'</a></li>';
                    else
                        $page_str .= '<li><a onclick="'.$function_name.'('.$i.')">'.$i.'</a></li>';
                }
            }
        } else if ( $offset >= ($page_count - $show_number) ) {
            for ($i = ($page_count - ($show_number * 2)); $i <= $page_count; $i++ )
            {
                if($i != 0){
                    if ( $i == $offset )
                        $page_str .= '<li class="active"><a href="javascript:void(0);"><b>'.$i.'</b></a></li>';
                    else{
                        if($click_type == 'url')
                            $page_str .= '<li><a href="'.$function_name.'/'.$i.'">'.$i.'</a></li>';
                        else
                            $page_str .= '<li><a onclick="'.$function_name.'('.$i.')">'.$i.'</a></li>';
                    }
                }
            }
        } else {
            for ($i = ($offset - $show_number); $i <= ($offset + $show_number); $i++ )
            {
                if ( $i == $offset )
                    $page_str .= '<li class="active"><a href="javascript:void(0);"><b>'.$i.'</b></a></li>';
                else{
                    if($click_type == 'url')
                        $page_str .= '<li><a href="'.$function_name.'/'.$i.'">'.$i.'</a></li>';
                    else
                        $page_str .= '<li><a onclick="'.$function_name.'('.$i.')">'.$i.'</a></li>';
                }
            }
        }

        //计算下一页
        if( $offset >= $page_count )
            $page_str .= '<li><a href="javascript:void(0);" class="shop" title="下一页">'.$text_ary[1].'</a></li>';
        else{
            if($click_type == 'url')
                $page_str .= '<li><a href="'.$function_name.'/'.($offset + 1).'" class="next" title="下一页">'.$text_ary[1].'</a></li>';
            else
                $page_str .= '<li><a onclick="'.$function_name.'('.($offset + 1).')" class="next" title="下一页">'.$text_ary[1].'</a></li>';
        }
        return $page_str;
    }

	
	private function form_hash(){
		
		$this->load->library('session');
		$hash = $this->session->userdata('formhash');
		if(empty($hash)){
			$hash = $this->get_random();
			$this->session->set_userdata('formhash', $hash);
		}
		//$this->smarty->assign('formhash',$hash);
	}
	

    /*
     * 获取物品列表
     * zxx
     * $id_commodity_class：当前物品分类id
     * $offset ：页码
     * $page ：条数
     * **/
    function get_commodity($id_commodity_class,$offset,$search_key='',$page=0){
        //分页数量
        $page = $page ? $page : ($this->config->item('page'));

        $this->load->model('commodity_model','commodity');
        $where = 'c.id_shop = '.$this->users['id_shop'].' and c.id_business = '.$this->users['id_business'];
        if($search_key == ''){
        }else{
            $where .= ' and c.name like \'%'.$search_key.'%\'';
        }

        if($id_commodity_class != 'all'){
            $where .= ' and c.id_class = \''.$id_commodity_class.'\'';
        }
        //获取物品列表
        $commodity_list = $this->commodity->get_commodity_introduction($where,$offset,$page,'c.state desc,c.weight desc',0);
        if(!$commodity_list){
            $offset = 1;
            $commodity_list = $this->commodity->get_commodity_introduction($where,$offset,$page,'c.state desc,c.weight desc',0);
        }
        //获取物品总数
        $commodity_count = $this->commodity->get_commodity_count($where);

        //分页代码
        $page_html = $this->get_page($commodity_count, $offset, 'commodity_list_page');

        $where_class = 'id_business = '.$this->users['id_business']. ' and type = \'cmd\'';
        //物品分类
        $commodity_class = $this->commodity->get_commodity_class($where_class);

        $data = array();
        $data['commodity_class'] = $commodity_class;
        $data['commodity_list'] = $commodity_list;
        $data['page_html'] = $page_html;
        return $data;
    }
    
    
    public function change_shop($shop_id){
    	$shop_id = intval($shop_id);
    	//检查是否有权限切换
    	if(!empty($this->users['shop_mgt']) && !empty($shop_id)){
    		$this->load->model('user_model');
    		$slist = $this->user_model->get_shop_list('id_business = '.$this->users['id_business']);
    		$shoplist = array();
    		if(!empty($slist)){
    			foreach ($slist as $k => $v){
    				$shoplist[] = $v['id_shop'];
    			}
    			
    			if(in_array($shop_id,$shoplist)){
    				$ouser = $this->users;
    				$shop_detal = $this->user_model->get_shop_by_shopid($shop_id);
    				foreach ($shop_detal as $k => $v){
    					$ouser[$k] = $v;
    				}
    				$ouser['id_shop'] = $shop_id;
    				$this->cache_data($this->session_id, $ouser);
    				//$this->returen_status(1, $this->lang->line('change_shop_success'),array('list_url'=>'/'));
    				header('location:'.$this->url);
    			}
    		}
    	}
    	$this->returen_status(0, $this->lang->line('u_canot_op'));
    }
    
    protected function get_shop_list(){
    	
    	$bid = $this->users['id_business'];
    	if(!empty($this->users['shop_mgt']) && !empty($bid)){
    		$this->load->model('user_model');
    		$slist = $this->user_model->get_shop_list('id_business = '.$bid);
    		$this->smarty->assign('shop_list',$slist);
    		return TRUE;
    	}
    	return FALSE;
    	
    }
    

    /*
     * 获取歌曲列表
     * zxx
     * $offset ：页码
     * $page ：条数
     * **/
    function get_song($id_class,$offset,$search_key='',$page=0){
        //分页数量
        $page = $page ? $page : ($this->config->item('page'));
//        $page = 2;
        $this->load->model('song_model','song');
        if($search_key == ''){
            $where = 's.id_shop = '.$this->users['id_shop'].' and s.id_business = '.$this->users['id_business'];
            $where_c = 'id_shop = '.$this->users['id_shop'].' and id_business = '.$this->users['id_business'];
        }else{
            $where = 's.id_shop = '.$this->users['id_shop'].' and s.id_business = '.$this->users['id_business'] . ' and (s.name like \'%'.$search_key.'%\' or s.singer like \'%'.$search_key.'%\')';
            $where_c = 'id_shop = '.$this->users['id_shop'].' and id_business = '.$this->users['id_business'] . ' and (name like \'%'.$search_key.'%\' or singer like \'%'.$search_key.'%\')';
        }
        if($id_class != 'all'){
            $where .= ' and s.id_class = \''.$id_class.'\'';
            $where_c .= ' and id_class = \''.$id_class.'\'';
        }
        //获取物品列表
        $song_list = $this->song->get_song_introduction($where,$offset,$page,'state desc,weight desc',0);
        if(!$song_list){
            $offset = 1;
            $song_list = $this->song->get_song_introduction($where,$offset,$page,'state desc,weight desc',0);
        }
        //获取歌曲总数
        $song_count = $this->song->get_song_count($where_c);
        //分页代码
        $page_html = $this->get_page($song_count, $offset, 'song_list_page','method');

        $this->load->model('commodity_model','commodity');
        $where_class = 'id_business = '.$this->users['id_business']. ' and type = \'song\'';
        //歌曲分类
        $song_class = $this->commodity->get_commodity_class($where_class);

        $data = array();
        $data['song_class'] = $song_class;
        $data['song_list'] = $song_list;
        $data['page_html'] = $page_html;
        return $data;
    }
    
    public function ini_user(){
    	$this->load->library('session');
        $this->session_id = $this->session->userdata('session_id');

    	$this->users = $this->get_session_data($this->session_id);
    	$this->page_data['user'] = $this->users;
    }

    /**
     * @param $html
     * @return mixed
     * 替换html标签
     */
    function replace_html($html){
        $return = str_replace('<', '&lt;', $html);
        $return = str_replace('>', '&gt;', $return);
        return $return;
    }

    /**
     * 建立文件夹目录
     * @param string $dirs 文件夹目录路径
     * @param string $mode 权限
     */
    public function makeDir($dirs = '', $mode = 0777)
    {
        $dirs = str_replace ('\\', '/', trim( $dirs ));
        if (! empty ( $dirs ) && ! is_dir ( $dirs )) {
            self::makeDir (dirname($dirs ));
            if(!mkdir ($dirs, $mode )){
                file_put_contents(DOCUMENT_ROOT.'/ac.txt',var_export('权限不足,建立'.$dirs.'目录失败',TRUE));
                return false;
//                exit('权限不足,建立'.$dirs.'目录失败');
            }
        }
        return true;
    }

    //获取远程文件大小
    function getFileSize($url){
        $url = parse_url($url);
        if($fp = @fsockopen($url['host'],empty($url['port'])?80:$url['port'],$error)){
            fputs($fp,"GET ".(empty($url['path'])?'/':$url['path'])." HTTP/1.1\r\n");
            fputs($fp,"Host:$url[host]\r\n\r\n");
            while(!feof($fp)){
                $tmp = fgets($fp);
                if(trim($tmp) == ''){
                    break;
                }else if(preg_match('/Content-Length:(.*)/si',$tmp,$arr)){
                    return sprintf("%.1f", (trim($arr[1])/1024));
                }
            }
            return null;
        }else{
            return null;
        }
    }

    //获取图片文件的本地路径+名字
    function file_url_name($path,$filename,$type=1){
        if($type == 0){
            $path = '/attachment/business/'.$path.'/';
        }else{
            $path = BASE_PATH. '/../attachment/business/'.$path.'/';
        }
        $cdir = str_split(strtolower($filename),1);
        $tmp = array_chunk($cdir, 4);
        if($tmp[0]){
            $dir = implode('/', $tmp[0]);
        }
        $path .= $dir.'/'.$filename;
        return $path;
    }

    //获取token
    public function get_access_token($id_business,$id_shop){
        $this->load->model('business_model','business');
        $wx_config = $this->business->get_biz_config(array('id_business'=>$id_business,'id_shop'=>$id_shop));
        if(!empty($wx_config['id_app']) && !empty($wx_config['app_secret'])){
            $curl = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$wx_config['id_app'].'&secret='.$wx_config['app_secret'];
            $token_tmp = request_curl($curl);
            $token = json_decode($token_tmp,1);
            if($token['access_token']){
                return $token['access_token'];
            }
        }
        return FALSE;
    }


    /*
     * zxx
     * 微信快速回复信息列表
     * */
    public function get_quick_reply($offset,$search_key,$page=20){
        $this->load->model('customer_model','customer');
        $where = 'cm.id_business = '.$this->users['id_business'] . ' and cm.id_shop = '.$this->users['id_shop'] . ' and bs.id_shop = '.$this->users['id_shop'] . ' and bs.state = \'subscribe\'';
        if($search_key != ''){
            $where .= ' and (bs.nick_name like \'%'.urlencode($search_key).'%\' or cm.msg_content like \'%'.$search_key.'%\')';
        }
        $data = array();
        //获取快速回复（列表）信息
        $quick_reply = $this->customer->get_quick_reply($where,$offset,$page);
        if(!$quick_reply){
            $offset = 1;
            $quick_reply  = $this->customer->get_quick_reply($where,$offset,$page);
        }

        foreach($quick_reply as $key=>$val){
            $one = strtotime($val['created']);
            $tow = strtotime(date('y-m-d h:i:s'));
            $cle = $tow - $one; //得出时间戳差值

            $m = ceil($cle/60);//得出一共多少分钟
            if($m > 48*60){
                $quick_reply[$key]['time'] = '0';
            }else{
                $quick_reply[$key]['time'] = '1';
            }
//            $quick_reply[$key]['msg_content'] = base64_encode($quick_reply[$key]['msg_content']);
            $where1 = 'id_customer_msg = ' . $val['id_customer_msg'];
            $reply_count  = $this->customer->customer_reply_count($where1);
            $quick_reply[$key]['is_send'] = $reply_count;
        }
        $data['quick_reply'] = $quick_reply;
        //获取快速回复的总条数
        $total = $this->customer->quick_reply_count($where);
        $data['page_html']  = $this->get_page($total,$offset,'quick_reply_page','method', $page);

        return $data;
    }


    function get_url_size($cis){
        $file_size = 0;
        $image_url = '';
        $data = array();
        $i_num = strpos($cis,'http');
        if($i_num === false){
            //获取本地文件大小
            $file_size =  sprintf("%.1f", (filesize(BASE_PATH . '/..'.$cis)/1024));
            $i_nums = strrpos($cis,"/");
            if($i_nums === false){
            }else{
                $image_url = substr($cis,strrpos($cis,"/")+1);
            }
        }else{
            //获取远程文件大小
            $file_size = 0;//$this->getFileSize($cis);
            $image_url = $cis;
        }
        $data['image_url'] = $image_url;
        $data['file_size'] = $file_size;
        return $data;
    }

    /*
     * zxx
     * 二维数组排序
     * */
    function array_sort($arr,$keys,$type='asc'){
        $key_value = $new_array = array();
        foreach ($arr as $k=>$v){
            $key_value[$k] = $v[$keys];
        }
        if($type == 'asc'){
            asort($key_value);
        }else{
            arsort($key_value);
        }
        reset($key_value);
        foreach ($key_value as $k=>$v){
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }

    /*
     * zxx
     * 回复内容
     * */
    function get_reply($id_open,$offset,$page=20){
        $data = array();
        $this->load->model('business_model','business');
        //获取当前回复人信息
        $where1 = 'id_business = ' . $this->users['id_business'];
        $bus_info = $this->business->get_business_phone($where1);
        $nick_name = $bus_info[0]['name'];

        $this->load->model('customer_model','customer');
        $return = array();
        $where = 'cm.id_open = \'' . $id_open .'\'';
        $customer_msg = $this->customer->get_quick_reply($where,1,$page,'cm.created desc');
        $return = $customer_msg;
        if($customer_msg){
            foreach($customer_msg as $k1=>$cm){
                $return[$k1]['reply_content'] = $cm['msg_content'];
                unset($return[$k1]['msg_content']);
                //获取当前回复人的回复信息
                $where = 'id_customer_msg = '.$cm['id_customer_msg'];
                //获取回复列表信息
                $reply = $this->customer->get_reply($where,$offset,$page);
                foreach($reply as $r){
                    if(count($return) > $page){
                        break;
                    }
                    $r['nick_name'] = urlencode($nick_name);
                    array_push($return,$r);
                }
            }
        }
        $data_list = $this->array_sort($return,'created','desc');
        $data['reply'] = $data_list;
        //获取快速回复的总条数
        $total = $this->customer->customer_reply_count($where);
        $data['page_html']  = $this->get_page($total,$offset,'reply_page','method', $page);

        return $data;
    }

    /*
     * zxx 上传附件到微信 （图。 音，视频）
     * $type 上传附件类型
     * $url  上传附件地址
     * */
    function upload_wx_attachment(&$type,$access_token,$file_name,$path_name='reply'){
//        $access_token = $this->get_access_token($this->users['id_business'],$this->users['id_shop']);
        $path = get_img_url($file_name,$path_name,1,'bg_mr.png');
        $post_data['media'] = '@'.$path;
        if($access_token){
            if($type == 'audio'){
                $type = 'voice';
            }
            $url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=". $access_token."&type=".$type;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch) ;
            if (curl_errno($ch)) {
                return curl_error($ch);
            }
            curl_close($ch);
            return $result;
        }
        return 0;
    }

    /*
     * zxx 发送微信客服消息（图，图文。音频。视频）
     * $type  发送信息类型
     * $types  用来判断返回类型 1：字符串类型 0：json类型
     * */
function to_wx_attachment($type,$id_open,$data,$access_token,$types=0){
//        $access_token = $this->get_access_token($this->users['id_business'],$this->users['id_shop']);
    $msgTxt = '';

    if($type == 'text'){
        $msg_content = str_replace('"','&quot;',$data['msg_content']);
        $msgTxt = '{
                "touser":"'.$id_open.'",
                "msgtype":"text",
                "text":
                {
                     "content":"'.$msg_content.'"
                }
            }';
    }elseif($type == 'image'){
        $msgTxt = '{
            "touser":"'.$id_open.'",
            "msgtype":"image",
            "image":
            {
                "media_id":"'.$data['media_id'].'"
            }
            }';
    }elseif($type == 'image-text'){
        $msgTxt = '{
        "touser":"'.$id_open.'",
        "msgtype":"news",
        "news":{
            "articles": [';
        if(count($data['image-text']) > 0){
            foreach($data['image-text'] as $k=>$it){
                $description = str_replace('"','&quot;',$it['description']);
                if($k>0){
                    $msgTxt .= ',';
                }
                $msgTxt .= '{"title":"'.$it['title'].'",
                 "description":"'.$description.'",
                 "url":"'.$it['url'].'",
                 "picurl":"'.$it['pic_url'].'"
                }';
            }
        }else{
            $msgTxt .='{
                 "title":"none",
                 "description":"none",
                 "url":"URL",
                 "picurl":"PIC_URL"
             }';
        }
        $msgTxt .=']}}';
    }elseif($type == 'audio'){
        $msgTxt = '{
            "touser":"'.$id_open.'",
            "msgtype":"voice",
            "voice":
            {
                "media_id":"'.$data['media_id'].'"
            }
        }';
    }elseif($type == 'video'){
        $msg_content = $data['msg_content'];
        $title = substr($msg_content,0,10);
        $description = substr($msg_content,0,50);
        $msgTxt = '{
        "touser":"'.$id_open.'",
        "msgtype":"video",
        "video":
        {
          "media_id":"'.$data['media_id'].'",
          "title":"'.$title.'",
          "description":"'.$description.'"
        }
    }';
    }

    if($access_token){
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $access_token;
        $result = request_curl($url,$msgTxt);
        $result = json_decode($result,1);
        if($types == 1){
            if($result['errcode'] == 'ok'){
                $resp = array(
                    'status' => 1,
                    'msg' => '',
                    'data' => ''
                );
                return $resp;
            }else{
                $resp = array(
                    'status' => 0,
                    'msg' => $result['errmsg'],
                    'data' => ''
                );
                return $resp;
            }
        }else{
            if($result['errcode'] == 'ok'){
                $resp = array(
                    'status' => 1,
                    'msg' => '',
                    'data' => ''
                );
                die(json_encode($resp));
            }else{
                $resp = array(
                    'status' => 0,
                    'msg' => $result['errmsg'],
                    'data' => ''
                );
                die(json_encode($resp));
            }
        }
    }
    $resp = array(
        'status' => 0,
        'msg' => 'access_token不正确！',
        'data' => ''
    );
    if($types == 1){
        return $resp;
    }else{
        die(json_encode($resp));
    }
}



    /*
     * 图文回复公用方法
     * */
    function img_text_reply($type = 1){
        $data = array();
        $select_op = array(
            '71' => array('info'=>'内容'),
            '54' => array('activity'=>'活动'),
            '57' => array('cmd'=>'物品')
        );
        $select_op_access = array();

        //去权限表检测是否有权限
        $this->load->model('right_model','right');
        $has_access = $this->right->check_select_options_access($this->users['id_business'],array_keys($select_op));
        foreach ($has_access as $k => $v){
            if(in_array($v['id_right'], array_keys($select_op))){
                $select_op_access = array_merge($select_op_access,$select_op[$v['id_right']]);
            }
        }

        $data['sop'] = $select_op_access;
        return $data;
    }



    /**
     * 拼装商家微官网首页。连接页面地址
     * @param string $type list(列表页)/eitity(文章详情)/link(外部连接。直接使用)
     * @param string $object_type	info/activity/commodity		功能模块
     * @param int    $id_object	功能对应的分类编号或者实体编号
     * @param string $url	链接地址
     * @param int $id_business 商家ID
     * @param int $id_shop 门店ID
     * $template 模板类型  1：默认模板  2：模板2
     * http://www.hipigo.com/wapi/1/0/home/class_list?r=info&c=7
     */
    function get_link_url($type,$object_type,$id_object,&$url,$id_business='',$id_shop='',$template=1){
        if($id_shop == ''){
            $this->load->model('shop_model','shop');
            $where = 'id_business = ' . $id_business;
            $shopInfo = $this->shop->get_shop_introduction($where);
            $id_shop = $shopInfo[0]['id_shop'];
        }

        if($this->users['biz_mod'] == 0){
            $id_shop = 0;
        }
        $host = 'http://'.$_SERVER['HTTP_HOST'].'/wapi/'.$id_business.'/'.$id_shop.'/home/';
        switch($type){
            case 'list':
                $host.='class_list?t='.$object_type.'&id='.$id_object.'&template='.$template;
                $url = $host;
                break;
            case 'eitity':
                $host.='content/'.$id_object;
                $url = $host;
                break;
        }
        return $url;
    }



    /*
     * 图片压缩处理 start
     */

    /**
     * 获取需要添加水印的图片的信息，并载入图片。
     * @param $file  来源图片
     * @return bool|resource
     */
    private function imgInfo($file)
    {
        if( ! file_exists($file))
            return FALSE;

        //取得文件的类型,根据不同的类型建立不同的对象
        list($width, $height, $ext) = @getimagesize($file);
        switch (image_type_to_mime_type($ext)) {
            case 'image/jpeg':
                $image = @imagecreatefromjpeg($file);
                break;
            case 'image/png':
                $image = @imagecreatefrompng($file);
                break;
            case 'image/gif':
                $image = @imagecreatefromgif($file);
                break;
            case 'image/bmp':
                $image = @imagecreatefromwbmp($file);
                break;
        }
        return $image;
    }

    /**
     * 水印位置算法
     * @param $pos
     * @param $sourceImg
     * @param $waterImg
     */
    private function waterPos($sourceImg, $waterImg, $pos)
    {
        switch ($pos) {
            case 0:     //随机位置
                $this->x = rand(0, $sourceImg[0] - $waterImg[0]);
                $this->y = rand(0, $sourceImg[1] - $waterImg[1]);
                break 1;
            case 1:     //上左
                $this->x = 0;
                $this->y = 0;
                break 1;
            case 2:     //上中
                $this->x = ($sourceImg[0] - $waterImg[0]) / 2;
                $this->y = 0;
                break 1;
            case 3:     //上右
                $this->x = $sourceImg[0] - $waterImg[0];
                $this->y = 0;
                break 1;
            case 4:     //中左
                $this->x = 0;
                $this->y = ($sourceImg[1] - $waterImg[1]) / 2;
                break 1;
            case 5:     //中中
                $this->x = ($sourceImg[0] - $waterImg[0]) / 2;
                $this->y = ($sourceImg[1] - $waterImg[1]) / 2;
                break 1;
            case 6:     //中右
                $this->x = $sourceImg[0] - $waterImg[0];
                $this->y = ($sourceImg[1] - $waterImg[1]) / 2;
                break 1;
            case 7:     //下左
                $this->x = 0;
                $this->y = $sourceImg[1] - $waterImg[1];
                break 1;
            case 8:     //下中
                $this->x = ($sourceImg[0] - $waterImg[0])/2;
                $this->y = $sourceImg[1] - $waterImg[1];
                break 1;
            default:    //下右
                $this->x = $sourceImg[0] - $waterImg[0];
                $this->y = $sourceImg[1] - $waterImg[1];
                break 1;
        }
    }

    /**
     * @param $sourceImg
     * @param $waterImg
     * @param int $pos
     * @param int $alpha
     */
    private function waterImg($sourceImg, $waterImg, $pos = 9, $alpha = 70)
    {
        if ($sourceImg[0] <= $waterImg[0] || $sourceImg[1] <= $waterImg[1])
            return FALSE;

        $this->waterPos($sourceImg, $waterImg, $pos);
        $cut = imagecreatetruecolor($waterImg[0], $waterImg[1]);
        imagecopy($cut, $this->image,0,0,$this->x,$this->y,$waterImg[0],$waterImg[1]);
        imagecopy($cut, $this->waterImg, 0,0,0,0, $waterImg[0], $waterImg[1]);
        imagecopymerge($this->image, $cut, $this->x, $this->y, 0,0, $waterImg[0], $waterImg[1], $alpha);
    }

    /**
     * @param $sourceImg
     * @param string $waterStr
     * @param int $fontSize
     * @param string $fontColor
     * @param string $fontFile
     * @param int $pos
     */
    private function waterStr($sourceImg, $waterStr = "http://www.hipigo.cn", $fontSize = 12,
                              $fontColor = "255,255,255", $fontFile = "simsunb.ttf", $pos = 9)
    {
        if($fontFile == "simsunb.ttf")
            $fontFile = BASE_PATH . "/" . $fontFile;

        if($sourceImg[0] < 200 || $sourceImg[1] < 200)
            $fontSize = 8;

        $rect = imagettfbbox($fontSize, 0, $fontFile, $waterStr);
        $w = abs($rect[2] - $rect[6]);
        $h = abs($rect[3] - $rect[7]);

        $this->waterImg = imagecreatetruecolor($w, $h);
        imagealphablending($this->waterImg, FALSE);
        imagesavealpha($this->waterImg, TRUE);

        //原图小于水印图 不加
        if($sourceImg[0] < $w || $sourceImg[1] < $h)
            return FALSE;

        $white_alpha = imagecolorallocatealpha($this->waterImg, 255, 255, 255, 127);
        imagefill($this->waterImg, 0,0, $white_alpha);

        $fontColor = explode(',', $fontColor);
        $color = imagecolorallocate($this->waterImg, $fontColor[0], $fontColor[1], $fontColor[2]);
        imagettftext($this->waterImg, $fontSize ,0,0, $fontSize, $color, $fontFile, $waterStr);
        $this->waterImg($sourceImg, array(0 => $w, 1 => $h), $pos);
    }

    /**
     * @param $sourceImg
     * @return bool
     */
    public function waterMark($sourceImg)
    {
        $this->image = $this->imginfo($sourceImg);
        $imageSetting = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", 'imageSetting');

        //管理员没开启添加水印
        if($imageSetting->start != 1)
            return FALSE;

        if ($imageSetting->waterType != 1) {
            $this->waterStr(@getimagesize($sourceImg), $imageSetting->waterStr, $imageSetting->fontSize, $imageSetting->fontColor,
                $imageSetting->fontFile, $imageSetting->pos, $imageSetting->alpha);
        }
        else {
            $waterPath = realpath(BASE_PATH . $imageSetting->waterImg);
            $this->waterImg = $this->imgInfo($waterPath);
            $this->waterImg(@getimagesize($sourceImg), @getimagesize($waterPath), $imageSetting->pos, $imageSetting->alpha);
        }

        imagejpeg($this->image, $sourceImg);
        imagedestroy($this->image);
        imagedestroy($this->waterImg);
    }

    /**
     * 拉伸图片
     * @author Jamai
     * @version 1.5
     *
     * @param     $file
     * @param     $fileName
     * @param int $cropWidth
     * @param int $cropHeight
     * @param int $type
     * @param int $quality
     *
     * @return bool
     */
    public function cropImage($file, $fileName,  $cropWidth = 800, $cropHeight = 800, $type = 1, $quality = 70)
    {
        $image = $this->imgInfo($file);

        //处理， 当图片小于512KB时 图片不进行压缩
        //if( abs(filesize($file)) < 1024 * 512)
        //    $quality = 100;

        #如果是执行调整尺寸操作则
        list($w, $h) = @getimagesize($file);
        $width = $w; $height = $h;

        $ratio = $width / $height;
        if($cropWidth == 0)
            $cropWidth = $cropHeight * $ratio;
        if($cropHeight == 0)
            $cropHeight = $cropWidth / $ratio;

        if($cropWidth >= $width && $cropHeight >= $height) {  //处理裁剪/缩放图片 > 原图
            $nImg = imagecreatetruecolor($w, $h);
            imagefilledrectangle($nImg, 0, 0, $w, $h, imagecolorallocate($nImg, 255, 255, 255));
            imagecopyresampled($nImg, $image, 0,0,0,0, $w, $h, $w, $h);
        }
        else {
            if($type == 1) {
                $cropRatio = $cropWidth / $cropHeight;
                if($cropRatio > $ratio)
                    $cropHeight = abs($cropWidth / $ratio);
                if($ratio > $cropRatio)
                    $cropWidth = abs($ratio * $cropHeight);

                /*
                if($width > $cropWidth) {
                    $tmpVar = $cropWidth / $width;
                    $width  = $cropWidth;
                    $height = $height * $tmpVar;
                    if($height > $cropHeight) {
                        $tmpVar = $cropHeight / $height;
                        $height = $cropHeight;
                        $width  = $width * $tmpVar;
                     }
                }
                elseif($height > $cropHeight) {
                    $tmpVar = $cropHeight / $height;
                    $height = $cropHeight;
                    $width  = $width * $tmpVar;
                    if($width > $cropWidth) {
                        $tmpVar = $cropWidth / $width;
                        $width  = $cropWidth;
                        $height = $height * $tmpVar;
                    }
                }
                */
                $nImg = imagecreatetruecolor($cropWidth, $cropHeight);     #新建一个真彩色画布
                imagefilledrectangle($nImg, 0, 0, $cropWidth, $cropHeight, imagecolorallocate($nImg, 255, 255, 255));
                imagecopyresampled($nImg, $image, 0,0,0,0, $cropWidth, $cropHeight, $width, $height); #重采样拷贝部分图像并调整大小
            }
            else { //裁剪.
                $nImg = imagecreatetruecolor($cropWidth, $cropHeight);
                imagefilledrectangle($nImg, 0, 0, $cropWidth, $cropHeight, imagecolorallocate($nImg, 255, 255, 255));

                if($h / $w > $cropHeight / $cropWidth) { #高比较大
                    $height = $h * $cropWidth / $w;
                    $intNH  = $height - $cropHeight;
                    imagecopyresampled($nImg, $image, 0, -$intNH/1.8, 0,0, $cropWidth, $height, $w, $h);
                }
                else {     #宽比较大
                    $width  = $w * $cropHeight / $h;
                    $intNH  = $width - $cropWidth;
                    imagecopyresampled($nImg, $image, -$intNH/1.8, 0, 0,0, $width, $cropHeight, $w, $h);
                }
            }
        }

        imagejpeg ($nImg, $fileName, $quality);          #以JPEG格式将图像输出到浏览器或文件
        imagedestroy($nImg);
        imagedestroy($image);
        return TRUE;
    }

    /**
     * 裁剪图片
     *
     * @param $file
     * @param $fileName
     * @param $x
     * @param $y
     * @param $x2
     * @param $y2
     * @param $cropWidth
     * @param $cropHeight
     */
    public function thumbImage($file, $fileName, $x, $y, $x2, $y2, $cropWidth, $cropHeight)
    {
        $image = $this->imgInfo($file);
        list ($srcWidth, $srcHeight) = @getimagesize($file);

        $widthRatio = $srcWidth / $cropWidth;
        $heightRatio = $srcHeight / $cropHeight;
        $nW = $widthRatio * ($x2 - $x);
        $nH = $heightRatio * ($y2 - $y); //按照比例来求高

        $nX = $widthRatio * $x;
        $nY = $heightRatio * $y;

        //var_dump($nX, $nY, $nW, $nH, $cropWidth, $cropHeight);exit;

        echo $nW, $nH;
        //创建缩略图
        $newImg = imagecreatetruecolor($nW, $nH);
        //复制图片
        imagecopyresampled($newImg, $image, 0, 0, $nX, $nY, $nW, $nH, $nW, $nH);

        imagejpeg($newImg, $fileName, 100);
        imagedestroy($newImg);
        imagedestroy($image);
    }

    /*
     * 图片压缩处理 end
     */
     
     
     /**
     * 返回客户端信息通用函数
     * @param number $status 返回状态
     * @param string $data	包含的数据
     * @param string $msg	状态说明
     */
    protected function return_client($status = 0, $data = null, $msg = null)
    {

        global $starttime;

        $resp = array(
            'status' => $status,
            'data' => empty($data) ? null : $data,
            'msg' => empty($msg) ? null : $msg,
            'time' => microtime(true) - $starttime);
        $json = json_encode($resp);
        die($json);
    }
}
/* End of file admin_controller.php */