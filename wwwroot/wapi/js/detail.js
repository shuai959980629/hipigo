//判断是PC还是触屏
var sourceType = navigator.userAgent.match(/.*Mobile.*/) ? 1 : 2;

var OFFSET = 10;
var page = 1;
var PAGESIZE = 25;
var myScroll,
	pullDownEl, pullDownOffset,
	pullUpEl, pullUpOffset,
	generatedCount = 0;
var maxScrollY = 0;
var fnMyFunc1; //函数变量
var hasMoreData = false;
//阻止手机的触摸默认事件

//if($("#modal").is(":visible")) { /** Jamai ADD 弹出层判断 **/
  document.addEventListener('touchmove', fnMyFunc1 = function(e) {
    e.preventDefault();
  }, false);
//}
//监听页面一载入就立即执行
document.addEventListener('DOMContentLoaded', function() {
	$(document).ready(function() {
		if(spking==1){
		 loaded();	
		}else{
		 $("#pullUp").hide();
		 $("#pullDown").hide();	
		}
	});
}, false);

function loaded() {
	
	
	pullDownEl = document.getElementById('pullDown');
	pullDownOffset = pullDownEl.offsetHeight;
	pullUpEl = document.getElementById('pullUp');
	pullUpOffset = pullUpEl.offsetHeight;

	hasMoreData = false;
	// $("#thelist").hide();
	$("#pullUp").hide();

	pullDownEl.className = 'loading';
	pullDownEl.querySelector('.pullDownLabel').innerHTML = 'Loading...';

	page = 1;
	$.get(
		base_url+"/community/spking_list", {
			"page": page,
			"pagesize": PAGESIZE,
			"aid":aid
		},
		function(response, status) {
			if (response.status == 1) {
				
				$("#thelist").show();

				if (response.list.length < PAGESIZE) {
					hasMoreData = false;
					$("#pullUp").hide();
				} else {
					//hasMoreData = true;
					//$("#pullUp").show();
				}

				// document.getElementById('wrapper').style.left = '0';

				myScroll = new iScroll('wrapper', {
					//zoom:true,
					//vScroll: false,
                    momentum:false,//阻止惯性  zxx
					hScrollbar: false,
					vScrollbar: false,
					useTransition: true,
					topOffset: pullDownOffset,
					onRefresh: function() {
						if (pullDownEl.className.match('loading')) {
							pullDownEl.className = 'idle';
							pullDownEl.querySelector('.pullDownLabel').innerHTML = '下拉刷新...';
							this.minScrollY = -pullDownOffset;
						}
						if (pullUpEl.className.match('loading')) {
							pullUpEl.className = 'idle';
							pullUpEl.querySelector('.pullUpLabel').innerHTML = '显示更多...';
						}
					},
					onScrollMove: function() {
						if (this.y > OFFSET && !pullDownEl.className.match('flip')) {
							pullDownEl.className = 'flip';
							pullDownEl.querySelector('.pullDownLabel').innerHTML = '正在刷新...';
							this.minScrollY = 0;
						} else if (this.y < OFFSET && pullDownEl.className.match('flip')) {
							pullDownEl.className = 'idle';
							pullDownEl.querySelector('.pullDownLabel').innerHTML = '正在加载...';
							this.minScrollY = -pullDownOffset;
						} 
						if (this.y < (maxScrollY - pullUpOffset - OFFSET) && !pullUpEl.className.match('flip')) {
							if (hasMoreData) {
								this.maxScrollY = this.maxScrollY - pullUpOffset;
								pullUpEl.className = 'flip';
								pullUpEl.querySelector('.pullUpLabel').innerHTML = '正在刷新...';
							}
						} else if (this.y > (maxScrollY - pullUpOffset - OFFSET) && pullUpEl.className.match('flip')) {
							if (hasMoreData) {
								this.maxScrollY = maxScrollY;
								pullUpEl.className = 'idle';
								pullUpEl.querySelector('.pullUpLabel').innerHTML = '正在加载...';
							}
						}
					},
					onScrollEnd: function() {
						if (pullDownEl.className.match('flip')) {
							pullDownEl.className = 'loading';
							pullDownEl.querySelector('.pullDownLabel').innerHTML = 'Loading...';
							//上拉刷新
							refresh();
						}
						if (hasMoreData && pullUpEl.className.match('flip')) {
							pullUpEl.className = 'loading';
							pullUpEl.querySelector('.pullUpLabel').innerHTML = 'Loading...';
							//上拉分页
							nextPage();
						}
					}
				});

				$("#thelist").empty();
				//首次载入数据
				refresh_new(response);
				if(response.list.length >= PAGESIZE){
				 $("#next_more").append('<a class="more left" href="#">查看更多</a>');
				}else{
				  $("#next_more").empty();	
				}
				setTimeout(function (){
				 myScroll.refresh(); //加载完数据后调用
				 },400)
				is_load_img();
				if (hasMoreData) {
					myScroll.maxScrollY = myScroll.maxScrollY + pullUpOffset;
				} else {
					myScroll.maxScrollY = myScroll.maxScrollY;
				}
				maxScrollY = myScroll.maxScrollY;
			};
		 },
   "json");
}
//下拉刷新
function refresh() {
	page = 1;
	$.get(
		base_url+"/community/spking_list", {
			"page": page,
			"pagesize": PAGESIZE,
			"aid":aid
		},
		function(response, status) {
			if (status == "success") {
				$("#thelist").empty();

				myScroll.refresh();

				if (response.list.length < PAGESIZE) {
					hasMoreData = false;
					$("#pullUp").hide();
					$("#next_more").empty();
				} else {
					//hasMoreData = true;
					//$("#pullUp").show();
				}

				refresh_new(response);
				myScroll.refresh(); // //加载完数据后调用
				is_load_img();       //当有图片时延时加载

				if (hasMoreData) {
					myScroll.maxScrollY = myScroll.maxScrollY + pullUpOffset;
				} else {
					myScroll.maxScrollY = myScroll.maxScrollY;
				}
				maxScrollY = myScroll.maxScrollY;
			};
		},
		"json");

}
//上拉分页
function nextPage() {
	page++;
	$.get(
		base_url+"/community/spking_list", {
			"page": page,
			"pagesize": PAGESIZE,
			"aid":aid
		},
		function(response, status) {
			if (status == "success") {
				if (response.list.length < PAGESIZE) {
					hasMoreData = false;
					$("#pullUp").hide();
					$("#next_more").empty();
				} else {
					//hasMoreData = true;
					//$("#pullUp").show();
				}

				refresh_new(response);
				myScroll.refresh(); // //加载完数据后调用
				is_load_img();       //当有图片时延时加载
				if (hasMoreData) {
					myScroll.maxScrollY = myScroll.maxScrollY + pullUpOffset;
				} else {
					myScroll.maxScrollY = myScroll.maxScrollY;
				}
				maxScrollY = myScroll.maxScrollY;
			};
		},
		"json");
}

