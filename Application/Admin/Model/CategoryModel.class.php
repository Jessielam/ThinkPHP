<?php
namespace Admin\Model;
use Think\Model;

class CategoryModel extends Model 
{
	protected $insertFields = array('cat_name','parent_id','is_floor');
	protected $updateFields = array('id','cat_name','parent_id','is_floor');

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

	//首页导航条数据--> 获取分类
	public function getNavData(){
		//读取缓存,如果已经有缓存直接返回，否则查找数据库
		$catData = S('catData');
		if(!$catData){
			$all = $this->select();
			$ret = array();
			foreach($all as $k=>$v){
				if($v['parent_id']==0){
					foreach($all as $k1=>$v1){
						if($v1['parent_id']==$v['id']){
							foreach($all as $k2=>$v2){
								if($v2['parent_id']==$v1['id']){
									$v1['children'][] = $v2;
								}
							}
							$v['children'][] = $v1;
						}
					}
					$ret[] = $v;
				}
			}
			S('catData', $ret, 86400);
			return $ret;
		}else{
			return $catData;
		}
	}

	public function getFloorData(){
		$floorData = S('floorData');
		if($floorData){
			return $floorData;
		}else{
			/*********** 想获取推荐的顶级分类***********/
			$ret = $this->where(array(
					'is_floor' => array('eq', '是'),
					'parent_id'	=> array('eq', 0),
				))->select();

			$goodsModel = D('Admin/goods');
			/******* 每个楼层取出楼层的数据*******/
			foreach($ret as $k=>$v){
				// 获取未推荐的二级分类
				//先取出该分类先所有的商id
				$goodsid = $goodsModel->getGoodsIdByCatId($v['id']);

				//进行连表，取出该分类有用到的品牌信息
				$ret[$k]['brand'] = $goodsModel->alias('a')
				->field('DISTINCT brand_id, b.brand_name,b.logo')
				->join('LEFT JOIN __BRAND__ b ON a.brand_id=b.id')
				->where(array(
					'a.id' => array('in',$goodsid),
					'a.brand_id'  => array('neq',0)
				))->limit(9)->select();

				$ret[$k]['subCat']=$this->where(array(
					'parent_id'	=> array('eq', $v['id']),
					'is_floor'	=> array('eq', '否')
				))->select();

				// 获取推荐的二级分类
				$ret[$k]['recSubCat']=$this->where(array(
					'parent_id'	=> array('eq', $v['id']),
					'is_floor'	=> array('eq', '是')
				))->select();

				//取出推荐二级分类的产品的数据
				foreach($ret[$k]['recSubCat'] as $k1=> &$v1){
					//获取对应的商品id
					$gids = $goodsModel->getGoodsIdByCatId($v1['id']);
					$v1['goods'] = $goodsModel->field('id,mid_logo,goods_name,shop_price')->where(array(
						'is_on_sale' => array('eq', '是'),
						'is_floor'	=> array('eq', '是'),
						'id'  => array('IN', $gids),
					))->order('order_num ASC')->limit(8)->select();
				}
			}
			S('floorData', $ret, 86400);
			return $ret;
		}
	}
}