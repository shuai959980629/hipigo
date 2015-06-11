<?php
/**
 * 
 * @copyright(c) 2013-11-20
 * @author zxx
 * @version Id:activity_Model.php
 */

class Activity_Model extends CI_Model
{
    protected $table = 'bn_merchant_activity';
    protected $table_config = 'bn_merchant_activity_config';
    protected $table_join = 'bn_merchant_activity_join';
    protected $table_subject = 'bn_answer_subject';
    protected $table_option = 'bn_answer_options';
    protected $table_result = 'bn_answer_result';
    protected $business_sub = 'bn_business_subscribe';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /*
     * zxx 获取最新活动
     * **/
    public function get_new_activity($where,$limit,$order='created desc')
    {
        $this->db->select('id_activity,id_shop,id_business,name,content as introduction,image_url,type')
            ->from($this->table)
            ->where($where)
            ->order_by($order)
            ->limit($limit);
        $result = $this->db->get()->result_array();
//        var_dump($this->db->last_query());
        return $result;
    }

    /**
     * 添加活动
     * @param array $add_data
     */
	public function add_activity($add_data){
		$this->db->insert($this->table,$add_data);
        return $this->db->insert_id();
		//debug($this->db->last_query());
	}

	/**
	 * 根据某条特定商家活动信息
	 * @param array $where
	 */
	public function get_activity($where){
		
		$query = $this->db->get_where($this->table,$where);
		$result = $query->result_array();
		return $result;
	}

	/**
	 * 获取商家活动列表
	 * @param int $start
	 * @param int $limit
	 */
	public function get_activity_list($where,$start,$limit,$order = 'created desc'){
		
		$this->db->select('*')
            ->from($this->table)
            ->where($where)
            ->limit($limit,$start);
        if( $order ){
            $this->db->order_by($order);
        }
		return $this->db->get()->result_array();
	}

	/**
	 * 修改商家活动
	 * @param array $where
	 * @param array $save_data
	 */
	public function edit_activity($where,$save_data){
		
		 $this->db->where($where);
		 return $this->db->update($this->table,$save_data);
		
	}

