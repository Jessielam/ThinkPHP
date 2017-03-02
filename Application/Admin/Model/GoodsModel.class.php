<?php
namespace Admin\Model;
use Think\Model;

class GoodsModel extends Model{
	//调用create方法的时候允许接收的字段
	protected $insertFields = 'goods_name,brand_id,cat_id,market_price,shop_price,is_on_sale,goods_desc,type_id,promote_price,promote_start_time,promote_end_time,is_new,is_hot,is_best,order_num,is_floor';
	protected $updateFields = 'id,cat_id,goods_name,brand_id,market_price,shop_price,is_on_sale,goods_desc,type_id,promote_price,promote_start_time,promote_end_time,is_new,is_hot,is_best,order_num,is_floor';
	//定义验证规则  1、 1表示一定要验证
	protected $_validate = array(
		array('goods_name','require', '商品名称不能为空!', 1),
		array('cat_id','require', '商品必须从属一个主分类!', 1),
		array('market_price','currency', '市场价格必须是一个货币类型！', 1),
		array('shop_price','currency', '本店价格必须是一个货币类型！', 1),
	);

	public function getGoodsIdByCatId($cat_id){
		//获取主分类
		$catModel = D('Admin/category');
		//获取该分类id下的所有子分类
		$children = $catModel->getChildren($cat_id);
		//把自己也加进去进行查找
		$children[] = $cat_id;

		//获取主分类的id
		$gids = $this->field('id')->where(array(
			'cat_id'	=> array('in', $children),	
		))->select();

		//获取扩展分类的ids
		$exModel = D('goods_cat');
		$gids1 = $exModel->field('DISTINCT goods_id id')
			->where(array(
				'cat_id'	=>	array('in', $children),
			))->select();

		//将两个获取的分类进行合并，得到的是一个二维数组
		if($gids&&$gids1){
			$gids = array_merge($gids, $gids1);
		}elseif($gids1){
			$gids = $gids1;
		}

		foreach($gids as $k=>$v){
			if(!in_array($v['id'],$id)){
				$id[] = $v['id'];
			}
		}

		return $id;

	}

	//thinkphp 提供一套方法（钩子方法）

	//数据插入数据库之前 ,第一个参数须是引用传递
	//按引用传递，函数内部想要修改函数外部的数据必须是按引用传递
	protected function _before_insert(&$data, $option){
		//获取当前时间
		$data['addtime'] = date('Y-m-d H:i:s', time());

		//如果使用在线编辑器的话,这个字段需要特殊过滤
		$data['goods_desc'] = removeXSS($_POST['goods_desc']);
		/***************在数据插入数据库之前，先处理logo*****************/
		//1、判断用户有没有上传图片
		if($_FILES['logo']['error']==0)
		{
			$ret = uploadOne('logo','Goods', 	array(
				array(700, 700),
				array(350, 350),
				array(130, 130),
				array(50, 50),
			));
			if($ret['ok'] == 1){
				/********把对应的路径保存到数据库中 ************/
				$data['logo'] = $ret['images'][0];
				$data['sm_logo'] = $ret['images'][4];
				$data['mid_logo'] = $ret['images'][3];
				$data['big_logo'] = $ret['images'][2];
				$data['mbig_logo'] = $ret['images'][1];
			}
		}
	}

