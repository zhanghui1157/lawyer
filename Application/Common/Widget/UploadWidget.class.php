<?php 
namespace Common\Widget;

use Think\Controller;
/**
* layui文件上传
* create by zhanghui
* 2018-7-3
*
* {:W('Common/Upload/showUpload',array(array('filename'=>'file_path','datafile'=>$entity['file_path'],'type'=>'type','datatype'=>$entity['datatype'])))}
*
* 使用方法及参数介绍
* filename------------附件路径的字段
* datafile------------数据库中已经存在的值
* type----------------附件的后缀
* datatype------------数据库中已经存在的附件的后缀
*/


class UploadWidget extends Controller{
	public function showUpload($param){
		$this->assign('param',$param);
		$this->display(T('Application://Common@Widget/upload'));
	}
	/***********************上传需要的方法*************************/
	//上传的接口，写在控制器和函数里面的
	public function uploadFile(){
    	$res=uploadFile('Admin','','pdf,xls,xlsx');
    	$this->ajaxReturn($res);
    }
    
 //    function uploadFile($detail,$key,$type=''){
	// 	if($key){
	// 		$rootPath='./File/'.$detail.'/'.$key."/"; // 设置附件上传根目录
	// 	}else{
	// 		$rootPath='./File/'.$detail.'/'; // 设置附件上传根目录
	// 	}

	// 	$config = array(
	//         'maxSize'    =>    100000000000,
	//         'rootPath'   =>	   $rootPath,
	//         'savePath'   =>     '', // 设置附件上传（子）目录
	//         'saveName'   =>    array('uniqid',''),  //图片名称不能重复
	//         'exts'       =>    $type,
	//         'autoSub'    =>    true,//自动子目录保存文件
	//         'subName'    =>    '',//子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组

	//     );
		
	// 	// 上传文件
	// 	$upload = new  \Think\Upload($config);
	// 	$info   =   $upload->upload();
	// 	// $this->ajaxReturn($info);
	// 	if(!$info) {// 上传错误提示错误信息
	// 		$arr=array('error'=>1,"message"=>$upload->getError());
	// 	}else{// 上传成功 获取上传文件信息
	// 		if($key){
	// 			$url="/File/".$detail.'/'.$key."/".$info[1]['savepath'].$info[1]['savename'];
	// 		}else{
	// 			$url="/File/".$detail.'/'.$info[1]['savepath'].$info[1]['savename'];
	// 		}
			
	// 		$arr=array('error'=>0,"url"=>$url);
	// 	}

	// 	return $arr;
	// }
	

	public function deleteFile(){
		$file=I('post.path');
		$file=".".$file;

        if (unlink($file) ) {
            $code=1;
        }else{
            $code=0;
        }

		$this->ajaxReturn($code);
	}
}