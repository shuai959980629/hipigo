<?php
/**
 * 通用图片MODEL
 * @author vikie
 *
 */
class Picture_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        //$this->load->database();
    }
    //评论图片设置
    private $_types = array(
                array(
                        'type' => 'big',
                        'width' => 960,
                        ),
                array(
                        'type' => 'middle',
                        'width' => 720,
                        ),
                array(
                        'type' => 'small',
                        'width' => 220,
                        )
            );
    //头像设置
    private $_avatar_types = array(
            array(
                    'type' => 'middle',
                    'width' => 80,
            ),
            array(
                    'type' => 'small',
                    'width' => 50,
            )
    );
    /**
     * 获取图片保存路径
     * @param string $upload_path
     */
    public function get_save_path($upload_path) {
        $save_path = $upload_path . date ( 'Y/n/j/' );
        
        if (! is_dir ( $save_path ))
            mkdir ( $save_path, 0777, true );
        
        return $save_path;
    }
    
    /**
     * 生成文件名
     * 时间戳-随机4位数
     */
    public function gen_file_name()
    {
        return time().'-'.str_pad(mt_rand(0, 9999), 4, '0');
    }
    
    /**
     * 根据不同的TYPE格式化图片地址
     * @param string $picture_url
     * @param string $type {big | middle | small}
     */
    public function format_picture_url($picture_url, $type)
    {
        $last_dot_pos = strrpos($picture_url, '.');
        $file_ext = substr($picture_url, $last_dot_pos);
        $formated_picture_url = substr($picture_url, 0, $last_dot_pos).'-'.$type.$file_ext;
        return $formated_picture_url;
    }
    
    /**
     * 生成头像缩略图
     * 头像一定会被裁剪
     * @param int $uid
     * @param string $avatar_dir 
     * @param string $file
     */
    public function resize_avatar($file_path)
    {
        if(! file_exists($file_path))
		return false;
        
        $this->load->library('image_lib');
        $result = array();
        
        $info = getimagesize($file_path);
		//以高宽较小值为准剪切掉多余部分
        $width = $height = $info[0] > $info[1] ? $info[1] : $info[0];
        $options = array(
                'file_path' => $file_path,
                'width' => $width,
                'height' => $height,
                'x' => 0,
                'y' => 0
        );
        $this->crop($options);  
		//dirname函数获取的路径末尾没有/
        $dir_name = dirname($file_path).'/';
        $info = getimagesize($file_path);
        //生成3张头像缩略图(big为裁剪后的原图用户图片放大)
        $this->_avatar_types[]=array(
                    'type' => 'big',
                    'width' => $width);
		foreach($this->_avatar_types as $key=>$type)
        {
            $base_name = $this->explode_name(basename($file_path));
		    $file_name = $base_name['name'].'-'.$type['type'].$base_name['ext'];
            //头像缩略图的大小必须生成相应尺寸，即使用户上传的图片很小，也需要拉伸成正常大小
            $new_path = $dir_name.$file_name;
            $new_height = $info[1]/$info[0]*$type['width'];
            $config = array(
                    'image_library' => 'gd2',
                    'source_image' => $file_path,
                    'create_thumb' => false,
                    'maintain_ratio' => true,
                    'quality' =>80,
                    'master_dim' => true,
                    'new_image' => $new_path,
                    'width' => $type['width'],
                    'height' => $new_height
            );

            $this->image_lib->initialize($config);
            $result [] = $this->image_lib->resize();
            $this->image_lib->clear();
        }
        //移动原图到上传目录
        $new_path = $dir_name.$base_name['name'].$base_name['ext'];
        @move_uploaded_file($file_path, $new_path);
        @unlink($file_path);
        return $result[0] && $result[1] && $result[2];
    }
    
    /**
     * 解析文件名
     * @param string $source_image
     * @return array 文件名+后缀
     */
    public function explode_name($source_image)
    {
        $ext = strrchr($source_image, '.');
        $name = ($ext === FALSE) ? $source_image : substr($source_image, 0, -strlen($ext));
    
        return array('ext' => $ext, 'name' => $name);
    }
    
    /**
     * 生成缩略图, 如果需要裁剪图片，先裁剪再生成缩略图
     * @param string $file_path
     * @param boolean $is_crop
     * @param float $crop_options 裁剪参数
     */
    public function resize($file_path, $is_crop = false, $crop_options = array())
    {
        if(! file_exists($file_path))
            return false;
        
        $this->load->library('image_lib');
        $result = array();
        
        //dirname函数获取的路径末尾没有/
        $dir_name = dirname($file_path).'/';
        $base_name = $this->explode_name(basename($file_path));
        
        if($is_crop)
        {
            //裁剪比例
            $crop_options += array('ratio' => 0.75);
            $ratio = $crop_options['ratio'];
            $info = getimagesize($file_path);
            //宽高算法
            if ($info[1]/$info[0] >= $ratio) {
                $width = $info[0];
                $height = $info[0] * $ratio;
            } else {
                $height = $info[1];
                $width = $info[1] / $ratio;
            }
            $options = array(
                    'file_path' => $file_path,
                    'width' => $width,
                    'height' => $height,
                    'x' => 0,
                    'y' => 0
            );
            
            if(! $this->crop($options))
                return false;
        }
        
        $info = getimagesize($file_path);
        
        foreach($this->_types as $key=>$type)
        {
            $file_name = $base_name['name'].'-'.$type['type'].$base_name['ext'];
            $new_path = $dir_name.$file_name;
            
            //如果原始图片的宽比缩略图的小，使用原始图片的值
            $new_width = $info[0] > $type['width'] ? $type['width'] : $info[0];
            $new_height = $info[1]/$info[0]*$new_width;
                
            $config = array(
                    'image_library' => 'gd2',
                    'source_image' => $file_path,
                    'create_thumb' => false,
                    'maintain_ratio' => true,
                    'master_dim' => true,
                    'quality' =>80,
                    'new_image' => $new_path,
                    'width' => $new_width,
                    'height' => $new_height
            );
                
            $this->image_lib->initialize($config);
            $ret = $this->image_lib->resize();
            $this->image_lib->clear();
            $result [] = $ret;
        }
        
        //移动原图到上传目录
        $new_path = $dir_name.$base_name['name'].$base_name['ext'];
        @move_uploaded_file($file_path, $new_path);
        return $result[0] && $result[1] && $result[2];
    }
  
    /**
     * 裁剪图片
     * @param string $file_path
     * @param int $width
     * @param int $height
     * @param array array('file_path' => 'jjj.jpg', 'width' => 100, 'height' => 100, 'x' => 10, 'y' => 10)
     * array('file_path' => 'jjj.jpg', 'width' => array(100,200), 'height' => array(100,200), 'x' => 10, 'y' => 10)
     */
    public function crop($options = array(),$new_set=array())
    {
        $this->load->library('image_lib');
        if(empty($options))
            return false;
        $file_path = $options['file_path'];

        if(is_array($options['width'])){
            $info = getimagesize($file_path);
            //dirname函数获取的路径末尾没有/
            $dir_name = dirname($file_path).'/';
            $base_name = $this->explode_name(basename($file_path));

            $x=0;
            $y=0;
            $width_a = 0;
            $height_a = 0;
            $type = 'small';
            foreach($options['width'] as $k=>$w){
                if($new_set){
                    $type = $new_set[0].'x'.$new_set[1];
                }else{
                    if($w == 180){
                        $type = 'big';
                    }else{
                        $type = 'small';
                    }
                }
                $file_name = $base_name['name'].'-'.$type.$base_name['ext'];
                $new_path = $dir_name.$file_name;

                $width_a = $w;
                $height_a = $options['height'][$k];
                $x = ($info[0]/2) - ($width_a/2);
                $y = ($info[1]/2) - ($height_a/2);
                $config = array(
                    'image_library' => 'gd2',
                    'source_image' => $file_path,
                    'maintain_ratio' => false,
                    'width' => $width_a,
                    'height' => $height_a,
                    'x_axis' => $x==0 && isset($options['x']) ? $options['x'] : $x,//isset($options['x']) && $options['x'] > 0  ? $options['x'] : 0,
                    'y_axis' => $y==0 && isset($options['y']) ? $options['y'] : $y,//isset($options['y']) && $options['y'] > 0  ? $options['y'] : 0,
                    'new_image' => $new_path
                );
                $this->image_lib->initialize($config);
                if ( ! $this->image_lib->crop()) {
                    log_message('debug',  $this->image_lib->display_errors());
                    return false;
                }
                $this->image_lib->clear();
                if(($info[0] < $width_a ||  $info[1] < $height_a) || ($info[0] >  $new_set[0] || $info[1] > $new_set[1])){
                    if($info[0] >  $new_set[0] || $info[1] > $new_set[1]){
                        $width_a = $new_set[0];
                        $height_a = $new_set[1];
                    }
                    /*缩放图片 start*/
                    $config = array(
                        'image_library' => 'gd2',//(必须)设置图像库
                        'source_image' => $file_path,//(必须)设置原始图像的名字/路径
                        'create_thumb' => false,//让图像处理函数产生一个预览图像(将_thumb插入文件扩展名之前)
                        'maintain_ratio' => false,//维持比例
                        'master_dim' => true,//auto, width, height 指定主轴线
                        'new_image' => $new_path,//设置图像的目标名/路径。
                        'width' => $width_a,
                        'height' => $height_a
                    );

                    $this->image_lib->initialize($config);
                    $ret = $this->image_lib->resize();
                    $this->image_lib->clear();
                    /*缩放图片 end*/
                }
            }
        }else{
            $config = array(
                'image_library' => 'gd2',
                'source_image' => $file_path,
                'maintain_ratio' => false,
                'width' => $options['width'],
                'height' => $options['height'],
                'x_axis' => isset($options['x']) && $options['x'] > 0  ? $options['x'] : 0,
                'y_axis' => isset($options['y']) && $options['y'] > 0  ? $options['y'] : 0,
                'new_image' => $file_path
            );
        }
        $this->image_lib->initialize($config);
        if ( ! $this->image_lib->crop()) {
            log_message('debug',  $this->image_lib->display_errors());
            return false;
        }
        $this->image_lib->clear();

        return true;
    }
    
    
    
}