<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Analysis extends Admin_Controller
{

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
    }


    public function info($type=0)
    {
        $type = in_array(intval($type), array(
            1,
            2,
            3)) ? intval($type) : 1;

        $count = $this->get_analysis('info', $type);
        $this->smarty->assign('sub', $count);
        $this->smarty->assign('active', $type);
        $this->smarty->view('analysis_info');
    }
    /**
     * 图文分析
     * 
     */

    public function get_analysis_list()
    {
        $this->load->model('statlog_model', 'statlog');
        $year = $_POST['year'];
        $month = $_POST['month'];
        $this->load->model('business_model', 'business');
        $time = $year . '-' . $month;
        $list = $this->statlog->get_date_list($time, $this->id_business, $this->id_shop);
        for ($i = 0; $i < count($list); $i++) {
            $date = $list[$i]['date'];
            $list[$i]['read'] = $this->statlog->get_read_count($this->id_business, $this->id_shop, $date);
        }
        die(json_encode($list));
    }
    /**
     *导出excel  
     */
     public function exp_info(){
        $date = !empty($_GET['d']) ? trim($_GET['d']) : header("location:/biz/");

        $this->load->model('business_model', 'business');
        $this->load->model('statlog_model', 'statlog');
        $list = $this->statlog->get_date_list($date, $this->id_business, $this->id_shop);
        $list = array_reverse($list);
        $data ='<table border="1" align="center" width="900" height="300">';
        $data.='<tr align="center" ><td>时间</td><td>阅读次数</td></tr>';
        for ($i = 0; $i < count($list); $i++) {
            $read = $this->statlog->get_read_count($this->id_business, $this->id_shop, $list[$i]['date']);
            $data.='<tr align="center" ><td>'.$list[$i]['date'].'</td><td>'.$read.'</td></tr>';
        }
        $data.='</table>';
        $filename = '图文分析-'.date('Y-m-d') ; //设置文件名
        print_excel($filename,$data); //导出 
     }

    public function get_view_cnt()
    {
        $type = intval($_POST['type']) ? intval($_POST['type']) : 1;
        $this->load->model('statlog_model', 'statlog');

        $this->load->model('business_model', 'business');
        $list = $this->statlog->get_view_count($this->id_business, $this->id_shop, $type);
        $data = array();
        for ($i = 0; $i < count($list); $i++) {
            $id_object = $list[$i]['id_object'];
            $title = $this->statlog->get_info_title($id_object);
            $title = csubstr($title,0,30);
            if ($title != '') {
                $data[] = array(
                    'num' => $list[$i]['num'],
                    'title' => $title,
                    'id_object' => $id_object);
            }
        }
        die(json_encode($data));
    }
    
    public function get_msg_cnt(){
        $this->load->model('statlog_model', 'statlog');

        $year = $_POST['year'];
        $month = $_POST['month'];
        $this->load->model('business_model', 'business');
        $time = $year . '-' . $month;
        $list = $this->statlog->get_msg_date_list($time, $this->id_business, $this->id_shop);
        for ($i = 0; $i < count($list); $i++) {
            $date = $list[$i]['date'];
            $list[$i]['people'] = $this->statlog->get_msg_people($date,$this->id_business, $this->id_shop);
            $list[$i]['send_cnt'] = $this->statlog->get_send_cnt($date,$this->id_business, $this->id_shop);
            $list[$i]['send_avg'] = round($list[$i]['send_cnt']/$list[$i]['people'],2);
        }
        die(json_encode($list));       
    }
    
    /**
     * 导出excel
     */
    public function exp_msg(){
        $date = !empty($_GET['d']) ? trim($_GET['d']) : header("location:/biz/");
        $this->load->model('business_model', 'business');
        $this->load->model('statlog_model', 'statlog');
        $list = $this->statlog->get_msg_date_list($date, $this->id_business, $this->id_shop);
        $list = array_reverse($list);
        $data ='<table border="1" align="center" width="900" height="300">';
        $data.='<tr align="center" ><td>时间</td><td>消息发送人数</td><td>消息发送次数</td><td>人均发送次数</td></tr>';
        for ($i = 0; $i < count($list); $i++) {
            $people = $this->statlog->get_msg_people($list[$i]['date'],$this->id_business, $this->id_shop);
            $send_cnt = $this->statlog->get_send_cnt($list[$i]['date'],$this->id_business, $this->id_shop);
            $send_avg = round($send_cnt/$people,2);
            $data.='<tr align="center" ><td>'.$list[$i]['date'].'</td><td>'.$people.'</td><td>'.$send_cnt.'</td><td>'.$send_avg.'</td></tr>';
        }
        $data.='</table>';
        $filename = '消息分析-'.date('Y-m-d') ; //设置文件名
        print_excel($filename,$data); //导出
    }

    public function message($type=0)
    {
        $type = in_array(intval($type), array(
            1,
            2,
            3)) ? intval($type) : 1;

        $count1 = $this->get_analysis('message', $type);
        $this->smarty->assign('sub1', $count1);

        //人数
        $count2 = $this->get_analysis('message', $type, true);
        $this->smarty->assign('sub2', $count2);

        $this->smarty->assign('active', $type);
        $this->smarty->view('analysis_message');
    }

    public function keyword($type=0)
    {
        $type = in_array(intval($type), array(
            1,
            2,
            3)) ? intval($type) : 1;

        $count = $this->get_analysis('keyword', $type);
        $this->smarty->assign('sub', $count);
        $this->smarty->assign('active', $type);
        $this->smarty->view('analysis_keyword');
    }

    public function menu($type=0)
    {
        $type = in_array(intval($type), array(
            1,
            2,
            3)) ? intval($type) : 1;

        $count = $this->get_analysis('menu', $type);
        $this->smarty->assign('sub', $count);
        $this->smarty->assign('active', $type);
        $this->smarty->view('analysis_menu');
    }


    private function get_analysis($analysis_type, $type, $other = false)
    {

        $this->load->model('statlog_model', 'statlog');
        $where = array();
        $where['id_business'] = $this->id_business;
        $where['id_shop'] = $this->id_shop;
        if ($analysis_type == 'info') {
            $where['object_type'] = 'info';
            $where['event'] = 'view';
        } elseif ($analysis_type == 'keyword') {
            $where['object_type'] = 'keyword';
            $where['event'] = 'search';
        } elseif ($analysis_type == 'message') {
            $where['object_type'] = 'msg';
            if ($other) {
                $where['event'] = 'peoples';
            } else {
                $where['event'] = 'times';
            }

        } elseif ($analysis_type == 'menu') {
            $where['object_type'] = 'menu';
            $where['event'] = 'click';
        }
        if ($type == 1) {
            $yesterday = date('Y-m-d', strtotime('-1 days'));
            $where["date"] = $yesterday;
        } elseif ($type == 2) {
            //上周
            $mon = date('Y-m-d', strtotime('-1 week Sunday -6 days'));
            $sun = date('Y-m-d', strtotime('-1 week Sunday'));

            $where["date >= "] = $mon;
            $where["date <= "] = $sun;

        } elseif ($type == 3) {

            $month = date('Y-m', strtotime('-1 months'));
            $where["DATE_FORMAT(date,'%Y-%m')"] = $month;

        }


        $result = $this->statlog->get_analysis_count($where);
        if ($result['cnt'] > 0) {
            return $result['cnt'];
        }
        return 0;

    }


}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
