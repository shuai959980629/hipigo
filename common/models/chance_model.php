<?php
/**
 *
 * @copyright(c) 2014-04-03
 * @author vikie
 * @version Id:Chance_Model.php
 */

class Chance_Model extends CI_Model
{


    public function __construct()
    {
        $this->load->database();
    }


    //查找当前商家可用的电子卷
    public function find($id_eticket)
    {
        $query = $this->db->query("SELECT id_eticket,name,length,quantity,get_quantity FROM bn_eticket WHERE id_eticket='$id_eticket'");
        $result = $query->result_array();
        return $result;
    }

    //查找当前商家可用的关注数
    public function find_user($bn_id)
    {
        $query = $this->db->query("SELECT id_business FROM bn_business_subscribe WHERE id_business='$bn_id'");
        return $query->num_rows();

    }

    //查询当前已有产生电子卷
    public function find_user_win($id_eticket)
    {
        $query = $this->db->query("SELECT id_business FROM bn_eticket_item WHERE id_object='$id_eticket' AND object_type='eticket'");
        return $query->num_rows();

    }

    //通过用户ID，电子卷ID，查询该用户已获得的电子卷数量
    public function find_num_win($id_object,$id_open)
    {
        $query = $this->db->query("SELECT id_business FROM bn_eticket_item WHERE id_object='$id_object' AND object_type='eticket'
	AND id_open='$id_open'");
        return $query->num_rows();
    }

    //查找当前前用户当天参与次数
    public function find_activity_time($id_activity, $id_open, $l_date, $m_date)
    {
        $query = $this->db->query("SELECT id_activity FROM bn_merchant_activity_join WHERE id_activity='$id_activity' AND id_open='$id_open'
	 AND created >'$l_date' AND created < '$m_date'");
        return $query->num_rows();
    }

   
	
	 //获取当前商家活动的配置
    public function find_activity_config($id_activity)
    {
    $query = $this->db->select("join_number_day,join_number_total,estimate_people_number,estimate_activity_day,requirement,
	success_reply,failure_reply,complete_time,type");
	$this->db->from('bn_merchant_activity');
    $this->db->join('bn_merchant_activity_config','bn_merchant_activity_config.id_activity = bn_merchant_activity.id_activity', 'left');
    $this->db->where('bn_merchant_activity.state',1);
	$this->db->where('bn_merchant_activity.id_activity',$id_activity);
	return $this->db->get()->result_array();
    }
	

    //获取当前商家活动的配置
    public function find_event($bn_id)
    {
        $query = $this->db->query("SELECT id_activity,created FROM bn_merchant_activity WHERE id_business='$bn_id' AND type='event'
	AND state=1 ORDER BY weight LIMIT 1");
        $result = $query->result_array();
        return $result;
    }

    //插入答题活动成绩表
    public function set_result_answer($answer_info)
    {
        $result = $this->db->insert('bn_answer_result', $answer_info);
        return $result;
    }

    /**
     * 电子卷插入活动记录表
     * @param array $data
     * @return boolean
     */
    public function set_activity($data)
    {

        $this->db->insert('bn_merchant_activity_join', $data);          
		return $this->db->insert_id();

    }
//如果答题获得电子卷更新获奖状态   
   public function update_jion($jion_id)
   {
   	$item=array('is_winner'=>1);
	$where=array('id_join'=>$jion_id);
   	$this -> db -> update('bn_merchant_activity_join', $item, $where);
   }

/**
 * 电子卷插入(事务和加锁方式)
 * @param array $data
 * @return boolean
 */
public function set_eticket($ob_id, $data) {
	     
	    //加锁
		$this -> db -> query("LOCK TABLES `bn_eticket_item` WRITE");
		$this -> db -> query("LOCK TABLES `bn_eticket` WRITE");
		//事务开始
		$this -> db -> query('BEGIN');
		$query = $this -> db -> query("SELECT get_quantity,quantity FROM bn_eticket WHERE id_eticket='$ob_id'");
		$result = $query -> result_array();
	    
		$eticket_info = array(
		  'id_business' => $data['id_business'],
		  'id_shop'     => $data['id_shop'],
		  'object_type' => $data['object_type'],
		  'id_object'   => $data['id_object'],
		  'code'        => $data['code'], 
		  'id_open'     => $data['id_open'], 
		  'id_customer' => $data['id_customer'],
		  'get_time'    => $data['get_time'],
		  'use_time'    => $data['use_time'],
		  'state'       => $data['state']
		     );
		$res1 = $this -> db -> insert('bn_eticket_item', $eticket_info);
		$item = array('get_quantity' => $result[0]['get_quantity'] + 1);
		$where = array('id_eticket' => $ob_id);
		$res2 = $this -> db -> update('bn_eticket', $item, $where);

		if ($result[0]['get_quantity'] < $result[0]['quantity'] && $res1 && $res2) {
			//提交事务
			$this -> db -> query("COMMIT");
		} else {
			$this -> db -> query("ROLLBACK");
		}
		
		//事务结束
		//$this -> db -> query("END");
		
		//解锁
			$this -> db -> query("UNLOCK TABLES");
			$this -> db -> query("UNLOCK TABLES");
		if ($result[0]['get_quantity'] < $result[0]['quantity']) {
			return TRUE;
		} else {
            return FALSE;
        }
}


    /**
     * 获取完成活动需要的时间，完成活动所需时间，单位秒
     */
    public function get_activity_time($id_activity)
    {
        $query = $this->db->select('complete_time')->from('bn_merchant_activity_config')->
            where('id_activity', $id_activity);
        $result = $query->get()->row_array();
        return $result['complete_time'];
    }

    /**
     * 获取一战到底的。题目。共计多少道题目
     */
    public function get_tips_count($id_activity)
    {
        $query = $this->db->select('count(id_subject) as num')->from('bn_answer_subject')->
            where('id_activity', $id_activity);
        $result = $query->get()->row_array();
        return $result['num'];
    }


    /**
     * 获取题目和答案
     */
    public function get_question_info($id_activity)
    {
        $this->db->select('bn_answer_subject.weight,bn_answer_subject.id_subject,bn_answer_subject.title,bn_answer_options.id_options,
        bn_answer_options.description');
        $this->db->from('bn_answer_subject');
        $this->db->join('bn_answer_options',
            'bn_answer_options.id_subject = bn_answer_subject.id_subject', 'left');
        $this->db->where('bn_answer_subject.id_activity', $id_activity);
		$this->db->order_by('bn_answer_subject.weight','DESC');
        return $this->db->get()->result_array();
    }

    /**
     * 查询题目正确答案
     */
    public function get_answer($where)
    {
        $this->db->select('id_subject,id_options');
        $this->db->from('bn_answer_subject');
        $this->db->where_in('id_subject', $where);
        return $this->db->get()->result_array();
    }

    /**
     * 查询我的电子券信息
     * select e.`name`, e.image,e.valid_begin,e.valid_end ,i.`code`,i.state
     * from bn_eticket as e 
     * LEFT JOIN bn_eticket_item as i 
     * ON e.id_eticket = i.id_object 
     * where id_open ='o5tjjt4L_RGB_gLcnVbeShz33G-g' 
     * and object_type = 'eticket'
     */
    public function get_tickets_list($id_open)
    {
        if ($id_open == '') {
            return false;
        }
        $this->db->select('e.name, e.image,e.valid_begin,e.valid_end ,i.code,i.state')->
            from('bn_eticket as e')->join('bn_eticket_item as i',
            'e.id_eticket = i.id_object', 'left')->where(array('id_open' => $id_open,
                'object_type' => 'eticket'))->order_by('i.state','ASC')->order_by('e.valid_end','desc');
        $result = $this->db->get()->result_array();
        $data = array();
        if(!empty($result)){
            for($i=0;$i<count($result);$i++){
                $result[$i]['due'] = $this->past_due($result[$i]['state'],$result[$i]['valid_end']);
                $result[$i]['image'] = $this->parse_img_url($result[$i]['image']);
            }
            foreach($result as $key =>$vals){
                if($vals['due']=='will'){
                    array_unshift($data,$vals);
                }else{ 
                    $data[]=$vals; 
                }
            }
            return $data;
        }else{
            return false;
        }
        //$return = !empty($result) ? $result : false;
        //return $return;
    }
    
    
    public function past_due($state,$endtime){
        if($state==2){
            return '';
        }else{
            $now = date('Y-m-d');
            $end = date("Y-m-d",strtotime($endtime));
            if($now==$end){
                return 'will';//即将过期
            }elseif($now>$end){
                return 'past';
            }             
        }        
    }
    
    public function parse_img_url($img){
        if($img!=''){
            $imgArray = explode('.',$img);
            $name = $imgArray[0];
            $strArray=str_split(substr($name, 0,4));
            $str=join("/",$strArray);
            $img_path = $_SERVER['DOCUMENT_ROOT'].'/attachment/business/ticket/'.$str.'/'.$img;
            if(file_exists($img_path)){
                return '/attachment/business/ticket/'.$str.'/'.$img;
            }else{
                return '/attachment/defaultimg/pic_005.png';
            }
        }else{
            //默认图片
            return '/attachment/defaultimg/pic_005.png';
        }
        
        
    }

}
