<?php
namespace Home\Controller;
use Think\Controller;

class NavController extends Controller{
	public function __construct(){
		parent::__construct();

		$catModel = D('Admin/Category');
		$catData = $catModel->getNavData();

		$this->assign('catData', $catData);
	}
}