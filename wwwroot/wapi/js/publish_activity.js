
//判断是PC还是触屏
var sourceType = navigator.userAgent.match(/.*Mobile.*/) ? 1 : 2;

//选中chk
var sel_chk = function (obj) {
  if($(obj).prev(':checkbox').attr('checked')) {
    $(obj).prev(':checkbox').removeAttr("checked");
  }
  else {
    $(obj).prev(':checkbox').attr('checked', true);
  }
}

//下一步
var next = function () {
  var title = $('[name=title]:text').val();
  var num   = $('[name=num]:text').val();
  var desc  = $("textarea").val();
  if(title.length < 0 || ! title || title == null) {
    alert('标题不能为空'); 
    return false;
  }
  if(num < 0 || num == null || ! $.isNumeric(num)) {
    alert('数量不能为空'); return false;
  }else if(num > 1000) {
    alert('数量只能输入数字且不能大于1000');
    return false;
}
  if(desc.length < 10) {
    alert('请输入10字以上的内容'); return false;
  }
  $('#first').css('display', 'none').next().css('display', 'block');
}

//上一步
var prev = function () {
  $('#second').css('display', 'none').prev().css('display', 'block');
}

var custom_num   = 0;
var custom_value = '';
var custom_condetion = function (obj) {
  var val  = $(obj).prev().val();
    var vals = val.replace(/(\r)*\n/g,"").replace(/\s/g,"");
    if(vals == ''){
        alert('请输入自定义信息！');
        return false;
    }
  var html = '<span class="partake_term">' + 
              '<input type="checkbox" name="join_condetion[]" value="' + 
              val + '"  checked="checked" /><em>' + val + '</em>' +
              '</span>';
  if(custom_num < 8) {
    if(custom_value == val) {
      alert('不能重复添加');
    }
    else {
      $('#custom_condetion').before(html).next().find(':input').val('');
      custom_num++;
      custom_value = val;
    }
  }
  else {
    alert('最多只能添加8个');
  }
    $(obj).prev().val('');
}

