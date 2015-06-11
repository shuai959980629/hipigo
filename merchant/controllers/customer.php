<?php
/**
 * 
 * @copyright(c) 2013-12-23
 * @author msi
 * @version Id:customer.php
 */

class Customer extends Admin_Controller{

    private $id_business;
    private $id_shop;
    public function __construct()
    {
        parent::__construct();
        $this->id_business = $this->users['id_business'];
        $this->id_shop = $this->users['id_shop'];//门店id

        if(empty($this->users) || empty($this->session_id)){
            header('location:'.$this->url.'user/login');
            die ;
        }
        $this->load->model('customer_model','customer');
    }

    //会员列表
	public function card_list(){
        $search_key = $this->input->post('search_key') ? $this->input->post('search_key') : '';
        $customer_type = $this->input->post('customer_type') ? $this->input->post('customer_type') : 'all';
        $offset = $this->input->post('offset') ? intval($this->input->post('offset')) : 1;//页码
        $id_customer = $this->input->post('id_customer') ? intval($this->input->post('id_customer')) : 0;//会员id

        if($id_customer){
            if(!$this->delete_customer($id_customer)){
                echo '0';
            }
        }

        //获取会员列表
        $data = $this->get_customer($offset,$search_key,$customer_type);
        $data['is_business'] = in_array($this->id_business,$this->config->item('BUSINESS_ID'));//用来判断是否是需要处理企业设置和会员部分的商家
        $this->smarty->assign($data);
        $this->smarty->assign('offset',$offset);
        $this->smarty->assign('page_type','customer');

        if($this->input->post('ispage') == 1){
            echo $this->smarty->view('lists');
        }else{
            $this->smarty->view('list_customer');
        }
	}

    function get_customer($offset,$search_key,$customer_type){
        $page = $this->config->item('page');
        $where =  'a.id_business = '.$this->id_business;
        if(in_array($this->users['id_business'],$this->config->item('BUSINESS_ID')) && $this->users['is_shop'] == 0){
            if($this->users['id_shop_array'])
                $where .= ' and';
                foreach($this->users['id_shop_array'] as $k=>$si){
                    if($k == 0){
                        $where .= ' (';
                        $where .= 'a.id_shop = '.$si;
                    }else
                        $where .= ' or a.id_shop = '.$si;
                    if(($k+1) == count($this->users['id_shop_array'])){
                        $where .= ')';
                    }
                }
        }else{
            $where .= ' and a.id_shop = '.$this->id_shop;//原来的
        }
        if($customer_type == 'on'){
            $where .= ' and b.state = \'subscribe\'';
            if(in_array($this->users['id_business'],$this->config->item('BUSINESS_ID')) && $this->users['is_shop'] == 0){
                if($this->users['id_shop_array'])
                    $where .= ' and';
                foreach($this->users['id_shop_array'] as $k1=>$si1){
                    if($k1 == 0){
                        $where .= ' (';
                        $where .= 'b.id_shop = '.$si1;
                    }else
                        $where .= ' or b.id_shop = '.$si1;
                    if(($k1+1) == count($this->users['id_shop_array'])){
                        $where .= ')';
                    }
                }
            }else{
                $where .= ' and b.id_shop = '.$this->id_shop;//原来的
            }

        }elseif($customer_type == 'off'){
            $where .= ' and (a.id_open = \'\' or a.id_open IS NULL)';
        }
        if($search_key){
        	$search_key = ltrim($search_key,'0');
            $where .=' and (a.number like \'%'.$search_key.'%\' or b.nick_name like \'%'.$search_key.'%\' or a.real_name like \'%'.$search_key.'%\' or a.cell_phone like \'%'.$search_key.'%\')';
        }
        //获取会员总数
        $total = $this->customer->get_member_card_count($where);
        //获取会员列表
        $clist = $this->customer->get_membercard_list($where,$offset,$page);
        if(!$clist){
            $offset = 1;
            $clist = $this->customer->get_membercard_list($where,$offset,$page);
        }
        //分页代码
        $page_html = $this->get_page($total, $offset, 'customer_list_page','method',$page);

        $this->load->model('business_model','business');
        $where_b = 'id_business = '.$this->id_business;
        $info = $this->business->get_business_phone($where_b);
        $whether_m = 0;
        if($info){
            $whether_m = $info[0]['whether_add_member'];
        }

        //获取当前商家所有门店信息
        $this->load->model('shop_model','shop');
        $shopList = $this->shop->get_shop_introduction($where_b);

        $data = array();
        $data['shop_list'] = $shopList;
        $data['customer_list'] = $clist;
        $data['page_html'] = $page_html;
        $data['total'] = $total;
        $data['whether_m'] = $whether_m;

        return $data;
    }

