<?php
/**
 * 
 * @copyright(c) 2013-11-20
 * @author zxx
 * @version Id:Business_Model.php
 */

class Business_Model extends CI_Model
{
    protected $table = 'bn_business';
    protected $table_attachment = 'bn_business_attachment';
    protected $table_subscribe = 'bn_business_subscribe';
    protected $table_biz_config = 'bn_business_config';

    public function __construct()
    {
        $this->load->database();
    }

    /*
    * zxx 总店电话
    * **/
    public function get_business_phone($where)
    {
        $this->db->select('*')->from($this->table)->where($where)->
            order_by('id_business', 'desc')->limit(1);
        $result = $this->db->get()->result_array();
        return $result;
    }


    /*
    * zxx 获取企业图片
    * **/
    public function get_business_attachment($where)
    {
        $this->db->select('*')->from($this->table_attachment)->where($where);
        $result = $this->db->get()->result_array();
        return $result;
    }


    /*
    * zxx  获取企业介绍
    * */
    public function get_business_info($business_id, $where = '')
    {
        $this->db->select('b.id_business,b.name,b.introduction,b.contact_number,b.logo,b.receive_mode,b.visit_mode,GROUP_CONCAT(CONCAT(ba.id_attachment)) as id_attachment,GROUP_CONCAT(CONCAT(ba.image_url)) as image_url,b.sld')
            ->from($this->table . ' as b')
            ->where('b.id_business', $business_id)
            ->join('bn_business_attachment as ba', "b.id_business=ba.id_business", 'left');

        if ($where != '') {
            $this->db->where($where);
        }
        $result = $this->db->get()->result_array();
        return $result;
    }


    /*
    * zxx
    * 添加一条企业信息
    */
    public function insert_business($data)
    {
        $business_info = array(
            'name' => $data['name'],
            'introduction' => $data['introduction'],
            'contact_number' => $data['contact_number'],
            'logo' => $data['logo_url']);
        $this->db->insert($this->table, $business_info);
        //插入的数据id
        $id_business = $this->db->insert_id();
        $business_attachment = array(
            'id_business' => $id_business,
            'image_url' => $data['img_url'],
            );
        $re = $this->db->insert($this->table_attachment, $business_attachment);

        return $re;
    }

    /*
    * zxx
    * 更新商家信息
    * */
    public function update_business($data, $where = '')
    {
        $re = $this->db->update($this->table, $data, $where);
        return $re;
    }


    /*
    * zxx
    * 删除企业图片
    */
    public function delete_synopsis_image($where)
    {
        $re = $this->db->delete($this->table_attachment, $where);
        return $re;
    }


    /*
    * zxx
    * 更新企业信息
    */
    public function update_synopsis($data, $where)
    {
        if ($data) {
            $business_info = array(
                'name' => $data['name'],
                'introduction' => $data['introduction'],
                'receive_mode' => $data['receive_mode'],
                'contact_number' => $data['contact_number']);
            if ($data['logo_url']) {
                $business_info['logo'] = $data['logo_url'];
            }
            $re = $this->db->update($this->table, $business_info, $where);

            if ($data['img_url']) {
                foreach ($data['img_url'] as $key => $diu) {
                    if ($diu != 'error') {
                        $business_attachment = array(
                            'id_business' => $data['id_business'],
                            'image_url' => $diu,
                            'size' => $data['size'][$key]);
                        $this->db->insert($this->table_attachment, $business_attachment);
                    }
                }
            }
            return $re;
        } else
            return '';

    }


    /*
    * zxx
    * 插入企业文本框内上传的附件
    * */
    public function insert_business_attachment($data, $where = '')
    {
        if ($where == '') {
            $this->db->insert($this->table_attachment, $data);
        } else {
            $this->db->delete($this->table_attachment, $where);
        }
        //        debug($this->db->last_query());
    }


