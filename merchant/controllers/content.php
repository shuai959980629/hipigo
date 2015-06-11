<?php
/**
 * 
 * @copyright(c) 2013-12-26
 * @author msi
 * @version Id:content.php
 */

class Content extends Admin_Controller{

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
        $this->load->model('info_model','info');
    }

	/**
     * zxx
	 * 添加内容
	 */
	public function add_content(){
        $data ['url_action'] = 'content/add_content';
        if($this->input->post('title'))
        {
            $this->load->library('form_validation');
            if ($this->form_validation->run('add_content') === TRUE)
            {
                $id_commodity_class = $this->input->post('membership');//商品分类编号
                $title = $this->input->post('title');//标题
                $content = $this->input->post('content');//内容
                $weight = $this->input->post('weight');//权重
                $state = $this->input->post('state');//状态
                $recommend = $this->input->post('recommend');//推荐

                $title = $this->replace_html($title);
                $content = htmlspecialchars_decode($content);
                $content_info = array(
                   'id_business' => $this->id_business,
                   'id_shop' => $this->id_shop,
                   'title' => $title,
                   'content' => $content,
                   'weight' => $weight,
                   'state' => $state,
                   'recommend' => $recommend,
                   'id_class'=>$id_commodity_class,
                   'created'=>date('Y-m-d H:i:s', time())
                );

                $id_info = $this->info->insert_info($content_info);

                $img_count = 0;
                $media_count = 0;

                preg_match_all('/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/', $content, $content_img);//匹配出src里面的图片路径
                preg_match_all('/<[embed|embed].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png|\.mp4|\.mp3|\.ogg|\.wav]))[\'|\"].*?[\/]?>/', $content, $content_audio);//匹配出src里面的图片路径

                $content_imgs = array();
                if(count($content_img) >= 2){
                    $content_imgs = $content_img[1];
//                    $img_count = count($content_img[1]);
                }
                $content_audios = array();
                if(count($content_audio) >= 2){
                    $content_audios = $content_audio[1];
//                    $media_count = count($content_audio[1]);
                }
//                if($img_count+$media_count > 20){
//                    $data['is_refresh'] = 0;
//                    $this->returen_status(0,"请确定附件最多20个！",$data);
//                }

                //将音视频路径加入到图片附件路径数组中
                foreach($content_audios as $k=>$ca){
                    $content_imgs[count($content_imgs)+$k] = $ca;
                }

                foreach($content_imgs as $ci){
                    $data = $this->get_url_size($ci);

                    $file_size = $data['file_size'];
                    $image_url = $data['image_url'];

                    $attachment_type = 'image';
                    $tmp = stripos($image_url, '.');
                    $file_type = substr($image_url,$tmp+1);

                    if(strtolower($file_type) == 'mp3' || strtolower($file_type) == 'ogg' ||strtolower($file_type) == 'wav'){
                        $attachment_type = 'audio';
                    }elseif(strtolower($file_type) == 'mp4' || strtolower($file_type) == 'swf'){
                        $attachment_type = 'video';
                    }
                    if($image_url != ''){
                        $content_info_attachment = array(
                            'id_info' => $id_info,
                            'type' => $attachment_type,
                            'show_url' => $image_url,
                            'size' => $file_size,
                        );
                        $this->info->insert_info_attachment($content_info_attachment);
                    }
                }

                $this->returen_status(1,$this->lang->line('add_data_success'),$data);
            }
            else
            {
                $this->returen_status(0,$this->form_validation->error_string(),$data);
            }
        }else{
            $this->load->model('commodity_model','commodity');
            $where_class = 'id_business = '.$this->id_business . ' and type = \'info\'';
            //物品分类
            $data['commodity_class'] = $this->commodity->get_commodity_class($where_class);

            $this->smarty->assign($data);
            $this->smarty->assign('type','add');
            $this->smarty->view('add_content');
        }
    }

    /**
     * zxx
     * 编辑内容
     * $id_info 内容id
     * $offset 页码
     * $item_sort 类型 mall:商城列表  commodity:物品列表
     */
	public function edit_content($id_info,$offset){
        $data ['url_action'] = 'content/edit_content/'.$id_info.'/'.$offset;
        if($this->input->post('title'))
        {
            $this->load->library('form_validation');
            if ( $this->form_validation->run('add_item') === TRUE)
            {
                $id_commodity_class = $this->input->post('membership');//商品分类编号
                $title = $this->input->post('title');//标题
                $content = $this->input->post('content');//内容
                $weight = $this->input->post('weight');//权重
                $state = $this->input->post('state');//状态
                $recommend = $this->input->post('recommend');//推荐

                $img_count = 0;
                $media_count = 0;
                preg_match_all('/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/', $content, $content_img);//匹配出src里面的图片路径
                preg_match_all('/<[embed|embed].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png|\.mp4|\.mp3|\.ogg|\.wav]))[\'|\"].*?[\/]?>/', $content, $content_audio);//匹配出src里面的图片路径

                $content_imgs = array();
                if(count($content_img) >= 2){
                    $content_imgs = $content_img[1];
//                    $img_count = count($content_img[1]);
                }
                $content_audios = array();
                if(count($content_audio) >= 2){
                    $content_audios = $content_audio[1];
//                    $media_count = count($content_audio[1]);
                }
//                if($img_count+$media_count > 20){
//                    $this->returen_status(0,"请确定附件最多20个！",$data);
//                }

                $content = htmlspecialchars_decode($content);
                $title = $this->replace_html($title);
                $content_info = array(
                    'id_business' => $this->id_business,
                    'id_shop' => $this->id_shop,
                    'title' => $title,
                    'content' => $content,
                    'weight' => $weight,
                    'state' => $state,
                    'recommend' => $recommend,
                    'id_class'=>$id_commodity_class,
                    'created'=>date('Y-m-d H:i:s', time())
                );

                $where = 'id_info = '.$id_info;
                $this->info->update_info($content_info,$where);

                $info_attachment = $this->info->get_info_attachment($where);

                //将音视频路径加入到图片附件路径数组中
                foreach($content_audios as $k=>$ca){
                    $content_imgs[count($content_imgs)+$k] = $ca;
                }

                $dbs = $info_attachment;//数据库中查询的附件数据
                $datas = $content_imgs;//编辑文本框post的附件数据
                if(count($datas) > 0){
                    foreach($datas as $k=>$ci){
                        $data = $this->get_url_size($ci);

                        $file_size = $data['file_size'];
                        $image_url = $data['image_url'];

                        $attachment_type = 'image';
                        $tmp = stripos($image_url, '.');
                        $file_type = substr($image_url,$tmp+1);

                        if(strtolower($file_type) == 'mp3' || strtolower($file_type) == 'ogg' ||strtolower($file_type) == 'wav'){
                            $attachment_type = 'audio';
                        }elseif(strtolower($file_type) == 'mp4' || strtolower($file_type) == 'swf'){
                            $attachment_type = 'video';
                        }


                        if(count($dbs) > 0){
                            foreach($dbs as $key=>$ia){
                                if($image_url == $ia['show_url']){
                                    unset($content_imgs[$k]);
                                    unset($info_attachment[$key]);
                                }
                            }
                        }else{
                            //如果存在传来的图片信息。但没有数据库信息。则直接处理post来的数据
                            if($image_url != ''){
                                $content_info_attachment = array(
                                    'id_info' => $id_info,
                                    'type' => 'image',
                                    'show_url' => $image_url,
                                    'size' => $file_size,
                                );
                                $this->info->insert_info_attachment($content_info_attachment);
                            }
                        }
                    }
                    if(count($dbs) > 0){
                        //如果传来的筛选后还有图片信息则插入数据库
                        if(count($content_imgs)>0){
                            foreach($content_imgs as $cis){
                                $data = $this->get_url_size($cis);

                                $file_size = $data['file_size'];
                                $image_url = $data['image_url'];

                                if($image_url != ''){
                                    $content_info_attachment = array(
                                        'id_info' => $id_info,
                                        'type' => 'image',
                                        'show_url' => $image_url,
                                        'size' => $file_size,
                                    );
                                    $this->info->insert_info_attachment($content_info_attachment);
                                }
                            }
                        }

                        //如果数据库查询出来的信息的筛选后还有图片信息则删除数据库信息
                        if(count($info_attachment)>0){
                            foreach($info_attachment as $ias){
                                $where = 'id_attachment = '.$ias['id_attachment'];
                                $this->info->delete_info_attachment($where);

                                //删除已上传的文件附件
                                $filename = $ias['show_url'];
                                $f_num = strpos($filename,'http');
                                if($f_num === false){
                                    //获取本地文件地址
                                    $path = $this->file_url_name('content',$filename);
                                    if(!file_exists($path) && !is_readable($path)){
//                                        $this->returen_status(0,$filename.'文件不在或只有只读权限~~',$data);
                                    }else{
                                        unlink($path);
                                    }
                                }
                                //删除文件附件 end
                            }
                        }
                    }
                }else{
                    //传来的内容里没图片信息。则删除附件表里面的所有图片
                    if(count($dbs) > 0){
                        foreach($dbs as $key=>$ia){
                            //删除已上传的文件附件
                            $filename = $ia['show_url'];
                            $f_num = strpos($filename,'http');
                            if($f_num === false){
                                //获取本地文件地址
                                $path = $this->file_url_name('content',$filename);
                                if(!file_exists($path) && !is_readable($path)){
//                                    $this->returen_status(0,$filename.'文件不在或只有只读权限~~',$data);
                                }else{
                                    unlink($path);
                                }
                                $where = 'id_info = ' . $ia['id_info'];
                                $this->info->delete_info_attachment($where);
                            }
                            //删除文件附件 end
                        }
                    }
                }

                $this->returen_status(1,$this->lang->line('edit_data_success'),$data);//'编辑商品信息成功'
            }
            else
            {
                $this->returen_status(0,$this->lang->line('edit_data_failed'),$data);
            }
        }else{
            $where_class = 'id_business = '.$this->id_business. ' and type = \'info\'';
            //物品分类
            $data['commodity_class'] = $this->info->get_info_class($where_class);

            $where = 'i.id_info = '.$id_info;
            //获取物品列表
            $content_info = $this->info->get_info_introduction($where,0, 20, '',0);
            if($content_info){
                $data['content_info'] = $content_info[0];
            }
            $this->smarty->assign($data);
            $this->smarty->assign('type','edit');
            $this->smarty->view('add_content');
        }
    }
	
	
	/**
     * zxx
	 * 删除物品
	 */
	public function del_content(){
        $id_class = $this->input->post('id_class');
        $id_info_class = $id_class ? $id_class : 'all';//物品分类编号
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        $id_info = $this->input->post('id_info');//物品id
        $search_key = $this->input->post('search_key') ? $this->input->post('search_key') : '';

        //删除物品信息
        $where = 'id_info = '.$id_info;
        $this->info->delete_info($where);

        //获取物品列表
        $data = $this->get_info($id_info_class,$offset,$search_key);

        if($data == ''){
            header('Location:http://'.$_SERVER['HTTP_HOST'].'/biz/');
            exit;
        }
        $this->smarty->assign($data);
        $this->smarty->assign('offset',$offset);
        $this->smarty->assign('page_type','info');

        $data = $this->smarty->fetch('lists.html');
        $resp = array(
            'status' => 1,
            'msg' => '',
            'data' => $data
        );
        die(json_encode($resp));
    }


    /**
     * zxx
     * 内容列表
     * $offset 页码
     * $type 类型 （0：默认 1：从活动过来绑定文章的）
     * $id_activity 活动id
     */
    public function list_content($offset=0,$type=0,$id_activity=0){
        $id_class = $_GET['id'];

        if(!$id_class){
            $is_class = 0;//地址栏不存在id，则显示分类下拉
            $id_class = $this->input->post('id_class');
        }else{
            $is_class = 1;//地址栏存在id，则不需要显示分类下拉
        }

        $id_info_class = $id_class ? $id_class : 'all';//内容分类编号
        if(!$offset){
            $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        }

        $search_key = $this->input->post('search_key') ? $this->input->post('search_key') : '';
        //获取物品列表
        $data = $this->get_info($id_info_class,$offset,$search_key);

        if($data == ''){
            header('Location:http://'.$_SERVER['HTTP_HOST'].'/biz/');
            exit;
        }
        $this->smarty->assign($data);
        $this->smarty->assign('offset',$offset);
        $this->smarty->assign('type',$type);
        $this->smarty->assign('id_activity',$id_activity);
        $this->smarty->assign('page_type','info');
        $this->smarty->assign('is_class',$is_class);

        if($id_class && $is_class != 1){
            echo $this->smarty->view('lists');
        }else{
            $this->smarty->view('list_content');
        }
    }


    /**
     * zxx
     * 物品搜索
     */
    public function search_content(){
        $id_class = $this->input->post('id_class');
        $id_info_class = $id_class ? $id_class : 'all';//商品分类编号
        $search_key = $this->input->post('search_key') ? $this->input->post('search_key') : '';
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码

        //获取物品列表
        $data = $this->get_info($id_info_class,$offset,$search_key);

        if($data == ''){
            header('Location:http://'.$_SERVER['HTTP_HOST'].'/biz/');
            exit;
        }

        $this->smarty->assign($data);
        $this->smarty->assign('offset',$offset);
        $this->smarty->assign('page_type','info');

        echo $this->smarty->view('lists');
    }


    /*
     * 获取内容列表
     * zxx
     * $id_info_class：当前分类id
     * $offset ：页码
     * $page ：条数
     * **/
    function get_info($id_info_class,$offset,$search_key='',$page=0){
        //分页数量
        $page = $page ? $page : ($this->config->item('page'));

        if(!$this->id_business){
            return '';
        }

        $where = 'i.id_shop = '.$this->id_shop.' and i.id_business = '.$this->id_business;
        if($search_key == ''){
        }else{
//            $where .= ' and (i.title like \'%'.$search_key.'%\' or i.content like \'%'.$search_key.'%\')';
            $where .= ' and i.title like \'%'.$search_key.'%\'';
        }

        if($id_info_class != 'all'){
            $where .= ' and i.id_class = \''.$id_info_class.'\'';
        }
        //获取内容列表
        $info_list = $this->info->get_info_introduction($where,$offset,$page,'i.state desc,i.weight desc');
        if(!$info_list){
            $offset = 1;
            $info_list = $this->info->get_info_introduction($where,$offset,$page,'i.state desc,i.weight desc');
        }
        //获取物品总数
        $info_count = $this->info->get_info_count($where);

        //分页代码
        $page_html = $this->get_page($info_count, $offset, 'content_list_page');

        $where_class = 'id_business = '.$this->id_business . ' and type = \'info\'';
        //物品分类
        $info_class = $this->info->get_info_class($where_class);

        foreach($info_list as $k=>$si){
            $info_list[$k]['content'] = strip_tags(html_entity_decode($info_list[$k]['content'],ENT_COMPAT,'utf-8'));
        }

        $data = array();
        $data['host'] = $this->users['host'];
        $data['info_class'] = $info_class;
        $data['info_list'] = $info_list;
        $data['page_html'] = $page_html;
        return $data;
    }


    /*
     * zxx
     * 绑定文章到活动上。即可成为参加活动才能查看的文章  （收费文章）
     */
    function bind_content(){
        $id_info = $this->input->post('id_info');//文章id
        $id_activity = $this->input->post('id_activity');//活动id

        $this->load->model('community_model','community');

        $where_c = 'id_content = ' . $id_info;
        $community_info = $this->community->get_community_info($where_c);
        if($community_info){
            $msg = '此文章已被活动“'.truncate_utf8($community_info[0]['name'],20).'”绑定了。';
            $resp = array(
                'status' => 0,
                'msg'=> $msg,
                'url' => ''
            );
            die(json_encode($resp));
        }
        $where_u = 'id_activity = ' . $id_activity;
        $data_u = array(
            'id_content'=>$id_info
        );
        $this->community->update_community($data_u,$where_u);

        $url = 'http://'.$_SERVER['HTTP_HOST'].'/biz/community/list_community/';
        $resp = array(
            'status' => 1,
            'url' => $url
        );
        die(json_encode($resp));
    }


}



/* End of file item.php */