<?php
namespace Admin\Controller\Answer;
class AnswerController extends \Admin\Controller\CommonController {
    protected $Mod='';

    public function _initialize(){
        parent::_initialize();
        $this->Mod=D('Answer');
        $this->assign('keywords','律师');
    }
    public function lists(){ 
        //律师姓名
        $lawyer=M('user')->where(array('status'=>array('neq',0)))->group('user_name')->field('user_name,id')->select();
        $this->assign('lawyer',$lawyer);

        //问答价格
        $price=zidian(1);
        $this->assign('price',$price);

        if(!empty(I('price'))){
            $where['money']=I('price');
        }
        if(!empty(I('lawyer'))){
            $where['user_id']=I('lawyer');
        }

        $where['status']=array('neq',0);
    	$data=$this->Mod->getAllList($where);
        $this->assign('search',I(''));
    	$this->assign('list',$data['list']);
        $this->assign('count',$data['count']);
        $this->assign('page',$data['page']);
        $this->show();
    }

    //律师认证状态
    public function check_status(){
        if(empty(I('post.'))){
            $id=I('id');
            $entity=$this->Mod->where(array('id'=>$id))->field('id,check_status,bo')->find();
            $this->assign('entity',$entity);
            $this->show();
        }else{
            $data=I('post.');
            $data['check_time']=time();
            $info=$this->Mod->where(array('id'=>$data['id']))->save($data);
            if($info){
                addlog($this->_manager,'审核id为 "'.$id.'" 的问答成功');
                $this->setMsgExit('问答审核成功');
            }else{
                $this->setErrorExit('问答审核失败');
            }
        }
    }

    //改变问答状态
    public function changeStatus(){
        $id=I('get.id');
        if(I('get.status')==1){
            $status=2;
        }else{
            $status=1;
        }

        $res=$this->Mod->where(array('id'=>$id))->setField('status',$status);
        if($res){
            addlog($this->_manager,'改变id为 "'.$id.'" 的问答状态成功');
            $this->setMsgExit('状态改变成功');
        }else{
            $this->setErrorExit('状态改变失败');
        }
    }

    public function voice_list(){
        $id=I('id');
        $type=I('type');
        if($type==1){
            $content=$this->Mod->where(array('id'=>$id))->getField('content');
            $this->assign('content',$content);
        }elseif($type==2){
            $list=M('voice')->where(array('type'=>2,'content_id'=>$id))->select();
            $this->assign('list',$list);
        }
          
        $this->display();
        
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
    
}
?>