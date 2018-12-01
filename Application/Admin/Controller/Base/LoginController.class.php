<?php
namespace Admin\Controller\Base;
use Think\Controller;
class LoginController extends Controller {
    public function index(){

        
        $loginsession_name = 'ADMINLOGINCHECK';
        $loginsession_time = 'ERRORTIME';
        if(IS_POST)
        {
            $data = array();
            $data['code']=0;
            $data['msg']='';            
            if($_POST){
                if(!session($loginsession_name))
                {
                    session($loginsession_name,0);//如果错误次数为空，则初始化
                }
                if(session($loginsession_name)>=C('LOGINERROR_MAX_NUMBER') &&  (session($loginsession_time)+C('LOGINERROR_TIME_LATER'))>time())
                {
                    $data['code']=0;
                    $data['msg']='密码已输入错误'.C('LOGINERROR_MAX_NUMBER').'次,请'.ceil((session($loginsession_time)+C('LOGINERROR_TIME_LATER')-time())/60/1000).'分钟后再试!';
                    $this->ajaxReturn($data,'JSON');
                    exit;
                }
                if($this->check_verify(I('post.checkCode')))
                {
                    $Condition = array('adminname'=>I('post.adminname'));
                    $entity = M('manager')->where($Condition)->find();
                    if($entity)
                    {
                        if($entity['password']==md5(I('post.password')))
                        {
                            //管理员权限列表
                            //$resourcelist = M()->table(C('DB_PREFIX').'manager manager,'.C('DB_PREFIX').'manager_role mrole, '.C('DB_PREFIX').'role_resource r_resource, '.C('DB_PREFIX').'resource resource')->where('manager.id='.$entity['id'].' and manager.id = mrole.manager_id and mrole.role_id = r_resource.role_id and resource.id = r_resource.resource_id')->field('resource.id as id,resource.m as m,resource.c as action,resource.a as method')->order('resource.id desc' )->select();
                            $rids = M('managerRole')->where(array('manager_id'=>$entity['id']))->getField('role_id',true);
                            $cond = array();
                            $cond['id'] = array('in',$rids);
                            $rolelist = M('role')->where($cond)->select();
                            $rolename = '';
                            foreach ($rolelist as $r)
                            {
                                $rolename = $rolename.$r['rolename'].',';
                            }
                            $entity['rolename'] = $rolename;
                            //$entity['authcode'] = $resourcelist;
                            $data['code']=1;
                            $data['msg']=messge('login_success');
                            addlog($entity,'登录成功');
                            session(C('MANAGERSESSION_ID'),$entity);
                        }else{
                            $data['msg']=messge('login_fail_passworderror');
                            session($loginsession_name,session($loginsession_name)+1);//错误次数加1
                            session($loginsession_time,time());//记录错误发生时间
                            addlog($entity,'登录密码输入错误');
                        }
                    }else{
                        $data['msg']=messge('login_fail_usernamenotexits');
                    }                
                }else{
                    $data['msg']=messge('login_fail_checkcodeerror');
                }
            }        
            $this->ajaxReturn($data,'JSON');
        }else{
            //查询系统配置信息
            $config = M('Config')->where(array('id'=>'default'))->find();
            if($config)
            {
                
            }else{
                $this->assign('configexist', 0);
            }
            $this->show();
        }
    }
    function check_verify($code, $id = 'admin'){
        $verify = new \Think\Verify();
        return $verify->check($code,$id);
    }
    public function checkcode()
    {
        ob_clean();
        $config = C('CHECKCODE');
        $Verify = new \Think\Verify($config);
        $Verify->entry('admin');
    }
}