	/**
	 * 返回总行数
	 * @param array $where
	 * @return int number
	 */
	public function get_activity_count($where){
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
	 * 删除活动
	 * @param array $where
	 * @return boolean
	 */
	public function del_activity($where){
        $re = $this->db->delete($this->table, $where);
        return $re;
	}
    /**
     * 删除活动配置
     * @param array $where
     * @return boolean
     */
    public function del_activity_config($where){
        $re = $this->db->delete($this->table_config, $where);
        return $re;
    }

    /**
     * 查询活动第二步配置
     * @param array $where
     */
    public function get_activity_two($where=''){
        $this->db->select('*')
            ->from($this->table_config);

        if($where){
            $this->db->where($where);
        }
//        $query = $this->db->get_where($this->table_config,$where);
//        $result = $query->result_array();
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * zxx
     * 添加活动第二步设置
     * @param array $add_data
     */
    public function insert_activity_two($add_data){
        $this->db->insert($this->table_config,$add_data);
        return $this->db->insert_id();
    }

    /**
     * 修改活动第二步设置
     * @param array $where
     * @param array $data
     */
    public function update_activity_two($where,$data){
        $re = $this->db->update($this->table_config, $data,$where);
//        debug($this->db->last_query());
        return $re;
    }

    /*
     * zxx 获取活动题目
     * $type 1:不连表查询  2：连表查询题目和所有选项  3：连表查询题目和正确答案选项
     * **/
    public function get_answer_subject($where,$offset = 0, $page = 20, $order='',$type = 1)
    {
        if($type == 2){
            $this->db->select('ts.id_activity,ts.id_subject,ts.title,ts.id_options,ts.weight,GROUP_CONCAT(CONCAT(topt.description)) as description,GROUP_CONCAT(CONCAT(topt.id_options)) as id_option')
                ->from($this->table_subject .' as ts');
            $this->db->join($this->table_option." as topt","ts.id_subject=topt.id_subject","left");
        }else if($type == 3){
            $this->db->select('ts.id_activity,ts.id_subject,ts.title,ts.id_options,ts.weight,topt.description,topt.id_options as id_option')
                ->from($this->table_subject .' as ts');
            $this->db->join($this->table_option." as topt","ts.id_options=topt.id_options","left");
        }else{
            $this->db->select('ts.id_activity,ts.id_subject,ts.title,ts.id_options,ts.weight,topt.description,topt.id_options as id_option')
                ->from($this->table_subject .' as ts');
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


    /**
     * zxx
     * 添加活动第三步设置  添加一站到底题目
     * @param array $add_data
     */
    public function insert_answer_subject($add_data){
        $this->db->insert($this->table_subject,$add_data);
        return $this->db->insert_id();
    }

    /**
     * zxx
     * 添加活动第三步设置  添加一站到底选项
     * @param array $add_data
     */
    public function insert_answer_option($add_data){
        $this->db->insert($this->table_option,$add_data);
        return $this->db->insert_id();
    }

    /**
     * 修改活动题目的正确答案id
     * @param array $where
     * @param array $data
     */
    public function update_answer_subject($where,$data){
        $this->db->where($where);
        return $this->db->update($this->table_subject,$data);
    }

    /**
     * 修改活动选项信息
     * @param array $where
     * @param array $data
     */
    public function update_answer_option($where,$data){
        $this->db->where($where);
        return $this->db->update($this->table_option,$data);
    }

    /*
      * zxx
      * 删除题目信息
      */
    public function delete_answer($where){
        $re = $this->db->delete($this->table_subject, $where);
        return $re;
    }

    /*
      * zxx
      * 删除选项信息
      */
    public function delete_answer_option($where){
        $re = $this->db->delete($this->table_option, $where);
        return $re;
    }



    /*
     * zxx 获取活动题目
     * $type 1:不连表查询  2：连表查询题目和所有选项  3：连表查询题目和正确答案选项
     * **/
    public function get_answer_option($where='',$offset = 0, $page = 20, $order='')
    {
        $this->db->select('*')
            ->from($this->table_option);

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
     * zxx 获取活动参与次数，参与人数及获奖人数。
     * **/
    public function get_activity_join_count($where,$num=1){
        if($num == 2){
            $this->db->select('*')
                ->from($this->table_join)
                ->where($where);
            $this->db->group_by('id_open');
            $return = $this->db->get()->result_array();
        }else{
            $this->db->select('*')
                ->from($this->table_join)
                ->where($where);
            $return = $this->db->count_all_results();
        }

//        debug($this->db->last_query());
        return $return;
    }

    /**
     * zxx
     * 添加参与活动数据
     * @param array $data
     */
    public function insert_activity_join($data){
        $this->db->insert($this->table_join,$data);
        return $this->db->insert_id();
    }

    /*
     * zxx 获取一站到底的排行榜
     * **/
    public function get_answer_rank($where,$offset = 0, $page = 20, $order='')
    {
        $this->db->select('ar.id_result,ar.score,ar.consuming,ar.created,bs.nick_name,bs.head_image_url')
            ->from($this->table_result .' as ar')
            ->join($this->business_sub." as bs","ar.id_open=bs.id_open","left");

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


    /**获取当前商家的事件活动
     * @param $where
     * @param int $offset
     * @param int $page
     * @param string $order
     * @return mixed
     */
    public function get_event_activity($where,$offset = 0, $page = 20, $order="ma.weight desc")
    {
        $this->db->select('ma.id_activity,ma.name,ma.type,ma.created,mac.id_config,mac.join_number_day,mac.join_number_total,mac.estimate_people_number,mac.estimate_activity_day,mac.complete_time,mac.requirement,mac.success_reply,mac.failure_reply')
            ->from($this->table.' as ma')
            ->where($where)
            ->join($this->table_config." as mac","ma.id_activity=mac.id_activity","left");

        if($offset){
            $this->db->limit($page,($offset-1)*$page);
        }
        if( $order ){
            $this->db->order_by($order);
        }
        $result = $this->db->get()->result_array();
        return $result;
    }

}



/* End of file user_model.php */