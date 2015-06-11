<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Resource_Logic
{
  
  public function formatData ($result)
  {
    foreach ($result as $key => $value) {
    
      if($value['num'] <= -1) {
        $result[$key]['num'] = '充足';
      }
      
      if($value['price'] == '0.00' || $value['price'] == 0) {
        $result[$key]['price'] = '免费';
      }
      else {
        $result[$key]['price'] = (PAY_BUY + $value['price']) . '元';
      }
      
      if(! $value['deadline'] || $value['deadline'] == 0) {
        $result[$key]['deadline'] = '有效期: 长期有效';
      }
      else {
        $result[$key]['deadline'] = '有效期:' . date('Y-m-d', $value['deadline']);
      }
    }
    
    return $result;
  }
  
}


/* End of file resource_logic.php */
/* Location: ./resource_logic.php */