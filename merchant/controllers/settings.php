<?php
/**
 * 
 * @copyright(c) 2013-11-25
 * @author msi
 * @version Id:settings.php
 */


class Settings extends Admin_Controller
{
    private $id_business;
    public function __construct()
    {
        parent::__construct();
        $this->id_business = $this->users['id_business'];
//        $this->id_shop = $this->users['id_shop'];

        if(empty($this->users) || empty($this->session_id)){
            header('location:'.$this->url.'user/login');
            die ;
        }
    }

    /*
     * zxx  展示添加总店介绍
     * */
    public function company_intro()
    {
        $data ['url_action'] = 'settings/company_intro';
        if($this->input->post('title'))
        {
            $this->load->library('form_validation');
            if ( $this->form_validation->run('edit_synopsis') === TRUE)
            {
                $title = $this->input->post('title');//企业名
                $content = $this->input->post('attachment');//企业内容
                $phone = $this->input->post('phone');//企业电话
//                $id_business = $this->users['id_business'];//企业id

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
                $wherea = 'id_object = '.$this->id_business.' and object_type = \'bn\' and id_business = '.$this->id_business;
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
                                    'id_shop' =>  $this->users['id_shop'],
                                    'object_type' => 'bn',
                                    'id_object' => $this->id_business,
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
                                        'id_shop' =>  $this->users['id_shop'],
                                        'object_type' => 'bn',
                                        'id_object' => $this->id_business,
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
                                    $path = $this->file_url_name('attachment',$filename);
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
                                $path = $this->file_url_name('attachment',$filename);
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

                $firm_img_name = array();
                $firm_img_size = array();
                $image_src = $this->input->post('image_src');
                if( trim($image_src) ){//图片
                    $data_image_src = explode(',',$image_src);
                    foreach($data_image_src as $k=>$dis){
                        //图片文件保存处理
                        $firm_img_name[$k] = $dis;
                        //获取本地文件地址
                        $path = $this->file_url_name('attachment',$dis);
                        //获取本地文件大小
                        $firm_img_size[$k] = sprintf("%.1f", (filesize($path)/1024));
                    }
                }

                $image_id = $this->input->post('image_id');
                if(trim($image_id)){
                    $data_image_id = explode(',',$image_id);
                    foreach($data_image_id as $ii){
                        //企业图片删除处理
                        if($ii){
                            $where = array('id_attachment'=>$ii);
                            $return = $this->business->get_business_attachment($where);
                            if($return){
                                foreach($return as $val){
                                    //获取本地文件地址
                                    $path = $this->file_url_name('attachment',$val['image_url']);
                                    if(!file_exists($path) && !is_readable($path)){
//                                        $this->returen_status(0,$val['image_url'].'文件不在或只有只读权限~~',$data);
                                    }else{
                                        unlink($path);
                                    }
                                    $this->business->delete_synopsis_image($where);
                                }
                            }
                        }
                    }
                }

                $logo_src = $this->input->post('logo_src');//图片上传后的文件名
                $receive_mode = $this->input->post('receive_mode');//会员卡领取方式
                $title = $this->replace_html($title);
                $synopsis_info = array(
                    'id_business' => $this->id_business,
                    'name' => $title,
                    'introduction' => $content,
                    'contact_number' => $phone,
                    'receive_mode' =>$receive_mode
                );

                if(count($firm_img_name) > 0){
                    $synopsis_info['img_url'] = $firm_img_name;//图片名
                    $synopsis_info['size'] = $firm_img_size;//上传图片大小
                }
//                    if($return_logo != ''){
//                        $synopsis_info['logo_url'] = $return_logo['file_name'];
//                    }
                if(trim($logo_src)){
                    $synopsis_info['logo_url'] = $logo_src;
                }
                $where = array('id_business' => $this->id_business);
//                var_dump($synopsis_info);
//                die;
                $b = $this->business->update_synopsis($synopsis_info,$where);
                $this->refresh_page();
                $this->returen_status(1,$this->lang->line('edit_data_success'),$data);
            }
            else
            {
                $this->returen_status(0,$this->lang->line('edit_data_failed'),$data);
            }
        }else{
            $this->load->model('business_model','business');
            //通过总店id查询企业信息
            $where = 'ba.object_type = \'bn\' and ba.id_object = 0';
            $businessInfo = $this->business->get_business_info($this->id_business,$where);

            $data['businessInfo'] = $businessInfo[0];
            if($data['businessInfo']['id_attachment']){
                $data['businessInfo']['id_attachment'] = explode(',',$businessInfo[0]['id_attachment']);
            }
            if($data['businessInfo']['image_url']){
                $data['businessInfo']['image_url'] = explode(',',$businessInfo[0]['image_url']);
            }
            $data['image_url_count'] = $data['businessInfo']['image_url'] == '' ? 0 : count($data['businessInfo']['image_url']);
            $data['logo_count'] = $data['businessInfo']['logo'] == '' ? 0 : count($data['businessInfo']['logo']);

            if($data['image_url_count'] > 5){
                foreach($data['businessInfo']['id_attachment'] as $k=>$ia){
                    if($k > 4){
                        $where = 'id_attachment = ' . $ia;
                        $this->business->delete_synopsis_image($where);
                    }
                }
            }

            $this->smarty->assign($data);

            $this->users['biz_name'] = $businessInfo[0]['name'];
            $current['business_name'] = $this->users['biz_name'];

            $this->smarty->view('synopsis');
        }
    }

