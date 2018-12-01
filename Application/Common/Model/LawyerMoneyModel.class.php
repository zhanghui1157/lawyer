<?php

namespace Common\Model;
use Think\Model;

class LawyerMoneyModel extends Model {
	protected $tableName="lawyer_money";
    public function getAllList($where){
		$count=$this->where($where)->count();
		$Page   =   new \Org\Util\UIPage($count,20);
		$list = $this->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach ($list as &$v) {
			$v['user_name']=M('user')->where(array('id'=>$v['user_id']))->getField('user_name');
			$v['txt_edit_time']=date('Y-m-d H:i:s',$v['edit_time']);//创建时间
			
		}
		$page=$Page->show();
		return array('list'=>$list,'page'=>$page,'count'=>$count);
    }

    public function getLogList($id){
    	$where['user_id']=$id;
    	$count=M('lawyer_cold')->where($where)->count();
		$Page   =   new \Org\Util\UIPage($count,20);
		$list = M('lawyer_cold')->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach ($list as &$v) {
			$v['txt_create_time']=date('Y-m-d H:i:s',$v['create_time']);//创建时间
			$type=M('order')->where(array('id'=>$v['order_id']))->getField('type');
			if($type==1){
				$v['txt_type']='订单';
			}elseif($type==2){
				$v['txt_type']='复听';
			}elseif($type==3){
				$v['txt_type']='问答';
			}elseif($type==4){
				$v['txt_type']='订单咨询';
			}

			if($v['status']==1){
				$v['txt_status']='冻结';
			}else{
				$v['txt_status']='解冻';
			}
		}
		$page=$Page->show();
		return array('list'=>$list,'page'=>$page,'count'=>$count);
    	
    }

}
