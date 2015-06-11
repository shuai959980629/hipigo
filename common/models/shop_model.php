<?php
/**
 * 
 * @copyright(c) 2013-11-20
 * @author zxx
 * @version Id:Shop_Model.php
 */

class Shop_Model extends CI_Model
{
    protected $table = 'bn_shop';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    
  /**
   * 根据读取内容来读取内容
   * @param $options 
   *          order  排序
   *          offset 偏移量
   *          limit  读取条数
   * @version 2.0
   * @author Jamai
   * @return Array
   **/
  public function byFieldSelect($fields = array(), $where = array(), $options = array(), $row = false)
  {
    if( ! $fields)
      $fields = '*';
    
    $this->db->select($fields)
              ->from($this->table);
    if($where)
      $this->db->where($where);
    
    if($options) {
      if($options['order'])
        $this->db->order_by($options['order']);
      else 
        $this->db->order_by('created DESC');
      
      if($options['offset'] && $options['limit'])
        $this->db->limit($options['limit'], $options['offset']);
      else if($options['limit'])
        $this->db->limit($options['limit']);
    }
    
    if($row)
      $result = (Array) $this->db->get()->row();
    else
      $result = $this->db->get()->result_array();
    
    return $result;
  }
    
    
    /*
     * zxx 获取门店和门店电话
     * **/
	public function get_shop_phone($where)
    {
        $this->db->select('id_shop,id_business,name,contact')
            ->from($this->table)
            ->where('contact !=','')
            ->where($where);
//            ->limit(1);
        $result = $this->db->get()->result_array();
		return $result;
	}


    /*
     * zxx 获取门店介绍,坐标位置,门店图片
     * **/
    public function get_shop_introduction($where,$offset=0,$page=10)
    {
        $this->db->select('id_shop,id_business,name,introduction,image_url,contact,address,latitude,longitude,baidu_location')
            ->from($this->table)
            ->where($where);

        if($offset){
            $this->db->limit($page,($offset-1)*$page);
        }
        $result = $this->db->get()->result_array();
        return $result;
    }


    /*
     * zxx
     * 更新门店信息
     */
    public function update_shop($data,$where){
        $re = false;
        if($data){
            $re = $this->db->update($this->table, $data, $where);
        }

        return $re;
    }


    public function get_map_title($bid,$type="map"){
    	$this->db->select('*');
    	$this->db->from('bn_wx_menu');
    	$this->db->where(array('id_business'=>$bid,'object_type'=>'view'));
    	$this->db->like(array('object_value'=>$type),'both');
    	$result = $this->db->get()->result_array();
    	return $result;
    }


}



/* End of file user_model.php */