<?php
return array(
	'tableName' => 'p39_fastway_region',    // 表名
	'tableCnName' => 'fastwaRegion',  // 表的中文名
	'moduleName' => 'Admin',  // 代码生成到的模块
	'withPrivilege' => FALSE,  // 是否生成相应权限的数据
	'topPriName' => '',        // 顶级权限的名称
	'digui' => 0,             // 是否无限级（递归）
	'diguiName' => '',        // 递归时用来显示的字段的名字，如cat_name（分类名称）
	'pk' => 'id',    // 表中主键字段名称
	/********************* 要生成的模型文件中的代码 ******************************/
	// 添加时允许接收的表单中的字段
	'insertFields' => "array()",
	// 修改时允许接收的表单中的字段
	'updateFields' => "array()",
	'validate' => "
		array('region_code', 'require', '城市缩写不能为空！', 1, 'regex', 3),
	",
	/********************** 表中每个字段信息的配置 ****************************/
	'fields' => array(
		'get_Region' => array(
			'text' => 'Region',
			'type' => 'radio',
			'values' => array(
				'是' => '是',
				'否' => '否',
			),
			'default' => '是',
		),
	),
	/**************** 搜索字段的配置 **********************/
	'search' => array(
	),
);