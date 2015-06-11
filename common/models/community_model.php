<?php
/**
 * 
 * @copyright(c) 2014-04-21
 * @author zxx
 * @version Id:Community_Model.php
 */

class Community_Model extends CI_Model
{
    protected $table = 'bn_community_activity';
    protected $table_join = 'bn_community_activity_join';
    protected $table_sub = 'bn_business_subscribe';
    protected $table_user = 'bn_hipigo_user';
    protected $table_re = 'bn_review';
    protected $table_ticket = 'bn_eticket_item';
    protected $table_log = 'bn_community_log';
    protected $table_spread = 'bn_community_spread';
    protected $table_extends = 'bn_community_activity_extends';
    protected $table_activity_resource = 'bn_community_activity_resource';
    protected $table_tag = 'bn_community_activity_tag';
    protected $table_appraise = 'bn_appraise';
    protected $table_resource= 'bn_resources';
    protected $tag= 'bn_tag';
    protected $table_business = 'bn_business';
    
    public function __construct()
    {
      parent::__construct();
      $this->load->database();
    }

    /*
     * 更新旧数据
     */
    public function update_old_bn(){
        return $this->db->query("UPDATE bn_community_activity l set l.id_business_source = l.id_business where l.object_type = 'community'");
    }

    //查询活动及附件信息
    public function query_activity_info($id_review){
        $query = $this->db->query("SELECT * FROM bn_merchant_activity a,bn_business_attachment y WHERE a.id_activity = y.id_object and y.id_object = ".$id_review);
        return $query->result_array();
    }


    //查询活动评论上传图片
    public function query_spking_imgs($id_review){

        $query = $this->db->query("SELECT image_url FROM bn_business_attachment WHERE id_object = ".$id_review);
        return $query->result_array();

    }


	/**
	 * 根据活动ID获取活动标签信息
	 * @param string $id_activity
	 * @return array
	 */
	public function get_activity_tag($id_activity){
		$query = $this->db->query("SELECT * from ".$this->table_tag." t,".$this->tag." g where t.id_tag = g.id_tag and id_activity=".$id_activity);
		$result = $query->result_array();
		return $result;
	}

	/**
	 * 根据活动ID获取活动资源信息
	 * @param string $id_activity
	 * @return array
	 */
	public function get_activity_resource($id_activity){
		$query = $this->db->query("SELECT * from ".$this->table_activity_resource." where id_activity=".$id_activity);
		$result = $query->result_array();
		return $result;
	}
    
  /**
   * 获取社区活动列表
   * @param $select String 查询字段 default *
   * @param $where String 查询条件
   * @param $options 
   *          order  排序
   *          offset 偏移量
   *          limit  读取条数
   * @author Jamai
   * @version 2.1
   **/
  public function getCommunities($select = '*', $where = '', $options = array())
  {
    $this->db->select($select)
      ->from($this->table . ' AS activity')
      ->join($this->table_activity_resource . " AS activity_re", "activity.id_activity = activity_re.id_activity", "left")
      ->join($this->table_resource . ' AS re', "activity_re.id_resource = re.id_resource", 'left');
    
    if($where)
      $this->db->where($where);
    
	  $this->db->where('type != 2');

    if($options) {
      if($options['order'])
        $this->db->order_by($options['order']);
      else 
        $this->db->order_by('id_activity DESC');
      
      if($options['offset'] && $options['limit'])
        $this->db->limit($options['limit'], $options['offset']);
      else if($options['limit'])
        $this->db->limit($options['limit']);
    }
    
    if(strtoupper($select) == 'COUNT(*)')
      $result = $this->db->count_all_results();
    else
      $result = $this->db->get()->result_array();
    // echo $this->db->last_query();
    return $result;
  }
  
  public function byFieldSelect($fields = array(), $where = array(), $options = array(), $row = false)
  {
    if( ! $fields)
      $fields = '*';
    
    $this->db->select($fields)
              ->from($this->table);
    if($where)
      $this->db->where($where);
    
    if($options === true) {
      $row = true;
    }
    else {
      if($options) {
        if($options['order'])
          $this->db->order_by($options['order']);
        else 
          $this->db->order_by('id_activity DESC');
        
        if($options['offset'] && $options['limit'])
          $this->db->limit($options['limit'], $options['offset']);
        else if($options['limit'])
          $this->db->limit($options['limit']);
      }
    }
    
    if($row)
      $result = (Array) $this->db->get()->row();
    else
      $result = $this->db->get()->result_array();
    
    return $result;
  }
  

  /**
   * 获取社区活动点赞用户信息
   * @param $select String 查询字段 default *
   * @param $where String 查询条件
   * @param $options 
   *          order  排序
   *          offset 偏移量
   *          limit  读取条数
   * @author sc
   * @version 2.1
   **/
  public function getAppraises($select = '*', $where = '', $options = array())
  {
    $this->db->select($select)
      ->from($this->table_appraise);
    
    if($where)
      $this->db->where($where);
    
    if($options) {
      if($options['order'])
        $this->db->order_by($options['order']);
      else 
        $this->db->order_by('id_activity DESC');
      
      if($options['offset'] && $options['limit'])
        $this->db->limit($options['limit'], $options['offset']);
      else if($options['limit'])
        $this->db->limit($options['limit']);
    }
    
    if(strtoupper($select) == 'COUNT(*)')
      $result = $this->db->count_all_results();
    else
      $result = $this->db->get()->result_array();
    
    return $result;
  }


    /*
     * zxx 获取互动活动（列表）信息
     */
    public function get_community_info($where,$offset = 0, $page = 20, $order='',$type=0)
    {
        if($type == 1){
            $this->db->select('ca.*,cae.id_tag')
                ->from($this->table . ' as ca')
                ->join($this->table_extends." as cae","ca.id_activity=cae.id_activity","left")
                ->where($where);
        }elseif($type == 2){
            $this->db->select('ca.*,t.id_tag,t.tag_name')//GROUP_CONCAT(CONCAT(t.id_tag)) as id_tag,GROUP_CONCAT(CONCAT(t.tag_name)) as tag_name')
                ->from($this->table . ' as ca')
                ->join($this->table_tag." as cat","ca.id_activity=cat.id_activity","left")
                ->join($this->tag." as t","t.id_tag = cat.id_tag","left")
                ->where($where)
                ->group_by('ca.id_activity');
        }elseif($type == 3){
            $this->db->select('ca.*,GROUP_CONCAT(CONCAT(t.id_tag)) as id_tag,GROUP_CONCAT(CONCAT(t.tag_name)) as tag_name')//GROUP_CONCAT(CONCAT(t.id_tag)) as id_tag,GROUP_CONCAT(CONCAT(t.tag_name)) as tag_name')
                ->from($this->table . ' as ca')
                ->join($this->table_tag." as cat","ca.id_activity=cat.id_activity","left")
                ->join($this->tag." as t","t.id_tag = cat.id_tag","left")
                ->where($where);
        }else{
            $this->db->select('*')
                ->from($this->table)
                ->where($where);
        }

        if($offset){
            $this->db->limit($page,($offset-1)*$page);
        }
        if($type == 1){
            if( !$order ){
                $order = 'ca.id_activity desc';
            }
            $this->db->order_by($order);
        }else{
            if( $order ){
                $this->db->order_by($order);
            }
        }
        $result = $this->db->get()->result_array();
//        debug($this->db->last_query());
        return $result;
    }


    /*
     * zxx 获取互动活动总条数
     * **/
    public function get_community_count($where){
        $this->db->where($where);
        $this->db->from($this->table);
        $return = $this->db->count_all_results();
//        debug($this->db->last_query());
        return $return;
    }

