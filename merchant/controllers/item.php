<?php
/**
 * 
 * @copyright(c) 2013-11-26
 * @author msi
 * @version Id:item.php
 */

class Item extends Admin_Controller{
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
	/**
     * zxx
	 * 添加物品
	 */
	public function add_item(){
        $data ['url_action'] = 'item/add_item';
        if($this->input->post('title'))
        {
            $this->load->library('form_validation');
            if ($this->form_validation->run('add_item') === TRUE)
            {
            	//debug('失败');
                $id_commodity_class = $this->input->post('membership');//商品分类编号
                $title = $this->input->post('title');//商品名
                $content = $this->input->post('content');//商品内容
                $price = $this->input->post('price');//价格
                $weight = $this->input->post('weight');//权重
                $state = $this->input->post('state');//状态
                $mall_sort = $this->input->post('mall_sort');//物品分类 1:物品信息 2：商城物品信息
                $image_src = $this->input->post('image_src');//图片上传后的文件名

                $title = $this->replace_html($title);
                $item_info = array(
                   'id_business' => $this->id_business,
                   'id_shop' => $this->id_shop,
                   'name' => $title,
                   'descript' => $content,
                   'price' => $price,
                   'weight' => $weight,
                   'state' => $state,
                   'id_class'=>$id_commodity_class,
                   'created'=>date('Y-m-d H:i:s', time())
                );

                if($image_src){
                    $item_info['image_url'] = $image_src;
                }
                $this->load->model('commodity_model','commodity');
                $id_commodity = $this->commodity->insert_commodity($item_info);

                $this->load->model('business_model','business');
                $img_count = 0;
                $media_count = 0;

                preg_match_all('/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/', $content, $content_img);//匹配出src里面的图片路径
                preg_match_all('/<[embed|embed].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png|\.mp4|\.mp3|\.ogg|\.wav]))[\'|\"].*?[\/]?>/', $content, $content_audio);//匹配出src里面的图片路径

                $content_imgs = array();
                if(count($content_img) >= 2){
                    $content_imgs = $content_img[1];
                    $img_count = count($content_img[1]);
                }
                $content_audios = array();
                if(count($content_audio) >= 2){
                    $content_audios = $content_audio[1];
                    $media_count = count($content_audio[1]);
                }
                if($img_count+$media_count > 10){
                    $data['is_refresh'] = 0;
                    $this->returen_status(0,"请确定附件最多10个！",$data);
                }

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
                        $business_attachment = array(
                            'id_business' => $this->id_business,
                            'id_shop' =>  $this->id_shop,
                            'object_type' => 'commodity',
                            'id_object' => $id_commodity,
                            'attachment_type' => $attachment_type,
                            'image_url' => $image_url,
                            'size' => $file_size,
                        );
                        $this->business->insert_business_attachment($business_attachment);
                    }
                }
                if($mall_sort === '0' && $state == '1'){
                    $mall_quantity = $this->input->post('mall_quantity');//商城数量
                    $mall_integral = $this->input->post('mall_integral');//商城积分
                    $mall_state = $this->input->post('mall_state');//商城状态
                    $mall_recommend = $this->input->post('mall_recommend');//商城推荐

                    $mall_info = array(
                        'id_business' => $this->id_business,
                        'id_shop' => $this->id_shop,
                        'id_commodity' => $id_commodity,
                        'quantity' => $mall_quantity,
                        'integral' => $mall_integral,
                        'state' => $mall_state,
                        'recommend' => $mall_recommend,
                        'created'=>date('Y-m-d H:i:s', time())
                    );
                    $this->load->model('mall_model','mall');
                    $this->mall->insert_mall($mall_info);

                    $data['list_url'] = BIZ_PATH.'goods/list_goods'.$this->tpl[0];
                }else{
                    $data['list_url'] = BIZ_PATH.'item/list_item'.$this->tpl[0];
                }
                $this->returen_status(1,$this->lang->line('add_data_success'),$data);
            }
            else
            {
                $this->returen_status(0,$this->form_validation->error_string(),$data);
            }
        }else{
            $this->load->model('commodity_model','commodity');
            $where_class = 'id_business = '.$this->id_business . ' and type = \'cmd\'';
            //物品分类
            $data['commodity_class'] = $this->commodity->get_commodity_class($where_class);

            $this->smarty->assign($data);
            $this->smarty->assign('type','add');
            $this->smarty->view('add_items');
        }
    }

    /**
     * zxx
     * 编辑物品
     * $id_commodity 物品id
     * $offset 页码
     * $item_sort 类型 mall:商城列表  commodity:物品列表
     */
	public function edit_item($id_commodity=0,$offset=1,$item_sort=''){
        $data ['url_action'] = 'item/edit_item/'.$id_commodity.'/'.$offset.'/'.$item_sort;
        if($this->input->post('title'))
        {
            $this->load->library('form_validation');
            if ( $this->form_validation->run('add_item') === TRUE)
            {
                $id_commodity_class = $this->input->post('membership');//商品分类编号
                $title = $this->input->post('title');//商品名
                $content = $this->input->post('content');//商品内容
                $price = $this->input->post('price');//价格
                $weight = $this->input->post('weight');//权重
                $state = $this->input->post('state');//状态
                $id_commodity = $this->input->post('id_commodity');//物品id
                $mall_sort = $this->input->post('mall_sort');//物品分类（ 1:物品信息 0：商城物品信息）

                $image_src = $this->input->post('image_src');//图片上传后的文件名

                $img_count = 0;
                $media_count = 0;

                preg_match_all('/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/', $content, $content_img);//匹配出src里面的图片路径
                preg_match_all('/<[embed|embed].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png|\.mp4|\.mp3|\.ogg|\.wav]))[\'|\"].*?[\/]?>/', $content, $content_audio);//匹配出src里面的图片路径

                $content_imgs = array();
                if(count($content_img) >= 2){
                    $content_imgs = $content_img[1];
                    $img_count = count($content_img[1]);
                }
                $content_audios = array();
                if(count($content_audio) >= 2){
                    $content_audios = $content_audio[1];
                    $media_count = count($content_audio[1]);
                }
                if($img_count+$media_count > 10){
                    $data['is_refresh'] = 0;
                    $this->returen_status(0,"请确定附件最多10个！",$data);
                }

                //将音视频路径加入到图片附件路径数组中
                foreach($content_audios as $k=>$ca){
                    $content_imgs[count($content_imgs)+$k] = $ca;
                }

                $this->load->model('business_model','business');

                $wherea = 'id_object = '.$id_commodity.' and object_type = \'commodity\' and id_business = '.$this->id_business . ' and id_shop = '.$this->id_shop;
                $info_attachment = $this->business->get_business_attachment($wherea);

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
                                if($image_url == $ia['image_url']){
                                    unset($content_imgs[$k]);
                                    unset($info_attachment[$key]);
                                }
                            }
                        }else{
                            //如果存在传来的图片信息。但没有数据库信息。则直接处理post来的数据
                            if($image_url != ''){
                                $business_attachment = array(
                                    'id_business' => $this->id_business,
                                    'id_shop' =>  $this->id_shop,
                                    'object_type' => 'commodity',
                                    'id_object' => $id_commodity,
                                    'attachment_type' => $attachment_type,
                                    'image_url' => $image_url,
                                    'size' => $file_size,
                                );
                                $this->business->insert_business_attachment($business_attachment);
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
                                    $business_attachment = array(
                                        'id_business' => $this->id_business,
                                        'id_shop' =>  $this->id_shop,
                                        'object_type' => 'commodity',
                                        'id_object' => $id_commodity,
                                        'attachment_type' => $attachment_type,
                                        'image_url' => $image_url,
                                        'size' => $file_size,
                                    );
                                    $this->business->insert_business_attachment($business_attachment);
                                }
                            }
                        }

                        //如果数据库查询出来的信息的筛选后还有图片信息则删除数据库信息
                        if(count($info_attachment)>0){
                            foreach($info_attachment as $ias){
                                $where = 'id_attachment = '.$ias['id_attachment'];
                                $this->business->delete_synopsis_image($where);

                                //删除已上传的文件附件
                                $filename = $ias['image_url'];
                                $f_num = strpos($filename,'http');
                                if($f_num === false){
                                    //获取本地文件地址
                                    $path = $this->file_url_name('commodity',$filename);
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
                            $filename = $ia['image_url'];
                            $f_num = strpos($filename,'http');
                            if($f_num === false){
                                //获取本地文件地址
                                $path = $this->file_url_name('commodity',$filename);
                                if(!file_exists($path) && !is_readable($path)){
//                                    $this->returen_status(0,$filename.'文件不在或只有只读权限~~',$data);
                                }else{
                                    unlink($path);
                                }
                                $where = 'id_attachment = '.$ia['id_attachment'];
                                $this->business->delete_synopsis_image($where);
                            }
                            //删除文件附件 end
                        }
                    }
                }

                $title = $this->replace_html($title);
                $item_info = array(
                    'id_business' => $this->id_business,
                    'id_shop' => $this->id_shop,
                    'name' => $title,
                    'descript' => $content,
                    'price' => $price,
                    'weight' => $weight,
                    'state' => $state,
                    'id_class'=>$id_commodity_class,
                    'created'=>date('Y-m-d H:i:s', time())
                );
                if($this->input->post('picNum') != 0){
                    if(trim($image_src)){
                        $item_info['image_url'] = $image_src;
                    }
                }else{
                    $item_info['image_url'] = '';
                }

                $this->load->model('commodity_model','commodity');
                $where = 'id_commodity = '.$id_commodity;
                $this->commodity->update_commodity($item_info,$where);

                $this->load->model('mall_model','mall');
