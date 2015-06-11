$(function () {
	
		setInterval(function(){          
    
				var start_time = new Date($('#kill_time').attr('data-time')).getTime();
				var sys_second = (start_time-new Date().getTime())/1000; 

				if (sys_second > 1) {

					sys_second -= 1;
					var day = Math.floor((sys_second / 3600) / 24);
					var hour = Math.floor((sys_second / 3600) % 24)+(day*24);
					var minute = Math.floor((sys_second / 60) % 60);
					var second = Math.floor(sys_second % 60);

					var str = (hour<10?"0"+hour:hour)+':'+(minute<10?"0"+minute:minute)+':'+(second<10?"0"+second:second);  	
					$('#kill_time').html(str);    

				}  
								
	}, 100);

	$("#reply_no_thing").click(function(){
		$("#comment_area_details").val('');
		$(".comment_popup").show();

		$("#comment_area_details").bind("input propertychange", function() { 
			$(this).next('button').addClass('send_active01');
		}); 
	});

	$('#reply_no_thing_activity').click(function () {
		
		var result = popupBuy(aid,'activity');
		//alert(result);
		
	});
	

	$('.send').click(function () {

		var comment_area = $('.comment_area').val();
		imgs = "";

		$(".relative").each(function(){
				img = $(this).find("img").attr("src");
				if(img!=undefined){
					imgs = imgs+img+",";
				}
		});

		if(comment_area==''&&imgs==''){
			J.showToast('请填写内容');
		}else{

			value = $('.comment_area').val();
			sp_rp = $('.comment_area').attr('data-id-parent');
			user_name =$('#user_name_review').val();

			$.get(base_url+"/community/spking?user_name="+user_name+"&userid="+userid+"&aid="+aid+"&content="+value+"&sp_rp="+sp_rp+"&imgs="+imgs, function(result){
				if(result!=""){

					if(sp_rp==0){
						J.showToast('评论成功');
					}else{
						J.showToast('回复成功');
					}

				   if($('.discuss').is(":hidden")){
						$("#c_review").attr('class', '');
						$("#p_review").attr('class', 'active01');
						$('.discuss').show();		
						$('.leaguer').hide();
				   }

				  $.get(base_url + '/community/list_spking', {aid: aid},
					function (result) {
					     var lists = list_skping(result);
						 $(".discuss").html(lists);
						 $('.comment_popup').hide();
						 $("#relative_pic").remove();
						 $(".pop_expression").hide();
						 init();
						 img_num_sele();
						 J.Scroll('#active_details_container');
					}, 'json');

				  $.get(base_url + '/community/spking_count', {aid: aid},
					function (result) {
						$('#spking_count').html(result);
					}, 'text');
						
				}
			});			

		}

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


    $(".open").click(function(){//打开
		// $("#ope").hide();
		// $("#upc").show();
		// window.location.href = base_url+'/community/detail?upc=false&aid='+aid;
    if($(this).prev('div').is(":hidden")) {
      $(this).find('i').html('&#983722;'); 
      $(this).find('span').html('收起全部');
      $(this).prev('div').show();
    }
    else {
      $(this).find('i').html('&#983721;');
    $(this).find('span').html('展开全部');
      $(this).prev('div').hide();
    }
    J.Scroll('#active_details_container');
	});
  
/*
	$("#upc").click(function(){//收起
		// $("#ope").show();
		// $("#upc").hide();
		// window.location.href = base_url+'/community/detail?upc=true&aid='+aid;
    $('this').prev('div').show();
	});
*/
	if(is_good==0){
		$("#x_button").attr('class','right hidden');
	}else{
		$("#z_button").attr('class','right hidden');
		$("#x_button").attr('class','right');
	}

	$('#z_button').click(function() {
		$.getJSON(base_url+"/community/good?aid="+aid+"&count=true", function(result){

			if(result.appraise_count){
				$(this).attr('class','right hidden');
				$("#x_button").attr('class','right');
				$("#z_button").attr('class','right hidden');
				$("#app_count").html(result.appraise_count);
			}

		});
	});

		$('#x_button').click(function() {
		$.getJSON(base_url+"/community/good_del?aid="+aid+"&count=true", function(result){

			if(result.appraise_count){
				$(this).attr('class','right hidden');
				$("#z_button").attr('class','right');
				$("#x_button").attr('class','right hidden');
				$("#app_count").html(result.appraise_count);
			}

		});
	});

	$('.haveback').click(function () {

		var simg = $(this).attr('data-simg-key');
		var comment_area = $('.comment_area').val();
		$('.comment_area').val(comment_area+simg);
		$('.send').addClass('send_active01');

	});

	$("#leaguer").hide();

	$('#p_review').click(function() {

		$('#c_review').attr('class','');

		//var is_hidden = $('.discuss').attr("class");

//		if(is_hidden=='discuss hidden') {
//			$('.discuss').attr("class","discuss clearfix");
//			$('.leaguer').attr("class","leaguer hidden");
//		} else {
//			$('.discuss').attr("class","discuss hidden");
//			$('.leaguer').attr("class","leaguer");
//		}

		$('#leaguer').hide();
		$('#discuss').show();

		$(this).find("span").prepend('<i class="iconfont icon iconfont_10">&#983722;</i>');
		$('#c_review').find("i").remove();
		$(this).attr('class', 'active01');
		J.Scroll('#active_details_container');

	});

	$('#c_review').click(function() {

		$(this).attr('class', 'active01');

//		var is_hidden = $('.discuss').attr("class");
//
//		if(is_hidden=='discuss hidden') {
//			$('.discuss').attr("class","discuss clearfix");
//			$('.leaguer').attr("class","leaguer hidden");
//		} else {
//			$('.discuss').attr("class","discuss hidden");
//			$('.leaguer').attr("class","leaguer");
//		}

		$('#discuss').hide();
		$('#leaguer').show();
		
		$(this).find("span").prepend('<i class="iconfont icon iconfont_10">&#983722;</i>');
		$('#p_review').find("i").remove();
		$('#p_review').attr('class','');
		 J.Scroll('#active_details_container');
	});

	init();
	img_num_sele();

	$('#p_review_f').click(function () {
		
		var result = popupBuy(aid,'activity');

	//参加数量+1
		$('#add_ac').click(function(){

			surplus = surplus < 0 ? 10000 : surplus;

			var ac_num_id = $('#ac_num');
			var ac_num = ac_num_id.val();

			if(parseInt(ac_num) < parseInt(surplus)){

				var new_ac_num = parseInt(ac_num)+parseInt(1);

				ac_num_id.attr("value",new_ac_num);

				//参与价格+
				var add_price = $('#add_price').attr('data-add_price');
				var price_total = addValue(join_price,add_price,1);
				$('#add_price').attr('data-add_price',price_total);
				$('#add_price').html(price_total);

			}
		});

		//参加数量-1
		$('#sub_ac').click(function(){

				var ac_num_id = $('#ac_num');
				var ac_num = ac_num_id.val();

				if(ac_num >1){

					var new_ac_num = parseInt(ac_num) - parseInt(1);
					ac_num_id.attr("value",new_ac_num);

					//参与价格-
					var add_price = $('#add_price').attr('data-add_price');
					var price_total = addValue(add_price,join_price,0);

					$('#add_price').attr('data-add_price',price_total);
					$('#add_price').html(price_total);
				
				}
		});
		
	});


	$('#att_activity').click(function () {
			
			//J.showToast('活动人数已满！');

			if(surplus == 0){//当活动total（活动数量）为0时surplus为-1
				
				J.showToast('活动人数已满！');
				return false;
			}

			//$.getJSON(base_url+"/community/att_activity?oid="+userid+"&aid="+aid, function(result){
			//	if(result.status){
			//		alert(result.status);
			//	}
			//});			
		
	});


	J.Scroll('#active_details_container');
});


//浮点数加减
function addValue(value1,value2,type){  
    if(value1=="")value1="0";  
    if(value2=="")value2="0";  
    var temp1=0;  
    var temp2=0;  
    if(value1.indexOf(".")!=-1)  
     temp1=value1.length - value1.indexOf(".")-1;  
    if(value2.indexOf(".")!=-1)  
     temp2=value2.length - value2.indexOf(".")-1;   
     var temp=0;  
    if(type==1){
       if(temp1>temp2)  
       temp = (parseFloat(value1)+parseFloat(value2)).toFixed(temp1);  
       else 
       temp = (parseFloat(value1)+parseFloat(value2)).toFixed(temp2);   
       return temp;	
    }else{
      if(temp1>temp2)  
       temp = (parseFloat(value1)-parseFloat(value2)).toFixed(temp1);  
       else 
       temp = (parseFloat(value1)-parseFloat(value2)).toFixed(temp2);   
       return temp;
    	
    }
}

function img_num_sele(){
	
	var z_num= 9;
	var img_num = $(".delete");
	var yes_len = img_num.length;
	
	$(".detail_hint").html('已选'+yes_len+'张，还有'+(z_num-yes_len)+'张可选');
	
	if(yes_len > 0){
		$("#iconudd").find("em").attr("class","dot");
		$("#iconadd").find("em").attr("class","dot");
	}

	if(yes_len==9){
		$(".pica").parent().hide();
	}else{
		$(".pica").parent().show();
	}

}	


function finishupload(data){  
			
	var img_num = $(".delete");
	var yes_len = img_num.length;

	if (data.status > 0) {

		var img_types =data.url.split('.');
		var img_sm = img_types[0]+'-small.'+img_types[1];

		if(yes_len==0){
			$('#relative_pic').append('<li class="relative"><img src="'+img_sm+'"/><a class="delete"> <i class="icon cancel-circle"></i></a></li>');
		}else{
			$('#relative_pic').prepend('<li class="relative"><img src="'+img_sm+'"/><a class="delete"> <i class="icon cancel-circle"></i></a></li>');
		}

		$('.send').addClass('send_active01');
		$('#iconudd').attr("class","more_choice left hidden");
		$('#iconadd').attr("class","more_choice left relative");
		
	} 

	 init();
	 //计算当前上传的图片数量
	 img_num_sele();

}


//评论、回复列表
function list_skping(result){  

	var discuss = '';

	$.each(result, function(ii, values) {

		discuss+='<li>';
		discuss+='<p class="say-pic left"><img src="'+values.header_img+'"/></p>';
		discuss+='<p class="say-person left">';
		discuss+='<span class="say-person-name">';
		discuss+='<span class="name_time"><em class="left">'+values.name+'</em><i class="time">'+values.created+'</i></span>';      
		
		if (values.spking_users_review_count == 0){
			discuss+='<span class="say-person-con"   data-id-parent="'+values.id_review+'">'+values.content+'<br>'+values.review_imgs+'</span>';
		}else{
			discuss+='<span class="say-person-con"   data-id-parent="'+values.id_parent+'">'+values.content+'<br>'+values.review_imgs+'</span>';
		}

		if (values.spking_users_review_count > 0){

			discuss+='<span class="reply" id="reply_'+values.id_review+'">';

			$.each(values.review, function(i, value) {
						
						discuss+='<b><em class="replyname">'+value.name+'</em> <a class="replycontent"  data-id-parent="'+value.id_parent+'" style="color:#000000">'+value.content+'</a><i>'+value.created+'</i></b>';

						if(i-(values.spking_users_review_count-1)==0&&values.spking_users_review_count>2){
							
							discuss+='<b><em class="see-more"><a href="#" class="see_more_c"  id="'+((values.spking_users_review_count/3)+1)+'" data-id-parent="'+value.id_parent+'">查看更多>></a></em></b>';
						}
	
			});

			discuss+='</span>';

		}
								
		discuss+='</span>';
		discuss+='</p>';
		discuss+='</li>';

	});
	
	return discuss;

}

//回复列表
function list_skping_reiew(result){  

	var discuss = '';

	$.each(result, function(i, value) {

			discuss+='<b><em class="replyname">'+value.name+'</em> <a class="replycontent"  data-id-parent="'+value.id_parent+'" style="color:#000000">'+value.content+'</a><i>'+value.created+'</i></b>';
			
			if(i == value.count&&value.is_review==true){
				discuss+='<b><em class="see-more"  ><a href="#" class="see_more_c" id="'+(value.num+1)+'" data-id-parent="'+value.id_parent+'">查看更多>></a></em></b>';
			}
			
	});
								
	return discuss;

}


function init(){ 

	$("#relative_file").change(function(){
		$("#image_upload").submit();
	});
	
//	var u = navigator.userAgent;
//
//	if (u.indexOf('Android') > -1 || u.indexOf('Linux') > -1) {
//		$("#relative_file").click(function(){
//			var fileInput = document.getElementById("relative_file");//隐藏的file文本ID 
//			fileInput.click();//加一个触发事件 
//
//			fileInput.change(function(){
//				$("#image_upload").submit();
//			});
//
//		});
//	}

	$('.delete').click(function () {

		$(this).parent().remove();
		img_num_sele();
		
	});

	$('.see_more_c').click(function () {

		  var num = $(this).attr("id")?$(this).attr("id"):2;
		  var id_review = $(this).attr("data-id-parent");

		  $.get(base_url + '/community/list_spking_review', {id_review: id_review,rt_page:num},
			function (result) {
				 var lists = list_skping_reiew(result);
				 $("#reply_"+id_review+"").html(lists);
				 $('.comment_popup').hide();
				 $("#relative_pic").remove();
				 init();
				 img_num_sele();
				 J.Scroll('#active_details_container');
			}, 'json');
		
	});

	$("#p_review_r").click(function(){
		 
		$("#comment_area_details").val('');
		$(".comment_popup").show();

		$("#comment_area_details").bind("input propertychange", function() { 
			$(this).next('button').addClass('send_active01');

		    var height_comment =  $(".comment_choose").height();

			if(height_comment==65){
				$(".comment_choose a").attr("style","line-height: 1.8;");
				$("#sendbutton").attr("style","margin-top: 15px;");
			}else if(height_comment==46){
				$(".comment_choose a").attr("style","line-height: 1.4;");
				$("#sendbutton").attr("style","margin-top: 8px;");
			}else if(height_comment==36){
				$(".comment_choose a").attr("style","line-height: 1.2;");
				$("#sendbutton").attr("style","margin-top: 0px;");
			}else if(height_comment==48){
				$(".comment_choose a").attr("style","line-height: 1.4;");
				$("#sendbutton").attr("style","margin-top: 8px;");
			}else {
				$(".comment_choose a").attr("style","line-height: 1.2;");
				$("#sendbutton").attr("style","margin-top: 0px;");
			}

		}); 

		$('.comment_area').attr('data-id-parent',"0");
		$("#comment_area_details").attr('placeholder','评论内容');

		J.Scroll('#active_details_container');

	});

	$('.more_choice').click(function () {
		
		var iconid = $(this).attr("id");

		if(iconid=='iconudd'){

			$(".pop_pic").show();
			$(".pop_expression").hide();
			$('#iconudd').attr("class","more_choice left hidden");
			$('#iconadd').attr("class","more_choice left relative");		
	
		}else{
							
			$(".pop_expression").hide();
			$(".pop_pic").hide();
			$('#iconudd').attr("class","more_choice left relative");
			$('#iconadd').attr("class","more_choice left hidden");		
			

		}

	});


	$('#smile').click(function () {
		
		if($('.pop_expression').is(':hidden')){
			$(".pop_expression").show();
			$(".pop_pic").hide();
			$('#iconudd').attr("class","more_choice left relative");
			$('#iconadd').attr("class","more_choice left hidden");	
		}

	});

	$('.pica').click(function () {
		$(".pop_expression").hide();
	});

	$('.replycontent').click(function () {

		var data_prant = $(this).attr('data-id-parent');
		var user_name = $(this).prev(".replyname").text();

		$("#comment_area_details").attr('placeholder','回复 '+user_name);
		$('#comment_area_details').attr('data-id-parent',data_prant);
		$("#comment_area_details").val('');
		$(".comment_popup").show();

		$("#comment_area_details").bind("input propertychange", function() { 
			$(this).next('button').addClass('send_active01');
		}); 

		$("#user_name_review").val(user_name);

	});

	$('.say-person-con').click(function () {

		var data_prant = $(this).attr('data-id-parent');
		
		$("#comment_area_details").attr('placeholder','回复内容');
		$('#comment_area_details').attr('data-id-parent',data_prant);
		$("#comment_area_details").val('');
		$(".comment_popup").show();

		$("#comment_area_details").bind("input propertychange", function() { 
			$(this).next('button').addClass('send_active01');
		}); 

	});

	$('.bluish_green').click(function () {

		var id = $(this).attr("id");
		var source = $(this).attr("data-source");

		if(source == 'resource'){
			source = 'resource_by';	
		}else if(source == 'community'){
			source = 'activity_by';
		}

		moreCode(aid,source);
	});
	

}