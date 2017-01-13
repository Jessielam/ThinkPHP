<?php
return array(
	//'配置项'=>'配置值'
	'DB_TYPE'               =>  'mysqli',     // 数据库类型
	//'DB_TYPE'               =>	'pdo',
    'DB_HOST'               =>  'localhost', // 服务器地址
    'DB_NAME'               =>  'thinkphp',          // 数据库名
    'DB_USER'               =>  'root',      // 用户名
    'DB_PWD'                =>  '113322',          // 密码
    'DB_PORT'               =>  '3306',        // 端口
    'DB_PREFIX'             =>  'p39_',    // 数据库表前缀 
    'DB_CHARSET'            =>  'utf8',      // 数据库编码默认采用utf8
    //'DB_DSN'    			=> 'mysql:host=localhost;dbname=thinkphp;charset=UTF-8'
    'DEFAULT_FILTER'        =>  'trim,htmlspecialchars',  //自定义过滤函数


    /*******图片相关的配置*************/
    'IMAGE_CONFIG'	=>	array(
    	'maxSize'	=>	1024*1024,
    	'exts'		=>	array('jpg', 'gif', 'png', 'jpeg'),
    	'rootPath'	=>	'./Public/Uploads/',		//保存图片的路径
    	'viewPath'	=>	'/Public/Uploads/',			//显示图片的路径
    ),
);