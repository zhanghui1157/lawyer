<?php
namespace Admin\Controller\Message;
class MessageController extends \Admin\Controller\CommonController {
    protected $Mod='';

    public function _initialize(){
        parent::_initialize();
        $this->Mod=D('Message');
    }
    public function lists(){
        $data=$this->Mod->getAllList($where);

        $this->assign('list',$data['list']);
        $this->assign('count',$data['count']);
        $this->assign('page',$data['page']);
        $this->display('lists');
    }

    
}
?>