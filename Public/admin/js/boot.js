var _osajaxfree = true;//ajax空闲
var _osloading=null;
function execute(action,method,data,suc,err){
    if(_osajaxfree){$.ajax({url:action,data:data,type:(method==0?'get':'post'),cache:false,dataType:'json',beforeSend:function(XMLHttpRequest){_osajaxfree=false;_osloading = parent.layer.load(1,{shade: [0.5,'#000']});},success:suc,error:err,complete:function(XMLHttpRequest,textStatus){_osajaxfree=true;parent.layer.close(_osloading);}});}else{parent.layer.msg('==我正忙活呢', {icon:2,shadeClose: true,shade: 0.5,});}
}
$(document).ready(function(){
    $('.viewimages').attr('title','点我看看');
    $('.viewimages').click(function(){
        if($(this).val()!='')
        {
            $('.replacenull').parent().attr('href',$(this).val());
            $('.replacenull').parent().click();
        }
    });
});
