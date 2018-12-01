<?php
return array(
    /* 数据库设置 */
    'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  '127.0.0.1', // 服务器地址
    'DB_NAME'               =>  'lawyer',          // 数据库名
    'DB_USER'               =>  'root',      // 用户名
    'DB_PWD'                =>  'root',          // 密码
    'DB_PORT'               =>  '3306',        // 端口
    'DB_PREFIX'             =>  'db_',    // 数据库表前缀
    'DB_PARAMS'          	=>  array(), // 数据库连接参数    
    'DB_DEBUG'  			=>  TRUE, // 数据库调试模式 开启后可以记录SQL日志
    'DB_FIELDS_CACHE'       =>  true,        // 启用字段缓存
    'DB_CHARSET'            =>  'utf8',      // 数据库编码默认采用utf8
    'DB_DEPLOY_TYPE'        =>  0, // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'DB_RW_SEPARATE'        =>  false,       // 数据库读写是否分离 主从式有效
    'DB_MASTER_NUM'         =>  1, // 读写分离后 主服务器数量
    'DB_SLAVE_NO'           =>  '', // 指定从服务器序号
    'DEFAULT_THEME'         =>  'default',	// 默认模板主题名称
    'APP_SUB_DOMAIN_DEPLOY' =>  false,   // 是否开启子域名部署
    'SHOW_PAGE_TRACE'       =>  false,
    'SYSTEM_VER'            =>  '2.1',
    'MANAGERSESSION_ID'     =>  'manager',//管理员session键名
    'uploaddir'             =>  'Public',
	'LANG_SWITCH_ON'        =>  true,
	'LANG_PATH'             =>  'COMMON_PATH.\'Lang/\'',//默认语言包
	'DEFAULT_LANG'          =>  'zh-cn',    // 默认语言
	'LANG_AUTO_DETECT'      =>  true,    // 自动侦测语言
    'LOAD_EXT_FILE'         => 'common,newfunction' ,

    //验证码设置
    'CHECKCODE'=>array(
        'fontSize' => 18, // 验证码字体大小
        'length' => 4, // 验证码位数
        'imageH' => 35,
        'imageW' => 150,
        'useZh' => 0,
        'zhSet' => '慧科',
        'useNoise'=>false,
        'useCurve'=>false,
    ),

    //以下为模块配置(此处配置直接会影响后台管理菜单)
    'MOUDLELIST'=>array(
        'Member'=>array('open'=>1,'name'=>'会员','ico'=>'desktop'),
        'Lawyer'=>array('open'=>1,'name'=>'律师','ico'=>'desktop'),
        'Answer'=>array('open'=>1,'name'=>'优问','ico'=>'desktop'),
        'Order' =>array('open'=>1,'name'=>'咨询订单','ico'=>'desktop'),
        'Combo' =>array('open'=>1,'name'=>'套餐','ico'=>'desktop'),
        'Money' =>array('open'=>1,'name'=>'财务','ico'=>'desktop'),
    	'Message' =>array('open'=>1,'name'=>'消息','ico'=>'desktop'),
    	'Wei'   =>array('open'=>1,'name'=>'小程序','ico'=>'desktop'),
        'Base'  =>array('open'=>1,'name'=>'系统配置','ico'=>'desktop'),//系统配置
    ),

    
);