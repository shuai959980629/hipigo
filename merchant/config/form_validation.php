<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config = array(
    'activity' => array(
        array(
            'field' => 'title',
            'label' => '标题',
            'rules' => 'required'
        ),
        array(
            'field' => 'content',
            'label' => '内容',
        	'rules' => 'required'
        )
    ),
    'edit_synopsis' => array(
        array(
            'field' => 'title',
            'label' => '标题',
            'rules' => 'required|min_length[2]|max_length[25]'
        ),
        array(
            'field' => 'content',
            'label' => '内容',
            'rules' => 'required|min_length[10]|max_length[50000]'
        ),
        array(
            'field' => 'phone',
            'label' => '电话',
            'rules' => 'required|numeric'
        )
    ),
    'add_item' => array(
        array(
            'field' => 'title',
            'label' => '标题',
            'rules' => 'required|min_length[2]|max_length[100]'
        ),
        array(
            'field' => 'content',
            'label' => '内容',
            'rules' => 'required|min_length[10]|max_length[60000]'
        ),
        array(
            'field' => 'phone',
            'label' => '电话',
            'rules' => 'required|numeric'
        ),
        array(
            'field' => 'price',
            'label' => '价格',
            'rules' => 'required|numeric'
        )
    ),
    'edit_shop' => array(
        array(
            'field' => 'title',
            'label' => '标题',
            'rules' => 'required|min_length[2]|max_length[25]'
        ),
        array(
            'field' => 'content',
            'label' => '内容',
            'rules' => 'required|min_length[10]|max_length[50000]'
        ),
        array(
            'field' => 'phone',
            'label' => '电话',
            'rules' => 'required|numeric'
        )
    ),
    'add_song' => array(
        array(
            'field' => 'name',
            'label' => '歌曲名',
            'rules' => 'required|min_length[1]|max_length[25]'
        ),
        array(
            'field' => 'singer',
            'label' => '原唱',
            'rules' => 'required|min_length[1]|max_length[20]'
        )
//        array(
//            'field' => 'song_url',
//            'label' => '歌曲地址',
//            'rules' => 'required'
//        ),
//        array(
//            'field' => 'lyric',
//            'label' => '歌词',
//            'rules' => 'required|min_length[10]|max_length[30000]'
//        )
    ),
    'add_ticket' => array(
        array(
            'field' => 'name',
            'label' => '名称',
            'rules' => 'required|min_length[1]|max_length[20]'
        ),
        array(
            'field' => 'number',
            'label' => '数量',
            'rules' => 'required|min_length[1]|max_length[6]'
        ),
        array(
            'field' => 'length',
            'label' => '最大长度',
            'rules' => 'required|min_length[6]|max_length[12]'
        )
    ),
	'edit_passwd' => array(
			array(
					'field' => 'opasswd',
					'label' => '旧密码',
					'rules' => 'required|min_length[6]'
			),
			array(
					'field' => 'npasswd',
					'label' => '新密码',
					'reles' => 'required|min_length[6]'
			),
			array(
					'field' => 'rnpasswd',
					'label' => '确认密码',
					'reles' => 'required|matches[npasswd]'
			)
	),
    'add_community' => array(
        array(
            'field' => 'title',
            'label' => '标题',
            'rules' => 'required|min_length[1]|max_length[64]'
        ),
        array(
            'field' => 'content',
            'label' => '内容',
            'reles' => 'required'
        )
    ),
    'add_resource' => array(
        array(
            'field' => 'title',
            'label' => '标题',
            'rules' => 'required|min_length[1]|max_length[64]'
        ),
    )

);