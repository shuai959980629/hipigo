<?php
/**
 * 
 * @copyright(c) 2013-11-20
 * @author zxx
 * @version Id:Shop_Model.php
 */

class Wxmenu_Model extends CI_Model
{
    protected $table = 'bn_wx_menu';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_wxmenu_id($where)
    {
        $this->db->select('id_shop_menu')
            ->from($this->table)
            ->where($where)
        	->limit(1);
        $result = $this->db->get()->row_array();
        return $result;
    }


}



/* End of file user_model.php */