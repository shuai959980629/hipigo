<?php
/**
 * 
 * @copyright(c) 2013-11-27
 * @author msi
 * @version Id:system.php
 */

class System extends Admin_Controller
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
 * @群发短信入口函数
 * @copyright(c) 2014-09-10
 * @author sc
 * @bulk_type 1:全部用户 2:指定用户 3:按分组
 */
public function wx_bulk_msg(){
	
	if($this->input->post('bulk_type')){
		
			$bulk_type = $this->input->post('bulk_type');
			$this->load->model('business_model','business');
			$reply_type = $this->input->post('reply_type');
			
			if($bulk_type==1){

				$where = 'id_business = ' . $this->id_business . ' and id_shop = ' . $this->id_shop . ' and state = \'subscribe\'';
				$total = $this->business->get_business_sub_all($where);
				$totalcount = count($total);
				$touser .= '"touser":[';

				foreach($total as $k=>$v){
					
						if($k+1==$totalcount){
							$touser .= '"'.$v['id_open'].'"';
						}else{
							$touser .= '"'.$v['id_open'].'"'.",";
						}

				}				

				$touser .= ']';

				$this->set_msg_type($reply_type,$touser,$bulk_type);

			}

			if($bulk_type==2){

				$bulk_list_user = $this->input->post('bulk_list_user');

				$touser .= '"touser":[';
				$touser .= $bulk_list_user;
				$touser .= ']';

				$this->set_msg_type($reply_type,$touser,$bulk_type);

			}
			
			if($bulk_type==3){

				$bulk_list_group = $this->input->post('bulk_list_group');

				$filter .= '"filter":{';
				$filter .= '"group_id":"'.$bulk_list_group.'"';
				$filter .= '}';

				$this->set_msg_type($reply_type,$filter,$bulk_type);

			}

	}else{
		
			$data = array();
			$select_op = array('71' => array('info'=>'内容'));
			$select_op_access = array();

			$this->load->model('right_model','right');
			$has_access = $this->right->check_select_options_access($this->id_business,array_keys($select_op));

			foreach ($has_access as $k => $v){
					if(in_array($v['id_right'], array_keys($select_op))){
						$select_op_access = array_merge($select_op_access,$select_op[$v['id_right']]);
					}
			}

			$data['sop'] = $select_op_access;

			$this->smarty->assign($data);
			$this->smarty->view('bulk_msg');
	}
	
}


/**
 * @群发类型分支函数
 * @copyright(c) 2014-09-10
 * @author sc
 * @type 1 文本 2 图文 3图片 4语音 5视频
 * @秒杀活动判断标识 kill_name_msg
 */
function set_msg_type($type,$msgtype,$bulk_type){

		switch ($type) {
			case 1:
				$content = $this->input->post('content');
			    $content = strip_tags($content);
				$this->set_msg_text($msgtype,$content,$bulk_type);
			break;
			case 2:
				$cnt_selected = $this->input->post('cnt_selected');

				if($this->input->post('kill_name_msg')==''){
					$this->set_msg_text_image($msgtype,$cnt_selected,$bulk_type);
				}else{
					$this->set_msg_text_image_kill($msgtype,$cnt_selected,$bulk_type);
				}

			break;
			case 3:
				$image_src = $this->input->post('image_src');
				$this->set_msg_image($msgtype,$image_src,$bulk_type);
			break;
			case 4:
				$audio_src = $this->input->post('image_src');
				$this->set_msg_audio($msgtype,$audio_src,$bulk_type);
			break;
			case 5:
				$video_src = $this->input->post('image_src');
				$this->set_msg_video($msgtype,$video_src,$bulk_type);
			break;
			default:
			return;

	  }

}


/**
 * @群发图文内容函数--秒杀活动
 * @copyright(c) 2014-09-10
 * @author sc
 * @type 图文
 */
function set_msg_text_image_kill($msgtype,$cnt_selected,$bulk_type){
		
		$kill_name_msg = $this->input->post('kill_name_msg');
		$kill_description_msg = $this->input->post('kill_description_msg');
		$this->load->model('wxreply_model','wxreply');
		$count = count($cnt_selected);
		$att_type = $this->input->post('select_btype');
		$access_token = $this->get_access_token($this->id_business,$this->id_shop);
		$articles = '{';
		$articles .='"articles": [';

		foreach($cnt_selected as $k=>$v){

			$id = $v;

			if($att_type == 'commodity' && !empty($id)){
				$wxxx = $this->wxreply->get_commodity_inid($id);
				$content_source_url = 'http://wx.hipigo.cn/wapi/90/1/community/detail?aid='.$id;
			}elseif ($att_type == 'info' && !empty($id)){
				$wxxx = $this->wxreply->get_info_inid($id);
				$content_source_url = $_SERVER['HTTP_HOST'].'/wapi/'.$this->id_business.'/1/home/content/'.$id;
				$att_type = 'content';
			}elseif ($att_type == 'activity' && !empty($id)){
				$wxxx = $this->wxreply->get_activity_inid($id);
				$content_source_url = 'http://wx.hipigo.cn/wapi/90/1/community/detail?aid='.$id;
			}

			$content .= $wxxx[0]['description'];
			
			if($k==0){
				$image_url = $wxxx[0]['image'];
				$type = 'image';
				$r = $this->upload_wx_attachment($type,$access_token,$image_url,$att_type);
				$return = json_decode($r,1);
				$media_id = $return['media_id'];
			}

		}	
		
		$title = $kill_name_msg;
		$digest = $kill_description_msg;
		$articles .=$this->set_articles_text_image($media_id,$title,$content_source_url,$content,$digest);
		$articles .=']';
		$articles .= '}';

//		echo 'ms<br>'.$articles;
//		exit;

		$url = 'https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token='.$access_token;
		$post_data = $articles;
		$uploadnews = request_curl($url,$post_data);		
		$uploadnews = json_decode($uploadnews,true);

		$result = '{';
		$result .= $msgtype.',';		
		$result .= '"mpnews":{"media_id":"'.$uploadnews['media_id'].'"},';	
		$result .= '"msgtype":"mpnews"';	
		$result .= '}';	
		
		//$this->msg_send($result,$bulk_type);
}


/**
 * @图文内容拼装函数
 * @copyright(c) 2014-09-10
 * @author sc
 * @type 图文
 */
function set_articles_text_image($media_id,$title,$content_source_url,$content,$digest){

		$articles .= '{';
		$articles .='"thumb_media_id":"'.$media_id.'",';
        $articles .='"author":"",';
		$articles .='"title":"'.$title.'",';
		$articles .='"content_source_url":"'.$content_source_url.'",';
		$articles .='"content":"'.$content.'",';
		$articles .='"digest":"'.$digest.'",';
        $articles .='"show_cover_pic":"1"';
		$articles .= '}';

		return $articles;
}


/**
 * @群发图文内容函数
 * @copyright(c) 2014-09-10
 * @author sc
 * @type 图文
 */
function set_msg_text_image($msgtype,$cnt_selected,$bulk_type){
		
		$this->load->model('wxreply_model','wxreply');
		$count = count($cnt_selected);
		$att_type = $this->input->post('select_btype');
		$access_token = $this->get_access_token($this->id_business,$this->id_shop);
		$articles = "{";
		$articles .='"articles": [';

		foreach($cnt_selected as $k=>$v){

			if($att_type == 'commodity' && !empty($v)){
				$wxxx = $this->wxreply->get_commodity_inid($v);
				$content_source_url = 'http://wx.hipigo.cn/wapi/90/1/community/detail?aid='.$v;
			}elseif ($att_type == 'info' && !empty($v)){
				$wxxx = $this->wxreply->get_info_inid($v);
				$content_source_url = $_SERVER['HTTP_HOST'].'/wapi/'.$this->id_business.'/1/home/content/'.$v;
			}elseif ($att_type == 'activity' && !empty($v)){
				$wxxx = $this->wxreply->get_activity_inid($v);
				$content_source_url = 'http://wx.hipigo.cn/wapi/90/1/community/detail?aid='.$v;
			}

			$image_url = $wxxx[0]['image'];
			$content = $wxxx[0]['description'];
			$title = $wxxx[0]['title'];
			$type = 'image';
			$r = $this->upload_wx_attachment($type,$access_token,$image_url,'content');
			$return = json_decode($r,1);
			$media_id = $return['media_id'];
			$digest = strip_tags($content);
			$digest = trim($digest);
			$digest = substr($digest,0,200);
			$content = str_replace('"','\"',$content);

			if($count==$k+1){
				$articles .=$this->set_articles_text_image($media_id,$title,$content_source_url,$content,$digest);
			}else{
				$articles .=$this->set_articles_text_image($media_id,$title,$content_source_url,$content,$digest).',';
			}

		}	

		$articles .=']';
		$articles .= "}";

		$articles = $this->json_html($articles);

		$url = 'https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token='.$access_token;
		$post_data = $articles;
		$uploadnews = request_curl($url,$post_data);		
		$uploadnews = json_decode($uploadnews,true);

		$result = '{';
		$result .= $msgtype.',';		
		$result .= '"mpnews":{"media_id":"'.$uploadnews['media_id'].'"},';	
		$result .= '"msgtype":"mpnews"';	
		$result .= '}';	
		
		$this->msg_send($result,$bulk_type);
}


