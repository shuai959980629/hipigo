<?php
/**
 * 
 * @copyright(c) 2014-6-26
 * @author Jamai
 * @version 2.0
 */

class Seometa_Model extends CI_Model
{
    protected $table = 'bn_seometa';

    public function __construct()
    {
      $this->load->database();
    }
    
    public function getSEOMedia($page)
    {
      $this->db->select('id_seometa, title, keywords, description')
            ->from($this->table)
            ->where(array('uri' => $page));
      
      $result = $this->db->get()->row();
      
      return $result;
    }
    
}
