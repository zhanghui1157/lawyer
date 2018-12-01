<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>主页</title>
<script src="__JS__/boot.js" type="text/javascript"></script>
<link href="__JS__/fullcalendar/fullcalendar.css" rel="stylesheet" type="text/css">
<script src="__JS__/fullcalendar/fullcalendar.min.js"></script>
<style>
  .ibox-title{border-bottom: 1px solid #e7e6e6;font-family: "微软雅黑";}
  .tongjilist{list-style: none; height: 180px;}
  .tongjilist li{width:15%; margin-right: 1%; float: left;font-family: "微软雅黑"; border: 1px solid #e7e6e6; border-radius: 5px; background: #fafafa; text-align: center; margin-bottom: 15px; padding-bottom: 15px; }
  .tongjilist li:nth-child(1) h1{color: #1ab394}
  .tongjilist li:nth-child(2) h1{color: #efab17}
  .tongjilist li:nth-child(3) h1{color: #44dad5}
  .tongjilist li:nth-child(4) h1{color: #f88e4e}
  .tongjilist li:nth-child(5) h1{color: #4dc11a}
  .tongjilist li:nth-child(6) h1{color: #ff2a00}
  .tongjilist li:nth-child(7) h1{color: #7bb2d8}
  .tongjilist li:nth-child(8) h1{color: #15a4df}
  .tongjilist li:nth-child(9) h1{color: #44dad5}
  .tongjilist li:nth-child(10) h1{color: #deb617}
  .tongjilist li:nth-child(11) h1{color: #3658d8}
  .tongjilist li:nth-child(12) h1{color: #4db420}
  .tongjilist li span{font-size: 14px; color: #333}
  .tongjilist li p{display: block;font-size: 10px; padding: 5px;color:#555; }
  
  .kuaijielist{list-style: none; font-family: "微软雅黑";height: auto}
  .kuaijielist  li{width:12%; margin-right: 1%; float: left;  border-radius: 5px;  text-align: center; margin-bottom: 15px; padding-bottom: 15px;}
  .kuaijielist  li span{display:block; margin-top: 8px;}
  .kuaijielist  li a{color:#333; font-size: 14px; text-align:center; text-decoration: none; display: block; padding: 10px;}
  .kuaijielist  li a:hover{border: 1px solid #e7e6e6; border-radius: 5px; background: #fafafa;}
  .kuaijielist  li img{width: 60%; max-width: 60px;}
</style>
</head>

<body>
<div class="mini-panel" title="header" style="width:100%;height:100%;"  showHeader="false"
    showToolbar="true"  showFooter="true">
    <!--toolbar-->
    <div property="toolbar" style="height:25px; line-height:25px; padding-left:10px">
       您好，欢迎登系统。今天是：{$month}
    </div>
    <!--footer-->
    <div property="footer"></div>
    <!--body-->
    <div class="ibox-content" style="padding: 10px;">
      <div class="ibox-title"><h5>数据汇总概况</h5></div>
      <ul class="tongjilist">
        <li><h1>{$data['article']}</h1><span>文章总数</span><p>最近时间{$num['article']}天前</p></li>
        <li><h1>{$data['member']}</h1><span>会员总数</span><p>最近时间 10 天前</p></li>
        <!-- <li><h1>{$data['member']}</h1><span>会员总数</span><p>最近时间{$num['member']}天前</p></li> -->
        <li><h1>{$data['result']}</h1><span>成果库</span><p>最近时间{$num['result']}天前</p></li>
        <li><h1>{$data['expert']}</h1><span>专家库</span><p>最近时间{$num['expert']}天前</p></li>
        <li><h1>{$data['need']}</h1><span>需求库</span><p>最近时间{$num['need']}天前</p></li>
        <li><h1>{$data['prodynamic']}</h1><span>项目动态</span><p>最近时间{$num['prodynamic']}天前</p></li>
        <li><h1>{$data['finance']}</h1><span>融资库</span><p><if condition="empty($num['finance'])">无更新<else/>最近时间{$num['finance']}天前</if></p></li>
        <li><h1>{$data['carboninfo']}</h1><span>碳市场</span><p>最近时间{$num['carboninfo']}天前</p></li>
        <li><h1>{$data['methods']}</h1><span>方法学</span><p>最近时间{$num['methods']}天前</p></li>
        <li><h1>{$data['anli']}</h1><span>绿色入住</span><p>最近时间{$num['anli']}天前</p></li>
        <li><h1>{$data['assess']}</h1><span>碳资产开发评估</span><p>最近时间{$num['assess']}天前</p></li>
        <li><h1>{$data['make']}</h1><span>绿色制造评估</span><p>最近时间{$num['make']}天前</p></li>

      </ul>
      <div class="ibox-title" style="clear: both;"><h5>快捷入口</h5></div>
      <ul class="kuaijielist">
        <li><a href="{:U('article/add')}"><img src="__PUBLIC__/Admin/images/indexico_03.png" ><span>发布文章</span></a></li>
        <li><a href="{:U('ads/index')}"><img src="__PUBLIC__/Admin/images/indexico_05.png" ><span>轮播管理</span></a></li>
        <li><a href="{:U('user/index')}"><img src="__PUBLIC__/Admin/images/indexico_07.png" ><span>会员列表</span></a></li>
        <li><a href="{:U('artCategory/index')}"><img src="__PUBLIC__/Admin/images/indexico_09.png" ><span>栏目管理</span></a></li>
        <li><a href="{:U('SysParam/index')}"><img src="__PUBLIC__/Admin/images/indexico_11.png" ><span>系统参数</span></a></li>
        <li><a href="{:U('FootInfo/index')}"><img src="__PUBLIC__/Admin/images/indexico_13.png" ><span>底部信息管理</span></a></li>


      </ul>
      <div class="ibox-title" style="clear: both;"><h5>网站访问量统计</h5></div>
      <div id="timemyChart2" style="height: 440px;"></div>
      <div style="height: 100px"></div>
    </div>
     
</div>

        
        
  <script type="text/javascript">  
		window.parent.hideTopMask(); 
	 	mini.parse();
	</script>
</body>
</html>
<script src="__PUBLIC__/Admin/js/echarts.js"></script>
<script src="__PUBLIC__/Admin/js/jquery-2.1.1.min.js"></script>
<script type="text/javascript">
$('a.winopen').click(function(){
    layer.open({
        type: 2,
        title: $(this).attr('tip'),
        shadeClose: true,
        shade: false,
        maxmin: true, //开启最大化最小化按钮
        area: ['100%', '100%'],
        content: $(this).attr('href')
    });
    return false;
});
</script>
<script>
var data1,data2;
$.ajax({
    url:"{:U('getNoticeData')}",
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
