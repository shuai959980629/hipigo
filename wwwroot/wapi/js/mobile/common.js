document.addEventListener('deviceready', onDeviceReady, false);
function onDeviceReady(){
    navigator.splashscreen.hide();
    //注册后退按钮
    document.addEventListener("backbutton", function (e) {
//        if(J.hasMenuOpen){
//            J.Menu.hide();
//        }else if(J.hasPopupOpen){
//            J.closePopup();
//        }else{
            var sectionId = $('section.active').attr('id');
            if(sectionId == 'index_section'){
                J.confirm('提示','是否退出程序？',function(){
                    navigator.app.exitApp();
                });
            }else{
                window.history.go(-1);
            }

//        }
    }, false);
}

var rule = {
    'userreg':/^[\da-zA-Z]{5,12}$/,
    'pwdreg':/^([\d#$%^&*!?]+[a-zA-Z#$%^&*!?]+|[a-zA-Z#$%^&*!?]+[\d#$%^&*!?]+)$/,
    'phonereg':/^(13[0-9]|14[0-9]|15[0-9]|18[0-9]|17[0-9]|18[0-9])\d{8}$/
};

/**
 * 文本域光标操作（选、添、删、取） Jquery扩展
 * @author Jamai
 */
(function($) {
  $.fn.extend({
    /*
     * 获取光标所在位置
     */
    iGetFieldPos:function(){
      var field=this.get(0);
      if(document.selection){
          //IE
          $(this).focus();
          var sel=document.selection;
          var range=sel.createRange();
          var dupRange=range.duplicate();
          dupRange.moveToElementText(field);
          dupRange.setEndPoint('EndToEnd',range);
          field.selectionStart=dupRange.text.length-range.text.length;
          field.selectionEnd=field.selectionStart+ range.text.length;
      }
      return field.selectionStart;
    },
    /*
     * 选中指定位置内字符 || 设置光标位置
     * --- 从start起选中(含第start个)，到第end结束（不含第end个）
     * --- 若不输入end值，即为设置光标的位置（第start字符后）
     */
    iSelectField:function(start,end){
      var field=this.get(0);
      //end未定义，则为设置光标位置
      if(arguments[1]==undefined){
          end=start;
      }
      if(document.selection){
          //IE
          var range = field.createTextRange();
          range.moveEnd('character',-$(this).val().length);
          range.moveEnd('character',end);
          range.moveStart('character',start);
          range.select();
      }else{
          //非IE
          field.setSelectionRange(start,end);
          $(this).focus();
      }
    },
    /*
     * 选中指定字符串
     */
    iSelectStr:function(str){
      var field=this.get(0);
      var i=$(this).val().indexOf(str);
      i != -1 ? $(this).iSelectField(i,i+str.length) : false;
    },
    /*
     * 在光标之后插入字符串
     */
    iAddField:function(str){
      var field=this.get(0);
      var v=$(this).val();
      var len=$(this).val().length;
      if(document.selection){
          //IE
          $(this).focus()
          document.selection.createRange().text=str;
      }else{
          //非IE
          var selPos=field.selectionStart;
          $(this).val($(this).val().slice(0,field.selectionStart)+str+$(this).val().slice(field.selectionStart,len));
          this.iSelectField(selPos+str.length);
      };
    },
    /*
     * 删除光标前面(-)或者后面(+)的n个字符
     */
    iDelField:function(n){
      var field=this.get(0);
      var pos=$(this).iGetFieldPos();
      var v=$(this).val();
      //大于0则删除后面，小于0则删除前面
      $(this).val(n>0 ? v.slice(0,pos-n)+v.slice(pos) : v.slice(0,pos)+v.slice(pos-n));
      $(this).iSelectField(pos-(n<0 ? 0 : n));
    }
  });
})(jQuery);

/*显示提示框*/
function showPop(msg){
    if(timer){
        clearTimeout(timer);
    }
    $(".instant_tip").show();
    $(".instant_tip").html(msg);
    var timer = setTimeout(function(){
      /*隐藏弹出层*/
      $(".instant_tip").hide();
    },2000);
}

var popupBuy = function (val, source) {
  $.ajax({
    url: base_url + '/common/popup',//+ source,
    async: false,
    data: {rid: val, source: source},
    type: 'get',
    dataType: 'text',
    success: function (html) {
      J.confirm('', html,
        function () {
		    if(source=='activity') {
				
			  var phone  = $('#phone').val();
			  var ac_num = $('#ac_num').val();
			  ac_num = ac_num == undefined ? 1 : ac_num;
			  var join_price_type = $('#join_price_type').val();
			  join_price_type = join_price_type? join_price_type : 0;

			  if(phone==""){
				 J.showToast("请输入手机号码");
				 return;
			  }

			  $.get(base_url + '/community/att_activity', {join_price_type: join_price_type,aid: aid, ac_num: ac_num, phone: phone,oid:oid},
				function (result) {
					messageResult(result);
				}, 'json');
				
				J.Scroll('#active_details_container');

				return;
          }		  

		   if(source=='wallet') {
			  
			  var card_account  = $('#card_account').val();
			  var price = $('#price').val();
			  var type = 'alipay';

			  $.get(base_url + '/user_activity/avail', {card_account: card_account, price: price,type:type},
				function (result) {
					J.showToast(result.msg);
				}, 'json');				

				return;
          }		 

		  if(source=='logout') {								
				window.location.href = base_url + '/user_activity/exit_login';
				return;
          }		

          if($('#jingle_popup').find('#ac_num').val()) {
              var data = {rid: $('#jingle_popup').find('#ac_num').attr('rel'), num: $('#jingle_popup').find('#ac_num').val(), source: source};
              if(source == 'act_resource_by'){
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
              }
          $.post(
            base_url + '/common/pay',
            data,
            function (result) {
              if(result.code != 1) {
                  J.showToast(result.msg);
                  if(source == 'act_resource_by'){
                    resourceBy(1);
                  }
              }
              else
                window.location.href = result.success;
            }, 'json');
          }
        }, 
        function () {});
    }
  });
}


/**
 * 弹出层带关闭按钮
 * @author Jamai
 * @version 2.1
 **/
 
var popupByConfirm = function (url, source) {
  $('.confirm').live('click', function() {
    //先隐藏弹出层在执行操作
    popupByCancel();
    
    $.ajax({
      url: url,
      async: false,
      data: {rid: $('#ac_num').attr('rel'), num: $('#ac_num').val(), source: source},
      type: 'post',
      dataType: 'json',
      success: function (result) {
        if(result.code != 1)
          alert(result.msg);
        window.location.href = result.success;
      }
    });
  });
}

var popupByCancel = function () {
  $('.cancel').live('click', function() {
    $('.jingle_popup').hide();
    $('.jingle_popup_mask').hide();
  });
}

/**
 * 增加1个数量 更换UI
 * @author Jamai
 **/
var plus = function (obj) {
  var surplus = parseInt($(obj).next('input').val()) == 0 ? 
        1 : parseInt($(obj).next('input').attr('num-total'));
  
  var num = parseInt($(obj).next('input').val());
  
  if(num >= 1) {
    $(obj).next('input').next().css('color', '#49d69c')
        .css('border', '1px solid #49d69c');
  }
  else {
    $(obj).next('input').next().css('color', '#b4c4bd')
        .css('border', '1px solid #b4c4bd');
  }
  
  if(num < surplus || surplus == -1) {
    //赋值
    $(obj).parent().prev().find(':checkbox').attr('checked', true);
    $(obj).next().val(num + 1);
  }
  
  if((num + 1) == surplus){
    $(obj).css('color', '#b4c4bd')
        .css('border', '1px solid #b4c4bd');
  }
}

/**
 * 减去1个数量  更换UI
 * @author Jamai
 **/
var subtract = function (obj) {
  var num = parseInt($(obj).prev().val());
  
  if((num - 1) <= 1) {
    $(obj).css('color', '#b4c4bd')
        .css('border', '1px solid #b4c4bd');
  }
  else {
    $(obj).prev().prev().css('color', '#49d69c')
        .css('border', '1px solid #49d69c');
  }
  
  if(num > 1) {
    $(obj).parent().prev().find(':checkbox').attr('checked', true);
    $(obj).prev().val(num - 1);
  }
}

/**
 * 购买 资源等
 **********/
var buy = function (id, source) {
  $.ajax({
    url: base_url + '/common/popup?rid=' + id + '&source=' + source,
    async: false,
    data: {id: id, source: source},
    type: 'get',
    dataType: 'text',
    success: function (result) {
      $(result).appendTo('body');
    }
  });
}

/**
 * 加载数据列表 优化后
 *    condition 为筛选做条件 默认可以不传入该参数
 * @author Jamai
 * @version 2.1
 **/
var offset = 1;
var refresh = function (_object, url, source, kw, page, condition) {
  $.ajax({
    url: url,
    async: false,
    data: {kw: kw, offset: page, source: source, condition: condition},
    type: 'POST',
    dataType: 'text',
    success: function (result) {
      var obj = $('.' + _object);
      var results = result.replace(/(\r)*\n/g,"").replace(/\s/g,"");
      if(results == 'no_data') {
        var html = '<div class="no_data">';
        switch (source) {
          case 'activity_list':
            html += '<img src="/wapi/img/mobile/no_thing.png" alt="no_data" />' +
                    '<p>该社区没有最新活动 <br />' + 
                    '去《<a href="' + base_url + '/community/coollife" class="haveback red01">精彩生活</a>》参加更多更精彩的活动吧！</p>';
            break;
          case 'resource_by':
            html += '<img src="/wapi/img/mobile/no_resource.png" alt="no_data" />' + 
                    '<p>这里购买的资源即为我的资源。<br />' + 
                    '去《<a href="' + base_url + '/resource/index"  class="haveback red01">资源库</a>》购买吧！</p>';
            break;
          case 'resource'://资源库
            html += '<img src="/wapi/img/mobile/no_resource.png" alt="no_data" />' + 
                    '<p>到这里去 -> <a href="' + base_url + '/community/coollife"  class="haveback red01">点击</a> 可能有你想要的！</p>';
            break;
          case 'activity_by':
            html += '<img src="/wapi/img/mobile/no_thing.png" alt="no_data" />' + 
                    '<p>你还没有最新的活动，<br />' + 
                    '去《<a href="' + base_url + '/square/square_index"  class="haveback red01">广场</a>》看看吧！有惊喜哦！</p>';
            break;
          case 'user_by':
            html += '<img src="/wapi/img/mobile/no_thing.png" alt="no_data" />' + 
                    '<p>该用户很懒！<br />' + 
                    '去《<a href="' + base_url + '/square/square_index"  class="haveback red01">广场</a>》看看吧！有惊喜哦！</p>';
            break;
          default:
            html += '<img src="/wapi/img/mobile/no_thing.png" alt="no_data" />' + 
                    '<p>去《<a href="' + base_url + '/square/square_index" class="haveback red01">广场</a>》看看吧！有惊喜哦！</p>';
            break;
        }
        html += '</div>';
        if( ! page && (_object != 'search_result' || kw != ''))
          $(obj).html(html);
        else if(_object == 'search_result' && kw == '')
            $(obj).html('');
      }
      else {
        if(kw && page) {
          obj.append(result);
        }
        else if(page) {
          obj.append(result);
        }
        else {
          obj.html(result);
        }

        if(result) {
          $('<li class="loadmore haveback" style="background: none;max-height: 25px;min-height: 25px;cursor: pointer; border-bottom: none;padding: 0;">查看更多<i class="iconfont icon iconfont_20">&#58933;</i></li>')
            .appendTo(obj)
            .click(function () {
              $(this).remove();
              offset++;
              setTimeout(refresh(_object, url, source, kw, offset, condition), 200);
            });
        }
      }
      J.Scroll('#' + $(obj).parent().parent().attr('id'));
    },
    beforeSend: function(XMLHttpRequest) {
      var html = '<div class="loading" style="margin:0 auto; text-align: center; width: 100px;">' +
            '<img src="/wapi/img/mobile/loading.gif" alt="loadding" /></div>';
      $('.' + _object).after(html);
    },
    complete: function () {
      $('.loading').remove();
    }
  });
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

/**
 * @返回消息处理
 * @param result.status 消息标识
 */
function messageResult(result) {

	  switch (result.status) {

			case 0:
				window.location.href = result.url;
			break;
			case 1:
				J.showToast('参加成功');
				find_join_num();
				find_join_list();
			break;
			case 2:
				J.showToast('活动已关闭');
			break;
			case 3:
				J.showToast('免费活动只能参加一次');
			break;
			case 5:
				J.showToast('活动剩余数不足');
			break;
			default:
			return;

	  }
}

var moreCode = function (val, source) { 
  
  $.ajax({
    url: base_url + '/common/codes',
    async: false,
    data: {id: val, source: source},
    type: 'post',
    dataType: 'json',
    success: function (result) {

      var html = '<div id="codeShow" style="padding: 0 10px;">';
      var h = 10;
      $.each(result.success, function (key, item) {
        html += '<p style="padding: 5px 0;"><span class="red01">' + item.code + 
                '</span><span style="color: #333;margin-left: 20px;">' + item.state + '</span></p>';
        h = h + 25;
      });
      html += '</div>';
      
      var width = '';
      var pos = 'center';
      if( (h - $(window).height() ) >= -100) {
        pos = 'top-second';
        //width = $(window).width();
        width = 'auto';
      }
      
      J.popup({
        width: width,
        html: html,
        pos: pos,
        onShow: function () {
          if(pos == 'top-second') {
            $('#jingle_popup').css('margin', '0 10px');
            $('#jingle_popup_mask').css('height', $('#jingle_popup').height() + 100 + 'px');
          }
        }
      });
      
    }
  });
  
  //J.Scroll('#' + $('article.active').attr('id'));
  //
}

//更新参加人数
function find_join_num(){

	$.get('/wapi/90/0/community/find_join_num', {aid: aid},
		function (result) {
			$('#join_num').html(result);
	}, 'text');

}

//更新参加人数列表
function find_join_list(){

	$.get('/wapi/90/0/community/list_users', {aid: aid},
		function (result) {
			var list = list_users(result);
			$('.leaguer').html(list);
	}, 'json');

}

//隐藏微信右上角菜单按钮
function remove_share(){
    document.addEventListener("WeixinJSBridgeReady",function onBridgeReady(){
        WeixinJSBridge.call("hideOptionMenu");
    });
}

document.addEventListener("WeixinJSBridgeReady",function onBridgeReady(){
    WeixinJSBridge.call("showOptionMenu");
});

//拼装参加人数列表
function list_users(result){  

	var discuss = '';

	$.each(result, function(i, value) {
				
				discuss+='<li>';
				discuss+='<span  class="leaguerhead left">';
				discuss+='<a href="'+url+'/user_activity/index?userid='+value.id_user+'" class="haveback">';
				discuss+='<img src="'+value.header_img+'"/>';
				discuss+='<em class="blackish_green">'+value.name+'</em>';      
				discuss+='</a>';
				discuss+='</span>';
				discuss+='<span class="leaguercode right">';

				if(value.eticket_item){

					$.each(value.eticket_item, function(ii, values) {
										
						if(ii==0){

								discuss+='<b class="code red">'+values.code+'</b>';

							if(value.eticket_user_counts > 1){
													
								discuss+=' <b>';
								discuss+='<a class="bluish_green haveback" id="'+values.id_user+'" data-source="'+values.eticket_type+'">查看更多消费码</a>';
								discuss+='</b>';

							}

						}

					});

				}

				discuss+='</span>';
				discuss+='</li>';
	});

	return discuss;
}