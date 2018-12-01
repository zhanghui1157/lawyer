<?php
namespace Admin\Controller\Wei;
class ParamterController extends \Admin\Controller\CommonController {
    public function lists(){

        if(empty(I('post.'))){

            $data=getSysSetting('weixinparamter');
            $this->assign('data',$data);
            $this->show();
        }else{
            $data=I('post.');

            setSysSetting('weixinparamter',$data);  

            $this->setMsgExit('设置参数成功');
        }
        
    }
   
}
?>