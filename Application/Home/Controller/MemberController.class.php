<?php
namespace Home\Controller;
use Think\Controller;

class MemberController extends Controller 
{
	public function ajaxCheckLogin(){
		if(session('m_id')){
			echo json_encode(array(
				'login'	=> 1,
				'username'	=> session('m_username'),
			));
		}else{
			echo json_encode(array(
				'login' => 0,
			));
		}
	}
	
	public function chkcode(){
      $Verify = new \Think\Verify(array(
         'fontSize'  => 30,   //字体的大小
         'length' => 4, //验证码位数
         'useNoise'  => TRUE,//关闭验证码杂点
      ));
      $Verify->entry();
   	}

	public function login()
	{
		if(IS_POST){
			$model = D('Admin/Member');
			if($model->validate($model->_login_validate)->create()){
				if($model->login()){
					$this->success('登录成功',U('/'));
					exit;
				}
			}
			$this->error($model->getError());
		}
		// 设置页面信息
    	$this->assign(array(
    		'_page_title' => '用户登录',
    		'_page_keywords' => 'homelam',
    		'_page_description' => '商城登录',
    	));
		$this->display();
	}

	public function regist(){
		if(IS_POST){
			$model = D('Admin/Member');
			if($model->create(I('post.'), 1)){
				if($model->add()){
					$this->success('注册成功,正在为你跳转', U('login'));
					exit;
				}
			}
			$this->error($model->getError());
		}
		// 设置页面信息
    	$this->assign(array(
    		'_page_title' => '用户注册',
    		'_page_keywords' => 'homelm, 用户注册',
    		'_page_description' => '用户注册',
    	));
		$this->display();
	}
	public function logout(){
		$model = D('Admin/Member');
		$model->logout();
		redirect('/');
	}
}