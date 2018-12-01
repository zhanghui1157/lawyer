<?php

namespace Common\Model;
use Think\Model;

class MoneyLogModel extends Model {
	protected $tableName="money_log";

    public function getAllList($where){
		$count=$this->where($where)->count();
		$Page   =   new \Org\Util\UIPage($count,20);
		$list = $this->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach ($list as &$v) {
			$user=M('user')->where(array('id'=>$v['user_id']))->field('user_name,bank_num,bank_card,bank_name')->find();
			$v['bank_name']=$user['bank_name'];
			$v['bank_card']=$user['bank_card'];
			$v['bank_num']=$user['bank_num'];
			$v['user_name']=$user['user_name'];
			$v['txt_create_time']=date('Y-m-d H:i:s',$v['create_time']);//创建时间
			$v['txt_check_time']=date('Y-m-d H:i:s',$v['check_time']);//律师认证时间

			if($v['status']==1){
				$v['txt_status']='待审核';
			}else if($v['status']==2){
				$v['txt_status']='通过';
			}else{
				$v['txt_status']='驳回';
			}
			
		}
		$page=$Page->show();
		return array('list'=>$list,'page'=>$page,'count'=>$count);
    }
	
	

}
