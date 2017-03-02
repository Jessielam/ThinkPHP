<?php
namespace Home\Controller;
class IndexController extends NavController {
	
    public function index(){
        // 设置页面信息
    	$this->assign(array(
    		'_show_nav' => 1,
    		'_page_title' => '首页',
    		'_page_keywords' => '首页',
    		'_page_description' => '首页',
    	));
    	$this->display();
    }
}