<?php
/**
 * 
 * @copyright(c) 2014-12-11
 * @author zxx
 * @version Id:Car.php
 */


class Car extends Admin_Controller
{
    private $id_business;
    private $id_shop;
    public function __construct()
    {
        parent::__construct();
        $this->id_business = $this->users['id_business'];
        $this->id_shop = $this->users['id_shop'];
        $this->load->model('businessonlineinformation_model','boi');
        if(empty($this->users) || empty($this->session_id)){
            header('location:'.$this->url.'user/login');
            die ;
        }
    }

    /**
     * zxx
     * 商家在线预约信息列表
     */
    public function list_information($offset=0){
        $page_type = $this->input->post('page_type');
        if(!$offset){
            $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        }

        $boi_type = $this->input->post('boi_type') ? $this->input->post('boi_type') : 'all';
        $search_key = $this->input->post('search_key') ? $this->input->post('search_key') : '';

        //获取预约信息
        $return = $this->get_boi($boi_type,$search_key,$offset);
        $this->smarty->assign($return);
        $this->smarty->assign('cm',$_GET['cm']);
        if($page_type){
            echo $this->smarty->view('lists');
        }else{
            $this->smarty->view('list_car');
        }
    }

    /**
     * zxx
     * 用户预约信息搜索
     */
    public function search_boi(){
        $boi_type = $this->input->post('boi_type');
        $search_key = $this->input->post('search_key');
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码

        //获取预约列表信息
        $data = $this->get_boi($boi_type,$search_key,$offset);

        $this->smarty->assign($data);
        echo $this->smarty->view('lists');
    }


    /*
     * zxx
     * 获取预约信息
     */
    function get_boi($boi_type,$search_key,$offset){
        $where = 'id_business = ' . $this->id_business;
        if($search_key != ''){
            $where .= ' and user_name like \'%'.$search_key.'%\'';
        }
        if($boi_type != 'all'){
            $where .= ' and state = '.$boi_type;
        }
        //获取列表
        $boi_list = $this->boi->get_online_information('*',$where,$offset,20,'created desc');
        //获取总数
        $boi_count = $this->boi->get_online_information('count(id_online) as total',$where);
        //分页代码
        $page_html = $this->get_page($boi_count[0]['total'], $offset, 'boi_list_page');
        $return['boi_list'] = $boi_list;
        $return['offset'] = $offset;
        $return['page_type'] = 'online_information';
        $return['page_html'] = $page_html;
        return $return;
    }

    /*
     * zxx
     * 编辑用户汽车保养预约信息
     * id_online  汽车保养预约信息唯一id
     * offset 页码
     * */
    public function edit_car($id_online = 0,$offset = 1,$cm=0){
        $data_page ['url_action'] = 'car/edit_car';
        if($_POST){
            $id_online = $this->input->post('id_online');
            if($id_online){
                $user_time = $this->input->post('user_time');
                $user_card = $this->input->post('user_card');
                $user_mileage = $this->input->post('user_mileage');
                $desc = $this->input->post('desc');
                $state = $this->input->post('state');
                $offset = $this->input->post('offset');

                $where = 'id_online = ' . $id_online;
                $edit_data = array(
                    'user_time'=>$user_time,
                    'user_card'=>$user_card,
                    'user_mileage'=>$user_mileage,
                    'user_desc'=>$desc,
                    'state'=>$state
                );
                $this->boi->update_online_information($edit_data,$where);

                $data_page['url'] = $this->url.'car/list_information/'.$offset;
                $data_page['is_refresh'] = 2;
                $this->returen_status(1,'提交编辑成功！',$data_page);
            }else{
                $this->returen_status(0,'提交编辑时发生错误！',$data_page);
            }
        }else{
            $where = 'id_online = ' . $id_online;
            $boi_list = $this->boi->get_online_information('*',$where);
            $data_page ['info'] = $boi_list[0];
            $data_page ['id_online'] = $id_online;
            $data_page ['offset'] = $offset;
            $data_page ['cm'] = $cm;
            $this->smarty->assign($data_page);

            $this->smarty->view('edit_car');
        }
    }

    /*
     * zxx
     * 删除预约信息
     */
    function handle_boi(){
        $id_online = $this->input->post('id_online');
        $do_type = $this->input->post('do_type');
        $where = 'id_online = ' . $id_online;
        if($do_type == 'delete'){
            $r = $this->boi->delete_online_information($where);
        }elseif($do_type == 'update'){
            $update_data = array(
                'state'=>1
            );
            $r = $this->boi->update_online_information($update_data,$where);
        }
        if($r){
            $resp = array(
                'status' => 1,
                'msg' => '成功！',
                'data' => ''
            );
        }else{
            $resp = array(
                'status' => 0,
                'msg' => '失败！',
                'data' => ''
            );
        }
        die(json_encode($resp));
    }


    /*
     * zxx
     * 编辑商家预约界面信息
     */
    function edit_online_page(){
        $this->load->model('ad_model','ad');
        if($_POST){
            $image_src = $this->input->post('image_src');
            $desc = $this->input->post('desc');
            $submit_type = $this->input->post('submit_type');
            $data = array(
                'description'=>$desc,
                'image_url'=>$image_src
            );
            if($submit_type == 'add'){
                $data['id_business'] = $this->id_business;
                $data['location'] = 'car_banner';
                $this->ad->insert_ad($data);
            }else{
                $where = 'id_business = ' . $this->id_business .' and location = \'car_banner\' and state = 1';
                $this->ad->update_ad($data,$where);
            }
        }else{
            $where = 'id_business = ' . $this->id_business .' and location = \'car_banner\' and state = 1';
            $ad_info = $this->ad->get_ad('*',$where);
            $type = 'add';
            if($ad_info){
                $type = 'edit';
            }
            $this->smarty->assign('ad',$ad_info[0]);
            $this->smarty->assign('type',$type);
            $this->smarty->view('edit_online_page');
        }
    }


}


/* End of file activity.php */