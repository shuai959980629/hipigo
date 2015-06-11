<?php
/**
 * @活动列表
 * @copyright(c) 2013-11-20
 * @author zhoushuai
 * @version Id:list_Model.php
 */

class List_Model extends CI_Model
{

    protected $table = 'bn_order';
    protected $table_ticket = 'bn_eticket_item';
    protected $table_income = 'bn_income_item';
    protected $table_business = 'bn_business';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * 获取活动信息
     */
    public function get_activity($where)
    {
        $this->db->select('*')->from('bn_community_activity as a')->where($where);
        $result = $this->db->get()->result_array();
        return $result[0];
        //return $this->db->last_query();
    }
    /**
     * 获取推广活动的信息
     */
    public function get_community_spread($where){
        $this->db->select('*')->from('bn_community_spread as s')->where($where)->limit(1);
        $result = $this->db->get()->result_array();
        //return $this->db->last_query();
        return $result;
    }

    /**
     * 活动成员列表，提供ajax接口，当前用户排在第一位，当传入page页数后返回对应页的数据，每页25条数据
     */
    public function get_act_list($where, $offset = 0, $page = 3, $order =
        "created desc")
    {
        $this->db->select('a.id_join,a.id_activity,a.role,a.created,a.update_time,e.code,a.id_open,b.nick_name,b.head_image_url as photo')->
            from(' bn_community_activity_join as a ')->join('bn_eticket_item  as  e ',
            'a.id_activity = e.id_object and  a.id_open = e.id_open ', 'left')->join('bn_business_subscribe as b ',
            ' a.id_open = b.id_open', 'left')->where($where);
        if ($offset) {
            $this->db->limit($page, ($offset - 1) * $page);
        }
        if ($order) {
            $this->db->order_by($order);
        }
        return $result = $this->db->get()->result_array();
        // return $this->db->last_query();
    }


    /**
     * 2,我的订单列表，提供ajax接口，未支付排在前面，历史订单（已支付）排在后面，
     * 当传入page页数后返回对应页的数据，每页25条数据，
     */
    public function get_order_list($where, $offset = 0, $page = 25, $order = "state ASC")
    {

        $this->db->select('a.id_activity,o.id_order,o.id_business,o.id_shop,o.state,o.pay_amount,o.total_amount,o.total_quantity,o.id_object,a.name,
        a.posters_url as img,a.state as isvalid')->
            from(' bn_order as o ')->join('bn_community_activity as a ',
            'a.id_activity = o.id_object', 'left')->where($where);
        if ($offset) {
            $this->db->limit($page, ($offset - 1) * $page);
        }
        if ($order) {
            $this->db->order_by($order);
        }
        $data = $this->db->get()->result_array();
		$d_img = 'activity.jpg'; //活动默认图片
		foreach ($data as $key => $value) {
		   foreach ($value as $k => $v) {
			  if($k=='img')
			  $data[$key][$k]=$this->parse_img_url('community',$v,$d_img);	//上传图片
		 }
	   }
    return $data;
       
    }

    /**
     * 获取支付或未支付的总数
     */
    public function get_pay_count($where, $state)
    {
        $this->db->select('count(*) as num ')->from(' bn_order as o ')->where($where)->
            where('state', $state);
        $result = $this->db->get()->result_array();
        $data = !empty($result) ? $result[0]['num'] : false;
        return $data;
    }

    /**
     * 获取电子券验证码
     * @aid 活动id
     * @oid 微信id
     */
    public function get_ecode($aid, $oid)
    {
        $this->db->select('code')->from('bn_eticket_item')->where(array(
            'object_type' => 'community',
            'id_object' => $aid,
            'id_open' => $oid));
        $result = $this->db->get()->result_array();
        $data = !empty($result) ? $result[0]['code'] : false;
        return $data;
    }

    /**
     * 生成订单
     */
    public function add($data, $userid)
    {
        //生成订单
        $this->db->trans_begin();
        $order_data = array(
            'id_order'=>$data['id_order'],
            'id_business' => $data['id_business'],
            'id_shop' => $data['id_shop'],
            'object_type' => $data['object_type'], //对象类型
            'id_object' => $data['aid'], //活动id
            'total'=>$data['total'],//活动购买数量
            'total_quantity' => $data['quantity'], //订单总数量
            'buyer' => $userid, 
            'identity'=>$data['identity'],//用户身份
            'pay_mode' => $data['payMode'], //支付方式
            'total_amount' => $data['price'], //订单总金额
            'pay_amount' => $data['payment'], //支付金额
            'created' => date("Y-m-d H:i:s", time()),
            'state' => 0, //0:未支付
            'phone' => $data['phone'],
            );
        $oid = $this->add_order($order_data);
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        } else {
            $this->db->trans_commit();
            return $oid;
        }
    }

