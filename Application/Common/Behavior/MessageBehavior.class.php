<?php  
namespace Common\Behavior;
/**
 * 给数据库中添加信息
 * 使用方法：\Think\Hook::listen('AddMessage',$param);
 */
class MessageBehavior{
    function run($param){
    	$data['create_time']=time();
    	$data['user_id']=$param['user_id'];
    	$data['content']=$param['content'];
        M('message')->add($data);
    }
}