<?php
/**
 * 
 * @copyright(c) 2013-12-4
 * @author zxx
 * @version Id:review_Model.php
 */

class Review_Model extends CI_Model
{
    protected $table = 'bn_review';
    protected $table_sub = 'bn_business_subscribe';
    protected $table_user = 'bn_hipigo_user';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * 获取最新一条评论信息
     *
     **/
    public function byNewReview($where)
    {
      $this->db->select('id_customer, content')
            ->from($this->table)
            ->where($where)
            ->order_by('id_review DESC')
            ->limit(1);
      
      return $this->db->get()->row();
    }
    

    /*
     * zxx 获取评论信息
     * **/
	public function get_review_info($where,$offset = 0, $page = 20, $order='',$type=1)
    {
        if($type == 2){
            $this->db->select('*')
                ->from($this->table . ' as r');
        }else{
            $this->db->select('r.id_review,r.id_object,r.name,r.content,r.created,r.image_url,r.id_open,r.id_customer,hu.head_image_url')
                ->from($this->table . ' as r')
                ->join($this->table_user." as hu","r.id_customer=hu.id_user","left");
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
     * zxx 获取评论条数
     * **/
    public function get_review_count($where,$type=1)
    {
        $this->db->where($where);
        $this->db->from($this->table ." as r");
        if($type != 2){
            $this->db->join($this->table_user." as hu","r.id_customer=hu.id_user","left");
        }
        $result = $this->db->count_all_results();
//        debug($this->db->last_query());
        return $result;
    }


    /*
      * zxx
      * 删除一条评论信息的分类,即state状态改变  （状态,default:1有效,0:删除）
      */
    public function delete_review($where){
        $re = $this->db->update($this->table, array('state'=>0),$where);
//        $re = $this->db->delete($this->table, array('id_object' => $id_commodity,'object_type'=>'commodity'));
        return $re;
    }

    /*
     * zxx
     * 添加一条回复信息
     */
    public function insert_reply($data){
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }


    /*
     * zxx
     * 更新一条回复信息
     */
    public function update_reply($data,$where){
        $re = $this->db->update($this->table, $data,$where);
        return $re;
    }

}



/* End of file user_model.php */