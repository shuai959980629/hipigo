<?php
/**
 * 
 * @copyright(c) 2013-11-27
 * @author msi
 * @version Id:song.php
 */

class Song extends Admin_Controller{
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
     * zxx
     * 添加歌曲
     */
    public function add_song(){
        $data ['url_action'] = 'song/add_song';
        if($this->input->post('name'))
        {
            $this->load->library('form_validation');
            if ( $this->form_validation->run('add_song') === TRUE)
            {
                $this->load->model('song_model','song');
                $where = 'id_business = '.$this->id_business.' and id_shop = '.$this->id_shop;
                $count = $this->song->get_song_count($where);
                if($count >= 40){
                    $this->returen_status(0,'最多可以添加40首歌曲!',$data);
                }
                $name = $this->input->post('name');//歌曲名
                $singer = $this->input->post('singer');//原唱名
                $lyric = $this->input->post('lyric');//歌词
                $weight = $this->input->post('weight');//权重
                $state = $this->input->post('state');//状态
                $commodity_class = $this->input->post('commodity_class');//分类
                $image_src = $this->input->post('image_src');//图片上传后的文件名
                $audio_src = $this->input->post('audio_src');//音频上传后的文件名

                $name = $this->replace_html($name);
                $singer = $this->replace_html($singer);
                $song_info = array(
                    'id_business' => $this->id_business,
                    'id_shop' => $this->id_shop,
                    'name' => $name,
                    'singer' => $singer,
                    'lyric' => $lyric,
                    'weight' => $weight,
                    'state' => $state,
                    'created'=>date('Y-m-d H:i:s', time()),
                    'id_class'=>$commodity_class
                );
                if($image_src){
                    $song_info['posters_url'] = $image_src;
                }
                if($audio_src){
                    $song_info['song_url'] = $audio_src;
                }
                $this->song->insert_song($song_info);
                $data['list_url'] = BIZ_PATH.'song/list_song'.$this->tpl[0];
                $this->returen_status(1,$this->lang->line('add_data_success'),$data);
            }
            else
            {
                $this->returen_status(0,$this->lang->line('add_data_failed'),$data);
            }
        }else{
            $this->load->model('commodity_model','commodity');
            $where = 'type = "song" and id_business = ' . $this->id_business;
            //获取歌曲分类
            $commodity_class = $this->commodity->get_commodity_class($where);
            $data ['song_info'] = array();
            if($commodity_class){
                $data['commodity_class'] = $commodity_class;
            }
            $this->smarty->assign($data);
            $this->smarty->assign('type','add');
            $this->smarty->view('add_song');
        }
    }

