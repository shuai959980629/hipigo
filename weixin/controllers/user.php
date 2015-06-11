<?php if (!defined('BASEPATH'))exit('No direct script access allowed');
/**
 * @author zhoushuai
 * @copyright 2014-05-21
 * @version 1.1
 * 用户
 */
class User extends WX_Controller
{

    private $id_business; //商家ID
    private $id_shop; //门店ID
    private $id_activity; //活动id
    private $id_open; //微信id;

    public function __construct()
    {
        parent::__construct();
        session_start();
        $this->id_business = $this->bid;
        $this->id_shop = $this->sid;
        $this->smarty->assign('bid', $this->bid);
        $this->smarty->assign('sid', $this->sid);
        $this->smarty->assign('bak_url', $this->bak_url);
    }

    
    /**
     *  @登录
     *  帐号长度为5-12位
        帐号唯一
        账号限制为英文+数字 区分大小写
     */
    public function login()
    {
         $this->load->model('hipigouser_model','hipigouser');
         if($this->userid && $this->identity!='visitor'){
            $data['last_login_time'] =  date('Y-m-d H:i:s',time());
            $where = 'id_user = ' . $this->userid;
            $this->hipigouser->update_hipigo_user($data,$where);//更新会员最后登录时间
            header("Location: /wapi/{$this->id_business}/{$this->id_shop}/community/home");
        }
        $login = $this->input->post('t');
        if($login == 'login'){
             $phone = $this->input->post('phone');
             //$this->user_check($phone);
             $pwd = $this->input->post('pwd');
             //$this->pwd_check($pwd); 
             $data = $this->hipigouser->login($phone,$pwd);
            if(empty($data)){
                parent::return_client(1,null,null);
             }else{
                parent::return_client(0,null,$data);
             } 
        }else{
            if(TPL_DEFAULT == 'mobile'){
                $this->media('login.js','js');
            }
           $this->smarty->view('login');
        }
    }
    
    //注册
    public function register()
    {
        $this->load->model('hipigouser_model','hipigouser');
		$is_wechat_user = $this->input->get('is_wechat_user');
        $reg = $this->input->post('t');

        if($reg == 'reg'){
            $verify = $_SESSION['hipigo_verify'];
            $user = $this->input->post('user');
            if(strpos($user,'hipigo') !== false){
                parent::return_client(0,null,"您输入的帐号不能存在'hipigo'！");
            }
            if (preg_match("/^(13[0-9]|14[0-9]|15[0-9]|18[0-9]|17[0-9]|18[0-9])\d{8}$/",$user)) {
                parent::return_client(0,null,'您输入的帐号格式不正确！');
            }
            $this->user_check($user);
            $pwd = $this->input->post('pwd');
            $this->pwd_check($pwd); 
            $chPWD = $this->input->post('chPWD');
            if($chPWD!=$pwd){
                parent::return_client(0,null,'两次密码不一样！');
            }
            $code = $this->input->post('code');
            if(md5(strtolower($code))!=$verify){
                parent::return_client(0,null,'验证码输入错误！');
            }
            if($this->userid && $this->identity!='visitor' && $is_wechat_user){
                $data_u = array(
                    'account_name'=>$user,
                    'password'=>md5($pwd)
                );
                $data_u['last_login_time'] =  date('Y-m-d H:i:s',time());
                $where_u = 'id_user = ' . $this->userid;
                $this->hipigouser->update_hipigo_user($data_u,$where_u);//更新会员最后登录时间
                if($is_wechat_user){
                    parent::return_client(2,"/wapi/{$this->id_business}/{$this->id_shop}/user_activity/user_info",'设置账号成功！');
                }
            }

            $data = $this->hipigouser->register($user,$pwd,$this->id_business);
            if($data==''){
                parent::return_client(1,null,null);
            }else{
                parent::return_client(0,null,$data);
            }
        }else{
            if(TPL_DEFAULT == 'mobile'){
                $this->media('register.js','js');
            }
            $this->smarty->assign('is_wechat_user', $is_wechat_user?1:0);
            $this->smarty->view('register');
        }
    }
    //验证码
    /**
     * 验证码生成图片
     * Enter description here ...
     */
    public function v_code(){
         createimg(4,$width=80,$height=30); 
    }
    

