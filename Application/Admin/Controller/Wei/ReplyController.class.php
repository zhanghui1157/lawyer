<?php
namespace Admin\Controller\Wei;
class ReplyController extends \Admin\Controller\CommonController {
    public function lists()
    {
    	if(!IS_POST){
            $data=getSysSetting('weixinreply');
            $this->assign('data',$data);
        }else{
            $data['scribe_content']=I('content');
            setSysSetting('weixinreply',$data);
            $this->setMsgExit('修改回复内容成功');
        }
    	$this->show();
    }
    
}
?>