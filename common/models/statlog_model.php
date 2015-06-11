<?php
/**
 * 
 * @copyright(c) 2013-11-20
 * @author zxx
 * @version Id:Business_Model.php
 */

class Statlog_Model extends CI_Model
{
    protected $table = 'bn_stat_log';

    public function __construct()
    {
        $this->load->database();
    }

    public function insert_log($data)
    {
        $this->db->insert($this->table, $data);
        return $re;
    }


    public function get_analysis_count($where, $where2 = array())
    {

        $this->db->select('SUM(`value`) as cnt');
        $this->db->where($where);
        if ($where2) {
            $this->db->where($where2);
        }
        $this->db->from('bn_stat_analysis');
        $result = $this->db->get()->row_array();
        //debug($this->db->last_query());
        return $result;

    }


    public function get_info_count($where, $where2 = array())
    {

        $this->db->select('SUM(*) as cnt');
        $this->db->where($where);
        if ($where2) {
            $this->db->where($where2);
        }
        $this->db->from($this->table);
        $result = $this->db->get()->row_array();
        //debug($this->db->last_query());
        return $result;
    }

    /**
     * 根据时间查询。。。图文分析 info 日期列表
     * @author zhoushuai
     * @param string time 时间Y-m
     * @param int int id_business 商家id
     * @param int int id_shop 门店ID
     * 
     * 
     */
    function get_date_list($time, $id_business, $id_shop, $type = 'info', $event =
        'view')
    {
        $this->db->select('DISTINCT(DATE_FORMAT(created, \'%Y-%m-%d\')) as `date` ', false);
        $this->db->from($this->table);
        $array = array(
            'id_business' => $id_business,
            'id_shop' => $id_shop,
            'DATE_FORMAT(created,\'%Y-%m\')' => $time,
            'object_type' => $type,
            'event' => $event);
        $this->db->where($array);
        $this->db->order_by('created', 'DESC');
        $return = $this->db->get()->result_array();
        //debug($this->db->last_query());
        return $return;
    }

    function get_read_count($id_business, $id_shop, $date, $type = 'info', $event =
        'view')
    {
        $where = array();
        $where['id_business'] = $id_business;
        $where['id_shop'] = $id_shop;
        $where['object_type'] = $type;
        $where['event'] = $event;
        $where["DATE_FORMAT(created,'%Y-%m-%d')"] = $date;
        return $this->get_reader_count($where);
    }

    function get_reader_count($where, $where2 = array())
    {

        $this->db->where($where);
        if ($where2) {
            $this->db->where($where2);
        }
        $this->db->from($this->table);
        $result = $this->db->count_all_results();
        //debug($this->db->last_query());
        return $result;
    }

    function get_view_count($id_business, $id_shop, $num, $type = 'info', $event =
        'view')
    {
        if ($num == 1) {
            $time = date('Y-m-d', time());
            $array = array(
                'id_business' => $id_business,
                'id_shop' => $id_shop,
                'DATE_FORMAT(created,\'%Y-%m-%d\')' => $time,
                'object_type' => $type,
                'event' => $event);
        } elseif ($num == 2) {
            $week = lastNWeek(time(),1);
            $mon = $week['mon'];
            $sun = $week['sun'];
            $array = array(
                'id_business' => $id_business,
                'id_shop' => $id_shop,
                'DATE_FORMAT(created,\'%Y-%m-%d\') >= ' => $mon,
                'DATE_FORMAT(created,\'%Y-%m-%d\') <= ' => $sun,
                'object_type' => $type,
                'event' => $event);
        } else {
            return array();
        }
        $this->db->select('count(*) AS num,id_object ');
        $this->db->from($this->table);
        $this->db->where($array);
        $this->db->group_by('id_object');
        $this->db->order_by('num', 'DESC');
        $this->db->order_by('id_object', 'DESC');
        $this->db->limit(20);
        $return = $this->db->get()->result_array();
        //debug($this->db->last_query());
        return $return;
    }

    public function get_info_title($id_object)
    {

        $this->db->select('title');
        $this->db->from("bn_info");
        $array = array('id_info' => $id_object);
        $this->db->where($array);
        $return = $this->db->get()->result_array();
        //debug($this->db->last_query());
        return $return[0]['title'];
    }


    /**
     * 根据时间查询。。。图文分析 info 日期列表
     * @author zhoushuai
     * @param string time 时间Y-m
     * @param int int id_business 商家id
     * @param int int id_shop 门店ID
     * 
     * 
     */
    function get_msg_date_list($time, $id_business, $id_shop)
    {
        $this->db->select('DISTINCT(DATE_FORMAT(created, \'%Y-%m-%d\')) as `date` ', false);
        $this->db->from('bn_customer_msg');
        $array = array(
            'id_business' => $id_business,
            'id_shop' => $id_shop,
            'DATE_FORMAT(created,\'%Y-%m\')' => $time);
        $this->db->where($array);
        $this->db->order_by('created', 'DESC');
        $return = $this->db->get()->result_array();
        //debug($this->db->last_query());
        return $return;
    }


    function get_msg_people($date, $id_business, $id_shop)
    {
        $where = array();
        $where['id_business'] = $id_business;
        $where['id_shop'] = $id_shop;
        $where["DATE_FORMAT(created,'%Y-%m-%d')"] = $date;
        $this->db->from('bn_customer_msg');
        $this->db->select(' COUNT(DISTINCT id_open) as cnt ');
        $this->db->where($where);
        $return = $this->db->get()->result_array();
        return $return[0]['cnt'];
    }
    
    
    function get_send_cnt($date, $id_business, $id_shop){
        
        $where = array();
        $where['id_business'] = $id_business;
        $where['id_shop'] = $id_shop;
        $where["DATE_FORMAT(created,'%Y-%m-%d')"] = $date;
        return $this->get_msg_count($where);
        
        
    }
    

    function get_msg_count($where, $where2 = array())
    {
        $this->db->where($where);
        if ($where2) {
            $this->db->where($where2);
        }
        $this->db->from('bn_customer_msg');
        $result = $this->db->count_all_results();
        //debug($this->db->last_query());
        return $result;
    }


}


/* End of file user_model.php */
