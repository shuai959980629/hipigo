<?php
/**
 * 
 * @copyright(c) 
 * @author Jamai
 * @version Id:Finance.php
 */
class Finance extends Admin_Controller{

  const poundage = 0.0005;
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

  
  /**
   * 申请结算信息
   **/
public function settlement()
{
    if($_POST) {//zxx
        $where_f = 'id_object = ' . $this->id_business . ' and object_type = \'business\' and status = \'apply\'';
        $this->load->model('financeaccount_model', 'financeaccount');
        $f_info = $this->financeaccount->get_finance_account($where_f);
        if($f_info){
            $resp = array('status' => 0,'msg'=>'该商家还有未通过结算申请的,不能再次申请。');
            die(json_encode($resp));
        }
        $finance = $_POST['finance'];
        $poundage = $_POST['poundage'];

        $this->load->model('business_model', 'business');
        $where_b = 'id_business = ' . $this->id_business;
        $business_info = $this->business->get_business_phone($where_b);

        $data_i = array(
            'id_object'=>$this->id_business,
            'object_type'=>'business',
            'type'=>$business_info[0]['type'],
            'status'=>'apply',
            'price'=>$finance,
            'poundage'=>$poundage,
            'created'=>date('Y-m-d H:i:s',time())
        );
        $r = $this->financeaccount->insert_finance_account($data_i);
        if($r){
            $resp = array('status' => 1,'msg'=>'申请成功。');
            die(json_encode($resp));
        }
    }
    //读取可结算金额和不可结算额
    $this->load->model('list_model', 'list');

    //已验证的可结算金额
    $where = 'i.income_object_type = \'business\' and i.id_income_object= ' . $this->id_business . ' and i.state = 2 and t.state = 2';
    $finance = $this->list->get_settlement_amount($where);//可结算

    //不可结算金额
    $where_un = 'i.income_object_type = \'business\' and i.id_income_object= ' . $this->id_business . ' and i.state = 1';
    $unFinance = $this->list->get_settlement_amount($where_un);

    //手续费
    $poundage = round($finance->total * self::poundage, 2);

    $this->smarty->assign('count', $finance->total + $unFinance->total);
    //可结算
    $this->smarty->assign('finance', $finance->total);
    //不能结算
    $this->smarty->assign('unFinance', $unFinance->total);
    //手续费
    $this->smarty->assign('poundage', $poundage);
    //扣除手续费应结算
    $this->smarty->assign('practical', $finance->total - $poundage);
    $this->smarty->display('settlement.html');
}
  
  /**
   * zxx
   * 申请中列表
   **/
  public function list_apply()
  {
      $this->load->model('list_model', 'list');
      $where = 'i.income_object_type = \'business\' and i.id_income_object= ' . $this->id_business . ' and i.state = 2 and t.state = 2';
      $finance_list = $this->list->get_settlement_info($where);

      $this->smarty->assign('finance_list', $finance_list);
      $this->smarty->display('list_apply.html');
  }


    /**
     * zxx
     * 结算列表
     **/
    public function list_finance()
    {
        $this->load->model('financeaccount_model', 'financeaccount');
        $where = 'id_object = ' . $this->id_business . ' and object_type = \'business\'';
        $finance_list = $this->financeaccount->get_finance_account($where);
        if($finance_list){
            $this->load->model('business_model', 'business');
            $this->load->model('hipigouser_model', 'hipigouser');
            foreach($finance_list as $k=>$v){
                $finance_info[$k]['object_name'] = '';
                if($v['object_type'] == 'business'){
                    $whereb = 'id_business = ' . $v['id_object'];
                    $bus_info = $this->business->get_business_phone($whereb);
                    $finance_list[$k]['object_name'] = $bus_info[0]['name'];
                }elseif($v['object_type'] == 'user'){
                    $whereu = 'id_user = ' . $v['id_object'];
                    $hipigo_info = $this->hipigouser->get_hipigo_user_info($whereu,2);
                    $finance_list[$k]['object_name'] = $hipigo_info[0]['nick_name'];
                }
            }
        }

        $this->smarty->assign('finance_list', $finance_list);
        $this->smarty->display('list_finance.html');
    }
}


/* End of file Finance.php */