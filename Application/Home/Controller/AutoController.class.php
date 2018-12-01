<?php
namespace Home\Controller;
use Think\Controller;

class AutoController extends Controller {
	
	public function index(){
		//检测冻结资金，如果到达规定时间，则解冻
		$config=$num=F('config');
		$time=$config['cashday'];
		$time=time()-$time*24*60*60;
		$where['status']=1;
		$where['create_time']=array('lt',$time);
		$cold=M('lawyer_cold')->where($where)->select();
		foreach ($cold as $k => &$v) {
			$lawyer=M('lawyer_money')->where(array('user_id'=>$v['user_id']))->field('now_money,money')->find();
			if($lawyer){
				$data['now_money']=$lawyer['now_money']+$v['money'];
				$data['edit_time']=time();
				$data['money']=$lawyer['money']+$v['money'];
				M('lawyer_money')->where(array('user_id'=>$v['user_id']))->field('now_money,money')->save($data);
			}else{
				$data['now_money']=$lawyer['now_money'];
				$data['user_id']=$v['user_id'];
				$data['edit_time']=time();
				$data['create_time']=time();
				M('lawyer_money')->add($data);
			}
			M('lawyer_cold')->where(array('id'=>$v['id']))->setField(array('status'=>0));
		}
		


		//检测律师接单是否在规定时间内解答
		$end_time=$config['end_time'];
		$time=time()-$end_time*60;
		$deal['answer_status']=0;
		$deal['create_time']=array('lt',$time);
		$deal['is_user']=1;
		$order_deal=M('order_deal')->where($deal)->field('id,user_id')->select();

		foreach ($order_deal as $k => &$v) {
			$id=$v['user_id'];
			M('order_deal')->where(array('id'=>$v['id']))->setField(array('is_user'=>0));

			M('order')->where(array('id'=>$v['order_id']))->setField(array('status'=>2));
			//发信息钩子
            $content='由于在 '.$end_time.'  分钟内未解答,您所抢的订单已失效';
            $param=array(
                'user_id'=>$id,
                'content'=>$content,
            );
            \Think\Hook::listen('AddMessage',$param);   
		}

		//系统评价
		$comment['status']=1;
		$time=time()-24*60*60;
		$comment['deal_time']=array('lt',$time);
		$res=M('order_deal')->where($comment)->select();

		if($res){
			foreach ($res as $k => &$v) {
				$save['point']=100;
				$save['comment_time']=time();
				$save['comment']='系统默认评价';
				$save['status']=2;
				M('order_deal')->where(array('id'=>$v['id']))->save($save);
			}
		}

		//分配佣金
		$order['money_status']=1;
		$order['type']=1;
		//查询订单表中未分配佣金的记录
		$money=M('order')->where($order)->select();
		$percent=F('config')['orderpercent'];
        $lawyerpercent=1-(0.01*$percent);

        $orderModel=M('order');
        $orderdealModel=M('order_deal');
        $lawyercoldModel=M('lawyer_cold');
		if($money){
			foreach ($money as $k => &$v) {
				//订单所在套餐人数
				$number=M('combo')->where(array('id'=>$v['combo_id']))->getField('number');
				//当前律师解答评价的人数
				$order_count=M('order_deal')->where(array('order_id'=>$v['id'],'status'=>2))->count();

				if($number==$order_count){//如果相等则分配佣金
					$order_res=M('order_deal')->where(array('order_id'=>$v['id'],'status'=>2))->select();
					$point=M('order_deal')->where(array('order_id'=>$v['id'],'status'=>2))->sum('point');
					foreach ($order_res as $kk => &$vv) {
						$lawyercoldModel->startTrans();
						$get_money=$lawyerpercent*$v['money']*($vv['point']/$point);
						$pei['get_money']=round($get_money,2);
		                $pei['money_status']=2;
		                $res1=$orderdealModel->where(array('id'=>$vv['id']))->field('get_money,money_status')->save($pei);

						$lawyer_cold['user_id']=$vv['user_id'];
		                $lawyer_cold['order_id']=$vv['order_id'];
		                $lawyer_cold['money']=$pei['get_money'];
		                $lawyer_cold['create_time']=time();
						$lawyer_cold['source']=1;
						$res2=$lawyercoldModel->add($lawyer_cold);
						if($res1 && $res2){
							$lawyercoldModel->commit();
						}else{
							$lawyercoldModel->rollback();
						}
					}
					$orderModel->where(array('id'=>$v['id']))->save(array('money_status'=>2));
				}

			}
		}
	}
}