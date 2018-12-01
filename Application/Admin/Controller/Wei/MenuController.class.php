<?php
namespace Admin\Controller\Wei;
class MenuController extends \Admin\Controller\CommonController {
    public function lists(){
        if(!IS_POST){

            $data=getSysSetting('weixinmenu');
            $this->assign('list',$data['button']);
            $this->display();

        }else{

            

            $menu=$_POST['menu'];

            $submenu=$_POST['sub'];

            //$this->ajaxError(count($menu));

            $data=array();

            $buttons=array();

            foreach($menu as $k=>$m){

                $fs=array();

                $fs['name']=$m['title'];

                $fs['type']='view';

                $fs['url']=$m['link'];

                if(isset($submenu[$k])){

                    $fs['sub_button']=array();

                    foreach($submenu[$k] as $k2=>$sub){

                        $fs['sub_button'][]=array(

                            'name'=>$sub['title'],

                            'type'=>'view',

                            'url'=>$sub['link']

                            

                        );

                    }

                }

                $buttons[]=$fs;

            }

            $data['button']=$buttons;       

            $msg=createWxMenu($data);           

            for($i=0;$i<3;$i++){

                if($msg->errcode=='40001'){

                    //刷新token

                    //refreshAccessToken();

                    $msg=createWxMenu($data);

                }else{

                    break;

                }   

            }

            if($msg->errcode=='0'){

                setSysSetting('weixinmenu',$data);  

               $this->setMsgExit('设置菜单成功');

            }else{

                $this->setErrorExit($msg->errmsg.',code:'.$msg->errcode);

            }

            

            //print_r($data);

            //print_r($submenu);

        }

    }
    
}
?>