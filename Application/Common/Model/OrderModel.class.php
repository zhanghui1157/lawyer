<?php

namespace Common\Model;
use Think\Model;

class OrderModel extends Model {
	protected $tableName="order";

    public function getAllList($where){
		$count=$this->where($where)->count();
		$Page   =   new \Org\Util\UIPage($count,20);
		$list = $this->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach ($list as &$v) {
			$v['user_name']=M('user')->where(array('id'=>$v['user_id']))->getField('user_name');
			$v['combo_price']=M('combo')->where(array('id'=>$v['combo_id']))->getField('price');
			$v['combo_number']=M('combo')->where(array('id'=>$v['combo_id']))->getField('number');
			$number=M('order_deal')->where(array('order_id'=>$v['id'],'is_use'=>1))->count();
			$v['wait_number']=$v['combo_number']-$number;
			if($v['status']==1){
				$v['txt_status']='待处理';
			}elseif($v['status']==2){
				$v['txt_status']='待解决';
			}elseif($v['status']==3){
				$v['txt_status']='已处理';
			}

			if($v['is_top']==0){
				$v['txt_top']='待接单';
			}elseif($v['is_top']==1){
				$v['txt_top']='待接单人数 '.$number;
			}elseif($v['is_top']==2){
				$v['txt_top']='接单完毕';
			}

			if($v['money_status']==1){
				$v['txt_money_status']='未分配';
			}elseif($v['money_status']==2){
				$v['txt_money_status']='已分配';
			}

			if(!empty($v['province'])){
				$v['address']=$v['province'];
			}

			if(!empty($v['province']) && !empty($v['city'])){
				$v['address']=$v['province'].'/'.$v['city'];
			}
				
			if(!empty($v['province']) && !empty($v['city']) && $v['area']){
				$v['address']=$v['province'].'/'.$v['city'].'/'.$v['area'];
			}

			$v['txt_create_time']=date('Y-m-d H:i:s',$v['create_time']);//创建时间
			$v['txt_deal_time']=date('Y-m-d H:i:s',$v['deal_time']);//律师认证申请时间
		}
		$page=$Page->show();
		return array('list'=>$list,'page'=>$page,'count'=>$count);
    }
	
	//订单所对应的处理信息
	public function getOrderInfo($id){

		$point=M('order_deal')->where(array('order_id'=>$id))->sum('point');
		$data=M('order_deal')
			->alias('d')
            ->join('db_user u on d.user_id=u.id')
            ->join('db_order o on o.id='.$id)
            ->join('db_combo c on c.id=o.combo_id')
            ->where(array('d.order_id'=>$id,'o.combo_id'))
            ->field('d.comment_time,d.comment,d.point,d.create_time,u.id as user_id,u.user_name,c.price,c.number,d.money_status,d.id,d.get_money,o.id as order_id,d.deal_type')
            ->select();
        foreach ($data as $k => &$v) {
        	$v['txt_comment_time']=date('Y-m-d H:i',$v['comment_time']);
        	if($v['money_status']==1){
        		$v['txt_money_status']='未分配';
        	}else{
        		$v['txt_money_status']='已分配';
        	}
        }
        return $data;
	}

}
