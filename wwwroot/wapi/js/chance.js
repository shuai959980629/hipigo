$(document).ready(function(){
//获奖判断
var base_url=$('#url').val();
var url_prefix=$('#url_prefix').val();
oid = $('#oid').val();
aid = $('#aid').val();
function chance_in(){
$.ajax({    
            url:base_url+'/chance/set_chance',
            data:'oid='+oid+'&aid='+aid,
            async: true,
            dataType: 'json',
            type:'get',
            beforeSend:function(){
            },
            complete:function(){
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

//显示中奖
function win_yes(json)
{
var next = json.time_nums==1 ? '<button class="left"><b class="left" id="left">再来一次</b></button>' :'';
$('#chance_yes').html('<div class="egg-modal">'+
'<h2>恭喜您！获得礼包一个</h2>'+
'<div class="pop-up clearfix">'+
'<div class="pop-up-con left">'+
json.cue+
'</div>'+
'<button class="seegift right"><a href="'+base_url+'/activity/gifts?aid='+aid+'&oid='+oid+'"><img src="'+url_prefix+'/img/seegift.png" alt="查看礼包"/></a></button>'+
'</div>'+
'<div class="pop-up-bt clearfix">'+next+'<button class="right"><b class="left"><a href="'+base_url+'/home/active_details/'+aid+'">返回首页</a></b><em class="arrow-right"></em></button>'+
'</div>'+
'</div>');
	
}

//显示未中奖
function win_no(json)
{
var next = json.time_nums==1 ? '<button class="left"><b class="left" id="left">再来一次</b></button>' :'';	
$('#chance_no').html('<div id="chance_no" class="egg-modal">'+
'<h2>'+json.cue+'</h2>'+
'<div class="pop-up-bt clearfix">'+next+'<em class="arrow-right"></em></button><button class="right"><b class="left"><a href="'+base_url+'/home/active_details/'+aid+'">返回首页</a></b><em class="arrow-right"></em></button>'+
'</div>'+
'</div>');
	
}

//超过次数
function win_out(json)
{
$('#chance_out').append('<div class="egg-modal">'+
'<h2>今天砸蛋机会用完啦，明天再来吧！</h2>'+
'<div class="pop-up-bt clearfix">'+
'<button class="right"><b class="left"><a href="'+base_url+'/home/active_details/'+aid+'">返回首页</a></b><em class="arrow-right"></em></button>'+
'</div>'+
'</div>');
	
}

//砸蛋
$('.span4 img').live('click',function(){
	    src = $(this)[0].src;
	    img_num = $(this).attr("data-id");
	    $('#chance_show').addClass("webgame_msgWindow_mask");
	    chance_in();
	    //$("#hammer").empty();
	    //$('#hammer').removeAttr("style");
	    explosion(src,img_num);
	    setTimeout(show_img_in,3000);
});
function show_img_in()
{
	$('#img_span'+img_num).append('<img src="">');
	$('#img_span'+img_num+' img').attr('src',src);
	$('#img_span'+img_num+' img').attr('data-id',img_num);
}
//返回
$('.right').live('click',function(){
		('#chance_show').addClass("webgame_msgWindow_mask");
	});
//再来一次
$('#left').live('click',function(){
	    $('#chance_yes').empty();
		$('#chance_no').empty();
		$('#chance_out').empty();
		$('#chance_error').hide();
		//window.location.reload();
	   $('#chance_show').removeClass("webgame_msgWindow_mask");
	});

//碎蛋
var fx  = {
	buffer : function(obj, cur, target, fnDo, fnEnd, fs){
		if(!fs)fs=6;
		var now={};
		var x=0;
		var v=0;
		if(!obj.__last_timer)obj.__last_timer=0;
		var t=new Date().getTime();
		if(t-obj.__last_timer>20)
		{
			fnMove();
			obj.__last_timer=t;
		}
		clearInterval(obj.timer);
		obj.timer=setInterval(fnMove, 20);
		function fnMove(){
			v=Math.ceil((100-x)/fs);			
			x+=v;			
			for(var i in cur)
			{
				now[i]=(target[i]-cur[i])*x/100+cur[i];
			}						
			if(fnDo)fnDo.call(obj, now);
			
			if(Math.abs(v)<1 && Math.abs(100-x)<1)
			{
				clearInterval(obj.timer);
				if(fnEnd)fnEnd.call(obj, target);
			}
		}
	},
	
	flex : function(obj, cur, target, fnDo, fnEnd, fs, ms){
		var MAX_SPEED=16;
		
		if(!fs)fs=6;
		if(!ms)ms=0.75;
		var now={};
		var x=0;	//0-100
		
		if(!obj.__flex_v)obj.__flex_v=0;
		
		if(!obj.__last_timer)obj.__last_timer=0;
		var t=new Date().getTime();
		if(t-obj.__last_timer>20)
		{
			fnMove();
			obj.__last_timer=t;
		}
		
		clearInterval(obj.timer);
		obj.timer=setInterval(fnMove, 20);
		
		function fnMove(){
			obj.__flex_v+=(100-x)/fs;
			obj.__flex_v*=ms;

			if(Math.abs(obj.__flex_v)>MAX_SPEED)obj.__flex_v=obj.__flex_v>0?MAX_SPEED:-MAX_SPEED;
			
			x+=obj.__flex_v;
			
			for(var i in cur)
			{
				now[i]=(target[i]-cur[i])*x/100+cur[i];
			}
			
			
			if(fnDo)fnDo.call(obj, now);
			
			if(Math.abs(obj.__flex_v)<1 && Math.abs(100-x)<1)
			{
				clearInterval(obj.timer);
				if(fnEnd)fnEnd.call(obj, target);
				obj.__flex_v=0;
			}
		}
	},
	linear : function (obj, cur, target, fnDo, fnEnd, fs){
		if(!fs)fs=50;
		var now={};
		var x=0;
		var v=0;
		
		if(!obj.__last_timer)obj.__last_timer=0;
		var t=new Date().getTime();
		if(t-obj.__last_timer>20)
		{
			fnMove();
			obj.__last_timer=t;
		}
		
		clearInterval(obj.timer);
		obj.timer=setInterval(fnMove, 20);
		
		v=100/fs;
		function fnMove(){
			x+=v;
			
			for(var i in cur)
			{
				now[i]=(target[i]-cur[i])*x/100+cur[i];
			}
			
			if(fnDo)fnDo.call(obj, now);
			
			if(Math.abs(100-x)<1)
			{
				clearInterval(obj.timer);
				if(fnEnd)fnEnd.call(obj, target);
			}
		}
	},
	
	stop:function (obj){
		clearInterval(obj.timer);
	},
	
	move3 : function(obj, json, fnEnd, fTime, sType){
		var addEnd=fx.addEnd;
		
		fTime||(fTime=1);
		sType||(sType='ease');
		
		setTimeout(function (){
			Utils.setStyle3(obj, 'transition', sprintf('%1s all %2', fTime, sType));
			addEnd(obj, function (){
				Utils.setStyle3(obj, 'transition', 'none');
				if(fnEnd)fnEnd.apply(obj, arguments);
			}, json);
			
			setTimeout(function (){
				if(typeof json=='function')
					json.call(obj);
				else
					Utils.setStyle(obj, json);
			}, 0);
		}, 0);
	
	}

};

//监听css3运动终止
(function (){
	var aListener=[];	//{obj, fn, arg}
	if(!Modernizr.csstransitions)return;	
	if(window.navigator.userAgent.toLowerCase().search('webkit')!=-1)
	{
		document.addEventListener('webkitTransitionEnd', endListrner, false);
	}
	else
	{
		document.addEventListener('transitionend', endListrner, false);
	}
	
	function endListrner(ev)
	{
		var oEvObj=ev.srcElement||ev.target;
		//alert(aListener.length);
		for(var i=0;i<aListener.length;i++)
		{
			if(oEvObj==aListener[i].obj)
			{
				aListener[i].fn.call(aListener[i].obj, aListener[i].arg);
				aListener.remove(aListener[i--]);
			}
		}
	}
	
	fx.addEnd=function (obj, fn, arg)
	{
		if(!obj || !fn)return;
		aListener.push({obj: obj, fn: fn, arg: arg});
	}
})();

//爆炸效果
function explosion(src,img_num){
	var now=0;
	var ready=true;
	var W=105;
	var H=165;	
	var $img = $(".span4");	
	var oDiv = $img.get(img_num);
	var next =function(){
		return (now+1)%3;
	}
		if(!ready)return;
		ready=false;
			
		var R=4;
		var C=7;		
		var cw=W/2;
		var ch=H/2;
		
		oDiv.innerHTML='';
		oDiv.style.background='';
		var aData=[];
		
		var wait=R*C;
		
		for(var i=0;i<R;i++){
			for(var j=0,k=0;j<C;j++,k++)
			{
				aData[i]={left: W*j/C, top: H*i/R};
				var oNewDiv=$('<div>');
				oNewDiv.css({
					position: 'absolute',					
					width:Math.ceil(W/C)+'px', 
					height: Math.ceil(H/R)+'px',
					background: 'url('+src+')'+-aData[i].left+'px '+-aData[i].top+'px no-repeat',					
					left: aData[i].left+'px',
					top: aData[i].top+'px'	
				});

								
				oDiv.appendChild(oNewDiv[0]);
				
				var l=((aData[i].left+W/(2*C))-cw)*Utils.rnd(2,3)+cw-W/(2*C);
				var t=((aData[i].top+H/(2*R))-ch)*Utils.rnd(2,3)+ch-H/(2*R);
				
				setTimeout((function (oNewDiv,l,t){
					return function ()
					{
						fx.buffer(
							oNewDiv,
							{	left: oNewDiv.offsetLeft, 
								top: oNewDiv.offsetTop	,
								opacity: 100,
								x:0,
								y:0,
								z:0,
								scale:1,
								a:0
							},
							{	left: l,
								top: t,
								opacity: 0,
								x:Utils.rnd(-180, 180),
								y:Utils.rnd(-180, 180),
								z:Utils.rnd(-180, 180),
								scale:Utils.rnd(1.5, 3),
								a:1
							},
							function (now){								
								this.style.left=now.left+'px';
								this.style.top=now.top+'px';
								this.style.opacity=now.opacity/100;
								Utils.setStyle3(oNewDiv, 'transform', 'perspective(500px) rotateX('+now.x+'deg) rotateY('+now.y+'deg) rotateZ('+now.z+'deg) scale('+now.scale+')');
							}, function (){
								setTimeout(function (){
									oDiv.removeChild(oNewDiv);
								}, 200);
								if(--wait==0)
								{
									ready=true;
									now=next();
								}
							}, 10
						);
					};
				})(oNewDiv[0],l,t), Utils.rnd(0, 200));
			}
		}
}

var Utils = {
	setStyle :function(obj,json){
		if(obj.length)
			for(var i=0;i<obj.length;i++) Utils.setStyle(obj[i], json);
		else
		{
			if(arguments.length==2)
				for(var i in json) obj.style[i]=json[i];
			else
				obj.style[arguments[1]]=arguments[2];
		}
	},
	setStyle3 : function(obj, name, value){
		obj.style['Webkit'+name.charAt(0).toUpperCase()+name.substring(1)]=value;
		obj.style['Moz'+name.charAt(0).toUpperCase()+name.substring(1)]=value;
		obj.style['ms'+name.charAt(0).toUpperCase()+name.substring(1)]=value;
		obj.style['O'+name.charAt(0).toUpperCase()+name.substring(1)]=value;
		obj.style[name]=value;
	},
	rnd  : function(n,m){
	   return Math.random()*(m-n) + n ;
	}

}


})
           
