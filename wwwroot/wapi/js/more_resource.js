
//判断是PC还是触屏
var sourceType = navigator.userAgent.match(/.*Mobile.*/) ? 1 : 2;

var page = 1;
var total = 0;
var price_one = 0;

$(function () {
  search();
  
  $('.searchbtn').click(function () {
    $('.resourceli ul').html('');
    search();
    $('.close').show();
  })
  .keypress(function (e) {
    if(e.which == 13)
      if($('#search').val() != '')
        search();
  });
  
  //滑动事件
  $('.resourceli').bind({
    'touchstart mousedown' : function(event) {
      if(sourceType == 1) {
        var touch = event.originalEvent.touches[0] || 
          event.originalEvent.changedTouches[0];
      }
      else {
        var touch = event.originalEvent;
      }
      var x = Number(touch.pageX);//Number(touch.pageX); //页面触点X坐标
      var y = Number(touch.pageY);//Number(touch.pageY); //页面触点Y坐标
      
      //记录触点初始位置
      startX = x;
      startY = y;
    },
    'touchmove mousemove' : function(event) {//mousemove PC移动在这里不做处理，用mouseup处理
      //event.preventDefault();
    },
    'touchend touchcancel mouseup' : function(event) {
      if(sourceType == 1) {
        var touch = event.originalEvent.touches[0] || 
          event.originalEvent.changedTouches[0];
      }
      else {
        var touch = event.originalEvent;
      }
      var x = Number(touch.pageX); //页面触点X坐标
      var y = Number(touch.pageY); //页面触点Y坐标
      if (y - startY < -50) { //向上滑动
        if(($(document).height() - startY) < $(window).height() ) {
          if( $('.load_more').length) {
          }
          else 
            $(this).after('<div class="load_more" style="text-align: center; height: 40px;">点击加载<div>');
          
          if(page == total) {
            $('.load_more').html('无更多数据');
          }
          else {
            $('.load_more').click(function () {//加载数据
              if(page < total) {
                page++;
                search();
              }
              
              $('.load_more').remove();
            });
          }
        }
      }
      else if(y - startY > 50) { //向下滑动
        
      }
    }
  });
  
});

$('.buy').live('click', function (e) {
  $.post(base_url + '/community/info', {id: $(this).attr('rel'), source: 'buy'}, function (result) {
    if(result) {
      $('#modal').html(result).show();//.css('overflow-y', 'hidden')
      $('.webgame_msgWindow_mask').show();
      handleTips();
    }
  }, 'text');
});

$('.info').live('click', function (e) {
  $.post(base_url + '/community/info', {id: $(this).attr('rel'), source: 'info'}, function (result) {
    if(result) {
      $('#modal').html(result).show();
      $('.webgame_msgWindow_mask').show();
      handleTips();
    }
  }, 'text');
});

$('#buy').live('click', function (e) {
  $.post(
    base_url + '/community/pay', 
    {rid: $(this).attr('rel'), num: $('#num').val()}, 
    function (result) {
      if(result.code != 1)
        alert(result.msg);
      
      window.location.href = result.success;
    }, 'json');
});

$('#add_ac').live('click', function() {
    var num_total = $(this).next('input').attr('num-total')==-1?10000:$(this).next('input').attr('num-total');
  var surplus = $(this).next().val() <= 0 ? 1 : num_total;
  var num = $(this).next().val();
  if(parseInt(num) < parseInt(surplus)) {
    //赋值
    $(this).next().val(parseInt(num) + 1);
    
    if($.trim($('.price').html()) != '免费') {
      var price = $('.price').html().replace('￥', "");
      
      if(num == 1)
        price_one = price;
      $('.price').html('￥' + acc(price, price_one));
    }
  }
});
  
$('#sub_ac').live('click', function() {
  var num = $(this).prev().val();
  if(parseInt(num) > 1) {
    $(this).prev().val(parseInt(num) - 1);
    
    if($.trim($('.price').html()) != '免费') {
      var price = $('.price').html().replace('￥', "");
      
      if(num == 1)
        price_one = price;
      $('.price').html('￥' + acc(price, price_one, '-'));
    }
  }
});

$('#come_back').live('click', function (e) {
  e.preventDefault();
  $('#modal').hide();
  $('.webgame_msgWindow_mask').hide();
  $("html, body").unbind("touchmove");
});

var refreshDate = function (result) {
  var html = '';
  $.each(result, function (key, value) {
    html += '<li>' +
            '<span>' + 
            '<input type="checkbox" name="resource[]" />' + 
            '<a href="javascript: void(0);" onclick="sel_chk(this);">' +
            value.resource_title + '</a></span>' +
            '<button class="right buy" rel="' + value.id_resource + '" >购买</button>' +
            '<button class="right info" rel="' + value.id_resource + '">详情</button>' +
            '</li>';
  });
  
  $('.resourceli ul').append(html);
  $('.nosearch').hide();
}

var search = function () {
  $.ajax({
    url: base_url + '/community/resources',
    type: 'POST',
    dataType: 'json',
    async: false,
    data: {page: page, search_key: $('#search').val()},
    success: function (result) {
      if(result.code == 1) { 
        if(result.success.length <= 0) { 
          if($('#search').val() != '') {
            var html = '<i class="iconfont icon iconfont_11">&#983715;</i>' + 
                      '<p>额~~暂无<span style="color: red;">' + 
                      $('#search').val() + '</span>相关结果，要不换个搜索词试试呢!</p>';
            
            $('.resourceli ul').html('');
            $('.nosearch').html(html).show();
            return false;
          }
        }
          $('#search').attr('placeholder',$('#search').val());
          $('#search').val('');
        //刷新数据
        refreshDate(result.success);
        total = result.options.total;
      }
    },
    beforeSend: function (XMLHttpRequest) {
      var html = '<div class="loading" style="margin:0 auto; text-align: center; width: 100px;">' +
            '<img src="/wapi/img/loading.gif" alt="加载" /></div>';
      $('.resourceli').after(html);
    },
    complete: function () {
      $('.loading').remove();
    }
  });
}

//选中chk
var sel_chk = function (obj) {
  if($(obj).prev(':checkbox').attr('checked')) {
    $(obj).prev(':checkbox').removeAttr("checked");
  }
  else {
    $(obj).prev(':checkbox').attr('checked', true);
  }
}


var clearInput = function (obj) {
  $('#search').val('');
  $('.resourceli ul').html('');
  $(obj).hide();
  
  search();
}









