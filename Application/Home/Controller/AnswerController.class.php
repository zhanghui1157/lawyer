<?php
namespace Home\Controller;
use Think\Controller;
header("Content-Type:text/html;charset=utf-8");
header('Access-Control-Allow-Origin:*');
class AnswerController extends Controller {
	public function question(){
		
		$data=I('');
		if(!empty($data['first'])){
			$first=M('dictionary')->where(array('name'=>$data['first']))->find();
			$data['cate']=$first['name'];
			$data['first']=$first['id'];
		}
		if(!empty($data['second'])){
			$second=M('dictionary')->where(array('name'=>$data['second']))->find();
			$data['cate']=$data['cate'].'>'.$second['name'];
			$data['second']=$second['id'];
		}

		$user_id=M('user')->where(array('openid'=>I('openid')))->getField('id');
		$this->assign('user_id',$user_id);
		$this->assign('data',$data);
		$this->assign('money',zidian(1));
		$this->display();
	}

	// 获取问答金额
	public function initMoney(){
		$data=zidian(1);
		json(1,'获取列表信息成功！',$data);
	}

	public function cate_choose(){
		if(empty(I('id'))){
			$data=M('dictionary')->where(array('level'=>1,'type'=>2))->field('id,name')->select();
		}else{
			$data=M('dictionary')->where(array('pid'=>I('id'),'type'=>2))->field('id,name')->select();
		}
			
		if(!empty($data)){
			json(1,'获取列表信息成功',$data);
		}else if(empty($data)){
			json(0,'获取列表信息为空');
		}else{
			json(0,'获取列表信息失败');
		}
	}
	public function cate_choose_all(){
		$shen_name=M('dictionary')->where(array('id'=>I('one')))->getField('name');
		$shi_name=M('dictionary')->where(array('id'=>I('two')))->getField('name');
		$data=$shen_name.'>'.$shi_name;
		if(!empty($data)){
			json(1,'获取信息成功',$data);
		}else if(empty($data['data'])){
			json(0,'获取信息为空');
		}else{
			json(0,'获取信息失败');
		}
	}


	public function sub(){
		$data=I('');
		$cond=array(
		   'title'=>$data['title'],
		   'first'=>$data['first'],
		   'second'=>$data['second'],
		   'content'=>I('content'),
		   'money'=>$data['money'],
		   'user_id'=>$data['user_id'],
		   'create_time'=>time(),
		   'type'=>1,
		);
		$result=M('answer')->add($cond);
		
		if($result){
			json(1,'信息提交成功！');
		}else{
			json(0,'信息提交失败！');
		}
	}
        
}