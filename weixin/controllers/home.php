<?php
/**
 * 
 * @copyright(c) 2013-11-25
 * @author msi
 * @version Id:index.php
 */


class Home extends WX_Controller
{

	/**
     * 企业简介
     */
    public function about()
    {
        $business_id = $this->bid;
        //获取商家信息
        $this->load->model('business_model','business');
        $where = 'ba.object_type = \'bn\' and ba.id_object = 0';
        $merchant = $this->business->get_business_info($business_id,$where);
        $images = explode(',', $merchant[0]['image_url']);
        foreach($images as $value){
            $path[] = get_img_url($value,'attachment',0,'bg_mr.png');
        }

        $this->load->model('shop_model','shop');
        //取得企业简介的的title
        $title = $this->shop->get_map_title($business_id,'about');
        if($title[0]){
            $title = $title[0];
            $this->smarty->assign('title',$title['name']);
        }

        $this->smarty->assign('cook',$_COOKIE['admin']);
        $this->smarty->assign('count',count($path));
        $this->smarty->assign('images', $path);
        $this->smarty->assign('introduction',html_entity_decode($merchant[0]['introduction'],ENT_COMPAT,'utf-8'));
        $this->smarty->view('about');
	}


    /**
     * 门店简介
     */
    public function about_shop()
    {
        $this->load->model('shop_model','shop');

        $id_business = $this->bid;
        $id_shop =  $this->sid;
        $where = 'id_business = ' . $id_business . ' AND id_shop = ' . $id_shop;
        //获取门店信息
        $shop = $this->shop->get_shop_introduction($where);
        $path = get_img_url($shop[0]['image_url'], 'shop',0,'bg_mr.png');

        $this->smarty->assign('path',$path);
        $this->smarty->assign('introduction',html_entity_decode($shop[0]['introduction'],ENT_COMPAT,'utf-8'));
        $this->smarty->assign('title','门店介绍');
        $this->smarty->view('about_shop');
    }


    /**
	 * 积分商城
	 */
	public function consume($p1,$p2,$p3){
		$limit = 7;
		$page = $p1 ? intval ( $p1 ) : 1;

        $business_id = $this->bid;
        $shop_id = $this->sid;
		
		// 获取商城列表
		$this->load->model ( 'commodity_model', 'commodity' );
		$this->load->model ( 'goods_model', 'goods' );
		$where = 'm.state = \'up\' AND m.id_business = ' . $business_id . ' AND m.id_shop = ' . $shop_id . ' and c.state = 1';

		$commodity = $this->commodity->get_commodity_list ( $where, $page, $limit, 'c.weight DESC,m.created DESC');
		foreach ( $commodity as $key => $value ) {
			$commodity [$key] ['path'] = get_img_url ( $value ['image_url'], 'commodity' );
			// 获取评论
			$where = 'object_type = \'commodity\' AND id_object =' . $value ['id_commodity'] .' and state=1 AND id_business = ' . $business_id . ' AND id_shop = ' . $shop_id;
			$commodity [$key] ['count'] = $this->goods->get_review_count_by_cdn ( $where );
		}
		$this->smarty->assign ( 'commodityList', $commodity );
		$this->smarty->assign('cpage',$page);
		$this->smarty->view ( 'consume' );
	}


    public function mallcontent($number,$id_commodity,$id_mall){
        if(!$number){
            $number = $this->input->post('num');
        }

        //获取授权用户openid
        $id_open = $this->get_open_ids($number,'mallcontent',$id_commodity,$id_mall,1);
    }

    public function mallcontent2($number,$id_commodity,$id_mall){
        //获取授权用户openid
        $id_open = $this->get_open_ids($number,'mallcontent',$id_commodity,$id_mall,1);
        if(!$id_open){
            $id_open = 0;
        }
        Header('Location: '.$this->url.'/home/mallcontent1/'.$id_open.'/'.$id_commodity.'/'.$id_mall);
    }


    /**
     * 商城详情
     */
    public function mallcontent1($id_open,$id_commodity,$id_mall)
    {
        $this->load->model('commodity_model','commodity');
        $this->load->model('goods_model','goods');
        //获取商城列表
        $id_business = $this->bid;
        $id_shop =  $this->sid;
        //获取上一条下一条商城id
        $where = 'm.state = \'up\' AND m.id_business = ' . $id_business;
        //if( $this->type == 1){
        $where .= ' AND m.id_shop = ' . $id_shop .' and c.id_commodity = '.$id_commodity;
        //}
        $commodities = $this->commodity->get_commodity_list ( $where, 0, 0, 'c.weight DESC,m.created DESC');

        $order = 0;
        foreach ( $commodities as $key => $value ) {
            if($value['id_mall'] == $id_mall){
                $order = $key;
            }
        }

        if( count($commodities) <= 0){
            echo '<script>alert("暂无此商品");javascript:history.go(-1);</script>';
            exit;
        }
        $commodities[$order]['path'] = get_img_url($commodities[$order]['image_url'], 'commodity');
        //评论列表
        $where = 'object_type = \'commodity\' AND id_object ='.$commodities[$order]['id_commodity'] .' and state=1 and id_business = '.$id_business . ' and id_shop = '.$id_shop;
        $review = $this->goods->get_review_list_by_cdn($where);
        foreach( $review as $key=>$value){
            $review[$key]['phone'] = substr($value['phone_number'],0,3).'****'.substr($value['phone_number'],7,4);
        }

        $this->smarty->assign('commodity',$commodities[$order]);
        $this->smarty->assign('review',$review);
        $this->smarty->assign('last',$commodities[$order-1]['id_mall']);
        $this->smarty->assign('next',$commodities[$order+1]['id_mall']);

        $this->load->model('business_model','business');
        $where_sub = 'id_business = ' . $this->bid . ' and id_shop = ' . $this->sid . ' and id_open = \'' . $id_open.'\'';
        $return = $this->business->get_business_sub($where_sub);
        $cus_data['id_open'] = $id_open;
        $can_review = 0;
        $nick_name = '';
        if($return){
            $can_review = 1;
            $nick_name = $return[0]['nick_name'];
        }
        $cus_data['can_review'] = $can_review;
        $cus_data['nick_name'] = $nick_name;
        $this->smarty->assign($cus_data);

		$this->smarty->view('mallcontent');
	}
	
