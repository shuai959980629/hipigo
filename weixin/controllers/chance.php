<?php
/**
 * 
 * @copyright(c) 2014-04-03
 * @author vikie
 * @version Id:chance.php
 */

class Chance extends WX_Controller
{
	
  public function __construct(){
		parent::__construct();
		$this->load->model('chance_model','chance');
	}
 
	
	//判断是否中奖并产生电子卷,砸金蛋和一战到底活动
	public function set_chance()
	{
		$bn_id = $this->bid;
        $id_shop =  $this->sid;
		$jion_id = $this->input->get('jion_id');
		$id_activity = $this->input->get('aid');   //获取活动类型
		$id_open = $this->input->get('oid');       //用户微信号
		$answer = $this->input->get('answer');     //答题信息
		$total_num =$this->input->get('total_num');//答题条数
		$new_time =$this->input->get('complete_time');//答题时间
		$id_customer = 0;  //用户注册编码，暂时设为0
		
		if(empty($id_activity) || empty($id_open))
		{
		    $item = array('status'=>'x','error'=>'活动ID或微信ID为空');
			echo json_encode($item);  
			exit;
		}
		
		//从活动配置表中获取活动预计参与人数，和每天参与次数
		$activity = $this->chance->find_activity_config($id_activity);
		$type = $activity[0]['type'];
		if(empty($activity))
		{
			$cue = empty($activity[0]['failure_reply']) ? '什么也没得到！' : $activity[0]['failure_reply'];
			$data=array('cue'=>$cue);
			$item = array('status'=>0,'data'=>$data);
			echo json_encode($item);
			exit; 
			
		}
		//获取用户当天参与活动次数
		$l_date = date('Y-m-d 00:00:00');
		$m_date = date('Y-m-d 00:00:00',strtotime('1 days'));
	    $num_user_time = $this->chance->find_activity_time($id_activity,$id_open,$l_date,$m_date);
		$time = $activity[0]['join_number_day'];      //每人每天参与次数
		$max_num = $activity[0]['join_number_total']; //每人总共参与次数 
		$time = !empty($time) ? $time:$max_num;
		//判断用户当天的参与次数是否达到上限(砸金蛋和答题不一样)
		
		if($num_user_time >= $time && $type=='egg')
		{
			$item = array('status'=>3);
			echo json_encode($item);
			exit; //已达到当天参与上限值
		}
		
		if($num_user_time > $time && $type=='answer')
		{
			$item = array('status'=>3);
			echo json_encode($item);
			exit; //已达到当天参与上限值
		}
		
		$join_number_total=$activity[0]['join_number_total']; //没人总共参与次数
		$estimate_people_number=$activity[0]['estimate_people_number']; //预计参与人数
		$join_number_day= empty($activity[0]['join_number_day']) ? 1 : $activity[0]['join_number_day']; //预计活动天数
		$estimate_activity_day = $activity[0]['estimate_people_number']; //每天参与次数
		if(!empty($join_number_total))
		{   //如果商家勾选每人最多参与次数
			$total = $join_number_total;
		}else if(!empty($estimate_people_number) && !empty($estimate_activity_day))
		{   //计算活动参与的最大次数
			$total = $estimate_people_number*$estimate_activity_day*$join_number_day;
		}else if(!empty($estimate_people_number))
		{   //预计参与人数不为空的情况
			$total = $estimate_people_number*$join_number_day;
			
		}else if(!empty($estimate_activity_day))
		{   //没人每天参与次数不为空
			$total = $estimate_activity_day*$join_number_day;
		}
		
		$complete_time = $activity[0]['complete_time'];
		//获取活动绑定的电子卷和参与条件
		$requirement=json_decode($activity[0]['requirement']);
		$user = $requirement->user;
		$eticket = $requirement->eticket;
		
		//判断活动类型
		switch ($type) {
			case 'egg':    $eticket_info=$this->agg_is_win($id_activity,$id_open,$total,$eticket);
				break;
			case 'answer': $eticket_info=$this->answer_is_win($id_activity,$id_open,$new_time,$total_num,$answer,$eticket,$jion_id);
				break;
			default:       
				break;
		}
		
		//插入活动记录表 (砸金蛋-活动)
		if($type=='egg')
		{
		$status = empty($eticket_info['code']) ? 0 : 1;
		$data1 = array(
			'id_activity' =>$id_activity,                                 //活动编号
			'id_open' =>$id_open,                                         //用户微信号
			'id_customer' =>$id_customer,                                 //用户注册编码
			'created' =>date('Y-m-d H:i:s'),                              //参与活动时间
			'is_winner' =>$status                                         //是否砸蛋成功
			 );
		$rs = $this->chance->set_activity($data1);	
		}
		
		//判断是否还有砸蛋机会
		$time_nums = $num_user_time+1>=$time ? 0:1; 
		if(empty($eticket_info['code']))
		{
			   
			//一战到底
			if($type=='answer')
			{
			$data=array('cue'=>$activity[0]['failure_reply'],
			'time_out'=>$eticket_info['time'],
			'accuracy'=>$eticket_info['accuracy'],
			'chance'=>$eticket_info['chance']
			);
			}else{
			//砸金蛋和事件活动	
			$data=array('cue'=>$activity[0]['failure_reply'],
			'time_nums'=>$time_nums);
			}
			//没中奖
			$item = array('status'=>0,'data'=>$data);
			echo json_encode($item);
			exit;
		}
		
		//创建电子卷
		$data = array(
			'id_business' =>$bn_id,                                 //商家编号
			'id_activity' =>$id_activity,                           //活动ID
			'id_shop' =>$id_shop,                                   //门店编号
			'object_type' =>'eticket',                              //电子卷类型
			'id_object' =>$eticket_info['id_eticket'],              //电子卷的类型
			'id_open' =>$id_open,                                   //用户微信号
			'id_customer' =>$id_customer,                           //用户注册编码
			'code' => $eticket_info['code'],                        //电子卷验证码
			'get_time' =>date('Y-m-d H:i:s'),                       //用户获得时间
			'use_time' =>0,                                         //用户使用时间
			'state' =>1,                                            //电子卷状态，1已获得
			);
		$id_object = $eticket_info['id_eticket'];
		if($this->chance->set_eticket($id_object,$data))
		{
			 //一战到底
			if($type=='answer')
			{
			$data=array('cue'=>$activity[0]['success_reply'],
			'time_out'=>$eticket_info['time'],
			'accuracy'=>$eticket_info['accuracy'],
			'chance'=>$eticket_info['chance']
			);
			}else{
			//砸金蛋和事件活动	
			$data=array('cue'=>$activity[0]['success_reply'],
			'time_nums'=>$time_nums); 
			  
			}
		    $item = array('status'=>1,'data'=>$data);
			echo json_encode($item);
			
		}		
		
	}

