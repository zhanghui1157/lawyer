<?php
namespace Admin\Controller\Order;
class OrderController extends \Admin\Controller\CommonController {
    protected $Mod='';

    public function _initialize(){
        parent::_initialize();
        $this->Mod=D('Order');
        $this->assign('keywords','订单');
    }

    public function lists(){
		$search=I('');
        $type=I('type');
        //$where['transaction_id']=array('neq','');
        if($type==1){
            $where['transaction_id']=array('neq','');
            $where['status']=1;
        }elseif($type==2){
            $where['status']=2;
        }elseif($type==3){
            $where['status']=3;
        }else{
            $where['transaction_id']=array('exp','is null');
        }
        $where['type']=1;
		if(!empty(I('trade_no'))){
            $where['trade_no']=I('trade_no');
        }

        $data=$this->Mod->getAllList($where);
        foreach ($data['list'] as $k => &$v) {
            $v['phone']=M('user')->where(array('id'=>$v['user_id']))->getField('mobile');
        }
        $this->assign('count',$data['count']);
        $this->assign('list',$data['list']);
        $this->assign('page',$data['page']);
		$this->assign('search',$search);
        $this->assign('type',$type);
        if($type==1){
            $this->display('lists1');
        }elseif($type==2){
            $this->display('lists2');
        }elseif($type==3){
            $this->display('lists3');
        }else{
            $this->display('lists4');
        }
    }

    public function edit(){
        if(empty(I('post.'))){
            $id=I('id');
            $entity=$this->Mod->where(array('id'=>$id))->find();
            $entity['cate_name']=M('dictionary')->where(array('id'=>$entity['cate_er']))->getField('name');

            $user=M('user')->where(array('id'=>$entity['user_id']))->find();

            $combo=M('combo')->where(array('id'=>$entity['combo_id']))->find();

            $cate=M('dictionary')->where(array('type'=>2,'pid'=>0))->field('name,id')->select();
            $this->assign('cate',$cate);
            $this->assign('entity',$entity);
            $this->assign('user',$user);
            $this->assign('combo',$combo);
            $this->show();
        }else{
            $id=I('u_id');
            $data=I('');
            $data['deal_time']=time();
            $res=$this->Mod->where(array('id'=>$id))->save($data);

            //给相同地的律师发消息通知有新的订单待接
            $city=$this->Mod->where(array('id'=>$id))->getField('city');
            $lawyer=M('user_info')->where(array('now_city'=>$city))->field('user_id')->select();
            $content='您所在的地区有新的订单，名额有限，请赶快去抢单！';
            foreach ($lawyer as $k => &$v) {
                $param=array(
                    'user_id'=>$v['user_id'],
                    'content'=>$content ,
                );
                \Think\Hook::listen('AddMessage',$param); 
            }

            if($res){
                addlog($this->_manager,'处理订单id为 "'.$id.'" 成功');
                $this->setMsgExit($this->keywords.'处理成功');
            }else{
                $this->setErrorExit($this->keywords.'处理失败');
            }
        }
    }


    //完成订单的处理情况
    public function deal(){

        $id=I('id');
        $data=$this->Mod->getOrderInfo($id);
        foreach ($data as $k=> &$v) {
            if($v['deal_type']==2){
                $v['path']=M('voice')->where(array('user_id'=>$v['user_id'],'type'=>1,'content_id'=>$v['order_id']))->select();
            }

        }
        $this->assign('data',$data);
        $this->display();
    }

    /**
     * 分配拥金，
     * 1、给账户进行相关的积分操作
     * 2、记录操作积分变动日志
     * 3、处理订单表中的佣金分配状态
     * @return [type] [description]
     */
    public function giveMoney(){
        $data=I('get.');
        $money=M('money');
        $money_log=M('money_log');
        $order=M('order');
        $money->startTrans();

        $data['create_time']=time();

        $info=$money->where(array('user_id'=>$data['user_id']))->find();
        if($info){
            $res1=M('money')->where(array('user_id'=>$data['user_id']))->setInc('number',$data['number']);
        }else{
            $res1=M('money')->add($data);
        }
        $data['type']=1;
        $res2=M('money_log')->add($data);
        $res3=M('order_deal')->where(array('id'=>$data['deal_id']))->setField('money_status',2);

        $order=M('order_deal')->where(array('order_id'=>$data['order_id']))->field('money_status')->select();
        $money_status=1;
        foreach ($order as $key => &$value) {
            if($value['money_status']==1){
                $money_status=2;
                break;
            }
        }
        if($money_status==1){
            M('order')->where(array('id'=>$data['order_id']))->setField('money_status',2);
        }
        if($res1 && $res2 && $res3){
            $money->commit();
            addlog($this->_manager,'给id为 "'.$data['user_id'].'" 的律师分配佣金 '.$data['number'].' 成功');
            $this->setMsgExit('处理成功');
        }else{
            $money->rollback();
            $this->setErrorExit('处理失败');
        }
    }

    public function getErCate(){
        $id=I('id');
        $data=M('dictionary')->where(array('pid'=>$id))->field('name,id')->select();
        $this->ajaxReturn($data);
    }

    public function del(){
        if(I('get.id')){
            $id = I('get.id');
            $this->Mod->where(array('id'=>array('eq',$id)))->delete();
            addlog($this->_manager,'删除套餐id为 "'.$id.'" 成功');
            $this->setMsgExit('已删除一条记录');
        }else{
            $this->setErrorExit('请至少指定要删除的对象');
        }
    }

    public function showOrder(){
        if(I('get.id')){
            $id=I('get.id');
            $show=M('order')->where(array('id'=>$id))->field('is_show')->find();

            if($show['is_show']==1){
                $data['is_show']=2;
            }elseif($show['is_show']==2){
                $data['is_show']=1;
            }

            $res=M('order')->where(array('id'=>$id))->save($data);
            if($res){
                $this->setMsgExit('操作成功！');
            }else{
                $this->setErrorExit('操作失败！');
            }
        }else{
            $this->setErrorExit('请选择对象');
        }
            
    }
    
}
?>