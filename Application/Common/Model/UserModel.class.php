<?php

namespace Common\Model;
use Think\Model;

class UserModel extends Model {
	protected $tableName="user";
	protected $pk='id';
    protected $_validate = array(
        array('mobile', 'require', '手机号码', self::MUST_VALIDATE),
    );

    protected $_auto = array(
		array('create_time','time', self::MODEL_INSERT,'function'),		
    );

    public function getAllList($where,$field){
    	if(empty($field)){
    		$field=true;
    	}
		$count=$this->where($where)->count();
		$Page   =   new \Org\Util\UIPage($count,20);
		$list = $this->where($where)->order('id desc')->field($field)->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach ($list as &$v) {

			if($v['identity']==1){
				$v['txt_identity']='普通会员';
			}elseif($v['identity']==2){
				$v['txt_identity']='认证律师';
			}

			if($v['status']==1){
				$v['txt_status']='待审核';
			}elseif($v['status']==2){
				$v['txt_status']='通过';
			}elseif($v['status']==3){
				$v['txt_status']='驳回';
			}

			$v['txt_create_time']=date('Y-m-d H:i:s',$v['create_time']);//创建时间
			$v['txt_check_time']=date('Y-m-d H:i:s',$v['check_time']);//律师认证时间
			$v['txt_apply_time']=date('Y-m-d H:i:s',$v['apply_time']);//律师认证申请时间
			
		}
		$page=$Page->show();
		return array('list'=>$list,'page'=>$page,'count'=>$count);
    }
	
	

}
