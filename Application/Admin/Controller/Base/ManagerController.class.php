<?php
namespace Admin\Controller\Base;
class ManagerController extends \Admin\Controller\CommonController {
    public function lists()
    {
        $Condition = array();
        $search = I('get.');
        $Condition['adminname']=array('like','%'.$search['adminname'].'%');
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
        $this->RecCount = M('Manager')->where($Condition)->count();
        $Page   =   new \Org\Util\UIPage($this->RecCount,10);
        $list = M('Manager')->where($Condition)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
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
                $manager = array();
                $manager['adminname'] = I('post.adminname');
                $manager['password'] = md5(I('post.password'));
                $manager['addtime'] = time();
                $manager['headpic'] = '/Public/manager/new/img/profile_small.jpg';
                $manager['isdeleted'] = 0;
                if(M('manager')->where(array('adminname'=>$manager['adminname']))->find())
                {
                    $this->setErrorExit("该管理员名称已存在");
                }
                $id = M('manager')->add($manager);
                $roles = I('post.role');
                if($roles)
                {
                    $del = M('ManagerRole')->where(array('manager_id='.$id.' and role_id not in('.implode(',',$roles).')'))->delete();
                }
                foreach($roles as $r)
                {
                    if(!M('ManagerRole')->where(array('manager_id'=>$id,'role_id'=>$r))->find())
                    {
                        M('ManagerRole')->add(array('manager_id'=>$id,'role_id'=>$r,'addtime'=>time()));
                    }
                }
                addlog($this->_manager,'添加管理员['.$manager['adminname'].']成功');
                $this->setMsgExit('添加管理员成功');
            }
            else
            {
                $_manager = M('manager')->where(array('id'=>$id))->find();
                $manager = array();
                $manager['adminname'] = I('post.adminname');
                if(I('post.password')!='')
                {
                    $manager['password'] = md5(I('post.password'));
                }
                $rows = M('manager')->where(array('id'=>$_manager['id']))->save($manager);
                $roles = I('post.role');
               
                foreach($roles as $r)
                {
					$condition['role_id']=$r;
                    if(M('ManagerRole')->where(array('manager_id'=>$id))->find())
                    {
                        M('ManagerRole')->where(array('manager_id'=>$id))->save($condition);
                    }
                }
                addlog($this->_manager,'编辑管理员['.$manager['adminname'].']成功');
                $this->setMsgExit('编辑管理员成功');
            }
        }else{  
            $id = I('get.id');
            if($id==0)
            {
                $this->assign('myrolelist',array());
                $this->assign('rolelist',M('Role')->field(true)->select());
                $this->assign('entity',array('id'=>0));
            }
            else
            {
                //获取目前角色
                $my_r_list = M('ManagerRole')->field('role_id')->where(array('manager_id'=>$id))->select();
                $myrolelist = array();
                foreach($my_r_list as $r)
                {
                    array_push($myrolelist,$r['role_id']);
                }
                $this->assign('myrolelist',$myrolelist);
                $this->assign('rolelist',M('Role')->field(true)->select());
                $this->assign('entity',M('Manager')->field(true)->where(array('id'=>$id))->find());
            }
            $this->show();
        }
    }
    public function del()
    {
        if(I('get.id'))
        {
            $id = I('get.id');
            M('manager')->where(array('id'=>array('eq',$id)))->delete();
            addlog($this->_manager,'删除单个管理员['.$id.']成功');
            $this->setMsgExit('已删除一条记录');
        }else
        {
            $this->setErrorExit('请至少指定要删除的对象');
        }
    }
    /*
     * 修改登录管理员的管理密码
     */
    public function modifypwd()
    {
        if(IS_POST)
        {
            $id = $this->_manager['id'];
            $_manager = M('manager')->where(array('id'=>$id))->find();
            $manager = array();
            if(md5(I('post.oldpwd'))!=$_manager['password'])
            {
                $this->setErrorExit('原始密码不正确');                
            }
            else
            {
                $manager['password'] = md5(I('post.newpwd'));
                M('manager')->where(array('id'=>$_manager['id']))->save($manager);
                addlog($this->_manager,'管理员['.$_manager['adminname'].']修改密码成功');
                session(C('MANAGERSESSION_ID'), null);
                $this->setMsgExit('密码修改成功');
            }
        }else{  
            $id = $this->_manager['id'];
            //获取目前角色
            $my_r_list = M('ManagerRole')->field('role_id')->where(array('manager_id'=>$id))->select();
            $myrolelist = array();
            foreach($my_r_list as $r)
            {
                array_push($myrolelist,$r['role_id']);
            }
            $this->assign('myrolelist',$myrolelist);
            $this->assign('rolelist',M('Role')->field(true)->select());
            $this->assign('entity',M('Manager')->field(true)->where(array('id'=>$id))->find());
            $this->show();
        }
    }
    public function modifyother()
    {
        if(IS_POST)
        {
            $id = $this->_manager['id'];
            $_manager = M('manager')->where(array('id'=>$id))->find();
            $headpic = I('post.headpic');
            if($headpic)
            {
                M('manager')->where(array('id'=>$_manager['id']))->save(array('headpic'=>$headpic));
                addlog($this->_manager,'管理员['.$_manager['adminname'].']修改头像成功');
                $this->reloadmanagerinfo();
                $this->setMsgExit('头像修改成功');
            }
            else
            {
                $this->setErrorExit('请指定头像文件');
            }
        }
        else
        {
            $id = $this->_manager['id'];
            $_manager = M('manager')->where(array('id'=>$id))->find();
            $this->assign('entity', $_manager);
            $this->show();
        }
    }
}
?>