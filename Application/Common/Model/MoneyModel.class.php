<?php

namespace Common\Model;
use Think\Model;

class MoneyModel extends Model {
	protected $tableName="plat_money";
    public function getAllList($where){
		$count=$this->where($where)->count();
		$Page   =   new \Org\Util\UIPage($count,20);
		$list = $this->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach ($list as &$v) {
			$v['txt_create_time']=date('Y-m-d H:i:s',$v['create_time']);//创建时间
		}
		$page=$Page->show();
		return array('list'=>$list,'page'=>$page,'count'=>$count);
    }
	
	public function getOrderInfo(){
		$id=I('id');
		$data=D('Order')->getOrderInfo($id);
		$this->assign('data',$data);
		$this->display('getOrderInfo');
	}

	public function getReorderInfo(){

	}

	public function getAnwserInfo(){

	}

}
