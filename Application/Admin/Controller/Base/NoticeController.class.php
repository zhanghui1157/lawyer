<?php
namespace Admin\Controller\Base;
class NoticeController extends \Admin\Controller\CommonController {
    public function lists()
    {
        if(IS_POST)
        {   
            $data=I('');
            $data['phone']=implode(',',$data['phone']);
            M('notice')->where(array('id'=>'default'))->save($data);
            $this->setMsgExit('编辑系统参数成功');
        }else{ 
            $config = M('notice')->where(array('id'=>'default'))->find();
            $phone=explode(',',$config['phone']);
            $this->assign('phone',$phone);
            $this->assign('entity',$config);
            $this->show();
        }
    }
}
?>