function chekcpwd(_obj){
    var pwd = $(_obj).val();
    var id = $(_obj).attr('id');
    if(id=='newPWDC'){
        var val_1 = $("#newPWD").val();
        var val_2 = $("#newPWDC").val();
        if(val_1!=val_2){
            J.showToast('两次密码不一致!');
            $(_obj).attr('class','error');
            return false;
        }else{
            $(_obj).attr('class','left');
            return true;
        }
    }
    if(pwd.length==0){
        $(_obj).attr('class','error');
        return false;
    }else if(!rule.pwdreg.test(pwd)||pwd.length>18 || pwd.length<6){
        J.showToast('请输入6-18位数字加字母!');
        $(_obj).attr('class','error');
        return false;
    }else{
        $(_obj).attr('class','left');
        return true;
    }
}

function restyle(_obj){
    $(_obj).attr('class','left');
}

function ensure(){
    if(!chekcpwd($("#newPWD"))|| !chekcpwd($("#newPWDC"))){
        return false;
    }
    var newpwd = $("#newPWD").val();
    var newpw = $("#newPWDC").val();
    $("#ensure").hide();
    $.ajax({
        url:url+'/user/edit',
        data:'&newpwd='+newpwd+'&newpw='+newpw,
        async: true,
        dataType: 'json',
        type:'post',
        success:function(data){
            if(data.status){
                window.location.href=url+"/community/home"
            }else{
                $("#ensure").show();
                J.showToast(data.msg);
            }
        }
    });
}