	public function music($page,$new){
        $limit = 10;
		$where = array();
    	if($this->type == 1){
    		$where['state'] = 1;
    		$where['id_business'] = $this->bid;
    		$where['id_shop'] = $this->sid;
    	}else{
    		$where['state'] = 1;
    		$where['id_business'] = $this->bid;
    	}
    	
    	$active = 0;
    	$order = 'created DESC';
    	$title = '';
    	if($new == 'weight'){
    		$active = 1;
    		$order = 'weight DESC,'.$order;
    		$title = '歌曲排行';
    		$this->smarty->assign('weight','weight');
    	}else{
    		$title = '新歌推荐';
    	}
    	$page = intval($page) < 1 ? 1 : intval($page);
    	
    	$this->load->model('song_model','song');
    	
    	$total = $this->song->get_song_count($where);
    	$count = ceil($total/$limit);
    	if($page > $count){
    		$page = $count;
    	}
    	
    	$slist = $this->song->get_song_list($where,$order,$page,$limit);
    	
    	$this->smarty->assign('slist',$slist);
    	$this->smarty->assign('cpage',$page);
    	$this->smarty->assign('cindex',$limit);
    	$this->smarty->assign('active',$active);
    	$this->smarty->assign('title',$title);
    	
    	$this->smarty->view('music2');
	}

    /*
     * zxx
     * $offset 页码
     * $class_type  分类id(可是多个 如：18-19-20)
     * $id_class 当前显示分类id
     * */
    public function music_type($offset,$class_type,$id_class){
        $page = 10;
        $where = array();
        if($this->type == 1){
            $where['state'] = 1;
            $where['id_business'] = $this->bid;
            $where['id_shop'] = $this->sid;
        }else{
            $where['state'] = 1;
            $where['id_business'] = $this->bid;
        }
        $order = 'state desc,weight desc';
        $title = '';

        $id_classes = explode('-',$class_type);
        $id_class_ = str_replace('-',',',$class_type);
        $ic_num = count($id_classes);
        if($ic_num > 0){
            $this->load->model('commodity_model','commodity');
            //获取歌曲类别。选项卡头
            $where_t = 'type = "song" and id_business = ' . $this->bid . ' and id_class in ('.$id_class_.')';
            $song_class = $this->commodity->get_commodity_class($where_t);
            $this->smarty->assign('song_class',$song_class);

            $slist = array();
            if(count($song_class) > 0){
                if(!$id_class){
                    $id_class = $song_class[0]['id_class'];
                }
                //获取歌曲title
                if($id_class){
                    $where_c = 'id_class = ' . $id_class .' and id_business = ' . $this->bid;
                    $song_name = $this->commodity->get_commodity_class($where_c);
                    $title = $song_name[0]['name'];
                }
                $this->load->model('song_model','song');

                $where['id_class'] = intval($id_class);
                $offset = intval($offset) < 1 ? 1 : intval($offset);

                $slist = $this->song->get_song_list($where,$order,$offset,$page);
                $this->smarty->assign('width',100/count($song_class));
            }
        }

        $this->smarty->assign('slist',$slist);
        $this->smarty->assign('cpage',$offset);
        $this->smarty->assign('cindex',$page);
        $this->smarty->assign('title',$title);
        $this->smarty->assign('class_type',$class_type);
        $this->smarty->assign('id_class',$id_class);
        $this->smarty->view('music_type');
    }

    /**
     * 套餐
     */
    public function setmeal()
    {
        $this->smarty->assign('ajaxurl','http://'.$_SERVER['HTTP_HOST'].'/wapi/'.$this->bid.'/'.$this->sid.'/home/meal_ajax/');
    	$this->smarty->assign('ctype',7);
    	$this->smarty->assign('title','超值套餐');
    	
    	$this->smarty->view('setmeal2');
    }

	public function index(){
//        $this->smarty->assign('openid',$_SESSION['openid']);
        $this->smarty->view('index');
	}


    public function foodcontent($number,$id_commodity,$commodity_type,$type=1){
        if(!$number){
            $number = $this->input->post('num');
        }

        //获取授权用户openid
        $id_open = $this->get_open_ids($number,'foodcontent',$id_commodity,$commodity_type,$type);
    }

    public function foodcontent2($number,$id_commodity,$commodity_type,$type=1){
        //获取授权用户openid
        $id_open = $this->get_open_ids($number,'foodcontent',$id_commodity,$commodity_type,$type);
        if(!$id_open){
            $id_open = 0;
        }
        Header('Location: '.$this->url.'/home/foodcontent1/'.$id_open.'/'.$id_commodity.'/'.$commodity_type.'/'.$type);
    }


