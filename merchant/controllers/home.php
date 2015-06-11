<?php
/**
 * 
 * @copyright(c) 2013-11-20
 * @author msi
 * @version Id:home.php
 */

class Home extends Admin_Controller
{
    private $id_business;
    private $id_shop;
    public function __construct()
    {
        parent::__construct();
        $this->id_business = $this->users['id_business'];
        $this->id_shop = $this->users['id_shop'];//门店id

        if(empty($this->users) || empty($this->session_id)){
            header('location:'.$this->url.'user/login');
            die ;
        }
    }

    public function up_old(){
        $this->load->model('community_model','community');
        $this->community->update_old_bn();		
	}

	public function index(){
        if(empty($this->users) || empty($this->session_id)){
            header('location:'.$this->url.'user/login');
        }
	    $where = $where2 = $where3 = $where4 = $where5= array();
        $where['id_business'] =$where2['id_business']= $where3['id_business']= $this->id_business;
		$where['id_shop']= $where2['id_shop'] = $where3['id_shop'] = $this->id_shop;
        $where5 = $where;
		$where5['state'] = 'subscribe';

        $this->load->model('business_model','business');
        $data = $this->business->get_business_inf(array('id_business'=>$this->id_business));
        $link = $data['sld'];
        $yesterday = date('Y-m-d',strtotime('-1 days'));
		$where["DATE_FORMAT(created,'%Y-%m-%d')"] = $yesterday;
        $where2["DATE_FORMAT(update_time,'%Y-%m-%d')"] = $yesterday;
		$where3["DATE_FORMAT(created,'%Y-%m-%d')"] = $yesterday;
        //关注数
		$sub = $this->business->get_user_count($where);
        //总数
		$cnt = $this->business->get_user_count($where5);
        //消息
        $msgcnt = $this->business->get_msg_count($where3,$where4);
        $this->smarty->assign('now',date('Y-m-d',time()));
		$this->smarty->assign('msg',$msgcnt);
		$this->smarty->assign('sub',$sub);
		$this->smarty->assign('cnt',$cnt);
        $this->smarty->assign('link',$link);
		$this->smarty->view('home');
		//header('location:'.$this->url.'settings/company_intro?cm=187');
	}

    /*
     * zxx
     * $id_config 分类配置id
     * 编辑首页分类配置
     * $page_num  当前编辑的配置页面id
     * */
    public function edit_home($page_num=0){
        $id_shop = $this->id_shop;
        if($this->users['biz_mod'] == 0){//共享模式
            $id_shop = 0;
        }
        $this->load->model('micrositeconfig_model','micrositeconfig');
        $this->load->model('business_model','business');
        if($_POST){
            $is_add = $this->input->post('is_add');
            $id_config = $this->input->post('id_config');
            $page_column = $this->input->post('page_column');
            $name = $this->input->post('name');
            $image = $this->input->post('image');
            $type = $this->input->post('type');
            $object_type = $this->input->post('object_type');
            $id_object = $this->input->post('id_object');
            $url = $this->input->post('url');
            $weight = $this->input->post('weight');
            $logo_src = $this->input->post('logo_src');
            $image_id = $this->input->post('image_id');
            $template = $this->input->post('template');
            //保存图片资源
            if($logo_src != '' && $image_id != ''){
                $this->save_image($this->id_business,$id_shop,$id_config,$logo_src,$image_id);
            }

            $data = array(
                'id_business' => $this->id_business,
                'id_shop' => 0,
                'id_column'=>$page_column,
                'name' => $name,
                'type' => $type,
                'object_type' => $object_type=='cmd'?'commodity':$object_type,
                'id_object' => $id_object,
                'url' => $url,
                'template'=>$template,
                'weight' => $weight
            );
            if($image){
                $data['image'] = $image;
            }

            if($is_add == 'add'){
                $ci = $this->micrositeconfig->insert_site_config($data);
                if($ci){
                    $this->returen_status(1,"提交成功！",$data);
                }else{
                    $this->returen_status(0,"提交失败，请重试！",$data);
                }
            }else{
                $where = 'id_config = '.$id_config;
                $cu = $this->micrositeconfig->update_site_config($data,$where);
                if($cu){
                    $this->returen_status(1,"修改成功！",$data);
                }else{
                    $this->returen_status(0,"修改失败，请重试！",$data);
                }
            }
        }else{
            $file_path = 'attachment';
            $id_object = 0;
            $where = 'ba.object_type = \'bn\' and ba.id_object = '.$id_object;
            if($page_num){
                $id_object = $page_num;
                $where = 'ba.object_type = \'site\' and ba.id_object = ' . $id_object;
                $file_path = 'site';
            }
            //获取商家信息
            $merchant = $this->business->get_business_info($this->id_business, $where);
            $images = explode(',', $merchant[0]['image_url']);
            $id_attachments = explode(',', $merchant[0]['id_attachment']);
            foreach ($images as $k=>$value) {
                $path[$k][] = get_img_url($value, $file_path, 0, 'bg_mr.png');
                $path[$k][] = $id_attachments[$k];
            }
            $data['images'] = $path;
            $data['image_url_count'] = $images == '' ? 0 : count($images);
            $data['page_num'] = $page_num;

            $where = 'id_business = '.$this->id_business . ' and id_shop = 0 and id_column = '.$id_object;
            $config_info = $this->micrositeconfig->get_site_config($where);
            foreach($config_info as $k=>$c){
                $config_info[$k]['link'] = $this->get_link_url($c['type'],$c['object_type'],$c['id_object'],$c['url'],$this->id_business,$id_shop,$c['template']);
            }

            $where_c = 'id_business = '.$this->id_business .' and id_shop = 0';
            $page_info = $this->micrositeconfig->get_page_column($where_c);
            //功能模块
            $return = $this->img_text_reply(0);
            $this->smarty->assign($return);

            $this->smarty->assign('id_business',$this->id_business);
            $this->smarty->assign('id_shop',$id_shop);

            $this->smarty->assign($data);
            $this->smarty->assign('page_info',$page_info);
            $this->smarty->assign('config_info',$config_info);
            $this->smarty->assign('page_type','site_config');
            $this->smarty->assign('cm',$_GET['cm']);
            $this->smarty->view('edit_home');
        }
    }

