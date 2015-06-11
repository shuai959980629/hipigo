<?php
/**
 * 
 * @copyright(c) 2013-12-20
 * @author msi
 * @version Id:customer_model.php
 */

class Wxreply_Model extends CI_Model{
	
	protected $table = 'bn_msg_reply';
	protected $table_config = 'bn_business_config';
	protected $table_attach = 'bn_msg_reply_attachment';
	
	public function __construct(){
		parent::__construct();
		$this->load->database();
	}
	
	//记录微信发送的文本信息
	public function add_wx_msg($add_data){
		$this->db->insert($this->table,$add_data);
		return $this->db->insert_id();
	}
	
	
	//根据条件获取微信配置
	public function get_biz_wxseting($where,$type=0,$start=0,$limit=0,$order = 'id_msg desc'){
		$this->db->select('*');
		$this->db->from($this->table);
		if($type==1){
			$this->db->where($where);
		}else{
			$this->db->where($where,null,false);
		}
		if($limit > 0){
			$this->db->limit($limit,$start);
		}
        $this->db->order_by($order);
		$result = $this->db->get()->result_array();
		//debug($this->db->last_query());exit;
		return $result;
	}
	
	//更新微信配置
	public function update_wx_msg($where,$update_data){
		$this->db->where($where);
		$result = $this->db->update($this->table,$update_data);
		return $result;
	}
	
	//根据条件获取附件
	public function get_msg_attach($where,$limit=25){
		$query = $this->db->get_where($this->table_attach,$where,$limit);
		$result = $query->result_array();
		return $result;
	}
	
	//根据id获取物品
	public function get_commodity_inid($in_array,$type=1){
		$this->db->select('id_commodity as id,name as title,descript as description,image_url as image');
		$this->db->from('bn_commodity');
		$this->db->where(array('state'=>1));
        if($type == 2){
            $this->db->where($in_array);
        }else{
            $this->db->where_in('id_commodity',$in_array);
        }
        $this->db->order_by('weight DESC');
		$result = $this->db->get()->result_array();
		return $result;
	}
	
	//根据id获取文章
	public function get_info_inid($in_array,$type=1){
		$this->db->select('a.id_info as id,a.title,a.content as description,b.show_url as image');
		$this->db->from('bn_info a');
		$this->db->join('bn_info_attachment b','a.id_info=b.id_info','left');
		$this->db->where(array('a.state'=>1));
        if($type === 1){
            $this->db->where_in('a.id_info',$in_array);
            $this->db->where(array('b.type'=>'image'));
        }else{
            $this->db->where($in_array);
        }
		$this->db->where("b.show_url<>''",null,false);
        $this->db->order_by('weight DESC');
		$this->db->group_by('a.id_info');
		$result = $this->db->get()->result_array();
		return $result;
	}
	
	
	//根据id获取活动
	public function get_activity_inid($in_array,$type=1){
		$this->db->select('id_activity as id,name as title,content as description,image_url as image');
		$this->db->from('bn_merchant_activity');
		$this->db->where(array('state'=>1));
        if($type == 2){
            $this->db->where($in_array);
        }else{
		    $this->db->where_in('id_activity',$in_array);
        }
        $this->db->order_by('weight DESC');
		$result = $this->db->get()->result_array();
//        debug($this->db->last_query());
		return $result;
	}
	
	
	//根据id获取活动
	public function get_activity_inid_kill($in_array){
		$this->db->select('id_activity as id,name as title,content as description,posters_url as image');
		$this->db->from('bn_community_activity');
		$this->db->where(array('state'=>2));
		$this->db->where_in('id_activity',$in_array);
		$result = $this->db->get()->result_array();
//        debug($this->db->last_query());
		return $result;
	}

	public function get_wxreplay_count($where){
		$this->db->from($this->table .' a');
		$this->db->where($where);
		$count = $this->db->count_all_results();
		return $count;
	}

    /*
      * zxx
      * 删除一条回复信息
      */
    public function delete_replay($where){
        $re1 = $this->db->delete($this->table, $where);
        $re = $this->db->delete($this->table_attach, $where);
        return $re;
    }
	
