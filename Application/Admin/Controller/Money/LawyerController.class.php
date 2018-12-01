<?php
namespace Admin\Controller\Money;
class LawyerController extends \Admin\Controller\CommonController {
    protected $Mod='';

    public function _initialize(){
        parent::_initialize();
        $this->Mod=D('LawyerMoney');
    }
    public function lists(){ 
        $search=I('');

        if(I('lawyer')){
            $where['user_id']=I('lawyer');
        }

        $lawyer=M('user')->where(array('identity'=>2,'status'=>2))->field('id,user_name')->select();
        $this->assign('lawyer',$lawyer);

        $data=$this->Mod->getAllList($where);
        $this->assign('search',I(''));
        $this->assign('list',$data['list']);
        $this->assign('count',$data['count']);
        $this->assign('page',$data['page']);
        $this->display('lists');
    }

    public function laywerLog(){
        $id=I('id');
        $data=$this->Mod->getLogList($id);
        $this->assign('list',$data['list']);
        $this->assign('count',$data['count']);
        $this->assign('page',$data['page']);
        $this->display('laywerLog');
    }

}
?>