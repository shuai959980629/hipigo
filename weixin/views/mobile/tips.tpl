<!--弹窗-->
<ul class="numchooise">

<li>
<span class="left">数量</span>
<span class="numchooiserg right">
<a href="javascript: void(0);" id="add_ac" onclick="plus(this)" class="haveback">+</a>
<input id="ac_num" type="text" readonly="" value="1" class="right" rel="<!--{$info.id_resource}-->" num-total="<!--{$info.num}-->">
<a href="javascript: void(0);" id="sub_ac" onclick="subtract(this)" class="readonly haveback">-</a>
</span>
</li>

<li>
<span class="left">价格</span>
<span class="numchooiserg right">
<em class="red"><!--{$info.price}--></em>
</span>
</li>

</ul>
