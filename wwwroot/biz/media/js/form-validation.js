//jQuery.extend(jQuery.validator.messages, {
//	required : "必填项",
//	remote : "请修正该字段",
//	email : "请输入正确格式的电子邮件",
//    isPhone:"请输入有效的联系电话",
//	url : "请输入合法的网址",
//	date : "请输入合法的日期",
//	dateISO : "请输入合法的日期 (ISO).",
//	number : "请输入合法的数字",
//	digits : "只能输入整数",
//	creditcard : "请输入合法的信用卡号",
//	equalTo : "请再次输入相同的密码",
//	accept : "请输入拥有合法后缀名的字符串",
//	maxlength : jQuery.validator.format("请最多输入{0}个字符。"),
//	minlength : jQuery.validator.format("请最少输入{0}个字符。"),
//	rangelength : jQuery.validator.format("请输入长度在{0}到{1}个字符。"),
//	range : jQuery.validator.format("请输入{0}到{1}之间的字符"),
//	max : jQuery.validator.format("最大值不能超过{0}"),
//	min : jQuery.validator.format("最小值不能小于{0}")
//});

jQuery.extend(jQuery.validator.messages, {
    required : "X",
    remote : "X",
    email : "X",
    isPhone:"X",
    url : "X",
    date : "X",
    dateISO : "X",
    number : "X",
    digits : "X",
    creditcard : "X",
    equalTo : "X",
    accept : "X",
    maxlength : jQuery.validator.format("X"),
    minlength : jQuery.validator.format("X"),
    rangelength : jQuery.validator.format("X"),
    range : jQuery.validator.format("X"),
    max : jQuery.validator.format("X"),
    min : jQuery.validator.format("X")
});

function jump_url(url) {
	if (url) {
		window.location.href = url;
	}
}

function post_ajax(from) {
	$.post($("#" + from).attr('action'), $("#" + from).serialize(), function(
			data, textStatus) {
		if (data.status == 1) {
			//post_success(data.msg, data.data.add_url, data.data.list_url);
            if(from == 'add_activity'|| from == 'answer_two'){
                window.location.href = data.data.url;
            }else{
			    html_notice('操作成功',data.msg,data.data);
            }
		} else {
			//post_error(data.msg);
            if(from == 'add_activity'||from == 'answer_two'){
                $('.alert-error').show();
                $('.alert-success').hide();
            }else{
                html_notice('操作失败',data.msg,data.data);
            }
		}
	}, "json");
}


function html_notice(title,msg,data){
	var nhtml = '<div id="prompt">';
	nhtml += '<div class="title">'+title+'</div>';
	nhtml += '<div class="content">'+msg+'</div>';
	nhtml += '<div class="prompt_button"><a class="determine prompt_confirm" href="javascript:void(0)" att="'+data.url+'" did="'+data.is_refresh+'">确定</a></div>';
	nhtml += '</div><div id="pop_up"></div>';
	
	$("body").append(nhtml);
}

$(".prompt_confirm").live('click',function(){
	$("#prompt").remove();
	$("#pop_up").remove();
    var is_refresh = $(this).attr('did');
    var url = $(this).attr('att');
    if(is_refresh == 1){
        window.location.href = document.location.href;
    }else if(is_refresh == 2){
        window.location.href = url;
    }
});

//function CheckMail(email)
//{
//    var MailReg = /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
//    if ( ! MailReg.test(email) )
//        return false;
//
//    return true;
//}
//
//function CheckPhone(phone)
//{
//    var PhoneReg = /^(13+\d{9})|(15+\d{9})|(18+\d{9})$/;
//    if ( ! PhoneReg.test(phone) )
//        return false;
//
//    return true;
//}

// 联系电话(手机/电话皆可)验证
jQuery.validator.addMethod("isPhone", function(value,element) {
    var length = value.length;
    var mobile = /^(((13[0-9]{1})|(15[0-9]{1}))+\d{8})$/;
    var tel = /^\d{3,4}-?\d{7,9}$/;
    return this.optional(element) || (tel.test(value) || mobile.test(value));
}, "X");//请正确填写您的联系电话

// 文字验证匹配
jQuery.validator.addMethod("isBlank", function(value,element) {
    var values = value.replace(/(\r)*\n/g,"").replace(/\s/g,"").replace(/&nbsp;/g,"").replace(/<br\/>/g,"");
    return this.optional(element) || (values != '');
}, "X");//请正确填写描述内容！

