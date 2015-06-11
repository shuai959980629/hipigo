var OFFSET = 5;
var page = 1;
var PAGESIZE = 25;
var base_url = '/wapi/90/1';
var myScroll,
	pullDownEl, pullDownOffset,
	pullUpEl, pullUpOffset,
	generatedCount = 0;
var maxScrollY = 0;
var func;

var hasMoreData = false;
//阻止手机的触摸默认事件
document.addEventListener('touchmove', func = function(e) {
	e.preventDefault();
}, false);

//监听页面一载入就立即执行
document.addEventListener('DOMContentLoaded', function() {
	$(document).ready(function() {
//		if(activity_num > 0){
            loaded();
//        }else{
//            $("#pullUp").hide();
//            $("#pullDown").hide();
//        }
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
		base_url+"/user_activity/get_activity", {
			"page": page,
			"pagesize": PAGESIZE,
			"screenwidth":screen.width,
            "page_type":2,
            "uid":userid==''?phone:userid,
//            "identity":identity,
//            'phone':phone,
            'id_join':id_join,
            'type':$('#page_type').val()//判断当前页面是我的资源还是我的活动
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
			}

            if(activity_num <= 0){
                $("#pullUp").hide();
                $("#pullDown").hide();
            }
		},
		"json");
}
//下拉刷新
function refresh() {
	page = 1;
	$.get(
		base_url+"/user_activity/get_activity", {
			"page": page,
			"pagesize": PAGESIZE,
			"screenwidth":screen.width,
            "page_type":2,
            "uid":userid==''?phone:userid,
//            "identity":identity,
//            'phone':phone,
            'id_join':id_join,
            'type':$('#page_type').val()//判断当前页面是我的资源还是我的活动
		},
		function(response, status) {
			if (status == "success") {
				$("#thelist").empty();

                if(!myScroll){

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
                }
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
			}
		},
		"json");
}
//上拉分页
function nextPage() {
	page++;
	$.get(
		base_url+"/user_activity/get_activity", {
			"page": page,
			"pagesize": PAGESIZE,
			"screenwidth":screen.width,
            "page_type":2,
            "uid":userid==''?phone:userid,
//            "identity":identity,
//            'phone':phone,
            'id_join':id_join,
            'type':$('#page_type').val()//判断当前页面是我的资源还是我的活动
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
function refresh_new(response) {
    var day_x = '';
    if(response.type == 1){
        if(response.list.length <= 0){
            var html1 = '<li>你还没有自己的资源信息！</li>';
            $("#thelist").html(html1);
        }
        $.each(response.list, function(key, value) {
            var day = '', month = '';
            day = value.mycreated.substring(8, 10);
            month = value.mycreated.substring(5, 7);
            if(month<10){
                month = month.substring(1, 2);
            }
            var html1 = '<li><span class="left time">';
            if(day != day_x) {
                day_x = day;
                html1 += '<b>' + day +'</b>'+month+ '月';
            }
            else {
                html1 += '<b>&nbsp;</b>';
            }

            var codes = '';
            if(value.code)
                $.each(value.code,function(i,n){
                    if(i>=2)
                        return false;
                    codes += '<em class="user_code">'+n+"</em>";
                });

            if(value.code.length>2){
                var code_html = code_mores(value.code);
                var code_more = '<a class="right" id="code_more" href="javascript:;" att="'+code_html+'">查看更多</a>';
            }else{
                var code_more ='';
            }

//            <a onclick="delete_resource(this,'+value.id_resource+')">X</a>
//            var code_ = empty(value.code) ? '':value.code;
            html1 += '</span><span class="user_activity_mes right">'+
                '<a href="javascript:void(0)"><h4>'+value.resource_title+'</h4></a>'+
                '<p class="code">'+codes+code_more+'</p>'+
                '</span>'+
                '</li>';

            $("#thelist").append(html1);
        });
    }else{
        $.each(response.list, function(key, value) {
            var codes = '';

            if(value.codes)
            $.each(value.codes,function(i,n){
                if(i>=2)
                    return false;
                codes += '<em class="user_code">'+n+"</em>";
            });

            if(value.codes.length>2){
                var code_html = code_mores(value.codes);
                var code_more = '<a class="right" id="code_more" href="javascript:;" att="'+code_html+'" data-aid="'+value.id_activity+'">查看更多</a>';
            }else{
                var code_more ='';
            }
            var day = '', month = '';
            day = value.created.substring(8, 10);
            month = value.created.substring(5, 7);
            if(month<10){
                month = month.substring(1, 2);
            }
            var html1 = '<li><span class="left time">';
            if(day != day_x) {
                day_x = day;
                html1 += '<b>' + day +'</b>'+month+ '月';
            }
            else {
                html1 += '<b>&nbsp;</b>';
            }

            var url = base_url+'/community/publish?aid='+value.id_activity;
            html1 += '</span><span class="user_activity_mes right">'+
                '<a href="'+value.url_link+'"><h4>'+value.title+'</h4></a>';//<a href="'+url+'">编辑</a>'+

            if(cook_userid == value.id_business && value.object_type == 'user'){
                html1 += '<a href="'+url+'">编辑</a>';
            }

            html1 += '<p class="code">'+codes+code_more+'</p>'+
                '</span>'+
                '</li>';

            /*if(cook_userid != ''){
             if (cook_userid == userid || cook_userid == phone){
             html+='<p class="user_code right">'+value.code+'</p>';
             }
             }*/
            //html+='</span></li>';
            $("#thelist").append(html1);
        });
    }
}


////删除资源信息
//function delete_resource(obj,id_resource){
//    if(confirm('你确定要删除这个购买信息')){}
//    $.ajax({
//        url:base_url+'/user_activity/delete_resource',
//        data:'id_resource='+id_resource,
//        async: false,
//        dataType:'json',
//        type:'get',
//        success:function(data){
//            if(data.status==1){
//                $(obj).parent().parent().remove();
//            }
//        }
//    })
//}

//列表验证码
function code_mores(value){
      code_m = '';
      $.each(value,function(i,n){
      code_m += '<li>'+value[i]+'</li>';
    });
    return code_m;
}
//查看更多验证码
$('#code_more').live('click',function(){
//var aid = $(this).attr('data-aid');
//$.ajax({
//            url:base_url+'/community/find_code_more',
//            data:'aid='+aid,
//            async: false,
//            dataType:'json',
//            type:'get',
//            success:function(data){
//            if(data.status==1){
//            code_m = '';
//            $.each(data.list,function(key, value){
//            code_m += '<li>'+value.code+'</li>';
//        })
//       }
//    }
//})
	
$('#user_code_more').show();
    document.removeEventListener('touchmove', func);
$('#yzm').append('<ul>'+$(this).attr('att')+
'</ul>'+
'<span class="right"><a href="javascript:;" class="closepop"></a></span>');	
})
//关闭查看更多
$('.closepop').live('click',function(){
$('#yzm').empty();
$('#user_code_more').hide();
    document.addEventListener('touchmove', func = function(e) {
        e.preventDefault();
    }, false);
})

//当页面全部加载完毕(这里等待图片全部加载)
document.onreadystatechange = function(){   
        if(document.readyState=="complete")   
        {   
           if(activity_num > 0){ 
            setTimeout(function(){
	        myScroll.refresh();
	      },500);
	    }  
	  } 
}

//加载更多
$('#next_more').live('click',function(){
 nextPage();
})


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
     	var height_r = parseInt((window_h-m_h)/2);
     	
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
  //载入大图
  var $tip=$('<div id="tip"><div class="t_box"><img id="zhe_img" class="simg" src="'+url_img+'"/></div></div>');
  $('.show_big_style').prepend($tip);
  $('#tip').show(200); 
  if(h>window_h && w>window_w){
	 if((w/window_w) > (h/window_h)){
	 var divcss = {
      'width':window_w
      };
      $("#zhe_img").css(divcss);	
	 }else{
	  var divcss = {
      'height':window_h
      };
      $("#zhe_img").css(divcss);	
	 }
   }
  if(h<window_h && w>window_w){
	 var divcss = {
      'width':window_w
      };
      $("#zhe_img").css(divcss);		
	 }
	 if(h>window_h && w<window_w){
	 var divcss = {
      'height':window_h
      };
      $("#zhe_img").css(divcss);		
	 }
     var divcss = {
      'text-align': 'center',
      'z-index': 4,
      'top': height_r,
      'position': 'fixed',
      'left': width_l
      };
      $(".show_big_style").css(divcss);
     }
  }

//我的资源信息
function my_resource(obj){
    $(obj).html('我的活动');
    $(obj).attr('onClick','my_activity(this)');
    $('#page_type').val(1);
    $("#thelist").html('');
    refresh();

}


//我的活动信息
function my_activity(obj){
    $(obj).html('我的资源');
    $(obj).attr('onClick','my_resource(this)');
    $('#page_type').val(0);
    $("#thelist").html('');
    refresh();
}

