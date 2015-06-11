var  player= {
        isIE : !!(window.attachEvent && !window.opera),
        isOpera : !!window.opera,
        isGecko :  navigator.userAgent.indexOf("Gecko") > -1 && navigator.userAgent.indexOf("KHTML") == -1,
        isSafari : (navigator.userAgent.indexOf("Safari") > -1),
        isChrome : (navigator.userAgent.indexOf("Chrome") > -1),
        isMozilla: (navigator.userAgent.indexOf("mozilla") > -1),
        name:"ZS_MUSIC_PLAYER",
        playid : "MUSIC_PLAYER",
        playbox:"MUSIC_PLAYER_BOX",
        auto:"-1",
        isInit:false,
        isStop:true,
        isLoop:false,
        url:"",
        name:"",
        PauseTime:"",
        currentId:0
}
function  insertAudioPlayer(objAttrs){  
        var bodyC =  document.body; 
        var playerDiv = document.createElement("div"); 
        playerDiv.setAttribute("style","position:absolute;width:0px; height:0px;background:#fff;display:none; top:0px; left:0px; z-index:-99999999;") ;
        playerDiv.setAttribute("id",player.playbox);
        var html = [];
        if(player.isIE){ 
            var strTemp = "<object classid=\"CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6\"";
            strTemp += " type=\"application/x-oleobject\"  width=\"0\" height=\"0\" id=" + player.playid;
            strTemp += " style=\"position:relative; left:0px; top:0px; display:none; width:0px; height:0px;\">\n";
            strTemp += "  <param name=\"autoStart\" value=\""+player.auto+"\">\n";
            strTemp += "  <param name=\"balance\" value=\"0\">\n";
            strTemp += "  <param name=\"currentPosition\" value=\"0\">\n";
            strTemp += "  <param name=\"currentMarker\" value=\"0\">\n";
            strTemp += "  <param name=\"enableContextMenu\" value=\"0\">\n";
            strTemp += "  <param name=\"enableErrorDialogs\" value=\"0\">\n";
            strTemp += "  <param name=\"enabled\" value=\"-1\">\n";
            strTemp += "  <param name=\"fullScreen\" value=\"0\">\n";
            strTemp += "  <param name=\"invokeURLs\" value=\"0\">\n";
            strTemp += "  <param name=\"mute\" value=\"0\">\n";
            strTemp += "  <param name=\"playCount\" value=\"1\">\n";
            strTemp += "  <param name=\"rate\" value=\"1\">\n";
            strTemp += "  <param name=\"uiMode\" value=\"none\">\n";
            strTemp += "  <param name=\"volume\" value=\"100\">\n";
            strTemp += "  <param name=\"URL\" value=\"\">\n";
            strTemp += "</object>";
            playerDiv.innerHTML  = strTemp;

        }else{
           html.push("<audio ");
            for (var key in objAttrs) {
                html.push(key);
                html.push("='");
                html.push(objAttrs[key]);
                html.push("' ");
            }
            html.push("></audio>"); 
            playerDiv.innerHTML = html.join("");
        }
        bodyC.appendChild(playerDiv);
        var playHTML = document.getElementById(player.playbox).innerHTML;
        if(playHTML!=""){
            player.isInit = true;
            return true;
        }else{
            player.isInit = false;
            return false;
        } 
}
function createPlayer(){
    return insertAudioPlayer({id: player.playid,height: 0,width: 0,autoplay: 'false'});
}

/*播放*/
function Play(url){ 
    if(!player.isInit){
        $('.error_pop').text('播放器初始化失败。请刷新页面，重新加载！').show();
        return false;
    }
    if(player.url == url){
        if(player.isIE){
            document.getElementById(player.playid).controls.play();
        }else{
            document.getElementById(player.playid).volume =1;
            document.getElementById(player.playid).startTime = player.PauseTime;
            document.getElementById(player.playid).play();
        }
        
    }else{
        player.url = url;
        if(player.isIE){
            document.getElementById(player.playid).URL = url;
            document.getElementById(player.playid).controls.play();
        }else{
            document.getElementById(player.playid).volume =1;
            document.getElementById(player.playid).src =url
            document.getElementById(player.playid).play();
        }            
    }
    return true;          
}
/*暂停*/
function Pause(){
    if(!player.isInit){
        $('.error_pop').text('播放器初始化失败。请刷新页面，重新加载！').show();
        return false;
    }
    if(player.isIE){
        document.getElementById(player.playid).controls.pause();
    }else{
        document.getElementById(player.playid).volume =0;
        player.PauseTime = document.getElementById(player.playid).currentTime;
        document.getElementById(player.playid).pause();
    }
    return true;
}


/*页面加载，创建播放器。*/
if(window.attachEvent) { 
    window.attachEvent("onload", createPlayer); 
} else if (window.addEventListener) { 
    window.addEventListener("load", createPlayer, false);   
}  


