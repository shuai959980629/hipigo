<?php
/**
 * 
 * @copyright(c) 2013-12-23
 * @author zxx
 * @version Id:menu_Model.php
 */

class Menu_Model extends CI_Model
{
    protected $table = 'bn_wx_menu';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /*
     * zxx 获取微信菜单
     * **/
	public function get_menu($where,$limit)
    {
        $this->db->select('id_shop_menu,id_business,id_shop,name,object_type,object_value,parent_id')
            ->from($this->table)
            ->where($where)
            ->limit($limit);
        $result = $this->db->get()->result_array();
		return $result;
	}


}



/* End of file user_model.php */