var resourceBy = function (p, obj) {
  $.ajax({
    url: base_url + '/community/resources',
    async: false,
    data: {by: true, page: p, search_key: $(obj).prev().val()},
    type: 'POST',
    dataType: 'json',
    success: function(result) {
      if(result.code == 1) {
        if(result.success.length <= 0) {
          if($(obj).prev().val()) {
            $('#resourceText').html('暂无关键字:"<span style="color: red;">' + 
                $(obj).prev().val() + '</span>"相关资源');return;
          }
          else {
            $('#resource_by').remove();
          }
        }
        var html = '';
        var total = 1;
        html += '<dt>使用我的资源</dt>'+
                '<dd>' +
                '<input type="text" name="search" placeholder="输入资源关键字" class="left" />' +
                '<a href="javascript: void(0);" class="search-btn left" onclick="resourceBy(1, this);">' +
                '<i class="iconfont icon iconfont_15">&#983340;</i></a>' + 
                '</dd>' +
                '<div id="search_div"></div>'+
                '<div id="resourceText">';
        if(p > 1 || (p==1 && $('[name="search"]').val())){
            html = '';
        }
          html += '<div id="resource_by_div'+p+'">';
        $.each(result.success, function (key, value) {
          var ch = '';
          if(r_id == value.id_resource) {
            ch = 'checked=checked';
          }
            html += '<dd><span class="onlyread-title left">' +
            '<input type="checkbox" ' + ch + ' name="resourceBy[]" value="' + 
            value.id_resource + '"';
            if(id_resource_by.indexOf(value.id_resource) > -1){
                html += ' checked="checked"';
            }
            html += ' /><a href="javascript: void(0);" onclick="sel_chk(this);">' +
            value.resource_title + '</a></span><span class="right onlyread-num">' + 
            '<a href="javascript: void(0);" id="add_ac">+</a>' +
            '<input id="ac_num" type="text" readonly value="';

            if($('#id_resource_by'+value.id_resource).length > 0){
                html += $('#id_resource_by'+value.id_resource).attr('did');
            }else{
                html += '1';
            }
            html += '" class="right" num-total="' +
            value.num + '" /><a href="javascript: void(0);" id="sub_ac">-</a></span>' +
            '</dd>';
          if(key === 0)
            total = value.total;
        });
        html += '<ol>';
        if(total) {
          if(total > 1) {
            for (var i = 1; i <= total; i++) {
              var defaultOn = '';
              if(i == p)
                defaultOn = '<li class="on" ';
              else 
                defaultOn = '<li ';
              html +=  defaultOn + 'onclick="resourceBy(' + i + ');" style="cursor: pointer;"></li>';
            }
          }
        }
        
        var startX = 0, startY = 0;
        html += '</ol></div>';

          $('#resourceText').find('div').hide();//隐藏所有的子div
          if(p == 1 && $('[name="search"]').val()){
              html += '</div>';
              $('#resource_by').find('#search_div').html(html);
              $('#resourceText').hide();
          }else if(p == 1){
              if($('#resource_by').find('#search_div').css('display') != 'none'){
                  $('#resource_by').find('#search_div').html('');
                  $('#resourceText').show();
              }
              html += '</div>';
              var id_div = '#resource_by_div'+p;
              if($('#resourceText').find(id_div).length > 0){
                  $(id_div).show();
              }else{
                  $('#resource_by').html(html);
              }
          }else if(p > 1){
              var id_div = '#resource_by_div'+p;
              if($('#resourceText').find(id_div).length > 0){
                  $(id_div).show();
              }else{
                  $('#resourceText').append(html);
              }
          }

          $('#resource_by').find('#resourceText').bind({
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
            event.preventDefault();
            if(sourceType == 1) {
              var touch = event.originalEvent.touches[0] || 
                event.originalEvent.changedTouches[0];
            }
            else {
              var touch = event.originalEvent;
            }
            var x = Number(touch.pageX); //页面触点X坐标
            var y = Number(touch.pageY); //页面触点Y坐标

            if(x - startX != 0) { //判断滑动方向  左右滑动
              if(x - startX < -50) { //左滑动
                if(total >= 1) {
                  var v = (p + 1);
                  if(total >= v)
                    resourceBy(v);
                  //$(this).stop(true, false).animate({"left": -300}, 1000);
                }
              }
              else if(x - startX > 50) { //右滑动
                if(total >= 1) {
                  var v = (p - 1);
                  if(v >= 1) 
                    resourceBy(v);
                  //$(this).stop(true, false).animate({"left":0}, 1000);
                }
              }
            }
            else if (y - startY != 0) {} //上下滑动
          }
        });
      }
      else {
        $('#resource_by').remove();
      }
    },
    complete: function() {
      $('#resource_by .loading').remove();
    }
  });
}


$(function() {

  resourceBy(1);
  $.ajax({
    url: base_url + '/community/resources',
    async: false,
    data: {level: true},
    type: 'POST',
    dataType: 'json',
    success: function (result) {
      if(result.code == 1) {
        var html = '';
        html += '<dt>资源库</dt>';
        
        if(result.success.length > 0) {
          $.each(result.success, function (key, value) {
            html += '<dd><span class="onlyread-title left" rel="' +
                    value.id_resource + '"><input type="checkbox" name="resource[]" value="' +
                    value.id_resource + '" /><a href="javascript: void(0);" onclick="sel_chk(this);">' +
                    value.resource_title + '</a></span>' +
                    '<button class="right buy" rel="' + value.id_resource + '" >购买</button>' +
                    '<button class="right info" rel="' + value.id_resource + '">详情</button>' +
                    '</dd>';
          });
        }
        
        html += '<dd><a href="' + base_url + '/community/more_resource" class="more_resource right">查看更多资源</a></dd>';
        $('#resource_lev').html(html);
      }
    },
    beforeSend: function(XMLHttpRequest) {
      if($('#resource_by .loading').length > 0) {//存在
        $('#resource_by .loading').remove();
      }
      /*
      var html = '<div class="loading" style="margin:0 auto; text-align: center; width: 100px;">' +
            '<img src="/wapi/img/loading.gif" alt="loadding" /></div>';
        $('#resource_lev').after(html);
      */
    },
    complete: function () {
      $('#resource_lev .loading').remove();
    }
    /*
    error: function (XMLHttpRequest, textStatus, errorThrown) {
      alert(errorThrown);
    }
    */
  });
  
  $('#custom_condetion').click(function () {
    if($('#custom_condetion').find(':checkbox').attr('checked')) {
      $(this).parent().next('p').show();
    }
    else {
      $(this).parent().next('p').hide();
    }
  });
  
});

