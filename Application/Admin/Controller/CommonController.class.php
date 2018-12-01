<?php
namespace Admin\Controller;
use Think\Controller;
class CommonController extends Controller {
    protected $CONFIG;
    protected $_manager;
    protected $_userAuth;
    //分页相关
    protected $PageSize=10;
    protected $RecCount=0;
    protected $PageIndex=1;
    //ajax相关
    protected $_ajaxmessage=array('code'=>0,'msg'=>'');
    function _initialize(){
        $this->_manager=session(C('MANAGERSESSION_ID'));
        //登录身份验证,权限验证
        if(strtolower(CONTROLLER_NAME)!="base/upload")
        {
            $this->checkUser();    
        }
                 
        $this->assign('_currentUser',$this->_manager);  
        //初始化分页参数
        if(I('get.pagesize'))
        {
            $this->PageSize = intval(I('get.pagesize'));
        }
        if(I('get.pageindex'))
        {
            $this->PageIndex = intval(I('get.pageindex'));
        }
        $this->CONFIG = F('site/config');
        $this->assign('CONFIG',$this->CONFIG);
    }
    function checkUser()
    {
        if(!$this->_manager){
            $this->error('请登录后再进行操作','/Admin/Base/Login',1);
        }else{
            $resourcelist = M()->table(C('DB_PREFIX').'manager manager,'.C('DB_PREFIX').'manager_role mrole, '.C('DB_PREFIX').'role_resource r_resource, '.C('DB_PREFIX').'resource resource')->where('manager.id='.$this->_manager['id'].' and manager.id = mrole.manager_id and mrole.role_id = r_resource.role_id and resource.id = r_resource.resource_id')->field('resource.id as id,resource.moudle as m,resource.action as action,resource.method as method')->order('resource.id desc' )->select();
            $this->_userAuth=$resourcelist;
            $c = CONTROLLER_NAME;
            //if(strpos($c, '/')){$c = substr($c,strpos($c, '/')+1);}
            /*if(!$this->checkauth($c,ACTION_NAME,$this->_userAuth))
            {
                $mesg = '抱歉,没有此操作的权限';
                if(IS_AJAX || IS_POST)
                {
                    $this->ajaxReturn(array('code'=>0,'error'=>1,'msg'=>$mesg,'message'=>$mesg));
                    exit;
                }
                else
                {
                    $this->error($mesg,'/Admin/Base/Login',1);
                }                
            }
             */
        }
    }
    /*
     * 检查用户权限
     */
    function checkauth($_action,$_method,$myauth)
    {
        if(strtolower($_action)=='base/config'&&strtolower($_method)=='updatecache')
        {
            return true;
        }
        if(strtolower($_action)=='base/selectwin' || strtolower($_action)=='base/index')
        {
            return true;
        }
        else
        {
            return checkauth_str($_action,$_method,$myauth);
        }
    }
    function setError($msg){
            $this->_ajaxmessage['code']=0;
            $this->_ajaxmessage['msg']=$msg;
    }
    function setMsg($msg){
            $this->_ajaxmessage['code']=1;
            $this->_ajaxmessage['msg']=$msg;
    }
    function setErrorExit($msg){
            $this->setError($msg);
            $this->ajaxReturn($this->_ajaxmessage,'json');
            exit;
    }

    function setMsgExit($msg){
            $this->setMsg($msg);
            $this->ajaxReturn($this->_ajaxmessage,'json');
            exit;
    }
    /*
     * 重新加载当前登录管理员信息
     */
    function reloadmanagerinfo()
    {
        $Condition = array('id'=>$this->_manager['id']);
        $entity = M('manager')->where($Condition)->find();
        $resourcelist = M()->table(C('DB_PREFIX').'manager manager,'.C('DB_PREFIX').'manager_role mrole, '.C('DB_PREFIX').'role_resource r_resource, '.C('DB_PREFIX').'resource resource')->where('manager.id='.$entity['id'].' and manager.id = mrole.manager_id and mrole.role_id = r_resource.role_id and resource.id = r_resource.resource_id')->field('resource.id as id,resource.action as action,resource.method as method')->order('resource.id desc' )->select();
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
        $entity['authcode'] = $resourcelist;
        session(C('MANAGERSESSION_ID'),$entity);
    }
    public function clearCache() {
        import("ORG.Io.Dir");
        $path = CACHE_PATH;   // 模版缓存目录         
        Dir::del($path);
        $path = TEMP_PATH;   // 数据缓存目录         
        Dir::del($path);
        $path = LOG_PATH;   // 日志目录         
        Dir::del($path);
        $path = DATA_PATH;  // 数据目录          
        Dir::del($path);        
    }
}