<?php
namespace Home\Controller;
use Think\Controller;
class NewsController extends Controller {
    
      public function getInfo(){
        $map['openid'] = I('post.openid');
        $info = M('user')->where($map)->find();
        json(1,'获取成功',$info);
    }
}