    public function get_biz_shop_info_by_uid($uid, $shop)
    {

        $this->db->select('a.id_user,a.name,a.pass,a.pass_hash,a.status,d.id_shop,b.id_business,b.real_name,c.name as biz_name,c.introduction as intro,c.contact_number as tell,c.logo,d.name as shop_name,d.introduction as shop_intro,d.image_url,d.longitude as shop_long,d.latitude as shop_lat');
        $this->db->from('hipigo_user as a');
        $this->db->join('bn_merchant_user as b', 'a.id_user=b.id_user', 'left');
        $this->db->join('bn_business as c', 'c.id_business=b.id_business', 'left');
        $this->db->join('bn_shop as d', 'd.id_business=c.id_business', 'left');
        $this->db->where(array('a.id_user' => $uid, 'd.id_shop' => $shop));
        $this->db->where('d.id_shop >', 0);

        $return = $this->db->get()->result_array();
        if (!empty($return[0])) {
            return $return[0];
        }

        return '';
        //d.name as shop_name,d.introduction as shop_intro,d.image_url,d.contact_number as shop_tell,d.longitude as shop_long,d.latitude as shop_lat

    }

    /**
     * 获取商家配置
     * @param unknown $where
     */
    public function get_biz_config($where)
    {
        $query = $this->db->get_where($this->table_biz_config, $where, 1);
        return $query->row_array();
    }


    /**
     * 获取用户统计数据
     * @param array $where
     */
    public function get_user_count($where, $where2 = array())
    {

        $this->db->where($where);
        if ($where2) {
            $this->db->where($where2);
        }
        $this->db->from('bn_business_subscribe');
        $result = $this->db->count_all_results();
        //debug($this->db->last_query());
        return $result;

    }


    public function get_msg_count($where, $where2 = array())
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


    /*
    * zxx 获取用户信息
     * $type 1查询关注者列表  0查询关注者总条数
    * **/
    public function get_business_sub($where,$type=1,$offset = 0, $page = 20, $order="id_bn_sub desc")
    {
        $this->db->select('*')->from($this->table_subscribe)->where($where);
        if($type == 1){
            if($offset){
                $this->db->limit($page,($offset-1)*$page);
            }
            if( $order ){
                $this->db->order_by($order);
            }
            $result = $this->db->get()->result_array();
        }else{
            $result = $this->db->count_all_results();
        }
        return $result;
    }
    
