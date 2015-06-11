var OFFSET = 5;
var page = 1;
var PAGESIZE = 25;
var myScroll,
	pullDownEl, pullDownOffset,
	pullUpEl, pullUpOffset,
	generatedCount = 0;
var maxScrollY = 0;
var hasMoreData = false;
//阻止手机的触摸默认事件
document.addEventListener('touchmove', function(e) {
	e.preventDefault();
}, false);

//监听页面一载入就立即执行
document.addEventListener('DOMContentLoaded', function() {
	$(document).ready(function() {
		if(activity_num>0 || bid==90)
		{
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
		base_url+"/community/home_list", {
			"page": page,
			"pagesize": PAGESIZE,
			"bid":bid,
            "screenwidth":screen.width
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
          $('#next_more').show();
				 //$("#next_more").append('<a class="more left" href="#" >查看更多</a>');		
				}else{
				 $("#next_more").hide();
				}
				myScroll.refresh(); //加载完数据后调用
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
		base_url+"/community/home_list", {
			"page": page,
			"pagesize": PAGESIZE,
			"bid":bid,
            "screenwidth":screen.width
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
//上拉分页
function nextPage() {
	page++;
	$.get(
		base_url+"/community/home_list", {
			"page": page,
			"pagesize": PAGESIZE,
			"bid":bid,
            "screenwidth":screen.width
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

//刷新数据
function refresh_new(response) {
  var i=0;
  //用户分享ID
  var suserid = (userid !='' || userid !=undefined) ? 'suserid='+userid : '';
  $.each(response.list, function(key, value) {
    var html = '';
    if(bid ==90) {
      if(value.object_type == 'user') {
        html = '<span class="source left">来自<a href="/wapi/90/0/user_activity/index?userid=' +
            value.id_business + '"  class="bottle_green">' + 
            decodeURIComponent(value.nick_name) + '</a></span>';
        value.id_business = bid;
      }
      else {
        html = '<span class="source left">来自<a href="/wapi/' +
          value.id_business + '/0/community/home" class="bottle_green">' +
          value.bn_name + '</a></span>';
      }
    }
    
    if(value.join_price == 0){
      var price_user = '人参加';
    }
    else{
      var price_user = '人付款';
    }
    var price = value.join_price == 0 ? '免费' : value.join_price + '元';
      //zxx start
      var nick_name = '';
      if(value.join_count == 0 || value.add_num == 0){
      }else{
//          nick_name = value.nick_more !='' ? '<p class="payer left"><a href="#">'+value.nick_more+'</a>等'+value.join_count+price_user : '<p class="payer left">已有'+value.add_num+price_user;
          nick_name = value.nick_more !='' ? '<p class="payer left"><a href="#">'+value.nick_more+'</a>等'+value.add_num+price_user : '<p class="payer left">已有'+value.add_num+price_user;
      }
      //zxx end

//    var nick_name = value.nick_more !='' ? '<p class="payer left"><a href="#">'+value.nick_more+'</a>等'+value.join_count+price_user : '<p class="payer left">已有'+value.add_num+price_user;//王青的
    var att_user_url = base_url+'/community/user_att_list?aid='+value.id_activity;
    
    var detail = '/wapi/'+value.id_business+'/0/community/detail?'+suserid+'&aid='+value.id_activity;
    
    if(value.posters_url != '' || value.posters_url){
    var img_type =value.posters_url.split('.');
    var img_s = img_type[0]+'-small.'+img_type[1];
    var img = '<img class="tip"  data-img_url='+img_s+' src="'+img_s+'"/>';
    }else{
    var img='';
    }
    
    var imgs='';
    if(value.imgs !=''){
      var imgs =	'';
      if(value.imgs !='')
        var leng = 3-1;
      $.each(value.imgs,function(i,n){
            if(i>=leng)
            return false;
            var img_types =value.imgs[i].split('.');
        value.imgs[i] = img_types[0]+'-small.'+img_types[1];
            imgs += '<img class="tip"  data-img_url='+value.imgs[i]+' src="'+value.imgs[i]+'"/>'+"&nbsp";
            });
    }
    
    //不给默认图片

    var activity_pic = '';
    if(img || imgs) {
      activity_pic = '<p class="activity_pic">'+img+imgs+'</p>';
    }
    var datee = new Date().getTime()/1000;
    var ended = '';
    if(value.end_date > 0)
    if(value.end_date - datee <= 0)
      ended = '<img class="ended" src="/wapi/img/ended.png" src="end"/>';

    $("#thelist").append('<li class="clearfix"  data-url="'+value.id_activity+'" data-detail_url="'+detail+'" id="detail_in" style="position: relative;cursor:pointer">'+ended+
        '<h4><a href="'+detail+'">'+value.name+'</a></h4>'+
              activity_pic +
              '<p class="source_cash left">'+html+
              '<span class="cash_status right"><em>'+price+'</em><a href="javascript:;"><i class="icon-thumbs-up gray"></i>赞'+value.appraise_count+'</a><a href="javascript:;"><i class="icon-comment-alt gray"></i>'+value.review_count+'</a></span>'+
              '</p>'+nick_name+
              '</li>'
    );
            i++;
  });
}

var formatDate = function (time) {
  var date   = new Date(time);
  var year   = date.getYear()+1900;
  var month  = date.getMonth()+1;
  var date   = date.getDate();
  var hour   = date.getHours();
  var minute = date.getMinutes();
  var second = date.getSeconds();
  return   year + "-" + month + "-" + date +"   "+hour+":"+minute+":"+second;
}

//当页面全部加载完毕(这里等待图片全部加载，ajax追加的数据)
function is_load_img(){
var imgList = $('li').find('img');
imgList.each(function(){
    $('img').load(function(){
         if(imgList.length==0){
            setTimeout(function(){
	           myScroll.refresh(); //重新计算高度
	           
	      },500);
       }
      imgList.length --;
    })
  })
}


//加载更多
$('#next_more').live('click',function(){
 nextPage();
})

//进入详情
$('#detail_in').live('click',function(){
	
//用户分享ID
var suserid = (userid !='' && userid !=undefined) ? '&suserid='+userid : '';	
var detail_id = $(this).attr('data-detail_url');
//var detail = detail_id;
window.location.href=detail_id;
})


//图片放大
$('.tip').live('click',function(e){
 e.stopPropagation();
 //e.cancelBubble = true;
 	 
     //全局蒙版	
     $('#show_back_top').addClass('webgame_msgWindow_mask');
     //透明层	
     $('#show_host').addClass('show_back_mian');
     var url_img = this.src.replace('-small', '');
	 var $tip=$('<div id="tip"><div class="t_box"><img id="zhe_img" class="simg" src="'+url_prefix+'/img/loading.gif"/></div></div>');
     $('.show_big_style').prepend($tip);
     //等待图片加载
	 var window_w = (document.body.clientWidth);
     var window_h = (document.body.clientHeight);
     var width_l  = (window_w)/2;
     var height_r = (window_h)/2;
	 var divcss = {
      'text-align': 'center',
      'z-index': 4,
      'top': height_r,
      'position': 'fixed',
      'left': width_l
      };
     $(".show_big_style").css(divcss);
	 compress(url_img);	
	 //return false;
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
   	$('#tip').remove();
	$('#show_back_top').removeClass('webgame_msgWindow_mask');
	$('#show_host').removeClass('show_back_mian');
	
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
//搜索
$('#search').live('click',function(){
 
 window.location.href=base_url+'/community/search';	
})

//赞
$('#goods1').live('click',function(){
	$('#goods').empty();	
	$('#goods1').empty();
	var total = $('#goods').attr('data-good');
	total = parseInt(total)+parseInt(1);
	$('.good').attr('data-good',total);
	$('#goods').append('<i class="iconfont icon iconfont_638">&#58884</i>'+total);
	$('#goods1').append('<i class="iconfont icon iconfont_638">&#58884</i>已赞'+total);
	$(".good").attr("id","changeId");
	$('.good').attr("id","good_yes");
	$(".goodt").attr("id","changeId");
	$('.goodt').attr("id","good_yes");
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
	$('.good').empty();	
	$('.goodt').empty();
	var total = $('.good').attr('data-good');
    if(total>0){
        total = total-1;
    }else{
        total = 0;
    }
	$('.good').attr('data-good',total);
	$('.good').html('<i class="icon-thumbs-up"></i>'+total);
	$('.goodt').html('<i class="icon-thumbs-up"></i>赞'+total);
	$(".good").attr("id","changeId");
	$('.good').attr("id","goods");
	$(".goodt").attr("id","changeId");
	$('.goodt').attr("id","goods1");
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
	 var aid = $(this).attr('data-aid');
	 var detail_url = $(this).attr('data-detail');
	 if(oid !=''){ 	
     	$.ajax({    
            url:base_url+'/community/att_activity',
            data:'oid='+oid+'&aid='+aid,
            async: false,
            dataType: 'json',
            type:'get',
            success:function(data){
            	
            if(data.status == 1)
             {
             //参加活动成功返回详情
             window.location.href=detail_url; 
             }else if(data.status==0){
             window.location.href=data.url;
             }else if(data.status==2){
             //活动已关闭
             url = base_url+'/community/home?oid='+oid; 
             window.location.href=base_url+'/home/error?url='+url+'&tip=活动关闭';
             }else if(data.status==3){
             //用户不存在或为传入OID
             window.location.href=base_url+'/home/error';	
             }else if(data.status==4){
             window.location.href=detail_url; 	
             }
           }
       })
    }else{
    	
    window.location.href=base_url+'/home/error';
 }	
       	
});


function alert_apply(){
    if(confirm('你还不是达人，不能发布活动，去申请达人吧？')){
        window.location.href=base_url+'/user_activity/expert';
    }
}
 	
	
