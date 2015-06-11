<?php
/**
 * 
 * @copyright(c) 2014-12-12
 * @author zxx
 * @version Id:ad_Model.php
 */

class Ad_Model extends CI_Model
{
    protected $table = 'bn_ad';
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /*
     * zxx
     * 获取商家在线预约专线广告信息
     */
    public function get_ad($field = '*',$where,$offset = 0, $page = 20, $order='')
    {
        $this->db->select($field)
            ->from($this->table);
        if($where){
            $this->db->where($where);
        }
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
    * zxx
    * 插入商家在线预约专线广告信息
    * */
    public function insert_ad($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id;
    }

    /*
     * zxx
     * 更新商家在线预约专线广告信息
     */
    public function update_ad($data,$where){
        $re = false;
        if($data){
            $re = $this->db->update($this->table, $data, $where);
        }
        return $re;
    }

    /*
    * zxx
    * 删除商家在线预约专线广告信息
    */
    public function delete_ad($where)
    {
        $re = $this->db->delete($this->table, $where);
        return $re;
    }

}



/* End of file user_model.php */