   //砸金蛋获奖判断
   private function agg_is_win($id_activity,$id_open,$total,$eticket)
   {
   	  
	   //建立一个用于存放所有电子卷信息的数组
		$eticket_info=array();
		//判断商家是否绑定电子卷
		if(empty($eticket))
		{
			return $eticket_info;
			exit;
		}
		foreach ($eticket as $key => $v) 
		{
		$rand = rand(1, 1000);	
		//获得该活动中配置的每个电子卷的名称和剩余量	
		$row = $this->chance->find($v->eticketId);
		$gift_num = $row[0]['quantity'] - $row[0]['get_quantity'];
		//获得当前用户在该活动中获得此电子卷的数量
		$num = $this->chance->find_num_win($v->eticketId,$id_open);
		$num = empty($num) ? 0 : $num;
		$total = $total-$num;
		
		//获取当前电子卷中奖概率
		$chance = round(intval($gift_num) / intval($total), 3);
		$code_str = 1;
		//判断是否中奖
		if($num<$v->getMaxNumber && $rand<$chance*1000 && $gift_num>0)
		  {
		  	
		  	if($row[0]['length']<=10){
			$str_s = str_pad(1,$row[0]['length'],"0");
			$str_e = str_pad(9,$row[0]['length'],"9");
			$code = rand($str_s,$str_e);	
			}else{
			$str_s = str_pad(1,10,"0");
			$str_e = str_pad(9,10,"9");
			$code1 = rand($str_s,$str_e);
			$num = $row[0]['length']-10;		
			$str_s1 = str_pad(1,$num,"0");
			$str_e1 = str_pad(9,$num,"9");
			$code2  = rand($str_s1,$str_e1);
			$code = $code1.$code2;	
			} 
			$eticket_info['code']=$code;
			$eticket_info['id_eticket'] = $row[0]['id_eticket'];
			$eticket_info['name'] = $row[0]['name'];
			break;
			
		  }
		
		}
	  
	  return $eticket_info;
   }
   
