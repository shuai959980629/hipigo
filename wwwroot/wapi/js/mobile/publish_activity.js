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
                html += '<dt>资源库<a href="' + base_url + '/resource/index" class="more_resource haveback right">查看更多资源</a></dt>';

                if(result.success.length > 0) {
                    $.each(result.success, function (key, value) {
                        html += '<dd><span class="onlyread-title left" rel="' +
                            value.id_resource + '"><input type="checkbox" name="resource[]" value="' +
                            value.id_resource + '" /><a href="javascript: void(0);" class="left haveback" onclick="sel_chk(this);">' +
                            value.resource_title + '</a></span><span class="right onlyread-num">' +
                            '<button class="right buy" onclick="popupBuy('+value.id_resource+',\'act_resource_by\')" rel="' + value.id_resource + '" >购买</button>' +
                            '<button class="right info" onclick="resouce_info(\''+base_url+'/resource/info?rid='+ value.id_resource +'\')" rel="' + value.id_resource + '">详情</button>' +
                            '</span></dd>';
                    });
                }
                $('#resource_lev').html(html);
            }
        },
        beforeSend: function(XMLHttpRequest) {
        },
        complete: function () {
        }

    });

    $('#custom_condetion').click(function () {
        if($('#custom_condetion').find(':checkbox').attr('checked')) {
            $(this).parent().next('p').show();
        }
        else {
            $(this).parent().next('p').hide();
        }
    });

    //表情弹框
    $('#face_').click(function () {
        $('#face').show();
        J.confirm('', $('#face').html(),
            function(){
            //确定
        },function(){
            var desc = $('#desc').val();
            var a = desc.replace(/\[(.*?)\]/g,"");
            $('#desc').val(a);
            //取消
        });
    });


//选择表情
    $('.smile_select').live('click',function(){
        var value = $('[name=desc]').val();
        var name = $(this).attr('data-smile-name');
        $('[name=desc]').val(value+name);
    });

    //cookie信息读取和制空
    if($.cookie('act_title') != '' && $.cookie('act_title') != 'null'){
        $('[name="title"]').val($.cookie('act_title'));
        $('[name="num"]').val($.cookie('act_num'));
        $('[name="desc"]').text($.cookie('act_desc'));
        $('[name="img_url"]').val($.cookie('act_img_url'));
        $('[name="price"]').val($.cookie('act_price'));
        $('[name="date"]').val($.cookie('act_date'));
        $('[name="date1"]').val($.cookie('act_date1'));
        $('[name="addr"]').val($.cookie('act_addr'));
        $('[name="tag"]').val($.cookie('act_tag'));

        if($.cookie('act_img_url')){
            var arr = $.cookie('act_img_url').split(',');
            for(var i in arr){
                var b = arr[i].split('');
                var str = '/attachment/business/community/';
                for(var s in b){
                    if(s == 0){
                        str += b[s];
                    }else if(s>0 && s < 4){
                        str += '/'+b[s];
                    }
                }
                str += '/'+arr[i];
                $('#show_attch').append('<span class="relative"><img class="datail_add_pic left" src="' + str + '" alt="card" /><a onclick="delete_attach(this,\'' + arr[i] + '\')" class="delete"><i class="icon cancel-circle"></i></a></span>');
            }
        }
        $.cookie('act_title',null);
        $.cookie('act_num',null);
        $.cookie('act_desc',null);
        $.cookie('act_img_url',null);
        $.cookie('act_price',null);
        $.cookie('act_date',null);
        $.cookie('act_date1',null);
        $.cookie('act_addr',null);
        $.cookie('act_tag',null);
    }

    //绑定时间控件
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
        startYear: date.getFullYear(),
        endYear: date.getFullYear() + 10
    }
    $('#date').scroller(options);
    $('#date1').scroller(options);

    $("#upload_button").live('change', function(){
        $('#show_attch').append('<span class="relative"><img class="datail_loading left" src="/wapi/img/mobile/loading.gif" alt="card" /></span>');
        $("#image_upload").submit();
    });
    
  //价格如果一旦被设置则不可修改
  if($('[name="is_edit"]:input').val() == 'edit') {
    $('[name="price"]:input').attr('readonly', 'readonly');
    $('[name="price"]:input').parent()
        .prev().append('<span style="color: gray; margin-left: 10px;">不可修改价格</span>');
  }
//var x = 0;
//    $('[contenteditable="true\"]').click(function () {
//        $(this).focus();
//        if(x == 0){
//            $(this).text('');
//            x = 1;
//        }
//    });

    //选中chk
    var sel_chk = function (obj) {
        if($(obj).prev(':checkbox').attr('checked')) {
            $(obj).prev(':checkbox').removeAttr("checked");
        } else {
            $(obj).prev(':checkbox').attr('checked', true);
        }
    }

});

