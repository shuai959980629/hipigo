<?php
/**
 * 
 * @copyright(c) 2014-04-21
 * @author zxx
 * @version Id:Community.php
 */


class Community extends Admin_Controller
{
    private $id_business;
    private $id_shop;
    public function __construct()
    {
        parent::__construct();
        $this->load->model('community_model','community');
        $this->id_business = $this->users['id_business'];
        $this->id_shop = $this->users['id_shop'];
        if(empty($this->users) || empty($this->session_id)){
            header('location:'.$this->url.'user/login');
            die ;
        }
    }
    // 社区活动  start
    /**
     * zxx
     * 添加社区活动
     */
    public function add_community(){
        $data_page ['url_action'] = 'community/add_community';
        if($this->input->post('title'))
        {
            $this->load->library('form_validation');
            if ($this->form_validation->run('add_community') === TRUE)
            {
                $title = $this->input->post('title');//商品名
                $content = $this->input->post('content');//活动内容
                $image_src = $this->input->post('image_src');//图片上传后的文件名
                $type = $this->input->post('type');//活动分类
                $start_time = $this->input->post('start_time');//活动开始时间
                $end_time = $this->input->post('end_time');//活动结束时间
                $price = $this->input->post('price');//价格
                $total = $this->input->post('total');//数量     == '' ? -1 : $this->input->post('total')
                $discount_price = $this->input->post('discount_price');//优惠价格

                $price_checkbox = $this->input->post('price_checkbox');//价格是否免费
                $total_checkbox = $this->input->post('total_checkbox');//数量是否充足  充足则为-1
                $discount_price_checkbox = $this->input->post('discount_price_checkbox');//优惠价格是否是免费
                if($price_checkbox){
                    $price = 0;
                }
                if($total_checkbox){
                    $total = -1;
                }
                if($discount_price_checkbox){
                    $discount_price = 0;
                }

                $this->load->model('tag_model', 'tag');

                $title = $this->replace_html($title);
                $community_info = array(
                    'id_business' => $this->id_business,
                    'id_business_source' => $this->id_business,
                    'name' => $title,
                    'content' => $content,
                    'join_price' => $price,
                    'join_count' => 0,
                    'review_count'=>0,
                    'appraise_count'=>0,
                    'total'=>$total,
                    'type'=>$type,
                    'check_num'=>0,
                    'is_spread'=>0,
                    'weight'=>0,
                    'state'=>2,//1 涂浩确认1.2版本活动默认开启
                    'created'=>date('Y-m-d H:i:s', time()),
                    'start_date'=>strtotime($start_time),
                    'end_date'=>strtotime($end_time)
                );

                if($type == 2){
                    $community_info['preferential_price'] = $discount_price;
                }

                if($image_src){
                    $community_info['posters_url'] = $image_src;
                }
                $id_commodity = $this->community->insert_community($community_info);

                $tag = $this->input->post('tag');//活动标签
                $tags = str_replace(' ', '', $tag);
                //处理标签进入标签表和活动标签表
                if($tags != '') {
                    $tagIDs = array();
                    $tags = explode(',', $tag);
                    foreach ($tags as $key => $value) {
                        if($value != '') {
                            $where_t = 'tag_name = \'' .trim($value). '\'';
                            $tag_info = $this->tag->get_tag($where_t);
                            if(!$tag_info){
                                $data = array(
                                    'tag_name' => trim($value),
                                );
                                $tagIDs[] = $this->tag->insert_tag($data);
                            }else{
                                $tagIDs[] = $tag_info[0]['id_tag'];
                            }
                        }
                    }
                    if($tagIDs){
                        $this->load->model('communitytag_model', 'communitytag');
                        //处理tag插入bn_community_activity_tag
                        $where_t = 'id_activity = ' . $id_commodity;
                        $this->communitytag->delete_activity_tag($where_t);
                        foreach($tagIDs as $ti){
                            $da_t = array(
                                'id_activity'=>$id_commodity,
                                'id_tag'=>$ti
                            );
                            $this->communitytag->insert_activity_tag($da_t);
                        }
                    }
                }

                //插入指定商家验证信息  start
                $this->load->model('ticket_model','ticket');
                $businessIDs = $this->input->post('id_business');//商家
                $shopIDs = $this->input->post('id_shop');//门店

                $data_bs = array();
                if($businessIDs){
                    //合并数组
                    foreach($businessIDs as $kb=>$vb){
                        $data_bs[$kb][0] = $vb;
                        $data_bs[$kb][1] = $shopIDs[$kb];
                    }

                    $IDs = unique_arr($data_bs);//二维数组去重
                    $IDs = array_merge($IDs);//索引重置
                    $val=array();
                    $num=array();
                    foreach ($IDs as $key => $row){
                        $val[$key] = $row[0];
                        $num[$key] = $row[1];
                    }
                    array_multisort($val, SORT_ASC, $IDs);//根据条件排序二维数组

//                    $b=array();
//                    foreach($IDs as $e){
//                        $id=intval($e[0]);
//                        $b[$id]=isset($b[$id])?($e[1]>$b[$id][1])? $e:$b[$id] : $e;
//                    }
//                    $b=array_values($b);//根据条件去重复

                    foreach($IDs as $v){
                        $verify_info = array(
                            'object_type' => 'community',
                            'id_object' => $id_commodity,
                            'id_business' => $v[0],
                            'id_shop' => $v[1]
                        );
                        $id_biz = $this->ticket->insert_ticket_verify($verify_info);
                    }
                }
                //插入指定商家验证信息  end

                $this->load->model('business_model','business');
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
//                if($img_count+$media_count > 10){
//                    $data_page['is_refresh'] = 0;
//                    $this->returen_status(0,"请确定附件最多10个！",$data_page);
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
                        $business_attachment = array(
                            'id_business' => $this->id_business,
                            'id_shop' =>  $this->id_shop,
                            'object_type' => 'community',
                            'id_object' => $id_commodity,
                            'attachment_type' => $attachment_type,
                            'image_url' => $image_url,
                            'size' => $file_size,
                        );
                        $this->business->insert_business_attachment($business_attachment);
                    }
                }
                $data_page['url'] = $this->url.'community/list_community'.$this->tpl[0];
                $data_page['is_refresh'] = 2;
                $this->returen_status(1,$this->lang->line('add_data_success'),$data_page);
            }
            else
            {
                $this->returen_status(0,$this->form_validation->error_string(),$data_page);
            }
        }else{
//            $classfy = array('普通活动','世界杯活动');
//            $this->smarty->assign('classfy',$classfy);

            $data_page['community_info']['start_date'] = time();
            $data_page['community_info']['end_date'] = strtotime("+1 day");
            $data_page['community_info']['join_price'] = null;
            $data_page['community_info']['preferential_price'] = null;

            $this->smarty->assign($data_page);
            $this->smarty->assign('type','add');
            $this->smarty->view('add_community');
        }
    }

    /**
     * zxx
     * 编辑社区活动
     * $id_activity 社区活动id
     * $offset 页码
     */
    public function edit_community($id_activity,$offset){
        $data ['url_action'] = 'community/edit_community/'.$id_activity.'/'.$offset;

        $this->load->model('ticket_model','ticket');
        $this->load->model('business_model','business');
        $this->load->model('tag_model', 'tag');
        if($this->input->post('title'))
        {
            $this->load->library('form_validation');
            if ( $this->form_validation->run('add_community') === TRUE)
            {
                $aid = $this->input->post('id_activity');//互动活动id

                $title = $this->input->post('title');//商品名
                $content = $this->input->post('content');//商品内容
                $type = $this->input->post('type');//活动分类
                $start_time = $this->input->post('start_time');//活动开始时间
                $end_time = $this->input->post('end_time');//活动结束时间

                $price = $this->input->post('price');//价格
                $total = $this->input->post('total');//数量     == '' ? -1 : $this->input->post('total')
                $discount_price = $this->input->post('discount_price');//优惠价格

                $price_checkbox = $this->input->post('price_checkbox');//价格是否免费
                $total_checkbox = $this->input->post('total_checkbox');//数量是否充足  充足则为-1
                $discount_price_checkbox = $this->input->post('discount_price_checkbox');//优惠价格是否是免费
                if($price_checkbox){
                    $price = 0;
                }
                if($total_checkbox){
                    $total = -1;
                }
                if($discount_price_checkbox){
                    $discount_price = 0;
                }

                $image_src = $this->input->post('image_src');//图片上传后的文件名

//                $img_count = 0;
//                $media_count = 0;
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
//                if($img_count+$media_count > 10){
//                    $data['is_refresh'] = 0;
//                    $this->returen_status(0,"请确定附件最多10个！",$data);
//                }

                //将音视频路径加入到图片附件路径数组中
                foreach($content_audios as $k=>$ca){
                    $content_imgs[count($content_imgs)+$k] = $ca;
                }

                $wherea = 'id_object = '.$aid.' and object_type = \'community\' and id_business = '.$this->id_business . ' and id_shop = '.$this->id_shop;
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
                                    'object_type' => 'community',
                                    'id_object' => $aid,
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
                                        'object_type' => 'community',
                                        'id_object' => $aid,
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
                                    $path = $this->file_url_name('community',$filename);
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
                                $path = $this->file_url_name('community',$filename);
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

                //记录修改活动日志 start
                $where = 'id_activity = '.$aid;
                //获取物品列表
                $community_info = $this->community->get_community_info($where);
                if($community_info){
                    foreach($community_info as $k_ci=>$v_ci){
                        $community_log = array(
                            'id_activity'=>$v_ci['id_activity'],
                            'id_business' => $v_ci['id_business'],
                            'name' => $v_ci['name'],
                            'content' => $v_ci['content'],
                            'total' => $v_ci['total'],
                            'check_num' => $v_ci['check_num'],
                            'posters_url' => $v_ci['posters_url'],
                            'notice' => $v_ci['notice']?$v_ci['notice']:'',
                            'join_price' => $v_ci['join_price'],
                            'is_spread' => $v_ci['is_spread'],
                            'join_count' => $v_ci['join_count'],
                            'review_count' => $v_ci['review_count'],
                            'appraise_count' => $v_ci['appraise_count'],
                            'created' => $v_ci['created'],
                            'weight' => $v_ci['weight'],
                            'state' => $v_ci['state'],
                            'sysuser_name' => $this->users['biz_name'],
                            'date' => date('Y-m-d H:i:s', time())
                        );
                        $this->community->insert_community_log($community_log);
                    }
                }
                //记录修改活动日志  end

                $title = $this->replace_html($title);
                $community_info = array(
                    'id_business' => $this->id_business,
                    'name' => $title,
                    'content' => $content,
                    'total'=>$total,
                    'type'=>$type,
                    'join_price' => $price,
                    'preferential_price' => $discount_price,
                    'start_date'=>strtotime($start_time),
                    'end_date'=>strtotime($end_time)
                );

                if($this->input->post('picNum') != 0){
                    if(trim($image_src)){
                        $community_info['posters_url'] = $image_src;
                    }
                }else{
                    $community_info['posters_url'] = '';
                }
                $id_commodity = $this->community->update_community($community_info,$where);

                //插入指定商家验证信息  start
                $businessIDs = $this->input->post('id_business');//商家
                $shopIDs = $this->input->post('id_shop');//门店
                $delete_business_id = $this->input->post('delete_business_id');//商家

                //删除某些商家验证信息
                if($delete_business_id){
                    $del_ids = explode(',',$delete_business_id);
                    foreach($del_ids as $kd=>$vd){
                        if($vd){
                            $where_d = 'id_ver_biz = ' . $vd;
                            $this->ticket->delete_ticket_verify($where_d);
                        }
                    }
                }

                if($businessIDs){
                    //合并数组
                    foreach($businessIDs as $kb=>$vb){
                        $data_bs[$kb][0] = $vb;
                        $data_bs[$kb][1] = $shopIDs[$kb];
                    }

                    $IDs = unique_arr($data_bs);//二维数组去重
                    $IDs = array_merge($IDs);//索引重置

                    $val=array();
                    $num=array();
                    foreach ($IDs as $key => $row){
                        $val[$key] = $row[0];
                        $num[$key] = $row[1];
                    }
                    array_multisort($val, SORT_ASC, $IDs);//根据条件排序二维数组

                    foreach($IDs as $k=>$v){
                        $verify_info = array(
                            'object_type' => 'community',
                            'id_object' => $aid,
                            'id_business' => $v[0],
                            'id_shop' => $v[1]
                        );
                        $id_biz = $this->ticket->insert_ticket_verify($verify_info);
                    }
                }
                //插入指定商家验证信息  end

                $tag = $this->input->post('tag');//活动标签
                $tags = str_replace(' ', '', $tag);
                //处理标签进入标签表和活动标签表
                if($tags != '') {
                    $tagIDs = array();
                    $tags = explode(',', $tag);
                    foreach ($tags as $key => $value) {
                        if($value != '') {
                            $where_t = 'tag_name = \'' .trim($value). '\'';
                            $tag_info = $this->tag->get_tag($where_t);
                            if(!$tag_info){
                                $data = array(
                                    'tag_name' => trim($value),
                                );
                                $tagIDs[] = $this->tag->insert_tag($data);
                            }else{
                                $tagIDs[] = $tag_info[0]['id_tag'];
                            }
                        }
                    }

                    if($tagIDs){
                        $this->load->model('communitytag_model', 'communitytag');
                        //处理tag插入bn_community_activity_tag
                        $where_t = 'id_activity = ' . $aid;
                        $this->communitytag->delete_activity_tag($where_t);
                        foreach($tagIDs as $ti){
                            $da_t = array(
                                'id_activity'=>$aid,
                                'id_tag'=>$ti
                            );
                            $this->communitytag->insert_activity_tag($da_t);
                        }
                    }
                }

                $data['url'] = $this->url.'community/list_community/'.$offset;
                $data['is_refresh'] = 2;
                $this->returen_status(1,$this->lang->line('edit_data_success'),$data);//'编辑商品信息成功'
            }
            else
            {
                $this->returen_status(0,$this->lang->line('edit_data_failed'),$data);
            }
        }else{
            $where = 'ca.id_activity = '.$id_activity;
            //获取活动信息 (包括标签信息)
            $community_info = $this->community->get_community_info($where,0, 20,'',3);
            if($community_info){
                $img_info = explode('.',$community_info[0]['posters_url']);
                $community_info[0]['posters_url'] = $img_info[0].'-big.'.$img_info[1];

                if(empty($community_info[0]['start_date'])){
                    $community_info[0]['start_date'] = time();
                    $community_info[0]['end_date'] = strtotime("+1 day");
                }
                $data['community_info'] = $community_info[0];
            }

            $data['list_url'] = BIZ_PATH.'community/list_community/'.$offset;

            $where_v = 'object_type = \'community\' and id_object = '.$id_activity;
            $verify_info = $this->ticket->get_ticket_verify_business($where_v);

            $this->load->model('shop_model','shop');
            foreach($verify_info as $k=>$v){
                if($v['id_shop'] == 0){
                    $where_b = 'id_business = ' . $v['id_business'];
                    $business_info = $this->business->get_business_phone($where_b);
                    if($business_info){
                        $verify_info[$k]['name'] = $business_info[0]['name'];
                    }
                }else{
                    $where_s = 'id_shop = '.$v['id_shop'].' and id_business = ' . $v['id_business'];
                    $shop_info = $this->shop->get_shop_introduction($where_s);
                    if($shop_info){
                        $verify_info[$k]['name'] = $shop_info[0]['name'];
                    }
                }
            }
            $data['verify_info'] = $verify_info;
//            $classfy = array('普通活动','世界杯活动');
//            $this->smarty->assign('classfy',$classfy);
            $this->smarty->assign($data);
            $this->smarty->assign('type','edit');
            $this->smarty->view('add_community');
        }
    }

    /*
     * zxx
     * 查看活动详情
     */
    function show_info(){
        $aid = $this->input->post('aid');
        $where = 'id_activity = '.$aid;
        //获取物品列表
        $data = array();
        $community_info = $this->community->get_community_info($where);
        if($community_info){
            $img_info = explode('.',$community_info[0]['posters_url']);
            $community_info[0]['posters_url'] = $img_info[0].'-big.'.$img_info[1];
            $data['community_info'] = $community_info[0];
        }
        $resp = array(
            'status' => 1,
            'msg' => '',
            'data' => $data
        );
        die(json_encode($resp));
    }


    /*
     * zxx
     * 搜索商家信息
     */
    function search_business(){
        $search_key = $this->input->post('search_key') ? $this->input->post('search_key') : '';//搜索关键字

        $data = array();
        $this->load->model('shop_model','shop');
        $where = 'name = \'' .$search_key. '\'';
        $result = $this->shop->get_shop_introduction($where);
        if($result){
//            $result[0]['type'] = 'shop';
            array_push($data,$result[0]);
        }
        $this->load->model('business_model','business');
        $where1 = 'name = \'' .$search_key. '\'';
        $result1 = $this->business->get_business_phone($where1);
        if($result1){
            $result1[0]['id_shop'] = 0;
//            $result1[0]['type'] = 'business';
            array_push($data,$result1[0]);
        }

        $resp = array(
            'status' => 1,
            'msg' => '',
            'data' => $data
        );
        die(json_encode($resp));
    }



    /**
     * zxx
     * 删除社区活动
     */
    public function del_community(){
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        $id_activity = $this->input->post('id_activity');//活动id
        $search_key = $this->input->post('search_key') ? $this->input->post('search_key') : '';//搜索关键字

        $page_type = $this->input->post('page_type');//one ：单个删除  more：批量删除

        if($page_type == 'one'){
            //获取互动活动信息
            $where_i = 'id_activity = ' . $id_activity;
//            $community_info= $this->community->get_community_info($where_i);
//            foreach($community_info as $ci){
//                //获取本地文件图片附件地址
//                $path = $this->file_url_name('community',$ci['posters_url']);
//                if(!file_exists($path) && !is_readable($path)){
//                }else{
//                    unlink($path);//删除图片附件
//                }
//            }
            //删除互动活动信息  逻辑删除 state=-1
            $data['state'] = -1;
            $this->community->update_community($data,$where_i);
        }else{
            foreach($id_activity as $ia){
//                //获取互动活动信息
                $where_i = 'id_activity = ' . $ia;
//                $community_info= $this->community->get_community_info($where_i);
//                foreach($community_info as $ci){
//                    //获取本地文件图片附件地址
//                    $path = $this->file_url_name('community',$ci['posters_url']);
//                    if(!file_exists($path) && !is_readable($path)){
//                    }else{
//                        unlink($path);//删除图片附件
//                    }
//                }
                //删除社区活动信息  逻辑删除 state=-1
                $data['state'] = -1;
                $this->community->update_community($data,$where_i);
            }
        }

        //获取社区活动列表
        $data = $this->get_community($offset,$search_key);
        $this->smarty->assign($data);
        $this->smarty->assign('offset',$offset);
        $this->smarty->assign('page_type','community');

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
     * 社区列表
     */
    public function list_community($offset=0){
        $where = array(
            'id_business'=>$this->id_business,
            'id_shop'=>$this->id_shop,
            'valid_end >='=>date('Y-m-d H:i:s', time())
        );
        $elist = $this->community->get_business_eticket($where);
        $page_type = $this->input->post('page_type');
        if(!$offset){
            $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        }
        $search_key = $this->input->post('search_key') ? $this->input->post('search_key') : '';
        //获取物品列表
        $data = $this->get_community($offset,$search_key);
        $this->smarty->assign($data);
        $this->smarty->assign('elist',$elist);
        $this->smarty->assign('offset',$offset);
        $this->smarty->assign('page_type','community');

        if($page_type){
            echo $this->smarty->view('lists');
        }else{
            $this->smarty->view('list_community');
        }
    }

    /**
     * zxx
     * 社区活动搜索
     */
    public function search_community(){
        $search_key = $this->input->post('search_key');
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码

        //获取互动活动列表
        $data = $this->get_community($offset,$search_key);

        $this->smarty->assign($data);
        $this->smarty->assign('offset',$offset);
        $this->smarty->assign('page_type','community');

        echo $this->smarty->view('lists');
    }

    /*
     * zxx
     * 更新社区活动状态
     * */
    public function update_state(){
        $id_activity = $this->input->post('id_activity');
        $state = $this->input->post('state');

        $where = 'id_activity = ' . $id_activity;
        $data['state'] = $state;
        $community = $this->community->update_community($data,$where);
        $resp = array(
            'status' => 1,
            'msg' => '',
            'data' => '已关闭！'
        );
        die(json_encode($resp));
    }

    /*
     * 获取社区活动列表
     * zxx
     * $offset ：页码
     * $page ：条数
     * **/
    function get_community($offset,$search_key='',$page=0){
        if(!$this->id_business){
            //没有用户登录信息时调用
            $this->ini_user();
//            $url = 'http://'.$_SERVER['HTTP_HOST'].BIZ_PATH."user/login";
//            header('location:'.$url);
        }
        //分页数量
        $page = $page ? $page : ($this->config->item('page'));
        $where = 'id_business = '.$this->id_business . ' and state >= 0';
        if($search_key == ''){
        }else{
            $where .= ' and name like \'%'.$search_key.'%\'';
        }

        //获取社区活动列表
        $community_list = $this->community->get_community_info($where,$offset,$page,'id_activity desc');
        if(!$community_list){
            $offset = 1;
            $community_list = $this->community->get_community_info($where,$offset,$page,'id_activity desc');
        }

        $this->load->model('business_model','business');
        $where_b = 'id_business = '.$this->id_business;
        $business_info = $this->business->get_business_phone($where_b);

        $this->load->model('review_model','review');
        $this->load->model('ticket_model','ticket');
        foreach($community_list as $k=>$cl){
            $community_list[$k]['share_link'] = 'http://'.$business_info[0]['sld'].'/wapi/'.$this->id_business.'/'.$this->id_shop.'/community/detail?aid=' . $cl['id_activity'];
            if($cl['posters_url']){
                $img_info = explode('.',$cl['posters_url']);
                $community_list[$k]['posters_url'] = $img_info[0].'-small.'.$img_info[1];
            }
//            //查询评论数
//            $where_r = 'r.object_type = \'community\' and r.id_object = ' . $cl['id_activity'] . ' and r.id_business = ' . $this->users['id_business'];
//            $review_count = $this->review->get_review_count($where_r);
//            $community_list[$k]['review_count'] = $review_count;
//
            //查询验证数  条件是已验证(使用)多少验证码
            $where_t = 'ti.state = 2 and ti.object_type = \'community\' and ti.id_object = ' . $cl['id_activity'];// . ' and ti.id_business = ' . $this->users['id_business'];
            $ticket_count = $this->ticket->get_ticket_item_count($where_t,2);
            $community_list[$k]['ticket_count'] = $ticket_count;

            //查询参加活动的用户人数
            $where_j = 'id_activity = ' . $cl['id_activity'];
            $activity_counts = $this->community->get_community_join_count($where_j);
            $community_list[$k]['person_count'] = $activity_counts?count($activity_counts):0;
        }
        //获取互动活动总数
        $community_count = $this->community->get_community_count($where);
        //分页代码
        $page_html = $this->get_page($community_count, $offset, 'community_list_page');

        $data = array();
        $data['community_list'] = $community_list;
        $data['page_html'] = $page_html;
        return $data;
    }


    /*
     * zxx
     * 发布公告
     */
    function publish_notice(){
        $id_activity = $this->input->post('id_activity');
        $notice = $this->input->post('notice');

        $where = 'id_activity = ' . $id_activity;
        $data = array(
            'notice'=>$notice
        );
        $this->community->update_community($data,$where);
        $resp = array(
            'status' => 1,
            'msg' => '发布公告成功',
            'data' => ''
        );
        die(json_encode($resp));
    }


    // 社区活动  end

    // 社区活动成员  start
    /*
     * zxx
     * 查看成员列表
     */
    function list_member($id_activity){
        if(!$id_activity){
            $id_activity = $this->input->post('id_activity');
        }
        $page_type = $this->input->post('page_type');
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        $search_key = $this->input->post('search_keys') != '' ? $this->input->post('search_keys') : '';

        $data = $this->get_member($id_activity,$offset,$search_key);
        $this->smarty->assign($data);
        $this->smarty->assign('page_type','member');

        if($page_type){
            echo $this->smarty->view('lists');
        }else{
            $this->smarty->view('list_member');
        }
    }



    function get_member($id_activity,$offset,$search_key=''){
        $this->load->model('lock_model','lock');
        $where = 'maj.id_activity = ' . $id_activity ;

        if($search_key != ''){
            $where .= ' and (maj.cellphone like \'%' .$search_key.'%\' or hu.nick_name like \'%' .urlencode($search_key).'%\')';
        }
        //获取互动活动列表
        $member_list = $this->community->get_community_member($where,$offset,$this->config->item('page'));
        if(!$member_list){
            $offset = 1;
            $member_list = $this->community->get_community_member($where,$offset,$this->config->item('page'));
        }

        $this->load->model('review_model','review');
        $this->load->model('ticket_model','ticket');
        $ms = $member_list;
        foreach($ms as $k=>$v){
            $member_list[$k]['head_image_url'] = get_img_url(str_replace('.jpg', '-middle.jpg',
                $v['head_image_url']), 'head', false, 'default-header.png');

            $oid = $v['id_open']?$v['id_open']:'';
            $uid = $v['id_user']?$v['id_user']:0;
            $cp = $v['cellphone']?$v['cellphone']:0;
            $where_r = 'r.object_type = \'community\' and r.state > 0 and r.id_object = ' . $v['id_activity'] . ' and r.id_open = \'' . $oid . '\'';
            $review_count = $this->review->get_review_count($where_r);
            $member_list[$k]['review_count'] = $review_count;

            if($uid === 0){//准用户参加活动的
                $where_ti = 'ti.object_type = \'community\' and ti.state = 2 and ti.id_object = ' . $v['id_activity'] . ' and ti.id_open = \'' . $cp . '\'';
                $where_ti_t = 'ti.object_type = \'community\' and ti.state > 0 and ti.id_object = ' . $v['id_activity'] . ' and ti.id_open = \'' . $cp . '\'';
            }else{//注册用户和微信用户参加活动的
                $where_ti = 'ti.object_type = \'community\' and ti.state = 2 and ti.id_object = ' . $v['id_activity'] . ' and ti.id_customer = '.$uid;
                $where_ti_t = 'ti.object_type = \'community\' and ti.state > 0 and ti.id_object = ' . $v['id_activity'] . ' and ti.id_customer = '.$uid;
            }
            //查询已验证的电子券条数
            $ticket_verify_count = $this->ticket->get_ticket_item_count($where_ti,2);
            $member_list[$k]['ticket_verify_count'] = $ticket_verify_count;

            //查询该用户有多少条电子券验证码
            $ticket_code_count = $this->ticket->get_ticket_item_count($where_ti_t,2);
            $member_list[$k]['ticket_code_count'] = $ticket_code_count;

            $where_l = 'object_type = \'getEticket\' and id_object = ' . $id_activity . ' and lock_phone = \''.$v['cellphone'].'\'';
            $result = $this->lock->get_lock($where_l);
            $lock_count = 0;
            if($result){
                foreach($result as $val){
                    $one = strtotime($val['created']);
                    $tow = strtotime(date('y-m-d h:i:s'));
                    $cle = $tow - $one; //得出时间戳差值

                    $m = ceil($cle/60);//得出一共多少分钟
                    if($m < 10*60){
                        $lock_count += 1;
                    }
                }
            }
            $member_list[$k]['lock'] = 0;
            if($lock_count >= 3){
                $member_list[$k]['lock'] = 1;
            }
        }
        //获取互动活动总数
        $community_counts = $this->community->get_community_member_count($where);
        $community_count = 0;
        if($community_counts){
            $community_count = count($community_counts);
        }
        //分页代码
        $page_html = $this->get_page($community_count, $offset, 'member_list_page');

        $data = array();
        $data['member_list'] = $member_list;
        $data['page_html'] = $page_html;
        $data['offset'] = $offset;
        $data['id_activity'] = $id_activity;
        return $data;
    }

    /*
     * zxx
     * 解除锁定
     */
    function del_lock(){
        $id_activity = $this->input->post('id_activity');
        $cellphone = $this->input->post('cellphone');

        $this->load->model('lock_model','lock');
        $where_l = 'object_type = \'getEticket\' and id_object = ' . $id_activity . ' and lock_phone = \''.$cellphone.'\'';
        $r = $this->lock->delete_lock($where_l);
        if($r){
            $resp = array(
                'status' => 1,
                'msg' => '解锁成功！',
                'data' => ''
            );
        }else{
            $resp = array(
                'status' => 0,
                'msg' => '解锁失败！',
                'data' => ''
            );
        }
        die(json_encode($resp));
    }


    /*
     * zxx
     * 取消和设置管理员
     */
    function update_role(){
        $id_join = $this->input->post('id_join');
        $role = $this->input->post('role');

        $where = 'id_join = ' . $id_join ;
        $data['role'] = $role;
        //设置取消管理员
        $this->community->update_community_join($data,$where);

        $resp = array(
            'status' => 1,
            'msg' => '',
            'data' => '更新成功了！'
        );
        die(json_encode($resp));
    }

    /*
     * zxx
     * 删除社区活动成员信息
     */
    function del_member(){
        $id_activity = $this->input->post('id_activity');
//        $id_join = $this->input->post('id_join');
        $id_user = $this->input->post('id_user');
        $cellphone = $this->input->post('cellphone');

        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        $page_type = $this->input->post('page_type');//one ：单个删除  more：批量删除

        $is_manage = $this->input->post('is_manage');//manage ：成员管理页  ‘’：互动活动成员页面

        $this->load->model('ticket_model','ticket');
        $this->db->trans_begin();

        //在存手机号码之前存的微信openid,不用兼容
        //只考虑手机号，就是准会员就行  候总确认
        if($page_type == 'one'){
            $where = 'id_activity = ' . $id_activity;
            if($id_user){
                $where_u = 'id_user = ' . $id_user .' and id_activity = ' . $id_activity;
                $where_t = 'object_type = \'community\' and id_customer = '.$id_user.' and id_object = '. $id_activity;
            }else{
                $where_u = 'cellphone = ' . $cellphone .' and id_activity = ' . $id_activity;
                $where_t = 'object_type = \'community\' and id_open = \''.$cellphone.'\' and id_object = '. $id_activity;
            }
            //删除成员
            $this->community->delete_community_join($where_u);
            //获取该成员对这个活动存在几个报名码
            $count = $this->ticket->get_ticket_item_count($where_t,2);
            //删除成员参加活动生成的报名码
            $this->ticket->delete_ticket_item($where_t);
            $activity_info = $this->community->get_community_info($where);
            if($activity_info){
                $j_c = 0;
                if(intval($activity_info[0]['join_count']) > $count){
                    $j_c = intval($activity_info[0]['join_count']) - $count;
                }
                $update_a = array(
                    'join_count' => $j_c
                );
                //减少成员数量
                $this->community->update_community($update_a,$where);
            }
        }else{
            $count_array = 0;
            foreach($id_user as $k=>$uid){
                $where = 'id_activity = ' . $id_activity;
                if($uid){
                    $where_u = 'id_user = ' . $uid .' and id_activity = ' . $id_activity;
                    $where_t = 'object_type = \'community\' and id_customer = '.$uid.' and id_object = '. $id_activity;
                }else{
                    $where_u = 'cellphone = ' . $cellphone[$k] .' and id_activity = ' . $id_activity;
                    $where_t = 'object_type = \'community\' and id_open = \''.$cellphone[$k].'\' and id_object = '. $id_activity;
                }
                //删除成员
                $this->community->delete_community_join($where_u);
                //获取该成员对这个活动存在几个报名码
                $count = $this->ticket->get_ticket_item_count($where_t,2);
                $count_array = $count_array + $count;
                //删除成员参加活动生成的报名码
                $this->ticket->delete_ticket_item($where_t);
            }

            $activity_info = $this->community->get_community_info($where);
            if($activity_info){
                $j_c = 0;
                if(intval($activity_info[0]['join_count']) > $count_array){
                    $j_c = intval($activity_info[0]['join_count']) - $count_array;
                }
                $update_a = array(
                    'join_count' => $j_c
                );
                //减少成员数量
                $this->community->update_community($update_a,$where);
            }
        }

        if ($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            $resp = array(
                'status' => 0,
                'msg' => '删除失败！',
                'data' => ''
            );
            die(json_encode($resp));
        }else{
            $this->db->trans_commit();
        }

        //删成员不删该成员评论  涂浩确认
        //获取互动活动列表
        if($is_manage == 'manage'){
            $data = $this->get_member_manage($offset);
            $this->smarty->assign('page_type','member_manage');
        }else{
            $data = $this->get_member($id_activity,$offset);
            $this->smarty->assign('page_type','member');
        }
        $this->smarty->assign($data);
        $this->smarty->assign('offset',$offset);

        $data = $this->smarty->fetch('lists.html');
        $resp = array(
            'status' => 1,
            'msg' => '',
            'data' => $data
        );
        die(json_encode($resp));
    }

    // 社区活动成员  end

    // 社区活动讨论  start
    /*
     * zxx
     * 查看评论列表
     */
    function list_discuss($id_activity){
        if(!$id_activity){
            $id_activity = $this->input->post('id_activity');
        }
        $page_type = $this->input->post('page_type');
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码

        $page = $this->config->item('page');
        $data = $this->get_discuss($id_activity,$offset,$page);
        $this->smarty->assign($data);
        $this->smarty->assign('page_type','member_discuss');

        if($page_type){
            echo $this->smarty->view('lists');
        }else{
            $this->smarty->view('list_discuss');
        }
    }

    function get_discuss($id_activity,$offset,$page=20){
        $this->load->model('review_model','review');
        $this->load->model('business_model','business');

        $where = 'r.object_type = \'community\' and r.state > 0 and r.id_parent = 0';
        if($id_activity){
            $where .= ' and r.id_object = ' . $id_activity ;
        }
        //获取讨论列表
        $review_list = $this->review->get_review_info($where,$offset,$page,'r.id_review desc');
        if(!$review_list){
            $offset = 1;
            $review_list = $this->review->get_review_info($where,$offset,$page,'r.id_review desc');
        }
        $ms = $review_list;
        foreach($ms as $k=>$v){
            $review_list[$k]['head_image_url'] = get_img_url(str_replace('.jpg', '-middle.jpg',
                $v['head_image_url']), 'head', false, 'default-header.png');

            $where_att = 'object_type = \'review\' and id_object = '.$v['id_review'];
            $att_info = $this->business->get_business_attachment($where_att);
            $review_list[$k]['is_img'] = 0;//是否有图片附件
            if($att_info){
                $review_list[$k]['is_img'] = 1;
            }

            $where_r = 'r.object_type = \'community\' and r.state > 0 and r.id_object = ' . $v['id_object'] . ' and r.id_parent = ' . $v['id_review'];
            $review_counts = $this->review->get_review_count($where_r);
            $review_list[$k]['reply_count'] = $review_counts;
        }
        //获取讨论总数
        $review_count = $this->review->get_review_count($where);
        //分页代码
        $page_html = $this->get_page($review_count, $offset, 'discuss_list_page','method',$page);

        $data = array();
        $data['review_list'] = $review_list;
        $data['page_html'] = $page_html;
        $data['offset'] = $offset;
        $data['id_activity'] = $id_activity;
        return $data;
    }

    /*
     * zxx
     * 删除讨论信息
     */
    function del_discuss(){
        $id_activity = $this->input->post('id_activity');
        $id_review = $this->input->post('id_review');
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        $page_type = $this->input->post('page_type');//one ：单个删除  more：批量删除
        $is_manage = $this->input->post('is_manage');//manage ：是讨论管理页面  ''：是讨论页面

        $this->load->model('review_model','review');
        if($page_type == 'one'){
            $where = 'id_review = ' . $id_review ;
            //删除评论均为修改状态
            $this->review->delete_review($where);

            $where_r = 'id_activity = ' . $id_activity;
            $community_info = $this->community->get_community_info($where_r);

            //减少评论数量
            if($community_info){
                $r_c = 0;
                if(intval($community_info[0]['review_count']) > 0){
                    $r_c = $community_info[0]['review_count'] - 1;
                }
                $data = array('review_count'=>$r_c);
                $this->community->update_community($data,$where_r);
            }
            //减少评论数量
//            $this->community->lower_review_count($id_activity,1);
        }else{
            foreach($id_review as $ia){
                $where = 'id_review = ' . $ia ;
                //删除评论均为修改状态
                $this->review->delete_review($where);
            }

            $where_r = 'id_activity = ' . $id_activity;
            $community_info = $this->community->get_community_info($where_r);

            //减少评论数量
            if($community_info){
                $r_c = 0;
                if(intval($community_info[0]['review_count']) > count($id_review)){
                    $r_c = intval($community_info[0]['review_count']) - count($id_review);
                }
                $data = array('review_count'=>$r_c);
                $this->community->update_community($data,$where_r);
            }

            //减少评论数量
//            $this->community->lower_review_count($id_activity,count($id_review));
        }
        //获取讨论列表
        $page =  $this->config->item('page');
        if($is_manage == 'manage'){
            $data = $this->get_discuss_manage($offset,$page);
        }else{
            $data = $this->get_discuss($id_activity,$offset,$page);
        }
        $this->smarty->assign($data);
        $this->smarty->assign('page_type','member_discuss');

        $data = $this->smarty->fetch('lists.html');
        $resp = array(
            'status' => 1,
            'msg' => '',
            'data' => $data
        );
        die(json_encode($resp));
    }
    // 社区活动讨论  end


    // 社区活动讨论回复  start
    /*
     * zxx
     * 查看 和 回复讨论
     */
    function reply_details($id_review,$id_activity){
        if(!$id_review){
            $id_review = $this->input->post('id_review');
        }
        if(!$id_activity){
            $id_activity = $this->input->post('id_activity');
        }
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        $page_type = $this->input->post('page_type');

        $this->load->model('review_model','review');
        $this->load->model('business_model','business');
        $where = 'r.id_review = '. $id_review;
        //获取讨论信息
        $discuss_info = $this->review->get_review_info($where);
        $where_att = 'object_type = \'review\' and id_object = ' . $id_review;
        $att_info = $this->business->get_business_attachment($where_att);

        $page = $this->config->item('page');
        $data = $this->get_reply($id_review,$offset,$page);
        $this->smarty->assign($data);
        $this->smarty->assign('id_activity',$id_activity);
        $this->smarty->assign('discuss_info',$discuss_info[0]);
        $this->smarty->assign('att_info',$att_info);
        $this->smarty->assign('page_type','review_reply');

        if($page_type){
            echo $this->smarty->view('lists');
        }else{
            $this->smarty->view('reply_details');
        }
    }

    function get_reply($id_review,$offset,$page=20){
        $this->load->model('review_model','review');
        $this->load->model('business_model','business');
        $where = 'r.state > 0 and r.id_parent = '. $id_review;

        //获取讨论回复列表
        $reply_list = $this->review->get_review_info($where,$offset,$page,'r.id_review desc');
        if(!$reply_list){
            $offset = 1;
            $reply_list = $this->review->get_review_info($where,$offset,$page,'r.id_review desc');
        }
//        foreach($reply_list as $k=>$rl){
//            $reply_list[$k]['head_image_url'] = '';
//            if(!$rl['id_open']){
//                $reply_list[$k]['head_image_url'] = $this->users['logo'];
//            }else{
//                $where_sub = 'id_open = \'' .$rl['id_open']. '\'';
//                $bus_info = $this->business->get_business_sub($where_sub);
//                if($bus_info){
//                    if($bus_info[0]['head_image_url']){
//                        $reply_list[$k]['head_image_url'] = $bus_info[0]['head_image_url'];
//                    }else{
//                        $reply_list[$k]['head_image_url'] = $this->url.'media/image/ico_user.png';
//                    }
//                }else{
//                    $review_list[$k]['head_image_url'] = $this->url.'media/image/ico_user.png';
//                }
//            }
//        }
        //获取讨论回复总数
        $review_count = $this->review->get_review_count($where);
        //分页代码
        $page_html = $this->get_page($review_count, $offset, 'reply_list_page','method',$page);

        $data = array();
        $data['reply_list'] = $reply_list;
        $data['page_html'] = $page_html;
        $data['offset'] = $offset;
        $data['id_review'] = $id_review;
        return $data;
    }

    /*
     * zxx
     * 发送讨论回复
     */
    function submit_reply(){
        $id_review = $this->input->post('id_review');
        $id_activity = $this->input->post('id_activity');
        $reply = $this->input->post('reply');
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码

        $this->load->model('review_model','review');
        $data_array = array(
            'id_business'=>$this->id_business,
            'id_shop'=>$this->id_shop,
            'id_object'=>$id_activity,
            'object_type'=>'community',
            'id_open'=>0,
//            'id_customer'=>0,
//            'phone_number'=>'',
            'name'=>$this->users['biz_name'],
            'content'=>$reply,
//            'image_url'=>'',
            'id_parent'=>$id_review,
            'created'=>date('Y-m-d H:i:s', time()),
            'state'=>1
        );
        $this->review->insert_reply($data_array);

        //获取讨论列表
        $page = $this->config->item('page');
        $data = $this->get_reply($id_review,$offset,$page);
        $this->smarty->assign($data);
        $this->smarty->assign('page_type','review_reply');

        $data = $this->smarty->fetch('lists.html');
        $resp = array(
            'status' => 1,
            'msg' => '',
            'data' => $data
        );
        die(json_encode($resp));

    }

    /*
     * zxx
     * 删除回复信息
     */
    function del_reply(){
        $id_activity = $this->input->post('id_activity');
        $id_review = $this->input->post('id_review');
        $delete_id = $this->input->post('delete_id');
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        $page_type = $this->input->post('page_type');//one ：单个删除  more：批量删除

        $this->load->model('review_model','review');
        if($page_type == 'one'){
            $where = 'id_review = ' . $delete_id ;
            //删除回复均为修改状态
            $this->review->delete_review($where);
            //减少评论数量
            $this->community->lower_review_count($id_activity,1);
        }else{
            foreach($delete_id as $ia){
                $where = 'id_review = ' . $ia ;
                //删除回复均为修改状态
                $this->review->delete_review($where);
            }
            //减少评论数量
            $this->community->lower_review_count($id_activity,count($delete_id));
        }
        //获取回复列表
        $page = $this->config->item('page');
        $data = $this->get_reply($id_review,$offset,$page);
        $this->smarty->assign($data);
        $this->smarty->assign('page_type','review_reply');

        $data = $this->smarty->fetch('lists.html');
        $resp = array(
            'status' => 1,
            'msg' => '',
            'data' => $data
        );
        die(json_encode($resp));
    }
    // 社区活动讨论回复  end


    // 社区活动成员管理  start
    /*
     * zxx
     * 所有社区活动的参与成员列表
     */
    function member_manage(){
        $page_type = $this->input->post('page_type');
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码

        $page = $this->config->item('page');
        $data = $this->get_member_manage($offset,$page);
//        var_dump($data);
        $this->smarty->assign($data);
        $this->smarty->assign('page_type','member_manage');

        if($page_type){
            echo $this->smarty->view('lists');
        }else{
            $this->smarty->view('member_manage');
        }
    }

    function get_member_manage($offset,$page=20){
        $this->load->model('business_model','business');
        //获取互动活动成员列表
        $member_list = $this->community->get_community_member('',$offset, $page, 'maj.id_join desc');
        if(!$member_list){
            $offset = 1;
            $member_list = $this->community->get_community_member('',$offset, $page, 'maj.id_join desc');
        }
//        foreach($member_list as $k=>$rl){
//            $member_list[$k]['head_image_url'] = '';
//            $member_list[$k]['nick_name'] = '';
//            $where_sub = 'id_open = \'' .$rl['id_open']. '\'';
//            $bus_info = $this->business->get_business_sub($where_sub);
//            if($bus_info){
//                if($bus_info[0]['head_image_url']){
//                    $member_list[$k]['head_image_url'] = $bus_info[0]['head_image_url'];
//                }else{
//                    $member_list[$k]['head_image_url'] = $this->url.'media/image/ico_user.png';
//                }
//                $member_list[$k]['nick_name'] = $bus_info[0]['nick_name'];
//            }else{
//                $member_list[$k]['head_image_url'] = $this->url.'media/image/ico_user.png';
//            }
//        }
        //获取互动活动成员总数
        $community_counts = $this->community->get_community_member_count('');
        $community_count = 0;
        if($community_counts){
            $community_count = count($community_counts);
        }

        //分页代码
        $page_html = $this->get_page($community_count, $offset, 'member_manage_page','method',$page);

        $data = array();
        $data['member_list'] = $member_list;
        $data['page_html'] = $page_html;
        $data['offset'] = $offset;
        return $data;
    }
    // 社区活动成员管理  end

    // 社区活动讨论管理  start
    /*
     * zxx
     * 所有社区活动的讨论和回复的列表
     */
    function discuss_manage(){
        $page_type = $this->input->post('page_type');
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码

        $page = $this->config->item('page');
        $data = $this->get_discuss_manage($offset,$page);
        $this->smarty->assign($data);
        $this->smarty->assign('page_type','member_discuss');

        if($page_type){
            echo $this->smarty->view('lists');
        }else{
            $this->smarty->view('discuss_manage');
        }
    }

    function get_discuss_manage($offset,$page=20){
        $this->load->model('review_model','review');
        $this->load->model('business_model','business');
        $where = 'r.object_type = \'community\' and r.state > 0 and r.id_parent = 0';

        //获取讨论列表
        $review_list = $this->review->get_review_info($where,$offset,$page,'r.id_review desc');
        if(!$review_list){
            $offset = 1;
            $review_list = $this->review->get_review_info($where,$offset,$page,'r.id_review desc');
        }

        $ms = $review_list;
        foreach($ms as $k=>$v){
//            if(!$v['id_open']){
//                $review_list[$k]['head_image_url'] = $this->users['logo'];
//            }else{
//                $where_sub = 'id_open = \'' .$v['id_open']. '\'';
//                $bus_info = $this->business->get_business_sub($where_sub);
//                if($bus_info){
//                    if($bus_info[0]['head_image_url']){
//                        $review_list[$k]['head_image_url'] = $bus_info[0]['head_image_url'];
//                    }else{
//                        $review_list[$k]['head_image_url'] = $this->url.'media/image/ico_user.png';
//                    }
//                }else{
//                    $review_list[$k]['head_image_url'] = $this->url.'media/image/ico_user.png';
//                }
//            }
            $where_r = 'r.object_type = \'community\' and r.state > 0 and r.id_object = ' . $v['id_object'] . ' and r.id_parent = ' . $v['id_review'];
            $review_counts = $this->review->get_review_count($where_r);
            $review_list[$k]['reply_count'] = $review_counts;
        }
        //获取讨论总数
        $review_count = $this->review->get_review_count($where);
        //分页代码
        $page_html = $this->get_page($review_count, $offset, 'discuss_manage_page','method',$page);

        $data = array();
        $data['review_list'] = $review_list;
        $data['page_html'] = $page_html;
        $data['offset'] = $offset;
        return $data;
    }

    // 社区活动讨论管理  end
    
    //为活动绑定电子卷
    public function bind_activ_eticket(){
        $aid = $this->input->post('id_activity');
        $id_etickets = $this->input->post('id_eticket');
        if(!isset($aid)|| !isset($id_etickets)){
            $this->return_client(0,null,null);
        }
        $result = $this->community->insert_eticket_bind($aid,$id_etickets);
        if($result){
            $this->return_client(1,null,null);
        }else{
            $this->return_client(0,null,null);
        }
     }


    /*
     * zxx 展示参加活动的界面
     */
    function to_join_community($id_community,$offset){
        $return_data['url'] = $this->url.'community/list_community/'.$offset;
        $return_data['id_community'] = $id_community;

        $this->smarty->assign($return_data);
        $this->smarty->view('join_activity');
    }

    /*
     * zxx
     * 搜索会员
     */
    function search_customer(){
        $name = $this->input->post('name');

        $this->load->model('user_model','user');
        $user_info = $this->user->get_userinfo_by_username_or_email_or_phone($name);
        if($user_info){
            $resp = array(
                'status' => 1,
                'msg' => $user_info
            );
        }else{
            $resp = array(
                'status' => 0,
                'msg' => '没有你要搜索的用户信息.'
            );
        }
        die(json_encode($resp));
    }

    /*
     * zxx
     * 参加活动
     */
    function join_community(){
        $id_user = $this->input->post('id_user');
        $phone = $this->input->post('phone');
        $id_commodity = $this->input->post('id_commodity');
        $url_action = $this->input->post('url_action');

        $data_insert = array(
            'id_activity'=>$id_commodity,
            'cellphone'=>$phone,
            'id_user'=>$id_user,
            'created'=>date('Y-m-d H:i:s',time()),
            'role'=>1,
            'identity'=>'register',
        );
        $this->community->insert_community_join($data_insert);
        $resp = array(
            'status' => 1,
            'msg' => '参加活动成功。',
            'url'=>$url_action
        );
        die(json_encode($resp));
    }

    /*
     * zxx
     * 解除绑定文章
     */
    function unbind_content(){
        $id_activity = $this->input->post('id_activity');

        $where = 'id_activity = ' . $id_activity;
        $data = array('id_content'=>0);
        $this->community->update_community($data,$where);
        $resp = array(
            'status' => 1,
            'msg' => '解除绑定成功。'
        );
        die(json_encode($resp));
    }


}


/* End of file activity.php */