<!--{if ! $lists}-->no_data<!--{else}-->
<!--{foreach from=$lists item=list key=key}-->

<li>
<span class="left time">
<!--{if $list.day }-->
<b><!--{$list.day}--></b><!--{$list.month}-->月
<!--{/if}-->
</span>

<span class="user_activity_mes right">

<!--{if ! $list.creator}-->

<!--{if $list.object_type == 'user'}-->
<a href="<!--{$url}-->/user_activity/index?userid=<!--{$list.id_business}-->" >
<!--{else}-->
<a href="/wapi/<!--{$list.id_business}-->/0/community/home" >
<!--{/if}-->

<span class="user_head left">
<img src="<!--{$list.img_url}-->" alt="图片" style="width:60px;height: 60px;"/>
<!--{if $list.object_type == 'user' }-->
<img src="/wapi/img/mobile/leader_icon.png" class="leader_icon" />
<!--{/if}-->
</span>
</a>
<!--{/if}-->

<h4><a href="<!--{$url}-->/community/detail?aid=<!--{$list.id_activity}-->"><!--{$list.title}--></a></h4>

</span>
</li>
<!--{/foreach}-->

<!--{/if}-->