    //搜索会员
    function search_customer(){
        $search_key = $this->input->post('search_key')?$this->input->post('search_key'):'';
        $customer_type = $this->input->post('customer_type') ? $this->input->post('customer_type') : 'all';
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        //获取搜索后的会员列表
        $data = $this->get_customer($offset,$search_key,$customer_type);

        $this->smarty->assign($data);
        $this->smarty->assign('offset',$offset);
        $this->smarty->assign('page_type','customer');
        echo $this->smarty->view('lists');
    }


    /*
     * zxx
     * 获取某个会员信息
     */
    function show_customer(){
        $id_customer = $this->input->post('id_customer')?$this->input->post('id_customer'):0;
        $where ='id_customer = '.$id_customer;
        //获取会员信息
        $customer_info = $this->customer->get_member_card_by_openid($where);
        if($customer_info){
            die(json_encode(array('error'=>1,'data'=>$customer_info[0])));
        }
        die(json_encode(array('error'=>0,'data'=>'没有此会员信息呢！')));
    }

    /*
     * zxx
     * 添加和编辑会员信息
     */
    function edit_customer($id_customer=0){
        $return_data['url_action'] = 'customer/edit_customer';
        if($_POST){
            $id_customer = $this->input->post('id_customer')?$this->input->post('id_customer'):0;
            $real_name = $this->input->post('real_name');
            $phone = $this->input->post('phone');
            $shop_ = $this->input->post('shop_');//添加会员所属门店id
            $sex = $this->input->post('sex');
            $birthday_month = $this->input->post('birthday_month');
            $birthday_day = $this->input->post('birthday_day');
            $image_src = $this->input->post('image_src');
            $data = array(
                'real_name'=>$real_name,
                'cell_phone'=>$phone,
                'sex'=>$sex,
                'birthday_month'=>$birthday_month,
                'birthday_day'=>$birthday_day,
                'head_url'=>$image_src
            );

            $sd_data = array();
            if(in_array($this->id_business,$this->config->item('BUSINESS_ID'))){
                //附件会员信息
                $card_number = $this->input->post('number');
                $qq = $this->input->post('qq');
                $weixin = $this->input->post('weixin');
                $optimal_contact = $this->input->post('optimal_contact');
                $optimal_time = $this->input->post('optimal_time');
                $optimal_time_end = $this->input->post('optimal_time_end');
                $height = $this->input->post('height');
                $blood = $this->input->post('blood');
                $nationality = $this->input->post('nationality');
                $nation = $this->input->post('nation');
                $domicile = $this->input->post('domicile');
                $address = $this->input->post('address');
                $marital_status = $this->input->post('marital_status');
                $education = $this->input->post('education');
                $salary = $this->input->post('salary');
                $working_condition = $this->input->post('working_condition');
                $occupation = $this->input->post('occupation');
                $company_type = $this->input->post('company_type');
                $company_industry = $this->input->post('company_industry');
                $company_name = $this->input->post('company_name');
                $car = $this->input->post('car');
                $purchase = $this->input->post('purchase');
                $smoking = $this->input->post('smoking');
                $drink = $this->input->post('drink');
                $habits = $this->input->post('habits');
                $religion = $this->input->post('religion');
                $interests = $this->input->post('interests');
                $before_learning = $this->input->post('before_learning');
                $one_learning = $this->input->post('one_learning');
                $sw_learning = $this->input->post('sw_learning');
                $two_learning = $this->input->post('two_learning');
                $fw_learning = $this->input->post('fw_learning');
//                $reflections = $this->input->post('reflections');

                $sd_data = array(
                    'qq'=>$qq,
                    'weixin'=>$weixin,
                    'optimal_contact'=>$optimal_contact,
                    'optimal_time_start'=>$optimal_time,
                    'optimal_time_end'=>$optimal_time_end,
                    'height'=>$height,
                    'blood'=>$blood,
                    'nationality'=>$nationality,
                    'nation'=>$nation,
                    'domicile'=>$domicile,
                    'address'=>$address,
                    'marital_status'=>$marital_status,
                    'education'=>$education,
                    'salary'=>$salary,
                    'working_condition'=>$working_condition,
                    'occupation'=>$occupation,
                    'company_type'=>$company_type,
                    'company_industry'=>$company_industry,
                    'company_name'=>$company_name,
                    'car'=>$car,
                    'purchase'=>$purchase,
                    'smoking'=>$smoking,
                    'drink'=>$drink,
                    'habits'=>$habits,
                    'religion'=>$religion,
                    'interests'=>$interests,
                    'before_learning'=>$before_learning,
                    'one_learning'=>$one_learning,
                    'sw_learning'=>$sw_learning,
                    'two_learning'=>$two_learning,
                    'fw_learning'=>$fw_learning,
//                    'reflections'=>$reflections,
                );

                $data['card_number']=$card_number;
            }

            if($id_customer){
                $where = 'id_customer = ' . $id_customer;
                $this->customer->update_customer($data,$where);

                if(in_array($this->id_business,$this->config->item('BUSINESS_ID'))){
                    $this->customer->update_customer_sd($sd_data,$where);
                    header('location:'.$this->url.'customer/card_list');
                }
            }else{
                $card_num = get_member_card();
                $data['id_business'] = $this->id_business;
                $data['id_shop'] = $shop_== 0?$this->id_shop:$shop_;//$this->id_shop;
                $data['number'] = $card_num;
                $data['created'] = date('Y-m-d H:i:s',time());
                $id_customer = $this->customer->add_member_card($data);
                if(in_array($this->id_business,$this->config->item('BUSINESS_ID'))){
                    $sd_data['id_customer'] = $id_customer;
                    $this->customer->add_customer_sd($sd_data);
                    header('location:'.$this->url.'customer/card_list');
                }
            }
        }else{
            if($id_customer){
                $where = 'c.id_customer = ' . $id_customer;
                $customer_info = $this->customer->get_customer_sd_list($where);
                $return_data['customer_info'] = $customer_info[0];
            }
            $return_data['is_shop'] = $this->users['is_shop'];
            if($this->users['is_shop'] == 1){
                $return_data['shop_name'] = $this->users['shop_name'];
                $return_data['id_shop'] = $this->users['id_shop'];
            }
            $return_data['id_customer'] = $id_customer;

            $this->smarty->assign($return_data);
            $this->smarty->view('edit_customer');
        }
    }

