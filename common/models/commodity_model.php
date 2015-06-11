<?php
/**
 * 
 * @copyright(c) 2013-11-20
 * @author zxx
 * @version Id:activity_Model.php
 */

class Commodity_Model extends CI_Model
{
    protected $table = 'bn_commodity';
    protected $table_class = 'bn_commodity_class';
    protected $table_mall = 'bn_mall';
    protected $table_review = 'bn_review';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /*
     * zxx 获取商品（列表）信息
     * $type  0:执行join  1：饮品美食列表的时候不需要join外表
     */
    public function get_commodity_introduction($where,$offset = 0, $page = 20, $order='',$type=1)
    {
        if($type === 0){
            $this->db->select('c.id_commodity,c.id_shop,c.id_business,c.name,c.descript,c.image_url,c.price,c.state,c.weight,c.created,c.id_class,cc.name as type')
            ->from($this->table.' as c')
            ->where($where)
            ->where('c.id_commodity NOT IN(SELECT id_commodity from bn_mall)')
            ->join($this->table_class." as cc","c.id_class=cc.id_class","left");
        }elseif($type == 2){
            $this->db->select('c.id_commodity,c.id_shop,c.id_business,c.id_class,r.id_review,r.phone_number,r.name,r.content')
                ->from($this->table.' as c')
                ->where($where)
                ->where('c.id_commodity IN(SELECT id_object from bn_review)')
                ->join($this->table_review." as r","c.id_commodity=r.id_object","left");
        }else{
            $this->db->select('c.id_commodity,c.id_shop,c.id_business,c.name,c.descript,c.image_url,c.price,c.state,c.weight,c.created,c.id_class')
                ->from($this->table.' as c')
                ->where($where);
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
     * zxx 获取商品信息总条数
     * **/
    public function get_commodity_count($where,$type=1){
        $this->db->where($where);
        if($type == '2'){
            $this->db->where('c.id_commodity IN(SELECT id_object from bn_review)');
            $this->db->join($this->table_review." as r","c.id_commodity=r.id_object","left");
        }else{
            $this->db->where('c.id_commodity NOT IN(SELECT id_commodity from bn_mall)');
        }
        $this->db->from($this->table . ' as c');
        $return = $this->db->count_all_results();
//        debug($this->db->last_query());
        return $return;
    }

    /*
     * zxx 获取商品类型  通过总店id
     * **/
    public function get_commodity_class($where)
    {
        $this->db->select('id_class,id_business,name')
            ->from($this->table_class)
            ->where($where);
        $result = $this->db->get()->result_array();
//        debug($this->db->last_query());
        return $result;
    }

    /*
     * zxx
     * 添加一条企业信息  返回插入数据的id
     */
    public function insert_commodity($data){
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /*
     * zxx
     * 更新一条企业信息
     */
        public function update_commodity($data,$where){
            $re = $this->db->update($this->table, $data,$where);
            return $re;
        }

    /*
       * zxx
       * 添加一条企业信息的分类
       */
    public function insert_commodity_class($data){
        $re = $this->db->insert($this->table_class, $data);
        return $re;
    }

    /*
      * zxx
      * 删除一条企业信息的分类
      */
    public function delete_commodity($id_commodity){
        $re = $this->db->delete($this->table, array('id_commodity' => $id_commodity));
        return $re;
    }
    public function get_commodity_list($where,$offset = 0, $page = 20, $order="state desc,weight desc")
    {
        $this->db->select('c.id_commodity,c.id_shop,c.id_business,c.name,c.descript,c.image_url,c.price,c.state,c.weight,c.created,c.id_class,cc.name as type,m.id_mall,m.recommend,m.integral,m.quantity')
             ->from($this->table.' as c')
             ->where($where)
             ->join($this->table_class." as cc","c.id_class=cc.id_class","left")
             ->join($this->table_mall." as m","c.id_commodity=m.id_commodity",'left');

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