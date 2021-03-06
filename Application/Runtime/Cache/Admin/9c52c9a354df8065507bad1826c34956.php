<?php if (!defined('THINK_PATH')) exit();?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <title>律师去哪</title>
    <meta name="keywords" content="">
    <meta name="description" content="">
    <!--[if lt IE 8]>
    <script>
        alert('系统不支持IE6-8，请使用谷歌、火狐等浏览器\n或360、QQ等国产浏览器的极速模式浏览本页面！');
    </script>
    <![endif]-->
    <link href="/Public/hplus/css/bootstrap.min.css?v=<?php echo C('SYSTEM_VER');?>" rel="stylesheet">
    <link href="/Public/hplus/css/font-awesome.min.css?v=<?php echo C('SYSTEM_VER');?>" rel="stylesheet">
    <link href="/Public/hplus/css/animate.min.css" rel="stylesheet">
    <link href="/Public/hplus/css/style.min.css?v=<?php echo C('SYSTEM_VER');?>" rel="stylesheet">
</head>

<body class="fixed-sidebar full-height-layout gray-bg">
    <div id="wrapper">
        <!--左侧导航开始-->
        <nav class="navbar-default navbar-static-side along_login_left" role="navigation"  >
            <div class="nav-close"><i class="fa fa-times-circle"></i>
            </div>
            <div class="sidebar-collapse">
                <ul class="nav" id="side-menu">
                    <li class="nav-header">
                        <div class="dropdown profile-element">
                            <span><img alt="image" class="img-circle" src="<?php echo ($_currentUser["headpic"]); ?>" style="width:64px;height:64px;" /></span>
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                <span class="clear">
                               <span class="block m-t-xs"><strong class="font-bold"><?php echo ($_currentUser["adminname"]); ?></strong></span>
                                <span class="text-muted text-xs block"><?php echo ($_currentUser["rolename"]); ?><b class="caret"></b></span>
                                </span>
                            </a>
                            <ul class="dropdown-menu animated fadeInRight m-t-xs">
                                <li><a class="J_menuItem" href="/Admin/Base/manager/modifypwd">修改密码</a></li>
                                <li><a class="J_menuItem" href="/Admin/Base/manager/modifyother">修改头像</a></li>
                                <li class="divider"></li>
                                <li><a href="/Admin/Base/Index/logout.html">安全退出</a>
                                </li>
                            </ul>
                        </div>
                        <div class="logo-element">
                        </div>
                    </li>                    
                    <?php echo ($menu); ?>
                </ul>
            </div>
        </nav>
        <!--左侧导航结束-->
        <!--右侧部分开始-->
        <div id="page-wrapper" class="gray-bg dashbard-1">
            <!-- <div class="row border-bottom">
                <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                    <div class="navbar-header"><a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
                        <form role="search" class="navbar-form-custom" method="post" action="" target="_blank">
                            <div class="form-group">
                                <input type="text" placeholder="请输入您需要查找的内容 …" class="form-control" name="top-search" id="top-search">
                            </div>
                        </form>!
                    </div>
                   
                </nav>
            </div> -->
            <div class="row content-tabs">
                <button class="roll-nav roll-left J_tabLeft"><i class="fa fa-backward"></i>
                </button>
                <nav class="page-tabs J_menuTabs">
                    <div class="page-tabs-content">
                        <a class="active J_menuTab" data-id="introduce">首页</a>
                    </div>
                </nav>
                <button class="roll-nav roll-right J_tabRight"><i class="fa fa-forward"></i>
                </button>
                <div class="btn-group roll-nav roll-right">
                    <button class="dropdown J_tabClose" data-toggle="dropdown">关闭操作<span class="caret"></span>

                    </button>
                    <ul role="menu" class="dropdown-menu dropdown-menu-right">
                        <li class="J_tabShowActive"><a>定位当前选项卡</a>
                        </li>
                        <li class="divider"></li>
                        <li class="J_tabCloseAll"><a>关闭全部选项卡</a>
                        </li>
                        <li class="J_tabCloseOther"><a>关闭其他选项卡</a>
                        </li>
                    </ul>
                </div>
                <a href="/Admin/Base/Index/logout" class="roll-nav roll-right J_tabExit"><i class="fa fa fa-sign-out"></i> 退出</a>
            </div>
            <div class="row J_mainContent" id="content-main">
                <iframe class="J_iframe" name="iframe0" width="100%" height="100%" src="/Admin/Base/Index/introduce" frameborder="0" data-id="introduce" seamless></iframe>
            </div>
            <div class="footer">
                <div class="pull-right">&copy; 2015-2020 <a href="" target="_blank"></a>
                </div>
            </div>
        </div>
        <!--右侧部分结束-->
        <!--右侧边栏开始-->
        <div id="right-sidebar">
            <div class="sidebar-container">

                <ul class="nav nav-tabs navs-3">
                    <li class="active"><a data-toggle="tab" href="#tab-1">
                        通知
                    </a>
                    </li>
                    <li><a data-toggle="tab" href="#tab-2">
                        项目进度
                    </a>
                    </li>
                    <li class="">
                        <a data-toggle="tab" href="#tab-3">
                            <i class="fa fa-gear"></i>
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div id="tab-1" class="tab-pane active">
                        <div class="sidebar-title">
                            <h3> <i class="fa fa-comments-o"></i> 最新通知</h3>
                            <small><i class="fa fa-tim"></i> 您当前有0条未读信息</small>
                        </div>
                        <div>                          
                        </div>
                    </div>
                    <div id="tab-2" class="tab-pane">
                        <div class="sidebar-title">
                            <h3> <i class="fa fa-cube"></i> 最新任务</h3>
                            <small><i class="fa fa-tim"></i> 您当前有0个任务，0个已完成</small>
                        </div>
                        <ul class="sidebar-list">                            
                        </ul>
                    </div>
                    <div id="tab-3" class="tab-pane">
                        <div class="sidebar-title">
                            <h3><i class="fa fa-gears"></i> 设置</h3>
                        </div>

                        <div class="setings-item">
                            <span>
                        显示通知
                    </span>
                            <div class="switch">
                                <div class="onoffswitch">
                                    <input type="checkbox" name="collapsemenu" class="onoffswitch-checkbox" id="example">
                                    <label class="onoffswitch-label" for="example">
                                        <span class="onoffswitch-inner"></span>
                                        <span class="onoffswitch-switch"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="setings-item">
                            <span>
                        隐藏聊天窗口
                    </span>
                            <div class="switch">
                                <div class="onoffswitch">
                                    <input type="checkbox" name="collapsemenu" checked class="onoffswitch-checkbox" id="example2">
                                    <label class="onoffswitch-label" for="example2">
                                        <span class="onoffswitch-inner"></span>
                                        <span class="onoffswitch-switch"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="setings-item">
                            <span>
                        清空历史记录
                    </span>
                            <div class="switch">
                                <div class="onoffswitch">
                                    <input type="checkbox" name="collapsemenu" class="onoffswitch-checkbox" id="example3">
                                    <label class="onoffswitch-label" for="example3">
                                        <span class="onoffswitch-inner"></span>
                                        <span class="onoffswitch-switch"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="setings-item">
                            <span>
                        显示聊天窗口
                    </span>
                            <div class="switch">
                                <div class="onoffswitch">
                                    <input type="checkbox" name="collapsemenu" class="onoffswitch-checkbox" id="example4">
                                    <label class="onoffswitch-label" for="example4">
                                        <span class="onoffswitch-inner"></span>
                                        <span class="onoffswitch-switch"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="setings-item">
                            <span>
                        显示在线用户
                    </span>
                            <div class="switch">
                                <div class="onoffswitch">
                                    <input type="checkbox" checked name="collapsemenu" class="onoffswitch-checkbox" id="example5">
                                    <label class="onoffswitch-label" for="example5">
                                        <span class="onoffswitch-inner"></span>
                                        <span class="onoffswitch-switch"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="setings-item">
                            <span>
                        全局搜索
                    </span>
                            <div class="switch">
                                <div class="onoffswitch">
                                    <input type="checkbox" checked name="collapsemenu" class="onoffswitch-checkbox" id="example6">
                                    <label class="onoffswitch-label" for="example6">
                                        <span class="onoffswitch-inner"></span>
                                        <span class="onoffswitch-switch"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="setings-item">
                            <span>
                        每日更新
                    </span>
                            <div class="switch">
                                <div class="onoffswitch">
                                    <input type="checkbox" name="collapsemenu" class="onoffswitch-checkbox" id="example7">
                                    <label class="onoffswitch-label" for="example7">
                                        <span class="onoffswitch-inner"></span>
                                        <span class="onoffswitch-switch"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="sidebar-content">
                            <h4>设置</h4>
                            <div class="small">
                                你可以从这里设置一些常用选项，当然啦，这个只是个演示的示例。
                            </div>
                        </div>

                    </div>
                </div>

            </div>



        </div>
        <!--右侧边栏结束-->        
    </div>
    <!-- 全局js -->
    <script src="/Public/hplus/js/jquery-2.1.1.min.js"></script>
    <script src="/Public/hplus/js/bootstrap.min.js?v=<?php echo C('SYSTEM_VER');?>"></script>
    <script src="/Public/hplus/js/jquery.metisMenu.js"></script>
    <script src="/Public/hplus/js/jquery.slimscroll.min.js"></script>
    <script src="/Public/hplus/js/layer/layer.min.js"></script>

    <!-- 自定义js -->
    <script src="/Public/hplus/js/hplus.min.js?v=<?php echo C('SYSTEM_VER');?>"></script>
    <script type="text/javascript" src="/Public/hplus/js/contabs.min.js"></script>
    <!-- 第三方插件 -->
    <script src="/Public/hplus/js/pace.min.js"></script>
    <script type="text/javascript">
        function updateorders(numbers)
        {
            $('#orderscount').text(numbers);
            $('#ordersinfo').text('您有'+numbers+'个未处理的分期订单');
        }
    </script>
</body>

</html>