    /*
     * 获取物品信息及评论
     * $commodity_type 0：物品  2:美食  3：饮品
     * $type 1，微官网美食饮品  2.物品
     * */
	public function foodcontent1($id_open,$id_commodity,$commodity_type,$type=1){
        $this->load->model('commodity_model','commodity');
        $where = 'c.id_commodity = '.$id_commodity . ' and c.id_shop = '.$this->sid.' and c.id_business = '.$this->bid .' and c.state=1';
        $where_next = 'c.id_commodity > ' . $id_commodity.' and c.id_shop = '.$this->sid.' and c.id_business = '.$this->bid .' and c.state=1';
        $where_last = 'c.id_commodity < ' . $id_commodity.' and c.id_shop = '.$this->sid.' and c.id_business = '.$this->bid .' and c.state=1';
        $where_review = 'r.id_object = '.$id_commodity.' and r.object_type = \'commodity\' and r.id_shop = '.$this->sid .' and r.id_business = '.$this->bid .' and r.state=1';
        $order = 'c.weight';
		$title = '';
        if($commodity_type == 0 && ($type == 2 || $type==3)){
        	if($type == 2){
        		$title = '物品详情';
        	}elseif ($type == 3){
        		$title = '套餐详情';
        	}
            $order = 'c.id_commodity';
            $this->smarty->assign('id_shop',0);
            $this->smarty->assign('type',$type);
        }else{
            $commodity_type = $commodity_type ? $commodity_type : 2;
            if($commodity_type == 3){//饮品推荐
                $where .= ' and c.id_class = 3';
                $where_next .= ' and c.id_class = 3';
                $where_last .= ' and c.id_class = 3';
                $title = '饮品详情';
            }else{
                $order = 'c.id_commodity';
                $where .= ' and c.id_class = 2';
                $where_next .= ' and c.id_class = 2';
                $where_last .= ' and c.id_class = 2';
                $title = '美食详情';
            }
        }

        $this->smarty->assign('title',$title);
        $this->smarty->assign('commodity_type',$commodity_type);
        $num = 0;//不是商品的物品
        if($type == 2 || $type==3){
            $num = 1;//物品，包括商品
        }

        $commodity_info = $this->commodity->get_commodity_introduction($where, 1, 1, $order.' desc',$num);
        $data['commodity_info'] = $commodity_info;

        //获取上一条下一条物品id
        $next = $this->commodity->get_commodity_introduction($where_next, 1, 1,  $order.' asc',$num);
        $last = $this->commodity->get_commodity_introduction($where_last, 1, 1,  $order.' desc',$num);
        $this->smarty->assign('last',$last[0]['id_commodity']);
        $this->smarty->assign('next',$next[0]['id_commodity']);

        $this->load->model('review_model','review');
        $data['review_info'] = $this->review->get_review_info($where_review);

//        $this->load->model('businessconfig_model','businessconfig');
//        $where = 'id_business = ' . $this->bid . ' and id_shop = ' . $this->sid;
//        $configs= $this->businessconfig->get_business_config($where);
//        $config['appid'] = $configs[0]['appid'];
//        $config['url_'] = 'http://'.$_SERVER['HTTP_HOST'].$this->url.'/home/get_open_id/2';
//        $this->smarty->assign($config);
        $this->smarty->assign($data);

        $this->load->model('business_model','business');
        $where_sub = 'id_business = ' . $this->bid . ' and id_shop = ' . $this->sid . ' and id_open = \'' . $id_open.'\'';
        $return = $this->business->get_business_sub($where_sub);
        $cus_data['id_open'] = $id_open;
        $can_review = 0;
        $nick_name = '';
        if($return){
            $can_review = 1;
            $nick_name = $return[0]['nick_name'];
        }
        $cus_data['can_review'] = $can_review;
        $cus_data['nick_name'] = $nick_name;

        $this->smarty->assign($cus_data);
        $this->smarty->assign('id_commodity',$id_commodity);
        $this->smarty->assign('id_business',$this->bid);
        $this->smarty->assign('id_shop',$this->sid);
	    $this->smarty->view('foodcontent');
	}


    //查看当前用户是否可以评论
    public function can_review(){
        $id_open = $this->input->post('id_open');
        $this->load->model('business_model','business');
        $where = 'id_business = ' . $this->bid . ' and id_shop = ' . $this->sid . ' and id_open = \'' . $id_open.'\'';
        $return = $this->business->get_business_sub($where);

        if($return){
            $resp = array(
                'result' => 1,
                'message' => '',
                'nick_name' => $return[0]['nick_name']
            );
            die(json_encode($resp));
        }else{
            $resp = array(
                'result' => 0,
                'message' => '得关注了此商家才能评论',
                'nick_name' => ''
            );
            die(json_encode($resp));
        }
    }


	public function huadong(){
	    $this->smarty->view('huadong');
	}

    /**
     * 评论
     */
    public function review()
    {
        //获取授权用户openid
        $id_open = $this->input->post('id_open');
        $nick_name = $this->input->post('nick_name');
//        $this->load->model('business_model', 'business');
//        $where = 'id_business = ' . $this->bid . ' and id_shop = ' . $this->sid . ' and id_open = \''.$id_open.'\'';
//        $person_info = $this->business->get_business_sub($where);
//        $nick_name = '';
//        if($person_info){
//            $nick_name = $person_info[0]['nick_name'];
//        }
//        $nick_name = urldecode($nick_name);
//        file_put_contents(DOCUMENT_ROOT.'/review.txt',var_export($id_open.'===='.$nick_name,TRUE));
        if($id_open == 0 && $nick_name == ''){
            $nick_name = '匿名';
        }
        $data = array(
            'id_business' => $this->input->post('business'),
            'id_shop' => $this->input->post('shop'),
            'id_object' => $this->input->post('id'),
            'object_type' => $this->input->post('type'),
            'phone_number' => '',
            'id_open' => $id_open,
            'id_customer' => 0,
            'name' => $nick_name,
            'content' => $this->input->post('content'),
            'created' => date('Y-m-d H:i:s', time()),
        );
        $this->load->model('goods_model', 'goods');
        $return = $this->goods->add_review($data);

        //查看是否有评论获得的电子券的信息
        $result = $this->event_chance('review',$id_open,$this->input->post('id'));
        $result_array = json_decode($result,true);
        $count = count($result_array['data']);
        $html = '';
        if($result_array['data']){
//                    $html = $result_array['success_reply'].'  ';
            foreach($result_array['data'] as $kra=>$vra){
                $html .= ' '.$vra['name'] . ' X 1 ';
                if($kra != 0 && ($kra+1) < count($result_array['data'])){
                    $html .= ',';
                }
            }
            $html .= '.共' .$count . '张电子券！';
        }

        if( $return == 1){
            echo json_encode(array('result' => 1,'message'=> '评论成功','message1'=>$result_array['msg'],'message2'=>$html, 'time'=>date('Y-m-d H:i', time()),'nick_name'=>urldecode($nick_name)));exit;
        }else{
            echo json_encode(array('result' => 0,'message'=> '评论失败','message1'=>$result_array['msg'],'message2'=>$html,'nick_name'=>urldecode($nick_name)));exit;
        }
    }
    
    
    public function active_details($id_activity)
    {
        $this->load->model('activity_model', 'activity');

        $id_business = $this->bid;
        $id_shop =  $this->sid;
        $where = 'state = 1 AND id_business = ' . $id_business;
        //if( $this->type == 1){
            $where .= ' AND id_shop = ' . $id_shop;
        //}
        /* if( !$id_activity ){
            $activity_1 = $this->activity->get_activity($where);
            $id_activity = $activity_1[0]['id_activity'] ? $activity_1[0]['id_activity'] : 0 ;
        } */
        $where .= ' AND id_activity = ' . $id_activity;
        $activity = $this->activity->get_activity($where);
        if( !$activity ){
            echo '<script>alert("暂无此活动");javascript:history.go(-1);</script>';
            exit;
        }

        $activity[0]['path'] = get_img_url($activity[0]['image_url'], 'activity');

        $this->smarty->assign('activity',$activity[0]);
    	$this->smarty->view('active_details');
    }
    
    
    public function foods($type){
    	$type = !in_array($type, array(2,3)) ? 2 : $type;
    	$title = '店内美食';
    	if($type == 3){
    		$title = '饮品推荐';
    	}

    	$this->smarty->assign('ajaxurl','http://'.$_SERVER['HTTP_HOST'].'/wapi/'.$this->bid.'/'.$this->sid.'/home/foods_ajax/');
    	$this->smarty->assign('ctype',$type);
    	$this->smarty->assign('title',$title);
    	$this->smarty->view('foods2');
    }


