<?php
/**
 * 图片控制器
 * @author vikie
 *
 */
class Tpicture extends WX_Controller
{
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * 图片上传
	 * 设置返回格式：POST字段ret_type，默认js回调，传json时返回json格式
	 * 
	 * @desc 上传多个图片需要注意， 如果使用swfupload可以直接调用这个方法，如果使用原生的input上传多个图片需要重写一个方法
	 */
	public function upload()
	{
		$this->load->model('picture_model', 'picture');
//        $aid = $this->input->get('aid');
		$path = $_POST['path']?$_POST['path']:$this->input->get('path');
        if(!$path){
            $path = 'community';
        }
		//加载图片配置
		$pic_config = array();
		$url = $_SERVER['DOCUMENT_ROOT'].'/attachment/business/'.$path.'/';
		
        $pic_config['upload_dir'] = $url;
        $pic_config['max_width'] = 10000;
        $pic_config['max_height'] = 10000;
        $pic_config['max_size'] = 10242880;
        $pic_config['allowed_types'] = 'gif|jpg|png|bmp|jpeg';

        //如果图片目录不存在，创建图片目录
		//$upload_path = $this->picture->get_save_path($pic_config['upload_dir']); (图片目录创建规则)
		$upload_path = $pic_config['upload_dir'];
		$fileName = md5(uniqid(TRUE));
        $fileName  = strtolower($fileName); //文件名不使用原始名

        $cdir = str_split($fileName,1);
        $tmp = array_chunk($cdir, 4);
        if($tmp[0]){
            $dir = implode('/', $tmp[0]);
        }

        $upload_path .= $dir;
		$pic_url = '/attachment/business/'.$path.'/'.$dir.'/';
        //当没有目录创建文件夹目录
        if( ! is_dir($upload_path)) {
            mkdir ($upload_path, 0777,TRUE);
        }
		if (! is_dir ( $upload_path )) {
			$this->_upload_status ( array (
					'status' => 0,
					'msg' => '创建目录失败' 
			) );
		}

		$config = array(
				'upload_path' => $upload_path,
				'file_name' => $fileName.'.jpg',
				'allowed_types' => $pic_config['allowed_types'],
				'max_size' => $pic_config['max_size'],
				'max_width' => $pic_config['max_width'],
				'max_height' => $pic_config['max_height'],
				'overwrite' => true
		);
		
		$this->load->library('upload', $config);

		if ( ! $this->upload->do_upload())
		{
			$error = $this->upload->display_errors();
			$this->_upload_status(array('status' => 0, 'msg' => $error));
		}
		else
		{
			$file = $this->upload->data();

            if($path == 'head'){
                //生成缩略图
                $result = $this->picture->resize_avatar($file['full_path']);
            }else{
                //裁剪并生成缩略图
                $result = $this->picture->resize($file['full_path'], false);
            }

			if($result)
			{
				$params = array(
						'path' => $pic_url.$file['orig_name'],
                        'filename' => $fileName.'.jpg',
						'info' => json_encode(array(
							'type' => $file['file_type'],
							'ext' => $file['file_ext'],
							'size' => $file['file_size'],
							'width' => $file['image_width'],
							'height' => $file['image_height'],
						)),
						'upload_date' => date('Y-m-d H:i:s'),
						);
			    $file_name = basename($params['path']);
            //评论上传图片存入数据库
            /*
            $arr=array('id_business'=>$this->bid,
			'id_shop'=>$this->sid,
			'object_type'=>'review',
			'id_object'=>$spking_id,
			'attachment_type'=>'image',
			'image_url'=>$file_name,
			'size'=>0);
            $img_id =  $this->community->insert_imgs($arr);*/
			    $callback = $path == 'head'?'show_img':'finishupload';
                if($_GET['con'] == '1'){
                    $callback = 'upload_side';//用来判断是上传的卡片反面照片
                }
                $file_name = basename($params['path']);
                $this->_upload_status(array(
                    'status' => 1,
                    'img_id' =>$img_id,
                    'url' => $params['path'],
                    'file_name'=>$file_name,
                    'callback'=>$callback
                ));
			}
			else
			{
				$this->_upload_status(array('status' => 0, 'msg' => '图片上传失败'));
			}
		}
	}
	
	//返回图片上传状态，修改
	private function _upload_status($ret)
	{
		$ret_type = $this->input->post('ret_type');
		//增加callback参数
		$callback = $this->input->post('callback');
		$callback = preg_match('/^[a-z_]\w*$/i', $callback) ? $callback : 'finishupload';
        if($ret['callback'] == 'show_img'){
            $callback = 'show_img';
        }elseif($_GET['con'] == '1'){
            $callback = 'upload_side';//用来判断是上传的卡片反面照片
        }
		if ('json' === $ret_type) {
			echo json_encode ($ret);					
		} else {
            echo "<script type='text/javascript'>window.parent.".$callback."(".json_encode($ret).")</script>";
		}
		exit ();
	}  
  
  /**
   * 上传头像
   * 
   */
  public function uploadPic()
  {
    $filePath = 'card';
    $path = $_GET['path']?$_GET['path']:'card';

    if( ! $filePath) {
      echo '上传错误';
      return;
    }
    //加载图片配置
    $pic_config = array(
      'upload_dir' => $_SERVER['DOCUMENT_ROOT'] . '/attachment/business/' . $path . '/',
      'max_width'  => 10000,
      'max_height' => 10000,
      'max_size'   => 10242880,
      'allowed_types' => 'gif|jpg|png|bmp|jpeg',
    );
    
    $upload_path = $pic_config['upload_dir'];
    
    $fileName = md5(uniqid(TRUE));
    $fileName  = strtolower($fileName); //文件名不使用原始名

    $cdir = str_split($fileName,1);
    $tmp = array_chunk($cdir, 4);
    if($tmp[0]){
        $dir = implode('/', $tmp[0]);
    }
    
    $pic_url = '/attachment/business/' . $path . '/' . $dir . '/';
    $upload_path .= $dir;
    
    //当没有目录创建文件夹目录
    if( ! is_dir($upload_path))
      mkdir ($upload_path, 0777,TRUE);
    
    if (! is_dir ( $upload_path )) {
      $this->_upload_status(array(
        'status' => 0,
           'msg' => '创建目录失败' 
      ));
    }
    
    $config = array(
      'upload_path' => $upload_path,
        'file_name' => $fileName . '.jpg',
    'allowed_types' => $pic_config['allowed_types'],
         'max_size' => $pic_config['max_size'],
        'max_width' => $pic_config['max_width'],
       'max_height' => $pic_config['max_height'],
        'overwrite' => true,
    );
    
    $this->load->library('upload', $config);
    if ($this->upload->do_upload($filePath)) {
      $file = $this->upload->data();
      $result = array(
        'code' => 1, 'msg' => 'upload success',
        'success' => $pic_url . $file['file_name'],
          'filename' => $file['file_name']
      );
      echo json_encode($result);exit;
    }
    else{
      $this->_upload_status(array('status' => 0, 'msg' => $this->upload->display_errors()));
    }
    
    //var_dump($file);exit;
    
  }
  
}