    //修改密码
    public function changepwd()
    {
        if(!$this->userid || $this->identity=='visitor'){
            header("Location: /wapi/{$this->id_business}/{$this->id_shop}/user/login");
        }
        $this->smarty->assign('identity', $this->identity);
        if(TPL_DEFAULT == 'mobile'){
            $this->media('layout_section.css','css');
        }
        if($this->identity=='wechat'){
            $where = array('id_user'=>$this->userid);
            $this->load->model('hipigouser_model','hipigouser');
            $is_r = $this->hipigouser->get_hipigo_user_info($where);
            if($is_r[0]['password']){
                if(TPL_DEFAULT == 'mobile'){
                    $this->media('change_password.js','js');
                }
                $this->smarty->view('change_password');
            }else{
                if(TPL_DEFAULT == 'mobile'){
                    $this->media('edit_pwd.js','js');
                }
                $this->smarty->view('editpwd');
            }
        }else{
            if(TPL_DEFAULT == 'mobile'){
                $this->media('change_password.js','js');
            }
            $this->smarty->view('change_password');
        }
    }
    
    public function change(){
        if(!$this->userid || $this->identity=='visitor'){
            parent::return_client(0,null,'请登录！');
        }
        
        $newpwd = $this->input->post('newpwd');
        $this->pwd_check($newpwd);
        $newpw = $this->input->post('newpw');
        $this->pwd_check($newpw);
        $this->load->model('hipigouser_model','hipigouser');
        $oldpwd = $this->input->post('oldpwd');
        $this->pwd_check($oldpwd); 
        $where = array('password'=>md5($oldpwd),'id_user'=>$this->userid);
        $is_r = $this->hipigouser->get_hipigo_user_info($where);
        if(!$is_r){
            parent::return_client(0,null,'原密码输入错误！');
        }
        if($newpw!=$newpwd){
            parent::return_client(0,null,'两次密码不一致！');
        }
        $data['password'] = md5($newpwd);
        if($this->hipigouser->update_hipigo_user($data,$where)){
            parent::return_client(1,null,null);
        }else{
            parent::return_client(0,null,'密码修改失败！');
        }
    }
    
    //找回密码
    public function findpwd()
    {
        $this->smarty->view('forgot_password');
    }
    
    public function send(){
        $this->load->model('hipigouser_model','hipigouser');
        $phone = $this->input->post('phone');
        if (!preg_match("/^(13[0-9]|14[0-9]|15[0-9]|18[0-9]|17[0-9]|18[0-9])\d{8}$/",$phone)) {
            parent::return_client(0,null,'手机号码输入错误');
        }

        $count = $this->hipigouser->query_lock($phone,'forgetPwd');

        if($count>=3){
            parent::return_client(0,null,'一小时内最多获取3次！');
        }
        
        //判断是否为修改/绑定手机
        if($this->input->post('source') != 'bind_phone') {
          $where = 'cellphone = \'' . $phone . '\'';
          $data = $this->hipigouser->check_user($where);
          if($data!='') {
            parent::return_client(0,null,$data);
          }
        }
        else {
          $where = 'cellphone = \'' . $phone . '\'';
          $is_r = $this->hipigouser->get_hipigo_user_info($where);
          if($is_r) {
            echo $this->returnJson(0, '该手机号码已经验证通过！', null);
            return;
          }
        }
        
//            $this->load->model('helper_model','helper');
//            $res = $this->helper->send_code($phone);
        //生成发送验证码
        $mobile_code = $this->random(6, 1);
        $_SESSION['mobile_code'] = $mobile_code;//绑定手机使用
//        $_COOKIE['mobile_code'] = $mobile_code;
//        setcookie('mobile_code', $mobile_code, time() + (60 * 60 * 24 * 30), '/', '.hipigo.cn');
        $result = $this->sendcode($phone,$mobile_code,'register');
        //end
        preg_match_all('/<[^>]+>(.*)<\/[^>]+>/', $result, $res);//匹配返回值
        if($res[1][0]==='0'){
            //最多获取3次，锁定1小时
            $data = array(
                'object_type'=>'forgetPwd',
                'lock_phone'=>$phone,
            );
            $this->hipigouser->insert_lock($data);
            parent::return_client(1,null,'验证码发送成功！');
        }else{
            parent::return_client(0,null,'验证码发送失败，请稍后再试！');
        }
    }
    