// 判断是否上传图片
jQuery.validator.addMethod("isUpload", function(value,element) {
    return this.optional(element) || ((value > 0) && $('#image_src').val() != '');
}, "X");//你忘了上传附件了哦！

// 判断企业log是否上传图片
jQuery.validator.addMethod("isUploadLogo", function(value,element) {
    return this.optional(element) || ((value > 0) && $('#logo_src').val() != '');
}, "X");//你忘了上传logo了哦！

// 判断是否上传音频
jQuery.validator.addMethod("isUploadAudio", function(value,element) {
    return this.optional(element) || ((value > 0) && $('#audio_src').val() != '');
}, "X");//你忘了上传音频附件了哦！

// 判断是否存在门店联系方式
jQuery.validator.addMethod("isHavePhone", function(value,element) {
    return this.optional(element) || ((value > 0));
}, "X");//请至少填写一个门店联系方式！

// 判断是否存在关键字回复内容
jQuery.validator.addMethod("isHavekey", function(value,element) {
    return this.optional(element) || ($('#wx_content').val() != '' && $('input[name="cnt_selected[]"]').length > 0);
}, "X");//"请填写完整关键字自动回复信息！"

// 判断输入数据是否符合要求
jQuery.validator.addMethod("isCompliance", function(value,element) {
    return this.optional(element) || (value >= 6 && value <= 12);
}, "X");//"请填写6~12之间的数字！"

// 去掉前后空格后比较文字长度
jQuery.validator.addMethod("text_trim", function(value,element) {
    return this.optional(element) || ($.trim(value).length >= 2 && $.trim(value).length <= 25);
}, "X");//请确保文字前后无空格后文字长度在2~25以内！

