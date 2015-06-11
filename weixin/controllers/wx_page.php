<?php
/**
 * 
 * @copyright(c) 2014-05-15
 * 
 */

class Wx_page extends WX_Controller
{

    private $id_business ;//商家ID
    private $id_shop ;//门店ID
    public function __construct(){
		parent::__construct();
		$this->id_business = $this->bid;
        $this->id_shop = $this->sid;
	}


    /**
     * zxx
     * 微官网页面分类的其他页面
     */
    public function page_two($num)
    {
        $this->load->model('business_model', 'business');
        $business_id = $this->business_id;
        //获取商家信息
        $where = 'ba.object_type = \'bn\' and ba.id_object = 0';
        $merchant = $this->business->get_business_info($business_id, $where);
        $images = explode(',', $merchant[0]['image_url']);
        foreach ($images as $value) {
            $path[] = get_img_url($value, 'attachment', 0, 'bg_mr.png');
        }

        $url = $_SERVER ['HTTP_HOST'];
        $url = mysql_escape_string($url);
        $where = 'b.sld = \''.$url . '\' and c.id_column = '.$num;
        $return = $this->business->get_bs_home_inf($where);
        for($i=0;$i<count($return);$i++){
            $return[$i]['image'] = get_img_url($return[$i]['image'], 'home', 0, 'bg_mr.png');
            $this->get_link_url($return[$i]['type'],$return[$i]['object_type'],$return[$i]['id_object'],$return[$i]['url'],$return[$i]['visit_mode'],$return[$i]['id_business']);
        }

        $this->smarty->assign("information", $return);
        $this->smarty->assign('images', $path);
        $this->smarty->assign('introduction', html_entity_decode($merchant[0]['introduction'],ENT_COMPAT, 'utf-8'));
        $this->smarty->view('page_column');
    }



}