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

    public function goods(){
        //获取该产品的面包屑
        //取到该产品的id
        $id = I('get.id');
        //根据产品id取出该产品的分类id
        $goodsModel = D('Admin/goods');
        $info = $goodsModel->find($id);

        //制作导航条(面包屑)
        $catModel = D('Admin/category'); 
        $catPath = $catModel->parentPath($info['cat_id']);

        //取出该商品的商品相册
        $gpModel = D('goods_pic');
        $gpData = $gpModel->where(array(
            'goods_id'=>array('eq',$id),
        ))->select();

        //取出商品属性
        $gaModel = D('goods_attr');
        $gaData = $gaModel->alias('a')
        ->field('a.*,b.attr_name,b.attr_type')
        ->join('LEFT JOIN __ATTRIBUTE__ b ON a.attr_id=b.id')
        ->where(array(
            'a.goods_id' => array('eq',$id),
        ))->select();
        
        //把取出来的属性按照可选和唯一分开存放
        $uniArr = array(); //唯一属性
        $mulArr = array(); //可选属性 
        foreach($gaData as $k=>$v){
            if($v['attr_type']=="唯一"){
                $uniArr[] = $v;
            }else{
                //把同一个属性的放在一起  => 三维
                $mulArr[$v['attr_name']][] = $v;
            }
        }

        //取出这件商品的会员价格
        $mpModel = D('member_price');
        $mpData = $mpModel->alias('a')
            ->field('a.price,b.level_name')
            ->join('LEFT JOIN __MEMBER_LEVEL__ b ON a.level_id=b.id')
            ->where(array(
                'goods_id' => array('eq',$id),
            ))
            ->select();

        $viewPath = C('IMAGE_CONFIG');

        $this->assign(array(
            'catPath'   => $catPath,
            'info'      => $info,
            'gpData'    => $gpData,
            'uniArr'    => $uniArr,
            'mulArr'    => $mulArr,
            'mpData'    => $mpData,
            'viewPath'  => $viewPath['viewPath'],
        ));

        // 设置页面信息
        $this->assign(array(
            '_show_nav' => 0,
            '_page_title' => $info['goods_name'],
            '_page_keywords' => '首页',
            '_page_description' => '首页',
        ));
        $this->display();
    }

    // 处理浏览历史
    public function displayHistory()
    {
        $id = I('get.id');
        // 先从COOKIE中取出浏览历史的ID数组
        $data = isset($_COOKIE['display_history']) ? unserialize($_COOKIE['display_history']) : array();
        // 把最新浏览的这件商品放到数组中的第一个位置上
        array_unshift($data, $id);
        // 去重
        $data = array_unique($data);
        // 只取数组中前6个
        if(count($data) > 6)
            $data = array_slice($data, 0, 6);
        // 数组存回COOKIE
        setcookie('display_history', serialize($data), time() + 30 * 86400, '/');
        // 再根据商品的ID取出商品的详细信息
        $goodsModel = D('Goods');
        //var_dump($data);echo "<hr />";
        $data = implode(',', $data);
        $gData = $goodsModel->field('id,mid_logo,goods_name')->where(array(
            'id' => array('in', $data),
            'is_on_sale' => array('eq', '是'),
        ))->order("FIELD(id,$data)")->select();
       // var_dump($data);
        //echo "<hr/>";
        //echo $goodsModel->getLastSql();
        echo json_encode($gData);
    }

    /**
     * 计算该件商品的会员价格，返回促销价格与会员价格中小的值
     * @param $goodsId  商品id  
     * @return price
     * @time 2017-3-12
     */
    public function ajaxGetMemberPrice(){
        $id = I('get.goodsId');
        $model = D('Admin/goods');
        echo $model->getMemberPrice($id);
    }

    public function testCurl(){
        $param = I('get.vpcode');
        if($param){
            echo json_encode(array(
                'result'    => true,
                'code'  => $param,
                'email' =>  'homelam@qq.com',
                'name'  =>  'homelam',
                'phone' => '1234566435'
            ));
        }else{
            echo json_encode(array(
                'result'    => false,
            ));
        }
    }
}