    public function authrize(){
        $code = $this->input->post('code');
        $phone = $this->input->post('phone');
        if (!preg_match("/^(13[0-9]|14[0-9]|15[0-9]|18[0-9]|17[0-9]|18[0-9])\d{8}$/",$phone)) {
            parent::return_client(0,null,'手机号码输入错误');
        }

        if($code == $_SESSION['mobile_code']){
//        if($code == $_COOKIE['mobile_code']){
            $_SESSION['phone'] = $phone;
            if($this->input->post('source') == 'bind_phone') { //绑定手机 By Jamai
              $this->load->model('hipigouser_model', 'hipigouser');
              $this->hipigouser->update_hipigo_user(
                    array('cellphone' => $phone), 
                    array('id_user' => $this->userid));
            }
            parent::return_client(1,null,null);
        }else{
            parent::return_client(0,null,'你输入的验证码错误，请重新输入！');
        }
    }
    
    public function editpwd(){
        if(!$this->userid || $this->identity=='visitor'){
            header("Location: /wapi/{$this->id_business}/{$this->id_shop}/user/login");
        }
        $this->smarty->view('editpwd');
    }
    public function forgetpwd(){
        $this->smarty->view('editpwd');
    }
    public function edit(){
        $this->load->model('hipigouser_model','hipigouser');
        if(empty($_SESSION['phone']) || !isset($_SESSION['phone'])){
            $this->userid = $_SESSION['userid'];
            $this->identity = $_SESSION['identity'];
            if(!$this->userid || $this->identity=='visitor'){
                parent::return_client(0,null,'请登录！');
            }
            //微信用户第一次修改密码 不用原密码、。
            $where = array('id_user'=>$this->userid);
        }else{
            //忘记密码通过手机号码修改新密码密码。
            $where = array('cellphone'=>$_SESSION['phone']);
        }
        $newpwd = $this->input->post('newpwd');
        $this->pwd_check($newpwd);
        $newpw = $this->input->post('newpw');
        $this->pwd_check($newpw);
        if($newpw!=$newpwd){
            parent::return_client(0,null,'两次密码不一致！');
        }
        $data['password'] = md5($newpwd);
        if($this->hipigouser->update_hipigo_user($data,$where)){
            parent::return_client(1,null,null);
        }else{
            parent::return_client(0,null,'密码修改失败！');
        }
    }

    //个人资料
    public function personal_info()
    {
        $this->smarty->view('personal_information');
    }

    //修改个人资料
    public function edit_info()
    {
        $this->smarty->view('personal_edit_information');
    }
    
    //修改手机号码
    public function edit_phone(){
        if(!$this->userid || $this->identity=='visitor'){
            header("Location: /wapi/{$this->id_business}/{$this->id_shop}/user/login");
        }
      
      if(TPL_DEFAULT == 'default') {
        $this->smarty->assign('identity', $this->identity);
        $this->smarty->view('edit_phone');
      }else{
        $this->smarty->assign('source', 'bind_phone');
        $this->smarty->view('forgot_password');
      }
    }
    
    public function checkpwd(){
        if(!$this->userid || $this->identity=='visitor'){
            parent::return_client(0,null,'请登录！');
        }
        $pwd = $this->input->post('pwd');
        $this->pwd_check($pwd);
        $this->load->model('hipigouser_model','hipigouser');
        $where = array('password'=>md5($pwd),'id_user'=>$this->userid);
        $is_r = $this->hipigouser->get_hipigo_user_info($where);
        if(!$is_r){
            parent::return_client(0,null,'你输入的密码不正确！');
        }
        parent::return_client(1,null,null);
    }
    