    /**
     * 下载
     */
    public function download($id_song,$song)
    {
        $filename = get_img_url($song, 'song',1);

        if(!file_exists($filename)){
            echo "<script>alert('没有该文件');window.history.go(-1)</script>";
            return ;
        }
//文件的类型
        header('Content-type: audio/mp3');
//下载显示的名字
        $this->load->model('song_model','song');
        $name = $this->song->get_song_introduction('id_song = ' . $id_song,0, 1);
        header('Content-Disposition: attachment; filename="'. $name[0]['name'] . '.mp3"');
        $re = readfile("$filename");
        exit();
    }
    

    /**
     * 我的位置
     */
    public function map()
    {
        $this->load->model('shop_model', 'shop');
        if($this->type){
            $where = 'id_shop = '.$this->sid .' and id_business = '.$this->bid;
        }else{
            $where = 'id_business = '.$this->bid;
        }
        $return = $this->shop->get_shop_introduction($where);
        $aa = json_encode($return);
        $this->smarty->assign('shop_info',$aa);
        $this->smarty->assign('s_count',count($return));
        $this->smarty->assign('type',$this->type);//是否是独占。。1：独占 0：共享
        //取得地图的title
        $title = $this->shop->get_map_title($this->bid);
        if($title[0]){
        	$title = $title[0];
        	$this->smarty->assign('title',$title['name']);
        }

        $this->smarty->view('map2');
    }
    
    
    /**
     * 我的位置（false）
     */
    public function map3()
    {
    	$this->smarty->view('map3');
    }


    public function create_menu(){
        //获取缓存数据
        $cache_data = $this->get_cache_data(md5('business'.$this->bid.$this->sid));
        if(!empty($cache_data)){
            $config['appid'] = $cache_data['appid'];
            $config['appsecret'] = $cache_data['appsecret'];

            $this->load->library('wx_menu',$config);
            //获取商家信息
            $where = '';
            if($this->type){
                $where = 'id_shop = '.$this->sid .' and id_business = '.$this->bid;
            }else{
                $where = 'id_business = '.$this->bid;
            }
            $this->load->model('shop_model','shop');
            $shop_number = $this->shop->get_shop_introduction($where);
            if(!empty($shop_number[0])){
                $return = $this->wx_menu->create_menu($shop_number[0],$this->bid,$this->sid);
                debug($return);
            }else{
                echo '<script>alert("没有门店数据");</script>';
                exit;
            }
        }else{
            echo '<script>alert("没有商家数据");</script>';
            exit;
        }
    }


    //废弃
    public function member($id){
    	//判断是否已经领取
    	$where = $card = array();
    	$this->load->model('customer_model','customer');
    	if(!empty($_GET['openid'])){
    		//检测该openid领取没有
    		$where = array('id_open'=>$_GET['openid']);
    		if(!empty($where)){
    			$card = $this->customer->get_member_card_by_openid($where);
    		}
    		
    		//判断用户信息是否存在，并获取
    		$wx_conf = $this->customer->get_wx_info_by_openid(array('id_business'=>$this->bid,'id_shop'=>$this->sid,'id_open'=>$_GET['openid']));
    		if(!$wx_conf['id_bn_sub']){
    			//获取信息
    			$this->get_weixin_info($_GET['openid']);
    		}
    		
    		if($card[0]){
    			header('location:http://'.$_SERVER['HTTP_HOST'].'/wapi/'.$this->bid.'/'.$this->sid.'/home/member_show/'.$card[0]['id_customer']);
    		}else{
                $img_url = 'b_1000.png';
                $img_url = 'b_'.$this->bid.'.png';

                $this->smarty->assign ( 'img_url',$img_url);

                //没有领取
    			$this->smarty->assign ( 'openid',$_GET['openid']);
    			$this->smarty->view ( 'member_input' );
    		}
    	}
    }

