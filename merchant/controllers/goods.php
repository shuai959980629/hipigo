<?php
/**
 * 
 * @copyright(c) 2013-11-22
 * @author msi
 * @version Id:goods.php
 */


class Goods extends Admin_Controller{

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
	 * 列表
	 */
	public function list_goods($offset=0,$ispage=0){
		$this->load->helper('url');
		$this->load->model('goods_model');
        if($offset == null)
            $offset = $this->input->post('offset') ? intval($this->input->post('offset')) : 1;//页码
        $start = ($offset - 1) * $this->config->item('page');

        $where_count = 'id_shop = '.$this->id_shop;
        $total = $this->goods_model->get_goods_count($where_count);

        $where = 'a.id_shop = '.$this->id_shop;
        $mdata = $this->goods_model->get_goods_list($start,$this->config->item('page'),$where);

        $this->load->model('review_model','review');
        if(!$mdata){
            $offset = 1;
            $start = ($offset - 1) * $this->config->item('page');
            $mdata = $this->goods_model->get_goods_list($start,$this->config->item('page'),$where);
        }

        $page_arr = $this->get_page($total,$offset,'goods_list_page');

//        debug($mdata);
        foreach($mdata as $key=>$md){
            $where = 'r.id_object = '.$md['id_commodity'] .' and r.object_type = \'commodity\' and r.state=1 AND r.id_business = ' . $this->id_business . ' AND r.id_shop = ' . $this->id_shop;
            $return =$this->review->get_review_count($where);
            $mdata[$key]['review_count'] = $return;
        }
        $this->smarty->assign('page_html',$page_arr);
        $this->smarty->assign('glist',$mdata);
        $this->smarty->assign('offset',$offset);
        $this->smarty->assign('page_type','goods');
        if($ispage == null){
            $ispage = $this->input->post('ispage');
        }
        if(intval($ispage) == 1){
            echo $this->smarty->view('lists');
        }else if(intval($ispage == 2)){
            $data = $this->smarty->fetch('lists.html');
            $resp = array(
                'status' => 1,
                'msg' => '',
                'data' => $data
            );
            die(json_encode($resp));
        }else{
            $this->smarty->view('list_goods');
        }
	}

    /**
	 * 删除
	 */
	public function del_goods(){
        $id_goods = $this->input->post('id_goods');
        $ispage = $this->input->post('ispage');
        $offset = $this->input->post('offset');
        $id_goods = intval($id_goods);
        $this->load->model('goods_model');
        $this->goods_model->del_goods(array('id_mall'=>$id_goods,'id_shop'=>$this->id_shop));

        $this->list_goods($offset,$ispage);
	}

	
	/**
	 * 设置推荐
	 * @param id $id
	 */
	public function set_hot(){
        $id_mall = $this->input->post('id_mall');
        $num = $this->input->post('num');

        $this->load->model('goods_model');
        $where = 'id_mall = '.$id_mall;
        $this->goods_model->edit_goods(array('recommend'=>intval($num)),$where);

        echo 'true';
	}
	
	public function set_sale_stuts(){
        $id_mall = $this->input->post('id_mall');
        $num = $this->input->post('num');

        $this->load->model('goods_model');
        $status = '';
        if(intval($num) == 1){
            $status = 'up';
        }else{
            $status = 'down';
        }
        $where = 'id_mall = '.$id_mall;
        $this->goods_model->edit_goods(array('state'=>$status),$where);

        echo 'true';
	}

    //$id   物品id
	public function list_comment($id=0,$page=0){
        if(!$id){
            $id = $this->input->post('id_commodity');
        }
        if(!$page){
            $page = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        }
        $id_class = $this->input->post('id_class') ? $this->input->post('id_class') : 'all';//评论分类

		$page = intval($page) < 1 ? 0 : intval($page);
		$id = intval($id);
        $data = $this->get_reviews($id,$page,$id_class);

		$this->smarty->assign('cpage',$page);
        $this->smarty->assign('id_commodity',$id);
        $this->smarty->assign('page_type','comments');
        $this->smarty->assign($data);

        if(intval($this->input->post('ispage')) == 1){
            echo $this->smarty->view('lists');
        }else{
            $this->smarty->view('list_comments');
        }
	}
	
	public function del_review(){
        $page = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        $id = $this->input->post('id_review');//评论id
        $id_commodity = $this->input->post('id_commodity');//物品id
        $id_class = $this->input->post('id_class') ? $this->input->post('id_class') : 'all';//评论分类

        $page = intval($page) < 1 ? 0 : intval($page);
        $id = intval($id);

        $this->load->model('goods_model');
//        $this->goods_model->del_review(array('id_review'=>$id));
        $data_goods['state'] = 0;
        $this->goods_model->delete_review_state($data_goods,array('id_review'=>$id));

        //删除评论信息
        $data = $this->get_reviews($id_commodity,$page,$id_class);

        $this->smarty->assign('cpage',$page);
        $this->smarty->assign('id_commodity',$id_commodity);
        $this->smarty->assign('page_type','comments');
        $this->smarty->assign($data);

        $data = $this->smarty->fetch('lists.html');
        $resp = array(
            'status' => 1,
            'msg' => '',
            'data' => $data
        );
        die(json_encode($resp));
	}

    /*
     * 获取评论列表
     * zxx
     * **/
    function get_reviews($id,$page,$id_class){
        $data = array();
        $where = 'c.id_shop = '. $this->id_shop . ' and c.id_business = '.$this->id_business.' and c.state = 1 and r.state = 1';
        if($id_class != 'all'){
            $where .= ' and r.object_type = \'commodity\'';
        }
//        $start = ($page-1)*($this->config->item('page'));
        if(!empty($id)){
            $where .= ' and r.id_object = '.$id;
        }

        $this->load->model('commodity_model','commodity');
        if($id_class != 'all'){
            $where .= ' and c.id_class = '.$id_class;
        }
        //获取物品列表
        $review_info = $this->commodity->get_commodity_introduction($where, $page,$this->config->item('page'),'c.id_commodity desc',2);
        if(!$review_info){
            $page = 1;
            $review_info = $this->commodity->get_commodity_introduction($where, $page,$this->config->item('page'),'c.id_commodity desc',2);
        }
        //获取物品总数
        $review_count = $this->commodity->get_commodity_count($where,2);

        //分页代码
        $page_html = $this->get_page($review_count, $page, 'review_list_page');
        //物品分类
        $where_class = 'id_business = '.$this->id_business. ' and type = \'cmd\'';
        $data['commodity_class'] = $this->commodity->get_commodity_class($where_class);

        $data['lreview'] = $review_info;
        $data['page_html'] = $page_html;
        return $data;
    }
}
/* End of file goods.php */