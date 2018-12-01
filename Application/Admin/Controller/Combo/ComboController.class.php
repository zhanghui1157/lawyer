<?php
namespace Admin\Controller\Combo;
class ComboController extends \Admin\Controller\CommonController {
    protected $Mod='';
    protected $keywords='';

    public function _initialize(){
        parent::_initialize();
        $this->Mod=D('Combo');
        $this->keywords="套餐";
        $this->assign('keywords',$this->keywords);
    }

    public function lists(){   
        $data=$this->Mod->getAllList($where);

        $this->assign('list',$data['list']);
        $this->assign('count',$data['count']);
        $this->assign('page',$data['page']);
        $this->show();
    }

    //编辑
    public function edit(){
        if(empty(I('post.'))){
            if(I('id')!=0){
                $id=I('id');
                $entity=$this->Mod->where(array('id'=>$id))->find();
                $this->assign('entity',$entity);
            }
            $this->show();
        }else{
            $data=I('post.');
            if($data['u_id']!=0){
                $date['update_time']=time();
                if(!$this->Mod->create($data)){
                    exit($this->Mod->getError());
                }else{
                    $res=$this->Mod->where(array('id'=>$data['u_id']))->save();
                    if($res){
                        addlog($this->_manager,'修改套餐名称为 "'.$data['title'].'" 成功');
                        $this->setMsgExit($this->keywords.'修改成功');
                    }else{
                        $this->setErrorExit($this->keywords.'修改失败');
                    }
                }
                    
            }else{
                $data['create_time']=time();
                if (!$this->Mod->create($data)){ // 创建数据对象
                     // 如果创建失败 表示验证没有通过 输出错误提示信息
                     exit($this->Mod->getError());
                }else{
                     // 验证通过 写入新增数据
                    $res=$this->Mod->add();
                    if($res){
                        addlog($this->_manager,'添加套餐名称为 "'.$data['title'].'" 成功');
                        $this->setMsgExit($this->keywords.'添加成功');
                    }else{
                        $this->setErrorExit($this->keywords.'添加失败');
                    }
                }
                    
            }
        }
    }

    public function del(){
        if(I('get.id')){
            $id = I('get.id');
            $this->Mod->where(array('id'=>array('eq',$id)))->delete();
            addlog($this->_manager,'删除套餐id为 "'.$id.'" 成功');
            $this->setMsgExit('已删除一条记录');
        }else{
            $this->setErrorExit('请至少指定要删除的对象');
        }
    }
    
}
?>