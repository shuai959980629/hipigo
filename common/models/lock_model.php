<?php
/**
 * 
 * @copyright(c) 2014-5-23
 * @author zxx
 * @version Id:lock_Model.php
 */

class Lock_Model extends CI_Model
{
    protected $table = 'bn_lock';

    public function __construct()
    {
        $this->load->database();
    }

    /*
    * zxx
     * 获取锁定信息
    * **/
    public function get_lock($where)
    {
        $this->db->select('id_lock,object_type,id_object,lock_phone,created')
            ->from($this->table)
            ->where($where)
            ->order_by('created', 'desc');
        $result = $this->db->get()->result_array();
        return $result;
    }

    /*
    * zxx
    * 删除锁定信息
    */
    public function delete_lock($where)
    {
        $re = $this->db->delete($this->table, $where);
        return $re;
    }


}


/* End of file user_model.php */
