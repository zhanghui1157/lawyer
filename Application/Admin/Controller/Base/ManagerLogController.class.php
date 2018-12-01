<?php
namespace Admin\Controller\Base;
class ManagerLogController extends \Admin\Controller\CommonController {
    public function lists()
    {
        $Condition = array();
        $search = I('get.');
        $Condition['managername']=array('like','%'.$search['adminname'].'%');
        if($search['starttime']!=''&&$search['endtime']!='')
        {
            $Condition['addtime'] = array(array('egt',  strtotime($search['starttime'])),array('elt',  strtotime($search['endtime'])+24*60*60),'and');
        }
        elseif($search['starttime']!='')
        {
            $Condition['addtime'] = array('egt',  strtotime($search['starttime']));
        }
        elseif($search['endtime']!='')
        {
            $Condition['addtime'] = array('elt',  strtotime($search['endtime'])+24*60*60);
        }
        if($search['get.ip']!='')
        {
            $Condition['ip']=array('like',  '%'.$search['get.ip'].'%');
        }
        $this->RecCount = M('ManagerLog')->where($Condition)->count();
        $Page   =   new \Org\Util\UIPage($this->RecCount,10);
        $list = M('ManagerLog')->where($Condition)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->list=$list;
        $this->assign('search',$search);
        $this->assign('page',$Page->show());
        $this->show();
    }
    public function del()
    {
        $this->setErrorExit('日志不允许删除');
        exit;
        if(I('post.id'))
        {
            $id = I('post.id');
            M('ManagerLog')->where(array('id'=>array('eq',$id)))->delete();
            addlog($this->_manager,'删除日志['.$id.']成功');
            $this->setMsgExit('已删除一条记录');
        }else
        {
            $this->setErrorExit('请至少指定要删除的对象');
        }
    }
}
?>