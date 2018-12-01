<?php
namespace Admin\Controller\Lawyer;
class LawyerController extends \Admin\Controller\CommonController {
    protected $Mod='';

    public function _initialize(){
        parent::_initialize();
        $this->Mod=D('User');
        $this->assign('keywords','律师');
    }
    public function lists(){ 
        $where['identity']=2;
        $where['status']=2;

        if(!empty(I('now_pro')) && !empty(I('now_city'))){
            $user['now_pro']=I('now_pro');
            $user['now_city']=I('now_city');
            $info=M('user_info')->where($user)->field('user_id')->select();
            foreach ($info as $k => &$v) {
                $arr[$k]=$v['user_id'];
            }
            if(!empty($arr)){
                $where['id']=array('in',$arr);
            }else{
                $where['id']=0;
            }
            $this->assign('user',$user);
        }
    	$data=$this->Mod->getAllList($where);
        foreach ($data['list'] as  &$v) {
            $v['headimg']=M('check_info')->where(array('user_id'=>$v['id']))->getField('headimg');
        }
        $this->assign('count',$data['count']);
    	$this->assign('list',$data['list']);
        $this->assign('page',$data['page']);

        $this->display('lists');
        
        
    }

    //律师认证状态
    public function check_status(){
        if(empty(I('post.'))){
            $id=I('id');
            $entity=$this->Mod->where(array('id'=>$id))->field('id,status,bo')->find();
            $this->assign('entity',$entity);
            $this->show();
        }else{
            $data=I('post.');
            $data['check_time']=time();
            $info=$this->Mod->where(array('id'=>$data['id']))->save($data);
            if($info){
                $this->setMsgExit('认证成功');
            }else{
                $this->setErrorExit('认证失败');
            }
        }
    }

    //律师认证详细信息
    public function check_info(){
        $id=I('id');
        $info=M('check_info')->where(array('user_id'=>$id))->find();
        $info['keywords']=explode(',', $info['keywords']);
        foreach ($info['keywords'] as $key => &$value) {
            $info['keywords'][$key]=M('dictionary')->where(array('id'=>$value))->getField('name');
        }
        $user=M('user')->alias('u')
            ->join('db_user_info i on i.user_id=u.id' )
            ->where(array('u.id'=>$id))
            ->find();
        $this->assign('user',$user);
        $this->assign('entity',$info);
        $this->show();
    }

    //律师信息
    public function edit(){
        $id=I('id');
        $info=M('lawyer_info')->where(array('user_id'=>$id))->find();
        $info['headimg']=M('user')->where(array('id'=>$id))->getField('headimg');
        $this->assign('entity',$info);
        $this->show();
    }

    public function del(){
        if(I('get.id')){
            $id = I('get.id');
            M('check_info')->where(array('user_id'=>$id))->delete();
            M('user')->where(array('id'=>$id))->setField(array('identity'=>1,'status'=>3));
            $this->setMsgExit('已删除一条记录');
        }else{
            $this->setErrorExit('请至少指定要删除的对象');
        }
    }

    public function showLaywer(){
        if(I('get.id')){
            $id=I('get.id');
            $show=M('user')->where(array('id'=>$id))->field('is_show')->find();

            if($show['is_show']==1){
                $data['is_show']=2;
            }elseif($show['is_show']==2){
                $data['is_show']=1;
            }

            $res=M('user')->where(array('id'=>$id))->save($data);
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