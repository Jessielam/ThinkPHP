<?php

//封装一个制作下拉列表的函数，以后需要用到下拉的时候直接调用函数就可以了
/**
 * @return string
 * $params  
 * $tableName 对应的数据表
 *  
 */
function buildSelect($tableName, $selectName, $valueFiledName, $textFieldName, $selectedValue=""){
	$model = D($tableName);
	$data = $model->select();
	$select="<select name='$selectName'><option value=''>请选择</option>";
	foreach($data as $k=>$v){
		$value = $v[$valueFiledName];
		$text = $v[$textFieldName];
		if($selectedValue&&$selectedValue==$value){
			$selected='selected="selected"';
		}else{
			$selected="";
		}
		$select .= '<option '.$selected.' value="'.$value.'">'.$text.'</option>';

	}
	$select .= "</select>";

	echo $select;
}
//封装上传图片的函数
function uploadOne($imageName, $dirName, $thumb=array()){
	if(isset($_FILES[$imageName])&&$_FILES[$imageName]['error']==0){
		$ic = C('IMAGE_CONFIG');
		$upload = new \Think\Upload(array(
			'rootPath'	=> $ic['rootPath'],
			'maxSize'	=> $ic['maxSize'],
			'exts'		=> $ic['exts'],
		));// 实例化上传类 

		$upload->savePath  = $dirName.'/'; // 设置附件上传(子)目录 

		//上传是指定一个要上传的图片的名称，否则会把表单中所有的图片都处理了，之后想其他图片就找不到图片了
		$info = $upload->upload(array($imageName=>$_FILES[$imageName]));
		if(!$info){
			return array(
				'ok'	=> 0,
				'error'	=> $upload->getError(),
			);
		}else{
			$ret['ok'] = 1;
			$ret['images'][0] = $logoName = $info[$imageName]['savepath'].$info[$imageName]['savename'];
			//判断是否有缩略图生成
			if($thumb){
				$image = new \Think\Image();
				foreach($thumb as $k=>$v){
					//缩略图的路径名称
					$ret['images'][$k+1] = $info[$imageName]['savepath'].'thumb_'.$k.'_'.$info[$imageName]['savename'];
					//打开要处理的图片
					$image->open($ic['rootPath'].$logoName);
					$image->thumb($v[0],$v[1])->save($ic['rootPath'].$ret['images'][$k+1]);
				}
			}
			return $ret;
		}
	}
}

//封装删除图片的函数
function deleteImage($images=array()){
	$ic = C('IMAGE_CONFIG');
	foreach($images as $v){
		unlink($ic['rootPath'].$v);
	}
}

//封装显示图片的函数
function showImage($url, $width='', $height=''){
	$ic = C('IMAGE_CONFIG');
	if($width){
		$width="width='$width'";
	}
	if($height){
		$height="height='$height'";
	}
	echo "<img $width $height src='{$ic['viewPath']}$url' />";
}

//用于过滤攻击代码，效率较低，尽量少用
function removeXSS($data){
	require_once './HtmlPurifier/HTMLPurifier.auto.php';
	$_clean_xss_config = HTMLPurifier_Config::createDefault();
	$_clean_xss_config->set('Core.Encoding', 'UTF-8');
	//设置保留的标签
	$_clean_xss_config->set('HTML.Allowed','div,b,strong,i,em,a[href|title],ul,ol,li,p[style],br,span[style],img[width|height|alt|src]');
	$_clean_xss_config->set('CSS.AllowedProperties', 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align');
	$_clean_xss_config->set('HTML.TargetBlank', TRUE);
	$_clean_xss_obj = new HTMLPurifier($_clean_xss_config);

	//执行过滤
	return $_clean_xss_obj->purify($data);
}