    /**
     * zxx
     * 编辑门店
     */
    public function shop_intro($id_shop=0,$type=0){
//        $merchent['biz_mod'] = 1;//独占模式
//        $merchent['biz_mod'] = 0;//共享模式
        $data ['url_action'] = 'settings/shop_intro';

        if(!$id_shop){
            $id_shop = $this->users['id_shop'];//企业id
        }

        if($this->input->post('title'))
        {
            $this->load->library('form_validation');
            if ( $this->form_validation->run('edit_shop') === TRUE)
            {
                $title = $this->input->post('title');//门店名
                $content = $this->input->post('content');//门店内容
                $phone_names = $this->input->post('phone_name');//门店联系方式名
                $phones = $this->input->post('phone');//门店联系方式号码
                $latitude = $this->input->post('latitude');//企业纬度
                $longitude = $this->input->post('longitude');//企业经度
                $image_src = $this->input->post('image_src');//门店图片

                $delcontact = $this->input->post('delcontact'); //需要删除的联系方式信息
                $address = $this->input->post('address'); //门店地址
                $baidu_location = $this->input->post('baidu_location'); //导航地址

                $id_shop = $this->input->post('id_shop'); //门店id

                $id_business = $this->id_business;

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

                $wherea = 'id_object = '.$id_shop.' and object_type = \'shop\' and id_business = '.$this->id_business . ' and id_shop = '.$id_shop;
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
                                    'id_shop' =>  $id_shop,
                                    'object_type' => 'shop',
                                    'id_object' => $id_shop,
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
                                        'id_shop' =>  $id_shop,
                                        'object_type' => 'shop',
                                        'id_object' => $id_shop,
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
                                    $path = $this->file_url_name('shop',$filename);
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
                                $path = $this->file_url_name('shop',$filename);
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

                $phone_name = explode(',',$phone_names);
                $phone = explode(',',$phones);

                foreach($phone_name as $key=>$pns){
                    if(trim($pns) == '' or trim($phone[$key]) == ''){
                        unset($phone_name[$key]);
                        unset($phone[$key]);
                    }
                }
                $phone_name = array_values($phone_name);//索引重置
                $phone = array_values($phone);//索引重置

                //将页面的多联系方式传入array
                $contact = array();
                if($phone_name){
                    foreach($phone_name as $k=>$pn){
                        $contact[$k][0] = $pn;
                        $contact[$k][1] = $phone[$k];
                    }
                }

                $contact_num = array();
                $this->load->model('shop_model','shop');
                $where = 'id_business = '.$this->id_business . ' and id_shop = '.$id_shop;
                $shop_contact = $this->shop->get_shop_phone($where);//查询门店的多联系信息
                $contact_num = json_decode($shop_contact[0]['contact']);//将查询出来的联系方式信息（json）转为array

                if( trim($delcontact) != ''){
                    $delete_keyid = explode(',',$delcontact);
                    foreach($contact_num as $k=>$cn){
                        foreach($delete_keyid as $dki){
                            if(intval($dki) == $k && $dki != ''){
                                unset($contact_num[$k]);//处理删除某联系方式逻辑
                            }
                        }
                    }
                }
                if($contact_num){
                    foreach($contact_num as $cn){
                        array_push($contact,$cn);//将删除后
                    }
                }

                $title = $this->replace_html($title);
                $shop_info = array(
                    'id_shop' => $id_shop,
                    'name' => $title,
                    'introduction' => $content,
                    'contact' => json_encode($contact),
                    'address' => $address,
                    'baidu_location' => $baidu_location,
                    'latitude' => $latitude,
                    'longitude' => $longitude
                );

                if(trim($image_src)){
                    $shop_info['image_url'] = $image_src;
                }

//                //修改企业名的缓存
//                $cache_user = $this->get_session_data($this->session_id);
//                $cache_user['shop_name'] = $title;
//                $this->cache_data($this->session_id, $cache_user,TRUE);
//                $this->page_data['user']['shop_name'] = $title;
//                //end

                $where = array('id_shop' => $id_shop);
                $b = $this->shop->update_shop($shop_info,$where);
                $this->refresh_page();
                $this->returen_status(1,$this->lang->line('edit_data_success'),$data);
            }else{
                $this->returen_status(0,$this->lang->line('edit_data_failed'),$data);
            }
        }else
        {
            if(intval($type) > 0){
                $where = 'id_shop = '. $id_shop;
                $datas =  $this->get_shop($where);

                if($datas['shopInfo'][0]['image_url'] != '' || !empty($datas['shopInfo'][0]['image_url'])){
                    $datas['shopInfo'][0]['img_count'] = 1;
                }else{
                    $datas['shopInfo'][0]['img_count'] = 0;
                }
                $data['shopInfo'] = $datas['shopInfo'][0];

                $this->smarty->assign($data);
                $this->smarty->view('edit_shop');
            }else{
                if(in_array($this->id_business,$this->config->item('BUSINESS_ID')) && $this->users['is_shop'] == 0){
                    $this->list_shop();
                }else
                    if($this->users['biz_mod'] != 0){
                        $where = 'id_shop = '. $id_shop;
                        $datas =  $this->get_shop($where);

                        if($datas['shopInfo'][0]['image_url'] != '' || !empty($datas['shopInfo'][0]['image_url'])){
                            $datas['shopInfo'][0]['img_count'] = 1;
                        }else{
                            $datas['shopInfo'][0]['img_count'] = 0;
                        }
                        $data['shopInfo'] = $datas['shopInfo'][0];

                        $this->smarty->assign($data);
                        $this->smarty->view('edit_shop');
                    }else{
                        //门店共享。调用列表页，并且不显示门店选择列
                        $this->list_shop();
                    }
            }
        }
    }