    /*
     * zxx
     * 获取订单信息
     */
    public function get_order_info($where){
        $this->db->select("*")->from($this->table)->where($where);
        $this->db->group_by('buyer');
        $result = $this->db->get()->result_array();
        return $result;
    }

 
    /**
     * 添加订单
     */
    public function add_order($data)
    {
        return $this->db->insert($this->table, $data);
        /*
        $oid = $this->db->insert_id();
        return $oid;
        */
    }

    /**
     * 修改订单
     */
    public function modify_order($data, $orderID)
    {
        return $this->db->update($this->table, $data, array('id_order' => $orderID));
    }

    /**
     * 获取订单状态
     */
    public function get_order_status($orderID)
    {
        $this->db->select("id_order as oid,state")->from($this->table)->where('id_order',$orderID);
        $result = $this->db->get()->result_array();
        $return = !empty($result) ? $result[0]['state'] : false;
        return $return;
    }
    /**
     * 查询订单
     */
     public function get_order($orderID){
        $this->db->select("*")->from($this->table)->where('id_order',$orderID);
        $result = $this->db->get()->result_array();
        $return = !empty($result) ? $result[0] : false;
        return $return;
     }
     
     public function get_order_id($where){
        $this->db->select("id_order")->from($this->table)->where($where);
        $result = $this->db->get()->result_array();
        $return = !empty($result) ? $result[0]['id_order'] : false;
        return $return;
        
     }

     
    /**
     *  社区活动参与表   
     */
    
    public function insert_member($aid,$user,$identity,$phones){  
        $userid = 0;
        $phone = $phones;
        if($identity=='visitor'){
            $phone = $user;
            $where = array('cellphone'=>$phone,'id_activity'=>$aid,'identity' =>$identity);
            $res = $this->query_member($where);
            if($res){
                $data = array('update_time'=>date('Y-m-d H:i:s',time()));
                $this->modify_member($data,$where);
                return true;
            }
        }else{
            $userid = $user;
            $where = array('id_user'=>$userid,'id_activity'=>$aid,'identity' =>$identity);
            $res = $this->query_member($where);
            if($res){
                $data = array('update_time'=>date('Y-m-d H:i:s',time()));
                $this->modify_member($data,$where);
                return true;
            }
        }
		$data = array(
    		'id_user'=>$userid,
    		'id_activity'=>$aid,
    		'created'=>date('Y-m-d H:i:s',time()),
    		'update_time'=>date('Y-m-d H:i:s',time()),
    		'cellphone'=>$phone,
    		'identity' =>$identity,
    		'role'=>1
		);
        return $this->db->insert("bn_community_activity_join", $data);
    }
    
    /**
     * 查询活动参与表
     */
    public function query_member($where){
        $this->db->select('*')->from('bn_community_activity_join')->where($where);
        $result = $this->db->get()->result_array();
        $return = !empty($result) ? $result : false;
        return $return;
    }
    /**
     * @修改活动参与表
     */
    public function modify_member($data,$where){
        return $this->db->update('bn_community_activity_join', $data, $where);
    }
    
    
    /**
     * @zhoushuai
     * @v-社区活动(bn_community_activity)。购买数量增加
     */
     
     public function add_join_count($aid,$count){
        //By Jamai , total = total-'.$count.' 增加数量的同时 减去总数量
        $mysql = 'update bn_community_activity set join_count=join_count+'.$count.', total = total-'.$count.' where id_activity = '.$aid;
        $this->db->query($mysql);
     }
     
