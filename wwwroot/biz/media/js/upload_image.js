/**
 * zxx
 * 上传图片
 */
function uplodImage(param){
    var inputImage = arguments[1] ? arguments[1] : '';
    var show = arguments[2] ? arguments[2] : '';
    var url = arguments[3] ? arguments[3] : '';
    var file_path = 'community';
    var name = 'uploadfile';
    if(show == 'show_synopsis'){
        file_path = 'attachment';
    }else if(show == 'show_banner'){
        file_path = 'banner';
    }else if(show == 'show_synopsis_logo'){
        file_path = 'logo';
        name = 'uploadlogo';
    }else if(show == 'show_shop'){
        file_path = 'shop';
    }else if(show == 'show_ticket'){
        file_path = 'ticket';
    }else if(show == 'show_activity'){
        file_path = 'activity';
    }else if(show == 'show_item'){
        file_path = 'commodity';
    }else if(show == 'show_song'){
        file_path = 'song';
    }else if(show == 'show_customer'){
        file_path = 'customer';
    }else if(show == 'show_song_audio'){
        file_path = 'songaudio';
        name = 'uploadaudio';
    }
    new AjaxUpload('#'+param, {
        action: url+'files/upload_all_file/'+file_path, name: name, type:'POST', id:'file',
        onChange:function(file,ext) {//提交前运行
            if(!upload_start(ext,show)){
                return false;
            }
        }, onSubmit : function(file, ext) {//提交中运行
            upload_submit(show);
        }, onComplete: function(file, response) {//提交完运行
            $('.load').hide();
            var dataAry = $.parseJSON(response);
            upload_complete(dataAry,inputImage,show);
        }
    });
}

/*
* zxx
* 上传前的判断
 */
function upload_start(ext,show){
    if(show == 'show_song_audio' && ext!='mp3' && ext!='ogg' && ext!='wav'){
        alert("选择的文件格式不正确哦!");
        return false;
    }else if(ext!='png' && ext!='jpg' && ext!='gif' && ext!='jepg' && show != 'show_song_audio') {
        alert("选择的图片格式不正确哦!");
        return false;
    }
    if(parseInt($('#picNum').val()) >= 5 && show == 'show_synopsis'){
        alert("企业文件只能传送5个!");
        return false;
    }else if((parseInt($('#picNum').val()) >= 1 && (show == 'show_shop' || show == 'show_activity' || show == 'show_item' || show == 'show_song'))
        || (parseInt($('#fileNum').val()) >= 1 && (show == 'show_ticket' || show == 'show_synopsis_logo' || show == 'show_song_audio'))){
        if(show == 'show_song_audio'){
            alert("音频文件只能传送1个!");
        }else
            alert("图片文件只能传送1张!");
        return false;
    }
    return true;
}

/**
 * zxx
 * 提交中的判断
 */
function upload_submit(show){
    var html = '';
    if(show == 'show_synopsis'){
        html = '<div class="fileupload-new thumbnail" id="loding_img"><img src="/biz/media/image/load.gif" style="width:136px;height:85px;"></div>';
        $('#'+show).append(html);
    }else if(show == 'show_synopsis_logo'){
        html = '<div class="fileupload-new thumbnail synopsis_logo_pic" id="loding_img"><img src="/biz/media/image/log_loading.gif" alt="" class="synopsis_logo_pic_loading"></div>';
        $('#'+show).html(html);
    }else if(show == 'show_shop' || show == 'show_ticket' || show == 'show_activity' || show == 'show_item' || show == 'show_song' || show == 'show_banner'){
        var style_ = 'style="width:136px;height:85px"';
        if(show == 'show_ticket'){
            style_ = '';
        }else if(show == 'show_song'){
            style_ = 'style="width:150px;height:150px"';
        }
        html = '<div class="fileupload-new thumbnail" id="loding_img"><img src="/biz/media/image/load.gif" '+style_+'></div>';
        $('#'+show).html(html);
    }else if(show == 'show_song_audio'){
        html = '<span class="nui-bg-dark"><img src="/biz/media/image/log_loading.gif"/></span>';
        $('#'+show).html(html);
    }else{
        $('.load').show();
    }
}

/**
 * zxx
 * 上传完成的判断
 */
function upload_complete(dataAry,inputImage,show){
    var html = '';
    if(dataAry.error == 0){
        if(show == 'show_synopsis'){
            $('#loding_img').remove();
            html = '<div class="fileupload-new thumbnail"><img src="'+dataAry.url+'" style="width:136px;height:85px;"><a onClick="delete_img(this,\''+dataAry.file_name+'\',\'image_src\')"></a></div>';
            if($('#' + inputImage).val() == '' || $('#' + inputImage).val() == ' '){
                $('#' + inputImage).val(dataAry.file_name);
            }else{
                $('#' + inputImage).val($('#' + inputImage).val()+','+dataAry.file_name);
            }
            if(parseInt($('#picNum').val()) > 0){
                $('#'+show).append(html);
            }else{
                $('#'+show).html(html);
            }
            $('#picNum').val(parseInt($('#picNum').val())+1);
        }else if(show == 'show_synopsis_logo'){
            html = '<div class="fileupload-new thumbnail synopsis_logo_pic"><img src="'+dataAry.url+'" alt=""><a onClick="delete_synopsis_logo(this)" style="right: -20px;"></a></div>';
            $('#'+show).html(html);
            $('#' + inputImage).val(dataAry.file_name);
            $('#fileNum').val(parseInt($('#fileNum').val())+1);
        }else if(show == 'show_shop' || show == 'show_ticket' || show == 'show_activity' || show == 'show_item' || show == 'show_song' || show == 'show_banner'){
            var style_ = 'style="width:136px;height:85px"';
            if(show == 'show_ticket'){
                style_ = '';
                $('#fileNum').val(1);
            }else if(show == 'show_song'){
                style_ = 'style="width:150px;height:150px"';
                $('#picNum').val(1);
            }else{
                $('#picNum').val(1);
            }
            html = '<div class="fileupload-new thumbnail"><img src="'+dataAry.url+'" alt="" '+style_+'><a onClick="delete_img(this)"></a></div>';
            $('#'+show).html(html);
            $('#' + inputImage).val(dataAry.file_name);
        }else if(show == 'show_song_audio'){
            html = '<span class="nui-bg-dark"><b class="audio-component-icon nui-ico fl"></b><em class="fl" title="音频">音频</em>'+
                '<a href="javascript:void(0)" onClick="delete_song_audio(this)">删除</a></span>';
            $('#'+show).html(html);
            $('#' + inputImage).val(dataAry.message);
            $('#fileNum').val(parseInt($('#fileNum').val())+1);
        }else if(show == 'show_customer'){
            html = '<div><img src="'+dataAry.url+'" style="width:120px;height:100px;"/><a onclick="delete_img(this)">X</a></div>';
            $('#'+show).html(html);
            $('#' + inputImage).val(dataAry.url);
        }else{
            $('#'+show).html('<img src="'+dataAry.url+'">');
            $('#' + inputImage).val(dataAry.file_name);
            $('#picNum').val(1);
        }
    }else{
        alert(dataAry.message);
        if($('#loding_img').length > 0){
            $('#loding_img').remove();
        }
    }
}