    /*
     * zxx
     * 展示页面改变的配置显示
     */
    public function show_config(){
        $id_config = $this->input->post('id_config');
        $this->load->model('micrositeconfig_model','micrositeconfig');

        //获取首页配置信息
        $where = 'id_config = ' . $id_config;
        $config_info = $this->micrositeconfig->get_site_config($where);
        foreach($config_info as $k=>$c){
            $config_info[$k]['link'] = $this->get_link_url($c['type'],$c['object_type'],$c['id_object'],$c['url'],$this->id_business,$this->id_shop);
        }

        //页面模块
        $where = 'id_business = '.$this->id_business . ' and id_shop = ' . $this->id_shop;
        $page_info = $this->micrositeconfig->get_page_column($where);
        $this->smarty->assign('page_info',$page_info);

        //功能模块
        $return = $this->img_text_reply(0);
        $this->smarty->assign($return);

        $this->load->model('commodity_model','commodity');
        $where_class = 'id_business = '.$this->id_business. ' and type = \''.$config_info[0]['object_type'].'\'';
        //分类
        $commodity_class = $this->commodity->get_commodity_class($where_class);
        $this->smarty->assign('commodity_class',$commodity_class);

        $this->smarty->assign('config_info',$config_info);
        $this->smarty->assign('page_type','site_config');

        echo $this->smarty->view('lists');
    }

    //根据功能模块显示分类模块
    public function get_class(){
        $type = $this->input->post('type') ? $this->input->post('type') : 'info';
        $this->load->model('commodity_model','commodity');
        $where_class = 'id_business = '.$this->id_business. ' and type = \''.$type.'\'';
        //分类
        $commodity_class = $this->commodity->get_commodity_class($where_class);
        $this->smarty->assign('commodity_class',$commodity_class);
        $this->smarty->assign('page_type','get_class');

        echo $this->smarty->view('lists');
    }


    /*
     * zxx
     * 删除首页分类配置
     * */
	public function delete_site_config(){
        $id_config = $this->input->post('id_config');

        $this->load->model('micrositeconfig_model','micrositeconfig');
        $where = 'id_config = '. $id_config;

        $config_info= $this->micrositeconfig->get_site_config($where);
        foreach($config_info as $ci){
            //获取本地文件图片附件地址
            $path = $this->file_url_name('home',$ci['image']);
            if(!file_exists($path) && !is_readable($path)){
            }else{
                unlink($path);//删除图片附件
            }
        }
        //删除首页配置信息
        $r = $this->micrositeconfig->delete_site_config($where);
        if($r){
            $resp = array(
                'status' => 1,
                'msg' => '',
                'data' => ''
            );
        }else{
            $resp = array(
                'status' => 0,
                'msg' => '删除失败',
                'data' => ''
            );
        }
        die(json_encode($resp));
    }



	public function test_mem(){
		$this->load->library('session');
		$session_id = $this->session->userdata('session_id');
        echo $session_id.'<br />';
		$mem = new memcache();
		$mem->connect("10.0.0.15", 11211);
		$mem->set('key','This is a test!', 0, 60);
		$val = $mem->get($session_id);
		var_dump($val);
//        phpinfo();
	}


    /*
     *zxx
     * 添加页面模块信息
     */
    public function add_column(){
        $name = $this->input->post('name');

        $this->load->model('micrositeconfig_model','micrositeconfig');
        $data = array(
            'id_business'=>$this->id_business,
            'id_shop'=>0,
            'name'=>$name
        );
        $config_info= $this->micrositeconfig->insert_site_column($data);

        $resp = array(
            'status' => 1,
            'msg' => '添加成功！',
            'data' => $config_info
        );
        die(json_encode($resp));
    }

