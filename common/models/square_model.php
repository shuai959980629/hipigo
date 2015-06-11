<?php
/**
 * 
 * @copyright(c) 2014-08-20
 * @author sc
 * @version Id:Square_Model.php
 */

class Square_Model extends CI_Model
{
    protected $table = 'bn_community_activity';
    protected $table_join = 'bn_community_activity_join';
    protected $table_sub = 'bn_business_subscribe';
    protected $table_user = 'bn_hipigo_user';
    protected $table_re = 'bn_review';
    protected $table_ticket = 'bn_eticket_item';
    protected $table_log = 'bn_community_log';
    protected $table_spread = 'bn_community_spread';
    protected $table_extends = 'bn_community_activity_extends';

    public function __construct(){

		  parent::__construct();
		  $this->load->database();

    }
    
  /**
   * 获取广场推荐信息（猜你喜欢）
   * @param $select String 查询字段 default *
   * @param $where String 查询条件
   * @param $options 
   *          order  排序
   *          offset 偏移量
   *          limit  读取条数
   * @author sc
   * @version 2.1
   **/
  public function getSquareCommunities($select = '*', $where = '', $options = array()){
    
    $this->db->select($select)->from($this->table);
    
    if($where){
		$this->db->where($where);
	}
    
    if($options) {

		  if($options['order']){
			$this->db->order_by($options['order']);
		  } else {
			$this->db->order_by('id_activity DESC');
		  }
		  
		  if($options['offset'] && $options['limit']){
			$this->db->limit($options['limit'], $options['offset']);
		  }else if($options['limit']){
			$this->db->limit($options['limit']);
		  }

    }
    
    if(strtoupper($select) == 'COUNT(*)'){
		  $result = $this->db->count_all_results();
	}else{
		  $result = $this->db->get()->result_array();
	}
    
	  return $result;
  }
  
	
  /**
   * 获取用户信息
   * @param $select String 查询字段 default *
   * @param $where String 查询条件
   * @param $options 
   *          order  排序
   *          offset 偏移量
   *          limit  读取条数
   * @author sc
   * @version 2.1
   **/
  public function getUserInformation($select = '*', $where = '', $options = array()){
    
    $this->db->select($select)->from($this->table_user);
    
    if($where){
		$this->db->where($where);
	}
    
    if($options) {

		  if($options['order']){
			$this->db->order_by($options['order']);
		  } else {
			$this->db->order_by('id_activity DESC');
		  }
		  
		  if($options['offset'] && $options['limit']){
			$this->db->limit($options['limit'], $options['offset']);
		  }else if($options['limit']){
			$this->db->limit($options['limit']);
		  }

    }
    
    if(strtoupper($select) == 'COUNT(*)'){
		  $result = $this->db->count_all_results();
	}else{
		  $result = $this->db->get()->result_array();
	}
    
	  return $result;
  }

}
