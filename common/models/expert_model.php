<?php
/**
 * 
 * @copyright(c) 2014-6-26
 * @author Jamai
 * @version 2.0
 */

class Expert_Model extends CI_Model
{
    protected $table = 'bn_expert';
    protected $table_user = 'bn_hipigo_user';

    public function __construct()
    {
        $this->load->database();
    }
    
  /**
   * 根据读取内容来读取内容
   * @param $options 
   *          order  排序
   *          offset 偏移量
   *          limit  读取条数
   * @version 2.0
   * @author Jamai
   * @return Array
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
      else 
        $this->db->order_by('created DESC');
      
      if($options['offset'] && $options['limit'])
        $this->db->limit($options['limit'], $options['offset']);
      else if($options['limit'])
        $this->db->limit($options['limit']);
    }
    
    if($row)
      $result = (Array) $this->db->get()->row();
    else
      $result = $this->db->get()->result_array();
    
    return $result;
  }
    
    /*
    * @author Jamai
    * 申请达人
    */
    public function insert_expert($data)
    {
      $re = $this->db->insert($this->table, $data);
      return $this->db->insert_id();
    }
    
    /*
    * @author Jamai
    * 查看单个达人申请是否有记录
    */
    public function expertBy($where)
    {
      $this->db->select('COUNT(*) as c')
          ->from($this->table);
      
      if( ! $where)
        $this->db->where($where);
      $return = $this->db->get()->result_array();
      return $return;
    }

    /**
     * zxx
     * 获取申请达人的用户是否已存在过的条数
     * @param $where
     * @return mixed
     */
    public function get_expert_count($where)
    {
        $this->db->where($where);
        $this->db->from($this->table);

        $result = $this->db->count_all_results();
//        debug($this->db->last_query());
        return $result;
    }

    /*
     * zxx
     * 获取达人表信息
     * */
    public function get_expert_info($where,$offset=0,$page=10,$order='id_expert desc')
    {
        $this->db->select('e.*,hu.sex,hu.head_image_url,hu.sign')
            ->from($this->table . ' as e')
            ->join($this->table_user." as hu","e.id_user=hu.id_user","left")
            ->where($where);

        if($offset){
            $this->db->limit($page,($offset-1)*$page);
        }
        if( $order ){
            $this->db->order_by($order);
        }
        $result = $this->db->get()->result_array();
        return $result;
    }
}
