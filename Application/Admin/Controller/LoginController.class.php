<?php
namespace Admin\Controller;
use Think\Controller;

class LoginController extends Controller 
{
	public function login(){
		$model = D('admin');
		//接收和验证表单
		if(IS_POST){
			if($model->validate($model->_login_validate)->create()){
				if($model->login()){
					$this->success('登录成功',U('Index/index'));
					exit;
				}
			}

			$this->error($model->getError());
		}
		$this->display();
	}

	public function chkcode(){
		$Verify = new \Think\Verify(array(
			'fontSize'	=> 30,	//字体的大小
			'length'	=> 4,	//验证码位数
			'useNoise'	=> TRUE,//关闭验证码杂点
		));

		$Verify->entry();
	}

	public function logout(){
		$model = D('admin');
		$model->logout();
		redirect('login');
	}
}