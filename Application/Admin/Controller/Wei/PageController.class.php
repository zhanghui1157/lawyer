<?php
namespace Admin\Controller\Wei;
class PageController extends \Admin\Controller\CommonController {
    public function lists(){
        $count=M('article')->count();
        $data=M('article')->order('id desc')->select();
        foreach ($data as $k => &$v) {
            if($v['is_connect']==1){
                $v['user_name']=M('user')->where(array('id'=>$v['lawyer']))->getField('user_name');
            }
        }
        $this->assign('list',$data);
        $this->assign('count',$count);
        $this->show();
    }


    public function edit(){
        if(I('post.')){   
            
            $data=I('');
            $data['content']=str_replace('&nbsp;', '',I('content','',''));
            $id = I('id');
            if($id==0){
                $data['create_time']=time();
                $res = M('article')->add($data);
                if($res){
                    $this->setMsgExit('添加成功');
                }else{
                    $this->setErrorExit('添加失败');
                }
            }else{  
                $res = M('article')->where(array('id'=>$id))->save($data);
                if($res){
                    $this->setMsgExit('修改成功');
                }else{
                    $this->setErrorExit('修改失败');
                }
            }
        }else{  
            $id = I('id');   
            if(!empty($id)){
                $entity=M('article')->where(array('id'=>$id))->find();
                $this->assign('entity',$entity);
            }            
            $lawyer=M('user')->where(array('identity'=>2,'status'=>2))->field('user_name,id')->select();
            $this->assign('lawyer',$lawyer);
            $this->show();
        }
    }

    public function del(){
        if(I('get.id')){
            $id = I('get.id');
            M('article')->where(array('id'=>array('eq',$id)))->delete();
            $this->setMsgExit('已删除一条记录');
        }else{
            $this->setErrorExit('请至少指定要删除的对象');
        }
    }
}
?>