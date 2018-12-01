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
        
    </head>
    <body class="gray-bg">
        <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-sm-12">
                <!-- 主体列表内容 -->
                
    <div class="ibox float-e-margins">
        <div class="ibox-title">
            <h5><?php echo ($keyword); ?>列表</h5>
        </div>
        <div class="ibox-content">
            <form role="form" class="form-inline" method="get" id="form1">
                <div class="form-group">
                    <select name="lawyer" id="lawyer" class="form-control">
                        <option value="">全部律师</option>
                        <?php if(is_array($lawyer)): $i = 0; $__LIST__ = $lawyer;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>" <?php echo ($search['lawyer']==$vo['id'])?'selected':'';?>><?php echo ($vo["user_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <select name="price" id="price" class="form-control">
                        <option value="">全部金额</option>
                        <?php if(is_array($price)): $i = 0; $__LIST__ = $price;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>" <?php echo ($search['price']==$vo['id'])?'selected':'';?>><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </form>
        </div>
        <div class="ibox-content">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th width="100">所属律师</th>
                            <th width="100">问答标题</th>
                            <th width="100">金额</th>
                            <th width="100">内容</th>
                            <th width="100">创建时间</th>
                            <th width="100">审核状态</th>
                            <th width="100">状态</th>
                            <th width="100">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(is_array($list)): foreach($list as $key=>$vo): ?><tr>
                            <td><?php echo ($vo["user_name"]); ?></td>
                            <td><?php echo ($vo["title"]); ?></td>
                            <td><?php echo ($vo["money"]); ?></td>
                            <td>
                                <?php if($vo["type"] == 1): ?><a href="javascript:void(0)" address="<?php echo U('voice_list',array('id'=>$vo['id']));?>?id=<?php echo ($vo["id"]); ?>&type=1"  tip="<?php echo ($vo["user_name"]); ?>问答回复" onclick="winopen(this,'60%','50%');" class="ajaxsubmit">点击查看文本</a> 
                                <?php else: ?> 
                                    <a href="javascript:void(0)" address="<?php echo U('voice_list',array('id'=>$vo['id']));?>?id=<?php echo ($vo["id"]); ?>&type=2"  tip="<?php echo ($vo["user_name"]); ?>问答回复" onclick="winopen(this,'60%','50%');" class="ajaxsubmit">点击查看语音</a><?php endif; ?>
                                    
                            </td>
                            <td><?php echo ($vo["txt_create_time"]); ?></td>
                            <td>
                                <?php if($vo["check_status"] == 1): ?><a href="javascript:void(0)" address="<?php echo U('check_status');?>?id=<?php echo ($vo["id"]); ?>"  tip="<?php echo ($vo["user_name"]); ?>的认证状态" onclick="winopen(this,'60%','50%');" class="ajaxsubmit"><?php echo ($vo["txt_check_status"]); ?></a> 
                                <?php elseif($vo["check_status"] == 2): ?>
                                    <?php echo ($vo["txt_check_status"]); ?>
                                <?php else: ?>
                                    <?php echo ($vo["txt_check_status"]); endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo U('changeStatus',array('id'=>$vo['id'],'status'=>$vo['status']));?>" tip="请确定改变当前的状态吗?" class="ajaxsubmit ajaxdo"><?php echo ($vo["txt_status"]); ?></a>
                            </td>
                            <td>
                                <a href="<?php echo U('del',array('id'=>$vo['id']));?>" tip="请确定要删除此<?php echo ($keywords); ?>吗?" class="ajaxsubmit ajaxdo">删除</a>
                            </td>
                        </tr><?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-center">
                共计：<?php echo ($count); ?>条&nbsp;&nbsp;<?php echo ($page); ?>
            </div>
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
        
    <script type="text/javascript">
        $(document).ready(function () {          
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