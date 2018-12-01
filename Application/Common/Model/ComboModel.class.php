<?php

namespace Common\Model;
use Think\Model;

class ComboModel extends Model {
	protected $tableName="combo";
	protected $pk='id';

    public function getAllList($where){
		$count=$this->where($where)->count();
		$Page   =   new \Org\Util\UIPage($count,20);
		$list = $this->where($where)->order($this->pk.' desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach ($list as &$v) {
			if($v['status']==1){
				$v['txt_status']='有效';
			}elseif($v['status']==2){
				$v['txt_status']='无效';
			}

			$v['txt_create_time']=date('Y-m-d H:i:s',$v['create_time']);//创建时间
			$v['txt_update_time']=date('Y-m-d H:i:s',$v['update_time']);//律师认证时间
			
		}
		$page=$Page->show();
		return array('list'=>$list,'page'=>$page,'count'=>$count);
    }
	
	

}
