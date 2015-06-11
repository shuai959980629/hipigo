<?php
/**
 * 
 * @copyright(c) 2013-12-20
 * @author msi
 * @version Id:customer_model.php
 */

class Customer_Model extends CI_Model{
	
	protected $table = 'bn_customer';
	protected $table_replay = 'bn_customer_reply';
    protected $table_replay_attachment = 'bn_customer_reply_attachment';
    protected $table_msg = 'bn_customer_msg';
    protected $table_sd = 'bn_customer_sd';
	
	public function __construct(){
		parent::__construct();
		$this->load->database();
	}
	
	
	//根据条件查询会员卡
	public function get_member_card_by_openid($where){
		
		$query = $this->db->get_where($this->table,$where,1);
		$result = $query->result_array();
		return $result;
		
	}

    //查看用户是否注册 zxx
    public function get_customer_count($where){
        if($where){
            $this->db->where($where);
        }
        $this->db->from($this->table);
        $return = $this->db->count_all_results();
        return $return;
    }
	//检测会员卡是否重复
	public function member_card_able($num){
		$query = $this->db->select('count(1) as cnt')->from($this->table);
		$query->where(array('number'=>$num));
		$result = $query->get()->row_array();
		if(!empty($result['cnt'])){
			return $result['cnt'];
		}
		return 0;
	}
	
	//获取会员卡数量
	public function get_member_card_count($where){
		$query = $this->db->select('count(1) as cnt')->from($this->table.' a')
		->join('bn_business_subscribe b','a.id_open=b.id_open','left');
		$query->where($where);
		$result = $query->get()->row_array();
		//debug($this->db->last_query());
		if(!empty($result['cnt'])){
			return $result['cnt'];
		}
		return 0;
	}
	
	//获取会员卡列表
	public function get_membercard_list($where,$offset=0,$page = 20){
        $this->db->select('a.*,b.nick_name,b.name_remarks,b.id_bn_sub,b.city')
            ->from($this->table.' a')
            ->join('bn_business_subscribe b','a.id_open=b.id_open','left')
            ->where($where)
            ->order_by('id_customer','DESC');
        if($offset){
            $this->db->limit($page,($offset-1)*$page);
        }
        $result = $this->db->get()->result_array();
//    	debug($this->db->last_query());
        return $result;
	}
	
	//添加会员卡
	public function add_member_card($add_data){
		$this->db->insert($this->table,$add_data);
		return $this->db->insert_id();
	
	}

    /*
     * zxx
     * 更新一条会员信息
     */
    public function update_customer($data,$where){
        $re = $this->db->update($this->table, $data,$where);
        return $re;
    }

    /*
     * zxx
     * 添加一条圣地文化的会员附件信息
     */
    public function add_customer_sd($add_data){
        $this->db->insert($this->table_sd,$add_data);
        return $this->db->insert_id();

    }

    /*
     * zxx
     * 更新一条圣地文化的会员附件信息
     */
    public function update_customer_sd($data,$where){
        $re = $this->db->update($this->table_sd, $data,$where);
        return $re;
    }

    /*
     * zxx
     * 删除会员信息
     * */
    public function delete_customer($where){
        $re = $this->db->delete($this->table, $where);
        $re = $this->db->delete($this->table_sd, $where);
        return $re;
    }


	//记录微信发送的文本信息
	public function add_wx_msg($add_data){
		$this->db->insert('bn_customer_msg',$add_data);
		return $this->db->insert_id();
	}
	
	
	public function get_wx_info_by_openid($where){
		$query = $this->db->get_where('bn_business_subscribe',$where,1);
		$result = $query->row_array();
		return $result;
	}

    /*
     * zxx
     * 更新一条会员信息
     */
    public function update_nick_name($data,$where){
        $re = $this->db->update('bn_business_subscribe', $data,$where);
        return $re;
    }

    /*
     * zxx
     * 获取快速回复信息（列表）
     * */
    public function get_quick_reply($where,$offset = 0, $page = 20, $order="cm.id_customer_msg desc")
    {
        $this->db->select('cm.id_customer_msg,cm.msg_type,cm.id_business,cm.id_shop,cm.id_open,cm.msg_content,cm.created,bs.nick_name,bs.name_remarks,bs.id_bn_sub')
            ->from($this->table_msg .' as cm')
            ->where($where)
            ->join('bn_business_subscribe as bs','cm.id_open = bs.id_open','left');

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
     * zxx 获取快速回复信息总条数
     * **/
    public function quick_reply_count($where){
        $this->db->from($this->table_msg . ' as cm');
        $this->db->where($where);
        $this->db->join('bn_business_subscribe as bs','cm.id_open = bs.id_open','left');

        $return = $this->db->count_all_results();
//        debug($this->db->last_query());
        return $return;
    }


    /*
     * zxx 获取快速回复的回复信息总条数
     * **/
    public function customer_reply_count($where){
        $this->db->from($this->table_replay);
        $this->db->where($where);

        $return = $this->db->count_all_results();
//        debug($this->db->last_query());
        return $return;
    }


    /*
     * zxx
     * 删除快速回复列表信息
     * */
    public function delete_quick_msg($where){
        $re = $this->db->delete($this->table_msg, $where);
        return $re;
    }
    /*
       * zxx
       * 删除快速回复列表回复信息
       * */
    public function delete_quick_reply($where){
        $re = $this->db->delete($this->table_replay, $where);
        return $re;
    }
    /*
     * zxx
     * 删除快速回复列表回复信息附件
     * */
    public function delete_reply_attachment($where){
        $re = $this->db->delete($this->table_replay_attachment, $where);
        return $re;
    }

    /*
     * zxx
     * 增加一条快速回复信息
     * */

    //记录微信发送的文本信息
    public function insert_quick_reply($data){
        $this->db->insert($this->table_replay,$data);
        return $this->db->insert_id();
    }

    /*
     * zxx
     * 获取回复列表
     * */
    public function get_reply($where,$offset = 0, $page = 20, $order="id_reply desc")
    {
        $this->db->select('*')
            ->from($this->table_replay)
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

    public function insert_reply_attachment($data){
        $this->db->insert($this->table_replay_attachment,$data);
        return $this->db->insert_id();
    }
    /*
     * zxx
     * 获取id_open
     * */
    public function get_openid($where)
    {
        $this->db->select('id_open')
            ->from($this->table_msg)
            ->where($where);
        $result = $this->db->get()->result_array();
        return $result;
    }



    /*
     * zxx
     * 获取回复附件信息
     * */
    public function get_reply_attachment($where,$offset = 0, $page = 20, $order="id_reply_attachment desc")
    {
        $this->db->select('*')
            ->from($this->table_replay_attachment)
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

    /**
     * zxx
     * @param $where
     * @param int $offset
     * @param int $page
     * @return mixed
     */
    public function get_customer_sd_list($where){
        $this->db->select('*')
            ->from($this->table . ' as c')
            ->join($this->table_sd . ' as csd','c.id_customer = csd.id_customer','left')
            ->where($where);
        $result = $this->db->get()->result_array();
        return $result;
    }
}



/* End of file customer_model.php */