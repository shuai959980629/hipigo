<?php
/**
 * 
 * @copyright(c) 2014-3-26
 * @author zxx
 * @version Id:Sysconfig_Model.php
 */

class Sysconfig_Model extends CI_Model
{
    protected $table = 'bn_sys_config';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /*
     * zxx
     * 获取个人资料背景图片信息
     */
    public function get_sysconfig($where='')
    {
        $this->db->select('*')
            ->from($this->table);

        if($where){
            $this->db->where($where);
        }

        $result = $this->db->get()->result_array();
        return $result;
    }

}



/* End of file user_model.php */