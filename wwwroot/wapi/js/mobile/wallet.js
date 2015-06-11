$(function () {

	  var num =2;
	  $("#morelook").click(function(){
			$.get(base_url+"/user_activity/my_wallet?num="+num, function(result){
				  var personal_homepage_activity = wallet_list(result);
				  $(".personal_homepage_activity").html(personal_homepage_activity);
				  num++;
			});
	   });


	$("#hint_i").click(function(){
		$(".hint").toggle();
	});

	$("#butedit").click(function(){
		var money_balance = $("#money_balance").text();
		var price = $('#price').val();
		
		if(money_balance == 0) {
			J.showToast('可提现金额不足');
			return;
		}

		if(price > money_balance){
			J.showToast('可提现金额不足');
			return;
		}

		popupBuy('', 'wallet');
	});

});

function wallet_list(result){  

	var discuss = '';
	var time_month_day ='';

	$.each(result, function(i, value) {

		discuss+='<li>';
		
		if(time_month_day!=value.time_month_day){
			discuss+='<span class="left time"><b>'+value.time_day+'</b>'+value.time_month+'月</span>';
		}

		discuss+='<span class="user_activity_mes right">';
		discuss+='<h4><a href="/wapi/'+value.id_business+'/0/community/detail?aid='+value.id_activity+'">'+value.order_name+'</a></h4>';
		discuss+='</span>';      
		discuss+='</li>';

		time_month_day = value.time_month_day;

	});

	return discuss;

}