    //领取会员卡处理
    public function member_card(){
    	$this->load->library('form_validation');
        $id_open = $this->input->post('openid');
        $receive_mode = $this->input->post('receive_mode');
        $tell = $this->input->post('tell');
        if($receive_mode == 1){//注册领取会员卡
            if($id_open){
                //检查是否唯一
                $this->load->model('customer_model','customer');
                $memcard = $this->customer->get_member_card_by_openid(array('id_open'=>$id_open));
                if(!empty($memcard)){
                    echo json_encode(array('status'=>0,'msg'=>'您已领取过会员卡了','data'=>$memcard[0]['id_customer']));
                    exit;
                }else {
                    //检查手机号码是否重复
                    $memcard = $this->customer->get_member_card_by_openid(array('id_business'=>$this->bid,'id_shop'=>$this->sid,'cell_phone'=>$tell));
                    if(!empty($memcard)){
                        echo json_encode(array('status'=>0,'msg'=>'您的手机号码已存在，不能重复领取'));
                        exit;
                    }

                    //获取会员卡号
                    $carid = '';
                    //查询卡号是否已绑定，重试3次都失败，提示用户重新领取
                    $this->load->model('customer_model','customer');
                    $k = 0;
                    for ($i=0;$i<3;$i++){
                        $carid = get_member_card();
                        $isable = $this->customer->member_card_able($carid);
                        if(empty($isable)){
                            break;
                        }else{
                            $k++;
                        }
                    }
                    if($k == 3){
                        echo json_encode(array('status'=>0,'msg'=>'领取失败，是重新尝试'));
                        exit;
                    }
                    $data['id_business'] = $this->bid;
                    $data['id_shop'] = $this->sid;
                    $data['id_open'] = $id_open;
                    $data['number'] = $carid;
                    //$data['nick_name'] = strip_tags($this->input->post('nickname'));
                    //$data['city'] = strip_tags($this->input->post('city'));
                    $data['sex'] = $this->input->post('gender') == 2 ? 2 : 1;
                    $data['real_name'] = strip_tags($this->input->post('username'));
                    $data['cell_phone'] = $tell;
                    $data['birthday_month'] = $this->input->post('month');
                    $data['birthday_day'] = $this->input->post('days');

                    $insert_id = $this->customer->add_member_card($data);

                    //查看是否有注册获得的电子券的信息
                    $result = $this->event_chance('register',$id_open);
                    $result_array = json_decode($result,true);
                    $count = count($result_array['data']);
                    $html = '';
                    if($result_array['data']){
                    //    $html = $result_array['success_reply'].'  ';
                        foreach($result_array['data'] as $kra=>$vra){
                            $html .= ' '.$vra['name'] . ' X 1 ';
                            if(($kra+1) <= count($result_array['data'])){
                                $html .= ',';
                            }
                        }
                        $html .= '.共' .$count . '张电子券！';
                    }
                    //if($k == 3){//会员卡领取成功.\n
                    echo json_encode(array('status'=>1,'msg'=>$html,'msg1'=>$result_array['msg']?$result_array['msg']:"",'data'=>$insert_id));
                    exit;
                    //}
                }
            }
        }else{//绑定领取会员卡
            if($tell){
                $this->load->model('customer_model','customer');
                //检查是否唯一
                $memcard = $this->customer->get_member_card_by_openid(array('id_business'=>$this->bid,'id_shop'=>$this->sid,'cell_phone'=>$tell));
                if(empty($memcard)){
                    echo json_encode(array('status'=>0,'msg'=>'领取失败，手机号码还不是会员。'));
                    exit;
                }else{
                    $data['id_open'] = $id_open;
                    $where = 'id_customer = ' . $memcard[0]['id_customer'];
                    $this->customer->update_customer($data,$where);

                    //查看是否有注册获得的电子券的信息
                    $result = $this->event_chance('register',$id_open);
                    $result_array = json_decode($result,true);
                    $count = count($result_array['data']);
                    $html = '';
                    if($result_array['data']){
                        foreach($result_array['data'] as $kra=>$vra){
                            $html .= ' '.$vra['name'] . ' X 1 ';
                            if(($kra+1) <= count($result_array['data'])){
                                $html .= ',';
                            }
                        }
                        $html .= '.共' .$count . '张电子券！';
                    }
                    echo json_encode(array('status'=>1,'msg'=>$html,'msg1'=>$result_array['msg']?$result_array['msg']:"",'data'=>$memcard[0]['id_customer']));
                    exit;
                }
            }
        }
    }
    
    
    protected function get_curl($url){
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL,$url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//不输出内容
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    	/*if($jsonData != ''){
    		curl_setopt($ch, CURLOPT_POST, 1);
    		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    		curl_setopt($ch, CURLOPT_POSTFIELDS,$jsonData);
    		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    	}*/
    	
    	$result = curl_exec($ch);
    	curl_close($ch);
    	return $result;
    }

    /*
     * 查询领取会员卡用户的信息
     */
    public function member_show($id){
        $bid = $this->bid;
        $sid = $this->sid;
    	if(empty($_GET['openid']) && $id){
    		$id = intval($id);
    		$this->load->model('customer_model','customer');
    		$memcard = $this->customer->get_member_card_by_openid(array('id_customer'=>$id));

    		if($memcard[0]){
    			$this->smarty->assign('card',$memcard[0]);
    		}else{
    			echo '<script type="text/javascript">alert("找不到对应的会员卡");</script>';
    			exit;
    		}

             $this->load->model('shop_model','shop');
             if($this->type){//独占1
                 $where = array('id_business'=>$bid,'id_shop'=>$sid);
             }else{//共享0
                 $where = 'id_business = '.$bid;
             }
             $shop = $this->shop->get_shop_introduction($where);
             if($shop[0]){
                 $contact = json_decode($shop[0]['contact']);
                 $shop[0]['contact'] = $contact[0];
             }
             $this->smarty->assign('shop',$shop[0]);

    		$map = 'http://'.$_SERVER['HTTP_HOST'].'/wapi/'.$this->bid.'/'.$this->sid.'/home/map';
    		$this->smarty->assign('map',$map);
             $img_url = 'c_1000.png';
             $img_url = 'c_'.$this->bid.'.png';

             $this->smarty->assign('img_url',$img_url);
             $this->smarty->assign('bid',$this->bid);
    		$this->smarty->view('member-show');
    	}else{
             //判断是否已经领取
             $where = $card = array();
             $this->load->model('customer_model','customer');
             if(!empty($_GET['openid'])){
                 //检测该openid领取没有
                 $where = array('id_open'=>$_GET['openid']);
                 if(!empty($where)){
                     $card = $this->customer->get_member_card_by_openid($where);
                 }

                 //判断用户信息是否存在，并获取
                 $wx_conf = $this->customer->get_wx_info_by_openid(array('id_business'=>$this->bid,'id_shop'=>$this->sid,'id_open'=>$_GET['openid']));
                 if(!$wx_conf['id_bn_sub']){
                     //获取信息
                     $this->get_weixin_info($_GET['openid']);
                 }

                 if($card[0]){
                     header('location:http://'.$_SERVER['HTTP_HOST'].'/wapi/'.$this->bid.'/'.$this->sid.'/home/member_show/'.$card[0]['id_customer']);
                 }else{
                     $this->load->model('shop_model','shop');
                     if($this->type){//独占1
                         $where = array('id_business'=>$bid,'id_shop'=>$sid);
                     }else{//共享0
                         $where = 'id_business = '.$bid;
                     }
                     $shop = $this->shop->get_shop_introduction($where);
                     if($shop[0]){
                         $contact = json_decode($shop[0]['contact']);
                         $shop[0]['contact'] = $contact[0];
                     }
                     $this->smarty->assign('shop',$shop[0]);

                     $this->load->model('business_model','business');
                     $where_b = 'id_business = ' . $bid;
                     $business_info = $this->business->get_business_phone($where_b);
                     $receive_mode = 1;
                     if($business_info){
                         $receive_mode = $business_info[0]['receive_mode'];
                     }
                     $this->smarty->assign('receive_mode',$receive_mode);

                     $img_url = 'b_1000.png';
                     $img_url = 'b_'.$this->bid.'.png';
                     $this->smarty->assign ( 'img_url',$img_url);

                     //没有领取
                     $this->smarty->assign ( 'openid',$_GET['openid']);
                     $this->smarty->assign('bid',$this->bid);
                     $this->smarty->view ( 'member-show' );
                 }
             }else{
                 $this->load->model('shop_model','shop');
                 if($this->type){//独占1
                     $where = array('id_business'=>$bid,'id_shop'=>$sid);
                 }else{//共享0
                     $where = 'id_business = '.$bid;
                 }
                 $shop = $this->shop->get_shop_introduction($where);
                 if($shop[0]){
                     $contact = json_decode($shop[0]['contact']);
                     $shop[0]['contact'] = $contact[0];
                 }
                 $this->smarty->assign('shop',$shop[0]);
             }

        }
    }

