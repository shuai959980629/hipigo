<?php
/**
 * 
 * @copyright(c) 2013-12-17
 * @author zxx
 * @version Id:Businessconfig_Model.php
 */

class Businessconfig_Model extends CI_Model
{
    protected $table = 'bn_business_config';

    public function __construct()
    {
        $this->load->database();
    }

    /*
     * zxx 商家的门店微信信息
     * **/
	public function get_business_config($where)
    {
        $this->db->select('id_business,id_shop,id_app as appid,app_secret as appsecret,token')
            ->from($this->table)
            ->where($where);
        $result = $this->db->get()->result_array();
//        debug($this->db->last_query());
		return $result;
	}




}



/* End of file user_model.php */