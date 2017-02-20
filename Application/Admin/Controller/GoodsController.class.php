<?php
namespace Admin\Controller;

class GoodsController extends BaseController {

   //显示和处理表单
   public function add(){
        //判断用户是否提交了表单

        if(IS_POST){   
            //var_dump($_POST);die;   
            $model = D('goods');
            //接收数据并保存到模型中
            /**
             *  默认有两个参数
             *  1、第一个参数：要接收的默认是$_POST
             *  2、表单的类型，当前是添加还是修改表单  1代表添加，2代表修改
             *  $_POST: 表单中的原始数据;
             *  I('post.')过滤之后的$_POST数据，过滤XSS攻击
            */
            if($model->create(I('post.'), 1)){ //数据验证
                //插入到数据库
                if($model->add()){   //在add()里有现调用_before_insert 这个钩子函数
                    //显示成功信息并等待1秒（默认）后跳转
                    $this->success('添加成功!', U('lst'));
                    exit;
                }
            }
            //如果上面失败，在这里处理失败的代码
            //从控制器取出错误信息
            $error = $model->getError();
            //由控制器显示错误信息，并且3秒跳回上一个页面
            $this->error($error);
        }
        //显示表单

        //用于在添加表单中显示会员的名称（级别）
        $memberLevelModel = D('member_level');
        $memberData = $memberLevelModel->select();

        //获取所有分类
        $catModel = D('category');
        $catData = $catModel->getTree();

        $this->assign(array(
            'memberData'        =>  $memberData,
            'catData'           =>  $catData,
            '_web_title'        =>  '添加新商品',
            '_page_btn_name'    =>  '商品列表' ,
            '_page_title'       =>  '添加新商品',
            '_page_btn_link'    =>  U('lst'),

        ));

        $this->display();
    }

    //显示和处理表单
   public function edit(){
        $id = I('get.id');
        $model = D('goods');
        //判断用户是否提交了表单
        if(IS_POST){
            if($model->create(I('post.'), 2)){ //数据验证 
                if( FALSE!==$model->save()){  //如果验证失败就返回false，如果成功则返回受影响的条数 
                    $this->success('修改成功!', U('lst'));
                    exit;
                }
            }
            
            $error = $model->getError();
            $this->error($error);
        }
        $data = $model->find($id); //取出对应的数据
        $this->assign('data', $data);

        //获取分类信息
        $catModel = D('category');
        $catData = $catModel->getTree();

        //获取会员信息
        $mlModel = D('member_level');
        $mlData = $mlModel->select();

        //获取会员的价格
        $mpModel = D('member_price');
        $mpData = $mpModel->where(array(
            'goods_id'  => array('eq', $id),
        ))->select();

        foreach($mpData as $k=>$v){
            $_mpData[$v['level_id']] = $v['price'];
        }

        //获取商品相册信息
        $gpModel = D('goods_pic');
        $gpData = $gpModel->field('id, mid_pic')->where(array(
            'goods_id'  =>  array('eq', $id),
        ))->select();

        //修改的时候，获取该商品的扩展分类
        $gcModel = D('goods_cat');
        $gcData = $gcModel->field('cat_id')->where(array(
                'goods_id'  => array('eq', $id),
            ))->select();

        /*************获取商品的属性信息************/
        /*$gaModel = D('goods_attr');
        $gaData = $gaModel
            ->alias('a')
            ->field('a.*,b.attr_name,b.attr_type,b.attr_option_values')
            ->join('LEFT JOIN __ATTRIBUTE__ b ON a.attr_id=b.id')
            ->where(array(
                'goods_id'  =>  array('eq',$id),
            ))->select();*/

        //取出当前类型下的所有属性
        $attrModel = D('attribute');
        $attrData = $attrModel
            ->alias('a')
            ->field('a.id attr_id,a.attr_name,a.attr_type,a.attr_option_values,b.attr_value,b.id')
            ->join('LEFT JOIN __GOODS_ATTR__ b ON (a.id=b.attr_id AND b.goods_id='.$id.')')
            ->where(array(
                'a.type_id' => array('eq',$data['type_id']),
            ))->select();

        //var_dump($attrData);die;
        $this->assign(array(
            'mlData'    =>  $mlData,
            'catData'   =>  $catData,
            'mpData'    =>  $_mpData,
            'gpData'    =>  $gpData,
            'gcData'    =>  $gcData,
            'gaData'    =>  $attrData,
            '_web_title' =>  '修改商品',
            '_page_btn_name'  =>  '商品列表' ,
            '_page_title'    =>  '修改商品',
            '_page_btn_link'  => U('lst'),

        ));
        $this->display();
    }