     /**
     * 参加社区活动，产生一张电子验证码
     *
     * @param source 来源  默认为community  
     * @param array $data
     * @return boolean
     */
    public function create_eticket($aid,$user,$identity,$bid,$sid, $source = 'community') {
	    $userid = 0;
        $phone = 0;
        if($identity=='visitor'){
            $phone = $user;
        }else{
            $userid = $user;
        }
	    $code = rand(10000000,99999999);
		$eticket_info = array(
		  'id_business' => $bid,
		  'id_shop'     => $sid,
		  'object_type' => $source,
		  'id_object'   => $aid,
		  'code'        => $code, 
		  'id_open'     => $phone, 
		  'id_customer' => $userid,
		  'get_time'    => date('Y-m-d H:i:s',time()),
		  );
      
      //if($source == 'resource') {//取消资源默认
        //$eticket_info['state'] = 2; //资源默认 已使用
        //$eticket_info['id_verify_business'] = 90;
        //$eticket_info['id_verify_shop'] = 0;
        //$eticket_info['use_time'] = date('Y-m-d H:i:s', time());
      //}
      //else 
        $eticket_info['state'] = 1;
        
		$this -> db -> insert('bn_eticket_item', $eticket_info);
        $id_item = $this->db->insert_id();
//        return $id_item;
        $data_info['code'] = $code;
        $data_info['id_item'] = $id_item;
        return $data_info;
    }
     
    /**
     * @获取活动支付价格，购买数量
     * @return array 
	 * @$type 0 普通活动 2 秒杀活动
     */ 
     public function ge_activity_price($aid,$currentAid,$suserid,$userid,$ac_num,$type){
        $where = array('id_activity'=>$aid);
        $activty=$this->get_activity($where);
        $total = $activty['total'];//活动可购买总数 ,-1为不限制
        $join_count = $activty['join_count'];//活动已购买数量
        $poor = $total-$ac_num;//$join_count;//活动剩余参数量
        
        //活动实际能购买的数量
//        if($ac_num>$poor && $total == 0){ //王青的判断
         if($poor < 0 && $total == 0){
            return array();
        }else{
            $times = $ac_num;
        }
        //是否推广（default0:未推广,1:已推广）活动推广表(community_spread)
        $activtyType = array('spread');
        /**
         * @推广活动
         */
        if(in_array(trim($type),$activtyType)){
            //获取推广价格
            $spread = $this->get_community_spread($where);
            $per_join_price = $spread[0]['spread_price'];
            $price =  $per_join_price*$times;    
        }else{
			
			if($type==2){
				$per_join_price = $activty['preferential_price'];
			}else{
				$per_join_price = $activty['join_price'];
			}

            $price = $per_join_price*$times;
        }
        $pay_price = $price;//没有分享的金额
        if($currentAid==$aid&&isset($suserid)&&$userid!=$suserid){
            $pay_share = PAY_SHARE*$times;
            $pay_price+=$pay_share;//分享过后的金额
        }
        $data = array(
            'pay_price' => $pay_price,
                'price' => $price,
       'per_join_price' => $per_join_price,
                'total' => $times,
          'id_business' => $activty['id_business'], //@author Jamai
          'object_type' => $activty['object_type'], //@author Jamai
                'title' => $activty['name']); //@author Jamai
        
        return $data;
    }
    
