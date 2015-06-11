<?php
/**
 * @author zxx
 * @结算账户表
 */
class Financeaccount_Model extends CI_Model
{
    public $table = 'bn_finance_account';


    public function __construct()
    {
        $this->load->database();
    }

    /*
    * zxx 获取结算账户信息
    * **/
    public function get_finance_account($where)
    {
        $this->db->select('*')
            ->from($this->table)
            ->where($where)
            ->order_by('id_finance_account', 'desc');
        $result = $this->db->get()->result_array();
        return $result;
    }


    /*
    * zxx
    * 插入结算账户信息
    * */
    public function insert_finance_account($data)
    {
       return $this->db->insert($this->table, $data);
    }



}

























?>