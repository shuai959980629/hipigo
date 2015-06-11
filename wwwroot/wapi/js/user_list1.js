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
		if(att_user==1)
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
		base_url+get_url, {
			"page": page,
			"pagesize": PAGESIZE,
			"aid":aid,
			"userid":userid
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
				refresh_new(response);   //刷新页面
				if(response.list.length >= PAGESIZE){
				 $("#next_more").append('<a class="more left" href="#">查看更多</a>');		
				}else{
				 $("#next_more").empty();
				}
				myScroll.refresh();      //加载完数据后调用
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
		base_url+get_url, {
			"page": page,
			"pagesize": PAGESIZE,
			"aid":aid,
			"userid":userid
		},
		function(response, status) {
			if (status == "success") {
				$("#thelist").empty();

				myScroll.refresh();

				if (response.list.length < PAGESIZE) {
					hasMoreData = false;
					$("#pullUp").hide();
				} else {
					//hasMoreData = true;
					//$("#pullUp").show();
				}

				refresh_new(response);   //刷新页面
				myScroll.refresh();      //加载完数据后调用

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
		base_url+get_url, {
			"page": page,
			"pagesize": PAGESIZE,
			"aid":aid,
			"userid":userid
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

				refresh_new(response);   //刷新页面
				myScroll.refresh();      //加载完数据后调用
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


//刷新页面
function refresh_new(response){
  $.each(response.list, function(key, value) {
  	    if(value.lock==1){
  	     var lock = '<i class="icon-lock"></i>';
  	     var onlock='<a class="lock">找回凭证</a>';
  	    }else{
  	      var lock = '';
  	      var onlock='<a data-id_join="'+value.id_join+'" class="lock" id="find_out"><font color="#f24949">找回凭证</font></a>';	
  	    }
  	    var lock = value.lock==1 ? '<i class="icon-lock"></i>' : '';
        var name = '匿名';
        if(value.nick_name){
          name = value.nick_name;
        }
        if(value.nick_name=='' && value.identity=='visitor'){
          name = value.cellphone;
        }
        var created = value.created;
        if(value.created){
            created = created.substring(0,11);
        }
        if((value.id_user == userid) && userid !='' && identity !='visitor') {
          //当前用户，并且不是准用户	
            
        	code_more(value.code);
        	var codes = '';
        	$.each(value.code,function(i,n){
        		if(i>=1)
        		return false;
        		codes += value.code[i]+"&nbsp&nbsp";
        		})
        		
            var nick_name = '<i class="usergreen">'+name+'</i>';
            var code = '<em class="user_code">'+codes+'</em></em></p>';
            var del = '<a class="right" id="code_more" href="javascript:;"></a>';
        
          }else if(identity=='visitor'){ 
            //准用户
         	var nick_name= '<i>'+value.cellphone+'</i><span id="lock'+value.id_join+'">'+lock+'</span>';
            var code = created+'获得';
            var del = '';
         
          }else{
           
           nick_name='<i>'+name+'</i>';
           var code= created+'获得';
           var del = '';
           
        }
       var user_code = identity=='visitor' ? 'id_join='+value.id_join : 'userid='+value.id_user;
       
       $("#thelist").append('<li class="clearfix">'+
        '<a href="'+base_url+'/user_activity/index?'+user_code+'"><span class="leaguer_head left"><img src="'+value.photo+'"/></span></a>'+
        '<span class="leaguer_msn right">'+
        '<p class="leaguer_msn_top">'+
        '<a href="'+base_url+'/user_activity/index?'+user_code+'"><em class="username left"   style="cursor:pointer;">'+nick_name+'</em></a>'+del+'</p>'+
        '<p class="leaguer_msn_bottom">'+code+'</p></span>'+
        '</li>');
    });
}

//列表验证码
function code_more(value){
      code_m = '';
      $.each(value,function(i,n){
      code_m += '<li>'+value[i]+'</li>';
    })	
}
//查看更多验证码
$('#code_more').live('click',function(){
$('#user_code_more').show();	
$('.modalv1').append('<ul>'+code_m+
'</ul>'+
'<span class="right"><a href="javascript:;" class="closepop"></a></span>');	
})
//关闭查看更多
$('.closepop').live('click',function(){
$('.modalv1').empty();
$('#user_code_more').hide();	
})

//加载更多
$('#next_more').live('click',function(){
 nextPage();
})

//找回凭证
$('#find_out').live('click',function(){
var id_join = $(this).attr('data-id_join');
$('#modalv1').attr('data-id_join',id_join);
$('#modalv1').fadeIn();
$('#show_back_top').addClass('webgame_msgWindow_mask');
})

//取消
$('#come_back').live('click',function(){
$('#modalv1').fadeOut();
$('#show_back_top').removeClass('webgame_msgWindow_mask');
})

//提交找回凭证
$('#find_code').live('click',function(){
	
var phone = $('#phone').val();
var id_join = $('#modalv1').attr('data-id_join');
$.ajax({    
            url:base_url+'/community/find_code',
            data:'aid='+aid+'&id_join='+id_join+'&phone='+phone,
            async: true,
            dataType:'json',
            type:'get',
            success:function(data){
             if(data.status==1){
             	//找回成功
             	$('#modalv1').fadeOut();
             	$('#show_back_top').removeClass('webgame_msgWindow_mask');
                 window.location.reload();
             }else if(data.status==0){
             	//超过次数
             	$('#modalv1').hide();
             	$('#show_back_top').removeClass('webgame_msgWindow_mask');
             	prompt('3次输入错误已被锁定');
             	//锁定
             	$('#lock'+id_join).append('<i class="icon-lock"></i>');
             	$('#onlock'+id_join).empty();
             	//$('#onlock'+id_join).html('<a class="lock">找回凭证</a>');
             }else if(data.status==2){
             	//找回失败
             	$('#modalv1').hide();
             	$('#show_back_top').removeClass('webgame_msgWindow_mask');
             	prompt('手机号码输入错误');
            }else{
            	
            }
           }
          
       })	  

})

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

//删除成员
 $('.att_del').live('click',function(){
 	   if(confirm("确定删除吗")){
         var id_join = $(this).attr('data-id_join');
         var aid = $(this).attr('data-aid');
         var user_oid = $(this).attr('data-oid');
         //移除当前用户
          $('#del_att'+id_join).remove();
       	  $.ajax({    
            url:base_url+'/community/att_del',
            data:'oid='+oid+'&aid='+aid+'&id_join='+id_join+'&user_oid='+user_oid,
            async: true,
            dataType:'json',
            type:'get',
            success:function(data){
            refresh();
            }
          })	  
        }else{
          
        }
  });
 