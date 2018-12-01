<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>律师后台登录</title>
        <meta name="keywords" content="">
        <meta name="description" content="">
        <link href="/Public/hplus/css/bootstrap.min.css?v=3.4.0" rel="stylesheet">
        <link href="/Public/hplus/css/font-awesome.min.css?v=4.3.0" rel="stylesheet">
        <link href="/Public/hplus/css/animate.min.css" rel="stylesheet">
        <link href="/Public/hplus/css/style.min.css?v=3.2.0" rel="stylesheet">
        <!-- 全局js -->
        <script src="/Public/hplus/js/jquery-2.1.1.min.js"></script>
        <script src="/Public/hplus/js/bootstrap.min.js?v=3.4.0"></script>
        <script src="/Public/hplus/js/layer/layer.min.js"></script>
        <script type="text/javascript" src="/Public/hplus/js/boot.js"></script>
        <script type="text/javascript">
            if (window.top !== window.self) {
                window.top.location = window.location;
            }
            var IEerror = false;
            $(document).ready(function () {
                $('#modal-form').modal('show');
                $('#modal-form').on('hide.bs.modal',function(){return false;});
                $("#login_sub").click(function () {
                    submitform();
                    return false;
                });
            });
            function submitform()
            {
                if($('input[name=adminname]').val()=='')
                {
                    showerror('请输入用户名');
                    $('input[name=adminname]').focus();
                    return;
                }
                if($('input[name=password]').val()=='')
                {
                    showerror('请输入密码');
                    $('input[name=password]').focus();
                    return;
                }
                if($('input[name=checkCode]').val()=='')
                {
                    showerror('请输入验证码');
                    $('input[name=checkCode]').focus();
                    return;
                }
                <?php if(($configexist) == "0"): ?>if (confirm('网站配置丢失,是否进行初始化?'))
                {
                    window.location.href = "/Admin/Base/DataBase/DBinit";
                    return false;
                }<?php endif; ?>
                var username = $('input[name="userCode"]').val();
                var password = $('input[name="password"]').val();
                var checkCode = $('input[name="checkCode"]').val();               
                function suc(data) {
                    if (data.code == 0)
                    {
                        showerror(data.msg);
                        refreshcode();
                    } else {
                        showsuccess(data.msg+',正在进入后台...');                        
                    }
                }
                function err(XMLHttpRequest, textStatus, errorThrown) {
                    showerror('访问服务器异常:' + XMLHttpRequest.status);
                }
                execute('/Admin/Base/Login/index.html', 1, $("#submitForm").serialize(), suc, err);
                //$("#submitForm").submit();
            }
            function showerror(msg)
            {
                $("#resultdiv").html('<div class="alert alert-danger alert-dismissable"><button id="login_result_close" aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><a id="login_result" class="alert-link">'+msg+'</a></div>');
                setTimeout(function () {
                    $("#login_result_close").click();
                }, 3000);
            }
            function showsuccess(msg)
            {
                $("#resultdiv").html('<div class="alert alert-success alert-dismissable"><button id="login_result_close" aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><a id="login_result" class="alert-link">'+msg+'</a></div>');
                setTimeout(function () {
                    window.location.href = "/Admin/Base/Index";
                }, 100);
            }
            function refreshcode()
            {
                $('#checkcodeimg').attr('src', '/Admin/Base/Login/checkcode.html?rnd=' + Math.random(1000));
                $('input[name=checkCode]').focus();
            }
        </script>
    </head>
    <body class="gray-bg along_login_bg">
        <div id="modal-form" class="modal fade" aria-hidden="true" data-backdrop="true">
        <div class="modal-dialog">
            <h1 style="text-align:center;color:#fff;margin-top: 30%">律师后台登录</h1>
            <div class="modal-content along_loginbox" style="margin-top: 15%">
                <div class="modal-body ">
                    <div class="row">
                        <div class="col-sm-6 b-r">
                            <form role="form" id="submitForm" action="/Admin/Base/App/login.html">
                                <div class="form-group">
                                    <label>用户名：</label>
                                    <input type="text" name="adminname" placeholder="请输入用户名" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>密码：</label>
                                    <input type="password" name="password" placeholder="请输入密码" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>验证码：</label>
                                    <input type="text" name="checkCode" placeholder="请输入验证码" class="form-control">
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-primary pull-right m-t-n-xs" id="login_sub" type="submit"><strong>登录</strong></button>
                                </div>
                            </form>
                        </div>
                        <div class="col-sm-6">
                            <p>以下是验证码图片</p>
                            <p class="text-center">
                                <img id="checkcodeimg" src="/Admin/Base/Login/checkcode.html" onclick="refreshcode()" title="点击刷新" />
                            </p>
                            <div id="resultdiv" class="ibox-content">
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </body>
</html>