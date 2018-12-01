<?php
namespace Admin\Controller\Base;
class ConfigController extends \Admin\Controller\CommonController {
    public function setsystem()
    {
        if(IS_POST)
        {
            $config = array();
            $config['systemname'] = I('post.systemname');
            $config['quespercent'] = I('post.quespercent');
            $config['orderpercent'] = I('post.orderpercent');
            $config['relisten'] = I('post.relisten');
            $config['relistenmoney'] = I('post.relistenmoney');
            $config['agency'] = I('post.agency');
            $config['keynum'] = I('post.keynum');
            $config['cashday'] = I('post.cashday');
            $config['cashnum'] = I('post.cashnum');
            $config['end_time'] = I('post.end_time');
            $config['askmoney'] = I('post.askmoney');
            $config['askdes'] = I('post.askdes');
            $config['share_title'] = I('post.share_title');
            $config['share_money'] = I('post.share_money');
            $config['rob_time'] = I('post.rob_time');
            
            $config['id'] = 'default';
            $newconfig = M('config')->save($config);
            F('config',$config);
            $this->setMsgExit('编辑系统参数成功');
        }else{ 
            $config = F('config');
            $this->assign('entity',$config);
            $this->show();
        }
    }
    /*
     * 更新系统缓存
     */
    public function updatecache()
    {
        updatecache();
        $this->setMsgExit('更新系统缓存成功');
    }
    /*
     * 测试smtp
     */
    public function testsmtp()
    {
        $config = array();
        $config['systememail'] = I('post.systememail');
        $config['smtpserver'] = I('post.smtpserver');
        $config['smtpport'] = I('post.smtpport');
        $config['smtpuser'] = I('post.smtpuser');
        $config['smtppwd'] = I('post.smtppwd');
        $config['checkemailtemplate'] = I('post.checkemailtemplate');
        $config['testemail'] = I('post.testemail'); 
        $suc = sendMail($config['testemail'],'发送邮件测试',$config['checkemailtemplate']);
        if($suc)
        {
                       
            $this->setMsgExit('发送邮件成功');
        }else
        {
            $this->setErrorExit('发送邮件失败');
        }
    }
}
?>