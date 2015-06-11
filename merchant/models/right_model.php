<?php

class Right_model Extends CI_Model {

    protected $table = 'bn_merchant_menu';

    public function __construct()
    {
        $this->load->database();
    }

    
    //获取用户菜单
    public function get_user_menu($where)
    {
        $this->db->select('r.id_menu,r.name,r.id_parent,r.id_right,r.orders,r.url as menu_url')
            ->from($this->table.' AS r')
            ->join('hipigo_profile_right as pr','pr.id_right = r.id_right','left')
            ->join('bn_merchant_user as mu','mu.id_profile = pr.id_profile AND mu.id_business = r.id_business','left')
            ->where($where)
            ->order_by('r.orders ASC');
        
        $result = $this->db->get()->result_array();
        return $result;
    }

    //获取用户菜单
    public function get_user_right($where)
    {
        $this->db->select('r.name,r.id_parent,r.id_right,r.orders,r.menu_url')
            ->from('hipigo_right AS r')
            ->join('hipigo_profile_right as pr','pr.id_right = r.id_right','left')
            ->join('bn_merchant_user as mu','mu.id_profile = pr.id_profile','left')
            ->where($where)
            ->order_by('r.orders ASC');

        $result = $this->db->get()->result_array();
        return $result;
    }
    
    
    /**
     * 根据id获取菜单内容
     * @param int $id
     */
    public function get_menu_by_id($where){
    	 $query = $this->db->get_where($this->table,$where);
    	 return $query->row_array();
    }
    
    
    /**
     * 检测相应的id是否有权限
     * @param array $where_in
     */
    public function check_select_options_access($id,$where_in){
    	$this->db->select('*');
    	$this->db->from('bn_merchant_right');
    	$this->db->where(array('id_business'=>$id));
    	$this->db->where_in('id_right',$where_in);
    	$result = $this->db->get()->result_array();
    	return $result;
    }
    
}