//                    $id_mall = $this->input->post('id_mall');//商城id

                if($mall_sort === '0' && $state == '1'){
                    $mall_quantity = $this->input->post('mall_quantity');//商城数量
                    $mall_integral = $this->input->post('mall_integral');//商城积分
                    $mall_state = $this->input->post('mall_state')?$this->input->post('mall_state'):'up';//商城状态
                    $mall_recommend = $this->input->post('mall_recommend');//商城推荐

                    $mall_info = array(
                        'id_business' => $this->id_business,
                        'id_shop' => $this->id_shop,
                        'id_commodity' => $id_commodity,
                        'quantity' => $mall_quantity,
                        'integral' => $mall_integral,
                        'state' => $mall_state,
                        'recommend' => $mall_recommend,
                        'created'=>date('Y-m-d H:i:s', time())
                    );
                    $re = $this->mall->update_mall($mall_info,$where);
                    if(!$re){
                        $this->mall->insert_mall($mall_info);
                    }
                }else{
                    $this->mall->delete_mall($where);
                }

                $this->returen_status(1,$this->lang->line('edit_data_success'),$data);//'编辑商品信息成功'
            }
            else
            {
                $this->returen_status(0,$this->lang->line('edit_data_failed'),$data);
            }
        }else{
            $this->load->model('commodity_model','commodity');
            $where_class = 'id_business = '.$this->id_business . ' and type = \'cmd\'';
            //物品分类
            $data['commodity_class'] = $this->commodity->get_commodity_class($where_class);

            $where = 'c.id_commodity = '.$id_commodity;
            //获取物品列表
            $commodity_info = $this->commodity->get_commodity_introduction($where,0, 20, '',1);
            if($commodity_info){
                $data['commodity_info'] = $commodity_info[0];
            }

            $this->load->model('mall_model','mall');
            //获取物品在商城表的信息
            $mall_info = $this->mall->get_mall_info($where);

            if($mall_info){
                $data['mall_info'] = $mall_info[0];
            }

            $offset = $offset?$offset:1;
            if($item_sort == 'mall'){
                $data['list_url'] = BIZ_PATH.'goods/list_goods/'.$offset.$this->tpl[0];
            }else{
                $data['list_url'] = BIZ_PATH.'item/list_item/'.$offset.$this->tpl[0];
            }

            $this->smarty->assign($data);
            $this->smarty->assign('type','edit');
            $this->smarty->view('add_items');
        }
    }
	
	/**
     * zxx
	 * 删除物品
	 */
	public function del_item(){
        $id_class = $this->input->post('id_class');
        $id_commodity_class = $id_class ? $id_class : 'all';//物品分类编号
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        $id_commodity = $this->input->post('id_commodity');//物品id
        $search_key = $this->input->post('search_key') ? $this->input->post('search_key') : '';//搜索关键字

        $this->load->model('commodity_model','commodity');

        //获取物品信息
        $where_i = 'id_commodity = ' . $id_commodity;
        $commodity_info= $this->commodity->get_commodity_introduction($where_i,0,0,'c.id_commodity desc',1);
        foreach($commodity_info as $ci){
            //获取本地文件物品图片附件地址
            $path = $this->file_url_name('commodity',$ci['image_url']);
            if(!file_exists($path) && !is_readable($path)){
            }else{
                unlink($path);//删除物品图片附件
            }
        }

        //删除物品信息
        $this->commodity->delete_commodity($id_commodity);

        $this->load->model('mall_model','mall');
        $where = 'id_commodity = '.$id_commodity;
        //删除商城信息
        $this->mall->delete_mall($where);

        $this->load->model('review_model','review');
        //删除评论信息
        $where_r = 'object_type = \'commodity\' and id_object = ' . $id_commodity;
        $this->review->delete_review($where_r);

        //获取物品列表
        $data = $this->get_commodity($id_commodity_class,$offset,$search_key);
        $this->smarty->assign($data);
        $this->smarty->assign('offset',$offset);
        $this->smarty->assign('page_type','commodity');

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
     * 物品列表
     */
    public function list_item($offset=0){
        $id_class = $this->input->post('id_class');
        $id_commodity_class = $id_class ? $id_class : 'all';//商品分类编号
        if(!$offset){
            $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        }
        $search_key = $this->input->post('search_key') ? $this->input->post('search_key') : '';
        //获取物品列表
        $data = $this->get_commodity($id_commodity_class,$offset,$search_key);
        $this->smarty->assign($data);
        $this->smarty->assign('offset',$offset);
        $this->smarty->assign('page_type','commodity');

        if($id_class){
            echo $this->smarty->view('lists');
        }else{
            $this->smarty->view('list_items');
        }
    }

    /**
     * zxx
     * 加入商城
     */
    public function join_mall(){
        $id_class = $this->input->post('id_class');
        $id_commodity_class = $id_class ? $id_class :  'all';//商品分类编号

        $this->load->model('mall_model','mall');
        //将物品加入到商城里
        $id_commodity = $this->input->post('id_commodity');
        $where = 'id_commodity = '.$id_commodity;
        $mall_info = array(
            'id_business' => $this->id_business,
            'id_shop' => $this->id_shop,
            'id_commodity' => $id_commodity,
            'quantity' => 1,
            'integral' => 0,
            'state' => 'up',
            'recommend' => 1,
            'created'=>date('Y-m-d H:i:s', time())
        );
        $re = $this->mall->update_mall($mall_info,$where);
        if(!$re){
            $this->mall->insert_mall($mall_info);
        }

        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        //获取物品列表
        $data = $this->get_commodity($id_commodity_class,$offset);

        $this->smarty->assign($data);
        $this->smarty->assign('offset',$offset);
        $this->smarty->assign('page_type','commodity');
        echo $this->smarty->view('lists');
    }

    /**
     * zxx
     * 物品搜索
     */
    public function search_item(){
        $id_class = $this->input->post('id_class');
        $id_commodity_class = $id_class ? $id_class : 'all';//商品分类编号
        $search_key = $this->input->post('search_key');
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码

        //获取物品列表
        $data = $this->get_commodity($id_commodity_class,$offset,$search_key);

        $this->smarty->assign($data);
        $this->smarty->assign('offset',$offset);
        $this->smarty->assign('page_type','commodity');

        echo $this->smarty->view('lists');
    }

}
/* End of file item.php */