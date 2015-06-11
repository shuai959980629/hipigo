var offset = 1;
$(function () {
  remove_share();
  
  refresh('resources_list', base_url + '/user_activity/ajaxlist', 'activity_by');
  
  $('.searchbutton').click(function () {
    var val = $(this).prev().val();
    if(! val) { }
    else {
      refresh('resources_list', base_url + '/user_activity/ajaxlist', 'activity_by', val);
    }
  });
  
  $('#filter_activity').click(function () {
    $('.pull_down').show();
  });
  
  $('.pull_down ul li').click(function () {
    $('.pull_down').hide();
    
    if($(this).index() == 1)
      refresh('resources_list', base_url + '/user_activity/ajaxlist', 'activity_by', '', '', 'by');
    else if($(this).index() == 2)
      refresh('resources_list', base_url + '/user_activity/ajaxlist', 'activity_by', '', '', 'jion');
    else
      refresh('resources_list', base_url + '/user_activity/ajaxlist', 'activity_by');
  });
  
  $('#code_more').live('click', function () {
    var val = $(this).attr('rel');
    moreCode($(this).attr('rel'), 'activity_by');
  });
  
  $('.close').live('click', function () {
    var obj = $(this);
    $.post(
      base_url + '/user_activity/disable', 
      {id: $(this).attr('rel')}, 
      function (result) {
        if(result.code == 1) {
          obj.addClass('disable').html('已关闭')
            .removeClass('close');
        }
        J.showToast(result.msg);
      }, 'json');
  });
  
});



