var auth = {"user":false,"pwd":false,"PWD":false,"code":false};
$(document).ready(function(){
    $("#user").blur(function(){
        var user = $(this).val();
        if(user.length==0){
            $(this).attr('class','error');
            auth.user = false;
        }else if(rule.phonereg.test(user)){
            J.showToast("该帐号已被注册!");
            $(this).attr('class','error');
            auth.user = false;
        }else if(!rule.userreg.test(user)){
            J.showToast("请输入正确的账号!");
            $(this).attr('class','error');
            auth.user = false;

        }else{
            $(this).attr('class','left');
            auth.user = true;
        }
    });

    $("#user").focus(function(){
        $(this).attr('class','left');
    });

    $("#pwd").blur(function(){
        var pwd = $(this).val();
        if(pwd.length==0){
            $(this).attr('class','error');
            auth.pwd =  false;
        }else if(!rule.pwdreg.test(pwd)||pwd.length>18 || pwd.length<6){
            J.showToast("请输入6-18位数字加字母!");
            $(this).attr('class','error');
            auth.pwd =  false;
        }else{
            $(this).attr('class','left');
            auth.pwd =  true;
        }
    });

    $("#pwd").focus(function(){
        $(this).attr('class','left');
    });

    $("#chPWD").blur(function(){
        var chPWD = $(this).val();
        var pwd = $("#pwd").val();
        if(chPWD.length==0){
            $(this).attr('class','error');
            auth.PWD =  false;
        }else if(pwd!=chPWD){
            J.showToast("两次密码不匹配!");
            $(this).attr('class','error');
            auth.PWD =  false;
        }else{
            $(this).attr('class','left');
            auth.PWD =  true;
        }
    });

    $("#chPWD").focus(function(){
        $(this).attr('class','left');
    });

    $("#v_code").blur(function(){
        var code = $(this).val();
        if(code.length==0){
            auth.code = false;
        }else{
            auth.code = true;
        }
    });

    //注册
    $("#register").click(function(){
        $("#register").focus();
        var user = $("#user").val();
        var pwd = $("#pwd").val();
        var code = $("#v_code").val();
        var chPWD = $("#chPWD").val();
        if(auth.user == false||auth.pwd == false||auth.PWD == false||auth.code == false){
            if(!auth.user){
                J.showToast("请输入正确的帐号!");
                $("#user").attr('class','error');
                return false;
            }
            if(!auth.pwd){
                J.showToast("请输入6-18位数字加字母密码!");
                $("#pwd").attr('class','error');
                return false;
            }
            if(!auth.PWD){
                if(chPWD.length==0){
                    J.showToast("请输入6-18位数字加字母密码!");
                }else{
                    J.showToast("两次密码不匹配!");
                }
                $("#chPWD").attr('class','error');
                return false;
            }
            if(!auth.code){
                J.showToast("请输入验证码!");
                $("#v_code").css("border-bottom","1px solid #f00");
                return false;
            }
            return false;
        }
        $("#register").hide();

        var loc_url = url+'/user/register';
        if(is_wechat_user == 1){
            loc_url = loc_url + '?is_wechat_user=true'
        }
        $.ajax({
            url:loc_url,
            data:'user='+user+'&pwd='+pwd+'&chPWD='+chPWD+"&code="+code+"&t=reg",
            async: true,
            dataType: 'json',
            type:'post',
            success:function(data){
                if(data.status == 2){
                    J.showToast(data.msg);
                    window.location.href = data.data;
                }else if(data.status == 1){
                    window.location.href = url+"/community/home"
                }else{
                    $("#register").show();
                    J.showToast(data.msg);
                }
            }
        });
    });
});
/*更新验证码*/
function RefreshImage(){
    document.getElementById("imgcode").src =url+'/user/v_code?'+Math.random(1);
}