create database thinkphp;
use thinkphp;
set names utf8;

drop table if exists p39_goods;
create table p39_goods
(
	id mediumint unsigned not null auto_increment comment 'Id',
	goods_name varchar(150) not null comment '商品名称',
	market_price decimal(10,2) not null comment '市场价格',
	shop_price decimal(10,2) not null comment '本店价格',
	goods_desc longtext comment '商品描述',
	is_on_sale enum('是','否') not null default '是' comment '是否上架',
	is_delete enum('是','否') not null default '否' comment '是否放入回收站',
	addtime datetime not null comment '添加时间',
	logo varchar(150) not null default '' comment '商品原图',
	sm_logo varchar(150) not null default '' comment '商品小图',
	mid_logo varchar(150) not null default '' comment '商品中图',
	big_logo varchar(150) not null default '' comment '商品大图',
	mbig_logo varchar(150) not null default '' comment '商品特大图',
	brand_id mediumint unsigned not null default '0' comment '品牌id',
	cat_id mediumint unsigned not null comment '商品分类',
	type_id mediumint unsigned not null comment '商品类型',
	promote_price decimal(10,2) not null default '0.00' comment '促销价格',
	promote_start_time datetime not null comment '促销开始时间',
	promote_end_time datetime not null comment '促销结束时间',
	is_new enum('是','否') not null default '是' comment '是否新品',
	is_hot enum('是','否') not null default '是' comment '是否是热销产品',
	is_best enum('是','否') not null default '是' comment '是否精品',
 	primary key (id),
	key shop_price(shop_price),
	key addtime(addtime),
	key is_on_sale(is_on_sale),
	key brand_id(brand_id),
	key cat_id(cat_id),
	key type_id(type_id),
	key promote_price(promote_price),
	key promote_start_time(promote_start_time),
	key promote_end_time(promote_end_time),
	key is_new(is_new),
	key is_hot(is_hot),
	key is_best(is_best)
)engine=InnoDB default charset=utf8 comment '商品表';

drop table if exists p39_brand;
create table p39_brand
(
	id mediumint unsigned not null auto_increment comment 'Id',
	brand_name varchar(30) not null comment '品牌名称',
	site_url varchar(150) not null default '' comment '官方网址',
	logo varchar(150) not null default '' comment '品牌Logo图片',
	primary key (id)
)engine=InnoDB default charset=utf8 comment '品牌表';

drop table if exists p39_member_level;
create table p39_member_level
(
	id mediumint unsigned not null auto_increment comment 'Id',
	level_name varchar(30) not null default '' comment '会员名称',
	jifenz_bottom mediumint unsigned not null comment '积分下限',
	jifenz_top mediumint unsigned not null comment '积分上限',
	primary key (id)
)engine=InnoDB default charset=utf8 comment '会员级别';

drop table if exists p39_member_price;
create table p39_member_price
(
	price decimal(10,2) not null comment'会员价格',
	level_id mediumint unsigned not null comment'级别Id',
	goods_id mediumint unsigned not null comment'商品Id',
	key level_id(level_id),
	key goods_id(goods_id)
)engine=InnoDB default charset=utf8 comment '会员级别';

drop table if exists p39_category;
create table p39_category
(
	id mediumint unsigned not null auto_increment comment'分类Id',
	cat_name varchar(30) not null default '' comment '分类名称',
	parent_id mediumint unsigned not null default '0' comment '父级分类，0表示父级分类',
	primary key(id)
)engine=InnoDB default charset=utf8 comment '商品分类表';

drop table if exists p39_goods_pic;
create table p39_goods_pic
(
	id mediumint unsigned not null auto_increment comment 'Id',
	pic varchar(150) not null default '' comment '商品原图',
	sm_pic varchar(150) not null  comment '商品小图',
	mid_pic varchar(150) not null  comment '商品中图',
	big_pic varchar(150) not null  comment '商品大图',
	goods_id mediumint unsigned not null comment '商品id',
	primary key(id),
	key goods_id(goods_id)
)engine=InnoDB default charset=utf8 comment '商品相册';

/**
 *	一件商品可以属于多个扩展分类，搜索任何一个分类都
 *  可以搜索出该商品
 *
 **/
