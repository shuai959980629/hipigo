$(function () {
	

	$.getJSON(base_url+"/community/kill_list?page=1", function(result){
		var list = list_kill(result);
		$("#kill_list_ul").append(list);

		J.Scroll('#seckill_list_container');
	});

	$("#share_hit").click(function(){
		$(".share_hit").show();
		$("#jingle_popup_mask").addClass('share_mask').show();
	});

  //消失
  $('#jingle_popup_mask.share_mask').click(function () {
    $(this).hide().removeClass();
    $('.share_hit').hide();
  });

    //消失
    $('.share_hit').click(function () {
        $('#jingle_popup_mask').removeClass().hide();
        $('.share_hit').hide();
    });

   
    $('.loadmore').click(function () {
		num = 1;
		$.getJSON(base_url+"/community/kill_list?page="+num+"", function(result){

			var list = list_kill(result);
			$("#kill_list_ul").append(list);

			num++;

		});

			J.Scroll('#seckill_list_container');
	
    });


	setInterval(function(){         
		$(".countdown").each(function(){    

				var start_time = new Date($(this).attr('value')).getTime();
				var sys_second = (start_time-new Date().getTime())/1000; 

				if (sys_second > 1) {

					sys_second -= 1;
					var day = Math.floor((sys_second / 3600) / 24);
					var hour = Math.floor((sys_second / 3600) % 24)+(day*24);
					var minute = Math.floor((sys_second / 60) % 60);
					var second = Math.floor(sys_second % 60);

					var str = (hour<10?"0"+hour:hour)+':'+(minute<10?"0"+minute:minute)+':'+(second<10?"0"+second:second);  	
					$(this).html(str);    

				}
								
			});    
	}, 100);
	
	J.Scroll('#seckill_list_container');
});


//秒杀列表
function list_kill(result){  
				
	var discuss = '';
	var num = 0;

	$.each(result, function(i, value) {

		discuss+='<li class="clearfix">';
			discuss+='<a href="'+url+'/community/detail?aid='+value.id_activity+'">';
				discuss+='<p class="seckill_list_left left"><img src="'+value.header_img+'"/></p>';
					discuss+=' <p class="seckill_list_right left">';
						discuss+='<span class="seckill_list_title">'+value.name+'</span>';
						
						if(value.total < 0){
							discuss+='<span class="seckill_list_limit">限量:<em>充足</em></span>';
						}else{
							discuss+='<span class="seckill_list_limit">限量:<em>'+value.total+'</em>件</span>';
						}
						
						discuss+='<span class="seckill_list_price">';
						discuss+='<b class="left"><em class="red">￥'+value.preferential_price+'</em><i>￥'+value.join_price+'</i></b>';
						
						if(value.kill_time==0){
							discuss+='<button class="finish grayish">已结束</button>';
						}else if(value.kill_time==1){
							discuss+='<button class="immediately_buy right">马上抢</button>';
						}else{
							discuss+=' <button value="'+value.kill_time+'" class="countdown right">'+value.kill_time+'</button>';
						}

					discuss+='</span>';
				discuss+='</p>';
			discuss+='</a>';
		discuss+='</li>';
		num = i;
	});
	
	if(num>24){
		discuss+= '<li class="loadmore haveback" style="background: none;max-height: 25px;min-height: 25px;cursor: pointer; border-bottom: none;padding: 0;">查看更多<i class="iconfont icon iconfont_20">&#58933;</i></li>';
	}

	return discuss;

}
