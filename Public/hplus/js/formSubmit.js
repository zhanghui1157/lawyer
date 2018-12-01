$(document).ready(function () {         
	$(".i-checks").iCheck({checkboxClass:"icheckbox_square-green",radioClass:"iradio_square-green",});
	$("#cancelbutton").click(function () {
	    parent.window.location.reload();
	});
	$("#submitbutton").click(function () {
	    if (validateForm(validate)) {
	        function suc(data) {
	            if (data.code == 1)
	            {
	                parent.layer.msg(data.msg, {icon: 1, shadeClose: true, shade: 0.5, end: function () {
	                        $("#cancelbutton").click();
	                    }});
	            } else {
	                parent.layer.msg(data.msg, {icon: 2, shadeClose: true, shade: 0.5, });
	            }
	        }
	        function err() {
	            parent.layer.msg('请求服务器失败', {icon: 2, shadeClose: true, shade: 0.5, });
	        }
	        execute(subAjaxUrl, 1, $('#submitForm').serialize(), suc, err);
	    }
	});
});
	
/**
 * 表单验证
 * @param  {[type]} id  div的class或id
 * @param  {[type]} des 验证返回的字符
 * @return {[type]}     [description]
 */
function validateForm(e) {
	for(var i=0;i<e.length;i++){
		if ($(e[i][0]).val() == ''){
	        parent.layer.msg(e[i][1], {icon: 2, shadeClose: true, shade: 0.5, });
	        $(e[i][0]).focus();
	        return false;
    	}
	}
    
    return true;
}