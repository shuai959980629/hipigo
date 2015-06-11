var offset = 1;
$(function () {
  refresh('resources_list', base_url + '/resource/lists', 'resource');
  $('.searchbutton').click(function () {
    var val = $(this).prev().val();
    if(! val) {
    }
    else {
      refresh('resources_list', base_url + '/resource/lists', 'resource', val);
      //refresh_bak(val, '', 'resource');
    }
  });
  
  $('.buy').live('click', function () {
    popupBuy($(this).attr('rel'), 'resource');
  });
  
});