	//搜索物品标题
	public function get_cmd_like_title($where,$like,$start,$limit){
		$this->db->select('id_commodity as id,name as title,descript as description,image_url as image');
		$this->db->from('bn_commodity');
		$this->db->where(array('state'=>1));
		$this->db->where($where);
		if($like){
			$this->db->like($like);
		}
		$this->db->order_by('weight DESC,created DESC');
		if($limit > 0){
			$this->db->limit($limit,$start);
		}
		$result = $this->db->get()->result_array();
		return $result;
	}
	
	//根据id获取文章
	public function get_info_like_title($where,$like,$start,$limit){
		$this->db->select('a.id_info as id,a.title,a.content as description,b.show_url as image');
		$this->db->from('bn_info a');
		$this->db->join('bn_info_attachment b','a.id_info=b.id_info','left');
		$this->db->where(array('a.state'=>1));
		$this->db->where($where);
		if($like){
			$this->db->like($like);
		}
		$this->db->order_by('a.weight DESC,a.created DESC');
		$this->db->where(array('b.type'=>'image'));
		$this->db->where("b.show_url<>''",null,false);
		$this->db->group_by('a.id_info');
		if($limit > 0){
			$this->db->limit($limit,$start);
		}
		$result = $this->db->get()->result_array();
		return $result;
	}
	
	
	//根据id获取活动
	public function get_activity_like_title($where,$like,$start,$limit){
		$this->db->select('id_activity as id,name as title,content as description,image_url as image');
		$this->db->from('bn_merchant_activity');
		$this->db->where(array('state'=>1));
		$this->db->where($where);
		if($like){
			$this->db->like($like);
		}
		$this->db->order_by('weight DESC,created DESC');
		if($limit > 0){
			$this->db->limit($limit,$start);
		}
		$result = $this->db->get()->result_array();
		return $result;
	}


	//根据id获取活动
	public function get_activity_like_title_kill($where,$like,$start,$limit){
		$this->db->select('name as title,id_activity as id,content as description,posters_url as image');
		$this->db->from('bn_community_activity');
		$this->db->where(array('state'=>2));
		$this->db->where($where);
		$this->db->order_by('created DESC');
		if($limit > 0){
			$this->db->limit($limit,$start);
		}

		$result = $this->db->get()->result_array();
		return $result;
	}

	//根据id获取活动
	public function get_activity_like_title_kill_m($where){
		$this->db->select('reply as title,id_msg as id');
		$this->db->from($this->table);
		$this->db->where($where);

		$result = $this->db->get()->result_array();
		return $result;
	}

	
	//批量添加附件
	public function add_bath_attach($add_data){
		return $this->db->insert_batch($this->table_attach, $add_data);
	}
	
	
	//删除附件
	public function delete_reply_attachment($where){
		$this->db->delete($this->table_attach,$where);
		return true;
	}
	
	
	//记录用户关注的信息
	public function add_wx_info($add_data){
		$this->db->insert('bn_business_subscribe',$add_data);
		return $this->db->insert_id();
	}
	
	//记录用户关注的信息
	public function edit_wx_info($update,$where){
		$this->db->where($where);
		$result = $this->db->update('bn_business_subscribe',$update);
		//debug($this->db->last_query()).'<br />';
		return $result;
	}
	
	
	public function delete_wx_info($where){
		$result = $this->db->delete('bn_business_subscribe',$where);
		return $result;
	}
	
	public function get_null_openid(){
		$result = $this->db->get_where('bn_business_subscribe',array('nick_name'=>''))->result_array();
		return $result;
	}


    //更新附件
    public function update_reply_attachment($data,$where){
        $this->db->where($where);
        $result = $this->db->update($this->table_attach,$data);
        return true;
    }

    //添加附件
    public function insert_attachment($data){
        $this->db->insert($this->table_attach,$data);
        return $this->db->insert_id();
    }

}



/* End of file customer_model.php */