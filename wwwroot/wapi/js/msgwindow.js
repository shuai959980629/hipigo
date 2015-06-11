YUI.add("msgWindow",function(Y){

	Y.MsgWindow = function(cfg){
		Y.MsgWindow.superclass.constructor.apply(this,arguments);
	};

	Y.MsgWindow.NAME = "msgWindow";
	Y.MsgWindow.ATTRS = {
		hasMask : {
			value : true
		},
		hasCloseBtn : {
			value : false
		},
		skinClassName : {
			value : null
		},
		hasTitleNode : {
			value : true
		},
		isDraggable : {
			value : true
		},
		title : {
			value : "提示"
		},
		width : {
			value : 300
		},
		height : {
			value : 200
		},
		zIndex : {
			value : 100
		},
		x : {
			value : null
		},
		y : {
			value : null
		},
		content : {
			value : ""
		},
		text4AlertBtn : {
			value : "确定"
		},
		text4ConfirmBtn : {
			value : "确定"
		},
		text4CancelBtn : {
			value : "取消"
		},
		text4PromptBtn : {
			value : "确定"
		},
		value4PromptInput : {
			value : ""
		},
		alertEvent : {
			value : null
		},
		confirmEvent : {
			value : null
		},
		cancelEvent : {
			value : null
		},
		closeEvent : {
			value : null
		},
		promptEvent : {
			value : null
		},
		destroyAfterBtnEvent : {
			value : true
		}
	};
	
	Y.extend(Y.MsgWindow,Y.Widget,{
		initializer : function(cfg){
			this._windowType = null;
			if(this.hasMask()){
				this._addMask();
			}
		},
		destructor : function(){
			if(this.hasMask()){
				this._removeMask();
			}
		},
		renderUI : function(){
			var contentBox = this.get("contentBox");
			this._headerNode = Y.Node.create('<div class="webgame_msgWindow_header"></div>');
			/*if(this.hasTitleNode()){
				this._titleNode = Y.Node.create('<span class="webgame_msgWindow_title">'+this.getTitle()+'</span>');
				this._headerNode.append(this._titleNode);
			}*/
			if(this.hasCloseBtn()){
				this._closeBtn = Y.Node.create('<span class="webgame_msgWindow_closeBtn">X</span>');
				this._headerNode.append(this._closeBtn);
			}
			this._bodyNode = Y.Node.create('<div class="webgame_msgWindow_body">'+this.getContent()+'</div>');
			this._footerNode = Y.Node.create('<div class="webgame_msgWindow_footer"></div>');
			switch(this._windowType){
				case "alert":
					this._alertBtn = Y.Node.create('<input type="button" class="webgame_msgWindow_alertBtn" value="' + this.get("text4AlertBtn") + '">');
					this._footerNode.append(this._alertBtn);
					break;
				case "confirm":
					this._confirmBtn = Y.Node.create('<input type="button" class="webgame_msgWindow_confirmBtn" value="' + this.get("text4ConfirmBtn") + '">');
					this._cancelBtn = Y.Node.create('<input type="button" class="webgame_msgWindow_cancelBtn" value="' + this.get("text4CancelBtn") + '">');
					this._footerNode.append(this._confirmBtn);
					this._footerNode.append(this._cancelBtn);
					break;
				case "prompt":
					this._promptInput = Y.Node.create('<input type="text" class="webgame_msgWindow_promptInput" value="' + this.get("value4PromptInput") + '">');
					this._promptBtn = Y.Node.create('<input type="button" class="webgame_msgWindow_promptBtn" value="' + this.get("text4PromptBtn") + '">');
					this._bodyNode.append(this._promptInput);
					this._footerNode.append(this._promptBtn);
					break;
				case "common":
					this._footerNode.remove();
					this._footerNode = null;
					break;
			}
			contentBox.append(this._headerNode);
			contentBox.append(this._bodyNode);
			contentBox.append(this._footerNode);
		},
		bindUI : function(){
			var boundingBox = this.get("boundingBox");
			this.on("titleChange",function(e){
				if(!this.hasTitleNode()) return;
				this._titleNode.setContent(e.newVal);	
			},this);
			this.on("widthChange",function(e){
				boundingBox.setStyle("width",e.newVal+"px");
			},this);
			this.on("heightChange",function(e){
				boundingBox.setStyle("height",e.newVal+"px");
			},this);
			this.on("zIndexChange",function(e){
				boundingBox.setStyle("zIndex",""+e.newVal);
				this._maskNode.setStyle("zIndex",""+e.newVal);
			},this);
			this.on("xChange",function(e){
				boundingBox.setStyles({
					left : e.newVal + "px",
					marginLeft : "0px",
					marginTop : "0px"
				});
			},this);
			this.on("yChange",function(e){
				boundingBox.setStyles({
					top : e.newVal + "px",
					marginLeft : "0px",
					marginTop : "0px"
				});
			},this);
			this.on("contentChange",function(e){
				this._bodyNode.setContent(e.newVal);
			},this);
			this._closeBtn && this._closeBtn.on("click",function(){
				if(Y.Lang.isFunction(this.get("closeEvent"))){
					this.get("closeEvent").call(this);
				}
				this.fire("closeEvent");
				this.destroy();	
			},this);
			this._alertBtn && this._alertBtn.on("click",function(){
				if(Y.Lang.isFunction(this.get("alertEvent"))){
					this.get("alertEvent").call(this);
				}
				this.fire("alertEvent");
				this.get("destroyAfterBtnEvent") && this.destroy();	
			},this);
			this._confirmBtn && this._confirmBtn.on("click",function(){
				if(Y.Lang.isFunction(this.get("confirmEvent"))){
					this.get("confirmEvent").call(this);
				}
				this.fire("confirmEvent");
				this.get("destroyAfterBtnEvent") && this.destroy();	
			},this);
			this._cancelBtn && this._cancelBtn.on("click",function(){
				if(Y.Lang.isFunction(this.get("cancelEvent"))){
					this.get("cancelEvent").call(this);
				}
				this.fire("cancelEvent");
				this.get("destroyAfterBtnEvent") && this.destroy();	
			},this);
			this._promptBtn && this._promptBtn.on("click",function(){
				var inputValue = this._promptInput.get("value");
				if(Y.Lang.isFunction(this.get("promptEvent"))){
					this.get("promptEvent").call(this,inputValue);
				}
				this.fire("promptEvent",inputValue);
				this.get("destroyAfterBtnEvent") && this.destroy();	
			},this);
			if(this.isDraggable()){
				var boundingBox = this.get("boundingBox"),
				    xOffset = yOffset = 0;
				this._headerNode.setStyle("cursor","move");
				this._headerNode.on("mousedown",function(){
					boundingBox.set("draggable",true);
				},this);
				this._headerNode.on("mouseup",function(){
					boundingBox.set("draggable",false);
				},this);
				boundingBox.on("dragstart",function(e){
					xOffset = e.pageX - boundingBox.getX();
					yOffset = e.pageY - boundingBox.getY();
				},this);
				boundingBox.on("dragend",function(e){
					e.preventDefault();
					boundingBox.setXY([e.pageX - xOffset,e.pageY - yOffset]);	
				},this);
				Y.one("body").on("dragover",function(e){
					e.preventDefault();
				},this);
			}
		},
		syncUI : function(){
			var boundingBox = this.get("boundingBox");
			boundingBox.addClass("webgame_msgWindow");	
			if(this.getSkinClassName()){
				boundingBox.addClass(this.getSkinClassName());	
			}
			boundingBox.setStyles({
				width : this.getWidth() + "px",
				height : this.getHeight() + "px",
				zIndex : "" + this.getZIndex()
			});
			if(this.getX() == null){
				var x = Y.one("body").get("offsetWidth")/2 - this.getWidth()/2;
				this.setX(x);
				boundingBox.setStyle("left",x + "px");
			} else {
				boundingBox.setStyle("left",this.getX() + "px");
			}
			if(this.getY() == null){
				var y = Y.one("body").get("offsetHeight")/2 - this.getHeight()/2;
				this.setY(y);
				boundingBox.setStyle("top",y + "px");
			} else {
				boundingBox.setStyle("top",this.getY() + "px");
			}
			if(this._promptInput){
				this._promptInput.focus();
				this._promptInput.select();
			}
		},
		hasMask : function(){
			return this.get("hasMask");
		},
		hasCloseBtn : function(){
			return this.get("hasCloseBtn");
		},
		getSkinClassName : function(){
			return this.get("skinClassName");
		},
		hasTitleNode : function(){
			return this.get("hasTitleNode");
		},
		isDraggable : function(){
			return this.get("isDraggable");
		},
		getTitle : function(){
			return this.get("title");
		},
		setTitle : function(title){
			this.set("title",title);
		},
		getWidth : function(){
			return this.get("width");
		},
		setWidth : function(width){
			this.set("width",width);
		},
		getHeight : function(){
			return this.get("height");
		},
		setHeight : function(height){
			this.set("height",height);
		},
		getZIndex : function(){
			return this.get("zIndex");
		},
		setZIndex : function(zIndex){
			this.set("zIndex",zIndex);
		},
		getX : function(){
			return this.get("x");
		},
		setX : function(x){
			this.set("x",x);
		},
		getY : function(){
			return this.get("y");
		},
		setY : function(y){
			this.set("y",y);
		},
		getXY : function(){
			return [this.getX(),this.getY()];
		},
		sexXY : function(pos){
			this.setX(pos[0]);
			this.setY(pos[1]);
		},
		getContent : function(){
			return this.get("content");
		},
		setContent : function(content){
			this.set("content",content);
		},
		alert : function(cfg){
			this._windowType = "alert";
			this._render4Window(cfg);
			return this;
		},
		confirm : function(cfg){
			this._windowType = "confirm";
			this._render4Window(cfg);
			return this;
		},
		prompt : function(cfg){
			this._windowType = "prompt";
			this._render4Window(cfg);
			return this;
		},
		common : function(cfg){
			this._windowType = "common";
			this._render4Window(cfg);
			return this;
		},
		_render4Window : function(cfg){
			Y.each(cfg,function(value,key){
				this.set(key,value);
			},this);
			this.render(document.body);
		},
		show : function(){
			this.get("boundingBox").show();
			if(this.hasMask()){
				this._maskNode.show();
			}
		},
		hide : function(){
			this.get("boundingBox").hide();
			if(this.hasMask()){
				this._maskNode.hide();
			}
		},
		getContentNode : function(){
			return this._bodyNode;
		},
		_addMask : function(){
			this._maskNode = Y.Node.create('<div class="webgame_msgWindow_mask" style="z-index:'+this.getZIndex()+'"></div>');	
			Y.one(document.body).append(this._maskNode);
		},
		_removeMask : function(){
			this._maskNode.remove();
		}
	}); 

},"0.1.0",{
	requires : ["widget"]	
});