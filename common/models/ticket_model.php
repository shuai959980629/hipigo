<?php
/**
 * 
 * @copyright(c) 2014-3-26
 * @author zxx
 * @version Id:ticket_Model.php
 */

class Ticket_Model extends CI_Model
{
    protected $table = 'bn_eticket';
    protected $table_item = 'bn_eticket_item';
    protected $table_verify = 'bn_eticket_verify_business';
    protected $table_user = 'bn_business_subscribe';
    protected $table_activity_config = 'bn_merchant_activity_config';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /*
     * zxx 获取电子券（列表）信息
     */
    public function get_ticket_introduction($where,$offset = 0, $page = 20, $order='')
    {
        $this->db->select('*')
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
     * zxx 获取电子券信息总条数
     * **/
    public function get_ticket_count($where){
        if($where){
            $this->db->where($where);
        }
        $this->db->from($this->table);
        $return = $this->db->count_all_results();
        return $return;
    }

    /*
     * zxx
     * 添加一条电子券信息  返回插入数据的id
     */
    public function insert_ticket($data){
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /*
     * zxx
     * 更新一条电子券信息
     */
    public function update_ticket($data,$where){
        $re = $this->db->update($this->table, $data,$where);
        return $re;
    }

    /*
     * zxx
     * 更新一条电子券使用数量信息
     */
    public function update_use_quantity($where){
        $sql = 'update bn_eticket set use_quantity=use_quantity+1 where ' . $where;
        $query = $this->db->query($sql);
        return $query;
    }

    /*
      * zxx
      * 删除一条电子券信息
      */
    public function delete_ticket($where){
        $re = $this->db->delete($this->table, $where);
        return $re;
    }

    /*
     * zxx 获取电子券明细
     */
    public function get_ticket_item($where,$offset = 0, $page = 20,$type = 1, $order='ti.id_item desc')
    {
        $this->db->select('ti.code,ti.get_time,ti.use_time,ti.state,ti.id_open,ti.id_customer,ti.object_type,ti.id_object')
            ->from($this->table_item .' as ti');
        if($type == 1){
            $this->db->join($this->table." as t","ti.id_object=t.id_eticket","left");
        }

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
     * zxx 获取电子券明细总条数
     * **/
    public function get_ticket_item_count($where,$type=1){
        if($where){
            $this->db->where($where);
        }
        $this->db->from($this->table_item .' as ti');
        if($type == 1){
            $this->db->join($this->table." as t","ti.id_object=t.id_eticket","left");
        }
        $return = $this->db->count_all_results();
//        debug($this->db->last_query());
//        file_put_contents(DOCUMENT_ROOT.'/123.txt',var_export($this->db->last_query(),TRUE));
        return $return;
    }

    /*
     * zxx
     * 添加一条电子券明细
     */
    public function insert_ticket_item($data){
        $this->db->insert($this->table_item, $data);
        return $this->db->insert_id();
    }

    /*
     * zxx
     * 更新一条电子券明细
     */
    public function update_ticket_item($data,$where){
        $re = $this->db->update($this->table_item, $data,$where);
        return $re;
    }

    /*
      * zxx
      * 删除一条电子券明细
      */
    public function delete_ticket_item($where){
        $re = $this->db->delete($this->table_item, $where);
        return $re;
    }

    /*
     * zxx 获取电子券验证信息
     */
    public function get_ticket_verify($where,$offset = 0, $page = 20, $order='ti.id_item desc',$type = 0)
    {
        //t.id_shop,t.id_business,t.id_eticket,t.name,t.image,t.valid_begin,t.valid_end,
        $this->db->select('ti.id_business,ti.id_shop,ti.id_object,ti.object_type,ti.id_item,ti.code,ti.id_open,ti.id_customer,ti.get_time,ti.use_time,ti.state')//,bs.nick_name
            ->from($this->table_item.' as ti')
            ->where($where);
//            ->join($this->table." as t","t.id_eticket=ti.id_object","left")
//            ->join($this->table_user." as bs","ti.id_open=bs.id_open","left");

        if($type == 1){
            $this->db->where('ti.id_object IN(SELECT id_resource from bn_resources_by)');
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
     * zxx 获取电子券验证总条数
     * **/
    public function get_ticket_verify_count($where){
        if($where){
            $this->db->where($where);
        }
//        $this->db->join($this->table." as t","t.id_eticket=ti.id_object","left");
//        $this->db->join($this->table_user." as bs","ti.id_open=bs.id_open","left");

        $this->db->from($this->table_item .' as ti');
        $return = $this->db->count_all_results();
        return $return;
    }


    /*
     * zxx 获取电子券验证商家信息
     */
    public function get_ticket_verify_business($where,$offset = 0, $page = 20, $order='id_ver_biz desc')
    {
        $this->db->select('*')
            ->from($this->table_verify)
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
     * zxx
     * 添加一条电子券验证商家信息
     */
    public function insert_ticket_verify($data){
        $this->db->insert($this->table_verify, $data);
        return $this->db->insert_id();
    }

    /*
     * zxx
     * 更新一条电子券验证商家信息
     */
    public function update_ticket_verify($data,$where){
        $re = $this->db->update($this->table_verify, $data,$where);
        return $re;
    }


    /*
      * zxx
      * 删除一条电子券明细
      */
    public function delete_ticket_verify($where){
        $re = $this->db->delete($this->table_verify, $where);
        return $re;
    }

}



/* End of file user_model.php */