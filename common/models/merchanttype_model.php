<?php
/**
 * 
 * @copyright(c) 2014-6-26
 * @author Jamai
 * @version 2.0
 */

class MerchantType_Model extends CI_Model
{
  protected $table = 'bn_merchant_type';

  public function __construct()
  {
    $this->load->database();
  }
  
  /**
   * 获取社区活动点赞用户信息
   * @param $select String 查询字段 default *
   * @param $where String 查询条件
   * @param $options 
   *          order  排序
   *          offset 偏移量
   *          limit  读取条数
   * @author Jamai
   * @version 2.1
   **/
  public function byFieldSelect($fields = array(), $where = array(), $options = array(), $row = false)
  {
    if( ! $fields)
      $fields = '*';
    
    $this->db->select($fields)
              ->from($this->table);
    if($where)
      $this->db->where($where);
    
    if($options) {
      if($options['order'])
        $this->db->order_by($options['order']);
      
      if($options['offset'] && $options['limit'])
        $this->db->limit($options['limit'], $options['offset']);
      else if($options['limit'])
        $this->db->limit($options['limit']);
    }
    
    if($row)
      $result = (Array) $this->db->get()->row();
    else
      $result = $this->db->get()->result_array();
    // debug($this->db->last_query());
    return $result;
  }
   
}
