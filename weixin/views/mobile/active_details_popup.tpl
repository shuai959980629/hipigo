<ul class="numchooise">
    <li>
      <span class="left">价格</span>
      	<input type="hidden" value="<!--{$info.type}-->" id="join_price_type"/>
	  <!--{if $info.join_price !=0}-->	

		<!--{if $info.type==2}-->	
			<span class="numchooiserg right"><em class="red"  id="add_price" data-add_price="<!--{$info.preferential_price}-->"><!--{$info.preferential_price}--></em>元</span>
		<!--{else}-->
			<span class="numchooiserg right"><em class="red"  id="add_price" data-add_price="<!--{$info.join_price}-->"><!--{$info.join_price}--></em>元</span>			
		<!--{/if}-->		


	  <!--{else}-->
	  <span class="numchooiserg right"><em class="red">免费</em></span>
	  <!--{/if}-->
    </li>  

    <!--{if $info.join_price !=0 && $info.type!=2}-->	
    <li>
      <span class="left">数量</span>

	  <!--{if $info.surplus > 0 || $info.surplus < 0 }-->
		  <span class="numchooiserg right">
			  <a href="javascript: void(0);" id="add_ac" class="haveback">+</a>
			  <input id="ac_num" type="text" readonly value="1" class="right" num-total="1"/>
			  <a href="javascript: void(0);" id="sub_ac" class="haveback">-</a>
		  </span>
	  <!--{/if}-->

	  <!--{if $info.surplus == 0 }-->
		  <span class="numchooiserg right">
			  <a href="javascript: void(0);" id="add_ac" class="haveback">+</a>
			  <input id="ac_num" type="text" readonly value="0" class="right" num-total="0"/>
			  <a href="javascript: void(0);" id="sub_ac" class="haveback">-</a>
		  </span>
	  <!--{/if}-->
    </li>
	
   <!--{else}-->
      <span class="numchooiserg right">
	<input id="ac_num" type="hidden" readonly value="1" class="right" num-total="1"/>
     </span>
   <!--{/if}-->

        <!--{if $info.join_condetion}-->
        <!--{foreach from=$info.join_condetion key=key item=value}-->
			<li>
		 <span class="left"><!--{$value}--></span>
		 <span class="numchooiserg right">
		 <input type="text" name="condetion[]" id="<!--{if $value == '电话号码'}-->phone<!--{else}-->username<!--{/if}-->"  maxlength="<!--{if $value == '电话号码'}-->11<!--{else}-->30<!--{/if}-->" class="numchooiserg_phone right" placeholder="<!--{if $value == '电话号码'}-->输入手机号码<!--{else}-->请输入<!--{$value}--><!--{/if}-->"/>
		 </span>
		</li>
	    <!--{/foreach}-->
	    <!--{else}-->
	  	<li>
		 <span class="left">手机号码</span>
		 <span class="numchooiserg right">
		 <input  id="phone" input type="text" class="numchooiserg_phone right" placeholder="输入手机号码"/>
		 </span>
		 </li>
	    <!--{/if}-->


    <p class="protocol"><input type="checkbox" checked="checked"/>我已看过并同意<a href="/wapi/90/0/community/user_agreement" class="haveback">《用户使用协议》</a></p>
</ul>