var FormValidation = function() {
	return {
		//main function to initiate the module
		init : function() {
			// for more info visit the official plugin documentation: 
			// http://docs.jquery.com/Plugins/Validation
			var form1 = $('#add_activity');
			var error1 = $('.alert-error', form1);
			var success1 = $('.alert-success', form1);
			form1.validate({
				errorElement : 'span', //default input error message container
				errorClass : 'help-inline', // default input error message class
				focusInvalid : false, // do not focus the last invalid input
				ignore : "",
				rules : {
					title : {
//                        minlength:2,
                        text_trim:true,
                        maxlength:25,
						required : true
					},
                    content : {
                        minlength : 2,
                        maxlength : 2000,
                        isBlank : true,
                        required : true
                    },
                    picNum:{
                        isUpload : true,
                        required : true
                    },
                    sort: {
                        number : true
                    }
				},
				invalidHandler : function(event, validator) { //display error alert on form submit              
					success1.hide();
					error1.show();
					App.scrollTo(error1, -200);
				},
				highlight : function(element) { // hightlight error inputs
					$(element).closest('.help-inline').removeClass('ok'); // display OK icon
					$(element).closest('.control-group').removeClass('success')
							.addClass('error'); // set error class to the control group
				},
				unhighlight : function(element) { // revert the change dony by hightlight
					$(element).closest('.control-group').removeClass('error'); // set error class to the control group
				},
				success : function(label) {
					label.addClass('valid').addClass('help-inline ok') // mark the current input as valid and display OK icon
					.closest('.control-group').removeClass('error').addClass(
							'success'); // set success class to the control group
				},
				submitHandler : function(form) {
					success1.show();
					error1.hide();
					post_ajax('add_activity');
					//form.submit();
				}
			});
		},

        //一战到底第二步设置
        answerTwoInit : function() {
            // for more info visit the official plugin documentation:
            // http://docs.jquery.com/Plugins/Validation
            var form1 = $('#answer_two');
            var error1 = $('.alert-error', form1);
            var success1 = $('.alert-success', form1);
            form1.validate({
                errorElement : 'span', //default input error message container
                errorClass : 'help-inline', // default input error message class
                focusInvalid : false, // do not focus the last invalid input
                ignore : "",
                rules : {
                    end_time : {
                        required : true
                    }
                },
                invalidHandler : function(event, validator) { //display error alert on form submit
                    success1.hide();
                    error1.show();
                    App.scrollTo(error1, -200);
                },
                highlight : function(element) { // hightlight error inputs
                    $(element).closest('.help-inline').removeClass('ok'); // display OK icon
                    $(element).closest('.control-group').removeClass('success')
                        .addClass('error'); // set error class to the control group
                },
                unhighlight : function(element) { // revert the change dony by hightlight
                    $(element).closest('.control-group').removeClass('error'); // set error class to the control group
                },
                success : function(label) {
                    label.addClass('valid').addClass('help-inline ok') // mark the current input as valid and display OK icon
                        .closest('.control-group').removeClass('error').addClass(
                            'success'); // set success class to the control group
                },
                submitHandler : function(form) {
                    success1.show();
                    error1.hide();
                    post_ajax('answer_two');
                    //form.submit();
                }
            });
        },

        //砸金蛋第二步设置
        eggTwoInit : function() {
            // for more info visit the official plugin documentation:
            // http://docs.jquery.com/Plugins/Validation
            var form1 = $('#egg_two');
            var error1 = $('.alert-error', form1);
            var success1 = $('.alert-success', form1);
            form1.validate({
                errorElement : 'span', //default input error message container
                errorClass : 'help-inline', // default input error message class
                focusInvalid : false, // do not focus the last invalid input
                ignore : "",
                rules : {
                    end_time : {
                        required : true
                    },
                    person_count : {
                        required : true
                    },
                    activity_day : {
                        required : true
                    }
                },
                invalidHandler : function(event, validator) { //display error alert on form submit
                    success1.hide();
                    error1.show();
                    App.scrollTo(error1, -200);
                },
                highlight : function(element) { // hightlight error inputs
                    $(element).closest('.help-inline').removeClass('ok'); // display OK icon
                    $(element).closest('.control-group').removeClass('success')
                        .addClass('error'); // set error class to the control group
                },
                unhighlight : function(element) { // revert the change dony by hightlight
                    $(element).closest('.control-group').removeClass('error'); // set error class to the control group
                },
                success : function(label) {
                    label.addClass('valid').addClass('help-inline ok') // mark the current input as valid and display OK icon
                        .closest('.control-group').removeClass('error').addClass(
                            'success'); // set success class to the control group
                },
                submitHandler : function(form) {
                    success1.show();
                    error1.hide();
                    post_ajax('egg_two');
                    //form.submit();
                }
            });
        },

        //事件第二步设置
        eventTwoInit : function() {
            // for more info visit the official plugin documentation:
            // http://docs.jquery.com/Plugins/Validation
            var form1 = $('#event_two');
            var error1 = $('.alert-error', form1);
            var success1 = $('.alert-success', form1);
            form1.validate({
                errorElement : 'span', //default input error message container
                errorClass : 'help-inline', // default input error message class
                focusInvalid : false, // do not focus the last invalid input
                ignore : "",
                rules : {
                    bound : {
                        required : true
                    }
                },
                invalidHandler : function(event, validator) { //display error alert on form submit
                    success1.hide();
                    error1.show();
                    App.scrollTo(error1, -200);
                },
                highlight : function(element) { // hightlight error inputs
                    $(element).closest('.help-inline').removeClass('ok'); // display OK icon
                    $(element).closest('.control-group').removeClass('success')
                        .addClass('error'); // set error class to the control group
                },
                unhighlight : function(element) { // revert the change dony by hightlight
                    $(element).closest('.control-group').removeClass('error'); // set error class to the control group
                },
                success : function(label) {
                    label.addClass('valid').addClass('help-inline ok') // mark the current input as valid and display OK icon
                        .closest('.control-group').removeClass('error').addClass(
                            'success'); // set success class to the control group
                },
                submitHandler : function(form) {
                    success1.show();
                    error1.hide();
                    post_ajax('event_two');
                    //form.submit();
                }
            });
        },


        //关键字自动回复
        replayInit : function() {
            // for more info visit the official plugin documentation:
            // http://docs.jquery.com/Plugins/Validation
            var form1 = $('#form_wx_reply');
            var error1 = $('.alert-error', form1);
            var success1 = $('.alert-success', form1);
            form1.validate({
                errorElement : 'span', //default input error message container
                errorClass : 'help-inline', // default input error message class
                focusInvalid : false, // do not focus the last invalid input
                ignore : "",
                rules : {
                    wx_rule : {
                        minlength:1,
                        required : true
                    },
                    wx_content : {
                    	 minlength:1,
                        isBlank : true,
                         required : true
                    }
                },
                invalidHandler : function(event, validator) { //display error alert on form submit
                    success1.hide();
                    error1.show();
                    App.scrollTo(error1, -200);
                },
                highlight : function(element) { // hightlight error inputs
                    $(element).closest('.help-inline').removeClass('ok'); // display OK icon
                    $(element).closest('.control-group').removeClass('success')
                        .addClass('error'); // set error class to the control group
                },
                unhighlight : function(element) { // revert the change dony by hightlight
                    $(element).closest('.control-group').removeClass('error'); // set error class to the control group
                },
                success : function(label) {
                    label.addClass('valid').addClass('help-inline ok') // mark the current input as valid and display OK icon
                        .closest('.control-group').removeClass('error').addClass(
                            'success'); // set success class to the control group
                },
                submitHandler : function(form) {
                    success1.show();
                    error1.hide();
                    post_ajax('form_wx_reply');
                    //form.submit();
                }
            });
        },

        //企业信息修改表单判断初始
        synopsisInit : function() {
            // for more info visit the official plugin documentation:
            // http://docs.jquery.com/Plugins/Validation
            var form1 = $('#form_synopsis_edit');
            var error1 = $('.alert-error', form1);
            var success1 = $('.alert-success', form1);
            form1.validate({
                errorElement : 'span', //default input error message container
                errorClass : 'help-inline', // default input error message class
                focusInvalid : false, // do not focus the last invalid input
                //onkeyup:true,// 是否在敲击键盘时验证,默认:true
                ignore : "",
                rules : {
                    title:{
                        minlength:2,
                        maxlength:25,
                        required:true
                    },
//                    content : {
//                        minlength : 10,
//                        maxlength : 50000,
//                        isBlank : true,
//                        required : true
//                    },
//                    picNum:{
//                        isUpload : true,
//                        required : true
//                    },
                    logoNum:{
                        isUploadLogo : true,
                        required : true
                    }
//                    ,
//                    phone : {
//                        isPhone:true,
//                        required : true
//                    }
                },
                onkeyup: function(element) {
                    $(element).valid();
                },
                onfocusout: function(element) {
                    $(element).valid();
                },
                invalidHandler : function(event, validator) { //display error alert on form submit
                    success1.hide();
                    error1.show();
                    App.scrollTo(error1, -200);
                },
                highlight : function(element) { // hightlight error inputs
                    $(element).closest('.help-inline').removeClass('ok'); // display OK icon
                    $(element).closest('.control-group').removeClass('success')
                        .addClass('error'); // set error class to the control group
                },
                unhighlight : function(element) { // revert the change dony by hightlight
                    $(element).closest('.control-group').removeClass('error'); // set error class to the control group
                },
                success : function(label) {
                    label.addClass('valid').addClass('help-inline ok') // mark the current input as valid and display OK icon
                        .closest('.control-group').removeClass('error').addClass(
                            'success'); // set success class to the control group
                },
                submitHandler : function(form) {
                    success1.show();
                    error1.hide();
                    post_ajax('form_synopsis_edit');
                    //form.submit();
                }
            });
        },

        //门店信息修改表单判断初始
        shopInit : function() {
            // for more info visit the official plugin documentation:
            // http://docs.jquery.com/Plugins/Validation
            var form1 = $('#form_shop_edit');
            var error1 = $('.alert-error', form1);
            var success1 = $('.alert-success', form1);
            form1.validate({
                errorElement : 'span', //default input error message container
                errorClass : 'help-inline', // default input error message class
                focusInvalid : false, // do not focus the last invalid input
                //onkeyup:true,// 是否在敲击键盘时验证,默认:true
                ignore : "",
                rules : {
                    title:{
                        minlength:2,
                        maxlength:25,
                        required:true
                    },
                    content : {
                        minlength : 10,
                        maxlength : 50000,
                        isBlank : true,
                        required : true
                    },
                    picNum : {
                        isUpload : true,
                        required:true
                    },
                    'phone[]' : {
                        minlength : 1
                    },
                    'phone_name[]':{
                        minlength : 2,
                        maxlength : 20
                    },
                    contact_count:{
                        isHavePhone : true,
                        required : true
                    }
                },
                invalidHandler : function(event, validator) { //display error alert on form submit
                    success1.hide();
                    error1.show();
                    App.scrollTo(error1, -200);
                },
                highlight : function(element) { // hightlight error inputs
                    $(element).closest('.help-inline').removeClass('ok'); // display OK icon
                    $(element).closest('.control-group').removeClass('success')
                        .addClass('error'); // set error class to the control group
                },
                unhighlight : function(element) { // revert the change dony by hightlight
                    $(element).closest('.control-group').removeClass('error'); // set error class to the control group
                },
                success : function(label) {
                    label.addClass('valid').addClass('help-inline ok') // mark the current input as valid and display OK icon
                        .closest('.control-group').removeClass('error').addClass(
                            'success'); // set success class to the control group
                },
                submitHandler : function(form) {
                    success1.show();
                    error1.hide();
                    post_ajax('form_shop_edit');
                    //form.submit();
                }
            });
        },


        //物品信息修改表单判断初始
        itemInit : function() {
            // for more info visit the official plugin documentation:
            // http://docs.jquery.com/Plugins/Validation
            var form1 = $('#form_item_add');
            var error1 = $('.alert-error', form1);
            var success1 = $('.alert-success', form1);
            form1.validate({
                errorElement : 'span', //default input error message container
                errorClass : 'help-inline', // default input error message class
                focusInvalid: false,//表单提交时, 焦点会指向第一个没有通过验证的域
                onsubmit:true,// 是否在提交是验证,默认:true
                onfocusout:true,// 是否在获取焦点时验证,默认:true
                onkeyup:true,// 是否在敲击键盘时验证,默认:true
                ignore : "",
                rules : {
                    title:{
                        minlength:2,
                        maxlength:25,
                        required : true
                    },
                    content : {
                        minlength : 10,
                        maxlength : 1000,
                        isBlank : true,
                        required : true
                    },
//                    picNum : {
//                        isUpload : true,
//                        required:true
//                    },
                    phone : {
                        isphone:true
                    },
                    price : {
                        number:true
                    },
                    weight: {
                        number : true
                    },
                    mall_quantity : {
                        number:true
                    },
                    mall_integral: {
                        number : true
                    }
                },
                invalidHandler : function(event, validator) { //display error alert on form submit
                    success1.hide();
                    error1.show();
                    App.scrollTo(error1, -200);
                },
                highlight : function(element) { // hightlight error inputs
                    $(element).closest('.help-inline').removeClass('ok'); // display OK icon
                    $(element).closest('.control-group').removeClass('success')
                        .addClass('error'); // set error class to the control group
                },
                unhighlight : function(element) { // revert the change dony by hightlight
                    $(element).closest('.control-group').removeClass('error'); // set error class to the control group
                },
                success : function(label) {
                    label.addClass('valid').addClass('help-inline ok') // mark the current input as valid and display OK icon
                        .closest('.control-group').removeClass('error').addClass(
                            'success'); // set success class to the control group
                },
                submitHandler : function(form) {
                    success1.show();
                    error1.hide();
                    post_ajax('form_item_add');
                    //form.submit();
                }
            });
        },

        //内容信息判断
        contentInit : function() {
        // for more info visit the official plugin documentation:
        // http://docs.jquery.com/Plugins/Validation
        var form1 = $('#form_content_add');
        var error1 = $('.alert-error', form1);
        var success1 = $('.alert-success', form1);
        form1.validate({
            errorElement : 'span', //default input error message container
            errorClass : 'help-inline', // default input error message class
            focusInvalid: false,//表单提交时, 焦点会指向第一个没有通过验证的域
            onsubmit:true,// 是否在提交是验证,默认:true
            onfocusout:true,// 是否在获取焦点时验证,默认:true
            onkeyup:true,// 是否在敲击键盘时验证,默认:true
            ignore : "",
            rules : {
                title:{
                    minlength:2,
                    maxlength:100,
                    required : true
                },
                content : {
                    minlength : 10,
                    maxlength : 60000,
                    isBlank : true,
                    required : true
                },
                weight: {
                    number : true
                }
            },
            invalidHandler : function(event, validator) { //display error alert on form submit
                success1.hide();
                error1.show();
                App.scrollTo(error1, -200);
            },
            highlight : function(element) { // hightlight error inputs
                $(element).closest('.help-inline').removeClass('ok'); // display OK icon
                $(element).closest('.control-group').removeClass('success')
                    .addClass('error'); // set error class to the control group
            },
            unhighlight : function(element) { // revert the change dony by hightlight
                $(element).closest('.control-group').removeClass('error'); // set error class to the control group
            },
            success : function(label) {
                label.addClass('valid').addClass('help-inline ok') // mark the current input as valid and display OK icon
                    .closest('.control-group').removeClass('error').addClass(
                        'success'); // set success class to the control group
            },
            submitHandler : function(form) {
                success1.show();
                error1.hide();
                post_ajax('form_content_add');
                //form.submit();
            }
        });
    },

    //歌曲信息修改表单判断初始
        songInit : function() {
            // for more info visit the official plugin documentation:
            // http://docs.jquery.com/Plugins/Validation
            var form1 = $('#form_song_add');
            var error1 = $('.alert-error', form1);
            var success1 = $('.alert-success', form1);
            form1.validate({
                errorElement : 'span', //default input error message container
                errorClass : 'help-inline', // default input error message class
                focusInvalid : false, // do not focus the last invalid input
                onkeyup :true,// 是否在敲击键盘时验证,默认:true
                ignore : "",
                rules : {
                    name:{
                        minlength:1,
                        maxlength:25,
                        required : true
                    },
                    singer:{
                        minlength:1,
                        maxlength:20,
                        required : true
                    },
//                    lyric:{
//                        minlength:10,
//                        maxlength:3000,
//                        isBlank : true,
//                        required : true
//                    },
//                    audioNum : {
//                        isUploadAudio:true,
//                        required : true
//                    },
//                    picNum : {
//                        isUpload : true,
//                        required:true
//                    },
                    weight : {
                        number : true
                    }
                },
                invalidHandler : function(event, validator) { //display error alert on form submit
                    success1.hide();
                    error1.show();
                    App.scrollTo(error1, -200);
                },
                highlight : function(element) { // hightlight error inputs
                    $(element).closest('.help-inline').removeClass('ok'); // display OK icon
                    $(element).closest('.control-group').removeClass('success')
                        .addClass('error'); // set error class to the control group
                },
                unhighlight : function(element) { // revert the change dony by hightlight
                    $(element).closest('.control-group').removeClass('error'); // set error class to the control group
                },
                success : function(label) {
                    label.addClass('valid').addClass('help-inline ok') // mark the current input as valid and display OK icon
                        .closest('.control-group').removeClass('error').addClass(
                            'success'); // set success class to the control group
                },
                submitHandler : function(form) {
                    success1.show();
                    error1.hide();
                    post_ajax('form_song_add');
                    //form.submit();
                }
            });
        },
        
        
        
        //修改密码
        modify_passwd : function() {
            // for more info visit the official plugin documentation:
            // http://docs.jquery.com/Plugins/Validation
            var form1 = $('#modify_passwd');
            var error1 = $('.alert-error', form1);
            var success1 = $('.alert-success', form1);
            form1.validate({
                errorElement : 'span', //default input error message container
                errorClass : 'help-inline', // default input error message class
                focusInvalid : false, // do not focus the last invalid input
                ignore : "",
                rules : {
                	opasswd:{
                        minlength:6,
                        required:true
                    },
                    npasswd : {
                        minlength : 6,
                        required : true
                    },
                    rnpasswd : {
                    	required:true,
                        equalTo: "#npasswd"
                    }
                },
                invalidHandler : function(event, validator) { //display error alert on form submit
                    success1.hide();
                    error1.show();
                    App.scrollTo(error1, -200);
                },
                highlight : function(element) { // hightlight error inputs
                    $(element).closest('.help-inline').removeClass('ok'); // display OK icon
                    $(element).closest('.control-group').removeClass('success')
                        .addClass('error'); // set error class to the control group
                },
                unhighlight : function(element) { // revert the change dony by hightlight
                    $(element).closest('.control-group').removeClass('error'); // set error class to the control group
                },
                success : function(label) {
                    label.addClass('valid').addClass('help-inline ok') // mark the current input as valid and display OK icon
                        .closest('.control-group').removeClass('error').addClass(
                            'success'); // set success class to the control group
                },
                submitHandler : function(form) {
                    success1.show();
                    error1.hide();
                    post_ajax('modify_passwd');
                    //form.submit();
                }
            });
        },


        //创建电子券信息的表单判断
        ticketInit : function() {
            // for more info visit the official plugin documentation:
            // http://docs.jquery.com/Plugins/Validation
            var form1 = $('#form_ticket_add');
            var error1 = $('.alert-error', form1);
            var success1 = $('.alert-success', form1);
            form1.validate({
                errorElement : 'span', //default input error message container
                errorClass : 'help-inline', // default input error message class
                focusInvalid : false, // do not focus the last invalid input
                onkeyup :true,// 是否在敲击键盘时验证,默认:true
                ignore : "",
                rules : {
                    name:{
                        minlength:1,
                        maxlength:20,
                        required : true
                    },
                    number:{
                        minlength:1,
                        maxlength:6,
                        required : true,
                        number:true
                    },
                    length:{
                        isCompliance : true,
                        required : true,
                        number:true
                    },
                    valid_begin:{
                        required : true
                    },
                    valid_end:{
                        required : true
                    },
                    content : {
                        minlength:10,
                        maxlength:60000,
                        required : true
                    }
                },
                invalidHandler : function(event, validator) { //display error alert on form submit
                    success1.hide();
                    error1.show();
                    App.scrollTo(error1, -200);
                },
                highlight : function(element) { // hightlight error inputs
                    $(element).closest('.help-inline').removeClass('ok'); // display OK icon
                    $(element).closest('.control-group').removeClass('success')
                        .addClass('error'); // set error class to the control group
                },
                unhighlight : function(element) { // revert the change dony by hightlight
                    $(element).closest('.control-group').removeClass('error'); // set error class to the control group
                },
                success : function(label) {
                    label.addClass('valid').addClass('help-inline ok') // mark the current input as valid and display OK icon
                        .closest('.control-group').removeClass('error').addClass(
                            'success'); // set success class to the control group
                },
                submitHandler : function(form) {
                    success1.show();
                    error1.hide();

                    if(!$('#id_ticket').val()){
                        $.post('/biz/ticket/search_tname', {
                            'name':$('input[name="name"]').val()
                        }, function(msg){
                            if(msg.status == 1){
                                post_ajax('form_ticket_add');
                                return true;
                            }else{
                                $('.alert-success').hide();
                                $('.alert-error').show();

                                $('input[name="name"]').parent().parent().closest('.control-group').removeClass('success').addClass('error');
                                alert('电子券名称重复了！');
                                return false;
                            }
                        }, 'json');
                    }else{
                        post_ajax('form_ticket_add');
                    }
                    //form.submit();
                }
            });
        },

        //互动活动信息修改表单判断初始
        communityInit : function() {
            // for more info visit the official plugin documentation:
            // http://docs.jquery.com/Plugins/Validation
            var form1 = $('#form_community_add');
            var error1 = $('.alert-error', form1);
            var success1 = $('.alert-success', form1);
            form1.validate({
                errorElement : 'span', //default input error message container
                errorClass : 'help-inline', // default input error message class
                focusInvalid: false,//表单提交时, 焦点会指向第一个没有通过验证的域
                onsubmit:true,// 是否在提交是验证,默认:true
                onfocusout:true,// 是否在获取焦点时验证,默认:true
                onkeyup:true,// 是否在敲击键盘时验证,默认:true
                ignore : "",
                rules : {
                    title:{
                        minlength:1,
                        maxlength:64,
                        required : true
                    },
                    content : {
                        minlength:10,
                        maxlength:60000,
                        required : true
                    },
                    picNum : {
                        isUpload : true,
                        required : true
                    }
                },
                invalidHandler : function(event, validator) { //display error alert on form submit
                    success1.hide();
                    error1.show();
                    App.scrollTo(error1, -200);
                },
                highlight : function(element) { // hightlight error inputs
                    $(element).closest('.help-inline').removeClass('ok'); // display OK icon
                    $(element).closest('.control-group').removeClass('success')
                        .addClass('error'); // set error class to the control group
                },
                unhighlight : function(element) { // revert the change dony by hightlight
                    $(element).closest('.control-group').removeClass('error'); // set error class to the control group
                },
                success : function(label) {
                    label.addClass('valid').addClass('help-inline ok') // mark the current input as valid and display OK icon
                        .closest('.control-group').removeClass('error').addClass(
                            'success'); // set success class to the control group
                },
                submitHandler : function(form) {
                    success1.show();
                    error1.hide();
                    post_ajax('form_community_add');
                    //form.submit();
                }
            });
        },

        //互动活动信息修改表单判断初始
        resourceInit : function() {
            // for more info visit the official plugin documentation:
            // http://docs.jquery.com/Plugins/Validation
            var form1 = $('#form_resource_add');
            var error1 = $('.alert-error', form1);
            var success1 = $('.alert-success', form1);
            form1.validate({
                errorElement : 'span', //default input error message container
                errorClass : 'help-inline', // default input error message class
                focusInvalid: false,//表单提交时, 焦点会指向第一个没有通过验证的域
                onsubmit:true,// 是否在提交是验证,默认:true
                onfocusout:true,// 是否在获取焦点时验证,默认:true
                onkeyup:true,// 是否在敲击键盘时验证,默认:true
                ignore : "",
                rules : {
                    title:{
                        minlength:1,
                        maxlength:64,
                        required : true
                    }
                },
                invalidHandler : function(event, validator) { //display error alert on form submit
                    success1.hide();
                    error1.show();
                    App.scrollTo(error1, -200);
                },
                highlight : function(element) { // hightlight error inputs
                    $(element).closest('.help-inline').removeClass('ok'); // display OK icon
                    $(element).closest('.control-group').removeClass('success')
                        .addClass('error'); // set error class to the control group
                },
                unhighlight : function(element) { // revert the change dony by hightlight
                    $(element).closest('.control-group').removeClass('error'); // set error class to the control group
                },
                success : function(label) {
                    label.addClass('valid').addClass('help-inline ok') // mark the current input as valid and display OK icon
                        .closest('.control-group').removeClass('error').addClass(
                            'success'); // set success class to the control group
                },
                submitHandler : function(form) {
                    success1.show();
                    error1.hide();
                    post_ajax('form_resource_add');
                    //form.submit();
                }
            });
        },

        //互动活动信息修改表单判断初始
        carInit : function() {
            // for more info visit the official plugin documentation:
            // http://docs.jquery.com/Plugins/Validation
            var form1 = $('#form_car_add');
            var error1 = $('.alert-error', form1);
            var success1 = $('.alert-success', form1);
            form1.validate({
                errorElement : 'span', //default input error message container
                errorClass : 'help-inline', // default input error message class
                focusInvalid: false,//表单提交时, 焦点会指向第一个没有通过验证的域
                onsubmit:true,// 是否在提交是验证,默认:true
                onfocusout:true,// 是否在获取焦点时验证,默认:true
                onkeyup:true,// 是否在敲击键盘时验证,默认:true
                ignore : "",
                rules : {
                    user_time:{
                        minlength:1,
                        maxlength:30,
                        required : true
                    },
                    user_card : {
                        minlength:1,
                        maxlength:7,
                        required : true
                    },
                    user_mileage : {
                        minlength:1,
                        maxlength:10,
                        required : true
                    },
                    user_card : {
                        minlength:1,
                        maxlength:7,
                        required : true
                    },
                    picNum : {
                        isUpload : true,
                        required : true
                    }
                },
                invalidHandler : function(event, validator) { //display error alert on form submit
                    success1.hide();
                    error1.show();
                    App.scrollTo(error1, -200);
                },
                highlight : function(element) { // hightlight error inputs
                    $(element).closest('.help-inline').removeClass('ok'); // display OK icon
                    $(element).closest('.control-group').removeClass('success')
                        .addClass('error'); // set error class to the control group
                },
                unhighlight : function(element) { // revert the change dony by hightlight
                    $(element).closest('.control-group').removeClass('error'); // set error class to the control group
                },
                success : function(label) {
                    label.addClass('valid').addClass('help-inline ok') // mark the current input as valid and display OK icon
                        .closest('.control-group').removeClass('error').addClass(
                            'success'); // set success class to the control group
                },
                submitHandler : function(form) {
                    success1.show();
                    error1.hide();
                    post_ajax('form_car_add');
                    //form.submit();
                }
            });
        }




    };
}();