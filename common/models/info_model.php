<?php
/**
 * 
 * @copyright(c) 2013-12-26
 * @author zxx
 * @version Id:info_Model.php
 */

class Info_Model extends CI_Model
{
    protected $table = 'bn_info';
    protected $table_class = 'bn_commodity_class';
    protected $table_attachment = 'bn_info_attachment';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /*
     * zxx 获取内容（列表）信息
     */
    public function get_info_introduction($where,$offset = 0, $page = 20, $order='',$type=1)
    {
        $this->db->select('*')
        ->from($this->table.' as i');
        if($type == 1){
            $this->db->join($this->table_class." as cc","i.id_class=cc.id_class","left");
        }else{
            $this->db->join($this->table_attachment." as ia","i.id_info=ia.id_info","left");
        }

        if($where != 1){
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

    /**
     * @author zhoushuai
     * @商家信息分类列表
     */
    public function get_info_list($where,$offset = 0, $page = 20, $order='')
    {
        $this->db->select('distinct(i.id_info),i.id_business,i.id_shop,i.title,i.created,ia.show_url,i.content')->from($this->table.' as i');
        //$this->db->join($this->table_class." as cc","i.id_class=cc.id_class","left");
        $this->db->join($this->table_attachment." as ia","i.id_info=ia.id_info","left");
        if($where != 1){
            $this->db->where($where);
        }
        if($offset){
            $this->db->limit($page,($offset-1)*$page);
        }
        if( $order ){
            $this->db->order_by($order);
        }
        $this->db->group_by('i.id_info');
        $result = $this->db->get()->result_array();
        //debug($this->db->last_query());
        return $result;
    }



    /*
     * zxx 获取内容信息总条数
     * **/
    public function get_info_count($where){
        $this->db->where($where);
        $this->db->from($this->table . ' as i');
        return $this->db->count_all_results();
    }

    /*
     * zxx 获取内容类型  通过总店id
     * **/
    public function get_info_class($where)
    {
        $this->db->select('id_class,id_business,name')
            ->from($this->table_class)
            ->where($where);
        $result = $this->db->get()->result_array();
        return $result;
    }

    /*
     * zxx
     * 添加一条内容数据
     */
    public function insert_info($data){
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /*
     * zxx
     * 更新一条内容信息
     */
        public function update_info($data,$where){
            $re = $this->db->update($this->table, $data,$where);
            return $re;
        }

    /*
      * zxx
      * 删除一条内容信息的分类
      */
    public function delete_info($where){
        $re = $this->db->delete($this->table, $where);
        return $re;
    }

    /*
     * zxx
     * 添加一条内容附件数据
     */
    public function insert_info_attachment($data){
        $this->db->insert($this->table_attachment, $data);
        return $this->db->insert_id();
    }

    /*
     * zxx
     * 更新一条内容附件信息
     */
    public function update_info_attachment($data,$where){
        $re = $this->db->update($this->table_attachment, $data,$where);
        return $re;
    }

    /*
      * zxx
      * 删除一条内容附件信息
      */
    public function delete_info_attachment($where){
        $re = $this->db->delete($this->table_attachment, $where);
        return $re;
    }


    /*
     * zxx 获取内容附件信息  通过内容id
     * **/
    public function get_info_attachment($where,$limit=0){
        $this->db->select('*')
            ->from($this->table_attachment)
            ->where($where);

        if($limit){
            $this->db->limit($limit);
        }
        $result = $this->db->get()->result_array();
//        debug($this->db->last_query());
        return $result;
    }
    
            //select `name` as title from bn_wx_menu where id_business=1 and object_type='view' and object_value='home/class_list?r=info&c=7'
    public function get_list_title($where){
        $this->db->select('name as title')->from('bn_wx_menu')->where($where);
        $result = $this->db->get()->result_array();
        //debug($this->db->last_query());
        $return = !empty($result) ? $result[0]['title'] : FALSE;
        return $return;
    }

}



/* End of file user_model.php */