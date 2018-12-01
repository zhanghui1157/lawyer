<?php
namespace Admin\Controller\Message;
class MessageController extends \Admin\Controller\CommonController {
    protected $Mod='';

    public function _initialize(){
        parent::_initialize();
        $this->Mod=D('Message');
    }
    public function lists(){
        $data=$this->Mod->getAllList($where);

        $this->assign('count',$data['count']);
        $this->assign('list',$data['list']);
        $this->assign('page',$data['page']);

        $this->display('lists');
    }

    //编辑
    public function edit(){
        if(empty(I('post.'))){
            if(I('id')!=0){
                $id=I('id');
                $entity=$this->Mod->where(array('id'=>$id))->find();
                $this->assign('entity',$entity);
            }
            $user=M('user')->where(array('identity'=>1))->field('id,nick_name')->select();
            $this->assign('user',$user);
            $this->show();
        }else{
            $data=I('post.');
            if($data['u_id']!=0){
                if(!$this->Mod->create($data)){
                    exit($this->Mod->getError());
                }else{
                    $res=$this->Mod->where(array('id'=>$data['u_id']))->save();
                    if($res){
                        $this->setMsgExit($this->keywords.'修改成功');
                    }else{
                        $this->setErrorExit($this->keywords.'修改失败');
                    }
                }
            }else{

                $data['create_time']=time();
                if($data['msgobj']==3){
                    if(!empty($data['city'])){
                        $lawyer=$this->getCityLawyer($data['city']);
                        foreach ($lawyer as $k => &$v) {
                            $data['user_id']=$v['id'];
                            $res=$this->Mod->add($data);
                        }
                        if($res){
                            $this->setMsgExit($this->keywords.'添加成功');
                        }else{
                            $this->setErrorExit($this->keywords.'添加失败');
                        }
                    }
                    
                }else{
                    if (!$this->Mod->create($data)){ // 创建数据对象
                         // 如果创建失败 表示验证没有通过 输出错误提示信息
                        exit($this->Mod->getError());
                    }else{
                        // 验证通过 写入新增数据
                        $res=$this->Mod->add();
                        if($res){
                            $this->setMsgExit($this->keywords.'添加成功');
                        }else{
                            $this->setErrorExit($this->keywords.'添加失败');
                        }
                    } 
                }
                    
                    
            }
        }
    }

    public function del(){
        if(I('get.id')){
            $id = I('get.id');
            $this->Mod->where(array('id'=>array('eq',$id)))->delete();
            $this->setMsgExit('已删除一条记录');
        }else{
            $this->setErrorExit('请至少指定要删除的对象');
        }
    }

    public function getUser(){
        $obj=I('obj');
        if($obj==1){
            $user=M('user')->where(array('identity'=>1))->field('id,nick_name as user_name')->select();
        }elseif($obj==2){
            $user=M('user')->where(array('identity'=>2))->field('id,user_name')->select();
        }

        $this->ajaxReturn($user);
    }


    public function getCityLawyer($city){
        $where['r.identity']=2;
        $where['r.status']=2;
        $where['i.now_city']=$city;
        $lawyer=M('user_info')
            ->alias('i')
            ->join('db_user r on r.id=i.user_id')
            ->where($where)
            ->field('r.id,r.user_name')->select();
        return $lawyer;
    }

    
}
?>