    /*
     * zxx
     * 添加一条互动活动信息  返回插入数据的id
     */
    public function insert_community($data){
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /*
     * zxx
     * 更新一条互动活动信息
     */
    public function update_community($data,$where){
        $re = $this->db->update($this->table, $data,$where);
//        debug($this->db->last_query());
        return $re;
    }


    /*
     * zxx  （废弃）
     * 更新一条互动活动信息  删除活动成员 成员数量减1
     */
    public function lower_member_count($aid,$num = 1)
    {
        return $this->db->query("UPDATE bn_community_activity SET join_count = join_count-'$num' WHERE id_activity='$aid'");
    }

    /*
     * zxx
     * 更新一条互动活动信息  删除评论 评论数量减1
     */
    public function lower_review_count($aid,$num = 1)
    {
        return $this->db->query("UPDATE bn_community_activity SET review_count = review_count-'$num' WHERE id_activity='$aid'");
    }

    /*
      * zxx
      * 删除一条互动活动信息
      */
    public function delete_community($where){
        $re = $this->db->delete($this->table,$where);
        return $re;
    }


    /**
     * 查询用户活动 (消费码需要单独出来处理，不能在当前位置处理)
     * @author Jamai
     * @version 2.1
     **/
    public function getActivityBy()
    {
      
    }

    /*
     * zxx
     * 查询用户发布的活动和参加的活动列表信息
     * $offset   ($offset-1)*$page
    */
    public function get_user_activity($where='',$offset = 0, $page = 20, $order='j.id_join desc')
    {
      //a.join_price, a.total, BY Jamai
        $query = $this->db->query("SELECT j.id_join, a.id_activity, j.update_time,
                            IF(j.created IS NOT NULL, j.created, a.created ) AS created,
                            j.role,j.id_open,j.id_user,j.cellphone,a.name as title,
                            a.join_price, a.total, a.state,
                            a.posters_url, a.object_type,a.id_business FROM bn_community_activity AS a LEFT JOIN bn_community_activity_join AS j ON a.id_activity = j.id_activity WHERE $where GROUP BY a.id_activity ORDER BY $order LIMIT $offset,$page");
        //echo $this->db->last_query();
        $result = $query->result_array();

        //查询验证码
        if($result){
            foreach ($result as $key => $value) {
                $arr = $this->find_all_codes($result,$value['object_type']);
                if(($value['id_user'] == $_SESSION['userid'] && !empty($value['id_user']))  || ($value['cellphone']== $_SESSION['userid'] && !empty($value['cellphone'])) ){
                    foreach ($arr as $k => $v) {
                        if($value['id_activity'] == $v['id_object']){
                            $result[$key]['codes'][] = $v['code'];
                        }
                    }
                }
                if(!isset($result[$key]['codes']))
                    $result[$key]['codes'] = '';
            }
        }
        return $result;
    }

    /*
     * zxx 获取互动活动成员（列表）信息
     */
    public function get_community_member($where='',$offset = 0, $page = 20, $order='maj.id_join desc',$type = 1)
    {
        if($type == 2){
            $this->db->select('maj.id_join,maj.id_activity,maj.created,maj.update_time,maj.role,maj.id_open,maj.id_user,bs.nick_name,bs.head_image_url,ma.name as title')
                ->from($this->table_join . ' as maj')
                ->join($this->table_sub." as bs","maj.id_open=bs.id_open","left")
                ->join($this->table." as ma","maj.id_activity=ma.id_activity","left")
                ->where('maj.role != 3');
            if($where){
                $this->db->where($where);
            }
        }elseif($type == 3){ //参加活动
            $this->db->select('maj.id_join, ma.id_activity, maj.update_time,
                            IF(maj.created IS NOT NULL, maj.created, ma.created ) AS created,
                            maj.role,maj.id_open,maj.id_user,maj.cellphone,ma.name as title,
                            ma.posters_url, ma.object_type,ma.id_business')
                ->from($this->table_join . ' as maj')
                ->join($this->table." as ma","maj.id_activity=ma.id_activity","right")
                ->join($this->table_user . ' as u', 'u.id_user=ma.id_business','left')
                ->where('maj.role != 3')
                ->group_by('ma.id_activity');
                //->join($this->table_ticket." as ti","maj.id_activity=ti.id_object and maj.id_user=ti.id_customer and ti.object_type = 'community'","left");
            if($where){
                $this->db->where($where);
            }
        }elseif($type == 4){
            $this->db->select('maj.id_join,maj.id_activity,maj.created,maj.update_time,maj.role,maj.id_open,maj.id_user,maj.cellphone,ma.name as title,ma.posters_url,ma.id_business')
                ->from($this->table_join . ' as maj')
                ->join($this->table . " as ma","maj.id_activity=ma.id_activity","left")
                ->where('maj.role != 3');
                //->join($this->table_ticket." as ti","maj.id_activity=ti.id_object and maj.cellphone=ti.id_open and ti.object_type = 'community'","left");
            if($where){
                $this->db->where($where);
            }
        }elseif($type == 1){
            $this->db->select('maj.id_join,maj.id_activity,maj.created,maj.update_time,maj.role,maj.id_open,maj.id_user,maj.cellphone,maj.identity,ma.name as title,hu.nick_name,hu.head_image_url,hu.id_user,hu.cellphone as hipigo_phone')
                ->from($this->table_join . ' as maj')
                ->join($this->table." as ma","maj.id_activity=ma.id_activity","left")
                ->join($this->table_user." as hu","maj.id_user=hu.id_user","left")
                ->where('maj.role != 3');
            if($where){
                $this->db->where($where);
            }
            $this->db->group_by('maj.id_user');
            $this->db->group_by('maj.cellphone');
        }else{
            $this->db->select('maj.id_join,maj.id_activity,maj.created,maj.role,maj.id_open,maj.id_user,maj.cellphone,bs.nick_name,bs.head_image_url')
                ->from($this->table_join . ' as maj')
                ->join($this->table_sub." as bs","maj.id_open=bs.id_open","left")
                ->where('maj.role != 3');
            if($where){
                $this->db->where($where);
            }
        }

        if($offset){
            $this->db->limit($page,($offset-1)*$page);
        }
        if( $order ){
            $this->db->order_by($order);
        }
        $result = $this->db->get()->result_array();
        //查询验证码
        if($result){
            foreach ($result as $key => $value) {
                $arr = $this->find_all_codes($result,$value['object_type']);
                if(($value['id_user'] == $_SESSION['userid'] && !empty($value['id_user']))  || ($value['cellphone']== $_SESSION['userid'] && !empty($value['cellphone'])) ){
                    foreach ($arr as $k => $v) {
                        if($value['id_activity'] == $v['id_object']){
                            $result[$key]['codes'][] = $v['code'];
                        }
                    }
                }
                if(!isset($result[$key]['codes']))
                    $result[$key]['codes'] = '';
            }
        }

        return $result;
    }

    /*
     * vikie 查询多验证码
     * **/
    public function find_all_codes($data,$object_type=''){
     $userid = $_SESSION['userid'];
   foreach ($data as $key => $value) {
     $aids[]=$value['id_activity'];  
     }    
     $this->db->select('code,id_object');
     $this->db->from('bn_eticket_item');
        $where = '';
        if($object_type){
            $where .= '(';
        }
        $where .= 'object_type = "community"';
        if($object_type){
            $where .= ' or object_type = "'.$object_type.'") ';
        }
        $this->db->where($where);

     $this->db->where_in('id_object',$aids);
     if($_SESSION['identity'] == 'visitor'){
     $this->db->where('id_open',$userid);    
     }else{
     $this->db->where('id_customer',$userid);  
     }  
     $data = $this->db->get()->result_array();
//        print_r($this->db->last_query());
     return $data;
   }
    

    /*
     * zxx 获取互动活动成员总条数
     * **/
    public function get_community_member_count($where = '',$type = 1){
        if($where){
            $this->db->where($where);
        }
        $this->db->from($this->table_join . ' as maj');

        if($type == 2){
            $this->db->join($this->table_sub." as bs","maj.id_open=bs.id_open","left");
            $this->db->join($this->table." as ma","maj.id_activity=ma.id_activity","left");

            $return = $this->db->count_all_results();
        }elseif($type == 3){
            $this->db->join($this->table." as ma","maj.id_activity=ma.id_activity","left");
            $this->db->join($this->table_ticket." as ti","maj.id_activity=ti.id_object and maj.id_user=ti.id_customer and ti.object_type = 'community'","left");

            $return = $this->db->count_all_results();
        }elseif($type == 1){
            $this->db->join($this->table." as ma","maj.id_activity=ma.id_activity","left");
            $this->db->join($this->table_user." as hu","maj.id_user=hu.id_user","left");
            $this->db->group_by('maj.id_user');
            $this->db->group_by('maj.cellphone');
            $return = $this->db->get()->result_array();
        }else{
            $this->db->join($this->table_sub." as bs","maj.id_open=bs.id_open","left");
            $return = $this->db->count_all_results();
        }

//        debug($this->db->last_query());
        return $return;
    }


    /*
     * zxx
     *添加一条活动参加表信息
     */
    public function insert_community_join($data){
        $re = $this->db->update($this->table_join, $data);
        return $re;
    }

    /*
     * zxx
     *更新一条活动参加表信息
     */
    public function update_community_join($data,$where){
        $re = $this->db->update($this->table_join, $data,$where);
        return $re;
    }


    /*
      * zxx
      * 删除一条活动参加表信息
      */
    public function delete_community_join($where){
        $re = $this->db->delete($this->table_join,$where);
        return $re;
    }

    /*
     * zxx 获取互动活动成员人数信息
     */
    public function get_community_join_count($where,$type=1, $order = '', $limit = 0)
    {
        if($where){
            $this->db->where($where);
        }
        if($type != 1){
            $this->db->from($this->table_join . ' as j');
            $this->db->join("bn_hipigo_user as hu","j.id_user=hu.id_user","left");
            $this->db->group_by('j.cellphone');
            $this->db->group_by('j.id_user');
            if($order)
              $this->db->order_by('j.updated DESC');
            if($limit)
              $this->db->limit($limit);
        }else{
            $this->db->from($this->table_join);
            $this->db->group_by('cellphone');
            $this->db->group_by('id_user');
        }
        /*
        if($type == 1){
            $this->db->where('role != 3');
        }
        */
        $return = $this->db->get()->result_array();
//        debug($this->db->last_query());
        return $return;
    }


    /*
     * zxx
     * 增加一条活动修改记录
     */
    public function insert_community_log($data){
        $this->db->insert($this->table_log, $data);
        return $this->db->insert_id();
    }


    /*
     * zxx
     * 获取后台推广的社区活动
     */
    public function get_community_spread($where='',$offset = 0, $page = 20, $order='ms.date desc')
    {
        $this->db->select('ms.*,ma.name')
            ->from($this->table_spread . ' as ms')
            ->join($this->table." as ma","ms.id_activity=ma.id_activity","left");
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


    //判断当前用户是否已微信关注
    public function is_att($id_open,$bn_id)
    {
        $query = $this->db->query("SELECT id_business FROM bn_business_subscribe WHERE id_open='$id_open' AND id_business='$bn_id'");
        return $query->num_rows();

    }

    //查询当前已有的微信关注数
    public function att_num($bn_id)
    {
        $query = $this->db->query("SELECT id_business FROM bn_business_subscribe WHERE id_business='$bn_id' AND state='subscribe'");
        return $query->num_rows();

    }
  
  //查询商家内部关注数
    public function follow_num($bn_id)
    {
        $query = $this->db->query("SELECT id_business FROM bn_follow WHERE id_business='$bn_id'");
        return $query->num_rows();
    }
  
  //查询商家内部关注数
    public function follow_att($oid,$bn_id)
    {
        $query = $this->db->query("SELECT id_business FROM bn_follow WHERE id_business='$bn_id' AND id_open='$oid'");
        return $query->num_rows();
    }
  
  //是否是微信关注账号
    public function is_member($oid)
    {
        $query = $this->db->query("SELECT id_open FROM bn_business_subscribe WHERE id_open='$oid'");
        return $query->num_rows();
    }
  
  
  //查询商家活动数
    public function activity_num($bn_id)
    {
        $query = $this->db->query("SELECT id_business FROM bn_community_activity WHERE id_business='$bn_id' AND state>=0");
        return $query->num_rows();
    }
    
    
    
  
  //查询商家logo
    public function logo($bn_id)
    {
        $query = $this->db->query("SELECT logo,name,sld FROM bn_business WHERE id_business='$bn_id'");
    $result = $query->result_array();
    $d_img = 'hipigo.png'; //活动默认图片
    $logo_url = $this->parse_img_url('logo',$result[0]['logo'],$d_img);
    $title = $result[0]['name'];
    $sld = $result[0]['sld'];
    $row = array('logo_url'=>$logo_url,'title'=>$title,'sld_url'=>$sld);
        return $row;
    }
    
  //查询商家是否认证
  public function merchant($bid){
  $this->db->select("id_right");
  $this->db->from('bn_merchant_right');
  $this->db->where('id_right',86);
  $this->db->where('id_business',$bid);
  $data = $this->db->get()->num_rows();
  return $data;
  }
  
  //查询用户头像(老方法)
  public function user_header($oid)
  {
    $this->db->select("head_image_url");
        $this->db->from('bn_business_subscribe');
        $this->db->where('id_open',$oid);
    $data = $this->db->get()->result_array();
    return $data[0]['head_image_url'];
  }

   //查询用户头像(新方法)
  public function new_user_header($userid,$new='')
  {
    $this->load->model('string_model','string');  
    $this->db->select("head_image_url,nick_name");
        $this->db->from('bn_hipigo_user');
        $this->db->where('id_user',$userid);
    $data = $this->db->get()->result_array();
    $arr = $this->move_array($data);
    if($new==''){
    //$arr['nick_name'] = $this->string->substrCn(urldecode($arr['nick_name']),0,4,$suffix = false, $charset = "utf-8");  
    $arr['nick_name'] = $this->string->cn_substr_utf8(urldecode($arr['nick_name']),8,0);
    }
    
    $d_img = 'default-header.png';
    
    if(substr($arr['head_image_url'], 0,4) != 'http'){
    //取小头像  
    $file = explode('.', $arr['head_image_url']);
    $arr['head_image_url'] = $file[0].'-small.'.$file[1];
    $arr['head_image_url'] = $this->parse_img_url('head',$arr['head_image_url'],$d_img);
    }
    return $arr;
  }

    
    //查询商家logo
    public function ranking($bn_id)
    {
        $query = $this->db->query("SELECT community_current,community_recently FROM bn_business_ranking WHERE id_business='$bn_id'");
    $result = $query->result_array();
    return $result;
    }
  
  //活动标题
    public function community_name($aid)
    {
      $this->load->model('string_model','string');   
        $this->db->select("name,notice,type");
        $this->db->from('bn_community_activity');
    $this->db->where('id_activity',$aid);
        //$this->db->where('state',1);
    $data = $this->db->get()->result_array();
    $arr['title'] = $data[0]['name']=$this->string->substrCn($data[0]['name'],0,14,$suffix = true, $charset = "utf-8");
    $arr['notice'] = $data[0]['notice'];
    $arr['type'] = $data[0]['type'];
    return $arr;
    }
  
  //删除
    public function att_del($id_join,$aid)
    {
        $where = array('id_join'=>$id_join,'id_activity'=>$aid);
        return $this->db->delete('bn_community_activity_join',$where); 
    
    }
  
  //添加评论
    public function spking_add($aid,$userid,$bid,$sid,$content,$name,$sp_rp)
    {
        
    $data = array('id_business'=>$bid,
    'id_shop'=>$sid,
    'id_object'=>$aid,
    'object_type'=>'community',
    'id_customer'=>$userid,
    'name'=>$name,
    'image_url'=>'',
    'content'=>$content,
    'id_parent'=>$sp_rp,
    'created'=>date('Y-m-d H:i:s'),
    'state'=>1
    );
        $this->db->insert('bn_review',$data); 
    $last_id = $this->db->insert_id();
    return $last_id;
    
    }
  
  //插入评论图片
  public function insert_imgs($imgs){
  $this->db->insert('bn_business_attachment',$imgs);  
  }



   //更新评论
    public function spking_update($aid)
    {
        $this->db->query("UPDATE bn_community_activity SET review_count = review_count+1 WHERE id_activity='$aid'");
    }
  
  //查询用户头像
    public function photo($oid)
    {
        $this->db->select("head_image_url");
        $this->db->from('bn_business_subscribe');
        $this->db->where('id_open',$oid);
        $this->db->where('state','subscribe');
    $data = $this->db->get()->result_array();
    
    return $data[0]['head_image_url'];
    }
  
  //查询当前用户是否为管理员
    public function community_user($aid,$userid)
    {
        $this->db->select("id_join");
        $this->db->from('bn_community_activity_join');
        $this->db->where('id_user',$userid);
        $this->db->where('id_activity',$aid);
    $this->db->where('role',2);
    $data = $this->db->get()->num_rows();
    return $data;
    }

//查询当前活动是否有人参加
    public function att_community_user($aid)
    {
        $this->db->select("id_open");
        $this->db->from('bn_community_activity_join');
        $this->db->where('id_activity',$aid);
    $data = $this->db->get()->num_rows();
    return $data;
    }

//世界杯活动
public function get_win_list($aid='',$where,$pagesize,$total,$order ="created DESC")
    {
        
        $aid_all = $this->find_eticket_all($aid);
      foreach ($aid_all as $key => $value) {
      $aids[]=$value['id_eticket'];  
      }  
        $this->db->select('id_item,id_customer as id_user,id_object,get_time as created');
    $this->db->from('bn_eticket_item');
    if(!empty($where)){
    $this->db->where($where);  
    }
    $this->db->where('object_type','eticket');
    $this->db->where_in('id_object',$aids);
    $this->db->group_by('id_customer');
    if($pagesize !=1 && $total !=1)
    {
    $this->db->limit($pagesize,$total);  
    }else{
    $this->db->limit(1);
    }
    $this->db->order_by('get_time','DESC');
        
    $data = $this->db->get()->result_array();
    //查询用户信息
    $arr = $this->user_list_header($data);
    
    foreach ($data as $key => $value) {
      foreach ($arr as $ks => $vs) {
       if($value['id_user']==$vs['id_user']){
         $data[$key]['photo'] = $arr[$ks]['head_image_url'];
        $data[$key]['nick_name'] = urldecode($arr[$ks]['nick_name']);
      }
        } 
    
    $data[$key]['created'] = date('m-d H:i',strtotime($value['created']));
    //$data[$key]['update_time'] = date('m-d H:i',strtotime($value['update_time']));
    if(!isset($data[$key]['nick_name']))
     $data[$key]['nick_name'] = '';
    if(!isset($data[$key]['photo']))
     $data[$key]['photo'] = '/attachment/defaultimg/user.jpg'; 
    
  }
    
  return $data;
  } 
    
    
//普通活动参与者
public function get_act_list($aid='',$where,$pagesize,$total,$order ="created DESC")
    {
        
        $this->db->select('id_join,id_user,cellphone,id_activity,role,created,update_time,identity');
    $this->db->from('bn_community_activity_join');
    $this->db->where($where);
      if($pagesize !=1 && $total !=1)
    {
    $this->db->limit($pagesize,$total);  
    }else{
    $this->db->limit(1);
    }
    $this->db->order_by('role','DESC');
        $this->db->order_by($order);
        $this->db->group_by('cellphone');
        $this->db->group_by('id_user');
    
        $data = $this->db->get()->result_array();
    //查询用户信息
    $arr = $this->user_list_header($data);
    //查询准用户是否被锁定
    //$lock_user = $this->find_lock_user($aid);
    //print_r($lock_user);
    //exit;

    foreach ($data as $key => $value) {
            foreach ($arr as $ks => $vs) {
                if($value['id_user']==$vs['id_user']){
                    $data[$key]['photo'] = $arr[$ks]['head_image_url'];
                    $data[$key]['nick_name'] = urldecode($arr[$ks]['nick_name']);
                }
            }
    
    if(!empty($value['cellphone']) && $value['cellphone'] !=0){
      $num = $this->is_lock($value['cellphone'],$aid);
      if($num >= 3){
      $data[$key]['lock'] = 1;  //此手机已被锁定  
      }
    }
//    foreach ($lock_user as $l => $lv) {
//             //if($value['cellphone']==$lv)
//      $data[$key]['lock'] = 1;  //此手机已被锁定
//           }
    
    $data[$key]['created'] = date('m-d H:i',strtotime($value['created']));
    $data[$key]['update_time'] = date('m-d H:i',strtotime($value['update_time']));
    if(!isset($data[$key]['nick_name']))
     $data[$key]['nick_name'] = '';
    if(!isset($data[$key]['photo']))
     $data[$key]['photo'] = '/attachment/defaultimg/user.jpg'; 
    if(!empty($value['cellphone']) && $_SESSION['userid'] !=$value['cellphone'])
    $data[$key]['cellphone'] = substr($value['cellphone'],0,2).'******'.substr($value['cellphone'],8,3);
  }
    
  return $data;
  }  

//查询用户是否被锁定
public function find_lock_user($aid){
    $time = time()-10*60;
  $time = date('Y-m-d H:i:s',$time);  
  $query = $this->db->select("lock_phone,count('lock_phone') num");
  $this->db->from('bn_lock');
    $this->db->where('object_type','getEticket');
  $this->db->where('id_object',$aid);
  $this->db->where('created >',$time);  
  $data = $this->db->get()->result_array();
  return $data;
  $data = $this->move_array($data);
  return array_count_values($data);
    
}
  
//查询当前用户的电子验证码
public function user_code($aid, $userid ,$type='', $object_type='')
{
 
 if(!empty($type)){
  $aid_all = $this->find_eticket_all($aid);
  foreach ($aid_all as $key => $value) {
  $aids[]=$value['id_eticket'];  
  }    
 }
  
  
 
 $this->db->select('code');
 $this->db->from('bn_eticket_item');
 if(empty($type)){
     $where='';
     if($object_type){
         $where .= '(';
     }
     $where .= 'object_type = "community"';
     if($object_type){
         $where .= ' or object_type = "'.$object_type.'")';
     }
     $where .= ' and id_object = ' . $aid;
     $this->db->where($where);
 }else{
 $this->db->where('object_type','eticket');  
 $this->db->where_in('id_object',$aids);
 }
 
 if($_SESSION['identity'] == 'visitor'){
 $this->db->where('id_open',$userid);    
 }else{
 $this->db->where('id_customer',$userid);  
 }    
 $data = $this->db->get()->result_array();
 //这里电子验证码可以有多个，变为数组方式
 foreach ($data as $key => $value) {
     $arr[] = $value['code'];
 }
 return $arr;  
}    

//查询所有用户头像
public function user_list_header($oid_arr)
{
  foreach ($oid_arr as $key => $value) {
     foreach ($value as $k => $v) {
       if($k=='id_user')
      $arr[]= $v;
     }
      
  } 
 
   $this->db->select('id_user,head_image_url,nick_name');
   $this->db->from('bn_hipigo_user');
   $this->db->where_in('id_user',$arr);
   $data = $this->db->get()->result_array();
     
   //判断用户头像是新上传还是微信用户
   $d_img = 'user.jpg';
   foreach ($data as $key => $value) {
        if(substr($value['head_image_url'], 0,4) != 'http'){
          //取小头像  
    $file = explode('.', $value['head_image_url']);
    $value['head_image_url'] = $file[0].'-small.'.$file[1];
          $data[$key]['head_image_url'] = $this->parse_img_url('head',$value['head_image_url'],$d_img);  //上传图片;
    
        }
     }
   
  return $data;
  
}  
  
  
   //内部关注
    public function bn_att($bid,$oid)
  {
    $data = array('id_business'=>$bid,
                  'id_open'=>$oid,
                  'created'=>date('Y-m-d H:i:s')
    );
       return $this->db->insert('bn_follow',$data);
    
  }
  
  //判断用户是否赞过
    public function is_good($aid,$userid)
  {
    $this->db->select("id_object");
        $this->db->from('bn_appraise');
    $this->db->where('id_object',$aid);
    $this->db->where('id_user',$userid);
    $this->db->where('object_type','community');
    return $data = $this->db->get()->num_rows();
    
  }
  
  //判断当前用户是否已经参加活动
    public function is_att_add($aid,$userid)
  {
       
    $this->db->select("id_activity");
        $this->db->from('bn_community_activity_join');
    $this->db->where('id_activity',$aid);
    $this->db->where('id_user',$userid);
    return $data = $this->db->get()->num_rows();
    
  }
  
  //获取评论回复总数
    public function reply_num($sk_id)
  {
       
    $this->db->select("id_parent");
        $this->db->from('bn_review');
    $this->db->where('bn_review.state',1);
    $this->db->where('bn_review.id_parent',$sk_id);
    return $data = $this->db->get()->num_rows();
    
  }
  
  //查询当前活动是否免费
    public function is_att_free($aid)
  {
    $this->db->select("id_business,object_type,join_price,state,total,join_count,is_spread");
        $this->db->from('bn_community_activity');
    $this->db->where('id_activity',$aid);
    $data = $this->db->get()->result_array();
    return $data;
  }
  
  
  //查询当前活动是否免费
    public function share_att($aid)
  {
    $this->db->select("spread_price");
        $this->db->from('bn_community_spread');
    $this->db->where('id_activity',$aid);
    $data = $this->db->get()->result_array();
    return $data;
  }
   
    //查询用户的商家关注ID
    public function find_id_user($oid,$bid)
    {
        $query = $this->db->query("SELECT id_bn_sub FROM bn_business_subscribe WHERE id_business='$bid' AND id_open='$oid'");
    $result = $query->result_array();
    return $result;
    }
  
  //查询当前活动是否有评论
    public function spking_count($aid)
  {
    $this->db->select("id_object");
        $this->db->from('bn_review');
    $this->db->where('id_object',$aid);
    $this->db->where('id_parent',0);
    $this->db->where('state',1);
    return $data = $this->db->get()->num_rows();
    
  }
  
   //查询当前活动评论内容
    public function spking_users($aid,$limit)
  {
    $this->db->select("id_business,id_customer,id_review,id_parent,id_object,name,content,UNIX_TIMESTAMP(created) as created");
    $this->db->from('bn_review');
    $this->db->where('id_object',$aid);
    $this->db->where('id_parent',0);
    $this->db->where('state',1);
	$this->db->order_by('created','DESC');
	$this->db->limit($limit);
    return $data = $this->db->get()->result_array();
    
  }

   //查询当前活动评论是否有回复
    public function spking_users_review($id_review,$limit)
  {
    $this->db->select("id_business,id_customer,id_review,id_parent,id_object,name,content,UNIX_TIMESTAMP(created) as created");
    $this->db->from('bn_review');
    $this->db->where('id_parent',$id_review);
    $this->db->where('state',1);
	$this->db->order_by('created','DESC');
	$this->db->limit($limit);
    return $data = $this->db->get()->result_array();
    
  }

  
  //查询商家是否开启广告
    public function is_open_ad($bid)
  {
    $this->db->select("key_value");
        $this->db->from('bn_sys_config');
    $this->db->where('id_business',$bid);
    $data = $this->db->get()->result_array();
    return $data;
  }
  
  //查询商家自己的广告
    public function bn_ad_self($bid)
  {
    
  $this->db->select("image_url,link_url");
  $this->db->from('bn_ad');
  $this->db->where('state',1);
  $this->db->where('id_business',$bid);
    $this->db->order_by('weight','DESC');
  $this->db->limit('5');
  $data = $this->db->get()->result_array();
  $d_img = 'banner.jpg'; //广告默认图片
        if($data){
            foreach ($data as $key => $value) {
                foreach ($value as $k => $v) {
                    if($k=='image_url')
                        $data[$key][$k]=$this->add_img_url($v,$d_img);
                }
            }
        }else{
            $this->db->select('*')
                ->from('bn_ad')
                ->where('id_business = 0 and state = 1');
            $data = $this->db->get()->result_array();

            foreach ($data as $key => $value) {
                foreach ($value as $k => $v) {
                    if($k=='image_url')
                        $data[$key][$k]=$this->add_img_url($v,$d_img);
                }
            }
        }

    return $data;
  }
  
  //查询系统广告
    public function ad($limit)
    {
      
    $this->db->select("image_url,link_url");
  $this->db->from('bn_ad');
  $this->db->where('state',1);
  $this->db->where('id_business',0);
    $this->db->order_by('weight','DESC');
  $this->db->limit($limit);
  $data = $this->db->get()->result_array();
  $d_img = 'banner.jpg'; //广告默认图片
  foreach ($data as $key => $value) {
    foreach ($value as $k => $v) {
      if($k=='image_url')
      $data[$key][$k]=$this->add_img_url($v,$d_img);
    }
  }
    return $data;
    
   }
  
  
  //获取对应活动评论和回复
    public function spking_list($bid,$aid,$pagesize,$total,$review_id='')
    {
    $this->load->model('helper_model','helper');  
    $query = $this->db->select("bn_review.id_customer,bn_review.id_review,bn_review.name,bn_review.content,bn_review.created,bn_review.image_url");
  $this->db->from('bn_review');
  $this->db->where('bn_review.id_parent',0);
  $this->db->where('bn_review.id_object',$aid);
  $this->db->where('bn_review.state',1);
  $this->db->where('bn_review.object_type','community');
  $this->db->order_by('bn_review.created','DESC');
  if($pagesize !=0 || $total !=0){
   $this->db->limit($pagesize,$total);  
  }
  if(!empty($review_id)){
  $this->db->where('bn_review.id_review',$review_id);  
  }
  $data = $this->db->get()->result_array();
  //获取评论多张图片
  $spking_img = $this->spking_img_more($data);
  //print_r($spking_img);
  //exit;
  $d_img = 'activity.jpg';   //评论默认图片
  $u_img = 'user.jpg';       //用户头像地址
  $arr = $this->user_list_header($data);
  foreach ($data as $key => $value) {
    foreach ($arr as $k_user => $v_user) {
       if($value['id_customer'] == $v_user['id_user'])
      $data[$key]['head_image_url'] = $arr[$k_user]['head_image_url'];
      }
    //评论图片(多图)
    foreach ($spking_img as $mk => $mv) {
             if($value['id_review']==$mv['id_object']){
              $img_url = $this->parse_imgs_url('community',$mv['image_url']);
        
        if(!empty($img_url)){
         $data[$key]['imgs'][] = $img_url;  
         } 
        }
            }
      foreach ($value as $k => $v) {
            //原始图片  
      if($k=='image_url' && !empty($v))        
      $data[$key][$k]=$this->parse_img_url('community',$v,$d_img);  //上传图片
      if($k=='created')                                               //回复时间
      $data[$key][$k] = $this->helper->format_date($v);
      if($k=='name')
      $data[$key][$k] = urldecode($v);
     }
     
    if(empty($data[$key]['head_image_url']))
      $data[$key]['head_image_url'] = '/attachment/defaultimg/'.$u_img;
    if(!isset($data[$key]['imgs']))
      $data[$key]['imgs'] = '';
  }
    if($pagesize ==0 && $total ==0 && !empty($review_id))
  {
     return $this->move_array($data);  
  }
    return $data;
    }

   //获取评论多张图片
    public function spking_img_more($aid_arr){
    foreach ($aid_arr as $key => $value) {
     foreach ($value as $k => $v) {
       if($k=='id_review')
      $arr[]= $v;
     }
   }
     $this->db->select('id_object,image_url'); 
     $this->db->from('bn_business_attachment');
     $this->db->where('object_type','review');
     $this->db->where('attachment_type','image');   
     $this->db->where_in('id_object',$arr);  
     $data = $this->db->get()->result_array();
     return $data;

}
  
  //活动对应的所有回复
    public function reply_list($aid)
    {
    $this->load->model('helper_model','helper');  
    $query = $this->db->select("bn_review.id_open,bn_review.id_parent,bn_review.name,bn_review.content,bn_review.created");
  $this->db->from('bn_review');
  $this->db->where('bn_review.id_parent !=',0);
  $this->db->where('bn_review.id_object',$aid);
  $this->db->where('bn_review.state',1);
  $this->db->order_by('bn_review.created','DESC');
  $data = $this->db->get()->result_array();
  foreach ($data as $key => $value) {
    foreach ($value as $k => $v) {
      if($k=='created')
      $data[$key][$k] = $this->helper->format_date($v);
      if($k=='name')
      $data[$key][$k] = urldecode($v);
      
    }
  }
  return $data;
    }
  
  //评论对应的所有回复
    public function reply_list_page($aid,$pagesize,$total,$review_id)
    {
    $this->load->model('helper_model','helper');
    $query = $this->db->select("bn_review.id_review,bn_review.id_open,bn_review.id_parent,bn_review.name,bn_review.content,bn_review.created");
  $this->db->from('bn_review');
  $this->db->where('bn_review.id_parent !=',0);
  $this->db->where('bn_review.id_object',$aid);
  $this->db->where('bn_review.id_parent',$review_id);
  $this->db->where('bn_review.state',1);
  $this->db->order_by('bn_review.created','DESC');
  $this->db->limit($pagesize,$total);
  $data = $this->db->get()->result_array();
  foreach ($data as $key => $value) {
    foreach ($value as $k => $v) {
      if($k=='created')
      $data[$key][$k] = $this->helper->format_date($v);
      if($k=='name')
      $data[$key][$k] = urldecode($v);
    }
  }
  return $data;
    }
  
  //活动赞(数量加1)
    public function good($aid)
  {
    $this->db->query("UPDATE bn_community_activity SET appraise_count = appraise_count+1 WHERE id_activity='$aid'");
     }
  //更新成员的最后讨论日期
    public function update_user_spking($aid,$userid)
  {
    $date = date('Y-m-d H:i:s');
    $this->db->query("UPDATE bn_community_activity_join SET update_time ='$date'  WHERE id_activity='$aid' AND id_user='$userid'");
     }
  
  
  //取消活动赞(数量减1)
    public function good_sub($aid)
  {
    return $this->db->query("UPDATE bn_community_activity SET appraise_count = appraise_count-1 WHERE id_activity='$aid' AND appraise_count>=1");
     }
  
  //删除暂记录
    public function good_del($aid,$userid)
  {
    if($userid !=0){
     $where = array('object_type'=>'community','id_object'=>$aid,'id_user'=>$userid);
     return $this->db->delete('bn_appraise',$where);  
    }else{
     $query = $this->db->select("id_appraise");
     $this->db->from('bn_appraise');
         $this->db->where('id_object',$aid);
     $this->db->where('object_type','community');
       $this->db->where('id_user',0);
     $this->db->limit(1);
         $data = $this->db->get()->result_array();
         $where = array('object_type'=>'community','id_object'=>$aid,'id_appraise'=>$data[0]['id_appraise']);
     return $this->db->delete('bn_appraise',$where);
    }
     
  }
     
  //判断此手机号码是否参加过该活动
  public function find_phone($aid,$phone){
  $query = $this->db->select("cellphone");
  $this->db->from('bn_community_activity_join');
    $this->db->where('id_activity',$aid);
  $this->db->where('cellphone',$phone);
  return $data = $this->db->get()->num_rows();  
  
  }
  
  //免费活动只能参加一次
  public function is_att_once($aid,$userid,$phone){
    
  $query = $this->db->select("id_activity");
  $this->db->from('bn_community_activity_join');
    $this->db->where('id_activity',$aid);
  if(empty($userid) || $_SESSION['identity'] =='visitor'){
  $this->db->where('cellphone',$phone);  
  }
  if(!empty($userid) && $_SESSION['identity'] !='visitor'){
  $this->db->where('id_user',$userid);  
  }
  return $data = $this->db->get()->num_rows();  
  
  }
  
  //判断用户是否参加过该活动
  public function find_userid($aid,$userid){
  $query = $this->db->select("cellphone");
  $this->db->from('bn_community_activity_join');
    $this->db->where('id_activity',$aid);
  $this->db->where('id_user',$userid);
  return $data = $this->db->get()->num_rows();  
  
  }
  
  //判断此手机号码是否参加过该活动
  public function find_phones($id_join){
  $query = $this->db->select("cellphone");
  $this->db->from('bn_community_activity_join');
    $this->db->where('id_join',$id_join);
    $data = $this->db->get()->result_array();
  return $data[0]['cellphone'];  
  
  }
  
  //判断此手机号码是否参加过该活动
  public function is_lock($phone,$aid){
  $time = time()-10*60;
  $time = date('Y-m-d H:i:s',$time);  
  $query = $this->db->select("lock_phone");
  $this->db->from('bn_lock');
    $this->db->where('object_type','getEticket');
  $this->db->where('lock_phone',$phone);
  $this->db->where('id_object',$aid);
  $this->db->where('created >',$time);
    return $data = $this->db->get()->num_rows();
  }

   //判断此手机号码是否参加过该活动
  public function add_lock($phone,$aid){
  $data = array(
    'object_type'=>'getEticket',
    'lock_phone'=>$phone,
    'created'=>date('Y-m-d H:i:s'),
    'id_object'=>$aid
    );
  return $this->db->insert('bn_lock',$data);
    
  }
  
  
  
  //参与数(减1)
    public function update_sub($aid)
   {
    $this->db->query("UPDATE bn_community_activity SET join_count = join_count-1 WHERE id_activity='$aid'");
     }
     
     //删除活动对应电子卷
    public function eticket_del($aid,$oid)
  {
    
      $where = array('id_object'=>$aid,'id_open'=>$oid,'object_type'=>'community');
        return $this->db->delete('bn_eticket_item',$where); 
    }
     
     
  
  //更新活动表记录（参与者加一）
    public function update_att_activity($aid)
  {
    return $this->db->query("UPDATE bn_community_activity SET join_count = join_count+1 WHERE id_activity='$aid'");
    }
  
  //更新活动表记录（浏览次数加一）
    public function check_num_update($aid)
  {
    return $this->db->query("UPDATE bn_community_activity SET check_num = check_num+1 WHERE id_activity='$aid'");
    }
  
  //插入活动赞表(增加一条赞)
    public function good_add($aid,$userid)
  {
    $data = array('object_type'=>'community',
    'id_object'=>$aid,
    'id_user'=>$userid,
    'created'=>date('Y-m-d H:i:s'));
     $this->db->insert('bn_appraise',$data);
  }

    //免费活动，直接参加（插入活动参与表）
     public function add_att_user($aid,$id_user='',$phone='', $condetion = '')
  {
    $identity = empty($_SESSION['identity']) ? 'visitor' : $_SESSION['identity'];
    $data = array(
    'id_user'=>$id_user,
    'id_activity'=>$aid,
    'created'=>date('Y-m-d H:i:s'),
    'update_time'=>date('Y-m-d H:i:s'),
    'cellphone'=>$phone,
    'identity' =>$identity,
    'role'=>1
    );
    
    if($condetion) {
      $data['condetion'] = serialize($condetion);
    }
    
    return $this->db->insert('bn_community_activity_join',$data);
  }
    
  //查询活动详情信息
  
    public function activity_detail($aid)
    {
    $this->load->model('string_model','string');
    if(TPL_DEFAULT == 'mobile'){
        $query = $this->db->select(
            'bn_community_activity.id_activity, preferential_price,name, created,is_spread, type,
            bn_community_activity.id_business, bn_community_activity.object_type, content, total, posters_url, join_price,
            join_count, appraise_count, review_count,
            start_date, end_date, addr,GROUP_CONCAT(CONCAT(cat.id_tag)) as id_tag');//,GROUP_CONCAT(CONCAT(ba.image_url)) as image_url');

        $this->db->from('bn_community_activity');
        $this->db->join('bn_community_activity_tag as cat',
            'bn_community_activity.id_activity = cat.id_activity', 'left');
//        $this->db->join('bn_business_attachment as ba',
//            'bn_community_activity.id_activity = ba.id_object and ba.object_type = \'community\'', 'left');
    }else{
    $query = $this->db->select(
          'bn_community_activity.id_activity, name, created,is_spread, type, 
          id_business, object_type, content, total, posters_url, join_price,
          join_count, appraise_count, review_count, 
          id_resource, bn_community_activity.start_date, bn_community_activity.end_date, bn_community_activity.addr, id_tag, join_condetion');
    
    $this->db->from('bn_community_activity');
    $this->db->join('bn_community_activity_extends as cae', 
              'cae.id_activity = bn_community_activity.id_activity', 'left');
    }
    $this->db->where('bn_community_activity.id_activity', $aid);
    $this->db->where('state >=', 0);
    $data = $this->db->get()->result_array();

  $data = $this->move_array($data);
  $data['surplus'] = $data['total'];// $data['total'] - $data['join_count'];
  //当总数设置为0时为不限制
  if($data['total']==-1)
    $data['surplus'] = -1;
  $d_img = 'activity.jpg'; //活动默认图片
  $data['posters_url']=$this->parse_img_url('community',$data['posters_url'],$d_img);
  //过滤掉图片标签
  $content_cut =  preg_replace("/<img.*?>/si","",$data['content']);
  $data['content_cut'] = $this->string->substrCn($content_cut,0,60,$suffix = true, $charset = "utf-8");
  
  return $data;
    }
  
  /**
   * 获取活动扩展信息
   *
   * @param id_activity  活动ID
   * @version 2.0
   * @author Jamai
   */
  public function activity_extends_detail($id_activity)
  {
    $query = $this->db->select('*')
            ->from('bn_community_activity_extends')
            ->where('id_activity = ' . $id_activity);
    
    return $this->move_array($this->db->get()->result_array());
  }
 
  
  //查询活动参与人数
    public function find_join_num($aid){
    $this->db->select('id_join');
  $this->db->from('bn_community_activity_join');
    $this->db->where('id_activity',$aid);
        $this->db->group_by('cellphone');
        $this->db->group_by('id_user');
  return $data = $this->db->get()->num_rows();    
    }
  
    //查询活动参与用户
    public function find_join_users($aid,$limit){
    $this->db->select('*');
  $this->db->from('bn_community_activity_join');
    $this->db->where('id_activity',$aid);
        $this->db->group_by('cellphone');
        $this->db->group_by('id_user');
		$this->db->limit($limit);
  return $data = $this->db->get()->result_array();    
    }
  
  //查询世界杯活动获奖者
  public function find_win_num($aid){
  $aid_all = $this->find_eticket_all($aid);
  if(empty($aid_all))
  return $data='';
  foreach ($aid_all as $key => $value) {
   $aids[]=$value['id_eticket'];  
  }
  
  
  $this->db->select('id_customer');
  $this->db->from('bn_eticket_item');
  
  $this->db->where_in('id_object',$aids);
    $this->db->where('object_type','eticket');
  $this->db->group_by('id_customer');
  return $data = $this->db->get()->num_rows();  
  }

   //查询获得世界杯活动优惠劵用户数量
   public function find_win_all(){
  $aid_all = $this->find_eticket_all();
  if(empty($aid_all))
  return $data='';
  foreach ($aid_all as $key => $value) {
   $aids[]=$value['id_eticket'];  
  }
  
  
  $this->db->select('id_customer');
  $this->db->from('bn_eticket_item');
  $this->db->where_in('id_object',$aids);
    $this->db->where('object_type','eticket');
  $this->db->group_by('id_customer');
  return $data = $this->db->get()->num_rows();  
  }
  //查找更多验证码
  public function find_code_mores($aid){
  $userid = $_SESSION['userid'];  
  $this->db->select('code');
  $this->db->from('bn_eticket_item');
  $this->db->where('object_type','community');
  $this->db->where('id_object',$aid);
  if($_SESSION['identity'] == 'visitor'){
    $this->db->where('id_open',$userid);    
    }else{
    $this->db->where('id_customer',$userid);  
    }  
  return $data = $this->db->get()->result_array();
  }
  
  //查询电子卷id
  public function find_eticket_all($aid=''){
  $this->db->select('id_eticket');
  $this->db->from('bn_eticket_bind');
  if(!empty($aid)){
  $this->db->where('id_activity',$aid);  
  }else{
  $this->db->group_by('id_eticket');  
  }
  
  return $data = $this->db->get()->result_array();
  }
  
  //查询商家名称
  public function find_business_name($bid){
  $this->db->select('name');
  $this->db->from('bn_business');  
  $this->db->where('id_business',$bid);
  $data = $this->db->get()->result_array();
  return $data[0]['name'];
  }
  
  //获取当前商家活动的配置(普通商家)
    public function community_list($bid,$total,$pagesize,$screen_width)
    {
    $this->load->model('string_model','string');
    $query = $this->db->select("id_business,id_activity,name,posters_url,join_price,join_count,review_count,appraise_count");
  $this->db->from('bn_community_activity');
    $this->db->where('id_business',$bid);
  $this->db->where('type != 2 and state >=',0);
  $this->db->order_by('created','DESC');
  $this->db->limit($pagesize,$total);
  $data = $this->db->get()->result_array();
  //查询活动参与人数
  $add_user_nums = $this->add_user_nums($data);
  //查询多张图片
  $imgs = $this->img_more($data);
  $nk_names = $this->find_join_name($data);
  $d_img = 'activity.jpg'; //活动默认图片
        $length = 20;
        if($screen_width <= 320){
            $length = 20;
        }elseif($screen_width > 320 && $screen_width <= 800){
            $length = 25;
        }elseif($screen_width > 800){
            $length = 30;
        }

  foreach ($data as $key => $value) {
    //活动参与人数
      foreach ($add_user_nums as $k_num => $v_num) {
       if($v_num['id_activity'] == $value['id_activity'])
       $data[$key]['add_num'][]= 1;  
       }
    
    foreach ($value as $k => $v) {
      if($k=='posters_url')
      $data[$key][$k]=$this->parse_img_url('community',$v,$d_img);
      if($k=='name')
      $data[$key][$k]=$this->string->substrCn($v,0,$length,$suffix = true, $charset = "utf-8");
      //分享用户
//      if($k=='id_activity' && $_SESSION['aid'] ==$v && $data[$key]['join_price']>0){
//        //分享用户价格加1
//        $pay_share = $_SESSION['userid'] != $_SESSION['suserid'] && !empty($_SESSION['userid']) ? $data[$key]['join_price']+PAY_SHARE : $data[$key]['join_price'];
//              $data[$key]['join_price'] = $pay_share;
//            }
             foreach ($nk_names as $nk => $nk_v) {
             if($k=='id_activity' && $v==$nk_v['id_activity'])
       $data[$key]['nick_more'] = urldecode($nk_v['nick_more']); 
            }
       
       foreach ($imgs as $mk => $mv) {
             if($k=='id_activity' && $v==$mv['id_object']){ 
        $img_url = $this->parse_imgs_url('_editor',$mv['image_url']);
        if(!empty($img_url)){
         $data[$key]['imgs'][] = $img_url;  
         } 
        }
            }
    }
    if(!isset($data[$key]['nick_more']))
      $data[$key]['nick_more']='';
    if(!isset($data[$key]['imgs']))
      $data[$key]['imgs']='';
     if(!isset($data[$key]['add_num'])){
       $data[$key]['add_num'] = 0;
     }else{
       $data[$key]['add_num'] = count($data[$key]['add_num']);
     }
  }

    return $data;
    }
  
  /**
   * 基于商户分类获取活动列表
   * 
   * @param $where Array 查询条件
   * @param $options 
   *          order  排序
   *          offset 偏移量
   *          limit  读取条数
   * 
   * @author Jamai
   * @version 2.1
   **/
  public function byBuinessTypeCommunity($where = array(), $options = array(), $row = false)
  {
    $this->db->select('c.*', false)
        ->from($this->table . ' AS c')
        ->join($this->table_business . ' AS b ', 
                'b.id_business = c.id_business OR b.id_business = c.id_business_source ', 'left')
        ->where('c.state > 0 ');
    
    if($where)
      $this->db->where($where);
    
    if($options) {
      if($options['order'])
        $this->db->order_by($options['order']);
      
      if($options['offset'] && $options['limit'])
        $this->db->limit($options['limit'], $options['offset']);
      else if($options['limit'])
        $this->db->limit($options['limit']);
    }
    
    $result = $this->db->get()->result_array();
    // debug($this->db->last_query());
    return $result;
  }
  

 //获取当前商家活动的配置(hipigo)
  public function hipigo_community_list_kill($total,$pagesize,$screen_width,$state=0)
  {
    $this->load->model('string_model','string');  
    $query = $this->db->select("ac.start_date,ac.end_date as end_time,ac.total,ac.preferential_price,ac.id_activity,ac.name,ac.posters_url,
    ac.join_price,ac.join_count,ac.review_count,ac.appraise_count,
    b.name as bn_name,ac.id_business, hu.nick_name, ac.object_type, acex.end_date,acex.id_tag")
          ->from('bn_community_activity AS ac')
          ->join('bn_community_activity_extends as acex', 
                  'acex.id_activity = ac.id_activity', 'left')
          ->join('bn_business as b','b.id_business=ac.id_business','left')
          ->join('bn_hipigo_user AS hu', 'hu.id_user=ac.id_business','left')
          ->where('ac.type = 2 and ac.state >=', $state)
          ->order_by('ac.created','DESC')
          ->limit($pagesize, $total);
  
  $data = $this->db->get()->result_array();
  //查询活动参与人数
  $add_user_nums = $this->add_user_nums($data);
  //查询多张图片
  $imgs = $this->img_more($data);
  //查询用户名
  $nk_names = $this->find_join_name($data);
  
  $d_img = 'activity.jpg'; //活动默认图片
      $length = 20;
      if($state == 0){
          if($screen_width <= 320){
              $length = 20;
          }elseif($screen_width > 320 && $screen_width <= 800){
              $length = 25;
          }elseif($screen_width > 800){
              $length = 30;
          }
      }
  foreach ($data as $key => $value) {
    //活动参与人数
    foreach ($add_user_nums as $k_num => $v_num) {
      if($v_num['id_activity'] == $value['id_activity'])
        $data[$key]['add_num'][]= 1;  
    }
    
    foreach ($value as $k => $v) {
      if($k=='posters_url')
      $data[$key][$k]=$this->parse_img_url('community',$v,$d_img);
      //if($k=='name' && $state == 0)
       // $data[$key][$k]=$this->string->substrCn($v,0,$length,$suffix = true, $charset = "utf-8");
      //分享用户
//      if($k=='id_activity' && $_SESSION['aid'] ==$v && $data[$key]['join_price']>0){
//        //分享用户价格加1
//        $pay_share = $_SESSION['userid'] != $_SESSION['suserid'] && !empty($_SESSION['userid']) ? $data[$key]['join_price']+PAY_SHARE : $data[$key]['join_price'];
//              $data[$key]['join_price'] = $pay_share;
//            }
            foreach ($nk_names as $nk => $nk_v) {
             if($v==$nk_v['id_activity'])
       $data[$key]['nick_more'] = urldecode($nk_v['nick_more']); 
            }
      
      foreach ($imgs as $mk => $mv) {
             if($k=='id_activity' && $v==$mv['id_object']){ 
        $img_url = $this->parse_imgs_url('_editor',$mv['image_url']);
        if(!empty($img_url)){
        $data[$key]['imgs'][] = $img_url;  
         } 
        }
            }
      
        }
    
     if(!isset($data[$key]['nick_more']))
     $data[$key]['nick_more']='';
     if(!isset($data[$key]['imgs']))
     $data[$key]['imgs']='';
     if(!isset($data[$key]['add_num'])){
       $data[$key]['add_num'] = 0;
     }else{
       $data[$key]['add_num'] = count($data[$key]['add_num']);
     }
     
  }
    return $data;
  }


  //获取当前商家活动的配置(hipigo)
  public function hipigo_community_list($total,$pagesize,$screen_width,$state=0)
  {
    $this->load->model('string_model','string');  
    $query = $this->db->select("ac.id_activity,ac.name,ac.posters_url,
    ac.join_price,ac.join_count,ac.review_count,ac.appraise_count,
    b.name as bn_name,ac.id_business, hu.nick_name, ac.object_type, acex.end_date,acex.id_tag")
          ->from('bn_community_activity AS ac')
          ->join('bn_community_activity_extends as acex', 
                  'acex.id_activity = ac.id_activity', 'left')
          ->join('bn_business as b','b.id_business=ac.id_business','left')
          ->join('bn_hipigo_user AS hu', 'hu.id_user=ac.id_business','left')
          ->where('ac.type != 2 and ac.state >=', $state)
          ->order_by('ac.created','DESC')
          ->limit($pagesize, $total);
  
  $data = $this->db->get()->result_array();
  //查询活动参与人数
  $add_user_nums = $this->add_user_nums($data);
  //查询多张图片
  $imgs = $this->img_more($data);
  //查询用户名
  $nk_names = $this->find_join_name($data);
  
  $d_img = 'activity.jpg'; //活动默认图片
      $length = 20;
      if($state == 0){
          if($screen_width <= 320){
              $length = 20;
          }elseif($screen_width > 320 && $screen_width <= 800){
              $length = 25;
          }elseif($screen_width > 800){
              $length = 30;
          }
      }
  foreach ($data as $key => $value) {
    //活动参与人数
    foreach ($add_user_nums as $k_num => $v_num) {
      if($v_num['id_activity'] == $value['id_activity'])
        $data[$key]['add_num'][]= 1;  
    }
    
    foreach ($value as $k => $v) {
      if($k=='posters_url')
      $data[$key][$k]=$this->parse_img_url('community',$v,$d_img);
      if($k=='name' && $state == 0)
        $data[$key][$k]=$this->string->substrCn($v,0,$length,$suffix = true, $charset = "utf-8");
      //分享用户
//      if($k=='id_activity' && $_SESSION['aid'] ==$v && $data[$key]['join_price']>0){
//        //分享用户价格加1
//        $pay_share = $_SESSION['userid'] != $_SESSION['suserid'] && !empty($_SESSION['userid']) ? $data[$key]['join_price']+PAY_SHARE : $data[$key]['join_price'];
//              $data[$key]['join_price'] = $pay_share;
//            }
            foreach ($nk_names as $nk => $nk_v) {
             if($v==$nk_v['id_activity'])
       $data[$key]['nick_more'] = urldecode($nk_v['nick_more']); 
            }
      
      foreach ($imgs as $mk => $mv) {
             if($k=='id_activity' && $v==$mv['id_object']){ 
        $img_url = $this->parse_imgs_url('_editor',$mv['image_url']);
        if(!empty($img_url)){
        $data[$key]['imgs'][] = $img_url;  
         } 
        }
            }
      
        }
    
     if(!isset($data[$key]['nick_more']))
     $data[$key]['nick_more']='';
     if(!isset($data[$key]['imgs']))
     $data[$key]['imgs']='';
     if(!isset($data[$key]['add_num'])){
       $data[$key]['add_num'] = 0;
     }else{
       $data[$key]['add_num'] = count($data[$key]['add_num']);
     }
     
  }
    return $data;
  }

//获取活动多张图片
public function img_more($aid_arr){
 foreach ($aid_arr as $key => $value) {
     foreach ($value as $k => $v) {
       if($k=='id_activity')
      $arr[]= $v;
     }
   }
$this->db->select('id_object,image_url'); 
$this->db->from('bn_business_attachment');
$this->db->where('object_type','community');
$this->db->where('attachment_type','image');   
$this->db->where_in('id_object',$arr);  
$data = $this->db->get()->result_array();
return $data;

}

//查询活动参与人数
public function add_user_nums($aid_arr){
 foreach ($aid_arr as $key => $value) {
     foreach ($value as $k => $v) {
       if($k=='id_activity')
      $arr[]= $v;
     }
   }
$this->db->select('id_activity'); 
$this->db->from('bn_community_activity_join');
$this->db->where_in('id_activity',$arr);  
$data = $this->db->get()->result_array();
return $data;

}


//获取当前活动参加者姓名
public function find_join_name($aid_arr){
 foreach ($aid_arr as $key => $value) {
     foreach ($value as $k => $v) {
       if($k=='id_activity')
      $arr[]= $v;
     }
   } 
 
   $this->db->select('id_activity,nick_name as nick_more');
   $this->db->from('bn_hipigo_user');
   $this->db->join('bn_community_activity_join','bn_community_activity_join.id_user=bn_hipigo_user.id_user');
   $this->db->where_in('bn_community_activity_join.id_activity',$arr);
   //$this->db->group_by('bn_hipigo_user.nick_name');
   $this->db->order_by('bn_community_activity_join.update_time');
   $data = $this->db->get()->result_array();
   //判断用户头像是新上传还是微信用户
   return $data;
  
}

public function search_num($title)
    {
     
    $query = $this->db->select("id_activity");
  $this->db->from('bn_community_activity');
  $this->db->like('name',$title);
    $this->db->where('state >=',0);
  $data = $this->db->get()->num_rows();
  return $data;
    }

  
//搜索活动
public function search_list($title,$total,$pagesize,$screen_width,$type=1)
    {
    $this->load->model('string_model','string');
        $this->db->from('bn_community_activity');
    if($type == 1){//id_business对应的bn_business表的id_business
        $query = $this->db->select("bn_community_activity.id_activity,bn_community_activity.object_type,bn_community_activity.name,bn_community_activity.posters_url,
    bn_community_activity.join_price,bn_community_activity.join_count,bn_community_activity.review_count,bn_community_activity.appraise_count,
    bn_business.name as bn_name,bn_business.id_business");
        $this->db->join('bn_business','bn_business.id_business=bn_community_activity.id_business','left');
        $this->db->where('bn_business.state',1);
        $this->db->where('bn_community_activity.object_type','community');
    }elseif($type == 2){//id_business对应的是bn_hipigo_user表的id_user
        $query = $this->db->select("bn_community_activity.id_activity,bn_community_activity.object_type,bn_community_activity.name,bn_community_activity.posters_url,
    bn_community_activity.join_price,bn_community_activity.join_count,bn_community_activity.review_count,bn_community_activity.appraise_count,
    bn_hipigo_user.nick_name as bn_name,bn_community_activity.id_business");
        $this->db->join('bn_hipigo_user','bn_hipigo_user.id_user=bn_community_activity.id_business','left');
        $this->db->where('bn_community_activity.object_type','user');
    }
        $this->db->where('bn_community_activity.state >=',0);
        $this->db->like('bn_community_activity.name',$title);
        $this->db->order_by('bn_community_activity.created','DESC');
        $this->db->limit($pagesize,$total);
        $data = $this->db->get()->result_array();

        $d_img = 'activity.jpg'; //活动默认图片
        $length = 20;
        if($screen_width <= 320){
            $length = 20;
        }elseif($screen_width > 320 && $screen_width <= 800){
            $length = 25;
        }elseif($screen_width > 800){
            $length = 30;
        }
      foreach ($data as $key => $value) {
        foreach ($value as $k => $v) {
          if($k=='posters_url'){
              if($type == 1){//id_business对应的bn_business表的id_business
                  $data[$key][$k]=$this->parse_img_url('community',$v,$d_img);
              }elseif($type == 2){//id_business对应的是bn_hipigo_user表的id_user
                  $data[$key][$k]='';
              }
          }
          if($k=='name'){
          $data[$key][$k]=$this->string->substrCn($v,0,$length,$suffix = true, $charset = "utf-8");
          //高亮显示搜索关键字
          $data[$key][$k]=str_replace($title, '<em>'.$title.'</em>', $v);
          }

          //分享用户
          if($k=='id_activity' && $_SESSION['aid'] ==$v && $data[$key]['join_price']>0){
            //分享用户价格加1
            $pay_share = $_SERVER['userid'] != $_SESSION['suserid'] ? $data[$key]['join_price']+PAY_SHARE : $data[$key]['join_price'];
                  $data[$key]['join_price'] = $pay_share;
                }
        }
      }
    return $data;
    }
  
  
/**
 * 参加社区活动，产生一张电子验证码
 * @param array $data
 * @return boolean
 */
public function set_eticket($aid,$userid,$bid,$sid,$phone='', $condetion = '') {
//      $userid = 0;
//    //$identity = !empty($_SESSION['identity']) ? 'visitor' : $_SESSION['identity'];
//
//    if($phone ==''){
//      if($_SESSION['identity'] =='visitor'){
//      $phone = $_SESSION['userid'];
//      }else{
//      $userid = empty($_SESSION['userid']) ? 0 : $_SESSION['userid'];
//      }
//    }

    $userid = 0;
    if($phone!='' && $_SESSION['identity'] =='visitor'){
        $phone = $_SESSION['userid'];
    }else{
        $userid = empty($_SESSION['userid']) ? 0 : $_SESSION['userid'];
    }

    $where = 'id_activity = ' . $aid;
    $comm_info = $this->get_community_info($where);

    $join_c = $comm_info[0]['join_count'];
    $total_c = $comm_info[0]['total'];
    $join = $join_c + 1;

    if(($total_c-1) >= 0 || $total_c <= -1){
        $code = rand(10000000,99999999);
    //事务开始
        $this -> db -> query('BEGIN');
        /*
        $eticket_info = array(
          'id_business' => $bid,
          'id_shop'     => $sid,
          'object_type' => $comm_info[0]['object_type'],
          'id_object'   => $aid,
          'code'        => $code,
          'id_open'     => $phone,
          'id_customer' => $userid,
          'get_time'    => date('Y-m-d H:i:s'),
          'state'       => 1
          );
        */
        //By Jamai 修改 
        $eticket_info = array(
          'id_business' => $comm_info[0]['id_business'],
          'id_shop'     => $sid,
          'object_type' => $comm_info[0]['object_type'],
          'id_object'   => $comm_info[0]['id_activity'],
          'code'        => $code,
          'id_open'     => $phone,
          'id_customer' => $userid,
          'get_time'    => date('Y-m-d H:i:s'),
          'state'       => 1
          );
        $res1 = $this -> db -> insert('bn_eticket_item', $eticket_info);
        $res2 = $this->add_att_user($aid, $userid, $phone, $condetion);//插入活动参与表
    //    $res3 = $this->update_att_activity($aid); //更新活动表记录（参与者加一）

        $tot = 0;
        if($total_c <= -1){
            $tot = -1;
        }elseif($total_c >= 1){
            $tot = $total_c - 1;
        }

        $data_c = array(
            'join_count'=>$join,
            'total'=>$tot
        );
        $res3 = $this->update_community($data_c,$where);//更新活动表记录（参与者加一）

        if ($res1 && $res2 && $res3) {
          //提交事务
          $this -> db -> query("COMMIT");
          return $code;
        } else {
          $this -> db -> query("ROLLBACK");
          return FALSE;
        }
    }
    else
        return FALSE;
    //事务结束
    //$this -> db -> query("END");
}


    //寻找图片路径
    public function parse_imgs_url($file,$img){
        if($img!=''){
            $imgArray = explode('.',$img);
            $name = $imgArray[0];
            $strArray=str_split(substr($name, 0,4));
            $str=join("/",$strArray);
      //$arr = explode('.', $img);
      //$img = $arr[0].'-small.'.$arr[1];
            $img_path = $_SERVER['DOCUMENT_ROOT'].'/attachment/business/'.$file.'/'.$str.'/'.$img;
      $arr = explode('.', $img_path);
      $img_path = $arr[0].'-small.'.$arr[1];
      if(file_exists($img_path)){
                return '/attachment/business/'.$file.'/'.$str.'/'.$img;
            }else{
                return '';
            }
        }else{
            //默认图片
            return '';
    }
        
    }
    
    
    //寻找图片路径
    public function parse_img_url($file,$img,$d_img){
        if($img!=''){
            $imgArray = explode('.',$img);
            $name = $imgArray[0];
            $strArray=str_split(substr($name, 0,4));
            $str=join("/",$strArray);
            $img_path = $_SERVER['DOCUMENT_ROOT'].'/attachment/business/'.$file.'/'.$str.'/'.$img;
            if(file_exists($img_path)){
                return '/attachment/business/'.$file.'/'.$str.'/'.$img;
            }
            else{
              return '';
                //return '/attachment/defaultimg/'.$d_img;
            }
        }else{
          return '';
            //默认图片
            //return '/attachment/defaultimg/'.$d_img;
        }
        
    }
  
  //寻找图片路径(广告位)
    public function add_img_url($img,$d_img){
        if($img!=''){
            $imgArray = explode('.',$img);
            $name = $imgArray[0];
            $strArray=str_split(substr($name,0,4));
            $str=join("/",$strArray);
            $img_path = $_SERVER['DOCUMENT_ROOT'].'/attachment/business/business_ad/'.$str.'/'.$img;
            if(file_exists($img_path)){
                return '/attachment/business/business_ad/'.$str.'/'.$img;
            }else{
                return '/attachment/defaultimg/'.$d_img;
            }
        }else{
            //默认图片
            return '/attachment/defaultimg/'.$d_img;
        }
        
    }

//替换内容中的表情
       public function replace_smile($field){
          if(strpos($field, '[') !== FALSE AND preg_match_all('/\[(.*?)\]/', $field, $matches)){
            $smile = _get_smile_array();
            //return $matches;
            for ($i = 0; $i < count($matches['0']); $i++)
      {
        if ($matches['1'][$i] != '')
        {
          foreach( $smile as $key =>$val){
             if($key == $matches['0'][$i]){
                           $field = str_replace($matches['0'][$i],"<img class='add_face' src = '/smile/{$val}'/>",$field);
             }
                    }
        }
      }
          }
          return $field;
       }

//将二维数组转换成一维
   function move_array($arr){
      foreach($arr as $key=>$val){
      foreach($val as $key=>$v){
           $array[$key] = $v;
       }
     }
     return $array;
   }
   /**
    * @author zhoushuai
    * @获取商户。拥有的电子卷
    */
    public function get_business_eticket($where){
        $this->db->select('id_eticket,name')->from('bn_eticket')->where($where);
        $data = $this->db->get()->result_array();
        //echo $this->db->last_query();
        return $data;
    }
    /**
     * @author zhoushuai
     * @给活动绑定电子卷
     * @param aid int 活动id
     * @param eid array 电子卷id
     */
    public function insert_eticket_bind($aid,$eid){
        $data = array();
        for($i=0;$i<count($eid);$i++){
            $etickets=array('id_eticket'=>$eid[$i],'id_activity'=>$aid);
            $result= $this->query__eticket_bind($etickets);
            if(empty($result)){
                $data[$i]= $etickets ;
            }
        }
        //echo $this->db->last_query();
        if(empty($data)){
            return true;
        }
        return $this->db->insert_batch('bn_eticket_bind', $data);
    }
    
    /**
     * @查询电子卷绑定表
     */
     
    public function query__eticket_bind($where){
        $this->db->select('*')->from('bn_eticket_bind')->where($where);
        $data = $this->db->get()->result_array();
        return $data;
    }
    
    /**
     * 添加资源扩展
     */
    public function insert_activity_extends($data) 
    {
      $this->db->insert($this->table_extends, $data);
      return $this->db->insert_id();
    }

    /**
     * zxx
     * 编辑资源扩展
     */
    public function update_activity_extends($data,$where)
    {
        $re = $this->db->update($this->table_extends, $data,$where);
        return $re;
    }


    /*
     * zxx
     * 添加用户发布活动使用的资源信息
     */
    public function insert_community_resource($data){
        $this->db->insert($this->table_activity_resource, $data);
        return $this->db->insert_id();
    }

    /*
     * zxx
     * 更新活动使用的资源信息
     */
    public function update_community_resource($data,$where){
        $re = $this->db->update($this->table_activity_resource, $data,$where);
//        debug($this->db->last_query());
        return $re;
    }
    /**
     * @author zxx
     * 获取该活动使用的资源
     */
    public function get_community_resource($where){
        $this->db->select('*')
            ->from($this->table_activity_resource)
            ->where($where);
        $data = $this->db->get()->result_array();
        //echo $this->db->last_query();
        return $data;
    }

}
