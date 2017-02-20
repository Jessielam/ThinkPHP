<?php
namespace Admin\Model;
use Think\Model;
class AdminModel extends Model 
{
	protected $insertFields = array('username','password','cpassword','captcha');
	protected $updateFields = array('id','username','password','cpassword');
	//添加和修改管理员的
	protected $_validate = array(
		array('username', 'require', '用户名不能为空！', 1, 'regex', 3),
		array('username', '', '用户名已经存在！', 1, 'unique', 3),
		array('username', '1,30', '用户名的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('password', 'require', '密码不能为空！', 1, 'regex', 1),
		array('cpassword', 'password', '密码必须一致！', 1, 'confirm',3),
	);

	//为登录表当制作一个验证规则
	public $_login_validate = array(
		array('username','require','用户名不能为空！',1),
		array('password','require','密码不能为空！',1),
		array('captcha','require','验证码不能为空！',1),
		array('captcha','check_verify','验证码错误！',1,'callback'),
	);

	//验证输入的验证码是否正确
	function check_verify($code, $id=''){
		$verify = new \Think\Verify();
		return $verify->check($code, $id);
	}

	public function login(){
		//从模型中获取登录的用户名和密码
		$username = $this->username;
		$password = $this->password;

		//先查询这个用户名和密码是否存在
		$user = $this->where(array(
			'username'	=>	array('eq', $username),
		))->find();

		if($user){
			//如果用户名和密码都相同的
			if($user['password']==md5($password)){
				//登录成功存进session
				session('id',$user['id']);
				session('username',$user['username']);
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

	public function search($pageSize = 20)
	{
		/**************************************** 搜索 ****************************************/
		$where = array();
		if($username = I('get.username'))
			$where['username'] = array('like', "%$username%");
		/************************************* 翻页 ****************************************/
		$count = $this->alias('a')->where($where)->count();
		$page = new \Think\Page($count, $pageSize);
		// 配置翻页的样式
		$page->setConfig('prev', '上一页');
		$page->setConfig('next', '下一页');
		$data['page'] = $page->show();
		/************************************** 取数据 ******************************************/
		$data['data'] = $this->alias('a')
			->field('a.*, GROUP_CONCAT(c.role_name) role_name')
			->join('LEFT JOIN __ADMIN_ROLE__ b ON a.id=b.admin_id
					LEFT JOIN __ROLE__ c ON b.role_id=c.id')
			->where($where)->group('a.id')->limit($page->firstRow.','.$page->listRows)->select();
		return $data;
	}
	// 添加前
	protected function _before_insert(&$data, $option)
	{
		//插入数据前对密码进行加密
		$data['password'] = md5($data['password']);
	}
	// 添加后
	protected function _after_insert(&$data, $option)
	{
		//把该管理员的角色id插入到片p39_admin_role表中
		$roleId = I('post.role_id');
		$arModel = D('admin_role');

		foreach($roleId as $k=>$v){
			$arModel->add(array(
				'admin_id'	=> $data['id'],
				'role_id'	=> $v,
			));
		}
	}
	// 修改前
	protected function _before_update(&$data, $option)
	{
		//修改后把中间表的数据对应的记录删除在进行添加
		$roleId = I('post.role_id');
		$arModel = D('admin_role');
		$arModel->where(array(
			'admin_id'	=> array('eq', $option['where']['id']),
		))->delete();

		foreach($roleId as $k=>$v){
			$arModel->add(array(
				'admin_id'	=> $option['where']['id'],
				'role_id'	=>$v
			));
		}
		if($data['password']){
			$data['password'] = md5($data['password']);
		}else{
			unset($data['password']);
		}
	}
	// 删除前
	protected function _before_delete($option)
	{
		//超级管理员是不能被删除的
		if($option['where']['id']==1)
		{
			$this->error = '超级管理员不能被删除';
			return FALSE;
		}
	}
	/************************************ 其他方法 ********************************************/
}