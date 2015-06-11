<?php
/**
 * 
 * @copyright(c) 2014-8-26
 * @author zxx
 * @version 2.1
 */

class Communitytag_Model extends CI_Model
{
    protected $table = 'bn_community_activity_tag';
    protected $table_tag = 'bn_tag';

    public function __construct()
    {
        $this->load->database();
    }

    /*
     * zxx 获取活动标签
     */
    public function get_tag($where,$offset = 0, $page = 20, $order='')
    {
        $this->db->select('ca.*,t.tag_name')
            ->from($this->table . ' as ca')
            ->join($this->table_tag." as t","ca.id_tag=t.id_tag","left")
            ->where($where);

        if($offset){
            $this->db->limit($page,($offset-1)*$page);
        }
        if( $order ){
            $this->db->order_by($order);
        }
        $result = $this->db->get()->result_array();
//        debug($this->db->last_query());
        return $result;
    }

    /*
    * @author zxx
    * 增加标签
    */
    public function insert_activity_tag($data)
    {
      $re = $this->db->insert($this->table, $data);
      return $this->db->insert_id();
    }
    /*
     * zxx
     * 更新一条标签信息
     */
    public function update_activity_tag($data,$where){
        $re = $this->db->update($this->table, $data,$where);
        return $re;
    }

    /*
      * zxx
      * 删除一条标签
      */
    public function delete_activity_tag($where){
        $re = $this->db->delete($this->table, $where);
        return $re;
    }

}