    /**
     * @生成订单，记录收入明细，组装提交支付宝需要数据
     */
    public function pay_activity_handle($pay,$currentAid,$suserid,$userid){
        
        $id_order = create_order_id();
        
        $data = array(
          'id_order' => $id_order,
          'quantity' => 1, //订单总数量
               'aid' => $pay['aid'], //活动id
       'id_business' => $pay['id_business'],
          //商家ID 需要是URL获取的商家 ID, 获取用户订单时需要根据活动详细信息判断
          //如重复的话，根据活动详情判断
           'id_shop' => $pay['sid'],
       'object_type' => 'community',//@author Jamai 2.0 更新 对象类型  为 社区活动，因为需要根据类型来判断社区活动ID
          'identity' => $_SESSION['identity'],
           'payMode' => _get_pay_mode(),//支付方式
             'price' => $pay['pay_price'],//订单总金额
           'payment' => $pay['pay_price'],//支付金额
             'total' => $pay['total'],//活动购买数量
             'phone' => $pay['phone'],//活动购买数量
        );

        try{
            $this->db->trans_begin();
            $this->add($data, $userid);//生成订单
            //收入明细 分享者
            $income = array();
            if($currentAid==$pay['aid']&&$userid!=$suserid){
                $share = array(
                    'income_object_type'=>'user',
                    'id_income_object'=>$suserid,
                    'amount'=>PAY_SHARE*$pay['total'],
                    'id_source_type'=>$id_order,
                    'source_type'=>'order',
                );
            }
            
            for($i=0;$i<$data['total'];$i++){
              $income[$i]= array(
                'income_object_type' => $pay['object_type'],
                  'id_income_object' => $pay['id_business'], //明细表 是用户ID 非商户ID
                            'amount' => $pay['per_join_price'],
                    'id_source_type' => $id_order,
                       'source_type' => 'order',
              );
            }
            
            if( ! empty($share)) 
              array_push($income, $share);
              
            $this->insert_income_item($income);
            $this->db->trans_commit();
            
            //支付宝数据
            $payData = array(
                'pay_type' => $data['payMode'],//支付方式
                'trade_no' => $id_order,//订单号
              'total_free' => $data['payment'],//支付金额
                   'title' => $pay['title'] . ' - 参加社区活动',
                    'desc' => '参加社区活动',
                  'source' => 'community',//社区活动支付
				   'id_business' => $pay['id_business'],
            );
            
            $this->pay($payData);//提交数据到支付宝平台。进行支付操作 
            return true;
        }
        catch(Exception $e) {
            $this->db->trans_rollback();
            return false;
        }     
    }
     
    
    /**
     * 支付接口
     */

    public function pay($data)
    {
        //获取支付配置
        $pay_config = _get_pay_config();
        if(empty($pay_config)|| $pay_config === false) {
            echo '<script>alert("缺少支付配置文件！");javascript:history.go(-1);</script>';
            exit;
        }

		$id_business = $data['id_business'];

        //初始化参数
        $parameter = array();
        //请保证以下参数，必填，正确
		if($this->is_pay_business($id_business)){
			$parameter['partner'] = $id_business;
		}else{
			$parameter['partner'] = $pay_config['biz_id'];
		}
        $parameter['app_id'] = $pay_config['app_id'][$data['source']];
        $parameter['input_charset'] = 'utf-8';
        $parameter['pay_method'] = $data['pay_type']; //支付方式，支付宝/财付通
        $parameter['total_fee'] = round($data['total_free'], 2); //仅支持2位有效数字
        $parameter['trade_no'] = $data['trade_no'];//随机生成一个订单号，根据业务处理
        $parameter['title'] = $data['title'];
        $parameter['desc'] = $data['desc'];
        $para_sort = $this->arg_sort($parameter);//排序
	    $url_string = $this->arr_to_url_string($para_sort);//转成键值对
	    $sign_key = 'APP_TOKEN='.$pay_config['app_token'].'&BIZ_TOKEN='.$pay_config['biz_token'];
	    $mysign = $this->md5_sign($url_string, $sign_key);
	    $para_sort['sign'] = $mysign;
        $html = '<form action="'.$pay_config['pay_geteway'].'" name="demopay">';
    	foreach ($para_sort as $k => $v){
    		$html .= '<input type="hidden" name="'.$k.'" value="'.$v.'" />';
    	}
    	$html .= '</form><script>window.onload = function(){document.forms["demopay"].submit();}</script>';

    	echo $html;

		$msg = date('Y-m-d H:i:s');
		$msg = $msg.": ";
		$msg .= $html;
		$msg .="\n";
		file_put_contents(DOCUMENT_ROOT.'/pay.txt',var_export($msg,TRUE),FILE_APPEND);
    }


    //判断商家是否指定账户收款
    public function is_pay_business($id_business){
		$is_pay_business = false;
		
		$result = $this->get_pay_business($id_business);

		if($result[0]['pay_mode']==1){
			$is_pay_business = true;		
		}

        return $is_pay_business;
    }

