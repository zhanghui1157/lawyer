<?php

namespace Common\Model;
use Think\Model;

class AnswerModel extends Model {
	protected $tableName="answer";
    public function getAllList($where){
		$count=$this->where($where)->count();
		$Page   =   new \Org\Util\UIPage($count,20);
		$list = $this->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach ($list as &$v) {
			$v['money']=M('dictionary')->where(array('id'=>$v['money']))->getField('name');
			$v['user_name']=M('user')->where(array('id'=>$v['user_id']))->getField('user_name');
			if($v['status']==1){
				$v['txt_status']='有效';
			}elseif($v['status']==2){
				$v['txt_status']='无效';
			}

			if($v['check_status']==1){
				$v['txt_check_status']='待审核';
			}elseif($v['check_status']==2){
				$v['txt_check_status']='通过';
			}elseif($v['check_status']==3){
				$v['txt_check_status']='驳回';
			}

			$v['txt_create_time']=date('Y-m-d H:i:s',$v['create_time']);//创建时间
			$v['txt_check_time']=date('Y-m-d H:i:s',$v['check_time']);//律师认证时间
			
		}
		$page=$Page->show();
		return array('list'=>$list,'page'=>$page,'count'=>$count);
    }

    public function getAnswerInfo($id){
    	$info=$this->where(array('id'=>$id))->find();
    	$info['user_name']=M('user')->where(array('id'=>$info['user_id']))->getField('user_name');
    	$info['txt_create_time']=date('Y-m-d H:i:s',$info['create_time']);//创建时间
    	return $info;
    }
	
	

}