    public function menu(){
        $where1 = 'parent_id = 0';
        $this->load->model('menu_model','menu');
        $menu_obj = $this->menu->get_menu($where1,3);
        $menus = $menu_obj;
        if($menu_obj){
            foreach($menu_obj as $key=>$mo){
                $where2 = 'parent_id = '.$mo['id_shop_menu'];
                $menus[$key]['menu_two'] = $this->menu->get_menu($where2,5);
            }
        }
//        print_r($menus);

         $data = '{"button":[';
         foreach($menus as $key1=>$menu1){
             if($menu1['object_type'] == 'click'){
                 if($key1 != 0){
                    $data .= ',';
                 }
                 $data .= '{"type":"click","name":"'.$menu1['name'].'"';
                 $menu_two = $menu1['menu_two'];
                 if(count($menu_two) > 0){
                     $data .= ',"sub_button":[';
                     foreach($menu_two as $key2=>$menu2){
                         if($key2 != 0){
                            $data .= ',';
                         }
                         if($menu2['object_type'] == 'click'){
                            $data .= '{"type":"click","name":"'.$menu2['name'].'","key":"'.$menu2['object_value'].'"}';//.'-'.$bclicktype[$keyBT2]
                         }elseif($menu2['object_type'] == 'view'){
                            $data .= '{"type":"view","name":"'.$menu2['name'].'","url":"'.$menu2['object_value'].'"}';
                         }
                     }
                     $data .= ']';
                 }else{
                    $data .= ',"key":"'.$menu1['object_value'].'"';
                 }
                 $data .= '}';
             }elseif($menu1['object_type'] == 'view'){
                 if($key1 != 0){
                    $data .= ',';
                 }
                 $data .= '{"type":"view","name":"'.$menu1['name'].'","url":"'.$menu1['object_value'].'"}';
             }
         }
         $data .= ']}';
        print_r($data);

    }

    /*
     * 文章详情页   wapi/1/1/home/content/33
     * **/
    public function content($id_info){
        $this->load->model('info_model','info');

        //zxx start   查看文章是否被活动绑定。绑定了则需要参加活动即可查看，即往下
        if( ! $this->userid){ //用户未登录，跳转到登录页面后，返回到当前页面
            $this->redirect('/user/login');
            die;
        }
        $this->load->model('community_model','community');
        $where_c = 'id_content = ' . $id_info . ' and id_business = ' . $this->bid;
        $comm_info = $this->community->get_community_info($where_c);//查看该文章是否被绑定
        if($comm_info){
            $id_community = $comm_info[0]['id_activity'];

            $this->load->model('communityjoin_model','communityjoin');
            $where_join = 'id_activity = ' .$id_community . ' and id_user = ' . $this->userid;
            $comm_join = $this->communityjoin->byFieldSelect('*', $where_join);//查看该登录用户是否已参加此文章绑定的活动
            if(!$comm_join){
                echo '<script>alert("要参加了活动“'.truncate_utf8($comm_info[0]['name'],20).'”,才能查看此文章哦!");javascript:history.go(-1);</script>';
                exit;
            }
        }
        //zxx end
        $where = 'id_info = '.$id_info;
        $content_info = $this->info->get_info_introduction($where,0,0,'i.weight desc');//获取文章详细

//        $where .= ' and type = \'image\'';
//        $content_info_attachment = $this->info->get_info_attachment($where);//获取文章附件信息
        if( !$content_info ){
            echo '<script>alert("暂无此文章");javascript:history.go(-1);</script>';
            exit;
        }

        $data['content_info'] = $content_info[0];
//        $data['content_info_attachment'] = $content_info_attachment;
        $this->smarty->assign($data);
        $this->save_analisys('info', intval($id_info), 'view');
        $this->smarty->view('info_details');
    }



    /*
     * zxx
     * 电子券详情页
     * **/
    public function ticket_content($id_ticket){
        $this->load->model('ticket_model','ticket');

        $where = 'id_eticket = '.$id_ticket;
        $ticket_info = $this->ticket->get_ticket_introduction($where);//获取电子券详细

        $data['ticket_info'] = $ticket_info[0];
//        $data['content_info_attachment'] = $content_info_attachment;
//debug($data);
        $this->smarty->assign($data);

        $this->save_analisys('info', intval($id_ticket), 'view');

        $this->smarty->view('ticket_details');
    }



