<?php  
namespace Home\Behavior;
    class AdBehavior{
        function run($arg){
            echo '我是一条'.$arg['name'].'广告,'.$arg['value'].'代言';        //在此介绍下，run必须的 ，细心的会在Think核心找到Behavior.class.php里面有这样一句操蛋的话  abstract public function run(&$params); 你懂的
        }
    }