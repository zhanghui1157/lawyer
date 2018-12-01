<?php
namespace Admin\Controller\Base;
class RoleController extends \Admin\Controller\CommonController {
    public function lists()
    {
        $Condition = array();
        $search = I('get.');
        $Condition['rolename']=array('like','%'.$search['name'].'%');
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
        $this->RecCount = M('Role')->where($Condition)->count();
        $Page   =   new \Org\Util\UIPage($this->RecCount,10);
        $list = M('Role')->where($Condition)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->list=$list;
        $this->assign('page',$Page->show());
        $this->assign('search',$search);         
        $this->show();
    }
    public function edit()
    {
        if(IS_POST)
        {
            $id = I('post.id');
            if($id==0)
            {
                $role = array();
                $role['rolename'] = I('post.rolename');

				$role['bpoint'] =I('post.bpoint');
			
                
				$role['company'] = I('post.company');
                $role['addtime'] = time(); 
                $newrole = M('Role')->add($role);
                addlog($this->_manager,'添加角色['. $role['rolename'].']成功');
                $this->setMsgExit('添加角色成功');
            }
            else
            {
                $_role = M('Role')->where(array('id'=>$id))->find();
                $role = array();
                $role['rolename'] = I('post.rolename');  
                $role['bpoint'] =I('post.bpoint');
				$role['company'] = I('post.company');
                $newrole = M('Role')->where(array('id'=>$_role['id']))->save($role);

                M('Role')->startTrans();
                $_role = M('Role')->where(array('id'=>$id))->save($role);   
                $resources = I('post.resource');
                $del = M('RoleResource')->where(array('role_id='.$id.' and resource_id not in('.implode(',',$resources).')'))->delete();
                foreach($resources as $r)
                {
                    if(!M('RoleResource')->where(array('role_id'=>$id,'resource_id'=>$r))->find())
                    {
                        M('RoleResource')->add(array('role_id'=>$id,'resource_id'=>$r));
                    }
                }
                M('Role')->commit();
                addlog($this->_manager,'编辑角色['.$role['rolename'].']成功');
                $this->setMsgExit('编辑角色成功');
            }
        }else{  
            $id = I('get.id');   
            if($id==0)
            {
                $this->assign('resourcelist',M('Resource')->field(true)->select());
                $this->assign('entity',array('id'=>0));
            }
            else
            {
                $this->assign('resourcelist',M('Resource')->field(true)->select());
                //获取目前权限
                $my_r_list = M('RoleResource')->field('resource_id')->where(array('role_id'=>$id))->select();
                $myresourcelist = array();
                foreach($my_r_list as $r)
                {
                    array_push($myresourcelist,$r['resource_id']);
                }
                $this->assign('myresourcelist',$myresourcelist);
                $this->assign('entity',M('Role')->field(true)->where(array('id'=>$id))->find());
            }

            $this->show();
        }
    }
    public function del()
    {
        if(I('get.id'))
        {
            $id = I('get.id');
            M('Role')->where(array('id'=>array('eq',$id)))->delete();
            addlog($this->_manager,'删除单条角色['.$id.']成功');
            $this->setMsgExit('已删除一条记录');
        }else
        {
            $this->setErrorExit('请至少指定要删除的对象');
        }
    }
}
?>