drop table if exists p39_goods_cat;
create table p39_goods_cat
(
	cat_id mediumint unsigned not null comment '分类id',
	goods_id mediumint unsigned not null comment '商品id',
	key cat_id(cat_id),
	key goods_id(goods_id)
)engine=InnoDB default charset=utf8 comment '商品扩展分类';

/***********************属性相关*************************/
/*************类型表***************/
drop table if exists p39_type;
create table p39_type
(
	id mediumint unsigned not null auto_increment comment '类型Id',
	type_name varchar(30) not null comment '类型名称',
	primary key(id)
)engine=InnoDB default charset=utf8 comment '商品类型';

/**********属性表*************/
drop table if exists p39_attribute;
create table p39_attribute
(
	id mediumint unsigned not null auto_increment comment'属性id',
	attr_name varchar(30) not null comment '属性名称',
	attr_type enum('唯一', '可选') not null comment '属性类型',
	attr_option_values varchar(100) not null default ''  comment '属性可选值',
	type_id mediumint unsigned not null comment '所属类型Id',
	primary key(id),
	key type_id(type_id)
)engine=InnoDB default charset=utf8 comment '属性表';

/***********商品属性表***********/
drop table if exists p39_goods_attr;
create table p39_goods_attr
(
	id mediumint unsigned not null auto_increment comment 'Id',
	attr_value varchar(30) not null default '' comment '属性值',
	attr_id mediumint unsigned not null comment '属性Id',
	goods_id mediumint unsigned not null comment '商品Id',
	primary key(id),
	key attr_id(attr_id),
	key goods_id(goods_id)
)engine=InnoDB default charset=utf8 comment '商品属性';

/***********属性库存量表**************/
drop table if exists p39_goods_number;
create table p39_goods_number
(
	goods_id mediumint unsigned not null comment '商品Id',
	goods_number mediumint unsigned not null default '0' comment '商品库存量',
	goods_attr_id varchar(150) not null comment '商品属性Id,如果有多个则用程序把它们用逗号隔开', 
	key goods_id(goods_id)
)engine=InnoDB default charset=utf8 comment '商品库存量';



/******************* RBAC *******************/

/****三个主表，两个中间表****/
drop table if exists p39_privilege;
create table p39_privilege
(	
	id mediumint unsigned not null auto_increment comment 'id',
	pri_name varchar(30) not null comment '权限名称',
	module_name varchar(30) not null default '' comment '模块名称',
	controller_name varchar(30) not null default '' comment '控制器名称',
	action_name varchar(30) not null default '' comment '方法名称',
	parent_id mediumint unsigned not null default '0' comment '上级权限id',
	primary key(id)
)engine=InnoDB default charset=utf8 comment '权限列表';

drop table if exists p39_role;
create table p39_role
(
	id mediumint unsigned not null auto_increment comment 'id',
	role_name varchar(30) not null comment '角色名称',
	primary key(id)
)engine=InnoDB default charset=utf8 comment '角色';

drop table if exists p39_role_pri;
create table p39_role_pri
(
	role_id mediumint unsigned not null comment '角色id',
	pri_id mediumint unsigned not null comment '权限id', 
	key pri_id(pri_id),
	key role_id(role_id)
)engine=InnoDB default charset=utf8 comment '权限与角色';

drop table if exists p39_admin_role;
create table p39_admin_role
(
	role_id mediumint unsigned not null comment '角色id',
	admin_id mediumint unsigned not null comment '管理员id', 
	key admin_id(admin_id),
	key role_id(role_id)
)engine=InnoDB default charset=utf8 comment '管理员与角色';

drop table if exists p39_admin;
create table p39_admin
(
	id mediumint unsigned not null auto_increment comment 'id',
	username varchar(30) not null comment '用户名',
	password char(32) not null comment '密码',
	primary key(id)
)engine=InnoDB default charset=utf8 comment '管理员';

/********** 默认有个管理员 *******/
/***
*	password : admin
**/
INSERT INTO p39_admin(id,username,password)VALUES(1,'root','21232f297a57a5a743894a0e4a801fc3');