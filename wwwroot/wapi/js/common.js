//判断是PC还是触屏
var sourceType = navigator.userAgent.match(/.*Mobile.*/) ? 1 : 2;

(function($) {
  $.extend({
    myTime: {
      /**
        * 当前时间戳
        * @return <int>               unix时间戳(秒)     
        */
      CurTime: function(){
              return Date.parse(new Date())/1000;
      },
      /**
       * 日期 转换为 Unix时间戳 
       * @param <string> 2014-01-01 20:20:20   日期格式
       * @return <int>               unix时间戳(秒)
       */
      DateToUnix: function(string) {
        var f = string.split(' ', 2);
        var d = (f[0] ? f[0] : '').split('-', 3);
        var t = (f[1] ? f[1] : '').split(':', 3);
        return (new Date(
                  parseInt(d[0], 10) || null,
                  (parseInt(d[1], 10) || 1) - 1,
                  parseInt(d[2], 10) || null,
                  parseInt(t[0], 10) || null,
                  parseInt(t[1], 10) || null,
                  parseInt(t[2], 10) || null
                  )).getTime() / 1000;
      },
      /**                             
        * 时间戳转换日期                             
        * @param <int> unixTime       待时间戳(秒)                             
        * @param <bool> isFull       返回完整时间(Y-m-d 或者 Y-m-d H:i:s)                             
        * @param <int>   timeZone     时区                             
        */
      UnixToDate: function(unixTime, isFull, timeZone) {
      
        if (typeof (timeZone) == 'number')
        {
          unixTime = parseInt(unixTime) + parseInt(timeZone) * 60 * 60;
        }
        
        var time = new Date(unixTime * 1000);
        var ymdhis = "";
        
        ymdhis += time.getUTCFullYear() + "-";
        ymdhis += (time.getUTCMonth()+1) + "-";
        ymdhis += time.getUTCDate();
        if (isFull === true)
        {
          ymdhis += " " + time.getUTCHours() + ":";
          ymdhis += time.getUTCMinutes() + ":";
          ymdhis += time.getUTCSeconds();
        }
        return ymdhis;
      }
    }
  });
})(jQuery);

//浮点数运算
var acc = function(arg1, arg2, sign) {
  var r1, r2, m, n;
  switch (sign) {
    case '-':
      try {
        r1 = arg1.toString().split(".")[1].length;
      }
      catch(e) {
        r1 = 0;
      }
      
      try {
        r2 = arg2.toString().split(".")[1].length;
      }
      catch(e) { 
        r2 = 0;
      }
      m = Math.pow(10, Math.max(r1, r2));
      
      //动态控制精度长度
      //n = (r1 >= r2) ? r1 : r2;
      return ((arg1 * m - arg2 * m) / m).toFixed(2);
    case 'x':
    case '/':
    default: //默认+
      try {
        r1 = arg1.toString().split(".")[1].length;
      }
      catch(e) {
        r1 = 0;
      }
      
      try {
        r2 = arg2.toString().split(".")[1].length;
      }
      catch(e) {
        r2c = 0;
      }
      m = Math.pow(10, Math.max(r1, r2));
      return ((arg1 * m + arg2 * m) / m).toFixed(2);
  }
}


//窗口改变大小时重新计算弹出层的高度
var resizeTimer = null;
$(window).resize(function() {
    if(resizeTimer)
        clearTimeout(resizeTimer);
    resizeTimer = setTimeout("handleTips()", 0);

});


var handleTips = function () {
    //弹出层不滑动body
    //if( ! $("#modal").is(":visible"))
    //  return false;

    $("#modal").unbind("touchmove");
    $('body').css('overflow-y', 'scroll');

    //判断当前窗口的高 处理在中间显示
    var re = $('#modal').height() - $(window).height();
    if(re > 0) { //当弹出层的高度窗口的高度
        $('#modal').css('top', $(document).scrollTop());
    }
    else {
        //计算弹出层的top值
        //当前窗口/2 - 弹出层/2 + 滚动条的高度
        $('#modal').css('top', ($(window).height() / 2) - ($('#modal').height() / 2) + $(document).scrollTop());
        if(sourceType == 1)
            $('body').bind('touchmove');//, function (event) { event.preventDefault();});
        else
            $('body').css('overflow-y', 'hidden');
    }
}


/**
 * 只能输入金额格式
 * @param o
 */
function validatePices(o) {
    var pice = o.value.replace(/^([1-9][0-9]{0,3}|[0-9])((\.[0-9]{0,2}){0,1})$/, '');
    if (pice.length > 0) {
        o.value = o.value.substring(0, o.value.length - 1);
        validatePices(o);
    }
}/**
 * 只能输入数字
 * @param o
 */
function validateInt(o) {
    var pice = o.value.replace(/^[1-9][0-9]{0,10}|[0-9]$/, '');
    if (pice.length > 0) {
        o.value = o.value.substring(0, o.value.length - 1);
        validateInt(o);
    }
}