	protected function _before_update(&$data, $option){

		$id = $option['where']['id'];  //需要修改商品的ID

		/************修改商品属性**************/
		//有就更新，没有就添加
		$gaId = I('post.goods_attr_id');
		$attrValue = I('post.attr_value');
		$gaModel = D('goods_attr');
		$_i = 0;			//循环次数
		foreach($attrValue as $k=>$v){
			foreach($v as $k1=>$v1){
				if($gaId[$_i]==''){
					$gaModel->add(array(
						'goods_id'	=> $id,
						'attr_id'	=> $k,
						'attr_value'=> $v1
					));
				}else{
					$gaModel->where(array(
						'id' => array('eq',$gaId[$_i]),
					))->setField('attr_value', $v1);
				}
				$_i++;
			}
		}

		//修改商品的扩展分类
		$gcModel = D('goods_cat');
		//先把原先的扩展分类删除
		$gcModel->where(array(
				'goods_id'=> array('eq',$id),
			))->delete();

		$ecid = I('post.ext_cat_id');
		//如果有扩展分类
		if($ecid){
			$gcModel = D('goods_cat');
			foreach($ecid as $k=>$v){
				if(empty($v)){
					continue;
				}
				$gcModel->add(array(
					'cat_id'	=>	$v,
					'goods_id'	=>  $id,
				));
			}
		}



		//如果使用在线编辑器的话,这个字段需要特殊过滤
		$data['goods_desc'] = removeXSS($_POST['goods_desc']);
		/***************在数据插入数据库之前，先处理logo*****************/
		//1、判断用户有没有上传图片
		if($_FILES['logo']['error']==0)
		{
			$ret = uploadOne('logo','Goods',array(
				array(700,700),
				array(350, 350),
				array(130, 130),
				array(50, 50),
			));
			if($ret['ok'] == 1){
				/********把对应的路径保存到数据库中 ************/
				$data['logo'] = $ret['images'][0];
				$data['sm_logo'] = $ret['images'][4];
				$data['mid_logo'] = $ret['images'][3];
				$data['big_logo'] = $ret['images'][2];
				$data['mbig_logo'] = $ret['images'][1];
			}

			/************把原来的图片给删除******************/
			//1、先找出对应图片的路径信息
			$oldlogo = $this->field('logo,sm_logo,mid_logo,big_logo,mbig_logo')->find($id);
			deleteImage($oldlogo);
		}

		//处理商品相册上传的图片
		if(isset($_FILES['pic'])){
	        $pics = array();
	        foreach($_FILES['pic']['name'] as $k=>$v){
	            $pics[] = array(
	                'name'  =>  $v,
	                'type'  =>  $_FILES['pic']['type'][$k],
	                'tmp_name'  =>  $_FILES['pic']['tmp_name'][$k],
	                'error' =>  $_FILES['pic']['error'][$k],
	                'size'  =>  $_FILES['pic']['size'][$k],   
	            );
	        }
	    }
	    //把处理好的数组传给$_FILES,因为uploadOne 是到$_FIELS中获取数据的
	    $_FILES = $pics;
	    //实例化模型
        $picModel = D('goods_pic');

        //循环调用uploadOne函数进行函数的上传
        foreach($pics as $k=>$v){
        	if($v['error']==0){
        		$rets = uploadOne($k, 'Goods', array(
        			array(50,50),
        			array(350,350),
        			array(700,700),
        		));

        		if($rets['ok']==1){
        			$picModel->add(array(
        				'pic'	=>	$rets['images'][0],
        				'sm_pic'	=>	$rets['images'][1],
        				'mid_pic'	=>	$rets['images'][2],
        				'big_pic'	=>	$rets['images'][3],
        				'goods_id'	=>	$id,
        			));
        		}
        	}
        }

		//更新之前先把原来的会员价格给删除
		$mpModel = D('member_price');
		$mpModel->where(array(
			'goods_id'	=>	array('eq', $id),
		))->delete();

		//获取提交的会员价格
		$memberPrice = I('post.member_price');

		foreach($memberPrice as $k=>$v){
			$_v = (float)$v;
			//如果设置了就把对应的会员价格插入数据表中
			if($_v>0){
				$mpModel->add(array(
					'price'		=>	$v,
					'level_id'	=>	$k,
					'goods_id'	=>	$id,	
				));
			}
		}
	}

	//第三个钩子函数 删除之前,删除商品之前把对应的图片也给删除了
	protected function _before_delete($option){
		$id = $option['where']['id'];	//要删除商品的id

		//在商品删除之前，先把商品的属性库存量也一并删除
		$gnModel = D('goods_number');
		$gnModel->where(array('goods_id'=>array('eq',$id),))->delete();

		//在商品删除之前不对应的商品属性也给删除
		$gaModel = D('goods_attr');
		$gaModel->where(array(
			'goods_id'	=>	array('eq',$id),
		))->delete();

		//在商品删除之前，把对应的扩展分类也删除
		$gcModel = D('goods_cat');
		$gcModel->where(array(
				'goods_id'	=> array('eq', $id),
			))->delete();
		//删除商品的时候把对应的商品的商品相册也给删除

		$gpModel = D('goods_pic');
		$pics = $gpModel->field('pic,sm_pic,mid_pic,big_pic')->where(array(
					'goods_id'	=>	array('eq',$id)
				))->select();
		
		foreach($pics as $k=>$v){
			deleteImage($v);
			$gpModel->where(array(
				'goods_id' => array('eq',$id)
			))->delete();
		}

		/************把原来的图片给删除******************/
		//1、先找出对应图片的路径信息
		$oldlogo = $this->field('logo,sm_logo,mid_logo,big_logo,mbig_logo')->find($id);
		
		deleteImage($oldlogo);

		//删除该商品的时候把对应的会员价格删除
		$mpModel = D('member_price');
		$mpModel->where(array(
			'goods_id'	=> array('eq',$id),
		))->delete();

	}

