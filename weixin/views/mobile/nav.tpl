
<!--{if $screenURL == 'community/home' || 
        $screenURL == 'square/square_index' || 
        $screenURL == 'user_activity/home' ||
        $screenURL == 'user/login' ||
        $screenURL == 'user/register'}-->

<nav class="header-secondary">
<!--{if $screenURL == 'community/home'}-->
<a href="javascript: void(0);" class="active">社区</a>
<!--{else}-->
<a href="<!--{$url}-->/community/home">社区</a>
<!--{/if}-->

<!--{if $screenURL == 'square/square_index'}-->
<a href="javascript: void(0);" class="active">
<!--{else}-->
<a href="<!--{$url}-->/square/square_index">
<!--{/if}-->

<span class="piazzafont relative">广场
<!--{if $read.life || $read.resource || $read.business || $read.expert}-->
<em class="reddot"></em><!--{/if}--></span></a>

<!--{if $userid}-->
<!--{if $screenURL == 'user_activity/home' }-->
<a href="javascript: void(0);" class="active">
<!--{else}-->
<a href="<!--{$url}-->/user_activity/home" >
<!--{/if}-->

<span class="piazzafont relative">个人中心
<!--{if $read.resource_by || $read.activity_by || $read.wallet || $read.activity_join}-->
<em class="reddot"></em>
<!--{/if}--></span></a>

<!--{else}-->
<!--{if $screenURL == 'user/register'}-->
<a href="javascript: void(0);" class="active">注册</a>
<!--{else}-->

<!--{if $screenURL == 'user/login'}-->
<a href="javascript: void(0);" class="active">登录</a>
<!--{else}-->
<a href="<!--{$url}-->/user/login" >登录</a>
<!--{/if}-->

<!--{/if}-->

<!--{/if}-->
</nav>

<!--{else}-->
<header>

<nav class="left">
<!--{if $backURL || $backTitle}-->

	<!--{if $backTitle==1}-->
	<a href="/wapi/90/0/community/home" data-target="back"><i class="iconfont icon iconfont_10">&#983698;</i>社区</a>
	<!--{else}-->
	<a href="<!--{$backURL}-->" ><i class="iconfont icon iconfont_10">&#983698;</i><!--{$backTitle}--></a>
	<!--{/if}-->

<!--{else}-->
<a href="javascript: void(0)" data-target="back"><i class="iconfont icon iconfont_10">&#983698;</i>返回</a>
<!--{/if}-->
</nav>

<h1 class="title"><!--{$screenTitle}--></h1>

<!--{if $screenURL == 'resource/bylist'}-->
<nav class="right"><a href="<!--{$url}-->/resource/index">购买资源</a></nav>
<!--{elseif $screenURL == 'community/publish'}-->
<nav class="right"><a class="join" onclick="next();">下一步</a></nav>
<!--{elseif $screenURL == 'user_activity/expert'}-->
<nav class="right"><a href="javascript:void(0);" class="join right">申请</a></nav>
<!--{elseif $screenURL == 'user/changepwd' || 
        $screenURL == 'user/findpwd' || 
        $screenURL == 'user/bind_phone' || 
        $screenURL == 'user/edit_phone'}-->
<nav class="right"><a id="authrize">确定</a></nav>
<!--{elseif $screenURL == 'community/detail'}-->
<nav class="right"><a href="#" id="share_hit">分享</a></nav>
<!--{elseif $screenURL == 'user_activity/bylist' || $screenURL == 'community/coollife'}-->
<nav class="right"><a href="javascript: void(0);" id="filter_activity">筛选</a></nav>
<!--{elseif $screenURL == 'user_activity/user_info'}-->
<nav class="right"><a href="#" id="save_btn">保存</a></nav>
<!--{/if}-->

</header>
<!--{/if}-->



