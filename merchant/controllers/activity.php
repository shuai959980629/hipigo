<?php
/**
 * 
 * @copyright(c) 2013-11-21
 * @author msi
 * @version Id:activity.php
 */
class Activity extends Admin_Controller
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

	public function add_activity($page=0,$a_id=0){
		$this->load->helper(array('form','url'));
		$this->load->library('form_validation');
//        $tpl = $this->input->post('tpl');//是否是admin模块下
		if($this->input->post('page')){
            $page = $this->input->post('page');//判断是第几步设置
            $reutn_url = array(
                'add_url' => '/activity/add_activity',
                'list_url' => '/activity/list_activity'
            );
            if($page == '1'){
                if ($this->form_validation->run('activity') === TRUE){
                    $image_src = $this->input->post('image_src');
                    $content = $this->input->post('content');
                    $type = $this->input->post('type');//活动类型

                    $this->load->model('business_model','business');

                    $title = $this->input->post('title');
                    $title = $this->replace_html($title);
                    $add = array(
                        'id_business' => intval($this->id_business),
                        'id_shop' => intval($this->id_shop),
                        'weight' => intval($this->input->post('sort')),
                        'state' => $this->input->post('status') == 1 ? 1 : 0,
//						'image_url' => $img,
                        'name' => trim($title),
                        'content' => $content,
                        'type' => $type
                    );

                    if($image_src){
                        $add['image_url'] = $image_src;
                    }
                    $this->load->model('activity_model');
                    $aid = $this->activity_model->add_activity($add);

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
                                'object_type' => 'activity',
                                'id_object' => $aid,
                                'attachment_type' => $attachment_type,
                                'image_url' => $image_url,
                                'size' => $file_size,
                            );
                            $this->business->insert_business_attachment($business_attachment);
                        }
                    }
                    if($aid > 0){
//                        header('location: '.$this->url.'activity/add_activity/2');
                        $data['url'] = $this->url.'activity/add_activity/2/'.$aid.$this->tpl[0];
                        $data['is_refresh'] = 2;
                        $this->returen_status(1, $this->lang->line('add_data_success'),$data);
                    }else{
                        $this->returen_status(0, $this->lang->line('add_data_failed'),$reutn_url);
                    }
                }else{
                    $this->returen_status(0, $this->form_validation->error_string());
                }
            }elseif($page == '2'){
                $is_customer = $this->input->post('is_customer') ? 'register':'wechat';//参与者是否是注册用户 1
                $join_count_check = $this->input->post('join_count_check');//1：是否勾选每人每天最多参与次数   2：是否勾选每人总共参与次数
//                $total_check = $this->input->post('total_check');//是否勾选每人总共参与次数
                $day_max_count = $this->input->post('day_max_count')?$this->input->post('day_max_count'):0;//每人每天最多参与次数
                $total_count = $this->input->post('total_count')?$this->input->post('total_count'):0;//每人总共参与次数
                $person_count = $this->input->post('person_count')?$this->input->post('person_count'):0;//预计人数
                $activity_day = $this->input->post('activity_day')?$this->input->post('activity_day'):0;//预计活动时间
                $bound = $this->input->post('bound');//是否绑定电子券
                $end_time = $this->input->post('end_time');//完成时间（秒）
                $success_tip = $this->input->post('success_tip');//成功提示语
                $failed_tip = $this->input->post('failed_tip')?$this->input->post('failed_tip'):'';//失败提示语
                $id_ticket = $this->input->post('id_ticket');//电子券编号
                $correct_rate = $this->input->post('correct_rate');//正确率
                $consume_time = $this->input->post('consume_time');//耗时
                $max_count = $this->input->post('max_count');//最多获得次数
                $aid = $this->input->post('a_id');//活动id
                $activity_type = $this->input->post('activity_type');//活动类型

                if($bound == 1){
                    if($activity_type == 'answer'){
                        //参加活动的条件拼接
                        $requirement = '{"type": "'.$activity_type.'","user": "'.$is_customer.'","eticket": [';
                        foreach($correct_rate as $k=>$v){//电子券编号  答题正确率 90%  耗时 120秒  最多获得次数
                            $requirement .= '{"eticketId":'.$id_ticket[$k].',"accuracy":'.$v.',"consuming":'.$consume_time[$k].',"getMaxNumber":'.$max_count[$k].'}';
                            if($k < (count($correct_rate) - 1) && count($correct_rate) != 1){
                                $requirement .= ',';
                            }
                        }
                        $requirement .= ']}';
                    }elseif($activity_type == 'egg'){
                        //参加活动的条件拼接
                        $requirement = '{"type": "'.$activity_type.'","user": "'.$is_customer.'","eticket": [';
                        foreach($max_count as $k=>$v){//电子券编号  最多获得次数
                            $requirement .= '{"eticketId":'.$id_ticket[$k].',"getMaxNumber":'.$v.'}';
                            if($k < (count($max_count) - 1) && count($max_count) != 1){
                                $requirement .= ',';
                            }
                        }
                        $requirement .= ']}';
                    }elseif($activity_type == 'event'){
                        $event = $this->input->post('event');//绑定的事件
                        $review_object = $this->input->post('review_object')?$this->input->post('review_object'):'';//绑定的评论对象
                        $id_object = $this->input->post('id_obj')?$this->input->post('id_obj'):'';//绑定的对象id
                        $count = $this->input->post('review_count')?$this->input->post('review_count'):'';//评论次数

                        //参加活动的条件拼接
                        $requirement = '{"type": "'.$activity_type.'","user": "'.$is_customer.'","eticket": [';
                        foreach($max_count as $k=>$v){//电子券编号 最多获得次数
                            $requirement .= '{"eticketId":'.$id_ticket[$k].',"getMaxNumber":'.$v.'}';
                            if($k < (count($max_count) - 1) && count($max_count) != 1){
                                $requirement .= ',';
                            }
                        }
                        $requirement .= '],"event": [';
                        foreach($event as $k1=>$e){//动作
                            if($e == 'review'){
                                $requirement .= '{"action":"'.$e.'","reviewObject":"'.($review_object=="cmd"?"commodity":$review_object).'","obejctId":"'.$id_object.'","count": '.$count.'}';
                            }else{
                                $requirement .= '{"action":"'.$e.'"}';
                            }
                            if($k1 < (count($event) - 1) && count($event) != 1){
                                $requirement .= ',';
                            }
                        }
                        $requirement .= ']}';
                    }
                }else{
                    $requirement = '';
                }

                $max_ = 0;//每人每天最多参与次数
                $total_ = 0;//每人总共参与次数
                if($join_count_check){
                    foreach($join_count_check as $jcc){
                        if($jcc == 1){
                            $max_ = $day_max_count;
                        }elseif($jcc == 2){
                            $total_ = $total_count;
                        }
                    }
                }

                $data_array = array(
                    'id_activity' => $aid,
                    'join_number_day' => $max_,
                    'join_number_total' => $total_,
                    'estimate_people_number' => $person_count,
                    'estimate_activity_day' => $activity_day,
                    'complete_time' => $end_time,
                    'requirement' => $requirement,
                    'success_reply' => $success_tip,
                    'failure_reply'=>$failed_tip
                );

                $this->load->model('activity_model','activity');
                $aid_two = $this->activity->insert_activity_two($data_array);

                if($activity_type == 'answer'){
                    if($aid_two > 0){
                        $data['url'] = $this->url.'activity/add_activity/3/'.$aid.$this->tpl[0];
                        $data['is_refresh'] = 2;
                        $this->returen_status(1, $this->lang->line('add_data_success'),$data);
                    }else{
                        $this->returen_status(0, $this->lang->line('add_data_failed'),$reutn_url);
                    }
                }elseif($activity_type == 'egg'){
                    if($aid_two > 0){
                        $data['url'] = $this->url.'activity/list_activity'.$this->tpl[0];
                        $data['is_refresh'] = 2;
                        $this->returen_status(1, $this->lang->line('add_data_success'),$data);
                    }else{
                        $this->returen_status(0, $this->lang->line('add_data_failed'),$reutn_url);
                    }
                }elseif($activity_type == 'event'){
                    if($aid_two > 0){
                        $data['url'] = $this->url.'activity/list_activity'.$this->tpl[0];
                        $data['is_refresh'] = 2;
                        $this->returen_status(1, $this->lang->line('add_data_success'),$data);
                    }else{
                        $this->returen_status(0, $this->lang->line('add_data_failed'),$reutn_url);
                    }
                }
            }
		}else{
            if(!$page){
                $page = 1;
            }
            $this->smarty->assign('type','add');
            if($page == 1){
                $this->smarty->view('add_activity');
            }elseif($page == 2){
                $pages = 20;
                $this->load->model('ticket_model','ticket');

                $where = 'id_shop = '.$this->id_shop.' and id_business = '.$this->id_business;
                $ticket_info = $this->ticket->get_ticket_introduction($where,1,$pages,'id_eticket desc');

                $this->load->model('activity_model','activity');
                $where .= ' and id_activity = '.$a_id . ' and state >= 0';
                $activity_info = $this->activity->get_new_activity($where,1);
                $activity_type = '';
                if($activity_info){
                    $activity_type = $activity_info[0]['type'];
                }

                $this->smarty->assign('ticket_info',$ticket_info);
                $this->smarty->assign('activity_type',$activity_type);
                $this->smarty->assign('a_id',$a_id);
                if($activity_type == 'answer'){
                    $this->smarty->assign('page_type','answer_two');
                    $this->smarty->view('answer_two');
                }elseif($activity_type == 'egg'){
                    $this->smarty->assign('page_type','egg_two');
                    $this->smarty->view('egg_two');
                }elseif($activity_type == 'event'){
                    //获取图文回复信息
                    $return = $this->img_text_reply();
                    $this->smarty->assign($return);

                    $this->smarty->assign('page_type','event_two');
                    $this->smarty->view('event_two');
                }
            }elseif($page == 3){
                $this->load->model('activity_model','activity');
                $where = 'ts.id_activity = ' . $a_id;
                //活动的题目信息
                $answer_subject = $this->activity->get_answer_subject($where,0,20,'ts.weight desc',3);
                $this->smarty->assign('answer_subject',$answer_subject);

                //活动的页面类型
                $where ='id_shop = '.$this->id_shop.' and id_business = '.$this->id_business .' and id_activity = '.$a_id;
                $activity_info = $this->activity->get_new_activity($where,1);
                $activity_type = '';
                if($activity_info){
                    $activity_type = $activity_info[0]['type'];
                }
                $this->smarty->assign('a_id',$a_id);
                $this->smarty->assign('activity_type',$activity_type);
                $this->smarty->view('answer_three');
            }
		}
	}

    //编辑活动
	public function edit_activity($page=1,$id=0){
        $this->load->model('activity_model');

        if($this->input->post('eid')){
            $aid = $this->input->post('eid');
            $page = $this->input->post('page');//判断是第几步设置
            if($page == '1'){
                $this->load->library('form_validation');
                if ($this->form_validation->run('activity') === TRUE){

                    $image_src = $this->input->post('image_src');
                    $content = $this->input->post('content');

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

                    $wherea = 'id_object = '.$aid.' and object_type = \'activity\' and id_business = '.$this->id_business . ' and id_shop = '.$this->id_shop;
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
                                        'object_type' => 'activity',
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
                                            'object_type' => 'activity',
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
                                        $path = $this->file_url_name('activity',$filename);
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
                                    $path = $this->file_url_name('activity',$filename);
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

                    $sdata = array();
    //				if ($_FILES) {
    //					$img_arr = $this->upload_files('actimg',$_FILES['actimg']['name'],'activity');
    //					if(!empty($img_arr['file_name'])){
    //						$sdata['image_url'] = $img_arr['file_name'];
    //					}
    //				}

                    if(trim($image_src)){
                        $sdata['image_url'] = $image_src;
                    }
                    $title = $this->input->post('title');
                    $title = $this->replace_html($title);

                    $sdata['state'] = $this->input->post('status') == 1 ? 1 : 0;
                    $sdata['weight'] = $this->input->post('sort');
                    $sdata['name'] = trim($title);
                    $sdata['content'] = $content;
                    $this->activity_model->edit_activity(array('id_activity'=>$aid,'id_shop'=>$this->id_shop),$sdata);
    //                $reutn_url = array(
    //                        'list_url' => '/activity/list_activity'
    //                );

                    $data['url'] = $this->url.'activity/edit_activity/2/'.$aid.$this->tpl[0];
                    $data['is_refresh'] = 2;
                    $this->returen_status(1, $this->lang->line('edit_data_success'),$data);
                }else {
                    $this->returen_status(0,$this->form_validation->error_string());
                }
            }elseif($page == '2'){
                $is_customer = $this->input->post('is_customer') ? 'register':'wechat';//参与者是否是注册用户 1
                $join_count_check = $this->input->post('join_count_check');//1：是否勾选每人每天最多参与次数   2：是否勾选每人总共参与次数
//                $total_check = $this->input->post('total_check');//是否勾选每人总共参与次数
                $day_max_count = $this->input->post('day_max_count')?$this->input->post('day_max_count'):0;//每人每天最多参与次数
                $total_count = $this->input->post('total_count')?$this->input->post('total_count'):0;//每人总共参与次数
                $person_count = $this->input->post('person_count')?$this->input->post('person_count'):0;//预计人数
                $activity_day = $this->input->post('activity_day')?$this->input->post('activity_day'):0;//预计活动时间
                $bound = $this->input->post('bound');//是否绑定电子券
                $end_time = $this->input->post('end_time');//完成时间（秒）
                $success_tip = $this->input->post('success_tip');//成功提示语
                $failed_tip = $this->input->post('failed_tip')?$this->input->post('failed_tip'):'';//失败提示语
                $id_ticket = $this->input->post('id_ticket');//电子券编号
                $correct_rate = $this->input->post('correct_rate');//正确率
                $consume_time = $this->input->post('consume_time');//耗时
                $max_count = $this->input->post('max_count');//最多获得次数
                $aid = $this->input->post('a_id');//活动id
                $activity_type = $this->input->post('activity_type');//活动类型

                if($bound == 1){
                    if($activity_type == 'answer'){
                        //参加活动的条件拼接
                        $requirement = '{"type": "'.$activity_type.'","user": "'.$is_customer.'","eticket": [';
                        foreach($correct_rate as $k=>$v){//电子券编号  答题正确率 90%  耗时 120秒  最多获得次数
                            $requirement .= '{"eticketId":'.$id_ticket[$k].',"accuracy":'.$v.',"consuming":'.$consume_time[$k].',"getMaxNumber":'.$max_count[$k].'}';
                            if($k < (count($correct_rate) - 1) && count($correct_rate) != 1){
                                $requirement .= ',';
                            }
                        }
                        $requirement .= ']}';
                    }elseif($activity_type == 'egg'){
                        //参加活动的条件拼接
                        $requirement = '{"type": "'.$activity_type.'","user": "'.$is_customer.'","eticket": [';
                        foreach($max_count as $k=>$v){//电子券编号  最多获得次数
                            $requirement .= '{"eticketId":'.$id_ticket[$k].',"getMaxNumber":'.$v.'}';
                            if($k < (count($max_count) - 1) && count($max_count) != 1){
                                $requirement .= ',';
                            }
                        }
                        $requirement .= ']}';
                    }elseif($activity_type == 'event'){
                        $event = $this->input->post('event');//绑定的事件
                        $review_object = $this->input->post('review_object')?$this->input->post('review_object'):'';//绑定的评论对象
                        $id_object = $this->input->post('id_obj')?$this->input->post('id_obj'):"";//绑定的对象id
                        $count = $this->input->post('review_count')?$this->input->post('review_count'):'';//评论次数

                        //参加活动的条件拼接
                        $requirement = '{"type": "'.$activity_type.'","user": "'.$is_customer.'","eticket": [';
                        foreach($max_count as $k=>$v){//电子券编号 最多获得次数
                            $requirement .= '{"eticketId":'.$id_ticket[$k].',"getMaxNumber":'.$v.'}';
                            if($k < (count($max_count) - 1) && count($max_count) != 1){
                                $requirement .= ',';
                            }
                        }
                        $requirement .= '],"event": [';
                        foreach($event as $k1=>$ev){//动作
                            if($ev == 'review'){
                                $requirement .= '{"action":"'.$ev.'","reviewObject":"'.($review_object=="cmd"?"commodity":$review_object).'","obejctId":"'.$id_object.'","count": '.$count.'}';
                            }else{
                                $requirement .= '{"action":"'.$ev.'"}';
                            }
                            if($k1 < (count($event) - 1) && count($event) != 1){
                                $requirement .= ',';
                            }
                        }
                        $requirement .= ']}';
                    }
                }else{
                    $requirement = '';
                }
                $max_ = 0;//每人每天最多参与次数
                $total_ = 0;//每人总共参与次数
                foreach($join_count_check as $jcc){
                    if($jcc == 1){
                        $max_ = $day_max_count;
                    }elseif($jcc == 2){
                        $total_ = $total_count;
                    }
                }

                $data_array = array(
                    'id_activity' => $aid,
                    'join_number_day' => $max_,
                    'join_number_total' => $total_,
                    'estimate_people_number' => $person_count,
                    'estimate_activity_day' => $activity_day,
                    'complete_time' => $end_time,
                    'requirement' => $requirement,
                    'success_reply' => $success_tip,
                    'failure_reply'=>$failed_tip
                );
                $this->load->model('activity_model','activity');
                $where = 'id_activity = ' . $aid;
                $a_info = $this->activity->get_activity_two($where);
                if(count($a_info) > 0){
                    $aid_two = $this->activity->update_activity_two($where,$data_array);
                }else{
                    $aid_two = $this->activity->insert_activity_two($data_array);
                }

                if($activity_type == 'answer'){
                    if($aid_two > 0){
                        $data['url'] = $this->url.'activity/edit_activity/3/'.$aid.$this->tpl[0];
                        $data['is_refresh'] = 2;
                        $this->returen_status(1, $this->lang->line('edit_data_success'),$data);
                    }else{
                        $this->returen_status(0, $this->lang->line('edit_data_failed'),'');
                    }
                }elseif($activity_type == 'egg'){
                    if($aid_two > 0){
                        $data['url'] = $this->url.'activity/list_activity'.$this->tpl[0];
                        $data['is_refresh'] = 2;
                        $this->returen_status(1, $this->lang->line('edit_data_success'),$data);
                    }else{
                        $this->returen_status(0, $this->lang->line('edit_data_failed'),'');
                    }
                }elseif($activity_type == 'event'){
                    if($aid_two > 0){
                        $data['url'] = $this->url.'activity/list_activity'.$this->tpl[0];
                        $data['is_refresh'] = 2;
                        $this->returen_status(1, $this->lang->line('edit_data_success'),$data);
                    }else{
                        $this->returen_status(0, $this->lang->line('edit_data_failed'),'');
                    }
                }
            }
        }else{
            if(!$page){
                $page = 1;
            }
            if($id){
                $this->load->model('activity_model','activity');
                $aid = intval($id);

                $this->smarty->assign('type','edit');
                $this->smarty->assign('eid',$aid);
                if($page == 1){
                    $mdata = $this->activity->get_activity(array('id_activity'=>$aid,'id_shop'=>$this->id_shop));
                    if(!empty($mdata[0]['id_activity'])){
                        $mdata = $mdata[0];
                    }
                    $this->smarty->assign('md',$mdata);
                    $this->smarty->view('add_activity');
                }elseif($page == 2){
                    $pages = 20;
                    $this->load->model('ticket_model','ticket');
                    //获取电子券信息
                    $where = 'id_shop = '.$this->id_shop.' and id_business = '.$this->id_business;
                    $ticket_info = $this->ticket->get_ticket_introduction($where,1,$pages,'id_eticket desc');

                    //获取活动的类型
                    $where_a = $where . ' and id_activity = '.$aid . ' and state >= 0';
                    $activity_info = $this->activity->get_new_activity($where_a,1);
                    $activity_type = '';
                    if($activity_info){
                        $activity_type = $activity_info[0]['type'];
                    }
                    //获取活动第二步设置信息
                    $where_two = 'id_activity = '.$aid;
                    $activity = $this->activity->get_activity_two($where_two);
                    $activity_infos=array();
                    if($activity){
                        foreach($activity as $k1=>$a){
                            if($a['requirement'] != ''){
                                $requirement = json_decode($a['requirement'],true);
                                $activity[$k1]['requirements'] = $requirement;
                            }
                            if($activity[$k1]['requirements']['eticket'])
                                foreach($activity[$k1]['requirements']['eticket'] as $k2=>$re){
                                    $where_ticket = $where . ' and id_eticket = ' . $re['eticketId'];
                                    $ticket = $this->ticket->get_ticket_introduction($where_ticket);
                                    if($ticket){
                                        $activity[$k1]['ticket'][$k2] = $ticket[0];
                                    }
                                }
                            if($activity[$k1]['requirements']['event'])
                                foreach($activity[$k1]['requirements']['event'] as $k3=>$rev){
                                    if($rev['action'] == 'review' && $rev['reviewObject'] != "" && $rev['obejctId'] != ""){
                                        $this->load->model('wxreply_model','wxreply');
                                        if($rev['reviewObject'] == 'activity'){
                                            $where_event = $where . ' and id_activity = ' . $rev['obejctId'];
                                            $event_name = $this->wxreply->get_activity_inid($where_event,2);
                                            $activity[$k1]['requirements']['event'][$k3]['event_name'] = $event_name[0]['title'];
                                        }elseif($rev['reviewObject'] == 'commodity'){
                                            $where_event = $where . ' and id_commodity = ' . $rev['obejctId'];
                                            $event_name = $this->wxreply->get_commodity_inid($where_event,2);
                                            $activity[$k1]['requirements']['event'][$k3]['event_name'] = $event_name[0]['title'];
                                            $activity[$k1]['requirements']['event'][$k3]['reviewObject'] = 'cmd';
                                        }elseif($rev['reviewObject'] == 'info'){
                                            $where_event = 'a.id_shop = '.$this->id_shop.' and a.id_business = '.$this->id_business .' and a.id_info = ' . $rev['obejctId'];
                                            $event_name = $this->wxreply->get_info_inid($where_event,2);
                                            $activity[$k1]['requirements']['event'][$k3]['event_name'] = $event_name[0]['title'];
                                        }
                                    }
                                }
                        }
                        $activity_infos = $activity[0];
                    }
//                    print_r($activity_infos);
                    $this->smarty->assign('activity',$activity_infos);
                    $this->smarty->assign('ticket_info',$ticket_info);
                    $this->smarty->assign('activity_type',$activity_type);
                    $this->smarty->assign('a_id',$aid);
                    if($activity_type == 'answer'){
                        $this->smarty->assign('page_type','answer_two');
                        $this->smarty->view('answer_two');
                    }elseif($activity_type == 'egg'){
                        $this->smarty->assign('page_type','egg_two');
                        $this->smarty->view('egg_two');
                    }elseif($activity_type == 'event'){
                        //获取图文回复信息
                        $return = $this->img_text_reply();
                        $this->smarty->assign($return);

                        $this->smarty->assign('page_type','event_two');
                        $this->smarty->view('event_two');
                    }
                }elseif($page == 3){
                    $where = 'ts.id_activity = ' . $aid;
                    //活动的题目信息
                    $answer_subject = $this->activity->get_answer_subject($where,0,20,'ts.weight desc',3);
                    $this->smarty->assign('answer_subject',$answer_subject);

                    //活动的页面类型
                    $where ='id_shop = '.$this->id_shop.' and id_business = '.$this->id_business .' and id_activity = '.$aid;
                    $activity_info = $this->activity->get_new_activity($where,1);
                    $activity_type = '';
                    if($activity_info){
                        $activity_type = $activity_info[0]['type'];
                    }
                    $this->smarty->assign('a_id',$aid);
                    $this->smarty->assign('activity_type',$activity_type);

                    if($this->input->post('ispage') == 1){
                        echo $this->smarty->view('lists');
                    }else{
                        $this->smarty->view('answer_three');
                    }
                }
            }
        }
	}
	
	public function del_activity(){
        $id = $this->input->post('id_activity');//活动id
        $id = intval($id);
        $this->load->model('activity_model','activity');

        //获取活动信息
        $where = 'id_activity = ' . $id;
        $activity_i= $this->activity->get_activity($where);
        foreach($activity_i as $ai){
            //获取本地文件地址
            $path = $this->file_url_name('activity',$ai['image_url']);
            if(!file_exists($path) && !is_readable($path)){
            }else{
                unlink($path);//删除附件
            }
        }
        $data['state'] = -1;
        $where_a = 'id_activity = ' . $id;
        $this->activity->edit_activity($where_a,$data);
        $this->list_activity();
	}


    public function list_activity(){
        $this->load->helper('url');
        $this->load->model('activity_model','activity');

        $search_key = $this->input->post('search_key') ? $this->input->post('search_key') : '';
        $offset = $this->input->post('offset') ? intval($this->input->post('offset')) : 1;//页码
        if($search_key){
            $where = 'id_shop = '.$this->id_shop .' and name like \'%'.urldecode($search_key).'%\'';
        }else{
            $where = 'id_shop = '.$this->id_shop;
        }
        $where .= ' and state >= 0';
        $total = $this->activity->get_activity_count($where);

//		$page = empty($page) ? 1 : intval($page);
        $start = ($offset - 1) * $this->config->item('page');
        $order = 'state desc,weight desc';
        $mlist = $this->activity->get_activity_list($where,$start,$this->config->item('page'),$order);
        if(!$mlist){
            $offset = 1;
            $start = ($offset - 1) * $this->config->item('page');
            $mlist = $this->activity->get_activity_list($where,$start,$this->config->item('page'),$order);
        }
        $page_arr = $this->get_page($total,$offset,'activity_list_page');

        foreach($mlist as $k=>$ml){
            $where = 'id_activity = ' . $ml['id_activity'];
            //参与总次数
            $join_count = $this->activity->get_activity_join_count($where);
            $mlist[$k]['join_count'] = $join_count;
            //参与总人数
            $join_person = $this->activity->get_activity_join_count($where,2);
            $mlist[$k]['join_person'] =count($join_person);
            //获奖人数
            $where .= ' and is_winner = 1';
            $award_count = $this->activity->get_activity_join_count($where);
            $mlist[$k]['award_count'] = $award_count;
        }

        $this->smarty->assign('page_html',$page_arr);
        $this->smarty->assign('alist',$mlist);
        $this->smarty->assign('offset',$offset);
        $this->smarty->assign('page_type','activity');

        if($this->input->post('ispage') == 1){
            echo $this->smarty->view('lists');
        }else if($this->input->post('ispage') == 2){
            $data = $this->smarty->fetch('lists.html');
            $resp = array(
                'status' => 1,
                'msg' => '',
                'data' => $data
            );
            die(json_encode($resp));
        }else{
            $this->smarty->view('list_activity');
        }
    }

    /**
     * zxx
     * 添加题目
     */
    function add_answer(){
        $aid = $this->input->post('a_id');//活动id
        $answer_ok = $this->input->post('answer_ok');//正确答案id
        $weight = $this->input->post('weight');//权重
        $title = $this->input->post('title');//题目信息
        $select_infos = $this->input->post('select_infos');//答案信息
        $is_page = $this->input->post('is_page')?$this->input->post('is_page') : 0;//1:是编辑   0:是添加

        $this->load->model('activity_model','activity');
        if($is_page == 1){
            $subject_id = $this->input->post('id_subject');//题目信息id  用以编辑题目及选项信息
            $id_option_e = $this->input->post('id_option_e');//需要删除的选项id

            if($subject_id){
                $this->db->trans_begin();
                if($id_option_e){
                    $id_option_es = explode(',',$id_option_e);
                    if($id_option_es){
                        foreach($id_option_es as $ioe){
                            $where_d = 'id_options = ' . $ioe;
                            //删除选项
                            $id_op = $this->activity->delete_answer_option($where_d);
                        }
                    }
                }

                $id_option_ok = 0;
                //选项信息
                foreach($select_infos as $si){
                    $option_data = array(
                        'id_subject' => $subject_id,
                        'description' => $si,
                    );
                    //添加选项信息
                    $this->activity->insert_answer_option($option_data);
                }

                //题目信息
                $answer_data = array(
                    'title' => $title,
                    'weight' => $weight
                );
                //如果存在变化选中项索引则执行下面操作
//                var_dump($subject_id);
                if($answer_ok){
                    $where_sub = 'id_subject = ' . $subject_id;
                    $answer_op = $this->activity->get_answer_option($where_sub);
                    if($answer_op){
                        $id_option_ok = $answer_op[intval($answer_ok)-1]['id_options'];
                    }
                    $answer_data['id_options'] = $id_option_ok;
                }
                //更新一条题目信息
                $this->activity->update_answer_subject($where_sub,$answer_data);
                if ($this->db->trans_status() === FALSE){
                    $this->db->trans_rollback();
                    $this->returen_status(0, $this->lang->line('add_data_failed'),'');
                }else{
                    $this->db->trans_commit();
                }
                if($this->input->post('eid')){
                    $reutn_url = array(
                        'list_url' => $this->url.'activity/edit_activity/3/'.$aid.$this->tpl[0]
                    );
                }else{
                    $reutn_url = array(
                        'list_url' => $this->url.'activity/add_activity/3/'.$aid.$this->tpl[0]
                    );
                }
                $this->returen_status(1, $this->lang->line('add_data_success'),$reutn_url);
            }else{
                $this->returen_status(0, $this->lang->line('add_data_failed'),'');
            }
        }else{
            //题目信息
            $answer_data = array(
                'id_activity' => $aid,
                'title' => $title,
                'id_options' => 0,
                'weight' => $weight
            );

            $this->db->trans_begin();
            //添加一条题目信息
            $id_subject = $this->activity->insert_answer_subject($answer_data);
            $id_option = 0;
            if($id_subject){
                //选项信息
                foreach($select_infos as $k => $si){
                    $option_data = array(
                        'id_subject' => $id_subject,
                        'description' => $si,
                    );
                    //添加选项信息
                    $id_op = $this->activity->insert_answer_option($option_data);
                    if(($k+1) == intval($answer_ok)){
                        $id_option = $id_op;
                    }
                }
            }else{
                $this->db->trans_rollback();
                $this->returen_status(0, $this->lang->line('add_data_failed'),'');
            }

            $where = 'id_subject = '.$id_subject;
            $data['id_options'] = $id_option;
            //更新题目的正确答案选项id
            $this->activity->update_answer_subject($where,$data);

            if ($this->db->trans_status() === FALSE){
                $this->db->trans_rollback();
                $this->returen_status(0, $this->lang->line('add_data_failed'),'');
            }else{
                $this->db->trans_commit();
            }

            if($this->input->post('eid')){
                $reutn_url = array(
                    'list_url' => $this->url.'activity/edit_activity/3/'.$aid.$this->tpl[0]
                );
            }else{
                $reutn_url = array(
                    'list_url' => $this->url.'activity/add_activity/3/'.$aid.$this->tpl[0]
                );
            }
            $this->returen_status(1, $this->lang->line('add_data_success'),$reutn_url);
        }
    }


    /**
     * zxx
     * 删除题目列表信息
     */
    function delete_answer(){
        $id_subject = $this->input->post('id_subject');//活动id

        $this->load->model('activity_model','activity');
        $where = 'id_subject = ' . $id_subject;
        $this->activity->delete_answer($where);
        $this->activity->delete_answer_option($where);
    }


    /*
     * zxx
     * 查询题目编辑的信息
     * */
    function show_edit_answer(){
        $id_subject = $this->input->post('id_subject');//题目编号id
        $this->load->model('activity_model','activity');
        $where = 'ts.id_subject = ' . $id_subject;
        //活动的题目信息
        $answer_subject = $this->activity->get_answer_subject($where,0,20,'ts.weight desc',2);
        foreach($answer_subject as $k=>$as){
            $answer_de = explode(',',$as['description']);
            $id_option = explode(',',$as['id_option']);
            foreach($answer_de as $k1=>$ad){
                $answer_subject[$k]['desc'][$k1] = $ad;
                $answer_subject[$k]['id_op'][$k1] = $id_option[$k1];
            }
        }
        $this->smarty->assign('answer_subject',$answer_subject[0]);

        $this->smarty->assign('page_type','answer_edit');
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
     * 一站到底排行榜 top30
     * */
    function answer_rank($aid){
        $this->load->model('activity_model','activity');
        $where = 'ar.id_activity = ' . $aid;
        $answer_rank = $this->activity->get_answer_rank($where,1,30,'ar.score desc,ar.consuming asc');

        $this->smarty->assign('answer_rank',$answer_rank);
        $this->smarty->view('answer_ranking');
    }


    /**
     * zxx
     * 判断电子券是否已被其他活动绑定过了
     */
    function verify_ticket(){
        $id_ticket = $this->input->post('id_ticket');//电子券id

        $this->load->model('activity_model','activity');
        $config = $this->activity->get_activity_two();

        $num = 0;
        if($config){
            foreach($config as $kc=>$vc){
                if($vc['requirement'] != ''){
                    $requirement = json_decode($vc['requirement'],true);
                    if($requirement['eticket']){
                        foreach($requirement['eticket'] as $ke=>$ve){
                            if($ve['eticketId'] == $id_ticket){
                                $num = 1;
                                echo $num;
                                return false;
                            }
                        }
                    }
                }
            }
        }
        echo $num;
    }
	
	    /*
     * 发布资源
     * */
    function resources(){
        $this->smarty->view('settlement');
        // $this->smarty->view('add_resources');
//        $this->smarty->view('list_myresources');
//        $this->smarty->view('list_resources');

    }

}


/* End of file activity.php */