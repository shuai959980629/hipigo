<?php
/**
 * 资源库管理
 * @copyright(c) 2014-06-26
 * @author Jamai
 * @version 2.0
 */
class Resources extends Admin_Controller{
  
  private $id_business;
  
  public function __construct() 
  {
      parent::__construct();
      $this->load->model('resources_model', 'resources');
      $this->id_business = $this->users['id_business'];
      $this->id_shop = $this->users['id_shop'];

      if(empty($this->users) || empty($this->session_id)){
          header('location:'.$this->url.'user/login');
          die ;
      }
  }
  
  /**
   * 发布资源
   *
   * @version 2.0
   * @author Jamai
   */
//  public function publish()
//  {
//    if($this->input->post() !== false) {
//
//    }
//
//    $pageParam['url_action'] = 'resources/publish';
//
//    $this->smarty->assign($pageParam);
//
//    $this->smarty->view('add_resources');
//  }


    /**
     * zxx
     * 添加资源管理信息
     */
    public function publish($id_resource=0){
        $resourceID = $id_resource;
        $data ['url_action'] = 'resources/publish';

        $this->load->model('business_model', 'business');
        if($this->input->post('title'))
        {
            $this->load->library('form_validation');
            if ($this->form_validation->run('add_resource') === TRUE)
            {
                $id_resource = $this->input->post('id_resource');//商品名
                $title = $this->input->post('title');//商品名
                $content = $this->input->post('content');//活动内容
                $price = $this->input->post('price');//价格
                $total = $this->input->post('total') == '' ? -1 : $this->input->post('total');//数量
                $date = $this->input->post('date');//截止时间
                $address = $this->input->post('address');//地址
                $lat = $this->input->post('lat');//地址经度
                $lon = $this->input->post('lon');//地址纬度
                $image_name = $this->input->post('image_name');//图片名称
                $image_src = $this->input->post('image_src');//图片路径

                $delete_id_res = $this->input->post('delete_id_res');//删除的图片附件id

                $title = $this->replace_html($title);
                $this->load->model('community_model', 'community');
                //替换表情
                $content = $this->community->replace_smile($content);
                $date_time = strtotime($date);
                if($id_resource){
                    $resourceID = $id_resource;
                    $update_info = array(
                        'resource_title' => $title,
                        'price' => $price,
                        'num' => $total,
                        'desc' => $content,
                        'deadline'=>$date_time,
                        'addr'=>$address,
                        'lon'=>$lon,
                        'lat'=>$lat
                    );
                    $this->resources->update_resource($update_info,'id_resource = '.$id_resource,1);

                    $d_id_res = array();
                    if($delete_id_res != ''){
                        $d_id_res = explode(',',$delete_id_res);
                        foreach($d_id_res as $rid){
                            $where = 'id_attachment = ' . $rid;
                            $this->business->delete_synopsis_image($where);
                        }
                    }
                }else{
                    $res_info = array(
                        'owner' => $this->id_business,
                        'resource_title' => $title,
                        'price' => $price,
                        'num' => $total,
                        'desc' => $content,
                        'deadline'=>$date_time,
                        'addr'=>$address,
                        'lon'=>$lon,
                        'lat'=>$lat,
                        'source'=>0,
                        'is_level'=>0,
                        'deleted'=>1,
                        'created'=>date('Y-m-d H:i:s', time())
                    );
                    $id_res = $this->resources->insert_resource($res_info);
                    $resourceID = $id_res;
                }

                if($image_name){
                    foreach($image_name as $k=>$v_img){
                        //获取本地图片文件大小
                        $data = $this->get_url_size($image_src[$k]);
                        $file_size = $data['file_size'];
                        $business_attachment = array(
                            'id_business' => $this->id_business,
                            'id_shop' =>  $this->id_shop,
                            'object_type' => 'resource',
                            'id_object' => $resourceID,
                            'attachment_type' => 'image',
                            'image_url' => $v_img,
                            'size' => $file_size,
                        );
                        $this->business->insert_business_attachment($business_attachment);
                    }
                }

                $data_page['url'] = $this->url.'resources/resource_by'.$this->tpl[0];
                $data_page['is_refresh'] = 2;
//                $data_page['page'] = 'resource';
                $this->returen_status(1,$this->lang->line('add_data_success'),$data_page);
            }
            else
            {
                $this->returen_status(0,$this->form_validation->error_string(),$data);
            }
        }else{
            $type = 'add';
            if($id_resource){
                $type = 'edit';
                $where = 'id_resource = ' . $id_resource;
                $res_info = $this->resources->get_resources($where);
                if($res_info){
                    if($res_info[0]['num'] <= -1)
                        $res_info[0]['num'] = -1;
                    $data['resource_info'] = $res_info[0];
                }
                $wheres = 'object_type = \'resource\' and id_object = ' . $id_resource;
                $resource_att = $this->business->get_business_attachment($wheres);
                $data['resource_att'] = $resource_att;
            }
            //查询评论表情
            $smile = _get_smile_array();
            $row = array();
            foreach( $smile as $key =>$val){
                $row[$key] = '/smile/'.$val;
            }
            $row = array_slice($row,0,14);

            $this->smarty->assign('row',$row);
            $this->smarty->assign($data);
            $this->smarty->assign('id_resource',$id_resource);
            $this->smarty->assign('type',$type);
            $this->smarty->view('add_resources');

        }
    }



