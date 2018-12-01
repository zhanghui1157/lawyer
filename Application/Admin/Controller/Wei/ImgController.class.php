<?php
namespace Admin\Controller\Wei;
class ImgController extends \Admin\Controller\CommonController{
    public function lists(){
    	$data=M('img')->select();
    	foreach ($data as &$v) {
    		if($v['status']==1){
    			$v['txt_status']='有效';
    		}else{
    			$v['txt_status']='无效';
    		}
    	}
    	$this->assign('list',$data);
        $this->display();
    }

    public function edit(){
    	if(empty(I('post.'))){
    		if(I('get.id')){
    			$entity=M('img')->where(array('id'=>I('get.id')))->find();
    			$this->assign('entity',$entity);
    		}
    		$this->display();
    	}else{
    		$data=I('');
    		if(empty($data['u_id'])){	
    			$data['create_time']=time();
    			$res=M('img')->add($data);
    			if($res){
    				$this->setMsgExit('添加成功！');
    			}else{
    				$this->setErrorExit('添加失败！');
    			}
    		}else{
    			$data['update_time']=time();
    			$res=M('img')->where(array('id'=>$data['u_id']))->save($data);
    			if($res){
    				$this->setMsgExit('修改成功！');
    			}else{
    				$this->setErrorExit('修改失败！');
    			}
    		}
    	}
        
    }
}
?>