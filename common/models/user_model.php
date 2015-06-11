<?php
/**
 * 
 * @copyright(c) 2013-11-20
 * @author msi
 * @version Id:user_model.php
 */

class User_Model extends CI_Model
{
	private $table_new = 'bn_hipigo_user';
	private $table = 'hipigo_user';
	private $error;//错误信息
	
	public function __construct(){
		parent::__construct();
		 $this->load->database();
	}
	
	
	/**
	 * 根据用户名获取用户信息
	 * @param string $username
	 * @return array
	 */
	public function get_userinfo_by_username_or_email_or_phone($username){
		$query = $this->db->query("SELECT a.id_user,a.name,a.pass,a.pass_hash,a.status,b.cell_phone,a.mail FROM hipigo_user a LEFT JOIN hipigo_user_contact b on a.id_user=b.id_user WHERE (a.mail='$username') OR (b.cell_phone='$username') OR (a.name='$username')");
		$result = $query->result_array();
		return $result;
		
	}
	

	/**
	 * 根据用户ID更新用户信息
	 * @param string $id_user
	 * @return array
	 */
	public function update_user_info($data,$where){
        $this->db->update($this->table_new, $data,$where);
	}


	/**
	 * 根据用户ID获取用户收入信息
	 * @param string $id_user
	 * @return array
	 */
	public function get_my_wallet($id_user,$num) 
  {
		$query = $this->db->query("SELECT * from bn_income_item where id_income_object=".$id_user." limit 0,".$num);
		$result = $query->result_array();
		return $result;
	}

	/**
	 * 根据用户ID获取用户收入信息
	 * @param string $id_order
	 * @return array
	 */
	public function get_my_order($id_order){
		$query = $this->db->query("SELECT * from bn_order where id_order=".$id_order);
		$result = $query->result_array();
		return $result;
	}

	/**
	 * 根据收入来源对象ID获取活动表信息
	 * @param string $id_source_type
	 * @return array
	 */
	public function get_my_source_activity($id_source_type){
		$query = $this->db->query("SELECT * from bn_order o,bn_community_activity y where y.id_activity = o.id_object and o.id_order =".$id_source_type);
		$result = $query->result_array();
		return $result;
	}

	/**
	 * 根据收入来源对象ID获取资源表信息
	 * @param string $id_source_type
	 * @return array
	 */
	public function get_my_source_resource($id_source_type){
		$query = $this->db->query("SELECT * from bn_order o,bn_resources y where y.id_resource = o.id_object and o.id_order =".$id_source_type);
		$result = $query->result_array();
		return $result;
	}

	public function get_userinfo_by_uid($uid){
		$query = $this->db->query("SELECT a.id_user,a.name,a.pass,a.pass_hash,a.status FROM hipigo_user a LEFT JOIN hipigo_user_contact b on a.id_user=b.id_user WHERE a.id_user=$uid");
		$result = $query->row_array();
		return $result;
	
	}
	
	/**
	 * 根据用户ID查询用户信息 
	 * @param string $id_user
	 * @return array
	 */
	public function get_userinfo_details($id_user){
		$query = $this->db->query("SELECT * FROM bn_hipigo_user  WHERE id_user=".$id_user);
		$result = $query->result_array();
		return $result;
	
	}
	
	
	/**
	 * 根据用户id获取商家信息
	 * @param unknown $uid
	 * @return array
	 */
	public function get_merchentinfo_by_uid($where){
		
		$result = array();
		$query = $this->db->select('a.id_shop,a.id_business,a.real_name,b.name as biz_name,b.introduction as intro,b.contact_number as tell,b.visit_mode,b.logo,b.sld as host')
		->from('bn_merchant_user a')
		->join('bn_business b','a.id_business=b.id_business','left')
		->where($where);
		$merchent = $query->get()->result_array();
		if(!empty($merchent[0])){
			$result = $merchent[0];
		}
		return $result;
		
	}
	
	
	public function get_shop_by_shopid($shop_id){
		
		$this->db->select("name as shop_name,introduction as shop_intro,image_url,contact,longitude as shop_long,latitude as shop_lat");
		$this->db->from('bn_shop');
		$this->db->where('id_shop',$shop_id);
		return $this->db->get()->row_array();
		
	}
	
	public function modify_passwd($data,$where){
		if(!empty($where)){
			$this->db->where($where);
			$this->db->update($this->table,$data);
		}
		return FALSE;
	}
	
	public function get_shop_list($where=''){
		
		$query = $this->db->get_where('bn_shop',$where);
		return $query->result_array();
		
	}
	
	public function check_menu_exist_by_uri($url){
		$query = $this->db->get_where('hipigo_right',array('object_type'=>'bn','menu_url'=>$url));
		return $query->result_array();
	}
	
	/**
	 * 根据域名查询商家logo
	 */
	public function get_logo_by_host($host){
		$query = $this->db->get_where('bn_business',array('sld'=>$host));
		return $query->row_array();
	}

    /*
    * zxx
    * 更新一条登录信息
    */
    public function update_merchant_user($data,$where){
        $re = $this->db->update('bn_merchant_user', $data,$where);
        return $re;
    }

}



/* End of file user_model.php */