<?php  

/**
 * 获取网站的配置信息
 * @return [type] [description]
 */
function getConfigInfo(){
	$config = M('Config')->field(true)->where(array('id'=>'default'))->find();
	return $config;
}

function uploadFile(){

	$config = array(
        'maxSize'    =>    3145728,
        'rootPath'   =>     './Uploads/img/', // 设置附件上传根目录
        'savePath'   =>     '', // 设置附件上传（子）目录
        'saveName'   =>    array('uniqid',''),  //图片名称不能重复
        'exts'       =>    array('jpg', 'gif', 'png', 'jpeg'),
        'autoSub'    =>    true,//自动子目录保存文件
        'subName'    =>    array('date','Y-m-d'),//子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
    );

	// 上传文件
	$upload = new  \Think\Upload($config);
	$info   =   $upload->upload();
	if(!$info) {// 上传错误提示错误信息
		$arr=array('error'=>1,"message"=>$upload->getError());
		
	}else{// 上传成功 获取上传文件信息
		$url="/Uploads/img/".$info[1]['savepath'].$info[1]['savename'];
		echo $url;exit;
	}
	
}

function uploadVoice(){
	$config = array(
        'maxSize'    =>    0,
        'rootPath'   =>     './Uploads/mp3/', // 设置附件上传根目录
        'savePath'   =>     '', // 设置附件上传（子）目录
        'saveName'   =>    array('uniqid',''),  //图片名称不能重复
        'exts'       =>    array('mp3','wma','rm','wav','midi','ape','flac'),
        'autoSub'    =>    true,//自动子目录保存文件
        'subName'    =>    array('date','Y-m-d'),//子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
    );

	// 上传文件
	$upload = new  \Think\Upload($config);
	$info   =   $upload->upload();
	if(!$info) {// 上传错误提示错误信息
		$arr=array('error'=>1,"message"=>$upload->getError());
		
	}else{// 上传成功 获取上传文件信息
		$url="/Uploads/mp3/".$info[1]['savepath'].$info[1]['savename'];
		echo $url;exit;
	}
	
}