    /**
     * zxx
     * 编辑歌曲
     */
    public function edit_song($id_song,$offset){
        $data ['url_action'] = 'song/edit_song';
        if($this->input->post('name'))
        {
            $this->load->library('form_validation');
            if ( $this->form_validation->run('add_song') === TRUE)
            {
                $name = $this->input->post('name');//歌曲名
                $singer = $this->input->post('singer');//原唱名
                $lyric = $this->input->post('lyric');//歌词
                $weight = $this->input->post('weight');//权重
                $state = $this->input->post('state');//状态
                $id_song = $this->input->post('id_song');//歌曲id
                $commodity_class = $this->input->post('commodity_class');//分类

                $image_src = $this->input->post('image_src');//图片上传后的文件名
                $audio_src = $this->input->post('audio_src');//音频上传后的文件名

                $name = $this->replace_html($name);
                $singer = $this->replace_html($singer);
                $song_info = array(
                    'id_business' => $this->id_business,
                    'id_shop' => $this->id_shop,
                    'name' => $name,
                    'singer' => $singer,
                    'lyric' => $lyric,
                    'weight' => $weight,
                    'state' => $state,
                    'created'=>date('Y-m-d H:i:s', time()),
                    'id_class'=>$commodity_class
                );
                if(trim($image_src)){
                    $song_info['posters_url'] = $image_src;
                }

                if(trim($audio_src)){
                    $song_info['song_url'] = $audio_src;
                }

                $this->load->model('song_model','song');
                $where = 'id_song = '.$id_song;
                $this->song->update_song($song_info,$where);

                $offset = $offset?$offset:1;
                $data['list_url'] = BIZ_PATH.'song/list_song/'.$offset.$this->tpl[0];
                $this->returen_status(1,$this->lang->line('edit_data_success'),$data);
            }
            else
            {
                $this->returen_status(0,$this->lang->line('edit_data_failed'),$data);
            }
        }else{
            $this->load->model('song_model','song');
            $where = 'id_song = '.$id_song;
            //获取歌曲列表
            $song_info = $this->song->get_song_introduction($where);
            if($song_info){
                $data['song_info'] = $song_info[0];
            }

            $this->load->model('commodity_model','commodity');
            $where = 'type = "song" and id_business = ' . $this->id_business;
            //获取歌曲分类
            $commodity_class = $this->commodity->get_commodity_class($where);
            if($commodity_class){
                $data['commodity_class'] = $commodity_class;
            }

            $this->smarty->assign($data);
            $this->smarty->assign('type','edit');
            $this->smarty->view('add_song');
        }
    }

	/**
     * zxx
     * 歌曲列表
	 */
	public function list_song($offset=0){
        $search_key = $this->input->post('search_key')?$this->input->post('search_key'):'';
        if(!$offset){
            $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        }
        $id_class = $this->input->post('id_class') ? $this->input->post('id_class') : 'all';//歌曲分类编号

        //获取物品列表
        $data = $this->get_song($id_class,$offset,$search_key);

        $this->smarty->assign($data);
        $this->smarty->assign('offset',$offset);
        $this->smarty->assign('page_type','song');

        if($this->input->post('ispage') == 1){
            echo $this->smarty->view('lists');
        }else{
            $this->smarty->view('list_songs');
        }
    }

	/**
     * zxx
     * 删除歌曲
	 */
	public function del_song(){
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        $id_song = $this->input->post('id_song');//歌曲id
        $search_key = $this->input->post('search_key') ? $this->input->post('search_key') : '';

        $id_class = $this->input->post('id_class');

        $this->load->model('song_model','song');
        //获取删除的歌曲名
        $where = 'id_song = ' . $id_song;
        $songInfo = $this->song->get_song_introduction($where);

        foreach($songInfo as $si){
            //获取本地文件歌曲地址
            $path = $this->file_url_name('song',$si['song_url']);
            if(!file_exists($path) && !is_readable($path)){
            }else{
                unlink($path);//删除歌曲附件
            }
            //获取本地文件海报地址
            $path = $this->file_url_name('song',$si['posters_url']);
            if(!file_exists($path) && !is_readable($path)){
            }else{
                unlink($path);//删除海报附件
            }
        }
        //删除歌曲信息
        $this->song->delete_song($id_song);

        //获取歌曲列表
        $data = $this->get_song($id_class,$offset,$search_key);
        $this->smarty->assign($data);
        $this->smarty->assign('offset',$offset);
        $this->smarty->assign('page_type','song');

        $data = $this->smarty->fetch('lists.html');
        $resp = array(
            'status' => 1,
            'msg' => '',
            'data' => $data
        );
        die(json_encode($resp));
    }

    //搜索歌曲
    function search_song(){
        $search_key = $this->input->post('search_key')?$this->input->post('search_key'):'';
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 1;//页码
        $id_class = $this->input->post('id_class');
        //获取物品列表
        $data = $this->get_song($id_class,$offset,$search_key);

        $this->smarty->assign($data);
        $this->smarty->assign('offset',$offset);
        $this->smarty->assign('page_type','song');
        echo $this->smarty->view('lists');
    }
}
/* End of file song.php */