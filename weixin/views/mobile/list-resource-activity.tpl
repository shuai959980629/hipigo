<!--{if ! $lists}-->no_data<!--{else}-->
<!--{foreach from=$lists item=list key=key}-->
<li class="clearfix">

<!--{if $source == 'activity_by'}-->
<a href="<!--{$url}-->/community/detail?aid=<!--{$list.id_activity}-->">
<!--{else}-->
<a href="<!--{$url}-->/resource/info?rid=<!--{$list.id_resource}-->">
<!--{/if}-->

<span class="resources_xx left">
<b class="resources_title">
<!--{$list.resource_title|default: $list.title}-->
</b>
<b class="resources_time">
<em class="red">
<!--{$list.price|default: $list.join_price}-->
</em>
<!--{if $source == 'resource' || $list.creator}-->
存货:<!--{$list.num|default:$list.total}-->
<!--{/if}-->
<!--{if $source == 'resource' || $source == 'resource_by' }-->
<!--{$list.deadline}-->
<!--{/if}-->
</b>
</span>
</a>

<!--{if $source == 'resource_by' || (! $list.creator && $source == 'activity_by')}-->
<b class="code">
<!--{if count($list.codes) > 1}-->
<a href="javascript: void(0);" id="code_more" rel="<!--{$list.id_resource|default:$list.id_activity}-->" class="haveback right">查看更多</a>
<!--{/if}-->

<!--{if $list.codes}-->
<!--{foreach from=$list.codes item=codes name=key}-->
<!--{if $codes.state == 1}-->
<em class="red right"><!--{$codes.code}--></em>
<!--{break}-->
<!--{/if}-->
<!--{/foreach}-->
<!--{/if}-->
</b>
<!--{elseif $source == 'activity_by' && $list.creator}-->
<!--{if $list.state == 2}-->
<button class="but close right margintop27" rel="<!--{$list.id_activity}-->">关闭</button>
<!--{else}-->
<button class="but disable right margintop27">已关闭</button>
<!--{/if}-->
<a href="<!--{$url}-->/community/publish?aid=<!--{$list.id_activity}-->">
<button class="but edit right margintop27">编辑</button></a>
<!--{elseif $source == 'resource'}-->
<span class="resources_buy right">
<button class="buy" rel="<!--{$list.id_resource}-->">购买</button>
</span>
<!--{/if}-->
</li>
<!--{/foreach}-->
<!--{/if}-->