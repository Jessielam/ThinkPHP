<?php
namespace Admin\Controller;

class CategoryController extends BaseController {

    public function lst(){
        //实例化模型
        $model = D('category');
        $data = $model->getTree();

        $this->assign(array(
            'data'  =>  $data,
            '_web_title' =>  '分类列表',
            '_page_btn_name'  =>  '添加新分类' ,
            '_page_title'    =>  '分类列表',
            '_page_btn_link'  => U('add'),

        ));

        $this->display();
    }

    //添加分类
    public function add(){
        $model = D('category');

        if(IS_POST){
            if($model->create(I('post.'),1)){
                if($model->add()){
                    $this->success('添加成功', U('lst'));
                    exit;
                }
            }
            $error = $model->getError();
            $this->error($error);
        }

        $catData = $model->getTree();

        $this->assign(array(
            'catData'   =>  $catData,
            '_web_title'    =>  '添加分类',
            '_page_title'   =>  '添加分类',
            '_page_btn_name'    =>  '分类列表',
            '_page_btn_link'    =>  U('lst'),
        ));

        $this->display();
    }

    //修改分类
    public function edit()
    {
        $id = I('get.id');
        // M:生成的是父类模型\Think\Model
        $model = D('category');
        if(IS_POST)
        {   
            if($model->create(I('post.'), 2))
            {   
                if(FALSE !== $model->save())
                {
                    $this->success('修改成功！', U('lst', array('p' => I('get.p', 1))));
                    exit;
                }
            }
            $this->error($model->getError());
        }
        $data = $model->find($id);
        
        // 取出所有的分类做下拉框
        $catData = $model->getTree();
        // 取出当前分类的子分类
        $children = $model->getChildren($id);
        
        $this->assign(array(
            'children' => $children,
            'data' => $data,
            'catData' => $catData,
        ));

        // 设置页面中的信息
        $this->assign(array(
            '_page_title' => '修改分类',
            '_page_btn_name' => '分类列表',
            '_page_btn_link' => U('lst'),
        ));
        $this->display();
    }

    public function delete(){
        //获取需要删除记录的id
        $id = I('get.id');
        //实例化模型
        $model = D('category');
        if( FALSE!==$model->delete($id) ){
            $this->success('删除成功!');
        }else{
            $this->error("删除失败！详情：", $this->getError());
        }
    }
}