function json_html($articles) { 
		$articles = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.'|[\x00-\x7F][\x80-\xBF]+'.'|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.'|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.'|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S','', $articles);

		$articles = preg_replace('/\xE0[\x80-\x9F][\x80-\xBF]'.'|\xED[\xA0-\xBF][\x80-\xBF]/S','', $articles);

		$articles = str_replace("'", '"', $articles);

		preg_replace('/,\s*([\]}])/m', '$1', $articles);

		$articles = str_replace("\n", ' ', $articles);

		$articles = str_replace("\t", ' ', $articles);

		return $articles; 
}


/**
 * @群发文本内容函数
 * @copyright(c) 2014-09-10
 * @author sc
 * @type 文本
 */ 
function set_msg_text($msgtype,$content,$bulk_type){

		$result = '{';
		$result .= $msgtype.',';		
		$result .= '"text":{"content":"'.$content.'"},';	
		$result .= '"msgtype":"text"';	
		$result .= '}';	

		$this->msg_send($result,$bulk_type);
}


/**
 * @群发视频内容函数
 * @copyright(c) 2014-09-10
 * @author sc
 * @type 视频
 */ 
function set_msg_video($msgtype,$video_src,$bulk_type){

		//获取token
		$access_token = $this->get_access_token($this->id_business,$this->id_shop);
		$type = 'video';
		//上传附件文件
		$r = $this->upload_wx_attachment($type,$access_token,$video_src,'keyword_reply');
		$return = json_decode($r,1);
		//获得mediaid
		$media_id = $return['media_id'];

		$result = '{';
		$result .= $msgtype.',';		
		$result .= '"mpvideo":{"media_id":"'.$media_id.'"},';	
		$result .= '"msgtype":"mpvideo"';	
		$result .= '}';	
		
		$this->msg_send($result,$bulk_type);
}


/**
 * @群发语音内容函数
 * @copyright(c) 2014-09-10
 * @author sc
 * @type 语音
 */ 
function set_msg_audio($msgtype,$audio_src,$bulk_type){

		//获取token
		$access_token = $this->get_access_token($this->id_business,$this->id_shop);
		$type = 'audio';
		//上传附件文件
		$r = $this->upload_wx_attachment($type,$access_token,$audio_src,'keyword_reply');
		$return = json_decode($r,1);
		//获得mediaid
		$media_id = $return['media_id'];

		$result = '{';
		$result .= $msgtype.',';		
		$result .= '"voice":{"media_id":"'.$media_id.'"},';	
		$result .= '"msgtype":"voice"';	
		$result .= '}';	
		
		$this->msg_send($result,$bulk_type);
}


/**
 * @群发图片内容函数
 * @copyright(c) 2014-09-10
 * @author sc
 * @type 图片
 */
function set_msg_image($msgtype,$image_src,$bulk_type){

		//获取token
		$access_token = $this->get_access_token($this->id_business,$this->id_shop);
		$type = 'image';
		//上传附件文件
		$r = $this->upload_wx_attachment($type,$access_token,$image_src,'keyword_reply');
		$return = json_decode($r,1);
		//获得mediaid
		$media_id = $return['media_id'];

		$result = '{';
		$result .= $msgtype.',';		
		$result .= '"image":{"media_id":"'.$media_id.'"},';	
		$result .= '"msgtype":"image"';	
		$result .= '}';	
		
		$this->msg_send($result,$bulk_type);
}


/**
 * @群发功能结束函数
 * @copyright(c) 2014-09-10
 * @author sc
 * @content 群发内容  | bulk_type 3：按分组 1、2：按用户
 */
function msg_send($content,$bulk_type){
	  
	  $access_token = $this->get_access_token($this->id_business,$this->id_shop);
	  
	  if($bulk_type==3){
			$url = 'https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token='.$access_token;
	  }else{
			$url = 'https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token='.$access_token;
	  }

	  $post_data = $content;
//	  echo $post_data.'<br>';
	  $result = request_curl($url,$post_data);
//	  echo $result; exit;
	  $result = json_decode($result,true);

	  if($result['errcode']==0){
		  $this->returen_status(1, $this->lang->line('add_msg_success'));
	  }else{
		  $this->returen_status(1, $this->lang->line('add_msg_failed'));
	  }

}


/**
 * @获取公众账号关注者信息
 * @copyright(c) 2014-09-10
 * @author sc
 */
function get_followers_list(){

		$offset = $this->input->post('offset') ? intval($this->input->post('offset')) : 1;//页码
        $this->load->model('business_model','business');
        $where = 'id_business = ' . $this->id_business . ' and id_shop = ' .$this->id_shop . ' and state = \'subscribe\'';

        //获取关注者总条数
        $total = $this->business->get_business_sub($where,0);

        //获取关注者信息
        $list_user = $this->business->get_business_sub_all($where);

		foreach($list_user as $k=>$v){
			$list_user[$k]['nick_name'] = urldecode($v['nick_name']);
		}	

        if(!$list_user){
            $offset = 1;
            $list_user = $this->business->get_business_sub($where,1,$offset,$this->config->item('page'));
        }

        //分页代码
        $page_html = $this->get_page($total, $offset, 'followers_list_page','method');

        $data = array();
        $data['list_user'] =$list_user;
        $data['page_html'] = $page_html;
        $data['total'] = $total;

        echo  json_encode($data);

}


/**
 * @获取公众账号用户分组信息
 * @copyright(c) 2014-09-10
 * @author sc
 */
