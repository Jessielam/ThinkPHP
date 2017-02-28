<?php
namespace Home\Controller;
class IndexController extends NavController {
	
    public function index(){

        $goodsModel = D('Admin/goods');
        $promoteGoods = $goodsModel->getPromoteGoods();

        $isHotGoods = $goodsModel->getRecGoods("is_hot");
        $isNewGoods = $goodsModel->getRecGoods("is_new");
        $isBestGoods = $goodsModel->getRecGoods("is_best");

        $this->assign(array(
            'promoteGoods'=> $promoteGoods,
            'isHotGoods'    => $isHotGoods,
            'isNewGoods'    => $isNewGoods,
            'isBestGoods'   => $isBestGoods
        ));

    	$this->assign(array(
    		'_page_title'	=> "京西商城",
    		'_page_keywords'	=> '网上商城,php技术',
    		'_page_description'	=> 'php商城',
    		'_show_nav'		=>	1,
    	));
        $this->display();
    }
}