var OFFSET = 5;
var page = 1;
var PAGESIZE = 10;

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
				myScroll.refresh(); //加载完数据后调用
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
function refresh_new(response){
                var i=0;
                //用户分享ID
                var suserid = (userid !='' || userid !=undefined) ? 'suserid='+userid : '';
                $.each(response.list, function(key, value) {
					var num_style = i%2==1 ? 'white clearfix' : 'clearfix';
					if(bid==90)
					{
					var num_style = i%2==1 ? 'hipigo white clearfix' : 'hipigo clearfix';	
					var class_name = '<li id="show_big'+value.id_activity+'" class="'+num_style+'">';	
					var bn_name = '<h2><a href="/wapi/'+value.id_business+'/1/community/home?oid='+oid+'">' +value.bn_name +'</a></h2>';	
					}else{
					 var class_name = '<li id="show_big'+value.id_activity+'" class="'+num_style+'">';	
					 var bn_name = '';	
					}
					var att_user_url = base_url+'/community/user_att_list?aid='+value.id_activity;
					var detail = '/wapi/'+value.id_business+'/1/community/detail?'+suserid+'&aid='+value.id_activity;
					var img_type =value.posters_url.split('.');
					var img_s = img_type[0]+'-small.'+img_type[1];
					$("#thelist").append(class_name+bn_name+ 
					'<span class="activity_pic left"><img class="tip"  data-img_url='+img_s+' src="'+img_s+'"/></span>'+
					'<span style="cursor:pointer" id="activity_detial_on'+value.id_activity+'" data-url='+detail+' class="activity_mes left">'+
                    '<h4><a href="'+detail+'">'+value.name+'</a></h4>'+
                    '<p><b class="cash left"><em>'+value.join_price+'</em>元</b><b class="operate right"><a href="'+detail+'"><i class="icon-comment-alt"></i>&nbsp;'+value.review_count+'</a>'+
					'<a href="javascript:;" id="att_activity" data-aid='+value.id_activity+' data-detail='+detail+'><i class="iconfont icon iconfont_10">&#983716 </i>'+value.join_count+'</a></b></p>'+
                    '</span></li>');
                    i++;
				});
		
	
}	

//加载更多
$('#next_more').live('click',function(){
 nextPage();
})

//进入详情
$('.activity_mes').live('click',function(){
//用户分享ID
var suserid = (userid !='' && userid !=undefined) ? '&suserid='+userid : '';	
var detail_url = $(this).attr('data-url');
//var detail = base_url+'/community/detail?'+suserid+'&aid='+detail_id+'&state='+state;
window.location.href=detail_url;
})


//关注
$(document).ready(function() {
	$('#bn_att').live('click',function(){
	$('.att_show').empty();
	$(".att_show").attr("id","changeId");
    $(".att_show").attr("id","");
	$('.att_show').html('已关注');
	var total = $('#total').attr('data-total');
	total = parseInt(total)+parseInt(1);
	$('#total').html('关注'+total);
	$.ajax({    
            url:base_url+'/community/bn_att',
            data:'oid='+oid+'&bid='+bid,
            async: true,
            dataType: 'json',
            type:'get',
            success:function(data){
           }
        })	
	});	
	
//图片放大
$('.tip').live('click',function(){
	 
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
 	
	 
});
