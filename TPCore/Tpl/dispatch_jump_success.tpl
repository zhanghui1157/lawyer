<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8"></meta>
        <meta name="renderer" content="webkit"></meta>
        <title>成功提示</title>
        <link href="/Public/admin/css/bootstrap.min.css?v=3.4.0" rel="stylesheet">
        <link href="/Public/admin/css/font-awesome.min.css?v=4.3.0" rel="stylesheet">
        <link href="/Public/admin/css/animate.min.css" rel="stylesheet">
        <link href="/Public/admin/css/style.min.css?v=3.2.0" rel="stylesheet">
        <!-- 全局js -->
        <script type="text/javascript" src="/Public/admin/js/jquery-2.1.1.min.js"></script>
        <script type="text/javascript" src="/Public/admin/js/bootstrap.min.js"></script>
        <script type="text/javascript">
            var _interval=null,_href=null;
            _interval = setInterval(function(){
                var time = parseInt($('#wait').text());
                if(time <= 0) {
                        location.href = href;
                        clearInterval(_interval);
                };
                $('#wait').text(time-1);
            }, 1000);
            $(document).ready(function(){
                _href = $('#href').attr('href');
                $('#ModalMsg').show();
                $('#gotolink').click(function(){
                    window.location.href=_href;
                });
                $('#tryit').click(function(){
                    window.location.reload();
                });
            });
        </script>
    </head>
    <body class="gray-bg"> 
        <div class="modal inmodal" id="ModalMsg" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content animated">
                    <div class="modal-header">                        
                        <h4 class="modal-title"> 
                            <div class="alert alert-success">成功提示</div>
                        </h4>
                        <small class="font-bold">
                            <?php 
                            echo($message);
                            ?>
                        </small>
                    </div>
                    <div class="modal-body">
                        页面将于<b id="wait"><?php echo($waitSecond); ?></b>秒后自动 <a id="href" href="<?php echo($jumpUrl); ?>">跳转</a>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-dismiss="modal" id='gotolink'>返回目标页面</button>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>