	//自定义一个search方法

	/**
	 *	$perpage 每页显示多少条记录
	 *
	 *
	**/
	public function search($perpage=10){

		/************搜索**********/
		//1、获取搜索表单提交的参数
		$gn = I('get.gn');  //搜索商品名称
		if($gn){
			//如果搜索商品名不为空，则拼凑where条件
			$where['goods_name'] = array('like', "%$gn%"); //WHERE goods_name LIKE '%$gn%'
		}

		$brandId = I('get.brand_id');
		if($brandId){
			$where['brand_id'] = array('eq', $brandId); 
		}

		//搜索商品价格
		$fp = I('get.fp');
		$tp = I('get.tp');
		if( $fp&&$tp ){
			$where['shop_price'] = array('between', array($fp, $tp)); //WHERE shop_price BETWEEN $fp AND $tp;
		}elseif($fp){
			$where['shop_price'] = array('egt', $fp); //WHERE shop_price >= $fp ;
		}elseif($tp){
			$where['shop_price'] = array('elt', $tp); //WHERE shop_price <= $tp;
		}

		//搜索添加时间
		$fa = I('get.fa');
		$ta = I('get.ta');
		if( $fp&&$tp ){
			$where['addtime'] = array('between', array($fa, $ta)); //WHERE addtime BETWEEN $fa AND ta;
		}elseif($fa){
			$where['addtime'] = array('egt', $fa); //WHERE addtime >= $fa ;
		}elseif($ta){
			$where['addtime'] = array('elt', $ta); //WHERE time <= $ta;
		}
		//搜索是否上架
		$ios = I('get.ios');
		if($ios){
			$where['is_on_sale'] = array('eq', $ios);
		}

		/***********排序*****************/
		$orderby = 'id';  //默认的排序方式
		$orderway = 'desc';
		$odby = I('get.orderby');
		if($odby){
			if($odby=="id_asc"){
				$orderway = 'asc';
			}elseif($odby=="price_desc"){
				$orderby = 'shop_price';
			}elseif($odby=="price_asc"){
				$orderby = 'shop_price';
				$orderway = 'asc';
			}
		}

		/**************分类*********/
		$catId = I('get.cat_id');
		if($catId){
			//从主分类和扩展分类取出要查询的商品ids
			$ids = $this->getGoodsIdByCatId($catId);
			$where['a.id'] = array('in', $ids);
		}

		/************翻页**********/
		//1、先取出总的记录
		$count = $this->where($where)->count();// 查询满足要求的总记录数

		$pageObj  = new \Think\Page($count, $perpage);// 实例化分页类 传入总记录数和每页显示的记录数
		$pageObj->setConfig('prev', '上一页');
		$pageObj->setConfig('next', '下一页');
		$pageString  = $pageObj->show();// 分页显示输出

		/**********取出对应的数据记录************/
		$data = $this->order("$orderby $orderway")
		->alias('a')
		->field('a.*, b.brand_name, c.cat_name, GROUP_CONCAT(e.cat_name) ext_cat_name')
		->join('LEFT JOIN __BRAND__ b ON b.id=a.brand_id
				LEFT JOIN __CATEGORY__ c ON a.cat_id=c.id
				LEFT JOIN __GOODS_CAT__ d ON a.id=d.goods_id
				LEFT JOIN __CATEGORY__  e on e.id=d.cat_id')
		->where($where)
		->limit($pageObj->firstRow.','.$pageObj->listRows)
		->group('a.id')
		->select();

		//var_dump($data);die;
		/**********返回数据****************/
		return array(
			'data'	=>	$data,  //数据
			'page'	=>	$pageString,   //翻页字符串
		);
	}

	/**********数据插入数据库会执行以下的钩子方法*********/
	protected function _after_insert($data, $option){

		/******************* 处理商品属性的添加***************/
		$attrValue= I('post.attr_value');
		$gaModel = D('goods_attr');
		foreach($attrValue as $k=>$v){
			//把属性的数组去重
			$v = array_unique($v);
			foreach($v as $k1=>$v1){
				$gaModel->add(array(
					'attr_id'	=>	$k,
					'attr_value'	=>	$v1,
					'goods_id'	=>	$data['id'],
				));
			}
		}

		/*********************处理扩展分类********************/
		//获取提交的分类id (数组)
		$ecid = I('post.ext_cat_id');
		//如果有扩展分类
		if($ecid){
			$gcModel = D('goods_cat');
			foreach($ecid as $k=>$v){
				if(empty($v)){
					continue;
				}
				$gcModel->add(array(
					'cat_id'	=>	$v,
					'goods_id'	=>	$data['id'],
				));
			}
		}

		$mp = I('post.member_price');
		$mpModel = D('member_price');

		foreach($mp as $k=>$v){
			$_v = (float)$v;
			if($_v>0){
				$mpModel->add(array(
					'price'		=>	$v,
					'level_id'	=>	$k,
					'goods_id'	=>	$data['id'],	
				));
			}
		}

		//处理商品相册上传的图片
		if(isset($_FILES['pic'])){
	        $pics = array();
	        foreach($_FILES['pic']['name'] as $k=>$v){
	            $pics[] = array(
	                'name'  =>  $v,
	                'type'  =>  $_FILES['pic']['type'][$k],
	                'tmp_name'  =>  $_FILES['pic']['tmp_name'][$k],
	                'error' =>  $_FILES['pic']['error'][$k],
	                'size'  =>  $_FILES['pic']['size'][$k],   
	            );
	        }
	    }
	    //把处理好的数组传给$_FILES,因为uploadOne 是到$_FIELS中获取数据的
	    $_FILES = $pics;
	    //实例化模型
        $picModel = D('goods_pic');

        //循环调用uploadOne函数进行函数的上传
        foreach($pics as $k=>$v){
        	if($v['error']==0){
        		$ret = uploadOne($k, 'Goods', array(
        			array(50,50),
        			array(350,350),
        			array(700,700),
        		));

        		if($ret['ok']==1){
        			$picModel->add(array(
        				'pic'	=>	$ret['images'][0],
        				'sm_pic'	=>	$ret['images'][1],
        				'mid_pic'	=>	$ret['images'][2],
        				'big_pic'	=>	$ret['images'][3],
        				'goods_id'	=>	$data['id'],
        			));
        		}
        	}
        }
	}

	/**
	 *  网站首页获取促销产品，默认是取出五件
	 * 
	 *	@param $limit  (optional)
	 *  @return Array
	 *  @author Homelam  
	 *  
	 */
	public function getPromoteGoods($limit=5){
		$today = date("Y-m-d H:i");
		$promoteGoods = $this->field('id, goods_name, promote_price, mid_logo')
		->where(array(
			"is_on_sale"	=> array('eq', '是'),
			"promote_price" => array('gt', 0),
			'promote_start_time' => array('elt', $today),
			'promote_end_time'	=> array('egt', $today),
		))->limit($limit)
		->order('order_num ASC')
		->select();

		return $promoteGoods;
	}

	/**
	 *  网站首页获取条件产品，默认是取出五件
	 * 	@param $recType 取出哪一种
	 *	@param $limit  (optional)
	 *  @return Array
	 *  @author Homelam  
	 *  
	 */
	public function getRecGoods($recType, $limit=5){
		$promoteGoods = $this->field('id, goods_name, shop_price, mid_logo')
		->where(array(
			"is_on_sale"	=> array('eq', '是'),
			"$recType" => array('eq', "是"),
		))->limit($limit)
		->order('order_num ASC')
		->select();

		return $promoteGoods;
	}
}		