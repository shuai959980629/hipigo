<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Order extends WX_Controller {
    
    private $id_business ;//商家ID
    private $id_shop ;//门店ID
    private $id_activity;//活动id
    private $id_open ;//微信id; 
    private $page ;//页码
    
    public function __construct()
    {
        parent::__construct();
        $this->id_business = $this->bid;
        $this->id_shop = $this->sid;
        
    }
    
    public function index (){
        $this->id_activity = $this->input->get('aid'); //活动id
        $this->id_open = $this->input->get('oid');//微信id; 
        if(empty($this->id_activity ) || empty($this->id_open)){
            parent::show_tip();
        }
        $where = array('id_business'=>$this->id_business,'object_type'=>'community','id_object'=>$this->id_activity,'buyer'=>$this->id_open);
        $this->load->model('list_model','list');
        //进入订单页面前。判断是否已经成功创建订单。若有订单，返回订单序列号,进入历史订单页面
        $order_id = $this->list->get_order_id($where); 
        if($order_id){
            header("Location: /wapi/{$this->id_business}/{$this->id_shop}/community/allorder_list?oid={$this->id_open}");
        }
        //新订单。。。进入订单详情页面。
        $where = array('id_activity'=>$this->id_activity);
        //查询活动信息
        $data = $this->list->get_activity($where);
        //debug($data);
        $this->smarty->assign('url_action','/wapi/'.$this->id_business.'/'.$this->id_shop.'/order/add_order?aid='.$this->id_activity.'&oid='.$this->id_open.'&price='.$data['join_price']);
        $this->smarty->assign('title','个人订单详情');
        $this->smarty->assign('atitle',$data['name']);
        $this->smarty->assign('price',$data['join_price']);
        $this->smarty->view('order_detail'); 
    }
    
    /**
     * @zhoushuai
     * 生成订单。。。
     * 订单创建成功。调用支付接口。
     */
     
     public function pay(){
        session_start();
        $currentAid= $_SESSION['aid'];//分享活动的id
        $suserid = $_SESSION['suserid'];//分享者id
        $userid = $_SESSION['userid'];//
        $this->id_activity = $this->input->get('aid'); //当前活动id
        $this->load->model('list_model','list');
        if(empty($this->id_activity )){
            parent::show_tip('传入参数有误。订单操作失败！',"/wapi/{$this->id_business}/{$this->id_shop}/community/detail?suserid={$suserid}&aid={$this->id_activity}");
        }
        //查询活动参与价格
        $where = array('id_activity'=>$this->id_activity);
        $activty=$this->list->get_activity($where);
        $price = $activty['join_price'];
        $pay_price = $price;
        if($currentAid==$this->id_activity&&isset($suserid)&&$userid!=$suserid){
            $pay_price+=PAY_SHARE;
        }
        $id_order = create_order_id();
        $data['id_order'] = $id_order;
        $data['quantity'] = 1;//订单总数量
        $data['aid'] = $this->id_activity;//活动id
        $data['id_business'] = $this->id_business;
        $data['id_shop'] = $this->id_shop;
        $data['object_type'] = 'community';//对象类型
        $data['identity'] = $_SESSION['identity'];//用户身份
        //支付方式
        $data['payMode'] = _get_pay_mode();//支付方式
        $data['price'] = $pay_price;//订单总金额
        $data['payment'] =$pay_price;//支付金额
        try{
            $this->db->trans_begin();
            $this->list->add($data,$userid);//生成订单
            //收入明细 分享者
            if($currentAid==$this->id_activity&&$userid!=$suserid){
                $income = array(
                    'income_object_type'=>'user',
                    'id_income_object'=>$suserid,
                    'amount'=>PAY_SHARE,
                    'id_source_type'=>$id_order,
                    'source_type'=>'order',
                );
                $this->list->insert_income_item($income);
            }
            $income = array(
                    'income_object_type'=>'business',
                    'id_income_object'=>$this->id_business,
                    'amount'=>$price,
                    'id_source_type'=>$id_order,
                    'source_type'=>'order',
                );
            $this->list->insert_income_item($income);
            $this->db->trans_commit();
            //支付宝数据
            $payData = array(
                'pay_type'=>$data['payMode'],
                'trade_no'=>$id_order,
                'total_free'=>$data['payment'],
                'title'=>'社区活动',
                'desc'=>'社区活动'
            );
            $this->list->pay($payData);//提交数据到支付宝平台。进行支付操作
            
        }catch(Exception $e){
             $this->db->trans_rollback();
             parent::show_tip("订单生成失败,请重新操作！","/wapi/{$this->id_business}/{$this->id_shop}/community/detail?suserid={$suserid}&aid={$this->id_activity}");          
        }
     }
     
     /**
      * @zhoushuai
      * 根据订单id 获取信息
      * 未支付订单。完成支付功能
      */
      
      public function _pay(){
        $id_order = $this->input->get('order_id');//订单id
        if(empty($id_order )){
           header("Location: /wapi/{$this->id_business}/{$this->id_shop}/community/allorder_list");
        }else{
            $this->load->model('list_model','list');
            $order = $this->list->get_order($id_order);
            $where = array('id_activity'=>$order['id_object']);
            //查询活动信息
            //状态 default 1:未开启,2:开启活动,0:关闭活动，-1:删除
            $data = $this->list->get_activity($where);
            $state = $data['state'];
            if($state<1){
                parent::show_tip("该活动已经关闭或被删除！","/wapi/{$this->id_business}/{$this->id_shop}/community/allorder_list?oid={$order['buyer']}");
            }
            if($order){
                //支付方式
                $order['payMode'] = _get_pay_mode();//支付方式
                $payData = array(
                    'pay_type'=>$order['payMode'],
                    'trade_no'=>$order['id_order'],
                    'total_free'=>$order['pay_amount'],
                    'title'=>'社区活动',
                    'desc'=>'社区活动'
                );
                $this->list->pay($payData);
            }else{
                header("Location: /wapi/{$this->id_business}/{$this->id_shop}/community/allorder_list");
            }
        }
      }
    
    
    
}