//加载更多
$('#next_more').live('click',function(){
 nextPage();
})

//当页面全部加载完毕(这里等待图片全部加载，ajax追加的数据)
function is_load_img(){
var imgList = $('body').find('img');
imgList.each(function(){
    $('img').load(function(){
         if(imgList.length==0){
            setTimeout(function(){
	           myScroll.refresh(); //重新计算高度
	           (function(window, PhotoSwipe){  //图片放大滑动
               var options = {},
               instance = PhotoSwipe.attach( window.document.querySelectorAll('#Gallery a'), options );
               }(window, window.Code.PhotoSwipe));
	      },500);
       }
      imgList.length --;
    })
  })
}

//页面全部加载包括图片(第一次进入页面时候调用)
document.onreadystatechange = function(){   
    if(document.readyState=="complete")
    {
      if(spking==1){
        setTimeout(function(){
        //myScroll.refresh();
       },500);
      }else{
        myScroll = new iScroll('wrapper', {
            momentum:false,//阻止惯性  zxx
                hScrollbar: false,
                vScrollbar: false,
                useTransition: true,
                topOffset: pullDownOffset
            })
       setTimeout(function(){
       myScroll.refresh();
       },1000);
      }
    }
}

//刷新页面
function refresh_new(response){
	var index=1;
	$.each(response.list, function(key, value) {
        var reply ='';
        if(value.reply !=null && value.reply !=''){
            $.each(value.reply,function(i,n){
                if(i>=3){return false;}
                if(n.content.indexOf('回复') ==0){var mao = ''}else{var mao = ': '}
                reply +='<b style="cursor:pointer" id="reply_one" data-index="'+index+'"  data-id_review="'+value.id_review+ '" data-name="'+n.name+'"><em>'+n.name+' </em>'+mao+n.content+'<i>'+n.created+'</i></b>';
            })

            if(value.reply.length>3){
            var more_url = base_url+'/community/reply_detail?oid='+oid+'&aid='+aid+'&sk_id='+value.id_review;
            var more ='<b><em class="see-more"><a href="'+more_url+'">查看更多回复</a></em></b>';
            }else{
            var more='';
            }
            if(value.image_url !='' || value.imgs !=''){
            var style_reply='<span id="reply'+value.id_review+'" class="pic_reply">'+reply+more+'</span>';
            }else{
            var style_reply='<span id="reply'+value.id_review+'" class="reply">'+reply+more+'</span>';
            }

            }else{
                var style_reply='';
            }
            //评论多图片（兼容老数据）
        var img='';
        var spking_img_style = '';
        if(value.image){
            $.each(value.image,function(i,n){
              if(n) {
                  var img_type = n.substring(n.lastIndexOf('.'), n.length);
                 var img_str =n.split(img_type);
                 var img_sm = img_str[0]+'-small'+img_type;
                img += '<li><a href="'+n+'"><img src="'+img_sm+'" class="imgcon"/></a></li>';
              }
            });
            spking_img_style = '<div id="MainContent"><ul id="Gallery" class="gallery">'+img+'</ul></div>';
        }

            if(value.image_url != '' || value.imgs !=''){
            var br = value.content != '' ? '<br>'	: '';
//            var spking_img='';
////               if(value.image_url !=''){
////                 var spking_img ='<li><a href="'+value.image_url+'"><img  src="'+value.image_url+'" class="imgcon" alt="来至'+value.name+'" /></a></li>';
////                 }
//               if(value.imgs !=''){
//                 $.each(value.imgs,function(i,n){
//                 var img_types =value.imgs[i].split('.');
//                 var img_sm = img_types[0]+'-small.'+img_types[1];
//                 spking_img += '<li><a href="'+value.imgs[i]+'"><img src="'+img_sm+'" class="imgcon" alt="来自'+value.name+'" /></a></li>';
//               })
//             }
//             var spking_img_style = '<div id="MainContent"><ul id="Gallery" class="gallery">'+spking_img+'</ul></div>';
             var content_img = '<span class="say-person-con">'+value.content+br+style_reply+spking_img_style+'</span>';
            }else{
            var content_img ='<span class="say-person-con">'+value.content+style_reply+spking_img_style+'</span>';
            }

        $("#thelist").append('<li>'+
        '<p class="say-pic left"><img src="'+value.head_image_url+'"/></p>'+
        '<p class="say-person left">'+
         '<span class="say-person-name" id="style_reply'+value.id_review+'">'+
         '<em class="left" data-oid ='+value.id_open+'>'+value.name+'</em>'+
         '<a href="javascript:;" id="reply_two" class="right" data-id_review="'+value.id_review+ '" data-index="'+index+'" data-name="'+value.name+'">'+
         '<i class="icon-comments-alt right"></i></a>'+
         '<span class="time">'+value.created+'</span>'+content_img+'</span>'+
         '</p></li>');
         index ++;
    });
}



