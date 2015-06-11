<?php
/**
 * 
 * @copyright(c) 2014-04-21
 * @author zxx
 * @version Id:Community_Model.php
 */

class Communityjoin_Model extends CI_Model
{
  protected $table = 'bn_community_activity_join';

  public function __construct()
  {
    parent::__construct();
    $this->load->database();
  }
  
  public function byFieldSelect($fields = array(), $where = array(), $options = array(), $row = false)
  {
    if( ! $fields)
      $fields = '*';
    
    $this->db->select($fields)
              ->from($this->table);
    if($where)
      $this->db->where($where);
    
    if($options === true) {
      $row = true;
    }
    else {
      if($options) {
        if($options['order'])
          $this->db->order_by($options['order']);
        else 
          $this->db->order_by('id_activity DESC');
        
        if($options['offset'] && $options['limit'])
          $this->db->limit($options['limit'], $options['offset']);
        else if($options['limit'])
          $this->db->limit($options['limit']);
      }
    }
    
    if($row)
      $result = (Array) $this->db->get()->row();
    else
      $result = $this->db->get()->result_array();
    
    return $result;
  }
  
 
}
