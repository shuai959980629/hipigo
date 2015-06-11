<?php
/**
 * 
 * @copyright(c) 2014-12-9
 * @author zxx
 * @version Id:car.php
 */

class Car extends WX_Controller
{

    /*
     * zxx
     * 汽车预约
     */
    public function car_appointment(){
        $this->load->model('businessonlineinformation_model','boi');
        if($_POST){
            $name = $this->input->post('name');//名字
            $phone = $this->input->post('phone');//电话
            $car_number = $this->input->post('car_number');//车牌号
            $car_time = $this->input->post('car_time');//预约时间
            $car_mileage = $this->input->post('car_mileage');//行驶里程
            $description = $this->input->post('description');//描述

            $data = array(
                'id_business'=>$this->bid,
                'type_online'=>1,
                'user_name'=>$name,
                'user_phone'=>$phone,
                'user_card'=>$car_number,
                'user_time'=>$car_time,
                'user_mileage'=>$car_mileage,
                'user_desc'=>$description,
                'state'=>0,
                'created'=>date('Y-m-d H:i:s',time())
            );
            $id = $this->boi->insert_online_information($data);
//            if($id){
                die(json_encode(array('state'=>1,'msg'=>'提交申请成功。')));
//            }else{
//                die(json_encode(array('state'=>0,'msg'=>'提交申请失败。')));
//            }
        }else{
            $this->load->model('ad_model','ad');
            $where = 'id_business = ' . $this->bid .' and location = \'car_banner\' and state = 1';
            $ad_info = $this->ad->get_ad('*',$where);
            $this->smarty->assign('ad',$ad_info[0]);
            $this->smarty->view('car_appointment');
        }
    }

}