$(document).ready(function(){
abc_array = new Array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","W","Z");
answer = new Array();
//答题页
$('.start').live('click',function(){
minus_one();
add_show_laoding();	
if(max_num_time){
$('.main').remove();
$('#contents').addClass('question');
contents();
if(data){
  $('#chance_show').removeClass("webgame_msgWindow_mask");
  len = data.item.length;
  jion_id = jion_id;
  times = data.times;
  $('#title').addClass("title");
  $('#title').html('剩余时间：<em id="Quiz_Time">'+times+'</em>秒');
  $('#Quiz_Time').html(times);
  json = eval(data.item);
  chance_in();
  setInterval(changeTime,1000); 	
}

}else{
	
$('#show_loading').empty();
$('#show_loading').addClass("qusition-modal");	
$('#show_loading').append('<h3>您今天答题次数用完啦！</h3>'+
'<h4><a href="'+base_url+'/home/active_details/'+aid+'">返回首页</a></h4>');	
}
})
function add_show_laoding()
{
$('#chance_show').addClass("webgame_msgWindow_mask");	
$('#show_loading').addClass("qusition-modal");	
$('#show_loading').append('<h3>正在努力加载...</h3>'+
'<h4><img src="'+url_prefix+'img/loading.gif" alt="请稍等..."/></h4>');
	
}	
//显示题目
function chance_in(){
	var num = $('.button').attr('data-num');
	num = isNaN(num) ? 0 : num; 
	if(num<len)
	{
	var str='';
	var index=0;
	var next_num = parseInt(num)+parseInt(1);
	$('.questioncon').remove();
	$('.button').remove();
    $.each(json[num].result,function(i,n){
    	var key = i.substring(0,i.length-1);
        str += '<a href="javascript:void(0);"><dd data-as_id="'+key+'">'+abc_array[index]+'.'+json[num].result[i]+'</dd></a>';
        index++;
        });	             
   $('#question').append('<div class="questioncon">\
    <dl>\
        <dt data-sj_id="'+json[num].id_subject+'"><em>第'+next_num+'题.</em>'+json[num].title+'</dt>'+str+'\
       </dl>\
</div>\
<div class="button" data-num='+next_num+'><button id="sub">下一题</button>\
</div>');
$('.questioncon dl dd').css({"background-color":'#f3f3f3'});
    if(num==len-1)
	{
	   $('#sub').html('确认');  
	}
    }else{
    	is_win();
    	var next_num = parseInt(next_num)+parseInt(1);
    	$('.button').attr('data-num',next_num);
    }
}
$('#sub').live('click',function(){
	
	
	 chance_in();
});
//选择答案
$('.questioncon dl dd').live('click',function(){
	    $('.questioncon dl dd').css({"background-color":'#f3f3f3'});
	    $(this).css({"background-color":'#a8f0d5'});
	    var as_id = $(this).attr('data-as_id');
	    var sj_id = $('.questioncon dl dt').attr('data-sj_id');
	    var num = $('.button').attr('data-num')-1;
	    answer[num]=Array();
	    answer[num][0]=sj_id;
	    answer[num][1]=as_id;
});
//检测是否中奖
function is_win(){
	var ts = $('#Quiz_Time').html();
	var num = $('.button').attr('data-num');
	var string='';
	if(ts>=0)
	{
	var complete_time = times-ts;	
	}else{
	var complete_time = parseInt(times)+parseInt(Math.abs(ts));	
	}
	var mao='"';
	$.each(answer,function(i,n){
		if(answer[i] instanceof Array)
		{
		string += mao+answer[i][0]+mao+':'+answer[i][1]+',';	
		}
	 
	})
	string=string.substring(0,string.length-1);
	string = '{'+string+'}';
	$('#chance_show').addClass("webgame_msgWindow_mask");
$.ajax({    
	       
            url:base_url+'/chance/set_chance',
            data:'oid='+oid+'&aid='+aid+'&answer='+string+'&complete_time='+complete_time+'&total_num='+num+'&jion_id='+jion_id,
            async: true,
            dataType: 'json',
            type:'get',
            beforeSend:function(){
            //网络延时	
            $('#loading').show();
            },
            complete:function(){
            $('#loading').hide();
            },
            success:function(data){
            switch(data.status)
            {
            	case 0:
            	 win_no(eval(data.data));
            	 break;
            	case 1:
            	 win_yes(eval(data.data));
            	 break; 
            	case 3:
            	 win_out(eval(data.data));
            	 break;   
            }	
          
        }
   })
}
//开始答题次数减一
function minus_one()
{
	$.ajax({    
	        url:base_url+'/activity/minus_one',
            data:'oid='+oid+'&aid='+aid,
            async: false,
            dataType: 'json',
            type:'get',
            beforeSend:function(){
            //网络延时	
            $('#loading').show();
            },
            complete:function(){
            $('#loading').hide();
            },
            success:function(data){
            max_num_time = (data.status==1) ? 1: 0;
            jion_id=data.jion_id;
            }
           })
           
}
function changeTime()
{
    var ts = $('#Quiz_Time').html();
    var num = $('.button').attr('data-num');
    if(num-1<len){
    ts = ts - 1;
    $("#Quiz_Time").html(ts);
	}
}

//显示题目
function contents()
{
$('#contents').html('<div class="main">'+
'<div class="container">'+
'<div id="title"></div>'+
'<div id="question">'+
'</div>'+
'</div>'+
'<div id="chance_yes">'+
'</div>'+	
'<div id="chance_no">'+
'</div>'+
'<div id="chance_out">'+
'</div>'+
'<div id="loading" class="qusition-modal" style="display:none;">'+
'<h3>正在努力加载...</h3>'+
'<h4><img src="'+url_prefix+'img/loading.gif" alt="请稍等..."/></h4>'+
'</div>'+
'<div id="chance_show" class="webgame_msgWindow_mask"></div>'+
'<footer>powered by <a href="http://www.it008.com" target="_blank">赏金猎人</a></footer>'+
'</div>');	
}
//中奖
function win_yes(json)
{
$('#chance_yes').append('<div class="qusition-modal">\
<h3>'+json.cue+'</h3>\
<h4>正确：'+json.accuracy+'题<br>时间：'+json.time_out+'秒<br>正确率：'+json.chance+'</h4>\
<p><button><a href="'+base_url+'/activity/gifts?aid='+aid+'&oid='+oid+'">查看礼包</a><em class="arrow-right"></em></button></p>\
<div class="qusition-popup-bt clearfix">\
<button class="blue left"><b class="left"><a href="'+base_url+'/activity/one_stop?aid='+aid+'&oid='+oid+'">再来一次</a></b><em class="arrow-right"></em></button><button class="yellow right"><b class="left"><a href="'+base_url+'/home/active_details/'+aid+'">返回首页</a></b><em class="arrow-right"></em></button>\
</div>\
</div>');
$('#chance_show').addClass("webgame_msgWindow_mask");	
}
//未中奖
function win_no(json)
{
$('#chance_no').append('<div class="qusition-modal">\
<h3>'+json.cue+'</h3>\
<h4>正确：'+json.accuracy+'题<br>时间：'+json.time_out+'秒<br>正确率：'+json.chance+'</h4>\
<div class="qusition-popup-bt clearfix">\
<button class="blue left"><b class="left"><a href="'+base_url+'/activity/one_stop?aid='+aid+'&oid='+oid+'">再来一次</a></b><em class="arrow-right"></em></button><button class="yellow right"><b class="left"><a href="'+base_url+'/home/active_details/'+aid+'">返回首页</a></b><em class="arrow-right"></em></button>\
</div>\
</div>');
$('#chance_show').addClass("webgame_msgWindow_mask");	
}
//超过次数
function win_out(json)
{
$('#chance_out').append('<div class="qusition-modal">\
<h3>今天机会用完啦！</h3>\
<h4>明天再来吧</h4>\
<div class="qusition-popup-bt clearfix">\
<button class="blue left"><b class="left"><a href="'+base_url+'/home/active_details/'+aid+'">关闭</a></b><em class="arrow-right"></em></button><button class="yellow right"><b class="left"><a href="'+base_url+'/home/active_details/'+aid+'">返回首页</a></b><em class="arrow-right"></em></button>\
</div>\
</div>');
$('#chance_show').addClass("webgame_msgWindow_mask");	
}

})
           