    //绑定手机
    public function bind_phone()
    {
        if( ! $this->userid)
          $this->redirect('/user/login');

      if(TPL_DEFAULT == 'default') {
        $this->smarty->assign('identity', $this->identity);
        $this->smarty->view('bind_phone');
      }else{
        //判断来自位置 用户中心为绑定  进入找回密码界面
        $this->smarty->assign('source', 'bind_phone');
        $this->smarty->view('forgot_password');
      }
    }
    
    public function getcode(){
        $this->load->model('hipigouser_model','hipigouser');
        $phone = $this->input->post('phone');
        if (!preg_match("/^(13[0-9]|14[0-9]|15[0-9]|18[0-9]|17[0-9]|18[0-9])\d{8}$/",$phone)) {
            parent::return_client(0,null,'手机号码输入错误');
        }
        $count = $this->hipigouser->query_lock($phone,'bingdingPhone');
        if($count>=3){
            parent::return_client(0,null,'一小时内最多获取3次！');
        }
        $where = 'cellphone = \'' . $phone . '\'';
        $is_r = $this->hipigouser->get_hipigo_user_info($where);
        if($is_r){
            parent::return_client(0,null,'该手机号码已经验证通过！');
        }else{
//            $this->load->model('helper_model','helper');
//            $res = $this->helper->send_code($phone);
//            if($res['SubmitResult']['code']==2){
            //生成发送验证码
            $mobile_code = $this->random(6,1);
            $_SESSION['mobile_code'] = $mobile_code;
            $result = $this->sendcode($phone,$mobile_code,'register');
            //end
            preg_match_all('/<[^>]+>(.*)<\/[^>]+>/', $result, $res);//匹配返回值
            if($res[1][0]==='0'){
                //最多获取3次，锁定1小时
                $data = array(
                    'object_type'=>'forgetPwd',
                    'lock_phone'=>$phone,
                );
                $this->hipigouser->insert_lock($data);
                parent::return_client(1,null,'验证码发送成功！');
            }else{
                parent::return_client(0,null,'验证码发送失败，请稍后再试！');
            }
        }
        
    }
    
    public function bind(){
        if(!$this->userid || $this->identity=='visitor'){
            parent::return_client(0,null,'请登录！');
        }
        $code = $this->input->post('code');
        $phone = $this->input->post('phone');
        if (!preg_match("/^(13[0-9]|14[0-9]|15[0-9]|18[0-9]|17[0-9]|18[0-9])\d{8}$/",$phone)) {
            parent::return_client(0,null,'手机号码输入错误');
        }        
        $this->load->model('hipigouser_model','hipigouser');
        if($code == $_SESSION['mobile_code']){
            $data['cellphone'] =  $phone;
            $where = array('id_user'=>$this->userid);
            $res = $this->hipigouser->update_hipigo_user($data,$where);
            if($res){
                parent::return_client(1,null,null);
            }else{
               parent::return_client(0,null,'手机验证失败！');
            }
        }else{
            parent::return_client(0,null,'你输入的验证码错误，请重新输入！');
        }
        
    }
    
    
    /**
     * 帐号 格式
     */
    public function user_check($str)
    {
        if($str==''){
            parent::return_client(0,null,'请输入您的帐号！');
        }elseif(!preg_match("/^[\da-zA-Z]{5,12}$/",$str)){
            parent::return_client(0,null,'您输入的帐号格式不正确！');
        }
    }
    
    /**
     * 密码验证
     * //密码长度为6-18位，由字母数字或者英文符号组成
     */
    public function pwd_check($str){
        if($str==''){
            parent::return_client(0,null,'请输入您的密码！');
        }elseif(preg_match("/^[\d]{6,18}$/", $str)){
            parent::return_client(0,null,'请输入6-18位数字加字母的密码！');
        }elseif(!preg_match("/^[\w~!@#$%^&_*]{6,18}$/", $str)){
            parent::return_client(0,null,'请输入6-18位数字加字母的密码！');
        }
    }
    


}
