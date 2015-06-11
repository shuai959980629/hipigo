var auth = {"user":false,"pwd":false};
$(document).ready(function(){
    //验证手机号码
    $("#phone").blur(function(){
        var mobile = $(this).val();
        if(mobile.length==0){
            $(this).attr('class','error');
            $("#login").attr('class','button');
            auth.user = false;
        }else if(!rule.userreg.test(mobile)){
            J.showToast("请输入正确的帐号！");
            $(this).attr('class','error');
            $("#login").attr('class','button');
            auth.user = false;
        }else{
            $(this).attr('class','left');
            $("#login").attr('class','button1');
            auth.user = true;
        }
    });

    $("#phone").focus(function(){
        $(this).attr('class','left');
        $("#login").attr('class','button');
    });

    //密码验证
    $("#pwd").blur(function(){
        var pwd = $(this).val();
        if(pwd.length==0){
            $("#login").attr('class','button');
            $(this).attr('class','error');
            auth.pwd =  false;
        }else if(!rule.pwdreg.test(pwd)||pwd.length>18 || pwd.length<6){
            J.showToast("请输入6-18位数字加字母密码！");
//            showPop("请输入6-18位数字加字母密码！");
            $("#login").attr('class','button');
            $(this).attr('class','error');
            auth.pwd =  false;
        }else{
            $(this).attr('class','left');
            $("#login").attr('class','button1');
            auth.pwd =  true;
        }
    });

    $("#pwd").focus(function(){
        $(this).attr('class','left');
        $("#login").attr('class','button');
    });

    //登录
    $("#login").click(function(){
        $("#login").focus();
        var phone = $("#phone").val();
        var pwd = $("#pwd").val();
        if(auth.user == false||auth.pwd == false){
            $("#login").attr('class','button');
            if(!auth.user){
                J.showToast("请输入正确的帐号！");
                $("#phone").attr('class','error');
                return false;
            }
            if(phone.indexOf('hipigo') >= 0 && phone != 'hipigo'){
                J.showToast("您输入的帐号不能存在'hipigo'！");
                $("#phone").attr('class','error');
                return false;
            }
            if(!auth.pwd){
                J.showToast("请输入6-18位数字加字母密码！");
                $("#pwd").attr('class','error');
                return false;
            }
            return false;
        }
        $("#login").attr('class','button');
        $("#login").hide();
        $.ajax({
            url:url+'/user/login',
            data:'phone='+phone+'&pwd='+pwd+"&t=login",
            async: true,
            dataType: 'json',
            type:'post',
            success:function(data){
                if(data.status){
                    window.location.href=url +"/community/home"
                }else{
                    $("#login").show();
                    $("#login").html('<img src="<!--{$url_prefix}-->img/loading.gif"/>登录中');
                    $("#login").attr('class','button');
                    J.showToast(data.msg.infor);
                    $("#login").attr('class','button');
                    $("#login").html('登录');
                    if(data.msg.type=='user'){
                        $("#phone").attr('class','error');
                    }
                    if(data.msg.type=='pwd'){
                        $("#pwd").attr('class','error');
                    }
                }
            }
        });
    });
});