$('#add_ac').live('click', function() {
  var surplus = $(this).next().val() == 0 ? 1 : $(this).next('input').attr('num-total');
  var num = $(this).next().val();
  if(parseInt(num) < parseInt(surplus) || parseInt(surplus) == -1) {
    //赋值
    $(this).parent().prev().find(':checkbox').attr('checked', true);
    $(this).next().val(parseInt(num) + 1);

      //在报错
//    if($.trim($('.price').html()) != '免费') {
//      var price = $('.price').html().replace('￥', "");
//
//      if(num == 1)
//        price_one = price;
//
//      $('.price').html('￥' + acc(price, price_one));
//    }
    
  }
});

$('#sub_ac').live('click', function() {
  var num = $(this).prev().val();
  if(parseInt(num) > 1) {
    $(this).parent().prev().find(':checkbox').attr('checked', true);
    $(this).prev().val(parseInt(num) - 1);

      //在报错
//    if($.trim($('.price').html()) != '免费') {
//      var price = $('.price').html().replace('￥', "");
//
//      if(num == 1)
//        price_one = price;
//      $('.price').html('￥' + acc(price, price_one, '-'));
//    }
    
  }
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

$('button').live('click', function (e) {
  e.preventDefault();//禁止提交
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

$('#come_back').live('click', function (e) {
  $('#modal').hide();
  $('.webgame_msgWindow_mask').hide();
  $("html, body").unbind("touchmove");
});

//提交表单 
var sub_from = function () {
  var title = $('[name=title]:text').val();
  var num   = $('[name=num]:text').val();
  var desc  = $('textarea').val();
  
  if(title.length < 0 || ! title || title == null) {
    alert('标题不能为空');
    return false;
  }
  if(num < 0 || num == null || num > 1000) {
    alert('数量只能输入数字且不能大于1000');
    return false;
  }
  if( ! $.isNumeric(num)) {
    alert('数量只能为整数');
    return false;
  }
  if(desc.length < 10) {
    alert('请输入10个以上的详情内容');
    return false;
  }
  
  var resourceBy = [];
  $('input[name="resourceBy[]"]:checked').each(function() {
    resourceBy.push($(this).val() + '&@&' + $(this).parent().next().find('input:text').val());
  });

    var have_resource_by = [];
    $('input[name="id_resource_by[]"]').each(function() {
        have_resource_by.push($(this).val() + '&@&' + $(this).attr('did'));
    });

  var resource = [];
  $('input[name="resource[]"]:checked').each(function() {
    resource.push($(this).val() + '&@&' + $(this).parent().next().find('input:text').val());
  });
  
  if(resource.length > 0) {
    alert('有未支付资源，请先支付');
    return false;
  }
  
  var price = $('[name=price]:text').val();
  var date  = $('[name=date]:text').val();
  var date1 = $('[name=date1]:text').val();
  var addr  = $('[name=addr]:text').val();
  var tag   = $('[name=tag]:text').val();
    var is_edit   = $('#is_edit').val();//编辑或发布的状态
    var aid   = $('#aid').val();//活动id

  if(price) {
    var reg = /^\d+(\.\d{1,2})?$/;
    if( ! reg.test(price)) {
      alert('请正确输入价格');
      return false;
    }

      if(price < 0 || price == null || price > 1000) {
          alert('价格不能大于1000');
          return false;
      }

//    if( price >= 100000) {
//      alert('价格不能大于6位数');
//      return false;
//    }
  }
    
  if(date || date1) {
    if (date >= date1) {
      alert('活动结束时间必须大于开始时间');
      return false;
    }
  }

  var join_condetion = [];
  $('input[name="join_condetion[]"]:checked').each(function() {
    join_condetion.push($(this).val());
  });
  $.ajax({
    url: base_url + '/community/publish',
    async: false,
    data: {title: title, num: num, desc: desc, resourceBy: resourceBy, resource: resource,
            price: price, date: date, date1: date1, addr: addr, 
            tag: tag, join_condetion: join_condetion,is_edit:is_edit,have_resource_by:have_resource_by,aid:aid},
    type: 'POST',
    dataType: 'json',
    success: function (result) {
      if(result.code >= 1) {
        alert(result.msg);
        window.location.href = base_url + '/community/detail?aid=' + result.success;
      }
    }
  });
}