    /**
     * 通过商家ID获取商家信息
     */
    public function get_pay_business($id_business){
        $this->db->select('*')->from($this->table_business)->where('id_business', $id_business);
        $result = $this->db->get()->result_array();
        return $result;
    }


    //对参数进行排序
    public function arg_sort($para)
    {
        ksort($para);
        reset($para);
        return $para;
    }
    //把数组转正key=value&类型的字符串
    public function arr_to_url_string($para)
    {
        $str = '';
        foreach ($para as $k => $v) {
            if ($v) {
                $str .= $k . '=' . urlencode($v) . '&';
            }
        }
        $str = substr($str, 0, -1);
        return $str;
    }

    //对数据进行签名
    public function md5_sign($str, $key)
    {
        return strtoupper(md5($str . $key));
    }
    
    
    public function check_sign($data,$sign){
        //获取支付配置
        $pay_config = _get_pay_config();
        $para = $this->arg_sort($data);
        $key =  $pay_config['app_token'].$pay_config['biz_token'];
        $mysign =  $this->md5_sign($this->arr_to_url_string($para),$key);
        if(strtoupper($sign)==$mysign){
            return TRUE;
        }else{
            return FALSE;
        }	   
	}
    
    /**
     * @寻找图片路径
     */
    public function parse_img_url($file,$img,$d_img){
        if($img!=''){
            $imgArray = explode('.',$img);
            $name = $imgArray[0];
            $strArray=str_split(substr($name, 0,4));
            $str=join("/",$strArray);
            $img_path = $_SERVER['DOCUMENT_ROOT'].'/attachment/business/'.$file.'/'.$str.'/'.$img;
            if(file_exists($img_path)){
                return '/attachment/business/'.$file.'/'.$str.'/'.$img;
            }else{
                return '/attachment/defaultimg/'.$d_img;
            }
        }else{
            //默认图片
            return '/attachment/defaultimg/'.$d_img;
        }
        
    }
    
 
    /**
     *@收入明细
     */
    public function insert_income_item($data){
        return $this->db->insert_batch("bn_income_item", $data);
    }
    /**
     * @修改收入明细状态
     */
    public function modify_income_item($data,$where){
        return $this->db->update('bn_income_item', $data, $where);
    }
    /**
     *@查询收入明细
     */
    public function query_income_item($where){
        $this->db->select('*')->from('bn_income_item')->where($where);
        $result = $this->db->get()->result_array();
        $return = !empty($result) ? $result : false;
        return $return;
    }
    
    /**
     * @支付操作日志
     */
     public function insert_handle_log($data){
        return $this->db->insert("bn_finance_log", $data);
     }
    /**
     * @修改支付操作日志
     */
    public function modify_handle_log($data,$where){
        return $this->db->update('bn_finance_log', $data, $where);
    }
     
    /**
     *@查询支付操作日志
     */
    public function query_handle_log($where){
        $this->db->select('*')->from('bn_finance_log')->where($where);
        $result = $this->db->get()->result_array();
        $return = !empty($result) ? $result[0] : false;
        return $return;
    }


    /*
     * zxx
     * 查找 总收入 和可结算 还有 不可结算金额
     */
    public function get_settlement_amount($where='')
    {
        $this->db->select('FORMAT(SUM(i.amount), 2) as total', false)
            ->from($this->table . ' as o')
            ->join($this->table_income." as i","o.id_order = i.id_source_type","left")
            ->join($this->table_ticket." as t","t.id_item = i.id_eticket","left");
        $this->db->where($where);

        return $this->db->get()->row();
    }

    /*
     * zxx
     * 查找 可结算 信息
     */
    public function get_settlement_info($where='')
    {
        $this->db->select('i.state AS i_state, i.*, t.state AS t_state, t.*, 
                o.id_object AS objectID, o.object_type AS objectType, 
                o.created AS o_created, o.buyer', false)
            ->from($this->table . ' as o')
            ->join($this->table_income." as i","o.id_order = i.id_source_type","left")
            ->join($this->table_ticket." as t","t.id_item = i.id_eticket","left");
        $this->db->where($where);

        $r = $this->db->get()->result_array();
       // echo $this->db->last_query();
        return $r;
    }
}


/* End of file list_model.php */
