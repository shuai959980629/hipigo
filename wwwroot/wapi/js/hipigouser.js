var rule = {
    /*[a-zA-Z][a-zA-Z0-9]{3,15}*/
    'userreg':/^[\da-zA-Z]{5,12}$/,
    /*/^([\d#$%^&*!?]+[a-zA-Z#$%^&*!?]+|[a-zA-Z#$%^&*!?]+[\d#$%^&*!?]+){1,}$*/
    'pwdreg':/^([\d#$%^&*!?]+[a-zA-Z#$%^&*!?]+|[a-zA-Z#$%^&*!?]+[\d#$%^&*!?]+)$/,
    /*/^13[0-9]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}$/*/
    //
    ///^1[3|4|5|7|8][0-9]{8}$/
    'phonereg':/^(13[0-9]|14[0-9]|15[0-9]|18[0-9]|17[0-9]|18[0-9])\d{8}$/
};
/*隐藏弹出层*/
function hidePop(){
    $("#pop").addClass('hidden');
    $(".login_input").removeAttr('readonly');
}
/*显示提示框*/
function showPop(msg){
    if(timer){
        clearTimeout(timer);
    }
    $("#tip").html(msg);
    $("#pop").removeClass('hidden');
    //$(".login_input").attr('readonly','true');
    var timer = setTimeout(function(){
        $("#pop").addClass('hidden');
        //$(".login_input").removeAttr('readonly');
    },2000);
}