function get_user_group(){

	  $access_token = $this->get_access_token($this->id_business,$this->id_shop);
	  $url = 'https://api.weixin.qq.com/cgi-bin/groups/get?access_token='.$access_token;

	  $result = request_curl($url);

	  echo  $result;

}


	public function edit_passwd(){
		
		$this->load->library('form_validation');
		$this->lang->load('user');
		if($this->input->post('opasswd')){

			if(FALSE !== $this->form_validation->run('edit_passwd')){
				//检查旧密码是否正确
				$this->load->model('user_model');
				$smerchent = $this->user_model->get_userinfo_by_uid($this->users['id_user']);
				//debug($smerchent);
				if($smerchent['pass_hash']){
					//用户输入的原始密码
					$opass = $this->md5pwd($this->input->post('opasswd'), $smerchent['pass_hash']);
					$new_pass = $this->md5pwd($this->input->post('npasswd'), $smerchent['pass_hash']);
					//debug($opass);
					if($smerchent['pass'] == $new_pass){
						$this->returen_status(1, $this->lang->line('passwd_is_not_modify'));
					}
					if($opass == $smerchent['pass']){
						$where = array('id_user'=>$this->users['id_user']);
						$update_data = array('pass'=>$new_pass);
						$this->user_model->modify_passwd($update_data,$where);
						$this->returen_status(1,$this->lang->line('passwd_modify_success'));
					}else{
						$this->returen_status(0, $this->lang->line('old_passwd_validate_error'));
					}
				}else{
					$this->returen_status(0, $this->lang->line('u_accout_is_not_find'));
				}
			}else{
				$this->returen_status(0, $this->form_validation->error_string());
			}
		}else{
			$this->smarty->view('passwd');
		}
	}

	//微信关注回复 自动回复
	private function wx_setting($type){

		$type = in_array($type, array('subscribe','autoreply')) ? $type : 'autoreply';
		$this->load->model('wxreply_model','wxreply');
        $reply_type = $this->input->post('reply_type');
        $id_msg = $this->input->post('msgid');
        $content_length = $this->input->post('content_length');
        $fileNum = $this->input->post('fileNum');
        //如果设置默认回复信息为空  则删除以前设置得默认回复信息 及附件
        $cnt_selected = $this->input->post('cnt_selected');

        if($id_msg != ''){
            if(($content_length === 0 && $fileNum === 0) || ($content_length === '0' && $fileNum === '0' && count($cnt_selected) <= 0)){
                $where_auto = 'id_msg = ' . $id_msg;
                $this->wxreply->delete_replay($where_auto);
                $this->returen_status(1, $this->lang->line('edit_data_success'));
            }elseif($content_length !== 0 && $fileNum === 0){
                $where_auto = 'id_msg = ' . $id_msg;
                $this->wxreply->delete_reply_attachment($where_auto);
                $this->returen_status(1, $this->lang->line('edit_data_success'));
            }elseif($reply_type == 2 && $cnt_selected == false){
                $where_auto = 'id_msg = ' . $id_msg;
                $this->wxreply->delete_replay($where_auto);
                $this->returen_status(1, $this->lang->line('edit_data_success'));
            }elseif($reply_type == 1 && $content_length === '0'){
                $where_auto = 'id_msg = ' . $id_msg;
                $this->wxreply->delete_replay($where_auto);
                $this->returen_status(1, $this->lang->line('edit_data_success'));
            }
        }
        if($content_length || $fileNum || $cnt_selected){


            $add_data = array();

            $image_src = $this->input->post('image_src');
            $video_content = $this->input->post('video_content');
            $msg_type = 'text';
            if($reply_type == '3'){
                $msg_type = 'image';
            }elseif($reply_type == '2'){
                $msg_type = 'image-text';
            }elseif($reply_type == '4'){
                $msg_type = 'audio';
            }elseif($reply_type == '5'){
                $msg_type = 'video';
            }

            $media_id = $file_size = 0;
            //判断当前发布的关注回复消息是否为 图片 音频 视频 类型的处理
            if($reply_type == '3' || $reply_type == '4' || $reply_type == '5'){
                if($image_src != '' && $image_src != ' '){
                    //获取图片路径+名字
                    $file_names = $this->file_url_name('keyword_reply',$image_src,0);
                    //获取图片大小
                    $data = $this->get_url_size($file_names);
                    $file_size = $data['file_size'];

                    //获取token
                    $access_token = $this->get_access_token($this->id_business,$this->id_shop);
                    //上传附件文件
                    $r = $this->upload_wx_attachment($msg_type,$access_token,$image_src,'keyword_reply');
                    $return = json_decode($r,1);
                    //获得mediaid
                    $media_id = $return['media_id'];
                }
            }

            $select_btype = $this->input->post('select_btype');
            $select_btype = $select_btype == 'cmd' ? 'commodity' : $select_btype;

            $content = $this->input->post('content');

            if($id_msg > 0){

                $where_up = 'id_msg = ' . $id_msg;
                //修改
                $update_data = $where2 = $update_data2 = array();
                $where2['id_business'] = $this->id_business;
                $where2['msg_type'] = 'event';

                $update_data['reply_type'] = $msg_type=='voice'?'audio':$msg_type;
                if($reply_type == '5'){
                    $update_data['reply'] = $video_content;
                }elseif($reply_type == '1'){
                    $update_data['reply'] = trim($content);
                }else{
                    $update_data['reply'] = '';
                }
                //当前编辑之后，处理是否是默认回复
                if($this->input->post('msgtype') == 'autoreply'){
                    $update_data['keyword'] = 'defaultreply';

                    $where2['keyword'] = 'defaultreply';
                    $update_data2['keyword'] = 'autoreply';
                    $this->wxreply->update_wx_msg($where2,$update_data2);
                }elseif($this->input->post('msgtype') == 'subscribe'){
                    $update_data['keyword'] = 'defaultsubscribe';

                    $where2['keyword'] = 'defaultsubscribe';
                    $update_data2['keyword'] = 'subscribe';
                    $this->wxreply->update_wx_msg($where2,$update_data2);
                }

                //更新关注回复表信息
                $this->wxreply->update_wx_msg($where_up,$update_data);
                //判断当前发布的关注回复消息是否为 图片 音频 视频 类型的处理
                if($reply_type == '3' || $reply_type == '4' || $reply_type == '5'){
                    if($image_src != '' && $image_src != ' '){
                        //删除关注回复的附件信息
                        $this->wxreply->delete_reply_attachment($where_up);
                        //插入一条新修改的附件信息
                        $insert_data = array(
                            'id_msg' => $id_msg,
                            'object_type' => '',
                            'id_object' => '',
                            'url'=>$image_src,
                            'size'=>$file_size,
                            'last_time'=>date('Y-m-d h:i:s',time()),
                            'id_media'=>$media_id
                        );
                        $this->wxreply->insert_attachment($insert_data);
                    }
                }elseif($reply_type == '2'){
                    if($cnt_selected){
                        //删除关注回复的附件信息
                        $this->wxreply->delete_reply_attachment($where_up);
                        foreach($cnt_selected as $csd){
                            //插入一条新修改的附件信息
                            $insert_data = array(
                                'id_msg' => $id_msg,
                                'object_type' => $select_btype,
                                'id_object' => $csd
                            );
                            $this->wxreply->insert_attachment($insert_data);
                        }
                    }
                }else{
                    $this->wxreply->delete_reply_attachment($where_up);
                }
                $this->returen_status(1, $this->lang->line('edit_data_success'));
            }else{

                //新增
                $keyword = 'defaultsubscribe';
                //用来判断当前页面是否是处理的默认回复或是关注回复
                if($this->input->post('fun_type') != 'subscribe'){
                    $keyword = 'defaultreply';
                }
                $add_data['id_business'] = $this->id_business;
                $add_data['id_shop'] = $this->id_shop;
                $add_data['msg_type'] = 'event';
                $add_data['keyword'] = $keyword;
                $add_data['reply_type'] = $msg_type;
                if($msg_type == 'text')
                    $add_data['reply'] = $content;
                elseif($msg_type == 'video')
                    $add_data['reply'] = $reply_type;
                else
                    $add_data['reply'] = '';

				$cnt_selected_kill = $this->input->post('cnt_selected_kill');

				if($cnt_selected_kill){
                    $add_data['reply'] = $cnt_selected_kill;
				}

                $id_msgs = $this->wxreply->add_wx_msg($add_data);

                if($msg_type == 'image-text'){

                    foreach($cnt_selected as $cs){
                        //插入一条新增加的附件信息
                        $insert_data = array(
                            'id_msg' => $id_msgs,
                            'object_type' => $select_btype,
                            'id_object' => $cs
                        );

                        $this->wxreply->insert_attachment($insert_data);

                    }

                }elseif($msg_type != 'text'){
                    //插入一条新增加的附件信息
                    $insert_data = array(
                        'id_msg' => $id_msgs,
                        'object_type' => '',
                        'id_object' => '',
                        'url'=>$image_src,
                        'size'=>$file_size,
                        'last_time'=>date('Y-m-d h:i:s',time()),
                        'id_media'=>$media_id
                    );
                    $this->wxreply->insert_attachment($insert_data);
                }
                $this->returen_status(1, $this->lang->line('add_data_success'));
            }
        }else{
            $where = "id_business='".$this->id_business."' AND id_shop='".$this->id_shop."'";
            $where .= " AND msg_type='event'";
            if($type == 'subscribe'){
                $where .= " AND (keyword='subscribe' OR keyword='defaultsubscribe')";
            }else{
                $where .= " AND (keyword='defaultreply' OR keyword='autoreply')";
            }
            $result = $this->wxreply->get_biz_wxseting($where);

            $wx_text = $wx_attachment = array();
            foreach ($result as $k=>$val){
                $where = 'id_msg = ' . $val['id_msg'];
                if($val['reply_type'] == 'text'){
                    $wx_text = $val;
                }elseif($val['reply_type'] == 'image' || $val['reply_type'] == 'audio' || $val['reply_type'] == 'video'){//获取图片附件数据  获取音频附件数据   获取视频附件数据
                    $wx_text = $val;
                    $wx_attachment = $this->wxreply->get_msg_attach($where,1);
                }elseif($val['reply_type'] == 'image-text'){
                    $wx_text = $val;
                    //查询附件表
                    $wx_attach = $this->wxreply->get_msg_attach($where);
                    if(is_array($wx_attach)){
                        $commodity = $info = $activity = array();
                        $att_type = '';
                        foreach ($wx_attach as $k2 => $v2){
                            if($v2['object_type'] == 'commodity'){
                                $commodity[] = $v2['id_object'];
                                $att_type = 'commodity';
                            }elseif ($v2['object_type'] == 'info'){
                                $info[] = $v2['id_object'];
                                $att_type = 'info';
                            }elseif ($v2['object_type'] == 'activity'){
                                $activity[] = $v2['id_object'];
                                $att_type = 'activity';
                            }
                        }
                        if($att_type == 'commodity' && !empty($commodity)){
                            //获取物品
                            $wx_text['cnt'] = $this->wxreply->get_commodity_inid($commodity);
                        }elseif ($att_type == 'info' && !empty($info)){
                            $wx_text['cnt'] = $this->wxreply->get_info_inid($info);
                        }elseif ($att_type == 'activity' && !empty($activity)){
							
							if($type == 'autoreply'){
								$where_kill = array('id_business'=>$this->id_business,'id_shop'=>$this->id_shop,'keyword'=>'defaultreply');
							}else{
								$where_kill = array('id_business'=>$this->id_business,'id_shop'=>$this->id_shop,'keyword'=>'defaultsubscribe');
							}

                            $wx_text['cnt'] = $this->wxreply->get_activity_like_title_kill_m($where_kill);
                            //$wx_text['cnt'] = $this->wxreply->get_activity_inid($activity);
                        }
                    }
                }
            }

            //获取图文回复信息
            $return = $this->img_text_reply();
            $this->smarty->assign($return);

            if(!$att_type){
                foreach($return['sop'] as $k=>$v){
                    $att_type = $k;
                    break;
                }
            }

//            $this->smarty->assign('att_type',$att_type=="commodity"?"cmd":$att_type);
            $this->smarty->assign('att_type','info');
            $this->smarty->assign('type',$type);
            $this->smarty->assign('reply_type',$result[0]['reply_type']);
            $this->smarty->assign('wx_text',$wx_text);
            $this->smarty->assign('wx_attachment',$wx_attachment);
            $this->smarty->assign('attachment_count',count($wx_attachment));
            $this->smarty->assign('page_type','wx_attention_reply');

            $this->smarty->view('wx_attention_reply');
        }
	}
	
	
	//处理微信默认回复
	public function wx_default_reply(){
		$this->wx_setting('autoreply');
	}
	
	
	public function wx_subscribe_reply(){
		$this->wx_setting('subscribe');
	}
	
	
	public function wx_reply($offset=0){
		if(!$offset){
			$offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
		}
		$search_key = $this->input->post('search_key') ? addslashes($this->input->post('search_key')) : '';//搜索关键字
		$reply_type = $this->input->post('reply_type') ? $this->input->post('reply_type') : 'all';//回复内容类型
	
		//获取回复列表等信息
		$data = $this->wx_replay_list($search_key,$reply_type,$offset);
		$this->smarty->assign($data);
	
		if(intval($this->input->post('ispage')) == 1){
			echo $this->smarty->view('lists');
		}else if(intval($this->input->post('ispage')) == 2){
			$data = $this->smarty->fetch('lists.html');
			$resp = array(
					'status' => 1,
					'msg' => '',
					'data' => $data
			);
			die(json_encode($resp));
		}else{
            $this->smarty->assign('html','reply_list');
			$this->smarty->view('wx_responce');
		}
	}
	
	//zxx 删除微信回复信息
	function wx_delete_reply(){
		$offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
		$search_key = $this->input->post('search_key') ? addslashes($this->input->post('search_key')) : '';//搜索关键字
		$reply_type = $this->input->post('reply_type') ? $this->input->post('reply_type') : 'all';//回复内容类型
		$id_msg = $this->input->post('id_msg');//回复内容id
		$delete_type = $this->input->post('delete_type');//删除类型。checked 为前面复选框删除  空的话则是行末的删除按钮删除
	
		$this->load->model('wxreply_model','wxreply');
		if($delete_type == 'checked'){
			if($id_msg != ''){
				foreach($id_msg as $di){
					$where = 'id_msg = '.$di;
					$this->wxreply->delete_replay($where);
				}
			}
		}else{
			$where = 'id_msg = '.$id_msg;
			$this->wxreply->delete_replay($where);
		}
	
		//获取回复列表等信息
		$data = $this->wx_replay_list($search_key,$reply_type,$offset);
		$this->smarty->assign($data);
	
		$data = $this->smarty->fetch('lists.html');
		$resp = array(
				'status' => 1,
				'msg' => '',
				'data' => $data
		);
		die(json_encode($resp));
	}

	//获取微信回复列表
	function wx_replay_list($search_key,$reply_type,$offset){
		$where = 'id_business = '.$this->id_business.' and id_shop = '.$this->id_shop.' and msg_type = \'text\'';
		if($search_key){
			$where .= ' and keyword like \'%'.$search_key.'%\'';
		}
		if($reply_type != 'all'){
			$where .= ' and reply_type = \''.$reply_type.'\'';
		}
		$this->load->model('wxreply_model','wxreply');
	
		$limit = $this->config->item('page');
		$start = ($offset - 1)*$limit;
		//1.获取总数
		$wxcnt = $this->wxreply->get_wxreplay_count($where);
		//获取微信回复信息
		$wxxx = $this->wxreply->get_biz_wxseting($where,1,$start,$limit);
        if(!$wxxx){
            $offset = 1;
            $start = ($offset - 1)*$limit;
            $wxxx = $this->wxreply->get_biz_wxseting($where,1,$start,$limit);
        }
		//获取分页代码
		$page_arr = $this->get_page($wxcnt,$offset,'responce_list_page','method',$limit);
		if(is_array($wxxx)){
			foreach ($wxxx as $k => $v){
				if($v['reply_type'] == 'image-text'){
					//查询附件表
					$wx_attach = $this->wxreply->get_msg_attach(array('id_msg'=>$v['id_msg']));
					if(is_array($wx_attach)){
						$commodity = $info = $activity = array();
						$att_type = '';
						foreach ($wx_attach as $k2 => $v2){
							if($v2['object_type'] == 'commodity'){
								$commodity[] = $v2['id_object'];
								$att_type = 'commodity';
							}elseif ($v2['object_type'] == 'info'){
								$info[] = $v2['id_object'];
								$att_type = 'info';
							}elseif ($v2['object_type'] == 'activity'){
								$activity[] = $v2['id_object'];
								$att_type = 'activity';
							}
						}
						if($att_type == 'commodity' && !empty($commodity)){
							//获取物品
							$wxxx[$k]['cnt'] = $this->wxreply->get_commodity_inid($commodity);
							//$wx_url .= '/home/mallcontent/';
						}elseif ($att_type == 'info' && !empty($info)){
							$wxxx[$k]['cnt'] = $this->wxreply->get_info_inid($info);
							//$wx_url .= '/home/info/';
						}elseif ($att_type == 'activity' && !empty($activity)){
							$wxxx[$k]['cnt'] = $this->wxreply->get_activity_inid($activity);
							//$wx_url .= '/home/active_details/';
						}
					}
//				}elseif($v['reply_type'] == 'image' || $v['reply_type'] == 'audio' || $v['reply_type'] == 'video'){
//                    //查询附件表
//                    $wx_attach = $this->wxreply->get_msg_attach(array('id_msg'=>$v['id_msg']));
//                    if(is_array($wx_attach)){
//                        $wxxx[$k]['url'] = $wx_attach[0]['url'];
//                    }
                }else{
					continue;
				}
			}
		}
	
		$data = array();
		$data['page_html'] = $page_arr;
		$data['wxlist'] = $wxxx;
		$data['offset'] = $offset;
		$data['page_type'] = 'wx_reply';
		return $data;
	}

		public function wx_imgreply_kill($page=0,$page_type=1){
        $type = $this->input->post('btype');
		if($type && in_array($type, array('activity','info','cmd'))){
			$page = $page ? intval($page) : 1;
			//ajax提交,根据不同的查询类型，返回相应的列表
			$this->load->model('wxreply_model','wxreply');
			$seek = $this->input->post('seek');
			$func = 'get_'.$type.'_like_title';

			$limit = 8;
			$start = ($page-1)*$limit;
			$where = array('id_business'=>$this->id_business,'id_shop'=>$this->id_shop);
	
			$like = array();
			if($type == 'cmd'){
				$like['name'] = $seek;
			}elseif ($type == 'activity'){
				$like['name'] = $seek;
			}elseif ($type == 'info') {
				$like['a.title'] = $seek;
			}

			$count_tmp = $this->wxreply->$func($where,$like,0,0);
			$list_count = count($count_tmp);
			$list = $this->wxreply->$func($where,$like,$start,$limit);
			//debug($list);
			$count_page = ceil($list_count/$limit);
	
			if($page > $count_page){
				$page = $count_page;
			}
			if($page < 1){
				$page = 1;
			}
	
			$page_arr = array();
			if($list){
				$page_arr = $this->get_page($list_count,$page,'get_select_content_kill','method',$limit);
			}

			$return_arr = array('status'=>1,'data'=>$list,'page'=>$page_arr,'count'=>$count_page,'cpage'=>$page);
			die(json_encode($return_arr));
	
		}else{
			//没有提交，$page当做编辑的id
			if(intval($page)){
				$this->load->model('wxreply_model','wxreply');
				$wx = $this->wxreply->get_biz_wxseting(array('id_msg'=>intval($page)),1);
				$wx = $wx[0];
                if($wx['reply_type'] == 'image-text' || $wx['reply_type'] == 'image' || $wx['reply_type'] == 'audio' || $wx['reply_type'] == 'video'){
                    $where = array('id_msg'=>$wx['id_msg']);
                    //查询附件信息
                    $wx_att = $this->wxreply->get_msg_attach($where);
                    if($wx['reply_type'] == 'image-text'){
                        $att_type = '';
                        $in_arr = array();
                        $wx_tmp = array();
                        foreach ($wx_att as $k => $v){
                            $att_type = $v['object_type'];
                            $in_arr[] = $v['id_object'];
                            $wx_tmp[$v['id_object']] = $v['id_attachment'];
                        }
                        $funs = 'get_'.$att_type.'_inid';
                        if($in_arr){
                            $att_data = $this->wxreply->$funs($in_arr,$page_type);
                            foreach ($att_data as $k => $v){
                                if(in_array($v['id'], array_keys($wx_tmp))){
                                    $att_data[$k]['id_attachment'] = $wx_tmp[$v['id']];
                                }else{
                                    unset($att_data[$k]);
                                }
                            }
                            $this->smarty->assign('wx_att',$att_data);
                            $this->smarty->assign('selected_tmp',implode(',', $in_arr));
                        }
                        $this->smarty->assign('att_type',$att_type=="commodity"?"cmd":$att_type);
                    }else{
                        //附件个数
                        $this->smarty->assign('attachment_count',count($wx_att));
                        //附件信息
                        $this->smarty->assign('attachment',$wx_att);
                    }
                }else{
                    //附件个数
                    $this->smarty->assign('attachment_count',0);
                }
				$this->smarty->assign('eid',$page);
				$this->smarty->assign('wx',$wx);
			}
		}
        //获取图文回复信息
        $return = $this->img_text_reply();
		$this->smarty->assign($return);

		$this->smarty->assign('box_cpage',$page);
        $this->smarty->assign('page_type','wx_imgreply');
		$this->smarty->view('wx_reply');
	}


	public function wx_imgreply($page=0,$page_type=1){
        $type = $this->input->post('btype');
		if($type && in_array($type, array('activity','info','cmd'))){
			$page = $page ? intval($page) : 1;
			//ajax提交,根据不同的查询类型，返回相应的列表
			$this->load->model('wxreply_model','wxreply');
			$seek = $this->input->post('seek');
			$func = 'get_'.$type.'_like_title';

			$limit = 8;
			$start = ($page-1)*$limit;
			$where = array('id_business'=>$this->id_business,'id_shop'=>$this->id_shop);
	
			$like = array();
			if($type == 'cmd'){
				$like['name'] = $seek;
			}elseif ($type == 'activity'){
				$like['name'] = $seek;
			}elseif ($type == 'info') {
				$like['a.title'] = $seek;
			}

			$count_tmp = $this->wxreply->$func($where,$like,0,0);
			$list_count = count($count_tmp);

			if($type == 'activity'){
				$where_kill = array('id_business'=>$this->id_business,'object_type'=>'community');
				$list = $this->wxreply->get_activity_like_title_kill($where_kill,$like,$start,$limit);
			}else{			
				$list = $this->wxreply->$func($where,$like,$start,$limit);
			}

			//debug($list);
			$count_page = ceil($list_count/$limit);
	
			if($page > $count_page){
				$page = $count_page;
			}
			if($page < 1){
				$page = 1;
			}
	
			$page_arr = array();
			if($list){
				$page_arr = $this->get_page($list_count,$page,'get_select_content','method',$limit);
			}

			$return_arr = array('status'=>1,'data'=>$list,'page'=>$page_arr,'count'=>$count_page,'cpage'=>$page,'data_kill'=>$list_kill);
			die(json_encode($return_arr));
	
		}else{
			//没有提交，$page当做编辑的id
			if(intval($page)){
				$this->load->model('wxreply_model','wxreply');
				$wx = $this->wxreply->get_biz_wxseting(array('id_msg'=>intval($page)),1);
				$wx = $wx[0];
                if($wx['reply_type'] == 'image-text' || $wx['reply_type'] == 'image' || $wx['reply_type'] == 'audio' || $wx['reply_type'] == 'video'){
                    $where = array('id_msg'=>$wx['id_msg']);
                    //查询附件信息
                    $wx_att = $this->wxreply->get_msg_attach($where);
                    if($wx['reply_type'] == 'image-text'){
                        $att_type = '';
                        $in_arr = array();
                        $wx_tmp = array();
                        foreach ($wx_att as $k => $v){
                            $att_type = $v['object_type'];
                            $in_arr[] = $v['id_object'];
                            $wx_tmp[$v['id_object']] = $v['id_attachment'];
                        }
                        $funs = 'get_'.$att_type.'_inid';
                        if($in_arr){
                            $att_data = $this->wxreply->$funs($in_arr,$page_type);
                            foreach ($att_data as $k => $v){
                                if(in_array($v['id'], array_keys($wx_tmp))){
                                    $att_data[$k]['id_attachment'] = $wx_tmp[$v['id']];
                                }else{
                                    unset($att_data[$k]);
                                }
                            }
                            $this->smarty->assign('wx_att',$att_data);
                            $this->smarty->assign('selected_tmp',implode(',', $in_arr));
                        }
                        $this->smarty->assign('att_type',$att_type=="commodity"?"cmd":$att_type);
                    }else{
                        //附件个数
                        $this->smarty->assign('attachment_count',count($wx_att));
                        //附件信息
                        $this->smarty->assign('attachment',$wx_att);
                    }
                }else{
                    //附件个数
                    $this->smarty->assign('attachment_count',0);
                }
				$this->smarty->assign('eid',$page);
				$this->smarty->assign('wx',$wx);
			}
		}
        //获取图文回复信息
        $return = $this->img_text_reply();
		$this->smarty->assign($return);

		$this->smarty->assign('box_cpage',$page);
        $this->smarty->assign('page_type','wx_imgreply');
		$this->smarty->view('wx_reply');
	}
	
	
	public function save_wx_reply(){
        $reply_type = $this->input->post('reply_type');
        $select_btype = $this->input->post('select_btype');
        $select_btype = $select_btype == 'cmd' ? 'commodity' : $select_btype;
		//post有效提交
		if($reply_type){
			//debug($_POST);
			$this->load->model('wxreply_model','wxreply');
			//检查有无id，是否为编辑
            $eid = $this->input->post('eid');
            $wx_rule = $this->input->post('wx_rule');
            $img_src = $this->input->post('image_src');
            $media_id = 0;
            $file_size = 0;
            $image_url = '';
            $type = 'text';
            if($reply_type == '3' || $reply_type == '4' || $reply_type == '5'){
                if($reply_type == '3'){
                    $type = 'image';
                }elseif($reply_type == '4'){
                    $type = 'voice';
                }elseif($reply_type == '5'){
                    $type = 'video';
                }
                if($img_src != '' && $img_src != ' '){
                    //获取图片路径+名字
                    $file_names = $this->file_url_name('keyword_reply',$img_src,0);
                    //获取图片大小
                    $data = $this->get_url_size($file_names);
                    $file_size = $data['file_size'];
//                $image_url = $data['image_url'];

                    //获取token
                    $access_token = $this->get_access_token($this->id_business,$this->id_shop);
                    //上传附件文件
                    $r = $this->upload_wx_attachment($type,$access_token,$img_src,'keyword_reply');
                    $return = json_decode($r,true);
                    //获得mediaid
                    $media_id = $return['media_id'];
                }
            }

			if($eid){
				//先编辑回复内容的信息
				$update_data['keyword'] = trim(strip_tags($wx_rule));

				if($reply_type == '2'){//图文
                    //删除附件
                    $this->wxreply->delete_reply_attachment(array('id_msg'=>intval($eid)));
                    $type = 'image-text';
					//重新添加附件
					$add_atta = array();
					if($this->input->post('cnt_selected') && $eid){
						$object_type = '';
						if($select_btype && in_array($select_btype,array('commodity','info','activity'))){
							$object_type = $select_btype;
						}
						$id_arr = $this->input->post('cnt_selected');
						foreach ($id_arr as $k => $v){
							$add_atta[$k]['id_msg'] = intval($eid);
							$add_atta[$k]['object_type'] = $object_type;
							$add_atta[$k]['id_object'] = $v;
						}
						//执行批量写入数据
						$this->wxreply->add_bath_attach($add_atta);
						$update_data['reply_type'] =  $type=='voice'?'audio':$type;
                        $this->wxreply->update_wx_msg(array('id_msg'=>intval($eid)),$update_data);
					}
				}elseif($reply_type == '3' || $reply_type == '4' || $reply_type == '5'){//图片 音频 视频
                    $where_msg = 'id_msg = ' . intval($eid);
                    if($img_src != '' && $img_src != ' '){
                        //删除附件
                        $this->wxreply->delete_reply_attachment($where_msg);
                        //查询回复表的回复类型
//                    $msg = $this->wxreply->get_biz_wxseting($where_msg,1);
                        $insert_d = array(
                            'id_msg' => intval($eid),
                            'object_type' => '',
                            'id_object' => '',
                            'url' => $img_src,
                            'id_media' => $media_id,
                            'size' => $file_size,
                            'last_time' => date('Y-m-d H:i:s', time())
                        );
                        //并插入一条新的附件信息
                        $this->wxreply->insert_attachment($insert_d);
                    }

                    //更新回复表信息
                    $update_data['reply_type'] = $type=='voice'?'audio':$type;
                    if($reply_type == '5'){
                        $video_content = $this->input->post('video_content');
                        $update_data['reply'] = $video_content;
                    }else{
                        $update_data['reply'] = '';
                    }
                    //更新回复表的回复类型
                    $this->wxreply->update_wx_msg($where_msg,$update_data);
                }else{
                    //删除附件
                    $this->wxreply->delete_reply_attachment(array('id_msg'=>intval($eid)));
					$update_data['reply_type'] = $type=='voice'?'audio':$type;
					$update_data['reply'] = trim($this->input->post('wx_content'));
                    $this->wxreply->update_wx_msg(array('id_msg'=>intval($eid)),$update_data);
				}
				$this->returen_status(1, $this->lang->line('edit_data_success'));
			}else{
				//新增规则
				$add_rule['id_business'] = $this->id_business;
				$add_rule['id_shop'] = $this->id_shop;
				$add_rule['msg_type'] = 'text';
				$add_rule['keyword'] = trim(strip_tags($wx_rule));

				if($reply_type == '1'){
					$add_rule['reply_type'] = 'text';
					$add_rule['reply'] = $this->input->post('wx_content');
					//写入数据库完成
					$this->wxreply->add_wx_msg($add_rule);
					$this->returen_status(1, $this->lang->line('add_data_success'));
				}elseif($reply_type == '2'){
					$add_rule['reply_type'] = 'image-text';
					//取得写入数据库的insert_id
	
					$id_msg = $this->wxreply->add_wx_msg($add_rule);
	
					$object_type = '';
					if($select_btype && in_array($select_btype,array('commodity','info','activity'))){
						$object_type = $select_btype;
					}
					$add_atta = array();
					if($this->input->post('cnt_selected') && $id_msg){
						$id_arr = $this->input->post('cnt_selected');
						foreach ($id_arr as $k => $v){
							$add_atta[$k]['id_msg'] = $id_msg;
							$add_atta[$k]['object_type'] = $object_type;
							$add_atta[$k]['id_object'] = $v;
						}
						//执行批量写入数据
						$this->wxreply->add_bath_attach($add_atta);
						$this->returen_status(1, $this->lang->line('add_data_success'));
					}
				}elseif($reply_type == '3' || $reply_type == '4' || $reply_type == '5'){
                    if($reply_type == '3'){
                        $add_rule['reply_type'] = 'image';
                    }elseif($reply_type == '4'){
                        $add_rule['reply_type'] = 'audio';
                    }elseif($reply_type == '5'){
                        $add_rule['reply_type'] = 'video';
                        $add_rule['reply'] = $this->input->post('video_content');
                    }
                    //添加回复表信息
                    $id_msg = $this->wxreply->add_wx_msg($add_rule);

                    $insert_d = array(
                        'id_msg' => $id_msg,
                        'object_type' => '',
                        'id_object' => '',
                        'url' => $img_src,
                        'id_media' => $media_id,
                        'size' => $file_size,
                        'last_time' => date('Y-m-d H:i:s', time())
                    );
                    //添加回复附件表信息
                    $this->wxreply->insert_attachment($insert_d);
                    $this->returen_status(1, $this->lang->line('add_data_success'));
                }
			}
		}
	}

	public function wx_attach_delete($id_msg,$att_id){
		$where = array(
				'id_msg'=>intval($id_msg),
				'id_attachment' => intval($att_id)
		);
		 
		$this->load->model('wxreply_model','wxreply');
		$this->wxreply->delete_reply_attachment($where);
		 
		 
	}
	
	
	/**
	 * 获取微信二维码
	 */
	public function ticket(){
		$host = 'http://'.$_SERVER['HTTP_HOST'];
		$path = '/attachment/business/ticket/';
		$file = $path.$this->id_business.'/'.$this->id_shop.'.jpg';
		//查看二维码是否存在，不存在则取
		if(!file_exists(DOCUMENT_ROOT.$file)){
			//取图片并保存
			$token = $this->get_access_token($this->id_business,$this->id_shop);
			if($token){
				$curl = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$token;
				$curl_data = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": 100}}}';
				$result = request_curl($curl,$curl_data);
				$result = json_decode($result,1);
				if($result['ticket']){
					if(!is_dir(DOCUMENT_ROOT.$path.$this->id_business.'/')){
						@mkdir(DOCUMENT_ROOT.$path.$this->id_business.'/',0777,TRUE);
					}
					$curl_img = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($result['ticket']);
					$img = @request_curl($curl_img,'',1);
					@file_put_contents(DOCUMENT_ROOT.$file, $img);
				}else{
					echo '<script type="text/javascript">alert("'.$result['errmsg'].'");</script>';
				}
			}
		}
		$this->smarty->assign('img',$host.$file);
		$this->smarty->view('ticket');
	}

    /*
     * zxx
     * 微信快速回复相关功能
     * */
    function wx_customer_msg(){
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        $search_key = $this->input->post('search_key') ? $this->input->post('search_key') : '';

        $return = $this->get_quick_reply($offset,$search_key);
        $this->smarty->assign($return);
        $this->smarty->assign('offset',$offset);
        $this->smarty->assign('page_type','quick_reply');

        if(intval($this->input->post('ispage')) == 1){
            echo $this->smarty->view('lists');
        }else{
            $this->smarty->view('list_quick_reply');
        }
    }

    /*
     * zxx
     * 删除微信快速回复信息
     * */
    function delete_quick_reply(){
        $id_msg = $this->input->post('id_msg');
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        $search_key = $this->input->post('search_key') ? $this->input->post('search_key') : '';
        $this->load->model('customer_model','customer');
        //删除快速回复信息
        $where = 'id_customer_msg = ' . $id_msg;
        $re = $this->customer->delete_quick_msg($where);
        $return = $this->customer->get_reply($where);
        if($return){
            foreach($return as $r){
                $re_reply = $this->customer->delete_quick_reply($where);
                $where1 = 'id_reply = '.$r['id_reply'];
                //如果本条回复里面包含了图片。音频。视频附件的话、。先删除附件文件，再删除该条数据库数据
                if($r['reply_type'] == 'audio' || $r['reply_type'] == 'video'|| $r['reply_type'] == 'image'){
                    $return_att = $this->customer->get_reply_attachment($where1);
                    foreach($return_att as $r_att){
                        //获取本地文件地址
                        $path = $this->file_url_name('reply',$r_att['view_url']);
                        if(!file_exists($path) && !is_readable($path)){
//                        $this->returen_status(0,$filename.'文件不在或只有只读权限~~',$data);
                        }else{
                            unlink($path);
                        }
                    }
                }
                $this->customer->delete_reply_attachment($where1);
            }
        }

        $return = $this->get_quick_reply($offset,$search_key);

        $this->smarty->assign($return);
        $this->smarty->assign('offset',$offset);
        $this->smarty->assign('page_type','quick_reply');

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
     * 添加快速回复信息，并向微信发送客服请求
     * */
    function quick_reply(){
        $id_msg = $this->input->post('id_msg');
        $id_open = $this->input->post('id_open');
        $msg_content = $this->input->post('msg_content');
        //获取token
        $access_token = $this->get_access_token($this->id_business,$this->id_shop);
        //发送客服信息   文字
        $info['msg_content'] = $msg_content;
        $return = $this->to_wx_attachment('text',$id_open,$info,$access_token,1);
        if($return['status'] == 1){
            $this->load->model('customer_model','customer');
            $data = array(
                'id_customer_msg'=>$id_msg,
                'reply_type'=>'text',
                'reply_content'=>$msg_content,
                'created'=>date('Y-m-d H:i:s', time())
            );
            $this->customer->insert_quick_reply($data);
        }
        die(json_encode($return));
    }

	
	public function analysis($type=0){
		//echo date('Y-m-d',strtotime('-1 weeks'));
		$type = in_array(intval($type), array(1,2,3)) ? intval($type) : 1;
		
		$where = $where2 = $where3 = $where4 = $where5= array();
		$where['id_business'] =$where2['id_business']= $where3['id_business']= $this->id_business;
		$where['id_shop']= $where2['id_shop'] = $where3['id_shop'] = $this->id_shop;
        $where5 = $where;
		$where5['state'] = 'subscribe';
		
		$this->load->model('business_model','business');
		//总数
		$cnt = $this->business->get_user_count($where5);
		
		if($type == 1){
		   $yesterday = date('Y-m-d',strtotime('-1 days'));
			$where["DATE_FORMAT(created,'%Y-%m-%d')"] = $yesterday;
            $where2["DATE_FORMAT(update_time,'%Y-%m-%d')"] = $yesterday;
			$where3["DATE_FORMAT(created,'%Y-%m-%d')"] = $yesterday;
		}elseif ($type == 2){
			//上周
			$mon = date('Y-m-d',strtotime('-1 week Sunday -6 days'));
			$sun = date('Y-m-d',strtotime('-1 week Sunday'));
			
			$where["DATE_FORMAT(created,'%Y-%m-%d') >= "] = $mon;
			$where["DATE_FORMAT(created,'%Y-%m-%d') <= "] = $sun;
			
            $where2["DATE_FORMAT(update_time,'%Y-%m-%d') >= "] = $mon;
			$where2["DATE_FORMAT(update_time,'%Y-%m-%d') <= "] = $sun;
            
            
			$where3["DATE_FORMAT(created,'%Y-%m-%d') >= "] = $mon;
			$where4["DATE_FORMAT(created,'%Y-%m-%d') <= "] = $sun;
		}elseif ($type == 3){
			$month = date('Y-m',strtotime('-1 months'));
			$where["DATE_FORMAT(created,'%Y-%m')"] = $month;
			$where2["DATE_FORMAT(update_time,'%Y-%m')"] = $month;
			$where3["DATE_FORMAT(created,'%Y-%m')"] = $month;
		}
		
		//关注数,加时间
        
		$sub = $this->business->get_user_count($where);
		
		//取消关注数
		$where2['state'] = 'unsubscribe';
		$usub = $this->business->get_user_count($where2);
		//exit;
		$msgcnt = $this->business->get_msg_count($where3,$where4);
		$this->smarty->assign('now',date('Y-m-d',time()));
		$this->smarty->assign('msg',$msgcnt);
		$this->smarty->assign('sub',$sub);
		$this->smarty->assign('usub',$usub);
		$this->smarty->assign('cnt',$cnt);
		$this->smarty->assign('active',$type);
		$this->smarty->view('analysis');
	}
	
    public function analysis_list(){
        $year = $_POST['year'];
        $month = $_POST['month']; 
        $this->load->model('business_model','business');
        $time = $year.'-'.$month;
        $list = $this->business->get_date_list($time,$this->id_business,$this->id_shop);
        $return = array();
        for($i=0;$i<count($list);$i++){
            $date = $list[$i];
            $new_sub = $this->business->get_new_sub_count($this->id_business,$this->id_shop,$date);
            $total = $this->business->get_new_sub_total($this->id_business,$this->id_shop,$date);
            $cancel_sub =  $this->business->get_cancel_sub($this->id_business,$this->id_shop,$date);
            $add_sub = $new_sub - $cancel_sub;
            $return[$i]= array('date'=>$date,'new_sub'=>$new_sub,'cancel_sub'=>$cancel_sub,'add_sub'=>$add_sub,'total'=>$total);
        }
        die(json_encode($return));
        //$return = array('date'=>$date,'new_sub'=>$new_sub,'cancel_sub'=>$cancel_sub,'add_sub'=>$add_sub,'total'=>$total);
       
    }
	public function exp_anly()
    {
        $date = !empty($_GET['d']) ? trim($_GET['d']) : header("location:/biz/");

        $this->load->model('business_model', 'business');
        $list = $this->business->get_date_list($date, $this->id_business, $this->id_shop);
        $list = array_reverse($list);
        $return = array();
        $data ='<table border="1" align="center" width="900" height="300">';
        $data.='<tr align="center" ><td>时间</td><td>新关注人数</td><td>取消关注人数</td><td>净增关注人数</td><td>累积关注人数</td></tr>';
        for ($i = 0; $i < count($list); $i++) {
            $date = $list[$i];
            $new_sub = $this->business->get_new_sub_count($this->id_business, $this->id_shop, $date);
            $total = $this->business->get_new_sub_total($this->id_business, $this->id_shop, $date);
            $cancel_sub = $this->business->get_cancel_sub($this->id_business, $this->id_shop, $date);
            $add_sub = $new_sub - $cancel_sub;
            //$data .= $date.",".$new_sub.",".$cancel_sub.",".$add_sub.",".$total.",\n";
            $date = iconv('utf-8', 'gbk', $date);
            $data.='<tr align="center"><td>'.$date.'</td><td>'.$new_sub.'</td><td>'.$cancel_sub.'</td><td>'.$add_sub.'</td><td>'.$total.'</td></tr>';
        }
        $data.='</table>';
        $filename = '用户分析-'.date('Y-m-d') ; //设置文件名
        print_excel($filename,$data); //导出

    }
    /*
     * zxx
     * 检测快速回复是否过期
     * */
    function are_expired(){
        $id_msg = $this->input->post('id_msg');
        $id_open = $this->input->post('id_open');

        $this->load->model('customer_model','customer');
        $where = 'cm.id_business = '.$this->id_business . ' and cm.id_shop = '.$this->id_shop .' and cm.id_customer_msg = '.$id_msg . ' and cm.id_open = "'.$id_open . '"';

        //获取快速回复信息
        $quick_reply = $this->customer->get_quick_reply($where);
        $is_expired = 0;
        foreach($quick_reply as $val){
            $one = strtotime($val['created']);
            $tow = strtotime(date('y-m-d h:i:s'));
            $cle = $tow - $one; //得出时间戳差值

            $m = ceil($cle/60);//得出一共多少分钟
            if($m > 48*60){
                $is_expired = 0;
            }else{
                $is_expired = 1;
            }
        }
        $resp = array(
            'status' => $is_expired,
            'msg' => '',
            'data' => ''
        );
        die(json_encode($resp));
    }

    /*
     * zxx
     * 回复信息列表
     * */
    function wx_reply_list($id_customer_msg,$id_open){
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        if(empty($id_customer_msg)){
            $id_customer_msg = $this->input->post('id_customer_msg') ;//回复id
        }

        $return = $this->get_reply($id_open,$offset);
        $this->smarty->assign($return);

        //获取图文回复信息
        $returns = $this->img_text_reply();
        $this->smarty->assign($returns);

        $this->smarty->assign('offset',$offset);
        $this->smarty->assign('id_customer_msg',$id_customer_msg);
        $this->smarty->assign('id_open',$id_open);
        $this->smarty->assign('page_type','img_text_reply');

        if(intval($this->input->post('ispage')) == 1){
            echo $this->smarty->view('lists');
        }else{
            $this->smarty->view('reply_msg');
        }
    }

    /*
     * zxx
     * 回复信息处理，并向微信发送客服请求
     * */
    function save_reply(){
        $id_customer_msg = $this->input->post('id_customer_msg');
        $type = $this->input->post('type');
        $content = $this->input->post('content') ? $this->input->post('content') : '';

        $file_name = $this->input->post('file_name');

        $video_content = $this->input->post('video_content') ? $this->input->post('video_content') : '';
        if($type == 'video'){
            $content = $video_content;
        }
        $data = array(
            'id_customer_msg'=>$id_customer_msg,
            'reply_type'=>$type,
            'reply_content'=>$content,
            'created'=>date('Y-m-d H:i:s', time())
        );
        $this->load->model('customer_model','customer');
        //插入新信息
        $id_reply = $this->customer->insert_quick_reply($data);

        //查询id_open
        $where = 'id_customer_msg = ' . $id_customer_msg;
        $result = $this->customer->get_openid($where);
        $id_open = $result[0]['id_open'];

        $path = $this->file_url_name('reply',$file_name,0);
        $data = $this->get_url_size($path);
        $file_size = $data['file_size'];

        //图文信息的所属类型（活动。内容。或物品）和所属类型id
        $object_type = $this->input->post('select_btype');
        $id_object = $this->input->post('cnt_select');
        $object_type = $object_type=='cmd'?'commodity':$object_type;
        if($type == 'image-text')
        {
            if(count($id_object)>0){
                foreach($id_object as $id){
                    $data_attachment = array(
                        'id_reply'=>$id_reply,
                        'view_url'=>'',
                        'size'=>0,
                        'object_type'=>$object_type,
                        'id_object'=>$id
                    );

                    //插入新信息
                    $this->customer->insert_reply_attachment($data_attachment);
                }
            }else{
                $data_attachment = array(
                    'id_reply'=>$id_reply,
                    'view_url'=>$file_name,
                    'size'=>$file_size,
                    'object_type'=>'',
                    'id_object'=>''
                );
                //插入新信息
                $this->customer->insert_reply_attachment($data_attachment);
            }
        }else{
            $data_attachment = array(
                'id_reply'=>$id_reply,
                'view_url'=>$file_name,
                'size'=>$file_size,
                'object_type'=>'',
                'id_object'=>''
            );
            //插入新信息
            $this->customer->insert_reply_attachment($data_attachment);
        }

        //获取token
        $info=array();
        $access_token = $this->get_access_token($this->id_business,$this->id_shop);
        if($type == 'image' || $type == 'audio' || $type == 'video'){
            //上传附件文件
            $r = $this->upload_wx_attachment($type,$access_token,$file_name);
            $return = json_decode($r,true);
            //获得mediaid
            $info['media_id'] = $return['media_id'];
        }
        if($type == 'voice'){
            $type = 'audio';
        }
        if($type == 'image-text'){
            $info['image-text'] = array();

            if($object_type == 'activity'){
                $this->load->model('activity_model','activity');
                foreach($id_object as $key=>$ids){
                    $where = 'id_activity = ' . $ids;
                    $return = $this->activity->get_activity($where);
                    foreach($return as $v){
                        $info['image-text'][$key]['title'] = $v['name'];
                        $info['image-text'][$key]['description'] = substr(str_replace('&nbsp;','',strip_tags(html_entity_decode($v['content'],ENT_COMPAT,'utf-8'))),0,50);
                        $info['image-text'][$key]['url'] = "http://".$_SERVER['HTTP_HOST']."/wapi/".$this->id_business."/".$this->id_shop."/home/active_details/".$ids;
                        $image_url = get_img_url($v['image_url'],'activity',1,'bg_mr.png');
                        $info['image-text'][$key]['pic_url'] = $image_url;
                    }
                }
            }elseif($object_type == 'commodity'){
                $this->load->model('commodity_model','commodity');
                foreach($id_object as $key=>$ids){
                    $where = 'id_commodity = ' . $ids;
                    $return = $this->commodity->get_commodity_introduction($where);
                    foreach($return as $v){
                        $info['image-text'][$key]['title'] = $v['name'];
                        $info['image-text'][$key]['description'] = substr(str_replace('&nbsp;','',strip_tags(html_entity_decode($v['descript'],ENT_COMPAT,'utf-8'))),0,50);
                        $info['image-text'][$key]['url'] = "http://".$_SERVER['HTTP_HOST']."/wapi/".$this->id_business."/".$this->id_shop."/home/foodcontent/".$ids.'/0/2';
                        $image_url = get_img_url($v['image_url'],'commodity',1,'bg_mr.png');
                        $info['image-text'][$key]['pic_url'] = $image_url;
                    }
                }
            }elseif($object_type == 'info'){
                $this->load->model('info_model','info');
                foreach($id_object as $key=>$ids){
                    $where = 'i.id_info = ' . $ids;
                    $return = $this->info->get_info_introduction($where,0, 0, '',0);

                    foreach($return as $v){
                        $info['image-text'][$key]['title'] = $v['title'];
                        $info['image-text'][$key]['description'] = substr(str_replace('&nbsp;','',strip_tags(html_entity_decode($v['content'],ENT_COMPAT,'utf-8'))),0,50);
                        $info['image-text'][$key]['url'] = "http://".$_SERVER['HTTP_HOST']."/wapi/".$this->id_business."/".$this->id_shop."/home/content/".$ids;
                        $image_url = get_img_url($v['show_url'],'content',1,'bg_mr.png');
                        $info['image-text'][$key]['pic_url'] = $image_url;
                    }
                }
            }
            //发送客服信息   图文
            $this->to_wx_attachment('image-text',$id_open,$info,$access_token);
        }elseif($type == 'image'){
            //发送客服信息   文字
            $this->to_wx_attachment('image',$id_open,$info,$access_token);
        }elseif($type == 'audio'){
            //发送客服信息   音频
            $this->to_wx_attachment('audio',$id_open,$info,$access_token);
        }elseif($type == 'video'){
            $info['msg_content'] = $video_content;
            //发送客服信息   视频
            $this->to_wx_attachment('video',$id_open,$info,$access_token);
        }elseif($type == 'text'){
            $msg_content = $content;
            preg_match_all("/<img\s.*?>/i",$msg_content,$arr);
            if(count($arr) > 0){
                foreach($arr as $as){
                    foreach($as as $a){
                        $rep_info = '';
                        if(strpos($a,'emoticons/images/2.gif')>0){//"色"
                            $rep_info = '/::B';
                        }elseif(strpos($a,'emoticons/images/4.gif')>0){//"得意"
                            $rep_info = '/:8-)';
                        }elseif(strpos($a,'emoticons/images/0.gif')>0){//"微笑"
                            $rep_info = '/::)';
                        }elseif(strpos($a,'emoticons/images/1.gif')>0){//撇嘴
                            $rep_info = '/::~';
                        }elseif(strpos($a,'emoticons/images/3.gif')>0){//"发呆"
                            $rep_info = '/::|';
                        }elseif(strpos($a,'emoticons/images/5.gif')>0){//"流泪"
                            $rep_info = '/::<';
                        }
                        $msg_content = str_replace($a,' '.$rep_info,$msg_content);
                    }
                }
            }
            $msg_content = str_replace('&nbsp;',' ',$msg_content);
            $msg_content = str_replace('<br/>','',$msg_content);
            $msg_content = str_replace('<br />','',$msg_content);
            $info['msg_content'] = $msg_content;
            //发送客服信息   视频
            $this->to_wx_attachment('text',$id_open,$info,$access_token);
        }
    }
    
    public function show_media(){
    	
    	$name = $_GET['name'];
    	$type = $_GET['type'];
    	$host = 'http://'.$_SERVER['HTTP_HOST'];
    	$path = '/attachment/weixin/';
    	$cdir = str_split(strtolower($name),1);
    	$tmp = array_chunk($cdir, 4);
    	if($tmp[0]){
    		$dir = implode('/', $tmp[0]);
    	}
    	$path .= $dir.'/'.$name;
    	
    	$media_url = '';
    	if(file_exists(DOCUMENT_ROOT.$path)){
    		$media_url = $host.'/attachment/weixin/'.$dir.'/'.$name;
    	}

    	$this->smarty->assign('type',$type);
    	$this->smarty->assign('media_url',$media_url);
    	$this->smarty->view('media');
    }

    /**
     * 下载微信发送的用户音频消息
     * song  音频文件文件名
     */
    public function download()
    {
        $song = $_GET['song'];
        $filename = get_img_url($song, 'weixin',1,'pic_005.png',1);
        if(!file_exists($filename)){
            echo "<script>alert('没有该文件');window.history.go(-1)</script>";
            return ;
        }
        //文件的类型
        header('Content-type: application/octet-stream');
        //下载显示的名字
        header('Content-Disposition: attachment; filename="'. $song . '"');

        $re = readfile("$filename");
        exit();
    }
}
/* End of file system.php */