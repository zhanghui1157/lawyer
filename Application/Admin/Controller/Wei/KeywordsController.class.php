<?php
namespace Admin\Controller\Wei;
class KeywordsController extends \Admin\Controller\CommonController {
    public function lists(){

        if(empty(I('post.'))){

            $data=getSysSetting('weixinkeywords');
            $this->assign('list',$data);
            $this->show();
        }else{
            $menu=$_POST['menu'];

            $data=array();

            foreach($menu as $k=>$m){

                $fs=array();

                $fs['keywords']=$m['keywords'];

                $fs['text']=$m['text'];
  
                $data[]=$fs;

            }



            setSysSetting('weixinkeywords',$data);  

            $this->setMsgExit('设置关键字成功');
        }
        
    }
}
?>