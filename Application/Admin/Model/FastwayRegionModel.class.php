<?php
namespace Admin\Model;
use Think\Model;
class FastwayRegionModel extends Model 
{
	protected $insertFields = array('get_Region');
	protected $_validate = array(
	);
	
	public function search($pageSize = 20)
	{
		/**************************************** 搜索 ****************************************/
		$where = array();
		/************************************* 翻页 ****************************************/
		$count = $this->alias('a')->where($where)->count();
		$page = new \Think\Page($count, $pageSize);
		// 配置翻页的样式
		$page->setConfig('prev', '上一页');
		$page->setConfig('next', '下一页');
		$data['page'] = $page->show();
		/************************************** 取数据 ******************************************/
		$data['data'] = $this->alias('a')->where($where)->group('a.id')->limit($page->firstRow.','.$page->listRows)->select();
		return $data;
	}
	// 添加前
	protected function _before_insert(&$data, $option)
	{
		$url="http://api.fastway.org/v2/psc/listrfs?CountryCode=24&api_key=95ebd31cc8a4fc88ca4e7dd0582cedd4&franchiseecode";
		//region--> json data
		$region = json_decode($this->curl($url));
		var_dump($region);die;
	}	
	// 修改前
	protected function _before_update(&$data, $option)
	{
	}
	// 删除前
	protected function _before_delete($option)
	{
		if(is_array($option['where']['id']))
		{
			$this->error = '不支持批量删除';
			return FALSE;
		}
	}
	/************************************ 其他方法 ********************************************/

	//curl 调用fastway api
	// return json_data
	//author: liuhongliang
	//date: 2017-1-23	
	protected function curl($url){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);

		curl_close($curl);

		return $result;
	}
}