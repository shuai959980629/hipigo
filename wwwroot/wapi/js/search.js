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
		if(search_num>0)
		{
		   loaded();	
		}else{
		   $("#pullUp").hide();
		   $("#pullDown").hide();
		}
	});
}, false);

function loaded() {
	var title = $('#textarea_in').val();
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
		base_url+"/community/search_list", {
			"page": page,
			"pagesize": PAGESIZE,
			"bid":bid,
			"title":title,
            "screenwidth":screen.width
		},
		function(response, status) {
       if(response.status==0){
          $('.nosearch').show();
          $("#thelist").hide();
        }
			else if (response.status == 1) {
				$("#thelist").show();
                $('.nosearch').hide();
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
function refresh(title) {
    var title = $('#textarea_in').val();
	page = 1;
	$.get(
		base_url+"/community/search_list", {
			"page": page,
			"pagesize": PAGESIZE,
			"bid":bid,
			"title":title,
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
	var title = $('#textarea_in').val();
	page++;
	$.get(
		base_url+"/community/search_list", {
			"page": page,
			"pagesize": PAGESIZE,
			"bid":bid,
			"title":title,
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
    //用户分享ID
    var suserid = (userid !='' || userid !=undefined) ? 'suserid='+userid : '';
    $.each(response.list, function(key, value) {
        var class_name = '<li id="show_big'+value.id_activity+'">';
        var detail = base_url+'/community/detail?'+suserid+'&aid='+value.id_activity;
        var img_s = value.posters_url;
        if(img_s){
            var img_type =img_s.split('.');
            img_s = img_type[0]+'-small.'+img_type[1];
        }
        var html = '<li id="detail_info" style="cursor:pointer" data-url="'+value.id_activity+'">';
        if(img_s){// || value.object_type == 'community'  zxx加
            html += '<span class="searchli_pic left"><img style="cursor:pointer" class="tip"  data-img_url="'+img_s+'" src="'+img_s+'"/></span>';
        }
        html += '<h3><a href="'+detail+'">'+value.name+'</a></h3></li>';
        $("#thelist").append(html);
    });
}

//加载更多
$('#next_more').live('click',function(){
 nextPage();
})

//当输入框内容有改变时开始搜索
$('#textarea_in').live('input propertychange', function() {
var content = $('#textarea_in').val();
if(content.length>0){
loaded();	
//refresh(content);
}	  
});

//清除输入框
$('.closepop').live('click', function() {
$('#textarea_in').val('');
});

//进入详情
$('#detail_info').live('click',function(){
//用户分享ID
var suserid = (userid !='' && userid !=undefined) ? '&suserid='+userid : '';	
var detail_id = $(this).attr('data-url');
var detail = base_url+'/community/detail?'+suserid+'&aid='+detail_id+'&state='+state;
window.location.href=detail;
})

//图片放大
$('.tip').live('click',function(e){
	 e.stopPropagation();
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


 	
	 

