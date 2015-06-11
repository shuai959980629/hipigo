<?php
/**
 * 
 * @copyright(c) 2014-3-26
 * @author zxx
 * @version Id:ticket.php
 */

class Ticket extends Admin_Controller
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

    /**
     * zxx
     * 创建电子券
     */
    public function add_ticket($id_ticket=0){
        $data ['url_action'] = 'ticket/add_ticket';
        if($this->input->post('name'))
        {
            $this->load->library('form_validation');
            if ($this->form_validation->run('add_ticket') === TRUE)
            {
                $type = $this->input->post('type');//类型
                $name = $this->input->post('name');//标题
                $number = $this->input->post('number');//内容
                $length = $this->input->post('length');//权重
                $address = $this->input->post('address');//兑换地址
                $valid_begin = $this->input->post('valid_begin');//开始时间
                $valid_end = $this->input->post('valid_end');//结束时间
                $image = $this->input->post('image_src');//图片名
                $id_ticket = $this->input->post('id_ticket');//电子券id

                $content = $this->input->post('content');//内容

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

                $this->load->model('ticket_model','ticket');

                $this->load->model('business_model','business');
                $data1['url'] = $this->url.'ticket/list_ticket';
                $data1['is_refresh'] = 2;
                if($id_ticket){

                    $wherea = 'id_object = '.$id_ticket.' and object_type = \'eticket\' and id_business = '.$this->id_business . ' and id_shop = '.$this->id_shop;
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
                                        'object_type' => 'eticket',
                                        'id_object' => $id_ticket,
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
                                            'object_type' => 'eticket',
                                            'id_object' => $id_ticket,
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
                                        $path = $this->file_url_name('_editor',$filename);
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
                                    $path = $this->file_url_name('_editor',$filename);
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

                    $where = 'id_eticket = ' . $id_ticket;
                    $ticket_info = array(
                        'name' => $name,
                        'type' => $type,
                        'quantity' => $number,
                        'length' => $length,
                        'describe'=>$content,
                        'address'=>$address,
                        'valid_begin'=>$valid_begin,
                        'valid_end'=> $valid_end
                    );
                    if($image != ''){
                        $ticket_info['image'] = $image;
                    }
                    $this->ticket->update_ticket($ticket_info,$where);
                    $this->returen_status(1,$this->lang->line('edit_data_success'),$data1);
                }else{
                    $ticket_info = array(
                        'id_business' => $this->id_business,
                        'id_shop' => $this->id_shop,
                        'name' => $name,
                        'type' => $type,
                        'quantity' => $number,
                        'get_quantity' => 0,
                        'use_quantity' => 0,
                        'length' => $length,
                        'describe'=>$content,
                        'image' => $image,
                        'address'=>$address,
                        'valid_begin'=>$valid_begin,
                        'valid_end'=> $valid_end,
                        'created'=>date('Y-m-d H:i:s', time())
                    );
                    $id_ticket = $this->ticket->insert_ticket($ticket_info);


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
                                'object_type' => 'eticket',
                                'id_object' => $id_ticket,
                                'attachment_type' => $attachment_type,
                                'image_url' => $image_url,
                                'size' => $file_size,
                            );
                            $this->business->insert_business_attachment($business_attachment);
                        }
                    }

                    $this->returen_status(1,$this->lang->line('add_data_success'),$data1);
                }
            }
            else
            {
                $this->returen_status(0,$this->form_validation->error_string(),$data);
            }
        }else{
            $type = 'add_ticket';
            if($id_ticket){
                $type = 'edit_ticket';
                $this->load->model('ticket_model','ticket');
                $where = 'id_eticket = ' . $id_ticket;
                $ticket_info = $this->ticket->get_ticket_introduction($where);
                if($ticket_info){
                    $data['ticket_info'] = $ticket_info[0];
                }
            }
            $this->smarty->assign($data);
            $this->smarty->assign('id_ticket',$id_ticket);
            $this->smarty->assign('type',$type);
            $this->smarty->view('add_ticket');
        }
    }

    /*
     * zxx
     * 查询电子券名称是否重复
     */
    public function search_tname(){
        $this->load->model('ticket_model','ticket');

        $name = $this->input->post('name');//标题
        $where_s = 'name = \'' . $name . '\'';
        $t_count = $this->ticket->get_ticket_count($where_s);
        if($t_count > 0){
            $resp = array(
                'status' => 0
            );
            die(json_encode($resp));
        }else{
            $resp = array(
                'status' => 1
            );
            die(json_encode($resp));
        }
    }


    /**
     * zxx
     * 电子券列表
     */
    public function list_ticket($offset=0){
        if(!$offset){
            $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        }
        //获取物品列表
        $data = $this->get_ticket($offset);
        $this->smarty->assign($data);
        $this->smarty->assign('offset',$offset);
        $this->smarty->assign('page_type','list_ticket');

        if($this->input->post('ispage') == 1){
            echo $this->smarty->view('lists');
        }else{
            $this->smarty->view('list_ticket');
        }
    }

    /*
     * zxx
     * 删除电子券
     * **/
    function delete_ticket(){
        $id_ticket = $this->input->post('id_ticket');
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码

        $this->load->model('ticket_model','ticket');
        //获取电子券信息
        $where = 'id_eticket = ' . $id_ticket;
        $ticket_info= $this->ticket->get_ticket_introduction($where);
        foreach($ticket_info as $ci){
            //获取本地文件图片附件地址
            $path = $this->file_url_name('ticket',$ci['image']);
            if(!file_exists($path) && !is_readable($path)){
            }else{
                unlink($path);//删除图片附件
            }
        }
        //删除电子券信息
        $this->ticket->delete_ticket($where);
        $where_item = 'object_type = \'eticket\' and id_object = '.$id_ticket;
        $this->ticket->delete_ticket_item($where_item);

        $data = $this->get_ticket($offset);
        $this->smarty->assign($data);
        $this->smarty->assign('offset',$offset);
        $this->smarty->assign('page_type','list_ticket');

        $data = $this->smarty->fetch('lists.html');
        $resp = array(
            'status' => 1,
            'msg' => '',
            'data' => $data
        );
        die(json_encode($resp));
    }

    /*
     * 获取电子券列表
     * zxx
     * $offset ：页码
     * $page ：条数
     * **/
    function get_ticket($offset,$page=0){
        //分页数量
        $page = $page ? $page : ($this->config->item('page'));
//        $page = 2;
        $this->load->model('ticket_model','ticket');

        $where = 'id_shop = '.$this->id_shop.' and id_business = '.$this->id_business;
        //获取物品列表
        $ticket_list = $this->ticket->get_ticket_introduction($where,$offset,$page,'valid_end desc,created desc');
        if(!$ticket_list){
            $offset = 1;
            $ticket_list = $this->ticket->get_ticket_introduction($where,$offset,$page,'valid_end desc,created desc');
        }


        //获取活动设置信息
        $this->load->model('activity_model','activity');
        $where_two = '';
        $activity = $this->activity->get_activity_two($where_two);
        $activity_infos=array();
        if($activity){
            foreach($activity as $k1=>$a){
                if($a['requirement'] != ''){
                    $requirement = json_decode($a['requirement'],true);
                    $activity[$k1]['requirements'] = $requirement;
                }
                if($activity[$k1]['requirements']['eticket']){
                    foreach($activity[$k1]['requirements']['eticket'] as $k2=>$re){
                        array_push($activity_infos,$re['eticketId']);
                    }
                }
            }
        }
        //去除重复的数组值
        $tickets = array_unique($activity_infos);
        foreach($ticket_list as $k=>$tl){
            $ticket_list[$k]['over'] = $tl['quantity'] - $tl['get_quantity'];
            $Date_1=date('Y-m-d H:i:s', time());
            $Date_2=$tl['valid_end'];
            if((strtotime($Date_1) - strtotime($Date_2))>0){//过期了！0 : 过期  1：未过期
                $ticket_list[$k]['is_expired'] = '0';//如果电子券过期且不存在与活动设置里则删除哦！
                if(in_array($tl['id_eticket'],$tickets)){
                    $ticket_list[$k]['is_expired'] = '1';//如果当前电子券id存在与活动设置表里面则不能删除
                }
            }else{
                $ticket_list[$k]['is_expired'] = '1';//未过期不能删除
            }
        }

        //获取物品总数
        $ticket_count = $this->ticket->get_ticket_count($where);
        //分页代码
        $page_html = $this->get_page($ticket_count, $offset, 'ticket_list_page','method',$page);
        $data = array();
        $data['ticket_list'] = $ticket_list;
        $data['page_html'] = $page_html;
        return $data;
    }

    /**
     * zxx
     * 电子券验证
     */
    function verify_ticket(){
        $this->load->model('ticket_model','ticket');
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        $search_key = $this->input->post('search_key') ? $this->input->post('search_key') : '';//搜索关键字

        //分页数量   $page = $this->config->item('page');
        $page = 20;
        $data = array();
//ti.id_shop = '.$id_shop.' and
        $where = '(ti.id_business = '.$this->id_business . ' or ti.id_verify_business = '.$this->id_business . ')'; //. ' and ti.object_type = \'eticket\'';
//        $where = '';
        if($search_key != ''){
            $where .= ' and ti.code = \'' .$search_key . '\'';
        }else{
            $where .= ' and ti.state = 2';
        }
        $verify_ticket = $this->ticket->get_ticket_verify($where,$offset,$page,'ti.use_time desc');

        $verify_info = array();
        if($verify_ticket){
            $this->load->model('community_model','community');
            $data_now=date("y-m-d h:i:s");
            $this->load->model('hipigouser_model','hipigouser');
            $this->load->model('business_model','business');
            foreach($verify_ticket as $kt=>$vt){
                $verify_ticket[$kt]['nick_name'] = '';
                if($vt['id_customer'] == 0 || $vt['id_customer'] == null){
                    if(strlen($vt['id_open']) <= 11){
                        $verify_ticket[$kt]['nick_name'] = $vt['id_open'];
                    }else{
                        $where = 'id_open = \''.$vt['id_open'].'\'';
                        $business_info = $this->business->get_business_sub($where);
                        if($business_info){
                            $verify_ticket[$kt]['nick_name'] = $business_info[0]['nick_name'];
                        }
                    }
                }else{
                    $where_hu = 'id_user = ' . $vt['id_customer'];
                    $hipigo_user = $this->hipigouser->get_hipigo_user_info($where_hu);
                    if($hipigo_user){
                        $verify_ticket[$kt]['nick_name'] = $hipigo_user[0]['nick_name'];
                    }
                }

                $verify_info[] = array('id_business'=>$vt['id_business'],'id_shop'=>$vt['id_shop']);

                $verify_ticket[$kt]['begin_num'] = 0;
                $verify_ticket[$kt]['end_num'] = 0;
                if($vt['object_type'] == 'eticket'){
                    $where_t = 'id_eticket = ' . $vt['id_object'];
                    $ticket_info = $this->ticket->get_ticket_introduction($where_t);
                    if($ticket_info){
                        if(strtotime($ticket_info[0]['valid_begin']) > strtotime($data_now)){
                            $verify_ticket[$kt]['begin_num'] = 1;
                        }elseif(strtotime($ticket_info[0]['valid_end']) < strtotime($data_now) && strtotime($ticket_info[0]['valid_end']) !== false){
                            $verify_ticket[$kt]['end_num'] = 1;
                        }
                        $verify_ticket[$kt]['name'] = $ticket_info[0]['name'];
                    }

                    //查询验证表的信息
                    $where_ver = 'id_object = ' . $vt['id_object'] . ' and object_type = \'eticket\'';
                    $ver_bus_info = $this->ticket->get_ticket_verify_business($where_ver);
                    if($ver_bus_info){
                        foreach($ver_bus_info as $k=>$val){
                            $verify_info[$k+1]['id_business'] = $val['id_business'];
                            $verify_info[$k+1]['id_shop'] = $val['id_shop'];
                        }
                    }
                }elseif($vt['object_type'] == 'community'){
                    $where_t = 'id_activity = ' . $vt['id_object'];
                    $community_info = $this->community->get_community_info($where_t);
                    if($community_info){
                        if($community_info[0]['state'] == 1 || $community_info[0]['state'] == -1){
                            $verify_ticket[$kt]['begin_num'] = 1;
                            $verify_ticket[$kt]['end_num'] = 1;
                        }
                        $verify_ticket[$kt]['name'] = $community_info[0]['name'];
                    }

                    //查询验证表的信息
                    $where_ver = 'id_object = ' . $vt['id_object'] . ' and object_type = \'community\'';
                    $ver_bus_info = $this->ticket->get_ticket_verify_business($where_ver);
                    if($ver_bus_info){
                        foreach($ver_bus_info as $k=>$val){
                            $verify_info[$k+1]['id_business'] = $val['id_business'];
                            $verify_info[$k+1]['id_shop'] = $val['id_shop'];
                        }
                    }
                }
            }
        }
        foreach($verify_ticket as $kt=>$vt){
            $is_in = 0;//判断当前商家和门店是否在电子券验证商家列表内
            $verify_ticket[$kt]['verify_ticket'] = 1;
            if($vt['object_type'] == 'eticket'){
                foreach($verify_info as $v){
                    if($v['id_business'] == $this->id_business && ($v['id_shop'] == $this->id_shop || $v['id_shop'] == 0)){
                        $is_in = 1;
                    }
                }

                if($is_in == 0){
                    $verify_ticket[$kt]['verify_ticket'] = 0;
                }
            }elseif($vt['object_type'] == 'community'){
                foreach($verify_info as $v){
                    if($v['id_business'] == $this->id_business && ($v['id_shop'] == $this->id_shop || $v['id_shop'] == 0)){
                        $is_in = 1;
                    }
                }

                if($is_in == 0){
                    $verify_ticket[$kt]['verify_ticket'] = 0;
                }
            }
        }

        $data['verify_ticket'] = $verify_ticket;

        $this->smarty->assign($data);
        $this->smarty->assign('page_type','verify_ticket');
        if($this->input->post('ispage') == 1){
            echo $this->smarty->view('lists');
        }else{
            $this->smarty->view('verify_ticket');
        }
    }

    /**
     * zxx
     * 电子券使用
     */
    function change_ticket_state(){
        $this->load->model('ticket_model','ticket');
        $this->load->model('list_model','list');
        $id_item = $this->input->post('id_item');
        $id_ticket = $this->input->post('id_ticket');
        $object_type = $this->input->post('object_type');

        $where_item = 'id_item = ' . $id_item;
        $update_date = array(
            'id_verify_business'=>$this->id_business,
            'id_verify_shop'=>$this->id_shop,
            'use_time'=>date('Y-m-d H:i:s', time()),
            'state'=>2
        );
        $where = 'id_eticket = ' . $id_ticket;

        $this->db->trans_begin();
        //更新电子券使用状态
        $this->ticket->update_ticket_item($update_date,$where_item);
        //更新电子券使用数量
        $this->ticket->update_use_quantity($where);

        if($object_type != 'eticket'){
            //查询订单号
            $where_i = 'id_eticket = ' .$id_item.' and source_type = \'order\'';
            $income_item = $this->list->query_income_item($where_i);
            $id_order = '';
            if($income_item){
                $id_order = $income_item[0]['id_source_type'];//订单号
            }
            $data_finance = array(
                'id_order'=>$id_order,
                'id_business'=>$this->id_business,
                'id_item'=>$id_item,
                'type'=>2,
                'sysuser_name'=>$this->users['biz_name'],
                'date'=>date('Y-m-d H:i:s', time())
            );
            //增加验证电子券日志记录
            $this->list->insert_handle_log($data_finance);
        }

        if ($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            $resp = array(
                'status' => 0,
                'msg' => '',
                'data' => ''
            );
            die(json_encode($resp));
        }else{
            $this->db->trans_commit();

//            $id_business = $this->users['id_business'];//总店id
//            $id_shop = $this->users['id_shop'];//门店id
//            $where = 'ti.id_shop = '.$id_shop.' and ti.id_business = '.$id_business . ' and ti.state = 2 and ti.id_item = ' . $id_item . ' and ti.object_type = \'eticket\'';
//            $verify_ticket = $this->ticket->get_ticket_verify($where,1,1);
//            $this->smarty->assign('verify_ticket',$verify_ticket);
//            $this->smarty->assign('page_type','change_ticket');
//            $data = $this->smarty->fetch('lists.html');
            $resp = array(
                'status' => 1,
                'msg' => '',
                'data' => ''
            );
            die(json_encode($resp));
        }
    }

    /**
     * zxx
     * 电子券明细
     */
    function item_ticket($id_ticket){
        $this->load->model('ticket_model','ticket');
//        $d = array( 'code'=>'1311', 'id_open'=>'1', 'id_customer'=>'1', 'get_time'=>date('Y-m-d H:i:s', time()), 'use_time'=>date('Y-m-d H:i:s', time()), 'state'=>'1', );
//        $q = $this->ticket->insert_ticket_item($d); die;
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        if(!$id_ticket){
            $id_ticket = $this->input->post('id_ticket');
        }
        //分页数量
        $page = $this->config->item('page');
//        $page = 2;
        $data = array();

        $where = 'ti.id_shop = '.$this->id_shop.' and ti.id_business = '.$this->id_business . ' and ti.id_object = ' . $id_ticket . ' and ti.object_type = \'eticket\'';

        $ticket_item = $this->ticket->get_ticket_item($where,$offset,$page,0);
        if(!$ticket_item){
            $offset = 1;
            $ticket_item = $this->ticket->get_ticket_item($where,$offset,$page,0);
        }
        $this->load->model('business_model','business');
        $this->load->model('hipigouser_model','hipigouser');

        foreach($ticket_item as $k=>$ti){
            $ticket_item[$k]['nick_name'] = '';

            $ticket_item[$k]['nick_name'] = '';
            if($ti['id_customer'] == 0 || $ti['id_customer'] == null){
                if(strlen($ti['id_open']) <= 11){
                    $ticket_item[$k]['nick_name'] = $ti['id_open'];
                }else{
                    $where = 'id_open = \''.$ti['id_open'].'\'';
                    $business_info = $this->business->get_business_sub($where);
                    if($business_info){
                        $ticket_item[$k]['nick_name'] = $business_info[0]['nick_name'];
                    }
                }
            }else{
                $where_hu = 'id_user = ' . $ti['id_customer'];
                $hipigo_user = $this->hipigouser->get_hipigo_user_info($where_hu);
                if($hipigo_user){
                    $ticket_item[$k]['nick_name'] = $hipigo_user[0]['nick_name'];
                }
            }

//            if(strlen($ti['id_open']) == 11){
//                $ticket_item[$k]['nick_name'] = $ti['id_open'];
//            }elseif(strlen($ti['id_open']) > 11){
//                $where_s = 'id_open = \'' .$ti['id_open']. '\'';
//                $bus_info = $this->business->get_business_sub($where_s);
//                if($bus_info){
//                    $ticket_item[$k]['nick_name'] = $bus_info[0]['nick_name'];
//                }
//            }elseif(strlen($ti['id_open']) == 0){
//                var_dump($ti['id_item']);
//                $where_h = 'id_user = ' .$ti['id_customer'];
//                $hu_info = $this->hipigouser->get_hipigo_user_info($where_h);
//                if($hu_info){
//                    $ticket_item[$k]['nick_name'] = $hu_info[0]['nick_name'];
//                }
//            }
        }
        //获取物品总数
        $ticket_count = $this->ticket->get_ticket_item_count($where);
        //分页代码
        $page_html = $this->get_page($ticket_count, $offset, 'ticket_item_page','method',$page);

        $data['ticket_item'] = $ticket_item;
        $data['page_html'] = $page_html;

        $this->smarty->assign($data);
        $this->smarty->assign('offset',$offset);
        $this->smarty->assign('id_ticket',$id_ticket);
        $this->smarty->assign('page_type','item_ticket');

        if($this->input->post('ispage') == 1){
            echo $this->smarty->view('lists');
        }else{
            $this->smarty->view('item_ticket');
        }
    }
    
    /**
     * @电子卷信息展示
     */
     public function ticket_detail($id_ticket){
        $this->load->model('ticket_model','ticket');
        if(!$id_ticket){
            $id_ticket = $this->input->post('id_ticket');
        }
        echo $id_ticket;
     }

}



/* End of file ticket.php */