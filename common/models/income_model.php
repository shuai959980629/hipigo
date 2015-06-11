<?php
/**
 * @author Jamai
 * @收入明细【收入金额，验证状态，结算状态，结算时间】
 */
class Income_Model extends CI_Model
{
    public $table = 'bn_income_item';
    
    /**
     * 已验证、可结算 或 未验证、不可验证 的明细信息
     * 
     */
    public function getFinance($id_business, $un = true)
    {
      $this->db->select('FORMAT(SUM(amount), 2) as total', false)
        ->from($this->table)
        ->where(array('income_object_type' => 'business',
        'id_income_object' => $id_business));

      if($un !== true)
        $this->db->where(array('state' => 2));
      else
        $this->db->where(array('state' => 1));
        
      return $this->db->get()->row();
    }
    /**
     * 修改验证状态 是否通过 通过为：2 ，未通过：1
     */
     
     
    

}

























?>