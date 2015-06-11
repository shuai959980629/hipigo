/**
 * 加载数据列表    经常生活,推荐商户,申请达人，广场搜索
 * @author zxx
 * kw  搜索关键字
 * page  页码    0：页面及搜索加载  1：分页加载
 * url  列表请求地址
 * append_name   加载容器名字
 **/
var refresh_page = function (kw, page,url,append_name) {
    $.ajax({
        url: url,
        async: false,
        data: {kw: kw, offset: page},
        type: 'POST',
        dataType: 'text',
        success: function (result) {
            var obj = $('.'+append_name);
            var results = result.replace(/(\r)*\n/g,"").replace(/\s/g,"");
            if(results != '') {
                if(kw && page) {
                    obj.append(result);
                } else if(page) {
                    obj.append(result);
                } else {
                    obj.html(result);
                }
                $('<li class="loadmore" style="height: 30px; text-align: center; cursor: pointer;">点击查看更多</li>')
                    .appendTo(obj)
                    .click(function () {
                        $(this).remove();
                        offset++;
                        setTimeout(refresh_page(kw, offset,url,append_name), 200);
                    });
            }else{
                no_data(obj,page,append_name);
            }
            J.Scroll("#"+obj.parent().parent().attr('id'));//滚动容器
        },
        beforeSend: function(XMLHttpRequest) {
            var html = '<div class="loading" style="margin:0 auto; text-align: center; width: 100px;">' +
                '<img src="/wapi/img/mobile/loading.gif" alt="loadding" /></div>';
            $('.'+append_name).after(html);
        },
        complete:function(){
            $('.loading').remove();
        }
    });
}


//没有数据的情况  zxx
function no_data(obj,page,source){
    var html = '<div class="no_data">';
    switch (source) {
        case 'activity_list':
            html += '<img src="/wapi/img/mobile/no_thing.png" alt="no_data" />' +
                '<p>该社区没有最新活动 <br />' +
                '去《<a href="' + base_url + '/community/coollife" class="haveback red01">精彩生活</a>》参加更多更精彩的活动吧！</p>';
            break;
        case 'resource_by':
            html += '<img src="/wapi/img/mobile/no_resource.png" alt="no_data" />' +
                '<p>这里购买的资源即为我的资源。<br />' +
                '去《<a href="' + base_url + '/resource/index"  class="haveback red01">资源库</a>》购买吧！</p>';
            break;
        case 'resource'://资源库
            html += '<img src="/wapi/img/mobile/no_resource.png" alt="no_data" />' +
                '<p>到这里去 -> <a href="' + base_url + '/community/coollife"  class="haveback red01">点击</a> 可能有你想要的！</p>';
            break;
        case 'activity_by':
            html += '<img src="/wapi/img/mobile/no_thing.png" alt="no_data" />' +
                '<p>你还没有最新的活动，<br />' +
                '去《<a href="' + base_url + '/square/square_index"  class="haveback red01">广场</a>》看看吧！有惊喜哦！</p>';
            break;
        case 'user_by':
            html += '<img src="/wapi/img/mobile/no_thing.png" alt="no_data" />' +
                '<p>该用户很懒！<br />' +
                '去《<a href="' + base_url + '/square/square_index"  class="haveback red01">广场</a>》看看吧！有惊喜哦！</p>';
            break;
        default:
            html += '<img src="/wapi/img/mobile/no_thing.png" alt="no_data" />' +
                '<p>去《<a href="' + base_url + '/square/square_index" class="haveback red01">广场</a>》看看吧！有惊喜哦！</p>';
            break;
    }
    html += '</div>';

    if( ! page)
        $(obj).html(html);
}