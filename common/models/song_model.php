<?php
/**
 * 
 * @copyright(c) 2013-11-27
 * @author zxx
 * @version Id:Song_Model.php
 */

class Song_Model extends CI_Model
{
    protected $table = 'bn_song';
    protected $table_class = 'bn_commodity_class';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /*
     * zxx 获取歌曲（列表）信息
     * **/
    public function get_song_introduction($where,$offset = 0, $page = 20, $order='',$type=1)
    {
        if($type){
        $this->db->select('id_song,id_shop,id_business,name,singer,song_url,lyric,posters_url,weight,state,created,id_class')
            ->from($this->table)
            ->where($where);
        }else{
            $this->db->select('s.id_song,s.id_shop,s.id_business,s.name,s.singer,s.song_url,s.lyric,s.posters_url,s.weight,s.state,s.created,s.id_class,cc.name as type')
                ->from($this->table.' as s')
                ->where($where)
                ->join($this->table_class." as cc","s.id_class=cc.id_class","left");
        }

        if($offset){
            $this->db->limit($page,($offset-1)*$page);
        }
        if( $order ){
            $this->db->order_by($order);
        }
        $result = $this->db->get()->result_array();
        return $result;
    }
    
    
    public function get_song_list($where,$order,$offset,$page){
    	$this->db->select('id_song,name,singer,song_url,lyric,posters_url,weight,state,created,id_class');
    	$this->db->from($this->table);
    	if(!empty($where)){
    		$this->db->where($where);
    	}
    	if(!empty($order)){
    		$this->db->order_by($order);
    	}
    	$this->db->limit($page,($offset-1)*$page);
    	$result = $this->db->get()->result_array();
//    	debug($this->db->last_query());
    	return $result;
    }
    

    /*
     * zxx 获取歌曲信息总条数
     * **/
    public function get_song_count($where){
        $this->db->where($where);
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }

    /*
     * zxx
     * 添加一条歌曲信息  返回插入数据的id
     */
    public function insert_song($data){
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /*
     * zxx
     * 更新一条歌曲信息
     */
    public function update_song($data,$where){
        $re = $this->db->update($this->table, $data,$where);
        return $re;
    }

    /*
      * zxx
      * 删除一条歌曲信息
      */
    public function delete_song($id_song){
        $re = $this->db->delete($this->table, array('id_song' => $id_song));
        return $re;
    }

}



/* End of file user_model.php */