$(document).ready(function() {
	
	//头部赞
	$('#goods').live('click',function(){
	$('#goods').empty();
	$('#goods1').empty();
	var total = $('#footer').attr('data-good');
	total = parseInt(total)+parseInt(1);
	$('#footer').attr('data-good',total);
	$('#goods').append('<i class="iconfont icon iconfont_638">&#58884</i>'+total);
	$('#goods1').append('<i class="iconfont icon iconfont_638">&#58884</i>已赞'+total);
	//$("#goods").attr("id","changeId");
	$('#goods').attr("id","good_yes");
	//$("#goods1").attr("id","changeId");
	$('#goods1').attr("id","good_yes");
	//$(".goodt").attr("id","changeId");
	//$('.goodt').attr("id","good_yes");
	$.ajax({    
            url:base_url+'/community/good',
            data:'userid='+userid+'&aid='+aid,
            async: true,
            dataType: 'json',
            type:'get',
            success:function(data){
           }
        })	
});	
	
//底部赞
$('#goods1').live('click',function(){
	$('#goods').empty();	
	$('#goods1').empty();
	var total = $('#footer').attr('data-good');
	total = parseInt(total)+parseInt(1);
	$('#footer').attr('data-good',total);
	$('#goods').append('<i class="iconfont icon iconfont_638">&#58884</i>'+total);
	$('#goods1').append('<i class="iconfont icon iconfont_638">&#58884</i>已赞'+total);
	//$("#goods").attr("id","changeId");
	$('#goods').attr("id","good_yes");
	//$("#goods1").attr("id","changeId");
	$('#goods1').attr("id","good_yes");
	//$(".goodt").attr("id","changeId");
	//$('.goodt').attr("id","good_yes");
	$.ajax({    
            url:base_url+'/community/good',
            data:'userid='+userid+'&aid='+aid,
            async: true,
            dataType: 'json',
            type:'get',
            success:function(data){
           }
        })	
});

//取消赞
$('#good_yes').live('click',function(){
	
	//$('#goods').empty();	
	//$('#goods1').empty();
	var total = $('#footer').attr('data-good');
    if(total>0){
        total = total-1;
    }else{
        total = 0;
    }
	$('#footer').attr('data-good',total);
	$('#good_yes').attr("id","goods");
	$('#good_yes').attr("id","goods1");
	$('#goods').html('<i class="icon-thumbs-up gray"></i>'+total);
	$('.payment').html('<i class="icon-thumbs-up"></i>赞'+total);
	$.ajax({    
            url:base_url+'/community/good_del',
            data:'userid='+userid+'&aid='+aid,
            async: true,
            dataType: 'json',
            type:'get',
            success:function(data){
           }
        })	
});

//参加活动
$('#att_activity').live('click',function(){
    if(surplus == 0){//当活动total（活动数量）为0时surplus为-1
        $('#modalv2').empty();
        $('#modalv2').append('<dl><dd>活动人数已满！</dd></dl>');
        $('#modalv2').fadeIn();
        setTimeout(function(){
            $('#modalv2').fadeOut();
        },3000);

        return false;
    }
  if(userid != undefined && userid !='' && identity !='visitor'){
    $('#phone').remove();
    if(checkInput()) {
      $('.caozuo_btn').find(':first')
                  //.attr('id', 'att_activity1')
                  .removeClass()
                  .addClass('make_sure01 left');
    }
    else {
      $('.caozuo_btn').find(':first')
                  //.removeAttr('id')
                  .removeClass()
                  .addClass('make_sure left');
    }
  }
  else {//准用户
    att_activity();
  }

  $('#modal').fadeIn();
  handleTips();
  
  $('#show_back_top').addClass('webgame_msgWindow_mask');
})

    var handleTips = function () {
        $('body').css('overflow-y', 'scroll');
        //判断当前窗口的高 处理在中间显示
        var re = $('#modal').height() - $(window).height();

        if(re > 0) {
            //当弹出层的高度窗口的高度
//    $('body').unbind('touchmove');
            document.removeEventListener('touchmove', fnMyFunc1);

            $('#modal').css('top', $(document).scrollTop());
        }
        else {
            //计算弹出层的top值
            //当前窗口/2 - 弹出层/2 + 滚动条的高度
            $('#modal').css('top', ($(window).height() / 2) - ($('#modal').height() / 2) + $(document).scrollTop());
            if(sourceType == 1)
                $('body').bind('touchmove', function (event) { event.preventDefault();});
            else
                $('body').css('overflow-y', 'hidden');
        }
    }

//窗口改变大小时重新计算弹出层的高度
var resizeTimer = null;
$(window).resize(function() {
  if(resizeTimer) 
    clearTimeout(resizeTimer);
  resizeTimer = setTimeout("handleTips()", 0);
});




//取消
$('#come_back').live('click',function(){
  $('#modal').fadeOut();
    document.addEventListener('touchmove', fnMyFunc1);
  $('#show_back_top').removeClass('webgame_msgWindow_mask');
});

$('#att_activity1').live('click',function(){
	 var phone  = $('#cell_phone').val();
	 var ac_num = $('#ac_num').val();
   var bool = true;
	 ac_num = ac_num == undefined ? 1 : ac_num;
   
  var condetion = [];
  $('[name="condetion[]"]:text').each(function() {
    if( ! $(this).val()) {
      alert('请输入' + $(this).attr('pre-data'));
      $(this).css('border-bottom', '2px solid red');
      bool = false;
      return false;
    }
    else {
        if($(this).attr('pre-data') == '电话号码'){
            if(!(/^1[3|5|8|6|7|4]\d{9}$/.test($(this).val())) || $("#vk_checked").attr("checked")!='checked'){
                alert('电话号码输入有误！');
                bool = false;
                return false;
            }
        }
      $(this).css('border-bottom', '2px solid #666');
      bool = true;
    }
    condetion.push($(this).attr('pre-data') + '&@&' + $(this).val());
  });

    if(condetion == ''){
        var phones = phone.replace(/(\r)*\n/g,"").replace(/\s/g,"");
        if(phones == ''){
            alert('请输入电话号码。');
            bool = false;
            return false;
        }else{
            if(!(/^1[3|5|8|6|7|4]\d{9}$/.test(phone)) || $("#vk_checked").attr("checked")!='checked'){
                alert('电话号码输入有误！');
                bool = false;
                return false;
            }
        }
    }
  if(bool !== true) {
    return false;
  }


  $('#modal').fadeOut();
  //'userid='+userid+'&aid='+aid+'&phone='+phone+'&ac_num='+
  //                ac_num+'&type='+type+'&condetion='+condetion+'&xcode='+xcode+'&from='+from
    $.ajax({
            url:base_url+'/community/att_activity',
            data:{userid: userid, aid: aid, phone: phone, ac_num: ac_num,
                  type: type, condetion: condetion, xcode: xcode, from: from},
            async: false,
            dataType: 'json',
            type:'get',
            beforeSend:function(){
            	$('#modalv2').fadeIn();
            },
            complete:function(){
                setTimeout(function(){
                    $('#modalv2').fadeOut();
                    $('#show_back_top').removeClass('webgame_msgWindow_mask');
                },3000);
            },
            success:function(data){
             
             $('#modal').fadeOut();
                document.addEventListener('touchmove', fnMyFunc1);
             if(data.status == 1)
             {
             $('#modalv2').empty();
             $('#modalv2').append('<dl><dd>参加成功</dd></dl>');
             $('#modalv2').fadeIn();
             setTimeout(function(){
             $('#modalv2').fadeOut();	
             },2000);	
             //刷新当前页码状态
             $('#att_activity').remove();
             $('#header').append('<a href="javascript:;"  class="join right">已参加</a>');
             $('.footerbtn').html('已参加');
             $('.footerbtn').attr('id','att_s');
             $('#att_activity').removeClass('att_activity');
             var num = $('#join_count').attr('data-join_count');
             total = parseInt(num)+parseInt(1);
             $('#join_count').html(total+'人');
             }else if(data.status==0){
             window.location.href=data.url;
             }else if(data.status==4){
             setTimeout(function(){
             $('#modalv2').fadeOut();
             window.location.href=base_url+'/user/login';	
             },1000);
             }else if(data.status==3){
             $('#modalv2').empty();
             $('#modalv2').append('<dl><dd>您已参加过此活动</dd></dl>');
             $('#modalv2').fadeIn();
             setTimeout(function(){
             $('#modalv2').fadeOut();	
             },3000);
             }else if(data.status==5){
                 $('#modalv2').empty();
                 $('#modalv2').append('<dl><dd>活动剩余数量不足</dd></dl>');
                 $('#modalv2').fadeIn();
                 setTimeout(function(){
                    $('#modalv2').fadeOut();
                 },3000);
             }else{
             //服务器错误
             $('#modalv2').empty();
             $('#modalv2').append('<dl><dd>暂时不能参加</dd></dl>');
             $('#modalv2').fadeIn();
             setTimeout(function(){
             $('#modalv2').fadeOut();	
             },2000);
             }
           }
        })
      	
});
 
//分享
$('#share').live('click',function(){
	   $('.share-open').show(300);
	   setTimeout(function(){
	   $('.share-open').hide(300);
	   },1800);
});	
    
//点击评论后显示样式
$('#style_show').live('click',function(){
	    //设置评论标志：0为评论，非零为回复
	    $('#show_pic').show();
	    $('#send_spking').attr('data-sp_rp',0); 
     	var state = $('#style_show_data').attr('data-style_show');
     	$('#textarea_in').val('');
     	//评论样式
     	var img_num = $(".closepop");
        var yes_len = img_num.length;
        //显示状态
        if(yes_len>0){
        $('#show_pic').html('<i class="iconfont icon iconfont_639 blue-green">&#58890</i><em></em>');
        $('#send_spking').attr('class','active');	
        }else{
        $('#show_pic').html('<i class="iconfont icon iconfont_639">&#58890</i>');
        	
        }
       	if(state==0)
       	{
       	 //显示图片
       	 $('#show_pic').attr('data-reply_one',1);
       	 $('#style_show_data').show(200);
       	 $("#textarea_in").focus();
       	 $('#style_show_data').attr('data-style_show',1);	
       	}else{
       	 $("#textarea_in").blur();	
       	 $('#style_show_data').hide(200);
       	 //隐藏图片和表情
       	 $('#show_pic_smile').hide(200);
       	 $('#style_show_data').attr('data-style_show',0);		
       	}
});
    
//显示图片，隐藏表情
$('#show_pic').live('click',function(){
	    $('#show_pic_smile').show();
	    $('#show_smile').hide();
	    $("#update_pics").slideToggle();
	    //计算当前上传的图片数量
	    var img_num = $(".closepop");
        var yes_len = img_num.length;
        var no_len = 9-yes_len;
        $('#yes_sele').html(yes_len);
        $('#no_sele').html(no_len);
        
});
    
//显示表情，隐藏图片
$('#icon_smile').live('click',function(){
	    //$('#update_pics').hide();
	    $('#update_pics').css('display','none');
        $('#show_pic_smile').show();
	    setTimeout(function (){
	    //$("#show_smile").slideToggle("slow");
	    $('#show_smile').css('display','block');
	    //$('#show_smile').show(300);
	    },100)
	    
	   
});
//选择表情 
$('.smile_ck').live('click',function(){
	    //$('#show_pic').hide();
     	var value = $('#textarea_in').val();
       	var name = $(this).attr('data-smile-name');
       	$('#textarea_in').val(value+name);
       	var content = $('#textarea_in').val();
       	if(content.length>0){$('#send_spking').attr('class','active');}
});
  
//提交评论和回复
$('#send_spking').live('click',function(){
   $('.goodt').show();
   $('.footerbtn').show();	
   var value = $('#textarea_in').val();
   var sp_rp = $('#send_spking').attr('data-sp_rp');
   
	   	var sp_rp = $('#send_spking').attr('data-sp_rp');
	    var user_name = $('#send_spking').attr('data-user_name');
	    if(sp_rp==0)
	    {  //评论
	       //获取多张已上传的图片
	       var idArr = new Array();  
           var idsContainer = $(".closepop");  //获取所有节点的dom数组  
           var len = idsContainer.length;               
           for(var index = 0; index < len; index++){  
           var $container = $(idsContainer[index]); 
           var id  = $container.attr("data-img_name");
           idArr.push(id);  
          }  
           //数组转字符串  
           imgs = idArr.join(',');
           if(value !='' || imgs !=''){
               $.ajax({
                   url:base_url+'/community/spking',
                   data:'userid='+userid+'&aid='+aid+'&content='+value+'&sp_rp='+sp_rp+'&imgs='+imgs,
                   async: true,
                   dataType: 'json',
                   type:'get',
                   success:function(data){
                   }
               });
	       setTimeout(function(){
	        $('#style_show_data').hide(200);
            $('#show_pic_smile').hide(200);
            $('#style_show_data').attr('data-style_show',0);
            var num = $('#review_count_bm').attr('data-review_count');
            var total = parseInt(num)+parseInt(1);
            $('.review_counts').html('');
            $('.review_counts').html(total);
            $('#top_count').html(total);
            $('#textarea_in').val('');
            $('#textarea_in').blur();
            var index = $(this).attr('data-index');
            if(spking==1 || index !=undefined)
             refresh();
            else
             loaded();
	        },500);

            $('.closepop').parents("li").remove();
          }else{
            prompt('评论不能为空');	
          }
      }
      else{
	        //回复
	        if(value !=''){
	    	$('#style_show_data').hide(200);
	    	$('#style_show_data').attr('data-style_show',0);
            $('#show_pic_smile').hide(200);
            $('#textarea_in').val('');
            $('#textarea_in').blur();
            $.ajax({    
            url:base_url+'/community/spking',
            data:'userid='+userid+'&aid='+aid+'&content='+value+'&sp_rp='+sp_rp+'&user_name='+user_name,
            async: true,
            dataType: 'json',
            type:'get',
            success:function(data){
            //清除回复用户名
            $('#send_spking').attr('data-user_name','');	
            if($('#reply'+sp_rp).length>0){
            //回复某人的回复	
            var str_reply = '<b style="cursor:pointer" id="reply_one" data-id_review="'+sp_rp+ '" data-name="'+data.name+'"><em>'+data.name+'</em> : '+data.content+'<i>刚刚</i></b>';	
            $('#reply'+sp_rp).prepend(str_reply);
            myScroll.refresh();
            }else{
            //回复评论	
            var str_reply = '<span id="reply'+sp_rp+'" class="reply"><b style="cursor:pointer" id="reply_one" data-id_review="'+sp_rp+ '" data-name="'+data.name+'"><em>'+data.name+'</em>: '+data.content+'<i>刚刚</i></b></span>';
            $('#style_reply'+sp_rp).append(str_reply);
            
            myScroll.refresh();
            }
           }
         })	
       }else{
       prompt('回复不能为空');	
       }
     }
    
     	
$('#send_spking').attr('class','enter'); 
$('#update_pic').show();        	
});
   
//回复回复者
$('#reply_one').live('click',function(){
$('#show_pic').hide();
$('#send_spking').show();	
var index = $(this).attr('data-index');
if(index !=undefined){
myScroll.scrollToElement("li:nth-child("+index+")",100);	
}
	
var id_review = $(this).attr('data-id_review');
var spking_id = $('#send_spking').attr('data-sp_rp');
//回复ID
$('#send_spking').attr('data-sp_rp',id_review); 
var name = $(this).attr('data-name');
var state = $('#style_show_data').attr('data-style_show');
$('#update_pics').hide();
$('#textarea_in').val('');
$('#textarea_in').attr('placeholder','回复 '+name+'：');
//添加回复者名字
$('#send_spking').attr('data-user_name',name);
var state = $('#style_show_data').attr('data-style_show');
if(state==0 || id_review !=spking_id)
{
$('#style_show_data').show(200);
$("#textarea_in").focus();
$('#style_show_data').attr('data-style_show',1);
//隐藏图片
$('#show_pic').attr('data-reply_one',0);	
}else{
$("#textarea_in").blur();
$('#style_show_data').hide(200);
$('#show_pic_smile').hide(200);
$('#style_show_data').attr('data-style_show',0);		                        
}
})

//回复评论
$('#reply_two').live('click',function(){
$('#show_pic').hide();
$('#send_spking').show();		
//定位到当前评论位置	
var index = $(this).attr('data-index');	
myScroll.scrollToElement("li:nth-child("+index+")",100);
var id_review = $(this).attr('data-id_review');
var spking_id = $('#send_spking').attr('data-sp_rp');
$('#send_spking').attr('data-sp_rp',id_review); 
var name = $(this).attr('data-name');
var state = $('#style_show_data').attr('data-style_show');
$('#update_pics').hide();
$('#textarea_in').val('');
$('#textarea_in').attr('placeholder','回复内容');
if(state==0 || id_review !=spking_id)
{
$('#style_show_data').show(200);
$("#textarea_in").focus();
$('#style_show_data').attr('data-style_show',1);
//隐藏图片
$('#show_pic').attr('data-reply_one',0);	
}else{
$("#textarea_in").blur();
$('#style_show_data').hide(200);
$('#show_pic_smile').hide(200);
$('#style_show_data').attr('data-style_show',0);		
}
	
})
//当回复框获得到焦点
$("#textarea_in").focus(function(){

//隐藏赞和评论	
$('#footer_style').hide();
//隐藏表情和图片
$('#show_pic_smile').hide();
$("#show_smile").hide();
$('#update_pics').hide();
$('#show_pic').attr('data-show_pic',0);	
});
//回复框失去焦点

$("#textarea_in").blur(function(){
$('#footer_style').show();
$('#show_pic').attr('data-show_pic',1);		
});

$('#scroller').click(function(){
$("#textarea_in").blur();	
$('#style_show_data').hide();
$('#show_pic_smile').hide();
$('#style_show_data').attr('data-style_show',0);	
});

//判断是否需要展开或收起
$(function(){
var content_h = $("#show_content").height();
if(content_h<100){
$('#is_open').hide();	
}	
})
//展开收起详情
$(".opens").click(function(){
	   var content_h = $("#show_content").height();
	   var is_open = $('#is_open').attr('data-is_open');
	   $('#show_content').removeAttr("style");
	   if(is_open==1)
	   {
	   	var divcss = {
        'max-height': '120px',
        'overflow': 'hidden',
        'display':'none',
        };
      $("#show_content").css(divcss);
      //获取需要改变的高度
      var new_content_h = $("#show_content").height();
      $('#show_content').removeAttr("style");
      var divcss = {
        'height': 'auto',
        'overflow': 'hidden',
        'display':'block'
       };
       $("#show_content").css(divcss);
       $("#show_content").animate({height:new_content_h});
       $('#is_open').attr('data-is_open',0);
	   $('#is_open').html('<a href="javascript:;"><i  class="icon-angle-down icon-large"></i><i class="red">展开</i></a>');
	   } else{
	     var divcss = {
	    'height': 'auto',
        'overflow': 'hidden',
        'display':'none'
        };
         $("#show_content").css(divcss);
         //获取需要改变的高度
         var new_content_h = $("#show_content").height();
         $('#show_content').removeAttr("style");
         var divcss = {
        'height': content_h,
        'overflow': 'hidden',
        'display':'block',
        'padding-bottom':'15px'
        };
         $("#show_content").css(divcss);
         $("#show_content").animate({height:new_content_h});
         $('#is_open').attr('data-is_open',1);
	   	 $('#is_open').html('<a href="javascript:;"><i  class="icon-angle-up icon-large"></i><i class="red">收起</i></a>');
	   }
   if(spking==1){
	   //高度改变后重新计算(这里需要延时)
	   setTimeout(function(){
	   myScroll.refresh();
	   },1000);
   }else{
	   //如果当前没有评论必须先实例化
	   myScroll = new iScroll('wrapper', {
           momentum:false,//阻止惯性  zxx
					hScrollbar: false,
					vScrollbar: false,
					useTransition: true,
					topOffset: pullDownOffset
				})
	   setTimeout(function(){
	   myScroll.refresh();
	   },1000);
	 } 
});
//图片加载延迟
function img_delay(){
     var $tip=$('<div id="tip"><div class="t_box"><img id="zhe_img" class="simg" src="'+url_prefix+'/img/loading.gif"/></div></div>');
     $('.show_big_style').prepend($tip);
     //等待图片加载
	 var window_w=(document.body.clientWidth);
     var window_h =(document.body.clientHeight);
     var width_l  = (window_w)/2;
     var height_r = (window_h)/2;
	 var divcss = {
      'text-align': 'center',
      'z-index': 3,
      'top': height_r,
      'position': 'fixed',
      'left': width_l
      };
     $(".show_big_style").css(divcss);	
}	
//图片放大(评论)
$('#tip').live('click',function(){
	 //全局取消	
     //$('#show_host').addClass('show_back_mian');
     $('#show_back_top').addClass('webgame_msgWindow_mask');
     //图片等待
     //img_delay();
     //var url_img=this.src.replace('-small','');
	 //图片高宽
	 //compress(url_img);
 })
   
//图片放大(海报)
$('.tips').live('click',function(){
	  //全局蒙版	
     //$('#show_host').addClass('show_back_mian');
     $('#show_back_top').addClass('webgame_msgWindow_mask');
     //图片等待
     // img_delay();
	 // var url_img = this.src.replace('-big', '');
	 //图片高宽
	 // compress(url_img);
   	 
})  
   
//关闭放大效果    
$('#show_host').live('click',function(){
	  //当大图片还未加载完成时，点击关闭
	  if($('#tip').attr('data-big_imgs')==1){
	   $('#show_host').attr('data-big_status',1);
	  }else{
	   $('#show_host').attr('data-big_status',0);
	  }
	  
	  $('#tip').hide(200);
   	  setTimeout(function (){
	  $('#tip').remove();
	  $('#show_host').removeClass('show_back_mian');
	  $('#show_back_top').removeClass('webgame_msgWindow_mask');
	  }, 200);	
   	 
})

//等比列压缩图片
function compress(url_img){
//图片高宽
	 var imges = new Image();  
          imges.src = url_img;  // 远程图片路径
          imges.onload = function(){
         var w = imges.width;
         var h = imges.height;
         var window_w=(document.body.clientWidth);
         var window_h =(document.body.clientHeight);
    //移除等待图片加载状态   
    $('#tip').remove();      
    //窗口高宽  
    if(w<window_w && h<window_h){
     	var width_l  = (window_w-w)/2;
        var height_r = (window_h-h)/2;
     }else if(w>window_w && h<window_h){
     	var b = w/window_w;
     	var m_h= h/b;
     	var width_l = 0;
     	var height_r = (window_h-m_h)/2;
     }else if(w<window_w && h>window_h){
     	var b = h/window_h;
     	var m_w = w/b;
     	var width_l  = (window_w-m_w)/2;
     	var height_r = 0;
     }else{
     	var b_h = h/window_h;
     	var b_w = w/window_w;
     	if(b_h>b_w){
     	var m_w = w/b_h;
     	var width_l = (window_w-m_w)/2;	
     	var height_r = 0;	
     }else{
     	var m_h = h/b_w;
     	var height_r = (window_h-m_h)/2;
     	var width_l = 0;	
     	}
     }
   
//显示大图片
    var $tip=$('<div id="tip" data-big_imgs=1><div class="t_box"><img id="zhe_img" class="simg"  src="'+url_img+'"/></div></div>');
    if($('#show_host').attr('data-big_status')==1){
     $('.show_big_style').prepend($tip);
     $('#tip').show(200);
     
       
  if(h>window_h && w>window_w){
	 if((w/window_w) > (h/window_h)){
	 var divcss = {
      'width':window_w,
      };
      $("#zhe_img").css(divcss);	
	 }else{
	  var divcss = {
      'height':window_h,
      };
      $("#zhe_img").css(divcss);	
	 }
   }
	 
	 if(h<window_h && w>window_w){
	 var divcss = {
      'width':window_w,
      };
      $("#zhe_img").css(divcss);		
	 }
	 if(h>window_h && w<window_w){
	 var divcss = {
      'height':window_h,
      };
      $("#zhe_img").css(divcss);		
	 }
     var divcss = {
      'text-align': 'center',
      'z-index': 3,
      'top': height_r,
      'position': 'fixed',
      'left': width_l
      };
      $(".show_big_style").css(divcss);
     }else{
      $('#show_host').attr('data-big_status',1);	
     }
  }
} 

//提示信息
function prompt(msg){
$('#prompt').append('<div class="modalv1" id="modalv3">'+
    '<dl>'+
    	'<dd>'+msg+'</dd>'+
    '</dl>'+
'</div>');
setTimeout(function(){
   $('#modalv3').fadeOut();
   $('#modalv3').remove();	   
  },2000);
}

});