    //删除商品，注意奥利用钩子函数把商品对应的图片也给删除
    public function delete(){
        //获取需要删除记录的id
        $id = I('get.id');
        //实例化模型
        $model = D('goods');
        if( FALSE!==$model->delete($id) ){
            $this->success('删除成功!');
        }else{
            $this->error("删除失败！详情：", $this->getError());
        }
    }

    public function lst(){
        //实例化模型
        $model = D('goods');

        //返回数据和翻页
        $data = $model->search();  //在商品模型中添加search方法
        //把对应的数据输出到页面中

        $this->assign($data);

        //在lst的分类中添加分类的搜索功能
        //现取出所有的分类
        $catModel = D('category');
        $catData = $catModel->getTree();

        $this->assign(array(
            'catData'   =>  $catData,
            '_web_title' =>  '商品列表',
            '_page_btn_name'  =>  '添加新商品' ,
            '_page_title'    =>  '商品列表',
            '_page_btn_link'  => U('add'),

        ));
        $this->display();
    }

    public function ajaxDelPic(){

        $picId = I('get.picid');

        //实例化模型
        $gpModel = D('goods_pic');
        $pic = $gpModel->field('pic,sm_pic,mid_pic,big_pic')->find($picId);

        //从硬盘上删除对应的图片
        deleteImage($pic);

        //把该条记录从数据库中删除
        $gpModel->delete($picId);
    }

    public function ajaxGetAttr(){
        $typeId = I('get.type_id');
        $attrModel = D('Attribute');
        $attrData = $attrModel->where(array(
            'type_id' => array('eq', $typeId),
        ))->select();
        //以json的方式返回取到的数据
        echo json_encode($attrData);
    }

    //ajax删除商品属性
    public function ajaxDelAttr(){
        $gaId = addslashes(I('get.gaid'));
        $goodsId = addslashes(I('get.goods_id'));
        $gaModel = D('goods_attr');

        $gaModel->delete($gaId);

        //把对应的属性库存也删除了
        $gnModel = D('goods_number');
        $gnModel->where(array(
            'goods_id'  => array('EXP',"=$goodsId or AND FIND_IN_SET($gaId,attr_list)"),
        ))->delete();
    }

    public function qty(){

        $id = I('get.id');

         $gnModel = D('goods_number');

        if(IS_POST){
            //print_r($_POST);die;

            /************修改商品属性的库存量******/
            /****先把原来的记录删除*****/
            $gnModel->where(array('goods_id'=>array('eq', $id),))->delete();

            /***把新的商品属性库存量保存仅数据表***/
            //接收表单提交的数据
            $gaId = I('post.goods_attr_id');
            $gaCount = count($gaId);
            $gn = I('post.goods_number');
            //计算商品属性与商品数量的比例
            $rate = $gaCount/count($gn);
 
            $_i=0;   //当前取第几个商品属性Id
            foreach($gn as $k=>$v){
                $_goodsAttrId = array();
                for($i=0;$i<$rate;$i++){
                    $_goodsAttrId[] = $gaId[$_i];
                    $_i++;
                } 

                //把取出来的数组进行升序排列
                sort($_goodsAttrId, SORT_NUMERIC);  //以数字的形式升序
                //把取出来的id数组转换成为字符串
                $_goodsAttrId = (string)implode(',',$_goodsAttrId);
                $gnModel->add(array(
                    'goods_id'  =>  $id,
                    'goods_number'  =>  $v,
                    'goods_attr_id' =>  $_goodsAttrId,
                ));
            }
            $this->success('修改成功!', U('qty?id='.$id));
            exit;
        }
        //首先取出该商品的商品属性
        //实例化模型
        $gaModel = D('goods_attr');
        $data = $gaModel
            ->alias('a')
            ->field('a.*, b.attr_name')
            ->join('LEFT JOIN __ATTRIBUTE__ b ON a.attr_id=b.id')
            ->where(array(
                'a.goods_id'  => array('eq',$id),
                'b.attr_type' => array('eq','可选'),
            ))->select();

        //转换数组，把二维数组转为三维数组
        $_data = array();
        foreach($data as $k=>$v){
            $_data[$v['attr_name']][] = $v;
        }
        //print_r($_data);die;

        //取出对应已经设置好的商品属性库存量
        $gnData = $gnModel->where(array('goods_id'=>array('eq', $id),))->select();

        //print_r($gnData);die;
        $this->assign(array(
            'gaData'    =>  $_data,
            'gnData'    =>  $gnData,
            '_web_title' =>  '商品库存',
            '_page_btn_name'  =>  '商品列表' ,
            '_page_title'    =>  '商品库存量',
            '_page_btn_link'  => U('lst'),

        ));
        $this->display();
    }
}