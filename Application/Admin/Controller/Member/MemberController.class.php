<?php
namespace Admin\Controller\Member;
class MemberController extends \Admin\Controller\CommonController {
    protected $Mod='';

    public function _initialize(){
        parent::_initialize();
        $this->Mod=D('User');
        $this->assign('keywords','会员');
    }

    public function lists(){   
        $type=I('get.type');
        if($type==1){
            $where['type']=1;
        }else{
            $where['type']=2;
        }
        //$where['status']=array('eq',0);
        $field='id,create_time,user_name,mobile';
        $data=$this->Mod->getAllList($where,$field);

        if($type==2){
            foreach ($data['list'] as $k => &$v) {
                $info=M('user_info')->where(array('user_id'=>$v['id']))->find();
                $info['home']=$info['home_pro'].'/'.$info['home_city'].'/'.$info['home_area'];
                $info['now']=$info['now_pro'].'/'.$info['now_city'].'/'.$info['now_area'];
                $info['txt_create_time']=date('Y-m-d H:i',$info['create_time']);
                $v['info']=$info;
            }
        }
        $this->assign('list',$data['list']);
        $this->assign('page',$data['page']);
        $this->assign('count',$data['count']);
        if($type==1){
            $this->display('lists');
        }else{
            $this->display('lists2');
        }
    }
}
?>