    public function foods_ajax($offset,$type){
    	$offset = $offset ? $offset : 1;
    	$page = 8;
    	$type = ($type>1) ? $type : 2;

    	$this->load->model('commodity_model','commodity');
    	//都带店id
    	$where = 'c.id_shop = '.$this->sid.' and c.id_business = '.$this->bid.' and c.state=1 and c.id_class = '.$type;
    	$order = 'c.id_commodity desc';
    	if($type == 3){
    		$order = 'c.weight desc';
    	}
    	 
    	$commodity_info = $this->commodity->get_commodity_introduction($where, $offset, $page,$order,0);
    	 
    	$data2 = array();
    	if($commodity_info){
    		foreach ($commodity_info as $k => $v){
    			$data2[$k]['tid'] = $v['id_commodity'];
    			$data2[$k]['subject'] = base64_encode($v['name']);
    			$data2[$k]['author'] = '';
    			$data2[$k]['views'] = '';
    			$data2[$k]['thumb'] = get_img_url($v['image_url'], 'commodity');
    			$path = get_img_url($v['image_url'], 'commodity',1);
    			if(file_exists($path)){
    				$img_tmp = getimagesize($path);
    				if($img_tmp[1] && $img_tmp[0]){
    					$data2[$k]['picWidth'] = $img_tmp[0];
    					$data2[$k]['picHeight'] = $img_tmp[1];
    				}
    			}
    		}
    	}
    	echo $_GET['jsoncallback'].'('.json_encode($data2).')';
    }
    

    public function test($page,$new){
    	$this->load->model('shop_model', 'shop');
    	if($this->type){
    		$where = 'id_shop = '.$this->sid .' and id_business = '.$this->bid;
    	}else{
    		$where = 'id_business = '.$this->bid;
    	}
    	$return = $this->shop->get_shop_introduction($where);
    	$return = $return[0];
    	//$url2 = 'http://api.map.baidu.com/marker?location='.$return['latitude'].','.$return['longitude'].'&title=我的位置&content='.$return['name'].'&output=html&src=yourComponyName|yourAppName';
    	//$url2 = 'http://map.baidu.com/mobile/webapp/place/detail/qt=inf&uid=c14fc238f7fadd4ea5da390f/shareurl=1&t=1393290254#place/detail/foo=bar&qt=inf&uid=c14fc238f7fadd4ea5da390f/shareurl=1&t=1393290254&vt=map';
    	//debug($url2);
    	$url2 = 'http://j.map.baidu.com/zLrPp';

    	header('location:'.$url2);
    	
    }
    
    
    public function meal_ajax($page){
    	
    	$business_id = $this->bid;
    	//获取套餐列表
    	$this->load->model('commodity_model', 'commodity');
    	$where = 'c.id_class = 1 AND c.id_business = '.$business_id . ' and c.state = 1';
    	if( $this->type == 1){
    		$where .= ' AND c.id_shop = ' . $this->sid;
    	}
    	$commodity = $this->commodity->get_commodity_list($where, intval($page), 8);
    	$i = 0;
    	$data2 = array();
    	foreach($commodity as $k=>$v){
    		$data2[$k]['tid'] = $v['id_commodity'];
    		$data2[$k]['subject'] = base64_encode($v['name']);
    		$data2[$k]['author'] = '';
    		$data2[$k]['views'] = '';
    		$data2[$k]['thumb'] = get_img_url($v['image_url'], 'commodity');
    		$path = get_img_url($v['image_url'], 'commodity',1);
    		if(file_exists($path)){
    			$img_tmp = getimagesize($path);
    			if($img_tmp[1] && $img_tmp[0]){
    				$data2[$k]['picWidth'] = $img_tmp[0];
    				$data2[$k]['picHeight'] = $img_tmp[1];
    			}
    		}
    	}
    	
    	echo $_GET['jsoncallback'].'('.json_encode($data2).')';
    }
    
    
    public function index1(){
    	$business_id = $this->bid;
        //获取商家信息
        $this->load->model('business_model','business');
        $where = 'ba.object_type = \'bn\' and ba.id_object = 0';
        $merchant = $this->business->get_business_info($business_id,$where);
        $images = explode(',', $merchant[0]['image_url']);
        foreach($images as $value){
            $path[] = get_img_url($value,'attachment',0,'bg_mr.png');
        }

        $this->load->model('shop_model','shop');
        //取得企业简介的的title
        $title = $this->shop->get_map_title($business_id,'about');
        if($title[0]){
            $title = $title[0];
            $this->smarty->assign('title',$title['name']);
        }

        $this->smarty->assign('images',$path);
        $this->smarty->assign('introduction',html_entity_decode($merchant[0]['introduction'],ENT_COMPAT,'utf-8'));
        $this->smarty->view('index1');
    }
	
    /**
     * @商家信息分类列表
     */
    public function class_list(){
        $limit = 12;
        $page = !empty($_GET['p'])  ? intval ( $_GET['p'] ) : 1;
        $business_id = $this->bid; //商家ID
        $shop_id = $this->sid;//门店ID
        $id_class = intval($_GET['id']);//分类ID；
        $template = $_GET['template'];//模板ID； 1：默认模板  2：模板2

        $this->load->model('info_model','info');
        //' and ia.type = "image" '.
        $where = 'i.id_business = '.$business_id.' and i.id_shop = '.$shop_id.' and state = 1 and i.id_class = '.$id_class;
        $content_info = $this->info->get_info_list($where,$page,$limit,'i.weight desc ,`i`.`id_info` desc ');//获取文章详细

        foreach($content_info as $k=>$v){
            $url = $this->get_link_url('eitity','',$v['id_info'],$v['url'],'',$v['id_business'],$v['id_shop']);
            $content_info[$k]['url'] = $url;
        }
        $domain = trim(strstr($_SERVER["REQUEST_URI"], 'home'));
        $where = "id_business = {$business_id} and object_type='view' and object_value='{$domain}'";
        $title = $this->info->get_list_title($where);
        $this->smarty->assign('title',$title?$title:"商家信息分类列表");

        $this->smarty->assign('list',$content_info);
        $this->smarty->assign('cpage',$page);
        $this->smarty->assign('type',$_GET['t']);
        $this->smarty->assign('id',$id_class);
        $this->smarty->assign('template',$template);
//         $this->smarty->assign('introduction',html_entity_decode($content_info[0]['introduction'],ENT_COMPAT,'utf-8'));
        $this->smarty->view('index2');
    }

