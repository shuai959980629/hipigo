function editor(name,path,doman){
	var editor;
	KindEditor.ready(function(K) {
		editor = K.create('textarea[name="'+name+'"]', {
			resizeType : 1,
            themeType:'default',
            allowImageUpload : true,
            uploadJson:doman+'files/upload_all_file/'+path,
            shadowMode:false,
            allowMediaUpload:false,
//			allowPreviewEmoticons : false,
//			allowImageUpload : false,
//			allowUpload : false,
//			allowMediaUpload:false,
			afterBlur:function(){
                this.sync();
                var content = $('#'+name).val();
                var num = 0;
                if(path == 'review_editor'){
                    if(140 > content.length && content.length > 1){
                        num = 1;
                    }
                }else if(path == 'community_editor' ){
                    if(content.length > 1){
                        num = 1;
                    }
                }
                if(num == 1){
                    $('#'+name).parent().children('.help-inline').remove();
                    $('#'+name).parent().parent().removeClass('error');
                }
            },
			items : [ 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor',
					'bold', 'italic', 'underline', 'removeformat', '|',
					'justifyleft', 'justifycenter', 'justifyright',
					'insertorderedlist', 'insertunorderedlist','|','table','|','link','unlink','|','image', 'multiimage','|','media','|','emoticons' ]
		});
	});
}