function resouce_info(url){
    $.cookie('act_title',$('[name="title"]').val());
    $.cookie('act_num',$('[name="num"]').val());
    // $.cookie('act_desc',$('[name="desc"]').text());
    $.cookie('act_desc', $('[name="desc"]').val());
    $.cookie('act_img_url',$('[name="img_url"]').val());
    $.cookie('act_price',$('[name="price"]').val());
    $.cookie('act_date',$('[name="date"]').val());
    $.cookie('act_date1',$('[name="date1"]').val());
    $.cookie('act_addr',$('[name="addr"]').val());
    $.cookie('act_tag',$('[name="tag"]').val());
    window.location.href=url;
}


function finishupload(data){
    if (data.status) {
        var url=data.url.replace('.jpg', '-small.jpg');
        var file_name=data.file_name.replace('.jpg', '-small.jpg');
        if($('[name=img_url]:hidden').val() == ''){
            $('[name=img_url]:hidden').val(data.file_name);
        }else{
            $('[name=img_url]:hidden').val($('[name=img_url]:hidden').val()+','+data.file_name);
        }
        $('#show_attch').children('span:last-child').remove();
        $('#show_attch').append('<span class="relative"><img class="datail_add_pic left" src="' + data.url + '" alt="card" /><a onclick="delete_attach(this,\'' + data.file_name + '\')" class="delete"><i class="icon cancel-circle"></i></a></span>');
        J.showToast("上传成功了哦！");
    }
}

//下一步
var next = function () {
  var title = $('[name=title]:text').val();
  var num   = $('[name=num]:text').val();
  var desc  = $("textarea").val();
  if(title.length < 0 || ! title || title == null) {
      J.showToast('标题不能为空！');
    return false;
  }
//  if(num < 0 || num == null || ! $.isNumeric(num)) {
//    alert('数量不能为空'); return false;
//  }else
  if(num > 1000) {
      J.showToast('数量只能输入数字且不能大于1000！');
    return false;
}
  if(desc.length < 10) {
      J.showToast('请输入10字以上的内容！');
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
            J.showToast('请正确输入价格！');
            return false;
        }

        if(price < 0 || price == null || price > 1000) {
            J.showToast('价格不能大于1000！');
            return false;
        }
    }

    if(date || date1) {
        if (date >= date1) {
            J.showToast('活动结束时间必须大于开始时间！');
            return false;
        }
    }

  $('#first').css('display', 'none').next().css('display', 'block');
    $('header:first').hide();
    $('header:last').show();
}

//上一步
var prev = function () {
  $('#second').css('display', 'none').prev().css('display', 'block');
    $('header:first').show();
    $('header:last').hide();
}

var custom_num   = 0;
var custom_value = '';
var custom_condetion = function (obj) {
  var val  = $(obj).prev().val();
    var vals = val.replace(/(\r)*\n/g,"").replace(/\s/g,"");
    if(vals == ''){
        J.showToast('请输入自定义信息！');
        return false;
    }
  var html = '<span class="partake_term">' + 
              '<input type="checkbox" name="join_condetion[]" value="' + 
              val + '"  checked="checked" /><em>' + val + '</em>' +
              '</span>';
  if(custom_num < 8) {
    if(custom_value == val) {
        J.showToast('不能重复添加！');
    }
    else {
      $('#custom_condetion').before(html).next().find(':input').val('');
      custom_num++;
      custom_value = val;
    }
  }
  else {
      J.showToast('最多只能添加8个！');
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
                        $('#anim').html('暂无关键字:"<span style="color: red;">' +
                            $(obj).prev().val() + '</span>"相关资源');return;
                    }
                    else {
                        $('#resource_by').remove();
                    }
                }
                var html = '';
                var total = 1;
                html += '<dt>使用我的资源</dt>'+
                    '<dd><div class="search_input relative">' +
                    '<input type="text" placeholder="输入关键字搜索" class="left" name="search">' +
                    '<a class="searchbutton right" onclick="resourceBy(1, this);"><i class="icon search nofloat"></i></a>' +
                    '</div></dd><div id="search_div"></div><div id="anim">';

                $.each(result.success, function (key, value) {
                    var ch = '';
                    if(r_id == value.id_resource) {
                        ch = 'checked=checked';
                    }
                    html += '<dd><span class="onlyread-title left"><input type="checkbox"' + ch + ' name="resourceBy[]" value="' + value.id_resource +'"';
                    if(id_resource_by.indexOf(value.id_resource) > -1){
                        html += ' checked="checked"';
                    }
                    html += '/><a onclick="sel_chk(this);" class="left haveback">'+value.resource_title+'</a></span>'+
                        '<span class="right onlyread-num"><a href="javascript: void(0);" id="add_ac">+</a><input id="ac_num" type="text" readonly="" value="' ;
                    if($('#id_resource_by'+value.id_resource).length > 0){
                        html += $('#id_resource_by'+value.id_resource).attr('did');
                    }else{
                        html += '1';
                    }
                    html += '" class="right" num-total="' + value.num + '">' +
                        '<a href="javascript: void(0);" id="sub_ac" class="readonly">-</a></span>'+
                        '</dd>' ;
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
                html += '</ol></div>';
                $('#resource_by').html('');
                $('#resource_by').html(html);
                $('[name="search"]').val($(obj).prev().val());
                scoll_div('resource_by',p,total);//滚动的时候的加载
            }
            else {
                $('#resource_by').remove();
            }
        },
        complete: function() {
        }
    });
}