    /*
     * zxx
     * 删除会员信息
     */
    function delete_customer($id_customer){
//        $id_customer = $this->input->post('id_customer');
        $where = 'id_customer = ' . $id_customer;
        $r = $this->customer->delete_customer($where);
        return $r ? 1: 0;
    }


    /*
     * zxx
     * 修改微信昵称
     * */
    function edit_name(){
        $page_type = $this->input->post('page_type');
        $nick_name = $this->input->post('nick_name');
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        $customer_type = $this->input->post('customer_type') ? $this->input->post('customer_type') : 'all';

        $where = '';
        if($page_type != 'list_followers'){
            $id_open = $this->input->post('id_open');
            $search_key = $this->input->post('search_key') ? $this->input->post('search_key') : '' ;
            $where = 'id_open = "' . $id_open . '" and id_business = '.$this->id_business . ' and id_shop = '.$this->id_shop;
        }else{
            $id_bn_sub = $this->input->post('id_bn_sub');
            $where = 'id_business = '.$this->id_business . ' and id_shop = '.$this->id_shop . ' and id_bn_sub = ' . $id_bn_sub;
        }

        //更新微信备注
        $re = $this->customer->update_nick_name(array('name_remarks'=>$nick_name),$where);

        if($page_type == 'customer_msg'){
            //在微信快速回复列表里修改微信备注名
            $data = $this->get_quick_reply($offset,$search_key);
            $this->smarty->assign($data);
            $this->smarty->assign('offset',$offset);
            $this->smarty->assign('page_type','quick_reply');

            echo $this->smarty->view('lists');
        }else if($page_type == 'customer'){
            //在会员列表里修改微信备注名
            $data = $this->get_customer($offset,$search_key,$customer_type);
            $this->smarty->assign($data);
            $this->smarty->assign('offset',$offset);
            $this->smarty->assign('page_type','customer');

            echo $this->smarty->view('lists');
        }elseif($page_type == 'list_followers'){
            $this->load->model('business_model','business');
            $info = $this->business->get_business_sub($where);
            echo $info[0]['name_remarks'].'('.urldecode($info[0]['nick_name']).')';
        }
    }


