/** 指纹 **/
(function(fp){	
	var obj=null;
	fp.init=function(){
		obj= new ActiveXObject("FPSOnline.FPSOnline1");		
		
	}
	//获取注册指纹
	fp.getRegPrint=function(callback){
		obj.sEnrollCount(3);
		obj.isSaveRegBmp(1);
		obj.SaveBmpAsFile ("C:\\regFinger.bmp","C:\\regFinger.bmp");
		if (obj.Register() ) {	
		    callback( obj.GetRegisterTemplate());
		}else{
			alert('未检测到指纹仪');
		}
	}
	fp.getVerifyPrint=function(){
		if(obj.Verify() ){				
			form1.S2.value=obj.GetVerifyTemplate();
			Procedur3();
		}else{
			alert('获取指纹失败');
		}
	},
	fp.verify=function(rg,vr){
		if (obj.VerifyTemplateFromstr(rg,vr) ) {
		    alert('验证成功');
		}else{
			alert('验证失败');
		}
	}
}(window.fingerPrint={}));