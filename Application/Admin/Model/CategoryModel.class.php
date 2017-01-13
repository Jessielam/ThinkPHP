<?php
namespace Admin\Model;
use Think\Model;

class CategoryModel extends Model 
{
	protected $insertFields = array('cat_name','parent_id');
	protected $updateFields = array('id','cat_name','parent_id');

	protected $_validate = array(
		array('cat_name', 'require', '分类名称不能为空！', 1, 'regex', 3),
	);
	
	public function getChildren($cateId){
		//取出所有的分类
		$data = $this->select();

		return $this->_getChildren($data, $cateId, TRUE);
	}

	private function _getChildren($data, $cateId, $isClear=FALSE){

		static $_ret = array();

		if($isClear){
			$_ret = array();
		}
		foreach($data as $k=>$v){
			if($v['parent_id']==$cateId){
				$_ret[] = $v['id'];

				$this->_getChildren($data, $v['id']);
			}
		}

		return $_ret;
	}

	public function getTree(){

		$data = $this->select();
		
		return $this->_getTree($data);
	}

	private function _getTree($data, $parent_id=0, $level=0){

		static $_ret = array();

		foreach($data as $k=>$v){
			if($v['parent_id'] == $parent_id){
				$v['level'] = $level;
				$_ret[] = $v;

				$this->_getTree($data, $v['id'], $level+1);
			}
		}
		
		return $_ret;
	}

	/***************删除分类前执行一下钩子函数**********************/
	protected function _before_delete(&$option){

		/*****************第一种方法************/
		/*
		$children = $this->getChildren($option['where']['id']);
		if($children){
			$children = implode(",", $children);
		}

		$model = new \Think\Model;
		$model->table('__CATEGORY__')->delete($children);
		*/

		/********************第二种方法***********/
		//批量删除(拼凑成一个固定的格式)
		$children = $this->getChildren($option['where']['id']);
		$children[] = $option['where']['id'];
		$option['where']['id'] = array(
			0	=>	'IN',
			1	=>	implode(',', $children),
		);
	}
}