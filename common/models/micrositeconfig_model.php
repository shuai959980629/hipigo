<?php
/**
 * 
 * @copyright(c) 2013-12-20
 * @author msi
 * @version Id:micrositeconfig_model.php
 */

class Micrositeconfig_Model extends CI_Model{
	
	protected $table = 'bn_micro_site_config';
    protected $table_column = 'bn_micro_site_column';

	public function __construct(){
		$this->load->database();
	}

    /*
     * zxx
     * 获取首页分类配置
     * */
    public function get_site_config($where,$offset = 0, $page = 20, $order="weight desc,id_config desc")
    {
        $this->db->select('*')
            ->from($this->table)
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

    /*
     * zxx
     * 删除首页分类配置
     * */
    public function delete_site_config($where)
    {
        $re = $this->db->delete($this->table, $where);
        return $re;
    }



    /*
     * zxx
     * 添加一条首页分类配置
     */
    public function insert_site_config($data){
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /*
     * zxx
     * 更新一条首页分类配置
     */
    public function update_site_config($data,$where){
        $re = $this->db->update($this->table, $data,$where);
        return $re;
    }


    /*
     * zxx
     * 获取页面信息
     * */
    public function get_page_column($where,$offset = 0, $page = 20, $order="id_column desc")
    {
        $this->db->select('*')
            ->from($this->table_column)
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

    /*
     * zxx
     * 添加一条页面配置
     */
    public function insert_site_column($data){
        $this->db->insert($this->table_column, $data);
        return $this->db->insert_id();
    }

}
