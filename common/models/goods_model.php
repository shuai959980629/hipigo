<?php
/**
 * 
 * @copyright(c) 2013-11-27
 * @author msi
 * @version Id:goods.php
 */

class Goods_Model extends CI_Model
{
	
	private $table = 'bn_mall';
    private $table_review = 'bn_review';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
	
	/**
	 * 获取商品总数
	 * @param array $where
	 * @return number
	 */
	public function get_goods_count($where = array()){
		
		$query = $this->db->select('count(1) as cnt')->from($this->table);
		if(!empty($where)){
			$query->where($where);
		}
		$result = $query->get()->row_array();
		if(!empty($result['cnt'])){
			return $result['cnt'];
		}
		return 0;
	}
	
	
	/**
	 * 获取商品列表
	 * @param int $start
	 * @param int $limit
	 * @param array $where
	 * @return array
	 */
	public function get_goods_list($start,$limit,$where){
		
		$query = $this->db->select("a.id_mall,a.id_business,a.id_shop,a.id_commodity,a.quantity,a.integral,a.state,a.recommend,a.created,b.name,b.image_url");
		if(!empty($where)){
			$query->where($where);
		}
		$query->from($this->table.' as a');
		$query->join('bn_commodity as b','a.id_commodity=b.id_commodity','left');
		$query->limit($limit,$start);
		
		$result = $query->get()->result_array();
		return $result;
		
	}
	
	
	/**
	 * 删除商品
	 * @param array $where
	 * @return boolean
	 */
	public function del_goods($where){
		if(!empty($where)){
			$this->db->where($where);
			return $this->db->delete($this->table);
		}
		return FALSE;
	}
	
	
	/**
	 * 编辑商品的信息
	 * @param array $save_data
	 * @param array $where
	 * @return boolean
	 */
	public function edit_goods($save_data,$where){
		
		if(!empty($save_data) && !empty($where)){
			$this->db->where($where);
			return $this->db->update($this->table,$save_data);
		}
		return FALSE;
		
	}
	
	
	/**
	 * 根据条件获取某个特定商品
	 * @param array $where
	 * @return boolean
	 */
	public function get_goods($where){
		
		if(!empty($where)){
			$query = $this->db->get_where($this->table,$where);
			$result = $query->row_array();
			return $result;
		}
		return FALSE;
	}
	
	
	
	public function get_review_count_by_cdn($where){
		$query = $this->db->select('count(1) as cnt')->from($this->table_review);
		if(!empty($where)){
			$query->where($where);
		}
		$result = $query->get()->row_array();
		if(!empty($result['cnt'])){
			return $result['cnt'];
		}
		return 0;
	}
	
	
	
	public function get_review_list_by_cdn($where,$start,$limit){
		
		$query = $this->db->get_where($this->table_review,$where,$limit,$start);
		return $query->result_array();
		
	}
	
	
	public function del_review($where){
		if(!empty($where)){
			$this->db->where($where);
			return $this->db->delete($this->table_review);
		}
		return FALSE;
	}


    public function add_review($data)
    {
        return $this->db->insert($this->table_review, $data);
    }

    /*
     * zxx
     * 逻辑删除评论
     * */
    public function delete_review_state($data,$where){
        $re = $this->db->update($this->table_review, $data,$where);
        return $re;
    }

}



/* End of file goods.php */