    /*
     * zxx
     * 将会员列表导出为excel列表
     * */
    function print_excel(){
        header("Content-type:application/vnd.ms-excel; charset=UTF-8");
        header("Content-Disposition:filename=customer_info.xls");
        echo iconv('utf-8', 'gbk', "用户编号\t");
        echo iconv('utf-8', 'gbk', "昵称\t");
        echo iconv('utf-8', 'gbk', "真实姓名\t");
        echo iconv('utf-8', 'gbk', "性别\t");
        echo iconv('utf-8', 'gbk', "电话号码\t");
        echo iconv('utf-8', 'gbk', "所在城市\t");
        echo iconv('utf-8', 'gbk', "生日\t");
        echo iconv('utf-8', 'gbk', "创建时间\t\n");
        $where = 'a.id_business = '.$this->id_business.' and a.id_shop = '.$this->id_shop .' and b.state = \'subscribe\' and b.id_shop = '.$this->id_shop;
        $c_list = $this->customer->get_membercard_list($where);
        foreach($c_list as $l){
            echo $l['number']." \t";
            if($l['nick_name']){
                if(iconv('utf-8', 'gbk', urldecode($l['nick_name'])) == false)echo " \t";
                else echo iconv('utf-8', 'gbk', urldecode($l['nick_name'])." \t");
            }else{
                echo " \t";
            }
            if($l['real_name']){
                echo iconv('utf-8', 'gbk', urldecode($l['real_name'])." \t");
            }else{
                echo " \t";
            }
            if($l['sex'] == 1){
                echo iconv('utf-8', 'gbk', "男 \t");
            }else{
                echo iconv('utf-8', 'gbk', "女 \t");
            }
            echo $l['cell_phone']." \t";
            if($l['city']){
                echo iconv('utf-8', 'gbk',  $l['city']." \t");
            }else{
                echo " \t";
            }
            echo iconv('utf-8', 'gbk',  $l['birthday_month']."-".$l['birthday_day']." \t");
            echo $l['created']." \t";
            echo "\n";
        }
    }


    //关注者列表
    function followers_list(){
        $offset = $this->input->post('offset') ? intval($this->input->post('offset')) : 1;//页码

        $data = $this->get_followers($offset);
        $this->smarty->assign($data);
        $this->smarty->assign('offset',$offset);
        $this->smarty->assign('page_type','list_followers');

        if($this->input->post('ispage') == 1){
            echo $this->smarty->view('lists');
        }else{
            $this->smarty->view('list_followers');
        }
    }

    function get_followers($offset){
        $this->load->model('business_model','business');
        $where = 'id_business = ' .$this->id_business . ' and id_shop = ' . $this->id_shop . ' and state = \'subscribe\'';

        //获取关注者总条数
        $total = $this->business->get_business_sub($where,0);

        //获取关注者信息
        $list_user = $this->business->get_business_sub($where,1,$offset,$this->config->item('page'));
        if(!$list_user){
            $offset = 1;
            $list_user = $this->business->get_business_sub($where,1,$offset,$this->config->item('page'));
        }
        //分页代码
        $page_html = $this->get_page($total, $offset, 'followers_list_page','method');

        $data = array();
        $data['list_user'] = $list_user;
        $data['page_html'] = $page_html;
        $data['total'] = $total;
        return $data;
    }
}
/* End of file customer.php */