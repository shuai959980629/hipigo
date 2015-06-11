<?php
/**
 * 
 * @copyright(c) 2013-11-29
 * @author zxx
 * @version Id:Files.php
 */

class Files extends Admin_Controller
{
    public function upload_all_file($type){
//        file_put_contents(DOCUMENT_ROOT.'/123.txt',var_export($_FILES,TRUE),FILE_APPEND);
        $fileId = '';
        $max_height = 800;
        $min_height = 500;
        $max_width = 320;
        $min_width = 200;
        $file_path = '';
        $message = '图片上传尺寸不正确,详细见注释！';
        if($type == 'song'){//歌曲海报
            $max_width = 100;
            $max_height = 100;
            $min_width = 50;
            $min_height = 50;
        }
        if($type == 'songaudio'){
            $fileId = 'uploadaudio';
            $type = 'song';
        }else if($type == 'logo'){
            $fileId = 'uploadlogo';
            $message = 'logo上传尺寸有误,详细见注释！';
            $max_width = 10000;
            $max_height = 80;
            $min_width = 0;
            $min_height = 0;
        }else if($type == 'home'){
            $fileId = 'uploadfile';
            $max_width = 333;
            $max_height = 190;
            $min_width = 333;
            $min_height = 190;
        }else if($type == 'content'){
            $fileId = 'imgFile';
//            $max_width = 630;
//            $max_height = 350;
//            $min_width = 360;
//            $min_height = 200;
            $max_width = 1920;
            $max_height = 1080;
            $min_width = 1;
            $min_height = 1;
        }elseif($type == 'ticket_editor'){
            $fileId = 'imgFile';
            $file_path = $type;
            $type = substr($type,stripos($type, '_'));
            $max_width = 1920;
            $max_height = 1080;
            $min_width = 1;
            $min_height = 1;
        }elseif($type == 'reply'){
            $fileId = 'uploadfile';
            $max_width = 630;
            $max_height = 350;
            $min_width = 360;
            $min_height = 200;
        }elseif($type == 'keyword_reply'){
            $fileId = 'uploadfile';
            $max_width = 630;
            $max_height = 350;
            $min_width = 360;
            $min_height = 200;
        }elseif($type == 'ticket'){
            $fileId = 'uploadfile';
            $max_width = 50;
            $max_height = 50;
            $min_width = 50;
            $min_height = 50;
        }elseif($type == 'resource'){
            $fileId = 'uploadfile';
            $max_width = 1920;
            $max_height = 1080;
            $min_width = 1;
            $min_height = 1;
        }elseif($type == 'customer'){
            $fileId = 'uploadfile';
            $max_width = 1920;
            $max_height = 1080;
            $min_width = 1;
            $min_height = 1;
        }elseif($type == 'before_train'||$type == 'after_train'){
            $fileId = 'imgFile';
            $max_width = 1920;
            $max_height = 1080;
            $min_width = 1;
            $min_height = 1;
        }
        else{
            $fileId = 'uploadfile';
            if($type == 'attachment' || $type == 'shop' || $type == 'site'){//企业介绍/门店展示//微官网配置页面的顶部滚动图片
                $max_width = 800;
                $max_height = 500;
                $min_width = 320;
                $min_height = 200;
            }else if($type == 'banner'){
                $max_width = 10000;
                $max_height = 10000;
                $min_width = 1;
                $min_height = 1;
            }else if($type == 'commodity' || $type == 'activity'){//物品、活动、内容
                $max_width = 630;
                $max_height = 350;
                $min_width = 360;
                $min_height = 200;
            }else if($type == 'attachment_editor' || $type == 'shop_editor'){//企业介绍/门店展示   编辑框上传
                $fileId = 'imgFile';
                $file_path = $type;
                $type = substr($type,stripos($type, '_'));
                $max_width = 800;
                $max_height = 500;
                $min_width = 320;
                $min_height = 200;
            }else if($type == 'commodity_editor' || $type == 'activity_editor' || $type == 'lyric_editor'){//物品、活动、内容  编辑框上传
                $fileId = 'imgFile';
                $file_path = $type;
                $type = substr($type,stripos($type, '_'));
                $max_width = 630;
                $max_height = 350;
                $min_width = 360;
                $min_height = 200;
            }else if($type == 'community'){
                $max_width = 1920;
                $max_height = 1080;
                $min_width = 1;
                $min_height = 1;
            }else if($type == 'community_editor'){//v社区活动 编辑框上传
                $fileId = 'imgFile';
                $file_path = $type;
                $type = substr($type,stripos($type, '_'));
//                $max_width = 630;
//                $max_height = 350;
//                $min_width = 360;
//                $min_height = 200;
            }
        }
        $logo_img_name = $_FILES[$fileId]['name'];
        if($fileId == 'uploadfile' && ($type == 'reply' || $type == 'keyword_reply')){
            $size=$_FILES[$fileId]['size'];
            if($_FILES[$fileId]['type'] == 'image/jpg'){
                $tmp_name = getimagesize($_FILES[$fileId]['tmp_name']);
                $img_width = $tmp_name[0];
                $img_height = $tmp_name[1];
                if(!($img_width >= $min_width && $img_width <= $max_width && $img_height >= $min_height && $img_height <= $max_height)){
                    header('Content-type: text/html; charset=UTF-8');
                    echo json_encode(array('error' => 1, 'message' => $message));
                    exit;
                }
                if($size>128*1024){
                    unlink($_FILES[$fileId]['tmp_name']);
                    echo json_encode(array('error' => 1, 'message' => '请上传的图片文件大小在128k内！'));
                    exit;
                }
            }elseif($_FILES[$fileId]['type'] == 'application/octet-stream'){
                $tmp = stripos($_FILES[$fileId]['name'], '.');
                $file_type = substr($_FILES[$fileId]['name'],$tmp+1);
                if(strtolower($file_type) == 'amr'){//strtolower($file_type) == 'mp3' ||
                    if($size>256*1024){
                        unlink($_FILES[$fileId]['tmp_name']);
                        echo json_encode(array('error' => 1, 'message' => '请上传的音频文件大小在256k内！'));
                        exit;
                    }
                }elseif(strtolower($file_type) == 'mp4'){
                    if($size>1024*1024){
                        unlink($_FILES[$fileId]['tmp_name']);
                        echo json_encode(array('error' => 1, 'message' => '请上传的视频文件大小在1M内！'));
                        exit;
                    }
                }elseif(strtolower($file_type) == 'jpg'){
                    if($size>128*1024){
                        unlink($_FILES[$fileId]['tmp_name']);
                        echo json_encode(array('error' => 1, 'message' => '请上传的图片文件大小在128k内！'));
                        exit;
                    }
                }
            }
        }elseif($fileId == 'uploadaudio'){
            if($_FILES[$fileId]['size'] > 10*1024*1024){
                unlink($_FILES[$fileId]['tmp_name']);
                echo json_encode(array('error' => 1, 'message' => '请上传的音频文件大小在10M内！'));
                exit;
            }
        }elseif($fileId != 'uploadaudio'){
            if($_FILES[$fileId]['type'] == 'image/gif' || $_FILES[$fileId]['type'] == 'image/jpg' || $_FILES[$fileId]['type'] == 'image/png'
                || $_FILES[$fileId]['type'] == 'image/jpeg' || $_FILES[$fileId]['type'] == 'application/octet-stream'){
                $tmp_name = getimagesize($_FILES[$fileId]['tmp_name']);
                $img_width = $tmp_name[0];
                $img_height = $tmp_name[1];

                if($type == 'logo'){
                    if($img_height != 80){
                        header('Content-type: text/html; charset=UTF-8');
                        echo json_encode(array('error' => 1, 'message' => $message));
                        exit;
                    }
                }elseif($type == 'home'){
                    if($img_width != 333 || $img_height != 190){
                        header('Content-type: text/html; charset=UTF-8');
                        echo json_encode(array('error' => 1, 'message' => $message));
                        exit;
                    }
                    if($_FILES[$fileId]['size'] > 150*1024){
                        unlink($_FILES[$fileId]['tmp_name']);
                        echo json_encode(array('error' => 1, 'message' => '请上传的图片文件大小在150K内！'));
                        exit;
                    }
                }elseif($type == 'ticket'){
                    if($img_width != 50 || $img_height != 50){
                        header('Content-type: text/html; charset=UTF-8');
                        echo json_encode(array('error' => 1, 'message' => $message));
                        exit;
                    }
                }elseif($type == 'community'){
                    if(!($img_width >= $min_width && $img_height >= $min_height && $img_width <= $max_width && $img_height <= $max_height)){
                        header('Content-type: text/html; charset=UTF-8');
                        echo json_encode(array('error' => 1, 'message' => $message));
                        exit;
                    }
                    if($_FILES[$fileId]['size'] > 2*1024*1024){
                        unlink($_FILES[$fileId]['tmp_name']);
                        echo json_encode(array('error' => 1, 'message' => '请上传的图片文件大小在2M内！'));
                        exit;
                    }
                }elseif($type == 'resource' || $type == 'customer' || $type == 'before_train'||$type == 'after_train'){
                    if(!($img_width >= $min_width && $img_height >= $min_height && $img_width <= $max_width && $img_height <= $max_height)){
                        header('Content-type: text/html; charset=UTF-8');
                        echo json_encode(array('error' => 1, 'message' => $message));
                        exit;
                    }
                    if($_FILES[$fileId]['size'] > 2*1024*1024){
                        unlink($_FILES[$fileId]['tmp_name']);
                        echo json_encode(array('error' => 1, 'message' => '请上传的图片文件大小在2M内！'));
                        exit;
                    }
                }elseif($file_path == 'community_editor' || $file_path == 'ticket_editor'){
                }else{
                    if(!($img_width >= $min_width && $img_width <= $max_width && $img_height >= $min_height && $img_height <= $max_height)){
                        header('Content-type: text/html; charset=UTF-8');
                        echo json_encode(array('error' => 1, 'message' => $message));
                        exit;
                    }
                }
            }elseif($_FILES[$fileId]['type'] == 'audio/mpeg' || $_FILES[$fileId]['type'] == 'audio/ogg' || $_FILES[$fileId]['type'] == 'audio/wav'
                || $_FILES[$fileId]['type'] == 'video/mp4' || $_FILES[$fileId]['type'] == 'application/x-shockwave-flash'){
                $fileId = 'content_audio';
                if($_FILES[$fileId]['size'] > 10*1024*1024){
                    unlink($_FILES[$fileId]['tmp_name']);
                    echo json_encode(array('error' => 1, 'message' => '请上传的音,视频文件大小在10M内！'));
                    exit;
                }
            }else{
                header('Content-type: text/html; charset=UTF-8');
                echo json_encode(array('error' => 1, 'message' => '上传文件的类型不能被支持！'));
                exit;
            }
        }

        $this->upload_files($fileId,$logo_img_name,$type,$file_path);
//        file_put_contents(DOCUMENT_ROOT.'/a.txt',var_export($return_logo,TRUE));

//        $uploaddir = DOCUMENT_ROOT . '/upload/temps/';
//        //编译文件名称 base64编码
//        $fileName = base64_encode(pathinfo($_FILES['uploadfile']['name'], PATHINFO_BASENAME)) .
//            '.' . pathinfo($_FILES['uploadfile']['name'], PATHINFO_EXTENSION);
//
//        $file = $uploaddir . basename($fileName);
//        $size=$_FILES['uploadfile']['size'];
////        if($size>1048576)
////        {
////            echo "error file size > 1 MB";
////            unlink($_FILES['uploadfile']['tmp_name']);
////            exit;
////        }
////        var_dump($_FILES);
//        if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $file)) {
//            echo $fileName;
//        } else {
//            //echo "error ".$_FILES['uploadfile']['error']." --- ".$_FILES['uploadfile']['tmp_name']." %%% ".$file."($size)";
//            echo $fileName;
//        }
    }