    /**
     * zxx
     * 刷新token （废弃）
     */
    function refresh_token($refresh_tokens){
        $this->load->model('businessconfig_model','businessconfig');
        $where = 'id_business = ' . $this->bid . ' and id_shop = ' . $this->sid;
        $config= $this->businessconfig->get_business_config($where);

        if($config){
            $url = 'https://api.weixin.qq.com/sns/oauth2/refresh_token?appid='.$config[0]['appid'].'&grant_type=refresh_token&refresh_token=' . $refresh_tokens;
            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_HEADER,0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            $res = curl_exec($ch);
            curl_close($ch);
            $json_obj = json_decode($res,true);
//            $openid = $json_obj['openid'];
            echo $json_obj;
            return $json_obj;
        }
        return '';
    }


    /*
     * zxx
     * 更新编码评论表用户名字
     */
    function update_name(){
        $this->load->model('review_model','review');
        $config= $this->review->get_review_info('');
        $update_return = array();
        foreach($config as $k=>$ur){
            $update_return['name'] = urlencode($ur['name']);
            $where_u = 'id_review = ' . $ur['id_review'];
            $this->review->update_reply($update_return,$where_u);
        }
        die('ok');
    }
    /**
     * 错误提示页面
     */
     function error(){
        $this->smarty->assign('url',$_GET['url']);
        $this->smarty->assign("tip",$_GET['tip']);
        $this->smarty->view('error_tip');
     }
	 
    /**
     *
     */
     function test_select(){
        $this->smarty->view('test_select');
     }
	     /**
     *
     */
     function member_show2(){
        $this->smarty->view('member-show2');
     }
	 
	 function member11(){
        $this->smarty->view('member11');
     }


    function test_oid(){
        echo 'oid:--';
        echo $_GET['code'];
        die;
    }



    public function wgw_index1(){
        $b = $this->input->get('b');//商家id
        $this->get_open_ids(1,'wgw_index',$b);
//        $this->smarty->view('hipigo_community');
    }

    public function wgw_index2($number){
        $b = $this->input->get('b');//商家id
        //获取授权用户openid
        $id_open = $this->get_open_ids($number);
        if(!$id_open){
            $id_open = 0;
        }

        $this->load->model('business_model','business');
        $where = 'id_business = '.$b;
        $merchant = $this->business->get_business_phone($where);

//        Header('Location:'.$this->url.'/community/home?oid='.$id_open);
        Header('Location:http://'.$merchant[0]['sld'].'/wapi/'.$b.'/0/community/home?oid='.$id_open);
    }

    public function set_cook(){
        $content = $this->input->post('content');
        if( !isset($_COOKIE["admin"]) ){
            setcookie('admin', $content, time() + (60 * 60 * 24 * 14), '/', '');
            echo $content;
            die;
        }
        echo '';
    }



    /**
     * zxx
     * 微官网页面分类的其他页面
     */
    public function column($num)
    {
        $this->load->model('business_model', 'business');
        $template = 1;
        $business_id = $this->bid;
        //获取商家信息
        $where = 'ba.object_type = \'site\' and ba.id_object = ' . $num;
        $file_path = 'attachment';
        if($num){
            $file_path = 'site';
        }
        $merchant = $this->business->get_business_info($business_id, $where);
        $images = explode(',', $merchant[0]['image_url']);
        foreach ($images as $value) {
            $path[] = get_img_url($value,$file_path, 0, 'bg_mr.png');
        }

        $url = $_SERVER ['HTTP_HOST'];
        $url = mysql_escape_string($url);
        $where = 'b.sld = \''.$url . '\' and c.id_column = '.$num;
        $return = $this->business->get_bs_home_inf($where);

        for($i=0;$i<count($return);$i++){
            $template = $return[$i]['template'];
            $return[$i]['image'] = get_img_url($return[$i]['image'], 'home', 0, 'bg_mr.png');
            $this->get_link_url($return[$i]['type'],$return[$i]['object_type'],$return[$i]['id_object'],$return[$i]['url'],$return[$i]['visit_mode'],$return[$i]['id_business'],$this->sid,$template);
        }

        $this->smarty->assign("information", $return);
        $this->smarty->assign('images', $path);
        $this->smarty->assign('introduction', html_entity_decode($merchant[0]['introduction'],ENT_COMPAT, 'utf-8'));
        $this->smarty->view('page_column');
    }


    /**
     * 拼装商家微官网首页。连接页面地址
     * @param string $type list(列表页)/eitity(文章详情)/link(外部连接。直接使用)
     * @param string $object_type	info/activity/commodity		功能模块
     * @param int    $id_object	功能对应的分类编号或者实体编号
     * @param string $url	链接地址
     * @param int $id_business 商家ID
     * @param int $id_shop 门店ID
     * http://www.hipigo.com/wapi/1/0/home/class_list?r=info&c=7
     */
    function get_link_url($type,$object_type,$id_object,&$url,$model,$id_business='',$id_shop='',$template=1){
        if($model=='share'){
            $id_shop = 0;
        }else{
            if($id_shop == ''){
                $this->load->model('shop_model','shop');
                $where = 'id_business = ' . $id_business;
                $shopInfo = $this->shop->get_shop_introduction($where);
                $id_shop = $shopInfo[0]['id_shop'];

            }
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


    /**
     * zxx
     * 推荐商户
     */
    function hotbusiness(){
        $this->media('layout_section.css','css');
        $this->smarty->view('hot_business');
    }

    /**
     * zxx
     * 推荐商户列表
     */
    function business_lists(){
        $offset = $this->input->post('offset');
        $offset = empty($offset) ? 1 : $offset;
        $pagesize = 10;
        $kw = $this->input->post('kw') ? $this->input->post('kw') : '';//筛选

        $this->load->model('shop_model','shop');
        $where = 'is_hot = 1';
        if($kw){
            $where .= ' and name like "%'.$kw.'%"';
        }
        $shopInfo = $this->shop->get_shop_introduction($where,$offset,$pagesize);
        if($shopInfo){
            $this->load->model('community_model','community');
            foreach($shopInfo as $ks=>$vs){
                $where_c = 'id_business = ' . $vs['id_business'];
                $activity_count = $this->community->get_community_count($where_c);
                $shopInfo[$ks]['activity_count'] = $activity_count;
            }
        }
        $page_types = 'hotbusiness';
        $this->smarty->assign('page_type', $page_types);
        $this->smarty->assign('shopInfo', $shopInfo);
        echo $this->smarty->display('lists.html');
        exit;
    }

    public function test3(){
        $this->smarty->view('index4');
    }


}


/* End of file index.php */