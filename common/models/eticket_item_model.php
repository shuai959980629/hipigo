<?php
/**
 * 
 * @copyright(c) 2014-6-26
 * @author Jamai
 * @version 2.0
 */

class Eticket_Item_Model extends CI_Model
{
  protected $table = 'bn_eticket_item';

  public function __construct()
  {
    $this->load->database();
  }
  
  public function getEticketItemCode($where)
  {
    $this->db->select(array('object_type', 'id_object', 'id_customer', 'use_time',
        'id_item', 'code', 'get_time', 'state'))
        ->from($this->table)
        ->where($where);
    $result = $this->db->get()->result_array();
    
    // echo $this->db->last_query();
    return $result;
  }
  /**
   * 参加社区活动，产生一张电子验证码
   *
   * @param source 来源  默认为community  
   * @param array $data
   * @return boolean
   */
  public function createEticket($aid,$user,$identity,$bid,$sid, $source = 'community')
  {
    $userid = 0;
    $phone = 0;
    if($identity=='visitor'){
        $phone = $user;
    }
    else{
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
    
    $eticket_info['state'] = 1;
      
    $this->db->insert($this->table, $eticket_info);
    $id_item = $this->db->insert_id();
    
    $data_info['code'] = $code;
    $data_info['id_item'] = $id_item;
    
    return $data_info;
  }
  
  
}
