<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Order extends WX_Controller {
    
    private $id_business ;//商家ID
    private $id_shop ;//门店ID
    private $id_activity;//活动id
    private $id_resource;//资源id
    private $id_open ;//微信id; 
    private $page ;//页码
    private $userID; //用户ID
	private $pay_notify = '40001';//异步通知返回标识
	private $out_trade_no_notify = '';
	private $status_notify = '';
	private $trade_no_notify = '';
    
    public function __construct()
    {
        parent::__construct();
        
        $this->id_business = $this->bid;
        //设置原ID到COOKIE，方便后面获取
        //因id_business是商家ID，在数据库目前达人也存储的是商家ID，所以要记录原商家ID
        setcookie('id_business_source', $this->id_business, 
              time() + (60 * 60 * 24 * 30), '/', '');
        
        if( ! $_SESSION)
          $_SESSION = $_COOKIE;
        
        $this->id_shop = $this->sid;
        
    }
    
    public function index (){
      header("Location: " . $_SERVER['HTTP_HOST'] . 
            "/wapi/{$this->id_business}/{$this->id_shop}/community/home");
    }
    
    /**
     * @zhoushuai
     * @version 2014-06-13 V-1.0
     * @param state 支付分类 GET请求方式
     * @支付接口，手机wapi支付。和pc端alipay
     */
     
    public function pay() 
    {
      
      $state = $this->input->get('state');//支付分类
      
      if( ! $this->userid) 
        if($state == 'resource')
          $this->redirect('/resource/index');
      
      switch($state) {
        case 'resource':
          $this->_pay_resource();
          break;
        default:
          //社区活动支付
          $this->_pay_activity();
          break;
      }
    }
     
    /**
     * 资源支付
     * @jamai
     * @param rid 资源id GET请求方式
     * @param num 资源购买数量
     */
    private function _pay_resource()
    {
      $this->id_resource = $this->input->get('rid'); //当前资源id
      $num= $this->input->get('num');//活动购买数量
      $num = intval($num) ? intval($num) : 1;
      
      //查看获取的RID资源是否存在 
      $this->load->model('resources_model', 'resources');
      $where = array('id_resource' => $this->id_resource);
      $result = $this->resources->get_resources($where);
      
      if( ! $result) {
        parent::show_tip('传入参数有误, 操作失败！',
          "/wapi/{$this->id_business}/{$this->id_shop}/community/publish");
        return;
      }
      
      //查看数量是否充足
        if($result[0]['num'] >= 0){
            if(($result[0]['num'] - $num) < 0) {
                parent::show_tip('数量不足, 操作失败！',
                    "/wapi/{$this->id_business}/{$this->id_shop}/community/publish");
                return;
            }
        }
      
      //价格计算
      $price = PAY_BUY + $result[0]['price']; //单价
      $totalPirce = $num * $price;//总价
      
      //组织数据
      $id_order = create_order_id();
      
      $data = array(
        'id_order' => $id_order,
        'quantity' => 1,
             'aid' => $this->id_resource,
     'id_business' => $result[0]['owner'],//资源创建者 //$this->id_business,
         'id_shop' => $this->id_shop,
     'object_type' => 'resource',
        'identity' => $_SESSION['identity'],
         'payMode' => _get_pay_mode(),
           'price' => $totalPirce,
         'payment' => $totalPirce,
           'total' => $num,
      );
      
      //添加到数据库 订单
      $this->load->model('list_model', 'order');
      
      try {
        $this->db->trans_begin();
        //添加到订单表
        $this->order->add($data, $this->userid);
        
        //收入明细
          $buy = array(
        'income_object_type' => 'system',
          'id_income_object' => 90,
                    'amount' => PAY_BUY * $num,
            'id_source_type' => $id_order,
               'source_type' => 'order',
          );
        
        $income = array();
        for($i = 0; $i < $num; $i++) {
          $income[$i] = array (
        'income_object_type' => 'business',
          'id_income_object' => $result[0]['owner'],//资源创建者 //$this->id_business,
                    'amount' => $result[0]['price'],
            'id_source_type' => $id_order,
               'source_type' => 'order',
          );
        }
        
        if(PAY_BUY) 
          array_push($income, $buy);
        
        $this->order->insert_income_item($income);
        $this->db->trans_commit();
        
        //支付宝数据
        $payData = array(
            'pay_type' => $data['payMode'],//支付方式
            'trade_no' => $id_order,//订单号
          'total_free' => $data['payment'],//支付金额
               'title' => $result[0]['resource_title'] . ' - 资源购买',
                'desc' => '资源购买',
              'source' => 'resource_buy',
        );
        $this->order->pay($payData);//提交数据到支付宝平台。进行支付操作
        return true;
      }
      catch(Exception $ex) {
        $this->db->trans_rollback();
      }
    }
     
     
     /**
      * @zhoushuai 
      * @BY Jamai update 2014-07-07  修改达人活动  直接指定订单/明细给当前达人
      * 
      * @活动支付
      * @param aid 活动id GET请求方式
      * @param ac_num 活动购买数量
      * @param type 活动类型
      */
     private function _pay_activity(){
        $currentAid= $_SESSION['aid'];//分享活动的id
        $suserid = $_SESSION['suserid'];//分享者id
        $userid = $_SESSION['userid'];//
        $this->id_activity = $this->input->get('aid'); //当前活动id
        $ac_num= $this->input->get('ac_num');//活动购买数量
        $type = $this->input->get('type');//活动类型。推广活动spread 。。。
        $ac_num = intval($ac_num)?intval($ac_num):1;
        $phone = $this->input->get('phone');
        $this->load->model('list_model','list');
        if(empty($this->id_activity)){
            $suserid = $suserid?$suserid:$userid;
            parent::show_tip('传入参数有误。订单操作失败！',"/wapi/{$this->id_business}/{$this->id_shop}/community/detail?suserid={$suserid}&aid={$this->id_activity}");
            exit;
        }
        /**
         * @第一步；活动原始单价，支付金额，分享支付金额，推广支付金额，购买数量
         */
        //得到参与该活动需要支付的金额￥
        $payData = $this->list->ge_activity_price($this->id_activity,$currentAid,$suserid,$userid,$ac_num,$type);
        if(empty($payData)){
            $suserid = $suserid?$suserid:$userid;
            parent::show_tip('购买数量超过活动参与总数，无法参加！',"/wapi/{$this->id_business}/{$this->id_shop}/community/detail?suserid={$suserid}&aid={$this->id_activity}");
            exit;
        }
        /**
         * @第二步；生成订单，记录收入明细，组装提交支付宝需要数据，
         * @完成以上操作-》提交数据到支付宝平台。进行支付操作。
         */
         $payData['aid']=$this->id_activity;
         
         //@author Jamai  修改商家ID 给创建者user
         //直接在活动价格时返回
         $payData['bid']=$this->id_business;
         
         $payData['sid']=$this->id_shop;
         $payData['phone']=$phone; 
         $result = $this->list->pay_activity_handle($payData,$currentAid,$suserid,$userid);
         if(!$result){
            $suserid = $suserid?$suserid:$userid;
            parent::show_tip("订单生成失败,请重新操作！","/wapi/{$this->id_business}/{$this->id_shop}/community/detail?suserid={$suserid}&aid={$this->id_activity}");
            exit;
         }
     }    
     
    
     /**
      * @sc 
      * @支付宝异步通知接收函数
      */
     public function pay_notify(){

		if($this->pay_notify_param()){ echo $this->pay_notify; $this->pay_notify_update(); }
		
     } 


     /**
      * @sc 
      * @支付宝异步通知订单查询及更新函数
      */
     public function pay_notify_update(){
		
			$out_trade_no = $this->get_out_trade_no();
			$status = $this->get_status();
			$trade_no = $this->get_trade_no();

			$this->load->model('list_model','list');

			$order = $this->list->get_order($out_trade_no);

			$code = $order['state'];
			$id_business = $order['id_business'];

			if($code==0&&($status=='TRADE_SUCCESS' || $status=='TRADE_FINISHED' || $status == 'TRADE_FINISH')) {

				//活动购买数量增加
				$this->list->add_join_count($order['id_object'],$order['total']);
				//社区活动参与表社区活动表添加一条记录--增加一个参与成员
				$this->list->insert_member($order['id_object'],$order['buyer'],$order['identity'],$order['phone']);
				//更新订单状态
				$data = array('state' => 1,'trade_no'=>$trade_no); 
				$this->list->modify_order($data, $out_trade_no);
				//查询收入明细记录
				$income = array('id_source_type'=>$out_trade_no,'source_type'=>'order');
				//同一个订单多条订单明细。记录不同的电子验证码
				$incomelist = $this->list->query_income_item($income);

				$codes='';

				$this->load->model('hipigouser_model','hipigouser');

				for($i=0;$i<count($incomelist);$i++){

					//存在分享额为支付时更新个人账户金额
					if($incomelist[$i]['id_income_object'] != $order['id_business'] && 
						$incomelist[$i]['income_object_type'] == 'user'){
						$where = 'id_user = ' . $incomelist[$i]['id_income_object'];
						$res = $this->hipigouser->get_hipigo_user_info($where);
						$amount['amount'] = $res[0]['amount']+$incomelist[$i]['amount'];
						$this->hipigouser->update_hipigo_user($amount,$where);
						$where = 'id_user = ' . $incomelist[$i]['id_income_object'];
						$res = $this->hipigouser->get_hipigo_user_info($where);
						$mocome = array('state'=>1);
					}else{
						//参加社区活动，产生一张电子验证码
						$result = $this->list->create_eticket($order['id_object'],$order['buyer'],$order['identity'],$order['id_business'],$order['id_shop']);
						$codes .= '['.$result['code'] .']';
						$mocome = array('state'=>1,'id_eticket'=>$result['id_item']);
					}

					//修改收入明细状态,向明细中添加电子验证码id
					$con = array('id_item' => $incomelist[$i]['id_item']);
					$this->list->modify_income_item($mocome,$con);

					/**
					 * @用户支付成功-记录操作日志
					 */
					 $Log = array(
						'id_order'=>$out_trade_no,//订单id
						'id_business'=>$id_business,//商家id
						'id_item'=>$incomelist[$i]['id_item'],//收入明细id
						'type'=>1,//当前操作,1:支付成功
						'date'=>date('Y-m-d H:i:s',time())
					);

					$this->list->insert_handle_log($Log);

				}

                   //发送消费码至手机
                   $this->sendcode($order['phone'],$codes);

			}

     } 


     /**
      * @sc 
      * @支付宝异步通知post参数判断函数
      * @param out_trade_no 支付平台订单流水号
      * @param trade_no 支付宝订单流水号
      * @param pay_time 支付宝平台时间
      * @param status 支付宝订单状态
      */
     public function pay_notify_param(){
		
		$is_post = strtolower($_SERVER['REQUEST_METHOD']);
		$msg = date('Y-m-d H:i:s');
		$msg = $msg.": ";

		if($is_post=='post'){

			foreach ($_REQUEST as $key => $value){

				if($key=='out_trade_no'){$out_trade_no = $value;}
				if($key=='trade_no'){$trade_no = $value;}
				if($key=='pay_time'){$pay_time = $value;}
				if($key=='status'){$status = $value;}
				
				$msg .= " ".$key." -> ".$value;
			}

			$msg .="\n";
			file_put_contents(DOCUMENT_ROOT.'/pay_notify.txt',var_export($msg,TRUE),FILE_APPEND);

			if($out_trade_no&&$trade_no&&$pay_time&&$status){
				$this->set_out_trade_no($out_trade_no);
				$this->set_status($status);
				$this->set_trade_no($trade_no);
				return true;
			}

		}

		return false;
		
     } 


     /**
      * @sc 
      * @支付宝异步通知测试函数
      */
     public function pay_notify_test(){
		
		$post_data = array(
			'out_trade_no' => '1404240645080705',
            'trade_no'=>'201404031100100437',
			'pay_time' =>time(),
			'status' => 'TRADE_FINISHED'
		);

		$url = "http://127.0.0.1/wapi/90/1/order/pay_notify";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($ch) ;

		if (curl_errno($ch)) {
			return curl_error($ch);
		}

		curl_close($ch);	
		
		echo $result;
		
     } 
    
	
	private function set_trade_no($trade_no) {
		$this->trade_no_notify = $trade_no;
	}

	private function set_out_trade_no($out_trade_no) {
		$this->out_trade_no_notify = $out_trade_no;
	}

	private function set_status($status) {
		$this->status_notify = $status;		
	}

	private function get_trade_no() {
		return $this->trade_no_notify;		
	}

	private function get_out_trade_no() {
		return $this->out_trade_no_notify;
	}

	private function get_status() {
		return $this->status_notify;		
	}


}