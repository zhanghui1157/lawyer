<?php
namespace Admin\Controller\Lawyer;
class CheckController extends \Admin\Controller\CommonController {
    protected $Mod='';

    public function _initialize(){
        parent::_initialize();
        $this->Mod=D('User');
        $this->assign('keywords','律师');
    }
    public function lists(){ 

        $where['type']=2;
        $where['status']=array('neq',0);

        if(empty(I('status'))){
            $where['status']=array('neq',0);
        }else{
            $where['status']=I('status');
            $this->assign('status',$where['status']);
        }

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
			$data['identity']=2;
			$data['status']=2;
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

    public function edit(){
        $id=I('id');
        $info=M('user_info')->where(array('user_id'=>$id))->find();

        $this->assign('entity',$info);
        $this->show();
    }
    
}
?>