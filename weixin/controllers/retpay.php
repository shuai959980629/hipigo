<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Retpay extends WX_Controller
{

    private $id_business; //商家ID
    private $id_shop; //门店ID
    private $id_activity; //活动id
    private $id_open; //微信id;
    private $page; //页码

    public function __construct()
    {
      parent::__construct();
      $this->load->model('list_model', 'list');
    }
    
    /**
     * 验证提交过来的数据是否真实有效
     * 
     * @param $error_return_url 失败返回的URL
     * @author Jamai
     * @version 2.0
     */
    private function vail_form ($error_return_url = '') 
    {
      $sign = $_REQUEST['sign'];
      
      unset($_REQUEST['_input_charset']);
      unset($_REQUEST['sign']);
              
      $self = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
      
      if( ! $this->list->check_sign($_REQUEST, $sign)) { //签名错误。。
        echo '出现异常，<a href="'.$self.'">请重试！</a>';
        return;
      }
      
      $state   = $_REQUEST['status'];
      $orderid = $_REQUEST['out_trade_no'];//订单号码
      $sd_id   = $_REQUEST['trade_no'];//流水号码
      //查询订单
      $order = $this->list->get_order($orderid);
      
      if($_COOKIE['id_business_source'])
        $this->id_business = $_COOKIE['id_business_source'];
      else
         $this->id_business = $order['id_business'];
      
      $this->id_shop     = $order['id_shop'];
      
      if ($state == 'TRADE_FINISH' || $state == 'TRADE_SUCCESS')
        return $order += array(
          'orderID' => $orderid,
          'sd_id' => $sd_id);
      else 
        //失败， 到具体方法进行相应的处理
        return false;
    }
    
    public function index()
    {
      $this->load->model('list_model', 'list');
        $sign = $_REQUEST['sign'];
        unset($_REQUEST['_input_charset']);
        unset($_REQUEST['sign']);
        $self= 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if( ! $this->list->check_sign($_REQUEST,$sign)) {//签名错误。。
          echo '出现异常，<a href="'.$self.'">请重试！</a>';
          return;
        }
        $state = $_REQUEST['status'];
        $orderid = $_REQUEST['out_trade_no'];//订单号码
        $sd_id = $_REQUEST['trade_no'];//流水号码
        //查询订单
        $order = $this->list->get_order($orderid);
        
        //$this->id_business = $order['id_business'];
        $this->id_business = $_COOKIE['id_business_source'];
        
        $this->id_shop = $order['id_shop'];
        if ($state == 'TRADE_FINISH' || $state == 'TRADE_SUCCESS' || $state=='TRADE_FINISHED') {
            // 支付成功 
            if($order['state']!=1){
                //订单未处理过。。。
                try{
                  
                    $this->db->trans_begin();
                    //活动购买数量增加
                    $this->list->add_join_count($order['id_object'],$order['total']);
                    //社区活动参与表社区活动表添加一条记录--增加一个参与成员
                    $this->list->insert_member($order['id_object'],$order['buyer'],$order['identity'],$order['phone']);
                    //更新订单状态
                    $data = array('state' => 1,'trade_no'=>$sd_id); 
                    $this->list->modify_order($data, $orderid);
                    //查询收入明细记录
                    $income = array('id_source_type'=>$orderid,'source_type'=>'order');
                    //同一个订单多条订单明细。记录不同的电子验证码
                    $incomelist = $this->list->query_income_item($income);

                    $codes='';
                    for($i=0;$i<count($incomelist);$i++){
                        //存在分享额为支付时更新个人账户金额
                        if($incomelist[$i]['id_income_object'] != $order['id_business'] && 
                            $incomelist[$i]['income_object_type'] == 'user'){
                            $this->load->model('hipigouser_model','hipigouser');
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
                            'id_order'=>$orderid,//订单id
                            'id_business'=>$this->id_business,//商家id
                            'id_item'=>$incomelist[$i]['id_item'],//收入明细id
                            'type'=>1,//当前操作,1:支付成功
                            'date'=>date('Y-m-d H:i:s',time())
                        );
                        $this->list->insert_handle_log($Log);
                    }
                    //发送消费码至手机
                    $this->sendcode($order['phone'],$codes);

                    $this->db->trans_commit();
                    //跳转
                    $this->redirect('/community/detail?aid=' . $order['id_object'] . '&from=' . 
                             $this->controller . '/' . $this->action, '', 
                             array('business' => $this->id_business, 'shop' => $this->id_shop));
                    //header("Location: /wapi/{$this->id_business}/{$this->id_shop}/community/detail?aid={$order['id_object']}&from=" . $this->controller . '/' . $this->action);
                    
                }catch(Exception $e){
                    $this->db->trans_rollback();
                    echo '出现异常，<a href="'.$self.'">请重试！</a>';
                } 
            }
            else {
                //订单已处理过了。。。
                $this->redirect('/community/detail?aid=' . $order['id_object'] . '&from=' . 
                             $this->controller . '/' . $this->action, '', 
                             array('business' => $this->id_business, 'shop' => $this->id_shop));
                
            }
        } else {
            //失败
            $where = array('income_object_type'=>'user','id_source_type'=>$orderid);
            $data = $this->list->query_income_item($where);
            $this->redirect('/community/detail?aid=' . $order['id_object'] . '&from=' . 
                             $this->controller . '/' . $this->action, '', 
                             array('business' => $this->id_business, 'shop' => $this->id_shop));
            //header("Location: /wapi/{$this->id_business}/{$this->id_shop}/community/detail?suserid={$data['id_income_object']}&aid={$order['id_object']}");
        }
    }

    public function resource_buy()
    {
      $order = $this->vail_form();
      
      //支付成功在进行处理
      if( ! $order) {
        //失败
        $where = array('income_object_type'=>'user','id_source_type'=>$order['orderID']);
        $data = $this->list->query_income_item($where);
        //header("Location: /wapi/{$this->id_business}/{$this->id_shop}/community/detail?suserid={$data['id_income_object']}&aid={$order['id_object']}");
      }
      else {
        if($order['state'] != 1) {//订单未处理过
          $this->db->trans_begin();
          try {
            $this->load->model('resources_model', 'resources');
            
            //减少 资源的数量
            $this->resources->update_resource(
              array('num' => 'num - ' . $order['total']),
              'id_resource = ' . $order['id_object']);
            
            //查看我的资源里是否已有该资源
            $where = array(
                'relate.id_resource' => $order['id_object'],
                'id_user' => $order['buyer']);
            $result = $this->resources->get_by_resources($where);
            
            //增加记录为我的资源
            if($result) {//修改
              $this->resources->update_by_resource(
                array(
                  'num' => 'num + ' . $order['total'],
                  'updated' => date('Y-m-d H:i:s', time())),
                array(
                  'id_user' => $order['buyer'], 
                  'id_resource' => $order['id_object']));
            }
            else {//添加
              $this->resources->insert_by_resource(array(
                'id_resource' => $order['id_object'],
                'num' => $order['total'],
                'id_user' => $order['buyer']));
            }
            
            //更新订单状态
            $this->list->modify_order(array(
              'state' => 1, 
              'trade_no' => $order['sd_id']),
              $order['orderID']);
            
            //查询收入明细记录  同一个订单多条订单明细。记录不同的电子验证码
            $incomelist = $this->list->query_income_item(array(
              'id_source_type' => $order['orderID'],
              'source_type'    => 'order'));
            
            for($i = 0; $i < count($incomelist); $i++) {
              //参加社区活动，产生一张电子验证码
              $result = $this->list->create_eticket(
                $order['id_object'], $order['buyer'], $order['identity'],
                $order['id_business'], $order['id_shop'], 'resource'); // 取消默认验证
              
              //资源由商户验证
              $mocome = array('state' => 1,'id_eticket' => $result['id_item']);
              
              //修改收入明细状态,向明细中添加电子验证码id
              $con = array('id_item' => $incomelist[$i]['id_item']);
              $this->list->modify_income_item($mocome, $con);
              
               // 用户支付成功-记录操作日志
              $log = array(
                'id_order'    => $order['orderID'],//订单id
                'id_business' => $order['id_object'],//$this->id_business,//商家id
                'id_item'     => $incomelist[$i]['id_item'],//收入明细id
                'type'        => 1,//当前操作,1:支付成功
                'date'        => date('Y-m-d H:i:s',time())
              );
              $this->list->insert_handle_log($log);
            }
            $this->db->trans_commit();
            $this->redirect('/community/publish?rid=' . $order['id_object'] . '&from=' . 
                             $this->controller . '/' . $this->action, '', 
                             array('business' => $this->id_business, 'shop' => $this->id_shop));
            // header("Location: /wapi/{$this->id_business}/{$this->id_shop}/community/publish?rid={$order['id_object']}");
          }
          catch(Exception $e) {
              $this->db->trans_rollback();
              echo '出现异常，<a href="' .$self . '">请重试！</a>';
          } 
        }
        else{
          //订单已处理过了。。。
          $this->redirect('/community/publish?rid=' . $order['id_object'] . '&from=' . 
                             $this->controller . '/' . $this->action, '', 
                             array('business' => $this->id_business, 'shop' => $this->id_shop));
          // header("Location: /wapi/{$this->id_business}/{$this->id_shop}/community/publish?rid={$order['id_object']}");
        }
      }
    }
}

?>