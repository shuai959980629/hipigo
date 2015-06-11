<?php
/**
 * 
 * @copyright(c) 2014-5-23
 * @author zxx
 * @version Id:Hipigouser_Model.php
 */

class Hipigouser_Model extends CI_Model
{
    protected $table = 'bn_hipigo_user';
    protected $table_action = 'bn_user_action_logs';

    public function __construct()
    {
        $this->load->database();
    }

    /*
    * zxx
     * 判断是否注册,获取用户信息
    * **/
    public function get_hipigo_user_info($where)
    {
        $this->db->select('*')
            ->from($this->table)
            ->where($where)
            ->order_by('id_user', 'desc');
        $result = $this->db->get()->result_array();
        return $result;
    }
    
    public function get_hipigo_user($where){
        $mysql = "SELECT *FROM (`bn_hipigo_user`) WHERE BINARY {$where} ORDER BY `id_user` desc limit 1";
        $query = $this->db->query($mysql);
        if ($query->num_rows()>0)
        {
           $row = $query->row_array();
           return $row;
        }else{
            return false;
        }
    }


    /*
    * zxx
    * 添加一条会员信息
    */
    public function insert_hipigo_user($data)
    {
        $re = $this->db->insert($this->table, $data);
        $id = $this->db->insert_id();
        return $id;
    }

    /*
       * zxx
       * 更新一条会员信息
       */
    public function update_hipigo_user($data,$where)
    {
        $re = $this->db->update($this->table, $data,$where);
        return $re;
    }
    
    /**
     * @author zhoushuai
     * 用户登录login
     */
    public function login($phone,$pwd){
        $this->db->select('*')->from('bn_hipigo_user')->where("cellphone",$phone)->or_where('account_name',$phone);
        $result = $this->db->get()->result_array();
        if(empty($result[0])){
            $msg = '您输入的帐号不存在！';
            return array('infor'=>$msg,'type'=>"user");
        }
        
        $pwd = md5($pwd);
        $sql = "SELECT `id_user` FROM (`bn_hipigo_user`) WHERE (`cellphone` =  '{$phone}' AND `password` =  '{$pwd}') OR (BINARY `account_name` =  '{$phone}' AND `password` =  '{$pwd}') LIMIT 1";
        $query = $this->db->query($sql);
        $row = $query->row_array();
        $userid = $row['id_user'];
        //echo $this->db->last_query();
        if($query->num_rows() <= 0 || empty($userid)){
            $msg = '您输入的密码错误！';
            return array('infor'=>$msg,'type'=>"pwd");
        }
        $data['last_login_time'] =  date('Y-m-d H:i:s',time());
        $where = 'id_user = ' . $userid;
        $this->update_hipigo_user($data,$where);//更新会员最后登录时间
        $_SESSION['userid'] = $userid;
        $_SESSION['identity'] = 'register';
        setcookie('userid', $userid, time() + (60 * 60 * 24 * 30), '/', '');
        setcookie('identity', 'register', time() + (60 * 60 * 24 * 30), '/', '');
		$_SESSION['state'] = '';
        return  array();
//        return array('infor'=>$userid,'type'=>"cookie");
    }
    /**
     * @author zhoushuai
     * 用户注册
     */
    public function register($user,$pwd,$bid){
        $msg = '';
        $where = '`account_name` = \'' . $user . '\' OR `cellphone` = \''.$user.'\'';
        $is_r = $this->get_hipigo_user($where);
        if(!$is_r){
            $data = array(
                'account_name' => $user,
                'password'=>md5($pwd),
                'nick_name' => $user,
                'created' => date('Y-m-d H:i:s',time()),
                'last_login_time' => date('Y-m-d H:i:s',time()),
                'id_business' => $bid
            );
            //注册会员
            $userid = $this->insert_hipigo_user($data);
            if($userid>0){
                $_SESSION['userid'] = $userid;
                $_SESSION['identity'] = 'register';
                setcookie('userid', $userid, time() + (60 * 60 * 24 * 30), '/', '');
                setcookie('identity', 'register', time() + (60 * 60 * 24 * 30), '/', '');
				$_SESSION['state'] = '';
            }else{
                $msg = '注册失败！';
            }
            
        }else{
            $msg = '你输入的帐号已存在！';
        }
        return $msg;
    }
    /**
     * @author zhoushuai
     */
    public function check_user($where){
        $msg = '';
        $is_r = $this->get_hipigo_user_info($where);
        if(!$is_r){
            $msg = '你不是会员或手机未绑定，无法找回密码！';
        }
        return $msg;
    }
    /**
     * 锁定
     */
    public function insert_lock($data){
        $re = $this->db->insert('bn_lock', $data);
        $id = $this->db->insert_id();
        return $id;
    }
    public function query_lock($phone,$type){
        $time = time()-60*60;
    	$time = date('Y-m-d H:i:s',$time);	
    	$query = $this->db->select("count(*) as num");
    	$this->db->from('bn_lock');
        $this->db->where('object_type',$type);
    	$this->db->where('lock_phone',$phone);
    	$this->db->where('created >',$time);
        $data = $this->db->get()->result_array();
    	return $data[0]['num'];
    }
   
  /**
   * 判断用户是否为达人
   * @version 2.0
   * @author Jamai
   * @return boolean
   */
  public function is_role($userID)
  {
    if( ! $userID)
      return false;
      
    $this->db->select('id_role')
        ->from($this->table)
        ->where('id_user = ' . $userID)
        ->limit(1);
    $result = $this->db->get()->row();
    if($result->id_role == 3)
      return true;
    return false;
  }
  
  /**
   * 根据读取内容来读取内容
   * @version 2.0
   * @author Jamai
   * @return 
   **/
  public function byFieldSelect($fields = array(), $where = array(), $row = false)
  {
    if( ! $fields)
      $fields = '*';
    
      $this->db->select($fields)
              ->from($this->table);
     if($where)
		$this->db->where($where);
    
     if($row)
      $result = (Array) $this->db->get()->row();
    else
      $result = $this->db->get()->result_array();
    
	  return $result;
  }
  
  /**
   * 查看记录
   *    根据读取内容来读取内容  
   * @version 2.0
   * @author Jamai
   * @return 
   **/
  public function getUserActionLogs($fields = array(), $where = array(), $row = false)
  {
    if( ! $fields)
      $fields = '*';
    
    $this->db->select($fields)
            ->from($this->table_action);
    if($where)
      $this->db->where($where);
    
    if($row)
      $result = (Array) $this->db->get()->row();
    else
      $result = $this->db->get()->result_array();
    return $result;
  }
  
  /**
   * 修改 已读 记录
   * @version 2.1
   * @author Jamai
   * @return 
   **/
  public function updateActionLogs($data, $where)
  {
    $this->db->update($this->table_action, $data, $where);
  }
  
  /**
   * 添加 已读 记录
   * @version 2.1
   * @author Jamai
   * @return 
   **/
  public function insertActionLogs($data)
  {
  $this->db->insert($this->table_action, $data);
  }
  
}





/* End of file user_model.php */