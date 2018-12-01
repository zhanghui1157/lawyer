<?php
namespace Admin\Controller\Base;
class ResourceController extends \Admin\Controller\CommonController {
    public function lists()
    {
        $Condition = array();
        $search = I('get.');
        $Condition['resourcename']=array('like','%'.$search['name'].'%');
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
        $this->RecCount = M('Resource')->where($Condition)->count();
        $Page   =   new \Org\Util\UIPage($this->RecCount,10);
        $list = M('Resource')->where($Condition)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->list=$list;
        $this->assign('page',$Page->show());
        $this->assign('search',$search);       
        $this->show();
    }
    /*
     * 编辑
     */
    public function edit()
    {
        if(IS_POST)
        {
            $id = I('post.id');
            $resource = array();            
            $resource['ico'] = I('post.ico');
            if($id==0)
            {
                $resource['resourcename'] = I('post.resourcename');
                $resource['moudle'] = I('post.moudle');
                $resource['action'] = I('post.action');
                $resource['method'] = I('post.method');
                $resource['pid'] = I('post.pid');
                $resource['display'] = I('post.display');
                $resource['type'] = I('post.type');
                $resource['readonly'] = 0;
                $resource['orderid'] =  I('post.orderid');
                $resource['addtime'] = time();
                $newid = M('Resource')->add($resource);
                addlog($this->_manager,'添加资源['.$resource['resourcename'].']成功');
                $this->setMsgExit('添加资源成功');                
            }
            else
            {
                $entity = M('Resource')->where(array('id'=>$id))->find();
                    $resource['resourcename'] = I('post.resourcename');
                    $resource['moudle'] = I('post.moudle');
                    $resource['action'] = I('post.action');
                    $resource['method'] = I('post.method');
                    $resource['type'] = I('post.type');
                    $resource['pid'] = I('post.pid');
                    $resource['orderid'] =  I('post.orderid');
                    $resource['display'] = I('post.display');
                $rows = M('Resource')->where(array('id'=>$id))->save($resource);
                addlog($this->_manager,'编辑资源['.$entity['resourcename'].']成功');
                $this->setMsgExit('编辑资源成功');
            }
        }
        else
        {  
            $id = I('get.id');
            if($id==0)
            {
                $this->assign('entity',array('id'=>0));
            }
            else
            {
                $this->assign('entity',M('Resource')->field(true)->where(array('id'=>$id))->find());
            }
            $moudlelist = C('MOUDLELIST');
            $this->assign('mlist', $moudlelist);
            $plist = M('resource')->where(array('display'=>1))->order('moudle asc')->select();
            $this->assign('plist', $plist);
            $this->show();
        }
    }
    public function del()
    {
        if(I('get.id'))
        {
            $id = I('get.id');
            M('Resource')->where(array('id'=>array('eq',$id)))->delete();
            addlog($this->_manager,'删除单条资源['.$id.']成功');
            $this->setMsgExit('已删除一条记录');
        }else
        {
            $this->setErrorExit('请至少指定要删除的对象');
        }
    }
}
?>