    /*
    * sc 获取关注用户
    * **/
    public function get_business_sub_all($where)
    {
        $this->db->select('*')->from($this->table_subscribe)->where($where);
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * @author zhoushuai
     * @根据商家网ID，获取商家信息
     */
     
     public function get_business_inf($where){
        $this->db->select('*')->from($this->table)->where($where)->limit(1);
        $result = $this->db->get()->result_array();
        $data = empty($result)?false:$result[0];
        return $data;        
     }


    /**
     * @author zhoushuai
     * @根据商家网站域名。获的商家ID
     */

    public function get_bs_bid($url)
    {
        $url = mysql_escape_string($url);
        $mysql = "select `id_business`,`name` from {$this->table} where `sld` = \"{$url}\" LIMIT 0,1";
        $query = mysql_query($mysql);
        $row = mysql_fetch_assoc($query);
        if(!empty($row)){
            return $row;
        }else{
            exit("商家不存在。。。");
        }
    }
    
    /**
     * 获取商家信息
     * 
     * @param String $where
     * @author Jamai
     * @version 2.0
     */
    public function getBusinessInfo($where)
    {
      
    }
    

    /**
     * @author zhoushuai
     * @根据商家网站域名。获的商家首页上传的图片信息。等内容
     */
    public function get_bs_home_inf($where='')
    {
        $this->db->select('b.id_business,b.visit_mode,c.name,c.image,c.url,c.weight,c.type,c.object_type,c.id_object,c.template');
        $this->db->from('bn_micro_site_config as c');
        $this->db->join('bn_business as b', 'b.id_business = c.id_business', 'left');
        $this->db->where($where);
        $this->db->order_by("c.weight", "desc");
        $this->db->order_by("c.id_config", "desc");
        //$this->db->limit(10);
        $return = $this->db->get()->result_array();
        return $return;
    }
    
    /**
     * @author
     * @获取门店ID
     * @param string $id_business ,商家ID
     * @param string $model 访问方式 default: exclusive独占 share 共享
     */
     //SELECT `id_shop` from bn_shop where id_business = 1
     public function get_shop_id($id_business,$model){
        if($model=='share'){
            return 0;
        }else{
            $this->db->select('s.id_shop');
            $this->db->from('bn_shop  as s');
            $this->db->where(array('s.id_business'=>$id_business));
            //$this->db->order_by("s.id_shop", "desc");
            $this->db->limit(1);
            $return = $this->db->get()->result_array();
            $shop_id = $return[0]['id_shop'];
            return $shop_id;
        }
        
     }     
     
     
    /**
     *  通过商家ID 。得到商家权限。判断商家是否有权限进入。微官网首页,
     *没有权限。页面跳转到商家后台登录页面--微官网配置  
     *@author zhoushuai
     *@param  int id_business 商家id
     *@param  int id_power 权限数值。
     */
     function get_power($id_business,$id_power){
        $bid = intval($id_business);
        $this->db->select('r.id_right');
        $this->db->from('bn_merchant_right  as r');
        $this->db->where(array('r.id_business'=>$bid));
        $return = $this->db->get()->result_array();
        $power = array();
        for($i=0;$i<count($return);$i++){
            array_push($power,$return[$i]['id_right']);
        }
        if(!in_array($id_power,$power)){
            header("location:/biz");
        }
     }
    
    /**
     * 根据时间查询。。。关注日期列表
     * @author zhoushuai
     * @param string time 时间Y-m
     * @param int int id_business 商家id
     * @param int int id_shop 门店ID
     * 
     * 
     */
     function get_date_list($time,$id_business,$id_shop){
        
        $this->db->select('DISTINCT(DATE_FORMAT(created, \'%Y-%m-%d\')) as `date` ',false);
        $this->db->from('bn_business_subscribe as `s`');
        $array= array('s.id_business' =>$id_business, 's.id_shop' => $id_shop, 'DATE_FORMAT(created,\'%Y-%m\')' => $time);
        $this->db->where($array);
        $this->db->order_by('created','DESC');
        $return = $this->db->get()->result_array();
        //debug($this->db->last_query());
        $data = array();
        for($i=0;$i<count($return);$i++){
            $data[$i] = $return[$i]['date'];
        }
        return $data;
     
     }
     
     
     function get_new_sub_count($id_business,$id_shop,$date){
        $where = array();
		$where['id_business'] = $id_business;
		$where['id_shop']  = $id_shop;
		//$where['state'] = 'subscribe';
        $where["DATE_FORMAT(created,'%Y-%m-%d')"] = $date ;
        return $this->get_user_count($where); 
     }
    
    function get_new_sub_total($id_business,$id_shop,$date){
        
        $where = array();
		$where['id_business'] = $id_business;
		$where['id_shop']  = $id_shop;
		//$where['state'] = 'subscribe';
        $where["DATE_FORMAT(created,'%Y-%m-%d') <= "]  = $date;
        $res = $this->get_user_count($where) -$this->get_new_cancal_total($id_business,$id_shop,$date);
        return $res;
        
    }
    
    function get_new_cancal_total($id_business,$id_shop,$date){
        $where = array();
		$where['id_business'] = $id_business;
		$where['id_shop']  = $id_shop;
		$where['state'] = 'unsubscribe';
        $where["DATE_FORMAT(update_time,'%Y-%m-%d') <= "]  = $date;
        return $this->get_user_count($where);
    }
    
    
    function get_cancel_sub($id_business,$id_shop,$date){
        
        $where = array();
		$where['id_business'] = $id_business;
		$where['id_shop']  = $id_shop;
		$where['state'] = 'unsubscribe';
        $where["DATE_FORMAT(update_time,'%Y-%m-%d')"] = $date;
        return $this->get_user_count($where); 

    }


    /*
     * zxx 获取用户是否注册
     * **/
    public function get_subscribe_count($where){
        if($where){
            $this->db->where($where);
        }
        $this->db->from($this->table_subscribe);
        $return = $this->db->count_all_results();
        return $return;
    }



}


/* End of file user_model.php */
