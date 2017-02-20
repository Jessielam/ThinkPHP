<?php
namespace Home\Controller;
class IndexController extends NavController {
	
    public function index(){

    	$this->assign(array(
    		'_page_title'	=> "京西商城",
    		'_page_keywords'	=> '网上商城,php技术',
    		'_page_description'	=> 'php商城',
    		'_show_nav'		=>	1,
    	));
        $this->display();
    }
}