function scoll_div(name,p,total){
    //滑动事件
    $('#'+name).unbind();
    $('#'+name).bind({'touchstart mousedown' : function(event) {
        var touch = event.originalEvent;
        var x = Number(touch.pageX); //页面触点X坐标
        var y = Number(touch.pageY); //页面触点Y坐标

        //记录触点初始位置
        startX = x;
        startY = y;
      },
      'touchend touchcancel mouseup' : function(event) {
        var touch = event.originalEvent;
        var x = Number(touch.pageX); //页面触点X坐标
        var y = Number(touch.pageY); //页面触点Y坐标

        if(x - startX != 0) { //判断滑动方向  左右滑动
          if(x - startX < -50) { //左滑动
            if(total >= 1) {
              var v = (p + 1);
              if(total >= v)
                  J.anim('#anim','scaleOut',function(){
                      resourceBy(v);
                  });
            }
          }
          else if(x - startX > 50) { //右滑动
            if(total >= 1) {
              var v = (p - 1);
              if(v >= 1)
                  J.anim('#anim','scaleOut',function(){
                      resourceBy(v);
                  });
            }
          }
        }
        else if (y - startY != 0) {} //上下滑动
      }
    });
}

$('#add_ac').live('click', function() {
  var surplus = $(this).next().val() == 0 ? 1 : $(this).next('input').attr('num-total');
  var num = $(this).next().val();
  if(parseInt(num) < parseInt(surplus) || parseInt(surplus) == -1) {
    //赋值
    $(this).parent().prev().find(':checkbox').attr('checked', true);
    $(this).next().val(parseInt(num) + 1);
  }
});

$('#sub_ac').live('click', function() {
  var num = $(this).prev().val();
  if(parseInt(num) > 1) {
    $(this).parent().prev().find(':checkbox').attr('checked', true);
    $(this).prev().val(parseInt(num) - 1);
  }
});

//提交表单 
var sub_from = function () {
  var title = $('[name=title]:text').val();
  var num   = $('[name=num]:text').val();
  var desc  = $('textarea').val();
  
  if(title.length < 0 || ! title || title == null) {
      J.showToast('标题不能为空！');
    return false;
  }
  if(num < 0 || num == null || num > 1000) {
      J.showToast('数量只能输入数字且不能大于1000！');
    return false;
  }

    if(num.length > 0)
  if( ! $.isNumeric(num)) {
      J.showToast('数量只能为整数！');
    return false;
  }
  if(desc.length < 10) {
      J.showToast('请输入10个以上的详情内容！');
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
      J.showToast('有未支付资源，请先支付！');
    return false;
  }
  
  var price = $('[name=price]:text').val();
  var date  = $('[name=date]:text').val();
  var date1 = $('[name=date1]:text').val();
  var addr  = $('[name=addr]:text').val();
  var tag   = $('[name=tag]:text').val();
    var is_edit   = $('#is_edit').val();//编辑或发布的状态
    var aid   = $('#aid').val();//活动id
    var img_url   = $('[name="img_url"]').val();//附件

  if(price) {
    var reg = /^\d+(\.\d{1,2})?$/;
    if( ! reg.test(price)) {
        J.showToast('请正确输入价格！');
      return false;
    }

      if(price < 0 || price == null || price > 1000) {
          J.showToast('价格不能大于1000！');
          return false;
      }
  }
    
  if(date || date1) {
    if (date >= date1) {
        J.showToast('活动结束时间必须大于开始时间！');
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
            price: price, date: date, date1: date1, addr: addr, img_url:img_url,delete_id:$('[name=delete_id]').val(),
            tag: tag, join_condetion: join_condetion,is_edit:is_edit,have_resource_by:have_resource_by,aid:aid},
    type: 'POST',
    dataType: 'json',
    success: function (result) {
      if(result.code >= 1) {
          J.showToast(result.msg);
          var location_url = base_url + '/community/detail?aid=' + result.success;
          if(result.code == 2){
              location_url += '&type=new';
          }
        window.location.href = location_url;
      }
    }
  });
}

var handleTips = function () {
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


//删除附件图片
function delete_attach(obj,src){
    $(obj).parent().remove();
    var srcs = $('[name="img_url"]').val();

    var imgsrcArray = srcs.split(",");
    for(var i=0;i<imgsrcArray.length;i++){
        if(imgsrcArray[i] == src){
            imgsrcArray.splice(i,1);
            $('[name=img_url]').val(imgsrcArray);
        }
    }
}

//删除附件id
function delete_attach_(obj,id){
    $(obj).parent().remove();
    if($('[name=delete_id]').val() == ''){
        $('[name=delete_id]').val(id);
    }else{
        $('[name=delete_id]').val($('[name=delete_id]').val() + ','+id);
    }
}
