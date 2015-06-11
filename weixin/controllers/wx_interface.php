<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Wx_interface extends WX_Controller {
    
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
    
    /**
     * @method  get方式请求
     * @aid 活动id 获取活动参与成员列表
     * @oid 微信id 验证是否为当前用户
     * @page 页码
     * @返回数据json格式
     * 1,活动成员列表，提供ajax接口，当传入page页数后返回对应页的数据，每页25条数据（json格式）
     */ 
     public function activ_list(){
        $this->id_activity = $this->input->get('aid'); //活动id
        $this->id_open = $this->input->get('oid');//微信id; 
        $this->page = intval($this->input->get('page'))?intval($this->input->get('page')):1;
        if(empty($this->id_activity ) || empty($this->id_open)){
           parent::return_client(0,null,'参数出入错误！');
        }       
        $where = array('id_activity'=>$this->id_activity);
        /**
         * @查询活动成员。并且分页
         */
         $data = array();
         $this->load->model('list_model','list');
         $res = $this->list->get_act_list($where,$this->page);
         if(empty($res)){
            parent::return_client(1,$res,'当前没有活动！');
         }
         /**
          * 当前用户排在第一位
          */
          for($i=0;$i<count($res);$i++){
              if($res[$i]['id_open']==$this->id_open){
                array_unshift($data,$res[$i]);   
              }else{
                $data[]=$res[$i];
              }
          }
          for($i=0;$i<count($data);$i++){
            //unset($data[$i]['id_open']);
            unset($data[$i]['id_activity']);
          }
         parent::return_client(1,$data,null);   
     }
     
     /**
      * @method  get方式请求
      * @aid 活动id 获取活动参与成员列表
      * @oid 微信id 验证是否为当前用户
      * @page 页码
      * @返回数据json格式
      * 2,我的订单列表，提供ajax接口，未支付排在前面，历史订单（已支付）排在后面，
      * 当传入page页数后返回对应页的数据，每页25条数据，（json格式）
      */
     public function order(){
        $this->id_activity = $this->input->get('aid'); //活动id
        $this->id_open = $this->input->get('oid');//微信id; 
        $this->page = intval($this->input->get('page'))?intval($this->input->get('page')):1;
        if(empty($this->id_activity ) || empty($this->id_open)){
           parent::return_client(0,null,'参数输入错误！');
        }
        $where = array('object_type'=>'community','buyer'=>$this->id_open);
        /**
         * @查询我的订单。并且分页
         */
         $this->load->model('list_model','list');
         //查询未支付总数 和 已支付总数
         $paid = $this->list->get_pay_count($where,1);
         $nonpay = $this->list->get_pay_count($where,0);
         $data = $this->list->get_order_list($where,$this->page);
         if(empty($data)){
            parent::return_client(0,$data,'当前没有订单！');
         }
         $this->load->model('chance_model','chance');
         for($i=0;$i<count($data);$i++){
              $data[$i]['img'] = $this->chance->parse_img_url($data[$i]['img']);
              $data[$i]['pay'] = $data[$i]['pay_amount']; 
              //已支付，查询返回的电子券验证码
              if($data[$i]['state']==1){
                   $data[$i]['ecode'] = $this->list->get_ecode($data[$i]['id_object'],$this->id_open);
              }
          }
         $result = array('paid'=>$paid,'nonpay'=>$nonpay,'list'=>$data);
         parent::return_client(1,$result,null);
        
     }
     
     
     /**
      * @zhoushuai
      * @获取表情
      * 
      */
      
      public function smile(){
         $smile = _get_smile_array();
         $data = array();
         foreach( $smile as $key =>$val){
            $data[$key] = "<img title='{$key}'  src = '/smile/{$val}'/>";
         }
         if(empty($data)){
            parent::return_client(0,$data,'表情库不存在!');
         }
         parent::return_client(1,$data,null);
      }
     
      
      /**
       * 表情替换
       * 将文字替换成 图片
       * @word 聊天内容
       */
       public function replace_smile(){
          $field = trim($this->input->get('word')); //聊天内容
          if(empty($field)){
             parent::return_client(0,null,'请输入聊天内容!');
          }
          if(strpos($field, '[') !== FALSE AND preg_match_all('/\[(.*?)\]/', $field, $matches)){
            $smile = _get_smile_array();
            if(empty($smile)){
                parent::return_client(0,null,'表情库不存在!');
            }
            for ($i = 0; $i < count($matches['0']); $i++)
			{
				if ($matches['1'][$i] != '')
				{
					foreach( $smile as $key =>$val){
					   if($key == $matches['0'][$i]){
                           $field = str_replace($matches['0'][$i],"<img title='{$key}'  src = '/smile/{$val}'/>",$field);  
					   }
                    }
				}
			}
          }
          parent::return_client(1,$field); 
       }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}