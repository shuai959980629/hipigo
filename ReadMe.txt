ATT相关开发及hipigo商家功能

一.目录说明

	merchant 		商家后台
	common			项目公用文件
	home			web前段项目
	mobile			手机端web项目
	system			ci框架
	weixin			微信数据接口
	temp			临时文件
	wwwroot			入口文件目录
	
	
二.简要的语法说明

	1.变量与函数的命名统一采用小写字母，单词之间用下划线分割
	2.controller的文件名，统一采用类名称小写加后缀构成,如user.php
	3.controller类命名统一采用首字母大写，核心业务类需加入_Controller做后缀，项目中会实际讲到
	4.类中成员方法必须写明函数功能、参数、返回值
	5.类中两个方法之间留两行空格
	6.特殊的功能性函数，通用型的放在common/funcs.php,项目独有的放在helpers中
	7.核心的业务处理放在common/controllers中用user_controller.php形式命名，实现多模块的共享
	8.核心的模型层放在common/models中用user_model.php命名，实现多模块共享
	
三.第三方插件说明

	1.smarty
	
		1).模板目录 APPNAME/views/templates
		2).编译目录 APPNAME/views/templates_c
		3).模板标签<!--{}-->如<!--{$users.username}-->
		4).controller中加载模板 $this->smarty->view('test.html');
		5).模板文件后缀统一采用.html


四.模块规划

	企业设置  settings

		企业介绍  /settings/company_intro
			
		门店介绍  /settings/shop_intro
			
	物品管理  item

		列表  /item/list_item
		添加  /item/add_item
		编辑  /item/edit_item
		删除  /item/del_item
		
	歌曲管理  song

		列表  /song/list_song
		添加  /song/add_song
		编辑  /song/edit_song
		删除  /song/del_song
		
	商品管理  goods	
		
		列表  /goods/list_goods
		添加  /goods/add_goods
		编辑  /goods/edit_goods
		删除  /goods/del_goods
		评论列表 /goods/list_comment
		删除评论 /goods/del_comment
		
	活动管理  activity

		列表  /activity/list_activity
		添加  /activity/add_activity
		编辑  /activity/edit_activity
		删除  /activity/del_activity

	用户管理  user

		登陆 /user/login
		退出 /user/logout



/business/activity/         活动
/business/commodity/        物品+商品
/business/logo/             企业logo
/business/shop/             门店
/business/song/             歌曲
/business/attachment/       企业简介图片
/business/content/          文章