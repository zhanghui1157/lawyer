<?php
namespace Home\Controller;

use Think\Controller;

/**
 * Home\Controller 的父类
 * @AUTHOR VIEWWING
 * DATE 2016年12月16日
 */
class BaseController extends Controller{
	private $appid='wx694a2af25a489771';
    private $secret='83dfa93de3c37349e7b5b23aa987b19e';
    
    // 获取OPENID
    function getOpenid(){
        $code = I('get.code');
        $appid = $this->appid;
        $appsecret = $this->secret;
        $weixin =  file_get_contents("https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$appsecret&js_code=".$code."&grant_type=authorization_code");

        $jsondecode = json_decode($weixin); //对JSON格式的字符串进行编码
        $array = get_object_vars($jsondecode);//转换成数组
        $title=F('config')['share_title']; 

        $openid=$array['openid'];
        $num=M('visit')->where(array('detail_time'=>date('Y-m-d',time()),'openid'=>$openid))->find();
        if(is_array($num)){
            M('visit')->where(array('detail_time'=>date('Y-m-d',time()),'openid'=>$openid))->setInc('count',1);
        }else{
            $data['openid']=$array['openid'];
            $data['create_time']=time();
            $data['detail_time']=date('Y-m-d',time());
            M('visit')->add($data);
        }

        json(1,'获取成功！',$array,$weixin,$title);
    }

    // 添加用户
    public function toAddUser(){
        $data['openid'] = $map['openid'] = I('post.openid');
        $data['nick_name'] = I('post.weixin_nick');
        $data['headimg'] = I('post.avatar');
        $data['last_login_time'] = time();
        $info = M('user')->where($map)->find();

        if(!$info){
            $data['create_time'] = time();
            $id=M('user')->add($data);
            if($id){
                $info = M('user')->where(array('id'=>$id))->find();
                json(1,'设置成功',$info);
            }else{
                json(0,'设置失败');
            }
        }else{
            if(M('user')->where($map)->data($data)->save()) {
                json(1,'更新成功',$info);
            }else{
                json(0,'设置不变',$info);
            }
        }
    }
    
    // 获取用户信息
    public function getInfo(){
        $map['openid'] = I('post.openid');
        $info = M('user')->where($map)->find();
        json(1,'获取成功',$info);
    }
}