    /*
     * zxx
     * 上传图片附件
     * $f_type  判断是否是编辑框传来的附件
     * */
    function upload_files($name = 'firmimg',$firm_img_name='',$file_path = 'attachment',$f_type='') {
        $path = '/attachment/business/'.$file_path.'/';
        $config['upload_path'] = DOCUMENT_ROOT.$path;
        if($file_path == 'content' || $f_type == 'community_editor' || $f_type == 'ticket_editor'){
//            $config['allowed_types'] = 'mp4|mp3|ogg|wav|gif|jpg|png';
            $config['allowed_types'] = 'jpeg|gif|jpg|png';
        }elseif($file_path == 'reply' || $file_path == 'keyword_reply'){
            $config['allowed_types'] = 'mp4|amr|jpg';
        }elseif($file_path == 'community' ||  $file_path == 'site' ||  $file_path == 'resource' || $file_path == 'customer'
            || $file_path == 'before_train'||$file_path == 'after_train'){
            $config['allowed_types'] = 'gif|jpg|png';
        }else{
            $config['allowed_types'] = 'mp3|ogg|wav|gif|jpg|png';
        }

        if($name != 'uploadaudio' && $name != 'content_audio'){
            if($file_path == 'reply' || $file_path == 'keyword_reply'){
                $config['max_size'] = '1048576';//1M为最大限制
            }else{
                $config['max_size'] = '2048';
            }
//            $config['max_width']  = '1200';
//            $config['max_height']  = '1200';
        }else{
            $config['max_size'] = '10240';
        }
        $config['encrypt_name'] = FALSE;
        //编译文件名称 base64编码
        $fileName = md5(uniqid(TRUE)) .
            '.' . pathinfo($firm_img_name, PATHINFO_EXTENSION);
        $config['file_name']  = strtolower($fileName); //文件名不使用原始名

        $cdir = str_split($config['file_name'],1);
        $tmp = array_chunk($cdir, 4);
        if($tmp[0]){
            $dir = implode('/', $tmp[0]);
        }

        $config['upload_path'] .= $dir;
        if( ! is_dir($config['upload_path'])) {
            $re = $this->makeDir($config['upload_path']);
//            file_put_contents(DOCUMENT_ROOT.'/lll.txt',var_export($config['upload_path'],TRUE),FILE_APPEND);
            if(!$re){
                echo json_encode(array('error' => 1, 'message' => '创建文件出现错误！'));
                exit;
            }
        }

        //当没有目录创建文件夹目录
        if( ! is_dir($config['upload_path'])) {
            mkdir ($config['upload_path'], 0777,TRUE);
        }

        $this->load->library('upload', $config);

        if($name == 'content_audio'){
            $name = 'imgFile';
        }
        if(!$this->upload->do_upload($name))
        {
            $data =  $this->upload->display_errors();
            header('Content-type: text/html; charset=UTF-8');
            echo json_encode(array('error' => 1, 'message' => '上传文件出现错误！'));
            exit;
        }
        else
        {
            $data = $this->upload->data();
            if($file_path == 'community'){
                $this->load->model('picture_model', 'picture');
                //裁剪并生成缩略图
//                $result = $this->picture->resize($data['full_path'], true,array(180,225));
                //裁剪图片
//                $result = $this->picture->crop(array('file_path' => $data['full_path'], 'width' => array(180,70), 'height' =>  array(225,70), 'x' => 0, 'y' => 0));
                resize($data['full_path'],180,225,1,'big');
                resize($data['full_path'],70,70,1,'small');
                //生成缩略图并填充白色背景
                $result = resize($data['full_path'],180,225);
                if($result){
                    $img_info = explode('.',$data['file_name']);
                    $return = $img_info[0].'-180x225.'.$img_info[1];
                }
                else
                {
                    header('Content-type: text/html; charset=UTF-8');
                    echo json_encode(array('error' => 1, 'message' => '图片上传失败！'));
                    exit;
                }
            }elseif($file_path == 'content'){
                $this->load->model('image_model', 'image');
                if($_FILES[$name]['size'] > 1024*300){
                    //裁剪并生成缩略图
                    $this->cropImage($data['full_path'],$data['full_path'], 1080, 1920, 0, 20);
                }
                $return = $data['file_name'];
            }elseif($f_type == 'ticket_editor'){
                $this->load->model('image_model', 'image');
                $tmp_name = getimagesize($_FILES[$name]['tmp_name']);
                $img_width = $tmp_name[0];
                $img_height = $tmp_name[1];
                if($_FILES[$name]['size'] > 1024*300 || ($img_width > 1080 || $img_height > 1920)){
                    //裁剪并生成缩略图
                    $this->cropImage($data['full_path'],$data['full_path'], 1080, 1920, 0, 20);
                }
                $return = $data['file_name'];
            }elseif($f_type == 'community_editor'){
                $this->load->model('image_model', 'image');

                $tmp_name = getimagesize($_FILES[$name]['tmp_name']);
                $img_width = $tmp_name[0];
                $img_height = $tmp_name[1];
                $fi = $data['full_path'];

                $info = pathinfo($fi);
                $hz = '.'.$info['extension'];

                $file_names = substr($fi,0,strpos($fi,$hz));
                if($_FILES[$name]['size'] > 1024*300 || ($img_width > 1080 || $img_height > 1920)){
//                    $file_name1 = $file_names. '_1080x1920.png';
                    $file_name3 = $file_names. '-small'.$hz;
                    //裁剪并生成缩略图
                    $this->cropImage($data['full_path'],$data['full_path'], 1080, 1920, 0, 20);
                    //裁剪并生成缩略图
                    $this->cropImage($data['full_path'],$file_name3, 70, 70, 0, 20);
                }else{
//                    $file_names = substr($data['full_path'],0,strpos($data['full_path'],'.png'));
                    $file_name2 = $file_names. '-small'.$hz;
                    //裁剪并生成缩略图
                    $this->cropImage($data['full_path'],$file_name2, 70, 70, 0, 20);
                }
                $return = $data['file_name'];
            }else{
                $return = $data['file_name'];
            }
        }
        if($file_path == 'site' || $file_path == 'content' || $file_path == 'community'||$file_path == 'banner' || $file_path == 'attachment'
            || $file_path == 'logo' || $file_path == 'shop' || $file_path == 'ticket' || $file_path == 'activity' || $file_path == 'commodity'
            || $file_path == 'song' || $f_type == 'attachment_editor' || $f_type == 'shop_editor' || $f_type == 'commodity_editor'
            || $f_type == 'activity_editor' || $f_type == 'lyric_editor' || $f_type == 'community_editor' || $f_type == 'ticket_editor'
            || $file_path == 'resource' || $file_path == 'customer'|| $file_path == 'before_train'||$file_path == 'after_train'){
            $url = $path . $dir.'/'.$return;
            header('Content-type: text/html; charset=UTF-8');
            if( $file_path == 'community' || $file_path == 'site' || $file_path == 'resource' || $file_path == 'customer'
                || $file_path == 'before_train'||$file_path == 'after_train' || $file_path == 'attachment' || $file_path == 'logo'
                || $file_path == 'shop' || $file_path == 'ticket' || $file_path == 'activity' || $file_path == 'commodity' || $file_path == 'song'){
                echo json_encode(array('error' => 0, 'url' => $url,'file_name'=>$data['file_name']));
            }else{
                echo json_encode(array('error' => 0, 'url' => $url,'file_name'=>$return));
            }
            exit;
        }else{
            header('Content-type: text/html; charset=UTF-8');
            if($file_path == 'home'){
                $url = $path . $dir.'/'.$return;
                echo json_encode(array('error' => 0, 'message' => $return,'path' => $url));
            }else{
                echo json_encode(array('error' => 0, 'message' => $return));
            }
            exit;
        }
    }


}


/* End of file settings.php */