<?php
namespace Admin\Controller\Money;
class OrderController extends \Admin\Controller\CommonController {
    protected $Mod='';

    public function _initialize(){
        parent::_initialize();
        $this->Mod=D('Money');
    }
    public function lists(){ 
        $type=I('type');
        $search=I('');
        $where['type']=$type;

        if(I('combo')){
            $where['content_id']=I('combo');
        }

        if(I('lawyer')){
            $id=M('answer')->where(array('user_id'=>I('lawyer')))->field('id')->select();
            foreach ($id as $k => $v) {
                $array[$k]=$v['id'];
            }
            if(!empty($array)){
                $where['content_id']=array('in',$array);
            }
        }

        if(!empty($search['starttime'])){
            $time=strtotime($search['starttime'].' 00:00:00');
            $where['create_time']=array('GT',$time);      
        }
        if(!empty($search['endtime'])){
            $time=strtotime($search['endtime'].' 23:59:59');
            $where['create_time']=array('LT',$time);      
        }

        if(!empty($search['endtime']) && !empty($search['starttime'])){
            $time1=strtotime($search['starttime'].' 00:00:00');
            $time2=strtotime($search['endtime'].' 23:59:59');
            $where['create_time']=array('between',array($time1,$time2));      
        }

        $data=$this->Mod->getAllList($where);
        $this->assign('search',I(''));
        $this->assign('list',$data['list']);
        $this->assign('count',$data['count']);
        $this->assign('page',$data['page']);
        $this->assign('type',$type);
        //所有套餐
        if($type==1 || $type==2){
            $combo=M('combo')->field('id,title')->select();
            $this->assign('combo',$combo);
        }else{
            $lawyer=M('user')->where(array('identity'=>2,'status'=>2))->field('id,user_name')->select();
            $this->assign('lawyer',$lawyer);
        }
            

        //计算总的钱数
        $sum['type']=$where['type'];
        $money=$this->Mod->where($sum)->sum('money');
        $plat_money=$this->Mod->where($sum)->sum('plat_money');
        $plat_money=round($plat_money,2);
        $this->assign('money',$money);
        $this->assign('plat_money',$plat_money);

        $this->display('lists');
    }

    public function getOrderInfo(){
        $id=I('id');
        $data=D('Order')->getOrderInfo($id);
        $this->assign('data',$data);
        $this->display();
    }

    public function getAnswerInfo(){
        $id=I('id');
        $data=D('Answer')->getAnswerInfo($id);
        $this->assign('data',$data);
        $this->display();
    }

}
?>