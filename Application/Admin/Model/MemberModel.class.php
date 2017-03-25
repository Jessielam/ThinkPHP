<?php
namespace Admin\Model;
use Think\Model;

class MemberModel extends Model 
{
	protected $insertFields = array('username','password','cpassword','chkcode','must_click');
	protected $updateFields = array('id','username','password','cpassword');

	protected $_validate = array(
		array('username', 'require', '用户名不能为空！', 1, 'regex', 3),
		array('username', '1,30', '用户名的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('password', 'require', '密码不能为空！', 1, 'regex', 1),
		array('password', '6,20', '密码的值最长不能超过 6-20 个字符！', 1, 'length', 3),
		array('cpassword', 'password', '两次密码输入不一致！', 1, 'confirm', 3),
		array('username', '', '用户名已经存在！', 1, 'unique', 3),
		array('chkcode', 'require', '验证码不能为空！', 1),
		array('chkcode', 'check_verify', '验证码不正确！', 1, 'callback'),
		array('must_click', 'require', '必须同意注册协议！', 1, 'regex', 3),
	);
	// 为登录的表单定义一个验证规则 
	public $_login_validate = array(
		array('username', 'require', '用户名不能为空！', 1),
		array('password', 'require', '密码不能为空！', 1),
		array('chkcode', 'require', '验证码不能为空！', 1),
		array('chkcode', 'check_verify', '验证码不正确！', 1, 'callback'),
	);

	//验证验证码是否正确
	function check_verify($code, $id=''){
		$verify = new \Think\Verify();
		return $verify->check($code, $id);
	}

		public function login(){
		//从模型中获取登录的用户名和密码
		$username = $this->username;
		$password = $this->password;
		//先查询这个用户名和密码是否存在
		$user = $this->field('id,jifen,password,username')->where(array(
			'username'	=>	array('eq', $username),
		))->find();

		if($user){
			//如果用户名和密码都相同的
			if($user['password']==md5($password)){
				//登录成功存进session
				session('m_id',$user['id']);
				session('m_username',$user['username']);

				//获取该用户会员id
				$mlModel = D('member_level');
				$levelId = $mlModel->field('id')
				->where(array(
					'jifen_bottom' => array('elt', $user['jifen']),
					'jifen_top'	=> array('egt', $user['jifen']),
				))->find();

				session('level_id', $levelId);

				//用户登录之后，把cookie中的数据移到数据库中
				$catModel = D('Home/Cart');
				$catModel->moveDataToDb();
				return TRUE;
			}else{
				$this->error='密码不正确,请重新输入';
				return FALSE;
			}
		}else{
			$this->error = "用户名不存在,请重新输入";
			return FALSE;
		}
	}
	//退出登录
	public function logout(){
		//把登录的session信息清除
		session(null);
	}

	protected function _before_insert(&$data, $option){
		$data['password'] = md5($data['password']);
	}
}