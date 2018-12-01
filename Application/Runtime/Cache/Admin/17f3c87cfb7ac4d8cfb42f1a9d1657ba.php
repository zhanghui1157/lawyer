<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>无标题文档</title>
</head>
<style>
    .tongjilist{list-style: none; font-family: "微软雅黑";height: auto}
    .tongjilist li{width:15%; margin-right: 1%; float: left; border: 1px solid #e7e6e6; border-radius: 5px; background: #fafafa; text-align: center; margin-bottom: 15px; padding-bottom: 15px;}
    .tongjilist li:nth-child(1) h1{color: #1ab394}
    .tongjilist li:nth-child(2) h1{color: #efab17}
    .tongjilist li:nth-child(3) h1{color: #44dad5}
    .tongjilist li:nth-child(4) h1{color: #f88e4e}
    .tongjilist li:nth-child(5) h1{color: #4dc11a}
    .tongjilist li:nth-child(6) h1{color: #ff2a00}
    .tongjilist li span{font-size: 14px; color: #333}
    
    .kuaijielist{list-style: none; font-family: "微软雅黑";height: auto}
    .kuaijielist  li{width:12%; margin-right: 1%; float: left;  border-radius: 5px;  text-align: center; margin-bottom: 15px; padding-bottom: 15px;}
    .kuaijielist  li span{display:block; margin-top: 8px;}
    .kuaijielist  li a{color:#333; font-size: 14px; text-align:center; text-decoration: none; display: block; padding: 10px;}
    .kuaijielist  li a:hover{border: 1px solid #e7e6e6; border-radius: 5px; background: #fafafa;}
    .kuaijielist  li img{width: 60%; max-width: 60px;}
    .ibox-title{border-bottom: 1px solid #e7e6e6;font-family: "微软雅黑";}
    .tongjilist{list-style: none; height: 180px;}
    
</style>
<body>
    <div class="ibox-content" style="padding: 10px;">
        <div class="ibox-title"><h5>系统概述统计</h5></div>
        <ul class="tongjilist">
            <a href="###"><li><h1><?php echo ($data["member_count"]); ?></h1><span>会员数量</span></li></a>
            <a href="###"><li><h1><?php echo ($data["lawyer_count"]); ?></h1><span>认证律师数量</span></li></a>
            <a href="###"><li><h1><?php echo ($data["answer_count"]); ?></h1><span>问答数量</span></li></a>
            <a href="###"><li><h1><?php echo ($data["order_count"]); ?></h1><span>订单数量</span></li></a>
            <a href="###"><li><h1><?php echo ($data["money_count"]); ?></h1><span>资金流动数量</span></li></a>
        </ul>
        <div class="ibox-title" style="clear: both"><h5>快捷入口</h5></div>
        <ul class="kuaijielist">
            <li><a class='winopen' href="<?php echo U('Admin/Member/Member/lists');?>"><img src="/Public/hplus/img/indexico_05.png" ><span>会员列表</span></a></li>
            <li><a class='winopen' href="<?php echo U('Admin/lawyer/lawyer/lists');?>"><img src="/Public/hplus/img/indexico_11.png" ><span>律师管理</span></a></li>
            <li><a class='winopen' href="<?php echo U('admin/answer/answer/lists');?>"><img src="/Public/hplus/img/indexico_03.png" ><span>问答管理</span></a></li>
            
            <li><a class='winopen' href="<?php echo U('Admin/Order/order/lists');?>"><img src="/Public/hplus/img/indexico_09.png" ><span>订单管理</span></a></li>
            
           <!--  <li><a class='winopen' href="<?php echo U('Admin/Order/money/lists');?>"><img src="/Public/hplus/img/indexico_13.png" ><span>资金管理</span></a></li> -->


        </ul>
        <div class="ibox-title" style="clear: both"><h5>小程序访问统计</h5></div>
        <div id="timemyChart2" style="height: 340px;"></div>
        
    </div>
</body>
</html>
<script src="/Public/hplus/js/echarts.js"></script>
<script src="/Public/hplus/js/jquery-2.1.1.min.js"></script>
<script src="/Public/hplus/js/layer/layer.min.js?v=<?php echo C('SYSTEM_VER');?>"></script>
<script type="text/javascript">
$('a.winopen').click(function(){
    layer.open({
        type: 2,
        title: $(this).attr('tip'),
        shadeClose: true,
        shade: false,
        maxmin: true, //开启最大化最小化按钮
        area: ['80%', '80%'],
        content: $(this).attr('href')
    });
    return false;
});
</script>
<script>
var data1,data2;
$.ajax({
    url:"<?php echo U('getNoticeData');?>",
    async:false,
    success:function(e){
        data1=e.data1;
        data2=e.data2;
    }
});

var  timemyChart2 = echarts.init(document.getElementById('timemyChart2'),'dark');
  
 timeoption2 = {
    tooltip : {
        trigger: 'axis'
    },
    legend: {
        data:['访问量']
    },
    toolbox: {
        show : true,
        feature : {
            mark : {show: true},
            dataView : {show: true, readOnly: false},
            magicType : {show: true, type: ['line', 'bar', 'stack', 'tiled']},
            restore : {show: true},
            saveAsImage : {show: true}
        }
    },
    calculable : true,
    xAxis : [
        {
            type : 'category',
            boundaryGap : false,
            data : data1
        }
    ],
    yAxis : [
        {
            type : 'value'
        }
    ],
    series : [
        {
            name:'访问量',
            type:'line',
            stack: '总量',
            data:data2
        },
       
    ]
};


timemyChart2.setOption(timeoption2);
</script>