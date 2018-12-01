<?php
return array(
    'URL_MODEL'=>1,
    'LOGINERROR_MAX_NUMBER' =>  10,//管理后台密码错误次数限制
    'LOGINERROR_TIME_LATER' =>  1*60*1000,//后台锁住后多长时间才能再次操作
    'CONTROLLER_LEVEL'      =>  2,//设置2级目录的控制器层

    // 配置邮件发送服务器
    'MAIL_SMTP'            =>  TRUE,
    'MAIL_HOST'            =>  'smtp.163.com',          //邮件发送SMTP服务器
    'MAIL_SMTPAUTH'   =>  TRUE,
    'MAIL_USERNAME'   =>  'dingxuegui@163.com',       //SMTP服务器登陆用户名
    'MAIL_PASSWORD'   =>  'zhanghui1157',              //SMTP服务器登陆密码
    'MAIL_SECURE'         =>  'tls',
    'MAIL_CHARSET'       =>  'utf-8',
    'MAIL_ISHTML'         =>  TRUE,
    'MAIL_FROMNAME' =>  'qqqqq',
    
);