<?php
/**
 * 
 * @copyright(c) 2014-6-26
 * @author Jamai
 * @version 2.0
 */

class Tag_Model extends CI_Model
{
    protected $table = 'bn_tag';

    public function __construct()
    {
        $this->load->database();
    }

    /*
    * zxx
     * 判断搜索关键字是否存在
    * **/
    public function get_tag($where='',$offset=0,$page=25,$order='is_level asc,search_num asc')
    {
        $this->db->select('*')
            ->from($this->table);

        if($where != ''){
            $this->db->where($where);
        }
        if($offset){
            $this->db->limit($page,($offset-1)*$page);
        }
        if( $order ){
            $this->db->order_by($order);
        }
        $result = $this->db->get()->result_array();
        return $result;
    }

    /*
    * @author Jamai
    * 添加tag
    */
    public function insert_tag($data)
    {
      $re = $this->db->insert($this->table, $data);
      return $this->db->insert_id();
    }

    /*
     * @author Jamai
     * 更新tag
     */
    public function update_tag($data, $where)
    {
        $re = $this->db->update($this->table, $data, $where);
        return $re;
    }
    
}
