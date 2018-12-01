<?php

namespace Common\Model;
use Think\Model;

class InfoModel extends Model {
	protected $tableName="info";

    public function getAllList($where){
		$count=$this->where($where)->count();
		$Page   =   new \Org\Util\UIPage($count,20);
		$list = $this->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach ($list as &$v) {
			$v['user_name']=M('user')->where(array('id'=>$v['user_id']))->getField('user_name');
			if($v['status']==1){
				$v['txt_status']='未读';
			}elseif($v['status']==2){
				$v['txt_status']='已读';
			}

			$v['txt_create_time']=date('Y-m-d H:i:s',$v['create_time']);//创建时间
			$v['txt_update_time']=date('Y-m-d H:i:s',$v['update_time']);//律师认证时间
		}
		
		$page=$Page->show();
		return array('list'=>$list,'page'=>$page,'count'=>$count);
    }
	
	

}
