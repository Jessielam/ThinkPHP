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
	
	// 找一个分类所有子分类的ID
	public function getChildren($catId)
	{
		// 取出所有的分类
		$data = $this->select();
		// 递归从所有的分类中挑出子分类的ID
		return $this->_getChildren($data, $catId, TRUE);
	}
	/**
	 * 递归从数据中找子分类
	 */
	private function _getChildren($data, $catId, $isClear = FALSE)
	{
		static $_ret = array();  // 保存找到的子分类的ID
		if($isClear)
			$_ret = array();
		// 循环所有的分类找子分类
		foreach ($data as $k => $v)
		{
			if($v['parent_id'] == $catId)
			{
				$_ret[] = $v['id'];
				// 再找这个$v的子分类
				$this->_getChildren($data, $v['id']);
			}
		}
		return $_ret;
	}
	// 获取树形数据
	public function getTree()
	{
		$data = $this->select();
		return $this->_getTree($data);
	}
	private function _getTree($data, $parent_id=0, $level=0)
	{
		static $_ret = array();
		foreach ($data as $k => $v)
		{
			if($v['parent_id'] == $parent_id)
			{
				$v['level'] = $level;  // 用来标记这个分类是第几级的
				$_ret[] = $v;
				// 找子分类
				$this->_getTree($data, $v['id'], $level+1);
			}
		}
		return $_ret;
	}
	
	protected function _before_delete(&$option)
	{
		/************** 修改原$option，把所有子分类的ID也加进来，这样TP会一起删除掉 *******/
		// 先找出所有子分类的ID
		$children = $this->getChildren($option['where']['id']);
		$children[] = $option['where']['id'];
		$option['where']['id'] = array(
			0 => 'IN',
			1 => implode(',', $children),
		);
	}

	/**
	 * 获取导航条上的数据
	 *
	 */
	public function getNavData()
	{
		// 先从缓存中取出数据
		$catData = S('catData');
		// 判断如果没有缓存或者缓存过期就重新构造数组
		if(!$catData)
		{
			// 取出所有的分类
			$all = $this->select();
			$ret = array();
			// 循环所有的分类找出顶级分类
			foreach ($all as $k => $v)
			{
				if($v['parent_id'] == 0)
				{
					// 循环所有的分类找出这个顶级分类的子分类
					foreach ($all as $k1 => $v1)
					{
						if($v1['parent_id'] == $v['id'])
						{
							// 循环所有的分类找出这个二级分类的子分类
							foreach ($all as $k2 => $v2)
							{
								if($v2['parent_id'] == $v1['id'])
								{
									$v1['children'][] = $v2;
								}
							}
							$v['children'][] = $v1;
						}
					}
					$ret[] = $v;
				}
			}
			// 把数组缓存1天
			S('catData', $ret, 86400);
			return $ret;
		}
		else
			return $catData;  // 有缓存直接返回缓存数据
	}

	//获取首页楼层数据
	public function getFloorData(){
		$floorData = S('floorData');
		if($floorData){
			return $floorData;
		}else{
			//获取推荐的顶级分类并返回
			$ret = $this->where(array(
				'parent_id'	=> array('eq',0),
				'is_floor'	=> array('eq','是'),	
			))->select();

			//获取推荐二级分类对应的产品
			$goodsModel = D('Admin/goods');

			//推荐顶级分类下未推荐的二级分类以及推荐的二级分类
			foreach($ret as $k=>$v){
				//取出这个楼层下所有的商品
				$goodIds = $goodsModel->getGoodsIdByCatId($v['id']);

				$ret[$k]['brand'] = $goodsModel->alias('a')
				->field('DISTINCT brand_id,b.id,b.logo')
				->join('LEFT JOIN __BRAND__ b ON a.brand_id=b.id')
				->where(array(
						'a.id' => array('in', $goodIds),
						'a.brand_id' => array('neq',0)
					))->limit(9)
				->select();	

				$ret[$k]['subCat'] = $this->where(array(
					'parent_id' => array('eq', $v['id']),
					'is_floor'	=> array('eq','否'),
				))->select();

				$ret[$k]['recSubCat'] = $this->where(array(
					'parent_id' => array('eq', $v['id']),
					'is_floor'	=> array('eq','是'),
				))->select();

				foreach($ret[$k]['recSubCat'] as $k1=>&$v1){
					//考虑扩展分类
					$gids = $goodsModel->getGoodsIdByCatId($v1['id']);
					//获取对应的产品数据, 默认取出8件
					$v1['goods'] = $goodsModel->field('id,goods_name,shop_price,mid_logo')
					->where(array(
						'is_floor'	=> array('eq','是'),
						'is_on_sale'	=> array('eq','是'),
						'id'	=> array('in',$gids)
					))->limit(8)->order('sort_num ASC')->select();
				}
			}
			//缓存数据，有效期设置为一天
			S('floorData', $ret, 86400);
			return $ret;
		}
	}
}