    //一战到底获奖判断
   private function answer_is_win($id_activity,$id_open,$new_time,$total_num,$answer,$eticket,$jion_id)
   {
   	 //验证答题正确率
     $n_arr=array();
	 $answer = json_decode($answer);
	 $i=0;
	 foreach ($answer as $key => $v)
	 {
	  $n_arr[$i]['id_subject']=$key;
	  $n_arr[$i]['id_options']=$v;
	  $wheres[$i]=$key;	
	  $i++;
	 }
	 
	 //查询正确答案
	 $row = $this->chance->get_answer($wheres);
	 
	 //比较答案
	 foreach ($row as $key => $value) {
		 foreach ($n_arr as $k => $v) {
			 if($value['id_subject'] == $v['id_subject'])
			 {
			   $win[]=$value['id_options']==$v['id_options'] ? 1:0;
			 }
		 }
		 
	 }
	 //用户当前中奖率
	 $win_in = array_count_values($win);
	 $wins = !empty($win_in[1]) ? $win_in[1]:0;
	 $chance = round(intval($wins) / intval($total_num), 2);
	 //插入答题活动记录表
	 $answer_info=array('id_activity'=>$id_activity,
	                    'id_open'=>$id_open,
	                    'id_customer'=>0,
	                    'score'=>$wins,
	                    'consuming'=>$new_time,
	                    'created'=>date('Y-m-d H:i:s')
	 );
	 $this->chance->set_result_answer($answer_info);
	 //建立一个用于存放所有电子卷信息的数组
	 $eticket_info=array();
	 //商家没有配置电子卷
	 if(empty($eticket))
		{
			$eticket_info['time'] = $new_time;
			$eticket_info['accuracy'] = $wins;
			$eticket_info['chance'] = ($chance*100).'%';
			return $eticket_info;
			exit; 
		}
	 foreach ($eticket as $key => $v) 
	{
	 	
	 //获得该活动中配置的每个电子卷的名称和剩余量	
	 $row = $this->chance->find($v->eticketId);
	 $last_num = $row[0]['quantity']-$row[0]['get_quantity'];	
	 //获得当前用户在该活动中获得此电子卷的数量
	 $num = $this->chance->find_num_win($v->eticketId,$id_open);
	 $num = empty($num) ? 0 : $num;
	 if($num<$v->getMaxNumber && $chance*100>=$v->accuracy && $new_time<=$v->consuming && $last_num>0)
		{
			if($row[0]['length']<=10){
			$str_s = str_pad(1,$row[0]['length'],"0");
			$str_e = str_pad(9,$row[0]['length'],"9");
			$code = rand($str_s,$str_e);	
			}else{
			$str_s = str_pad(1,10,"0");
			$str_e = str_pad(9,10,"9");
			$code1 = rand($str_s,$str_e);
			$num = $row[0]['length']-10;		
			$str_s1 = str_pad(1,$num,"0");
			$str_e1 = str_pad(9,$num,"9");
			$code2  = rand($str_s1,$str_e1);
			$code = $code1.$code2;	
			} 
			
			$eticket_info['code']=$code;
			$eticket_info['id_eticket'] = $row[0]['id_eticket'];
			$eticket_info['name'] = $row[0]['name'];
			$eticket_info['time'] = $new_time;
			$eticket_info['accuracy'] = $wins;
			$eticket_info['chance'] = ($chance*100).'%';
			//更新活动参与表状态为中奖
	        $this->chance->update_jion($jion_id);
			break;
		}else{
			
			$eticket_info['time'] = $new_time;  //答题所花费的时间
			$eticket_info['accuracy'] = $wins;  //正确题数
			$eticket_info['chance'] = ($chance*100).'%';
		}
		
	}
	 return $eticket_info;
   }




}