    /*
     * zxx
     * 改变页面模块信息
     */
    function change_page(){
        $page_num = $this->input->post('page_num');
        $id_shop = $this->id_shop;
        if($this->users['biz_mod'] == 0){//共享模式
            $id_shop = 0;
        }
        $this->load->model('micrositeconfig_model','micrositeconfig');
        $where = 'id_business = '.$this->id_business . ' and id_shop = 0 and id_column = ' . $page_num;
        $config_info = $this->micrositeconfig->get_site_config($where);
        foreach($config_info as $k=>$c){
            $config_info[$k]['link'] = $this->get_link_url($c['type'],$c['object_type'],$c['id_object'],$c['url'],$this->id_business,$id_shop,$c['template']);
        }

        $this->smarty->assign('config_info',$config_info);
        $this->smarty->assign('page_type','change_page');

        $this->load->model('business_model','business');
        //获取商家信息
        $file_path = 'attachment';
        if($page_num == 0){
            $where = 'ba.object_type = \'bn\' and ba.id_object = 0';
        }else{
            $file_path = 'site';
            $where = 'ba.object_type = \'site\' and ba.id_object = ' . $page_num;
        }
        $merchant = $this->business->get_business_info($this->id_business, $where);
        $images = array();
        $path = array();
        if($merchant[0]['image_url']){
            $images = explode(',', $merchant[0]['image_url']);
            $id_attachments = explode(',', $merchant[0]['id_attachment']);
            foreach ($images as $k=>$value) {
                $path[$k][] = get_img_url($value, $file_path, 0, 'bg_mr.png');
                $path[$k][] = $id_attachments[$k];
            }
        }
        $data['images'] = $path;
        $data['image_url_count'] = $images == '' ? 0 : count($images);
        $data['page_num'] = $page_num;

        $this->smarty->assign($data);
        $list_left = $this->smarty->fetch('lists.html');
        $resp = array(
            'status' => 1,
            'list_left' => $list_left,
            'list_right' => $data
        );
        die(json_encode($resp));
    }


    /*
     * zxx
     * 读取顶部显示的图片
     */
    function get_top_image(){
        $this->load->model('business_model','business');
        //获取商家信息
        $where = 'ba.object_type = \'bn\' and ba.id_object = 0';
        $merchant = $this->business->get_business_info($this->id_business, $where);
        $images = explode(',', $merchant[0]['image_url']);
        $id_attachments = explode(',', $merchant[0]['id_attachment']);
        foreach ($images as $k=>$value) {
            $path[$k][] = get_img_url($value, 'attachment', 0, 'bg_mr.png');
            $path[$k][] = $id_attachments[$k];
        }
        $data['images'] = $path;
        $data['image_url_count'] = $images == '' ? 0 : count($images);

        $this->smarty->assign($data);
    }


    /*
     * zxx
     * 保存顶部显示的图片
     */
    function save_top_img(){
        $id_shop = $this->id_shop;
        if($this->users['biz_mod'] == 0){//共享模式
            $id_shop = 0;
        }
        $id_config = $this->input->post('id_config');
        $logo_src = $this->input->post('logo_src');
        $image_id = $this->input->post('image_id');

        $this->save_image($this->id_business,$id_shop,$id_config,$logo_src,$image_id);

        echo '图片附件处理成功！';
        die;
    }


    /*
     * zxx
     * 保存图片资源
     */
    function save_image($id_business,$id_shop,$id_config,$logo_src,$image_id){
        $this->load->model('business_model','business');
        if($image_id){
            $image_ids = explode(',',$image_id);
            foreach($image_ids as $ii){
                $where = 'id_attachment = ' . $ii;
                $this->business->delete_synopsis_image($where);
            }
        }

        $image_info = array(
            'id_business' => $id_business,
            'id_shop' => $id_shop,
            'object_type' => 'site',
            'id_object' => $id_config,
            'attachment_type' => 'image'
        );

        $firm_img_name = '';
        $firm_img_size = '';
        if( trim($logo_src) ){//图片
            $data_image_src = explode(',',$logo_src);
            foreach($data_image_src as $k=>$dis){
                //图片文件保存处理
                $firm_img_name = $dis;
                //获取本地文件地址
                $path = $this->file_url_name('site',$dis);
                //获取本地文件大小
                $firm_img_size = sprintf("%.1f", (filesize($path)/1024));

                $image_info['image_url'] = $firm_img_name;//图片名
                $image_info['size'] = $firm_img_size;//上传图片大小

                //插入微官网配置顶部图片附件
                $this->business->insert_business_attachment($image_info);
            }
        }
    }
}
/* End of file home.php */