    /**
   * 获取我的资源  商户
   *
   * @version 2.0
   * @author Jamai
   */
    public function resource_by()
    {
        $where = array();
        if($this->id_business)
            $where = array('owner' => $this->id_business);

        $pager  = $this->config->item('page');
        $page   = ! $this->input->get('offset') ? 1 : $this->input->get('offset');
//        $offset = ($page-1)*$pager;//$pager * ($page > 0 ? ($page - 1) : 0);
        $key    = trim($this->input->get('search'));

        $resources = $this->resources->get_resources($where, $order = 'created DESC',
            $page, $pager, $key);

        //获取互动活动总数
        $count = $this->resources->get_resources_count($where, $key);

        //分页代码
        $page_html = $this->get_page($count, $page, 'resource_by_page');

        if($key){
            $this->load->model('tag_model', 'tag');
            $where = 'tag_name like \'%'.$key.'%\'';
            $tag_info = $this->tag->get_tag($where);
            if($tag_info){
                $data_u = array(
                    'search_num'=>intval($tag_info[0]['search_num'])+1
                );
                $this->tag->update_tag($data_u,$where);
            }else{
                $data_i = array(
                    'tag_name'=>$key,
                    'search_num'=>1,
                    'is_level'=>0,
                    'created'=>date('Y-m-d H:i:s',time()),
                );
                $this->tag->insert_tag($data_i);
            }
        }

        $this->smarty->assign('page_html', $page_html);
        $this->smarty->assign('key', $key);
        $this->smarty->assign('resources', $resources);

        $this->smarty->view('list_by_resources');
    }
  
  /**
   * 关闭资源
   *
   * @version 2.0
   * @author Jamai
   */
  public function disable()
  {
    $value = $this->input->post('value');
    $data = array('deleted' => 0);
    
    if(is_array($value) && $value) {//多个关闭
      foreach ($value as $k => $v) 
        $result = $this->resources->update_resource($data, array('id_resource' => $v));
      if($result)
        echo json_encode(1);
    }
    else {
      if($value) { // 单个关闭
        $result = $this->resources->update_resource($data, array('id_resource' => $value));
        
        if($result)
          echo json_encode(1);
      }
      else 
        echo json_encode(0);
    }
    return;
  }
  
  /**
   * 获取资源库资源
   *
   * @version 2.0
   * @author Jamai
   */
  public function resource_list()
  {
    $pager  = $pager ? $pager : ($this->config->item('page'));
    $page   = ! $this->input->get('offset') ? 1 : $this->input->get('offset');
//    $offset = $pager * ($page > 0 ? ($page - 1) : 0);
    $key    = trim($this->input->get('search'));
    $where  = array();
    
    $resources = $this->resources->get_resources($where, $order = 'created DESC',
        $page, $pager, $key);
          
    //获取互动活动总数
    $count = $this->resources->count_resources($where, $key);
    //分页代码
    $page_html = $this->get_page($count, $page, 'resource_list_page');

    $this->smarty->assign('page_html', $page_html);
    $this->smarty->assign('key', $key);
    $this->smarty->assign('resources', $resources);
    $this->smarty->view('list_resources');
  }
  
  /**
   * 资源详情
   *
   * @version 2.0
   * @author Jamai
   */
    public function resource_info()
    {
        $id = $this->input->post('id');
        if( ! empty($id)) {
            $resource = $this->resources->get_resource_info($id);

            $data['resource'] = $resource;

//            $this->load->model('business_model', 'business');
//            $where = 'object_type = \'resource\' and id_object = ' . $id;
//            $resource_att = $this->business->get_business_attachment($where);
//            $data['resource_att'] = $resource_att;

            $this->smarty->assign($data);
            echo $this->smarty->view('resource_info');
            return;
        }
        echo 0;return;
    }
}