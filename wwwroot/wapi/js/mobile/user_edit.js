$(function() {
	
	J.Scroll("#edit_info_section");

	$("#relative_file").live('change', function(){
		$("#image_upload").submit();
	})
	
	$("#save_btn").bind('click',function(){
		submit_info();
	});

	$('.logout_account').click(function () {

		popupBuy(aid,'logout');
		
	});

	$("#binding").click(function(event) {
		window.location.href = base_url + '/user/register?is_wechat_user=true';
	});

	$("#nick_name_u").click(function(event) {
		$("#nick_name").val('');
	});

	$("#sign").click(function(event) {
		$("#sign").hide();
		$("#sign_revise_info").attr('class','');
		$("#sign_revise_info").text('');
	});

	$("#sex_revise_info").click(function(event) {

		var sex = $('input[name="sex"]:checked').attr("value");

		if(sex==1){
			$("#sex_id").attr('value','男');
			$("#sex_id").attr('data-sex',1);
			$("#sex_revise_info_f").html('<label><input id="sex_man" type="radio" name="sex" value="1" id="RadioGroup1_0" checked="checked">男</label><label><input id="sex_women" type="radio" name="sex" value="2" id="RadioGroup1_1">女</label>');		
		}else{
			$("#sex_id").attr('value','女');
			$("#sex_id").attr('data-sex',2);
			$("#sex_revise_info_f").html('<label><input id="sex_man" type="radio" name="sex" value="1" id="RadioGroup1_0">男</label><label><input id="sex_women" type="radio" name="sex" value="2" id="RadioGroup1_1" checked="checked">女</label>');	
		}
		
		if($("#sex_id").is(":hidden")){

			$("#sex_id").attr('class','');
			$("#sex_revise_info_f").attr('class','right hidden');

		}else{
			$("#sex_id").attr('class','hidden');
			$("#sex_revise_info_f").attr('class','right');
		}

	});


	$("#binding_phone").click(function(event) {
		var phone_url = $("#binding_phone").attr('data-link');
		window.location.href = phone_url;
	});

	$("#edit_phone").click(function(event) {
		var phone_url = $("#edit_phone").attr('data-link');
		window.location.href = phone_url;
	});



	var date = new Date();

	var options = {
		setText: '确定',
		cancelText: '取消',

		preset : 'datetime',
		mode: 'scroller',
		display: 'modal',
		theme: 'android',
		condition: 'mo',

		dateFormat: 'yy-mm-dd',
		dateOrder: 'yymmdd',
		timeFormat: '',//HH:ii
		timeWheels: '',//HHii
		endYear:2060,
		startYear:1800

	}

	$('#date').scroller(options);

});


function show_img(data)
{
	if (data.status) {
		var url=data.url.replace('.jpg', '-small.jpg');
		var file_name=data.file_name.replace('.jpg', '-small.jpg');
		$('#pic').attr('src',url);
		$('#image_url').val(data.file_name);
		
		$.ajax({
			url:base_url+'/user_activity/save_img',
			data:'userid='+userid+'&image_url='+data.file_name,
			async: false,
			dataType: 'json',
			type:'get',
			success:function(data){
				J.showToast(data.msg); 
			}
		});
	}
}

function submit_info(){

	if($('#account_name').length > 0){
		var account_name = $('#account_name').val();

		if(account_name == ''){
			J.showToast('请输入账号为5-12位的字母+数字！');
			return false;
		}
	}

	var nick_name = $('#nick_name').val();
	var sign = $('#sign_revise_info').val();
	if(sign==""){
		sign ="暂无签名";
	}

	if(nick_name == ''){
		J.showToast('请填写昵称！');
		return;
	}

	var sex = $('input[name="sex"]:checked').val();

	if(!sex){
		var sex = $('input[name="sex"]').val();
	}

	var birthday = $('#date').val();

	$.post(base_url + '/user_activity/user_info', {
		'userid':userid,
		'account_name':account_name,
		'nick_name': nick_name,
		'sex':sex,
		'birthday':birthday,
		'sign':sign
	}, function(data){
		J.showToast(data.msg);
 		$("#sign").html(sign);
		$("#sign_revise_info").attr('class','hidden');
 		$("#sign").show();
	}, 'json');
	
}

