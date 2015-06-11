
$(function () {
  $('[name="code"]:input').keyup(function(e) {
    if(! /^\d+$/.test($('[name="code"]:input').val()) && e.keyCode != 8) {
      $(this).iDelField(1);
    }
  });
  
  remove_share();
  
  $('#searchCode').click(function () {
    $('.empty').hide();
    var val = $('[name="code"]:input').val();
    if(val) {
      if(/^\d+$/.test(val)) {
        $.ajax({
          url: base_url + '/user_activity/veri_code',
          async: false,
          data: {code: val},
          type: 'post',
          dataType: 'json',
          success: function (result) {
            $('[name="code"]:input').val('');
            if(result.code == 1) {
              var li = '';
              $.each(result.success, function (key, item) {
                li += '<li>' + 
                      '<input type="checkbox" name="veri_codes[]" rel="' + 
                      item.id_item + '" />' + '<span style="cursor: pointer;"><em class="red">' + 
                      item.code + '</em>' + '<i>' + item.nick_name + '</span></i>' +
                      '</li>';
              });
              $('.search_identify_code .empty').after(li);
              $('.confirm').show();
            }
            else {
              $('.empty').html(result.msg).show();
            }
          }
        });
      }
      else {
        //J.alert('提示', '消费码只能是数字');
      }
    }
  });
  
  $('.search_identify_code li span').live('click', function () {
    if($('[name="veri_codes[]"]:checked').attr('checked')) {
      $(this).prev('input').removeAttr('checked');
    }
    else {
      $(this).prev('input').attr('checked', 'checked');
    }
  });
  
  $('.confirm').click(function () {
    var codes = [];
    $('[name="veri_codes[]"]:checked').each(function () {
      codes.push($(this).attr('rel'));
    });
    
    if(codes.length > 0) {
      $.ajax({
        url: base_url + '/user_activity/code_confirm',
        async: false,
        data: {codes: codes},
        type: 'post',
        dataType: 'json',
        success: function (result) {
          if(result.code == 1) { //成功
            alert(result.msg);
            window.location.reload();
          }
        },
      });
    }
    else {
      J.alert('提示', '请选择需要验证的消费码');
    }
  });
  
  //J.Scroll('#validate_container');
});
