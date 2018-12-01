<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title></title>
        <meta name="keywords" content="">
        <meta name="description" content="">
        <link href="/Public/hplus/css/bootstrap.min.css?v=3.4.0" rel="stylesheet">
        <link href="/Public/hplus/css/font-awesome.min.css?v=4.3.0" rel="stylesheet">
        <link href="/Public/hplus/css/animate.min.css" rel="stylesheet">
        <link href="/Public/hplus/css/style.min.css?v=3.2.0" rel="stylesheet">
        <link href="/Public/hplus/css/combo.select.css" rel="stylesheet">
        
<link href="/Public/plug/kindeditor/themes/default/default.css" rel="stylesheet" type="text/css" media="all"/>

    </head>
    <body class="gray-bg">
        <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-sm-12">
                <!-- 主体列表内容 -->
                
    <div class="ibox float-e-margins">
        <div class="ibox-title">
            <h5>问答回复内容</h5>
        </div>
        <div class="ibox-content">
            <?php if(!empty($list)): ?><div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th width="100">编号</th>
                                <th width="100">内容</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(is_array($list)): foreach($list as $key=>$vo): ?><tr>
                                <td><?php echo ($vo["id"]); ?></td>
                                <td>
                                    <audio controls="controls">
                                        <source src="<?php echo ($vo["path"]); ?>" type="audio/ogg">
                                    </audio>
                                </td>
                            </tr><?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <textarea name="content" placeholder="编辑答案内容" class="content" style="width:700px;height:200px;visibility:hidden;"><?php echo ($content); ?></textarea><?php endif; ?>
        </div>
    </div>

            </div>
        </div>
    </div>
        <!-- 全局js -->
        <script src="/Public/hplus/js/jquery-2.1.1.min.js"></script>
        <script src="/Public/hplus/js/bootstrap.min.js?v=<?php echo C('SYSTEM_VER');?>"></script>
        <script src="/Public/hplus/js/layer/layer.min.js?v=<?php echo C('SYSTEM_VER');?>"></script>
        <script src="/Public/hplus/js/laydate/laydate.js?v=<?php echo C('SYSTEM_VER');?>"></script>
        <script type="text/javascript" src="/Public/hplus/js/boot.js?v=<?php echo C('SYSTEM_VER');?>"></script>
        <script src="/Public/hplus/js/jquery.combo.select.js"></script>
        <script type="text/javascript" src="/Public/hplus/js/winopen.js"></script>
        <script type="text/javascript" src="/Public/hplus/js/areas.js?v=<?php echo C('SYSTEM_VER');?>"></script>
        <script type="text/javascript">
            $(document).ready(function () {  
                $('select').comboSelect();  
                $('a.ajaxdo').click(function(){
                    var url=$(this).attr('href');
                    parent.layer.confirm($(this).attr('tip'), {
                        title:'操作确认',
                        btn: ['确定','取消'], //按钮
                        shade: true, //显示遮罩
                        shade: 0.5
                    }, function(){
                        execute(url, 1, null,function(data){
                            if(data.code==1){parent.layer.msg(data.msg, {icon:1,shadeClose: true,shade: 0.5,end:function(){window.location.reload();}});}else{parent.layer.msg(data.msg, {icon:2,shadeClose: true,shade: 0.5});}
                        }, function(){
                            parent.layer.msg('服务器处理失败', {icon:2,shadeClose: true,shade: 0.5});
                        }); 
                    }, function(){

                    });
                    return false;
                });
            });
        </script>
        
    <script charset="utf-8" src="/Public/plug/kindeditor/kindeditor-min.js"></script>
    <script charset="utf-8" src="/Public/plug/kindeditor/lang/zh_CN.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {  
            var editor;
            KindEditor.ready(function(K) { 
                editor = K.create('textarea[name="content"]', { 
                    allowFileManager : true, 
                    afterBlur:function(){this.sync();} 
                }); 
            });        
            $('select').comboSelect();

            $("#lawyer").change(function(){
                $('#form1').submit();
            });
            $("#price").change(function(){
                $('#form1').submit();
            })
        });
    </script>


    </body>
</html>