<?php
/**
 * 资源库管理 模型层
 * @copyright(c) 2014-06-24
 * @author Jamai
 * @version 2.0
 */

class Resources_Model extends CI_Model
{
    protected $table = 'bn_resources';
    protected $relate_table = 'bn_resources_by';
    protected $table_item = 'bn_eticket_item';
    protected $table_bus = 'bn_business';

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
  public function byFieldSelect($fields = array(), $where = array(), $options = array(), $row = false, $table = 'default')
  {
    if( ! $fields)
      $fields = '*';
    
    $this->db->select($fields);
    
    if($table !== 'default') 
      $this->db->from($this->relate_table);
    else 
      $this->db->from($this->table);
    
              
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
    
    /**
     * 获取我的资源
     *
     * @version 2.0
     * @author Jamai
     */
    public function get_by_resources($where, $order = 'relate.created DESC', $offset = 0, $limit = 25)
    {
      $this->db->select('*, relate.num AS num, relate.created AS mycreated')
          ->from($this->relate_table . ' AS relate')
          ->join($this->table . ' AS re', 're.id_resource = relate.id_resource')
          ->where($where)
          ->order_by($order);
      
      if($offset)
        $this->db->limit($limit, ($offset - 1) * $limit);

      $result = $this->db->get()->result_array();
//        echo $this->db->last_query();
      return $result;
    }
    
    public function count_by_resources($where)
    {
      $this->db->select('COUNT(*) AS c')
          ->from($this->relate_table . ' AS relate')
          ->join($this->table . ' AS re', 're.id_resource = relate.id_resource')
          ->where($where);
      
      $result = $this->db->get()->result_array();
      return $result[0]['c'];
    }
    
    /**
     * 更新指定资源
     *
     * @version 2.0
     * @author Jamai
     */
    public function update_by_resource($data, $where)
    {
      $this->db->where($where);
      
      foreach ($data as $key => $value) {
        $this->db->set($key, $value, false);
      }
      $result = $this->db->update($this->relate_table);
      return $result;
    }
    
    /**
     * 添加 By 资源
     *
     * @version 2.0
     * @author Jamai
     */
    public function insert_by_resource($data)
    {
      $this->db->insert($this->relate_table, $data);
      return $this->db->insert_id();
    }
    
    /**
     * 删除指定资源
     *
     * @version 2.0
     * @author Jamai
     */
    public function del_resource($id) 
    {
      $re = $this->db->delete($this->table, 
        array('id_resource' => $id));
      return $re;
    }
    
    /**
     * 更新指定资源
     *
     * @version 2.0
     * @author Jamai
     */
    public function update_resource($data, $where, $blog = false)
    {
      $this->db->where($where);
      foreach ($data as $key => $value) {
        if($blog !== false)
          $this->db->set($key, $value);
        else
          $this->db->set($key, $value, false);
      }
      $result = $this->db->update($this->table);
      return $result;
    }
    
    /**
     * 获取资源库资源
     *
     * @version 2.0
     * @author Jamai
     */
    public function get_resources($where, $order = 'created DESC' ,$offset = 0, $page = 25, $like = '')
    {
      $this->db->select('*')
        ->from($this->table . ' as r')
        ->where($where)
        ->join($this->table_bus . ' AS b', 'r.owner = b.id_business')
        ->order_by($order);
      
      if($offset){
        $this->db->limit($page, ($offset - 1) * $page);
      }
      if($like !== '')
        $this->db->like('resource_title', $like);
        
      $result = $this->db->get()->result_array();
//         debug($this->db->last_query());
      return $result;
    }


    /*
     * zxx 获取资源库总条数
     */
    public function get_resources_count($where, $like = ''){
        $this->db->where($where);
        $this->db->from($this->table );

        if($like !== '')
            $this->db->like('resource_title', $like);

        $return = $this->db->count_all_results();
//        debug($this->db->last_query());
        return $return;
    }

    public function count_resources($where, $like = '')
    {
      $this->db->select('COUNT(*) AS c')
        ->from($this->table)
        ->where($where);
      
      if($like !== '')
        $this->db->like('resource_title', $like);
      
      $result = $this->db->get()->result_array();

      return $result[0]['c'];
    }
    
    
    /**
     * 获取资源库资源
     *
     * @version 2.0
     * @author Jamai
     */
    public function get_resource_info($idResource)
    {
      $this->db->select('*')
        ->from($this->table)
        ->where(array('id_resource' => $idResource))
        ->limit(1);
      $result = $this->db->get()->result_array();
      return $result[0];
    }
    
    /**
     * 获取推荐资源
     *
     * @version 2.0
     * @author Jamai
     */
    public function get_level($limit = 10,$offset=0)
    {
      $this->db->select('*')
        ->from($this->table)
        ->where(array('is_level' => 1))
        ->where('(num > 0 or num <=-1) AND deleted = 1')
        ->order_by('created desc')
        ->limit($limit, $offset);
      $result = $this->db->get()->result_array();
      return $result;
    }

    /*
     * zxx
     * 添加一条资源管理信息  返回插入数据的id
     */
    public function insert_resource($data){
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }


    /*
     * zxx
     * 更新我的资源
     */
    public function update_resource_by($data,$where){
        $re = $this->db->update($this->relate_table, $data,$where);
//        debug($this->db->last_query());
        return $re;
    }
}