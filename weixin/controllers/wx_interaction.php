<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Wx_interaction extends WX_Controller {

    public function index()
    {
    	$echoStr = $_GET["echostr"];

    	if ($echoStr) {
    		$this->valid();
    	}else{
    		$this->response_msg();
    	}
    }


    public function valid() {
        $echoStr = $_GET["echostr"];
        //valid signature , option
        if ($this->checkSignature()) {
            echo $echoStr;
            exit;
        }
    }

    private function checkSignature() {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $cache_data = $this->get_cache_data(md5('business'.$this->bid.$this->sid));
        if($cache_data){
            $token = $cache_data['token'];
        }
//        $token = TOKEN;
        $tmpArr = array (
            $token,
            $timestamp,
            $nonce
        );
        sort($tmpArr,SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }


    /*
     * zxx 返回门店和总店名字和电话信息   一键预订
     * */
    public function get_phones()
    {
        //总店
        $this->load->model('business_model','business');
        $where = 'id_business = '.$this->bid;
        $business_number = $this->business->get_business_phone($where);

        $html = '';
        if($business_number){
            if($business_number[0]['contact_number'] && $business_number[0]['name']){
                $html .= $business_number[0]['name'].':'.$business_number[0]['contact_number'];
            }
            //分店
            $this->load->model('shop_model','shop');
            if($this->type){
                $where = 'id_business = '.$business_number[0]['id_business'].' AND id_shop='.$this->sid;
            }else{
                $where = 'id_business = '.$business_number[0]['id_business'];
            }
            $shop_number = $this->shop->get_shop_phone($where);
            if($shop_number){
                foreach($shop_number as $bn) {
                    $contact = json_decode($bn['contact']);
                    if($contact && $contact[0][1]){
//                        $html .= $bn['name'].':';
                        foreach($contact as $con) {
                            if($con[1] && $con[0]){
                                if($html != ''){
                                    $html .= "\r\n";
                                }
                                $html .= ' '.$con[0].':'.$con[1];
                            }
                        }
                    }else{
                        $html .= '无联系方式！';
                    }
                }
			}
            return $html;
            exit;
        }
        $html .= 'Not Message！';
        return $html;
    }


    /*
     * $shop_id 门店id
     * zxx 返回门店信息,门店展示  我的位置
     * */
    public function get_infos($where)
    {
        //分店信息
        $this->load->model('shop_model','shop');
        $shop_number = $this->shop->get_shop_introduction($where);
        return $shop_number;
    }

    /*
     * $shop_id 门店id
     * zxx 返回最新活动  最新活动
     * */
    public function get_new_activitys($where)
    {
        //分店信息
        $this->load->model('activity_model','activity');
        $where .=  ' and state = 1';
        $order = 'weight desc';
        $activity_news = $this->activity->get_new_activity($where,10,$order);
        return $activity_news;
    }

    /*
     * $business_id 企业id
     * zxx 返回企业介绍信息   企业简介
     * */
    public function get_business_infos($business_id)
    {
        //企业介绍信息
        $this->load->model('business_model','business');
        $business_infos = $this->business->get_business_info($business_id);
        $img_url = explode(',',$business_infos[0]['image_url']);
        $business_infos[0]['image_url'] = $img_url;
        return $business_infos;
    }

    public function get_member_card($where){
    	$this->load->model('customer_model','customer');
    	return $this->customer->get_member_card_by_openid($where);
    }


    /**
     * PHP 过滤HTML代码空格,回车换行符的函数
     * echo deletehtml()
     */
    function deletehtml($str)
    {
        $str = trim($str);
        $str=strip_tags($str,"");
        $str=preg_replace("{\t}","",$str);
        $str=preg_replace("{\r\n}","",$str);
        $str=preg_replace("{\r}","",$str);
        $str=preg_replace("{\n}","",$str);
        $str=preg_replace("{ }","",$str);
        return $str;
    }

    /*
   * $id_info 内容id
   * $type info :文章详细  newinfo :最新文章
   * zxx 返回内容。
   * */
    public function get_contents($where,$type)
    {
        //分店信息
        $this->load->model('info_model','info');
        $info_details = array();
        if($type == 'info'){
            $info_details = $this->info->get_info_introduction($where,0,0,'weight desc');//获取文章信息
            $where .= ' and type = \'image\'';
            $info_attachment = $this->info->get_info_attachment($where,1);//获取文章附件信息
            $info_details[0]['name'] = $info_details[0]['title'];
//            $info_details[0]['introduction'] = strip_tags(html_entity_decode($info_details[0]['content'],ENT_COMPAT,'utf-8'));

            $content_info = strip_tags(html_entity_decode($info_details[0]['content'] ,ENT_COMPAT,'utf-8'));
            $a = $this->deletehtml($content_info);
            $info_details[0]['introduction'] = $a;

            unset($info_details[0]['title']);
            unset($info_details[0]['content']);

            $info_details[0]['image_url'] = '/attachment/defaultimg/bg_mr.png';
            if(count($info_attachment) > 0){
                $info_details[0]['image_url'] = $info_attachment[0]['show_url'];
            }
        }else{
            $info_details = $this->info->get_info_introduction($where,1,10,'weight desc');//获取文章信息
            foreach($info_details as $key=>$value){
                $where = 'id_info = '.$value['id_info'].' and type = \'image\'';
                $info_attachment = $this->info->get_info_attachment($where,1);//获取文章附件信息
                $info_details[$key]['name'] = $info_details[$key]['title'];
//                $info_details[$key]['introduction'] =  strip_tags(html_entity_decode($info_details[$key]['content'],ENT_COMPAT,'utf-8'));

                $content_info = strip_tags(html_entity_decode($info_details[$key]['content'],ENT_COMPAT,'utf-8'));
                $a = $this->deletehtml($content_info);
                $info_details[$key]['introduction'] = $a;

                unset($info_details[$key]['title']);
                unset($info_details[$key]['content']);

                $info_details[$key]['image_url'] = '/attachment/defaultimg/bg_mr.png';
                if(count($info_attachment) > 0){
                    $info_details[$key]['image_url'] = $info_attachment[0]['show_url'];
                }
            }
        }
        return $info_details;
    }

    /*
     * zxx 微信信息返回
     * **/
    public function response_msg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty ($postStr))
        {
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = strval($postObj->FromUserName);
            $toUsername = strval($postObj->ToUserName);
            $keyword = trim($postObj->Content);
            $time = time();
            $returnMsgType = "text";
            $msgText = "";//返回的消息
            //返回信息的xml格式内容
            $textTpl = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[%s]]></MsgType>
                <Content><![CDATA[%s]]></Content>
                </xml>";
            $MsgType = $postObj->MsgType;//消息类型
            //保存文本消息到数据库
            $add_data = array();
            $add_data['id_open'] = strval($fromUsername);
            $add_data['id_business'] = $this->bid;
            $add_data['id_shop'] = $this->sid;
            $add_data['msg_type'] = strtolower(strval($MsgType));
            if(strtolower($MsgType)=='event')
            {
                $MsgEvent = $postObj->Event;//获取事件类型
                if (strtolower($MsgEvent)=='subscribe')
                {
                	$submsg = $this->get_subscribe_msg($fromUsername,$toUsername);
                    //订阅事件
                    echo $submsg;
                    //取得用户信息
                    $this->get_weixin_info($fromUsername);

                    //查看是否有关注获得电子券的信息
                    $result = $this->event_chance('subscribe',$fromUsername);
                    file_put_contents(DOCUMENT_ROOT.'/a.txt',var_export('result:----',TRUE),FILE_APPEND);
                    file_put_contents(DOCUMENT_ROOT.'/a.txt',var_export($result,TRUE),FILE_APPEND);
                    $result_array = json_decode($result,true);
                    $count = count($result_array['data']);
                    $html = '';
                    if($result_array['data']){
                        $html = $result_array['msg'] . '\r\n';
                        foreach($result_array['data'] as $kra=>$vra){
                            $html .= '  '.$vra['name'] . ' X 1\r\n';
//                            if($kra != 0 && ($kra+1) <= count($result_array['data'])){
//                                $html .= ',';
//                            }
                        }
                        $html .= '共'.$count . '张电子券！';
                    }

                    //发送被动消息
                    $access_token = $this->get_access_token($this->bid,$this->sid);
                    file_put_contents(DOCUMENT_ROOT.'/a.txt',var_export('$access_token:----',TRUE),FILE_APPEND);
                    file_put_contents(DOCUMENT_ROOT.'/a.txt',var_export($access_token,TRUE),FILE_APPEND);
                    $msgTxt = '{
                        "touser":"'.$fromUsername.'",
                        "msgtype":"text",
                        "text":
                        {
                             "content":"'.$html.'"
                        }
                    }';
                    if($access_token){
                        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $access_token;
                        $result = request_curl($url,$msgTxt);
                        $result = json_decode($result,1);
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
                }elseif (strtolower($MsgEvent)=='unsubscribe'){
                	//更新取消关注的相关状态
                	$this->unsubscribe($fromUsername);
                }else if(strtolower($MsgEvent)=='click')
                {
                    $textTpl = "<xml>
                             <ToUserName><![CDATA[".$fromUsername."]]></ToUserName>
                             <FromUserName><![CDATA[".$toUsername."]]></FromUserName>
                             <CreateTime>".$time."</CreateTime>";

                    //点击事件
                    $eventClickType = $postObj->EventKey;//菜单的自定义的key值
                    $eventClickType_content = explode('-',$eventClickType);
                    if($eventClickType == 'v01_02')
                    {//文本  一键预订
                        $infos = $this->get_phones();
                        $textTpl .= "<MsgType><![CDATA[text]]></MsgType>
                                <Content><![CDATA[".$infos."]]></Content>";
                    }
                    elseif($eventClickType == 'v01_01' || $eventClickType == 'v02_01' || $eventClickType_content[0] == 'v04_01' || $eventClickType_content[0] == 'v04_02')
                    {//图文   门店展示  最新活动  文章详细  最新文章
                        $where = '';
                        if($this->type){
                            $where = 'id_shop = '.$this->sid .' and id_business = '.$this->bid;
                        }else{
                            $where = 'id_business = '.$this->bid;
                        }
                        $imgtype = '';
                        if($eventClickType == 'v01_01') {//门店展示
                            $infos = $this->get_infos($where);
                            $imgtype = 'shop';
                        }
                        elseif($eventClickType_content[0] == 'v04_01') {//文章详细
                            $id_info = $eventClickType_content[1];
                            $where = 'id_info = '.$id_info;
                            $infos = $this->get_contents($where,'info');
                            $imgtype = 'content';
                        }
                        elseif($eventClickType_content[0] == 'v04_02') {//最新文章
                            $id_class = $eventClickType_content[1];
                            if($this->type){
                                $where = 'i.id_shop = '.$this->sid .' and i.id_business = '.$this->bid;
                            }else{
                                $where = 'i.id_business = '.$this->bid;
                            }
                            $where .= ' and i.id_class = '.$id_class.' and i.state=1';
                            $infos = $this->get_contents($where,'newinfo');
                            $imgtype = 'content';
                        }
                        else {//最新活动
                            $infos = $this->get_new_activitys($where);
                            $imgtype = 'activity';
                        }
                        $textTpl .= "<MsgType><![CDATA[news]]></MsgType>
                             <ArticleCount>".count($infos)."</ArticleCount>
                             <Articles>";
                        foreach($infos as $is)
                        {
                            $image_url = strpos($is['image_url'],'http');
                            if($image_url === false){
                                $image_url = get_img_url($is['image_url'],$imgtype,0,'bg_mr.png');
                            }else{
                                $image_url = $is['image_url'];
                            }
                            $textTpl .= "<item>
                                 <Title><![CDATA[".str_replace('%','',$is['name'])."]]></Title>
                                 <Description><![CDATA[".substr(str_replace('%','',str_replace('&nbsp;','',strip_tags(html_entity_decode($is['introduction'],ENT_COMPAT,'utf-8')))),0,50)."]]></Description>
                                 <PicUrl><![CDATA[".$image_url."]]></PicUrl>";
                            if($eventClickType == 'v01_01') {
                                $textTpl .= "<Url><![CDATA[http://".$_SERVER['HTTP_HOST']."/wapi/".$this->bid."/".$this->sid."/home/about_shop]]></Url>";
                            }
                            elseif($eventClickType_content[0] == 'v04_01' || $eventClickType_content[0] == 'v04_02') {
                                $textTpl .= "<Url><![CDATA[http://".$_SERVER['HTTP_HOST']."/wapi/".$this->bid."/".$this->sid."/home/content/".$is['id_info']."]]></Url>";
                            }
                            else{
                                if($is['type'] == 'answer'){
                                    $textTpl .= "<Url><![CDATA[http://".$_SERVER['HTTP_HOST']."/wapi/".$this->bid."/".$this->sid."/activity/one_stop?aid=".$is['id_activity']."&oid=".strval($fromUsername)."]]></Url>";
                                }elseif($is['type'] == 'egg'){
                                    $textTpl .= "<Url><![CDATA[http://".$_SERVER['HTTP_HOST']."/wapi/".$this->bid."/".$this->sid."/activity/egg?aid=".$is['id_activity']."&oid=".strval($fromUsername)."]]></Url>";
                                }else if($is['type'] == 'event'){
                                    $textTpl .= "<Url><![CDATA[http://".$_SERVER['HTTP_HOST']."/wapi/".$this->bid."/".$this->sid."/home/active_details/".$is['id_activity']."]]></Url>";
                                }
                            }
                             $textTpl .= "</item>";
                        }
                        $textTpl .= "</Articles>";
                    }elseif ($eventClickType == 'v03_01'){//会员卡信息
                    	$textTpl .= "<MsgType><![CDATA[news]]></MsgType>
                             <ArticleCount>1</ArticleCount>
                             <Articles><item><Title><![CDATA[会员卡信息]]></Title>";
                    	$openid = strval($fromUsername);
                    	$card = $this->get_member_card(array('id_open'=>$openid));
                        $img_url = 'b_1000.png';
                        $img_url = 'b_'.$this->bid.'.png';
                        if($this->bid == '1009'){
                            $img_url = 'b_'.$this->bid.'2.png';
                        }
                    	if($card[0]){
                    		//已经领取会员卡
                    		$textTpl .= "<Description><![CDATA[您已领取会员卡，点击查看详情]]></Description>
                                 <PicUrl><![CDATA[http://".$_SERVER['HTTP_HOST']."/wapi/img/".$img_url."]]></PicUrl>";
		                    $textTpl .= "<Url><![CDATA[http://".$_SERVER['HTTP_HOST']."/wapi/".$this->bid."/".$this->sid."/home/member_show/".$card[0]['id_customer']."]]></Url>";
                    	}else{
                    		//没有领取会员卡
//                    		$biz_cache = $this->get_cache_data(md5('business'.$this->bid.$this->sid));
//                    		$appid = $biz_cache['appid'];
                    		$textTpl .= "<Description><![CDATA[您还没有领取会员卡，点击立即领取]]></Description>
                                 <PicUrl><![CDATA[http://".$_SERVER['HTTP_HOST']."/wapi/img/".$img_url."]]></PicUrl>";
                    		$textTpl .= "<Url><![CDATA[http://".$_SERVER['HTTP_HOST']."/wapi/".$this->bid."/".$this->sid."/home/member_show/?openid=".$openid."]]></Url>";
                    	}
                    	$textTpl .= "</item>";
                    	$textTpl .= "</Articles>";

                    }elseif($eventClickType_content[0] == 'v05_01'){// 发送文本给用户
                        $content = explode('v05_01-',$eventClickType);
                        $textTpl .= "<MsgType><![CDATA[text]]></MsgType>
                                <Content><![CDATA[".$content[1]."]]></Content>";
                    }elseif($eventClickType == 'v05_02'){//V社区
//                        $url ="http://".$_SERVER['HTTP_HOST']."/wapi/".$this->bid."/".$this->sid."/community/home?oid=".strval($fromUsername);//候总确认
                        $url ="http://".$_SERVER['HTTP_HOST']."/wapi/".$this->bid."/0/community/home";//?oid=".strval($fromUsername);
                        $content = '<a href="'.$url.'">欢迎来到V社区.点击进入</a>';

                        $textTpl .= "<MsgType><![CDATA[text]]></MsgType>
                                <Content><![CDATA[".$content."]]></Content>";
                    }elseif($eventClickType == 'v05_03'){//我的口袋
//                        $this->load->model('ticket_model','ticket');
//                        $where = 'ti.id_shop = '.$this->sid.' and ti.id_business = '.$this->bid . ' and ti.state = 1 and ti.object_type = \'eticket\' and ti.id_open = \''.strval($fromUsername).'\' and t.valid_end >= \'' . date('Y-m-d H:m:s',time()) . '\'';
//                        $ticket_count = $this->ticket->get_ticket_item_count($where);

                        $url = "http://".$_SERVER['HTTP_HOST']."/wapi/".$this->bid."/".$this->sid."/activity/gifts?ooid=".strval($fromUsername);
                        $content = '<a href="'.$url.'">欢迎来到我的口袋，点击进入</a>';

                        $textTpl .= "<MsgType><![CDATA[text]]></MsgType>
                                <Content><![CDATA[".$content."]]></Content>";
                    }
                    $textTpl .= "</xml>";
//                file_put_contents(DOCUMENT_ROOT.'/vs.txt',var_export($textTpl,TRUE));
                    $resultStr = sprintf($textTpl);
                    echo $resultStr;
                    $this->analysis_click(strtolower($eventClickType));
                    exit;
                }
            }elseif (strtolower($MsgType) == 'text'){//文本消息
            	$sendmsg = '';
            	$fromUsername = strval($fromUsername);
//            	$keyword = strip_tags($keyword);
            	//查询like匹配关键字库有无相关规则，有则根据类型回复，否则回复默认字符并记录用户发送的消息
            	$sendmsg = $this->get_wxmsg_by_rule($fromUsername,$toUsername,$keyword);

                //保存文本信息
                $add_data['msg_content'] = strval($keyword);
//                $add_data['msg_content'] = $this->replace_emoticons($content_);
                if($add_data['msg_content']){
                    $this->save_user_msg($add_data);
                }
            	//关键字不匹配，调用默认回复
            	if(empty($sendmsg)){
            		$sendmsg = $this->get_subscribe_msg($fromUsername,$toUsername,'defaultreply');
            	}
            	if($sendmsg){
            		echo $sendmsg;
            	}
            	exit;
            }
            else
            {
            	$msgText = $this->get_subscribe_msg($fromUsername,$toUsername,'defaultreply');
            }
            if(strtolower($MsgType) == 'text'){
            	//保存文本信息
//            	$add_data['msg_content'] = strval($keyword);
            }else{
            	if($add_data['msg_type'] == 'voice'){
            		$add_data['msg_type'] = 'audio';
            	}
            	$add_data['msg_content'] = $this->get_wx_media($postObj);
            }
            if($add_data['msg_content']){
            	$this->save_user_msg($add_data);
            }
            
            if (!empty ($keyword))
            {
                $contentStr = $keyword . "__O(∩_∩)O哈哈~";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $returnMsgType, $contentStr);
                echo $resultStr;
            }
            else
            {
                $resultStr = $msgText; //sprintf($textTpl, $fromUsername, $toUsername, $time, $returnMsgType, $msgText);
                echo $resultStr;
            }
        }
        else
        {
            echo "no no no!!";
            exit;
        }
    }

    //保存用户发送给服务号的文本信息
    public function save_user_msg($add_data){
    	$this->load->model('customer_model','customer');
    	$this->customer->add_wx_msg($add_data);
    }

    public function get_wx_media($postObj){
    	$token = $this->get_access_token();
    	if($token && $postObj->MediaId){
    		
    		$file_name = md5($postObj->MsgId);
    		$type = '';
    		if(strtolower($postObj->MsgType) == 'voice'){
    			$get_url = 'http://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$token.'&media_id='.$postObj->MediaId;
    			$type = $postObj->Format;
    		}elseif (strtolower($postObj->MsgType) == 'image'){
    			$get_url = $postObj->PicUrl;
    			$type = 'jpg';
    		}elseif (strtolower($postObj->MsgType) == 'video'){
    			$get_url = 'http://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$token.'&media_id='.$postObj->MediaId;
    			$type = 'mp4';
    		}elseif (strtolower($postObj->MsgType) == 'text'){
                $get_url = 'http://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$token.'&media_id='.$postObj->MediaId;
                $type = 'mp4';
            }
    		$wx_media = request_curl($get_url);
    		
    		$path = DOCUMENT_ROOT.'/attachment/weixin/';
    		$cdir = str_split(strtolower($file_name),1);
    		$tmp = array_chunk($cdir, 4);
    		if($tmp[0]){
    			$dir = implode('/', $tmp[0]);
    		}
    		$path .= $dir.'/';
    		if( ! is_dir($path)) {
    			mkdir($path,0777,true);
    		}
    		if(file_put_contents($path.$file_name.'.'.$type, $wx_media)){
    			return $file_name.'.'.$type;
    		}
    	}
    	return false;
    }

    public function get_subscribe_msg($from,$to,$type='defaultsubscribe'){
//    	$to = strval($to);
//    	$from = strval($from);
    	$send_msg = '';
    	$this->load->model('wxreply_model','wxreply');

    	//2.根据商家id,门店id获取关注事件的消息
    	if($this->bid){
    		$sub_where['id_business'] = $this->bid;
    		$sub_where['id_shop'] = $this->sid;
    		$sub_where['msg_type'] = 'event';
    		$sub_where['keyword'] = $type;
    		$wxmsg = $this->wxreply->get_biz_wxseting($sub_where,1);

    		if(!empty($wxmsg[0]['reply_type'])){

    			//3.根据关注事件的消息类型，回复相应的信息
    			if($wxmsg[0]['reply_type'] == 'text'){
                    $wxmsg[0]['reply'] = $this->get_wx_emoticons($wxmsg[0]['reply']);
    				//回复文本
					$html = $wxmsg[0]['reply'];//strip_tags($wxmsg[0]['reply']);
    				$send_msg = $this->send_text_msg($to, $from, $html);//
    			}elseif($wxmsg[0]['reply_type'] == 'image-text'){
                    //回复图文
                    if($wxmsg[0]['id_msg']){
                        $data = array();
                        //1.附件表查出对应的id
                        $att_arr = $this->wxreply->get_msg_attach(array('id_msg'=>$wxmsg[0]['id_msg']),10);

                        $att_type = '';
                        if($att_arr){
                            $commodity = $info = $activity = array();
                            foreach ($att_arr as $k1 => $v1){
                                if($v1['object_type'] == 'commodity'){
                                    $commodity[] = $v1['id_object'];
                                    $att_type = 'commodity';
                                }elseif($v1['object_type'] == 'info'){
                                    $info[] = $v1['id_object'];
                                    $att_type = 'info';
                                }else {
                                    $activity[] = $v1['id_object'];
                                   $att_type = 'activity';
                                }
                            }
                        }

                        $wx_url = 'http://'.$_SERVER['HTTP_HOST'].'/wapi/'.$this->bid.'/'.$this->sid;
                        //2.根据对应的类型，去查出相应的信息(标题，图片，描述，url)
                        if($att_type == 'commodity' && !empty($commodity)){
                            //获取物品
                            $data = $this->wxreply->get_commodity_inid($commodity);
                            $wx_url .= '/home/foodcontent/%s/0/2';
                        }elseif ($att_type == 'info' && !empty($info)){
                            $data = $this->wxreply->get_info_inid($info);
                            $wx_url .= '/home/content/%s';
                            $att_type = 'content';
                        }elseif ($att_type == 'activity' && !empty($activity)){
                            $data = $this->wxreply->get_activity_inid_kill($activity);
                            //$wx_url .= '/home/active_details/%s';
							$wx_url .='/community/detail?aid=%s';
                        }
						
                        //3.拼装成data传给组装器
                        if($data && is_array($data)){
                            foreach ($data as $k2 => $v2){
                                $data[$k2]['title'] = truncate_utf8($v2['title'],20);
                                $data[$k2]['description'] = truncate_utf8($v2['description'],500);
								
                                $image_url = strpos($v2['image'],'http');
                                if($image_url === false){

									if($att_type == 'activity'){
										$image_url = $this->showImage($v2['id'], 'community');
									}else{
										$image_url = get_img_url($v2['image'],$att_type,0,'bg_mr.png');
									}

                                }else{
                                    $image_url = $v2['image'];
                                }
                                $data[$k2]['image'] = $image_url;
                                $data[$k2]['url'] = sprintf($wx_url,$v2['id']);
                            }
                            $send_msg = $this->send_textimg_msg($from,$to, $data);

                        }
                    }
                }elseif($wxmsg[0]['reply_type'] == 'image'){
    				//回复图片
                    $media_id = $this->do_reply($wxmsg[0]['id_msg'],'image');
                    $send_msg = $this->send_img_msg($to,$from,$media_id);

    			}elseif($wxmsg[0]['reply_type'] == 'audio'){
                    //回复音频
                    $media_id = $this->do_reply($wxmsg[0]['id_msg'],'voice');
                    $send_msg = $this->send_voice_msg($to,$from,$media_id);

                }elseif($wxmsg[0]['reply_type'] == 'video'){
                    $wxmsg[0]['reply'] = $this->get_wx_emoticons($wxmsg[0]['reply']);
                    //回复视频
                    $media_id = $this->do_reply($wxmsg[0]['id_msg'],'video');
                    $send_msg = $this->send_video_msg($to,$from,$media_id,substr($wxmsg[0]['reply'],0,10),substr($wxmsg[0]['reply'],0,80));
                }
    		}
    	}
    	return $send_msg;
    }


    //发送图文消息
    public function send_textimg_msg($to,$from,$data){
    	$texttpl = '';
    	if(is_array($data)){
    		$texttpl = '<xml>
				<ToUserName><![CDATA['.$to.']]></ToUserName>
				<FromUserName><![CDATA['.$from.']]></FromUserName>
				<CreateTime>'.time().'</CreateTime>
				<MsgType><![CDATA[news]]></MsgType>
				<ArticleCount>'.count($data).'</ArticleCount>
				<Articles>';
    			foreach ($data as $k => $v){
    				$texttpl .= '<item>
					<Title><![CDATA['.strip_tags(trim($v['title'])).']]></Title>
					<Description><![CDATA['.substr(strip_tags(trim($v['description'])),0,50).']]></Description>
					<PicUrl><![CDATA['.$v['image'].']]></PicUrl>
					<Url><![CDATA['.$v['url'].']]></Url>
					</item>';
    			}
    		$texttpl .=	'</Articles></xml> ';
    	}
    	return $texttpl;
    }

    //发送文本消息
    public function send_text_msg($to,$from,$msg){
        $textTpl = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[%s]]></MsgType>
                <Content><![CDATA[%s]]></Content>
                </xml>";
        return sprintf($textTpl,$from,$to,time(),'text',$msg);
    }

    //发送图片消息
    public function send_img_msg($to,$from,$media_id){
        $texttpl = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[%s]]></MsgType>
                <Image>
                <MediaId><![CDATA[%s]]></MediaId>
                </Image>
                </xml>";
        return sprintf($texttpl,$from,$to,time(),'image',$media_id);
    }

    //发送音频消息
    public function send_voice_msg($to,$from,$media_id){
        $texttpl = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[%s]]></MsgType>
                <Voice>
                    <MediaId><![CDATA[%s]]></MediaId>
                </Voice>
                </xml>";
        return sprintf($texttpl,$from,$to,time(),'voice',$media_id);
    }

    //发送视频消息
    public function send_video_msg($to,$from,$media_id,$title,$description){
        $texttpl = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[%s]]></MsgType>
                <Video>
                    <MediaId><![CDATA[%s]]></MediaId>
                    <Title><![CDATA[%s]]></Title>
                    <Description><![CDATA[%s]]></Description>
                </Video>
                </xml>";
        return sprintf($texttpl,$from,$to,time(),'video',$media_id,$title,$description);
    }

    //附件回复的处理
    public function do_reply($id_msg,$type){
        $this->load->model('wxreply_model','wxreply');
        $where = 'id_msg = ' . $id_msg;
        $msg_attachment = $this->wxreply->get_msg_attach($where,1);
        if($msg_attachment){
            //时间差的小时分钟数
            $time_difference = $this->hours_min(strtotime($msg_attachment[0]['last_time']),time());
            if($time_difference/24 > 3 || empty($msg_attachment[0]['id_media'])){
                if($type == 'image' || $type == 'voice' || $type == 'video'){
                    //获取token
                    $access_token = $this->get_access_token($this->users['id_business'],$this->users['id_shop']);
                    //上传附件文件
                    $r = $this->upload_wx_attachment($type,$access_token,$msg_attachment[0]['url'],'keyword_reply');
                    $return = json_decode($r,true);
                    //获得mediaid
                    $media_id = $return['media_id'];

                    $data_i['id_media'] = $media_id;
                    $data_i['last_time'] = date('Y-m-d h:i:s',time());

                    //更新回复附件表的media_id 和 last_time
                    $this->wxreply->update_reply_attachment($data_i,$where);
                    return $media_id;
                }
            }else{
                return $msg_attachment[0]['id_media'];
            }
        }
        return '';
    }

    public function get_wxmsg_by_rule($from,$to,$key){
    	$smsg = '';
    	if($key != ''){
//    		$where = "id_business='".$this->bid."' AND id_shop='".$this->sid."' AND msg_type='text' and keyword like '%".$key."%'";
            $where = "id_business='".$this->bid."' AND id_shop='".$this->sid."' AND msg_type='text' and binary keyword = '".$key."'";
    		$this->load->model('wxreply_model','wxreply');
    		$wxmsg = $this->wxreply->get_biz_wxseting($where);
			
    		//如果取到多条，随机发送一条信息
    		$sendmsg = array();
    		if(!empty($wxmsg)){
    			shuffle($wxmsg);
    			$sendmsg = $wxmsg[0];
    			if(!empty($smsg)){
    				//命中关键词，记录统计
    				$this->save_analisys('keyword', $sendmsg['id_reply'], 'search');
    			}
    		}
    		if($sendmsg){
                $where_ = 'id_msg = ' . $sendmsg['id_msg'];
    			if($sendmsg['reply_type'] == 'text'){
    				//文本消息
    				$smsg = $this->send_text_msg($to,$from, $this->get_wx_emoticons($sendmsg['reply']));
    			}elseif ($sendmsg['reply_type'] == 'image'){
    				//图片消息
                    $media_id = $this->do_reply($sendmsg['id_msg'],'image');
                    $smsg = $this->send_img_msg($to, $from, $media_id);
    			}elseif ($sendmsg['reply_type'] == 'audio'){
                    //音频消息
                    $media_id = $this->do_reply($sendmsg['id_msg'],'voice');
                    $smsg = $this->send_voice_msg($to, $from,$media_id);
                }elseif ($sendmsg['reply_type'] == 'video'){
                    //视频消息
                    $media_id = $this->do_reply($sendmsg['id_msg'],'video');
                    $smsg = $this->send_video_msg($to, $from, $media_id,substr($sendmsg['reply'],0,10),substr($sendmsg['reply'],0,80));
                }else{
    				//图文混排
    				if($sendmsg['id_msg']){
    					$data = array();
    					//1.附件表查出对应的id
    					$att_arr = $this->wxreply->get_msg_attach(array('id_msg'=>$sendmsg['id_msg']),10);

    					$att_type = '';
    					if($att_arr){
    						$commodity = $info = $activity = array();
    						foreach ($att_arr as $k1 => $v1){
    							if($v1['object_type'] == 'commodity'){
    								$commodity[] = $v1['id_object'];
    								$att_type = 'commodity';
    							}elseif($v1['object_type'] == 'info'){
    								$info[] = $v1['id_object'];
    								$att_type = 'info';
    							}else {
    								$activity[] = $v1['id_object'];
    								$att_type = 'activity';
    							}
    						}
    					}
    					
    					$wx_url = 'http://'.$_SERVER['HTTP_HOST'].'/wapi/'.$this->bid.'/'.$this->sid;
    					//2.根据对应的类型，去查出相应的信息(标题，图片，描述，url)
    					if($att_type == 'commodity' && !empty($commodity)){
    						//获取物品
    						$data = $this->wxreply->get_commodity_inid($commodity);
    						$wx_url .= '/home/foodcontent/%s/0/2';
    					}elseif ($att_type == 'info' && !empty($info)){
    						$data = $this->wxreply->get_info_inid($info);
    						$wx_url .= '/home/content/%s';
    						$att_type = 'content';
    					}elseif ($att_type == 'activity' && !empty($activity)){
    						$data = $this->wxreply->get_activity_inid($activity);
    						$wx_url .= '/home/active_details/%s';
    					}
    					
    					//3.拼装成data传给组装器
    					if($data && is_array($data)){
    						foreach ($data as $k2 => $v2){
    							$data[$k2]['title'] = truncate_utf8($v2['title'],20);
    							$data[$k2]['description'] = truncate_utf8($v2['description'],500);

                                $image_url = strpos($v2['image'],'http');
                                if($image_url === false){
                                    $image_url = get_img_url($v2['image'],$att_type,0,'bg_mr.png');
                                }else{
                                    $image_url = $v2['image'];
                                }
    							$data[$k2]['image'] = $image_url;
    							$data[$k2]['url'] = sprintf($wx_url,$v2['id']);
    						}
    						
    						$smsg = $this->send_textimg_msg($from,$to, $data);
    					}
    				}
    			}
    		}
    	}
    	return $smsg;
    }
    
    
    public function get_wx_emoticons($content){
    	//正则表达式匹配出img
    	$pattern='/<[img|IMG].*?alt=[\'|\"](.*?)[\'|\"].*?[\/]?>/';
    	$content = preg_replace($pattern,"[\\1]",$content);
    	return strip_tags(html_entity_decode($content,ENT_COMPAT,'utf-8'),'<a>');
    }
    
    
    //取消关注
    private function unsubscribe($openid){
    	$where['id_business'] = $this->bid;
    	$where['id_shop'] = $this->sid;
    	$where['id_open'] = strval($openid);
    	$where['state'] = 'subscribe';
    	$update['state'] = 'unsubscribe';
    	$update['update_time'] = date('Y-m-d H:i:s', time());
    	$this->load->model('wxreply_model','wxreply');
    	$this->wxreply->edit_wx_info($update,$where);
    }
    
    
    private function analysis_click($value){
    	$where['id_business'] = $this->bid;
    	//$where['id_shop'] = $this->sid;
    	$where['object_type'] = 'click';
    	$where['object_value'] = $value;
    	
    	$this->load->model('wxmenu_model','wxmenu');
    	$obj = $this->wxmenu->get_wxmenu_id($where);
    	if($obj['id_shop_menu']){
    		$this->save_analisys('menu', $obj['id_shop_menu'], 'click');
    	}
    	
    }
    
}