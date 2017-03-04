<?php
namespace Home\Controller;
class IndexController extends NavController {
	
    public function index(){
        //取出商品信息，实例化后台的商品模型
        $goodsModel = D('Admin/goods');
        //获取疯狂抢购的产品,默认取出五件
        $promoteData = $goodsModel->getPromoteProducts();

        //获取其他类型的产品 is_new|is_hot|is_best 
        $newProductData = $goodsModel->getRecProducts('is_new'); //新货上市
        $hotProductData = $goodsModel->getRecProducts('is_hot'); //热销产品
        $bestProductData = $goodsModel->getRecProducts('is_best');//精品

        //获取推荐分类--楼层数据
        $catgoryModel = D('Admin/category');
        $floorData = $catgoryModel->getFloorData();

        //把产品信息输出到页面中
        $this->assign(array(
            'promoteData'   =>  $promoteData,
            'newProductData' => $newProductData,
            'hotProductData' => $hotProductData,
            'bestProductData' => $bestProductData,
            'floorData' => $floorData,
        ));
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