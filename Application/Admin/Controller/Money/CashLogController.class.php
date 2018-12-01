<?php
namespace Admin\Controller\Money;
class CashLogController extends \Admin\Controller\CommonController {
    protected $Mod='';

    public function _initialize(){
        parent::_initialize();
        $this->Mod=D('MoneyLog');
    }
    public function lists(){ 
        $where['status']=array('like',array(2,3));
        $data=$this->Mod->getAllList($where);

        $this->assign('list',$data['list']);
        $this->assign('count',$data['count']);
        $this->assign('page',$data['page']);
        $this->display('lists');
    }
}
?>