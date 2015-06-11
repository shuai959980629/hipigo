var offset = 1;
$(function () {
  //屏蔽微信
  remove_share();
  
  refresh('resources_list', base_url + '/resource/lists', 'resource_by');
  $('.searchbutton').click(function () {
    var val = $(this).prev().val();
    if(! val) {
    }
    else {
      refresh('resources_list', base_url + '/resource/lists', 'resource_by', val);
    }
  });
  
  $('#code_more').live('click', function () {
    var val = $(this).attr('rel');
    moreCode($(this).attr('rel'), 'resource_by');
  });
  
});
