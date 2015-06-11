<?php
/**
 * 
 * @copyright(c) 2013-11-27
 * @author zxx
 * @version Id:Mall_Model.php
 */

class Mall_Model extends CI_Model
{
    protected $table = 'bn_mall';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /*
     * zxx
     * 添加一条商城信息
     */
    public function insert_mall($data){
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /*
     * zxx
     * 更新一条商城信息 ，返回更新条数
     */
    public function update_mall($data,$where){
        $this->db->update($this->table, $data,$where);
        return mysql_affected_rows();
    }

    /*
     * zxx
     * 删除一条商城信息
     */
    public function delete_mall($where){
        $re = $this->db->delete($this->table, $where);
        return $re;
    }


    /*
     * zxx
     * 获取商城类型  通过物品id
     * **/
    public function get_mall_info($where)
    {
        $this->db->select('id_mall,quantity,integral,state,recommend')
            ->from($this->table . ' as c')
            ->where($where);
        $result = $this->db->get()->result_array();
        return $result;
    }

}



/* End of file user_model.php */