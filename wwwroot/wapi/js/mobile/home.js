$(function () {

  refresh('list', base_url + '/community/home_list', 'activity_list');
  
  J.Slider({
    selector : '#slider',
    interval: 3000,
    autoPlay: true,
    onBeforeSlide : function(){
      return true;
    },
    onAfterSlide : function(i){
      $('#slider div').show();
    }
  });
});