    //zxx 门店列表页
    public function list_shop(){
//        $id_business = $this->users['id_business'];//企业id
        $where = 'id_business = '. $this->id_business;
        $data =  $this->get_shop($where,1);

        $this->smarty->assign($data);
        $this->smarty->view('list_shops');
    }

    //zxx 编辑门店
    function edit_shoplist($id_shop){
        $where = 'id_shop = '. $id_shop;
        $data =  $this->get_shop($where);
        $data ['url_action'] = 'settings/shop_intro/'.$id_shop.$this->tpl[0];
        $data['shopInfo'] = $data['shopInfo'][0];

        $this->smarty->assign($data);
        $this->smarty->view('edit_shop');
    }


    //$type 0:编辑页  1：列表页(过滤标签的)
    function get_shop($where,$type=0){
        $this->load->model('shop_model','shop');
        $shopInfo = $this->shop->get_shop_introduction($where);
        foreach($shopInfo as $k=>$si){
            $shopInfo[$k]['contact'] = json_decode($shopInfo[$k]['contact']);
            $shopInfo[$k]['contact_count'] = count($shopInfo[$k]['contact']);
            if($type == 1){
                $shopInfo[$k]['introduction'] = strip_tags(html_entity_decode($shopInfo[$k]['introduction'],ENT_COMPAT,'utf-8'));
            }
        }
        $data['shopInfo'] = $shopInfo;

        return $data;
    }

    /*
     * zxx  删除总店图片
     * */
    public function delete_synopsis_img($id_attachment)
    {
        $where = array('id_attachment'=>$id_attachment);
        $this->load->model('business_model','business');

        //获取企业图片附件
        $attachemnt_i= $this->business->get_business_attachment($where);
        foreach($attachemnt_i as $ai){
            //获取本地文件地址
            $path = $this->file_url_name('attachment',$ai['image_url']);
            if(!file_exists($path) && !is_readable($path)){
            }else{
                unlink($path);//删除附件
            }
        }

        $res = $this->business->delete_synopsis_image($where);

        echo json_encode(array('result'=> $res));
        exit;
    }

    public function upload_all_file(){
        $uploaddir = DOCUMENT_ROOT . '/attachment/firm/';
        //编译文件名称 base64编码
        $fileName = base64_encode(pathinfo($_FILES['uploadfile']['name'], PATHINFO_BASENAME)) .
            '.' . pathinfo($_FILES['uploadfile']['name'], PATHINFO_EXTENSION);

        $file = $uploaddir . basename($fileName);
//        $size=$_FILES['uploadfile']['size'];
//        if($size>1048576)
//        {
//            echo "error file size > 1 MB";
//            unlink($_FILES['uploadfile']['tmp_name']);
//            exit;
//        }
        if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $file)) {
            echo $fileName;
        } else {
            //echo "error ".$_FILES['uploadfile']['error']." --- ".$_FILES['uploadfile']['tmp_name']." %%% ".$file."($size)";
            echo $fileName;
        }
    }
    
    /**
     * zxx
     * 绑定帐号 支付宝帐号或银行卡号
     **/
    public function bind_account()
    {
        $this->load->model('business_model','business');
        $data = array();

//        $id_business = $this->users['id_business'];//企业id
        $where = 'id_business = ' . $this->id_business;

        if($this->input->post()){
            $type = $this->input->post('type');//绑定类型
            $account = $this->input->post('account');//绑定类型
            $open_bank = $this->input->post('open_bank');//开户银行名称
            $card_account = $this->input->post('card_account');//银行开户卡号

            $update_data = array(
                'type'=>$type
            );
            if($open_bank){
                $update_data['open_bank'] = $open_bank;
            }
            if($account){
                $update_data['card_account'] = $account;
            }elseif($card_account){
                $update_data['card_account'] = $card_account;
            }
            $this->business->update_business($update_data, $where);
        }

        $business_info = $this->business->get_business_phone($where);
        $data['business_info'] = $business_info[0];

        $this->smarty->assign($data);
        $this->smarty->display('setting-bind-account.html');
    }
    
}


/* End of file settings.php */