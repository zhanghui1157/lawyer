<?php
    if(C('LAYOUT_ON')) {
        echo '{__NOLAYOUT__}';
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>系统发生错误</title>
        <meta name="keywords" content="">
        <meta name="description" content="">
        <link href="/Public/admin/css/bootstrap.min.css?v=3.4.0" rel="stylesheet">
        <link href="/Public/admin/css/font-awesome.min.css?v=4.3.0" rel="stylesheet">
        <link href="/Public/admin/css/animate.min.css" rel="stylesheet">
        <link href="/Public/admin/css/style.min.css?v=3.2.0" rel="stylesheet">
    </head>
    <body class="gray-bg"> 
        <div class="wrapper wrapper-content">
        <div class="row">
            <div class="animated fadeInRight">
                <div class="mail-box-header">
                    <div class="pull-right tooltip-demo">
                        <a href="javascript:postproblem()" class="btn btn-white btn-sm" data-toggle="tooltip" data-placement="top" title="发送错误报告"><i class="fa fa-reply"></i> 发送错误报告</a>
                        <a href="javascript:printpage()" class="btn btn-white btn-sm" data-toggle="tooltip" data-placement="top" title="打印"><i class="fa fa-print"></i> </a>
                    </div>
                    <h2 id='mail-title'><?php echo strip_tags($e['message']);?></h2>
                    <div class="mail-tools tooltip-demo m-t-md">
                        <?php if(isset($e['file'])) {?><h3><span class="font-noraml">错误位置</span>FILE: <?php echo $e['file'] ;?> &#12288;LINE: <?php echo $e['line'];?></h3><?php }?>
                        <h5>
                        <span class="pull-right font-noraml"><?php echo date('Y年m月d日 h时i分s秒',time());?></span>
                        <span class="font-noraml">IP地址： </span><?php echo $_SERVER["REMOTE_ADDR"];?>
                    </h5>
                    </div>
                </div>
                <div class="mail-box">
                    <div class="mail-body">
                        <h4>TRACE：</h4>
                        <?php if(isset($e['trace'])) {?>
                        <p><?php echo nl2br($e['trace']);?></p>
                        <?php }?>
                        <p class="text-right">
                        </p>
                    </div>                    
                    <div class="mail-body text-right tooltip-demo">
                        <a class="btn btn-sm btn-white" href="javascript:postproblem()"><i class="fa fa-reply"></i>发送错误报告</a>
                        <button title="" onclick='printpage()' data-placement="top" data-toggle="tooltip" type="button" data-original-title="打印" class="btn btn-sm btn-white"><i class="fa fa-print"></i> 打印</button>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
        <!-- 全局js -->
        <script src="/Public/admin/js/jquery-2.1.1.min.js"></script>
        <script src="/Public/admin/js/bootstrap.min.js"></script>
        <script src="/Public/admin/js/layer/layer.min.js"></script>
        <script type="text/javascript" src="/Public/manager/scripts/manager/boot.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                
            });
            function postproblem()
            {
                function suc(data){
                    if(data.code==1){
                        layer.msg('发送成功，谢谢您的参与', {icon:1,shadeClose: true,shade: 0.5,end:function(){}});
                    }
                    else{
                        layer.msg(data.msg, {icon:2,shadeClose: true,shade: 0.5,});
                    }
                }
                function err(){layer.msg('报告发送失败',{icon:2,shadeClose: true,shade: 0.5,});}
                execute('/ajax/postproblem',1,{title:$('#mail-title').html(),content:$('.mail-body').html()},suc,err);
            }
            function printpage()
            {
                layer.msg('请使用浏览器的打印功能', {icon:2,shadeClose: true,shade: 0.5,});
            }
        </script>
    </body>
</html>