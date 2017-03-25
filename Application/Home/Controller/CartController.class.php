<?php
namespace Home\Controller;
use Think\Controller;

class CartController extends Controller{
	public function add(){
		if(IS_POST){
			
		    //对提交的表单进行验证处理
		    $cartModel = D('cart');
			if($cartModel->create(I('post.'), 1)){
				//如果验证通过
				if($cartModel->add()){
					$this->success('添加成功',U('lst'));
					exit;
				}
			}else{
				$this->error('操作失败，原因：'.$cartModel->getError());
			}
		}
	}

	public function lst(){
		$carModel = D('cart');
		$data = $carModel->cartList();
//var_dump($data);die;
		$this->assign(array(
			'data'	=> $data,
		));
		$this->assign(array(
    		'_page_title' => '我的购物车',
    		'_page_keywords' => '购物车，shoppingcart',
    		'_page_description' => '购物车',
    	));
		$this->display();
	}
}