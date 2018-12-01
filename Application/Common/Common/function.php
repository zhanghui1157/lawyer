<?php

/**
 * POST data
 */
//json返回

/**
 * 创建订单号 15位定长
 * @return string
 */
function new_order_id(){
	return NOW_TIME.rand(10000,99999);
}


function implodeUrlParm($parm){
	$p=array();
	foreach($parm as $k=>$v){
		$p[]="{$k}={$v}";
	}
	$str= implode('&',$p);
	return $str;
}

function saveExcel($data,$name='',$titleRow=null){
	if(empty($name)){
		$name=date('Ymd');
	}
    error_reporting(E_ALL);
    date_default_timezone_set('Europe/London');
    Vendor('PHPExcel');
    //import("Org.Util.PHPExcel");   
    //import("Org.Util.PHPExcel.IOFactory");
    $objPHPExcel = new \PHPExcel();
    /*以下是一些设置 ，什么作者  标题啊之类的*/
    $objPHPExcel->getProperties()->setCreator("huike")
                           ->setLastModifiedBy("huike")
                           ->setTitle('excel export')
                           ->setSubject('excel export')
                           ->setDescription('excel export')
                           ->setKeywords("excel")
                          ->setCategory("result file");
     /*以下就是对处理Excel里的数据*/
    $sheet=$objPHPExcel->setActiveSheetIndex(0);
    //列名
    $colNames=array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
    $startRow=1;
    //第一行设置列名，为空则跳过
    if(is_array($titleRow)){
    	foreach($titleRow as $k=>$v){
    		$key=$colNames[$k].$startRow;
    		$sheet->setCellValue($key, $v);
		}
    	$startRow=2;    	
    }
    
    
    foreach($data as $k => $row){
    	//循环行
    	$rowNum=$k+$startRow;  
    	$colIndex=0;//起始列
    	foreach($row as $field){
    		//循环列
         	//$objPHPExcel->setActiveSheetIndex(0)
            $colName=$colNames[$colIndex].$rowNum;               
        	$sheet->setCellValue($colName,$field);
        	$colIndex++;//下一格
        }
    }
    $objPHPExcel->getActiveSheet()->setTitle('sheet1');
    $objPHPExcel->setActiveSheetIndex(0);
     header('Content-Type: application/vnd.ms-excel');
     header('Content-Disposition: attachment;filename="'.$name.'.xls"');
     header('Cache-Control: max-age=0');
     $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
     $objWriter->save('php://output');
     exit;
}


//以下是微信接口相关操作*****************************
/*
 * 获取token
 */
function WX_gettoken()
{
	if(!S('wx_token'))
	{
		$token = \Org\Wechat\WXCommon::getToken();
		if($token)
		{
			S('wx_token',$token,$token['expires_in']-60);
		}
		else
		{
			throw_exception('获取token失败');
			return false;
		}
	}
	return getValueByKeys(S('wx_token'),'access_token');
}
/*
 * 过滤返回结果
 */
function WX_formatresult($json)
{
	if (array_key_exists('errcode', $json)) {
		throw_exception('获取API失败:'.  getErrorCode($json['errcode']));
		exit;
	} else {
		return $json;
	}
}
/*
 * 获取用户信息
 */
function WX_getuserinfo($access_token,$openid)
{
	$url = sprintf("https://api.weixin.qq.com/sns/userinfo?access_token=%s&openid=%s&lang=%s",$access_token,$openid,'zh_CN');
	$content = file_get_contents($url);
	return json_decode($content, true);
	//return WX_formatresult(json_decode($content, true));
}
/*
 * 查询微信返回码对应文本
 */
function getErrorCode($code)
{
	$codearr = array('1'=>'系统繁忙，此时请开发者稍候再试',
			'0'=>'请求成功',
			'40001'=>'获取access_token时AppSecret错误，或者access_token无效。请开发者认真比对AppSecret的正确性，或查看是否正在为恰当的公众号调用接口',
			'40002'=>'不合法的凭证类型',
			'40003'=>'不合法的OpenID，请开发者确认OpenID（该用户）是否已关注公众号，或是否是其他公众号的OpenID',
			'40004'=>'不合法的媒体文件类型',
			'40005'=>'不合法的文件类型',
			'40006'=>'不合法的文件大小',
			'40007'=>'不合法的媒体文件id',
			'40008'=>'不合法的消息类型',
			'40009'=>'不合法的图片文件大小',
			'40010'=>'不合法的语音文件大小',
			'40011'=>'不合法的视频文件大小',
			'40012'=>'不合法的缩略图文件大小',
			'40013'=>'不合法的AppID，请开发者检查AppID的正确性，避免异常字符，注意大小写',
			'40014'=>'不合法的access_token，请开发者认真比对access_token的有效性（如是否过期），或查看是否正在为恰当的公众号调用接口',
			'40015'=>'不合法的菜单类型',
			'40016'=>'不合法的按钮个数',
			'40017'=>'不合法的按钮个数',
			'40018'=>'不合法的按钮名字长度',
			'40019'=>'不合法的按钮KEY长度',
			'40020'=>'不合法的按钮URL长度',
			'40021'=>'不合法的菜单版本号',
			'40022'=>'不合法的子菜单级数',
			'40023'=>'不合法的子菜单按钮个数',
			'40024'=>'不合法的子菜单按钮类型',
			'40025'=>'不合法的子菜单按钮名字长度',
			'40026'=>'不合法的子菜单按钮KEY长度',
			'40027'=>'不合法的子菜单按钮URL长度',
			'40028'=>'不合法的自定义菜单使用用户',
			'40029'=>'不合法的oauth_code',
			'40030'=>'不合法的refresh_token',
			'40031'=>'不合法的openid列表',
			'40032'=>'不合法的openid列表长度',
			'40033'=>'不合法的请求字符，不能包含\uxxxx格式的字符',
			'40035'=>'不合法的参数',
			'40038'=>'不合法的请求格式',
			'40039'=>'不合法的URL长度',
			'40050'=>'不合法的分组id',
			'40051'=>'分组名字不合法',
			'40117'=>'分组名字不合法',
			'40118'=>'media_id大小不合法',
			'40119'=>'button类型错误',
			'40120'=>'button类型错误',
			'40121'=>'不合法的media_id类型',
			'40132'=>'微信号不合法',
			'40137'=>'不支持的图片格式',
			'41001'=>'缺少access_token参数',
			'41002'=>'缺少appid参数',
			'41003'=>'缺少refresh_token参数',
			'41004'=>'缺少secret参数',
			'41005'=>'缺少多媒体文件数据',
			'41006'=>'缺少media_id参数',
			'41007'=>'缺少子菜单数据',
			'41008'=>'缺少oauth code',
			'41009'=>'缺少openid',
			'42001'=>'access_token超时，请检查access_token的有效期，请参考基础支持-获取access_token中，对access_token的详细机制说明',
			'42002'=>'refresh_token超时',
			'42003'=>'oauth_code超时',
			'43001'=>'需要GET请求',
			'43002'=>'需要POST请求',
			'43003'=>'需要HTTPS请求',
			'43004'=>'需要接收者关注',
			'43005'=>'需要好友关系',
			'44001'=>'多媒体文件为空',
			'44002'=>'POST的数据包为空',
			'44003'=>'图文消息内容为空',
			'44004'=>'文本消息内容为空',
			'45001'=>'多媒体文件大小超过限制',
			'45002'=>'消息内容超过限制',
			'45003'=>'标题字段超过限制',
			'45004'=>'描述字段超过限制',
			'45005'=>'链接字段超过限制',
			'45006'=>'图片链接字段超过限制',
			'45007'=>'语音播放时间超过限制',
			'45008'=>'图文消息超过限制',
			'45009'=>'接口调用超过限制',
			'45010'=>'创建菜单个数超过限制',
			'45015'=>'回复时间超过限制',
			'45016'=>'系统分组，不允许修改',
			'45017'=>'分组名字过长',
			'45018'=>'分组数量超过上限',
			'46001'=>'不存在媒体数据',
			'46002'=>'不存在的菜单版本',
			'46003'=>'不存在的菜单数据',
			'46004'=>'不存在的用户',
			'47001'=>'解析JSON/XML内容错误',
			'48001'=>'api功能未授权，请确认公众号已获得该接口，可以在公众平台官网-开发者中心页中查看接口权限',
			'50001'=>'用户未授权该api',
			'50002'=>'用户受限，可能是违规后接口被封禁',
			'61451'=>'参数错误(invalid parameter)',
			'61452'=>'无效客服账号(invalid kf_account)',
			'61453'=>'客服帐号已存在(kf_account exsited)',
			'61454'=>'客服帐号名长度超过限制(仅允许10个英文字符，不包括@及@后的公众号的微信号)(invalid kf_acount length)',
			'61455'=>'客服帐号名包含非法字符(仅允许英文+数字)(illegal character in kf_account)',
			'61456'=>'客服帐号个数超过限制(10个客服账号)(kf_account count exceeded)',
			'61457'=>'无效头像文件类型(invalid file type)',
			'61450'=>'系统错误(system error)',
			'61500'=>'日期格式错误',
			'61501'=>'日期范围错误',
			'9001001'=>'POST数据参数不合法',
			'9001002'=>'远端服务不可用',
			'9001003'=>'Ticket不合法',
			'9001004'=>'获取摇周边用户信息失败',
			'9001005'=>'获取商户信息失败',
			'9001006'=>'获取OpenID失败',
			'9001007'=>'上传文件缺失',
			'9001008'=>'上传素材的文件类型不合法',
			'9001009'=>'上传素材的文件尺寸不合法',
			'9001010'=>'上传失败',
			'9001020'=>'帐号不合法',
			'9001021'=>'已有设备激活率低于50%，不能新增设备',
			'9001022'=>'设备申请数不合法，必须为大于0的数字',
			'9001023'=>'已存在审核中的设备ID申请',
			'9001024'=>'一次查询设备ID数量不能超过50',
			'9001025'=>'设备ID不合法',
			'9001026'=>'页面ID不合法',
			'9001027'=>'页面参数不合法',
			'9001028'=>'一次删除页面ID数量不能超过10',
			'9001029'=>'页面已应用在设备中，请先解除应用关系再删除',
			'9001030'=>'一次查询页面ID数量不能超过50',
			'9001031'=>'时间区间不合法',
			'9001032'=>'保存设备与页面的绑定关系参数错误',
			'9001033'=>'门店ID不合法',
			'9001034'=>'设备备注信息过长',
			'9001035'=>'设备申请参数不合法',
			'9001036'=>'查询起始值begin不合法');
	return $codearr[$code];
}
/*
 * 检查用户权限是否可以访问相应资源（$_action=控制器名称,$_method=方法名称,$myauth=资源集合）
 */

function checkauth_str($_action, $_method, $myauth) {
	if (strtolower($_action) == 'index') {
		return true;
	}
	foreach ($myauth as $rs) {
		if (strtolower($_action) == strtolower($rs['m'].'/'.$rs['action'])) {
			if ($rs['method'] == '*')
			{
				return true;
			}
			foreach(explode(',',$rs['method']) as $m2)
				if(strtolower($m2) == strtolower($_method))
				{
					return true;
				}
		}
	}
	return false;
}
/*
 * 生成guid字符串
 */
function getGUID(){
	if (function_exists('com_create_guid')){
		return com_create_guid();
	}else{
		mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
		$charid = strtoupper(md5(uniqid(rand(), true)));
		$hyphen = chr(45);// "-"
		$uuid = chr(123)// "{"
		.substr($charid, 0, 8).$hyphen
		.substr($charid, 8, 4).$hyphen
		.substr($charid,12, 4).$hyphen
		.substr($charid,16, 4).$hyphen
		.substr($charid,20,12)
		.chr(125);// "}"
		return $uuid;
	}
}
/*
 * 将thinkphp getField(id,true)查询后的数组转化成形如1,2,3的字符串
 */
function createsqlarrstr($arr)
{
	return implode(',',$arr);
}
/*
 * 拼接sql形如1,2,3的两个字符串
 */
function appendsqlarrstr($str1,$str2)
{
	$str1 = trim($str1);
	$str2 = trim($str2);
	if($str1=='')
	{
		return $str2;
	}
	else
	{
		if($str2=='')
		{
			return $str1;
		}
		else
		{
			return $str1.','.$str2;
		}
	}
}
/*
 * 验证码验证
 */
function check_verify($code, $id){
	$verify = new \Think\Verify();
	return $verify->check($code,$id);
}


/*
 * **聚合数据（JUHE.CN）数据接口调用通用DEMO SDK
 * **DATE:2014-04-14
 * 手机号码
 * 模板ID
 * 需替换的标签值
 */

function sendSMS($mobile, $tpl_id, $tpl_value) {  //发送手机短信
	$config = M('config')->field('tel_key,tel_url')->where()->find();
	header('content-type:text/html;charset=utf-8');
	$appkey = $config['tel_key']; #通过聚合申请到数据的appkey
	$url = $config['tel_url']; #请求的数据接口URL
	$data['mobile'] = $mobile;  //接收短信的手机号码
	$data['tpl_id'] = $tpl_id;  //短信模板ID，请参考个人中心短信模板设置
	$data['tpl_value'] = urlencode($tpl_value);  //是变量名和变量值对
	$params = 'key=' . $appkey . '&mobile=' . $data['mobile'] . '&tpl_id=' . $data['tpl_id'] . '&tpl_value=' . $data['tpl_value'];
	$content = juhecurl($url, $params, 0);
	if ($content) {
		$result = json_decode($content, true);
		#错误码判断
		$error_code = $result['error_code'];
		if ($error_code == 0) {
			#根据所需读取相应数据
			return '1';
			
		} else {
			return $error_code . ':' . $result['reason'];
		}
	}
}
/*
 * **请求接口，返回JSON数据
 * **@url:接口地址
 * **@params:传递的参数
 * **@ispost:是否以POST提交，默认GET
 */

function juhecurl($url, $params = false, $ispost = 0) {
	$httpInfo = array();
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22');
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	if ($ispost) {
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_URL, $url);
	} else {
		if ($params) {
			curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
		} else {
			curl_setopt($ch, CURLOPT_URL, $url);
		}
	}
	$response = curl_exec($ch);
	if ($response === FALSE) {
		#echo "cURL Error: " . curl_error($ch);
		return false;
	}
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$httpInfo = array_merge($httpInfo, curl_getinfo($ch));
	curl_close($ch);
	return $response;
}
/*
 * 模拟提交数据函数
 */
function curlPost($url,$data){
	$curl = curl_init(); // 启动一个CURL会话
	curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
	// curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
	// curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
	curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
	//curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
	curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
	curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data)); // Post提交的数据包
	//curl_setopt($curl, CURLOPT_COOKIEFILE, $GLOBALS['cookie_file']); // 读取上面所储存的Cookie信息
	curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
	curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
	$tmpInfo = curl_exec($curl); // 执行操作
	if (curl_errno($curl)) {
		echo 'Errno'.curl_error($curl);
	}
	curl_close($curl); // 关键CURL会话
	// if (preg_match("!Location: (.*)!", $tmpInfo, $matches)) {
	//     echo "$test_name: redirects to $matches[1]\n";
	// } else {
	//     echo "$test_name: no redirection\n";
	// }
	return $tmpInfo; // 返回数据
}
/*
 * 判断访问终端设备类型是否为移动设备
 */
function yesOrNoTel() {
	$useragent = $_SERVER['HTTP_USER_AGENT'];
	if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
	{return true;} else {return false;};
}
//获取数组中指定键的值
function getValueByKeys($data, $key) {
	return $data[$key];
}
/*
 * 给列表中的栏目增加带树状标识的名称
 */
function create_cat_name2($arr)
{
	foreach ($arr as &$r)
	{
		$r['name2'] = str_repeat('—',$r['level']-1).$r['name'];
	}
	return $arr;
}
/*
 * 将人民币分转换成元
 */
function convertprice($price)
{
	return $price/100;
}
/*
 * 更新留言回复数
 */
function update_home_messageboard($mid)
{
	$replycount = M('MessageboardReply')->where(array('messageboard_id'=>$mid))->count();
	$managerreplycount = M('MessageboardReply')->where(array('messageboard_id'=>$mid,'ismanager'=>1))->count();
	M('Messageboard')->where(array('id'=>$mid))->save(array('replycount'=>$replycount,'adminreplycount'=>$managerreplycount));
}
function field_count($table,$where)
{
	return M($table)->where($where)->count();
}
function field_sum($table,$field,$where)
{
	return M($table)->where($where)->count($field);
}
/*
 * 计算指定时间是否为最新
 */
function get_newstatus($time,$hour)
{
	return time()-$time < $hour*60*60;
}
function updatecache()
{
	$config = M('Config')->where(array('id'=>'default'))->find();
	if($config)
	{
		$config['area'] = M('area')->where()->select();//区域信息
		$config['news'] = M('news')->where()->select();//新闻、学习、模范、活动信息
		$config['user'] = M('user')->where()->select();//会员信息
		$catlist = M('cat')->where()->order('path asc')->select();//新闻分类信息
		$config['cat'] = create_cat_name2($catlist);
		F('site/config',$config);
	}
	else
	{
		echo 'sitecofnig not find!';
		exit;
	}
}
//获取文件缩略图名称
function getImagesThumb($bigimgurl) {
	$pinfo = pathinfo($bigimgurl);
	return substr($bigimgurl, 0, strpos($bigimgurl, '.' . $pinfo['extension'], 0)) . '_S.' . $pinfo['extension'];
}
/**
 *求两个已知经纬度之间的距离,单位为米
 *@param lng1,lng2 经度
 *@param lat1,lat2 纬度
 *@return float 距离，单位米
 **/
function getdistance($lng1,$lat1,$lng2,$lat2){
	//将角度转为狐度
	$radLat1=deg2rad($lat1);//deg2rad()函数将角度转换为弧度
	$radLat2=deg2rad($lat2);
	$radLng1=deg2rad($lng1);
	$radLng2=deg2rad($lng2);
	$a=$radLat1-$radLat2;
	$b=$radLng1-$radLng2;
	$s=2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)))*6378.137*1000;
	return $s;
}

function json($code,$msg="",$data=array()){  
    $result=array(  
      'code'=>$code,  
      'msg'=>$msg, 
      'data'=>$data   
    );  
    //输出json  
    echo json_encode($result);  
    exit;  
}

function JReturn($message='成功',$statusCode=1,$data = array())
{
	$rs =array('message'=>$message,'statusCode'=>$statusCode);
	$rs =json_encode(array_merge($rs,$data),JSON_UNESCAPED_UNICODE);
	exit($rs);
	
}
function pass_time($id){
	
	$member= M('police')->where(array('police_id'=>$id))->find();
	if(empty($member)) JReturn('账号不存在',0);
	$res= M('police_log')->where(array('uid'=>$id))->order('login_time desc')->limit(1)->find();
	$timestamp=$res['timestamp'];
	$time=time();
	if($time > $timestamp){
		
		JReturn('登录超时，请重新登录',-1);
		unset($id);
	}
}
//数据字典根据id查找name
function find_zidian($id){
	
	$result= M('dictionary')->where(array('id'=>$id))->find();
	if(!empty($result['level']==2)){
		$result_top= M('dictionary')->where(array('id'=>$result['pid']))->find();
		if($result_top['level']==1){
			return $result_top['name']." / ".$result['name'];
		}
	}else{
		return $result['name'];
	}
}
//身份证15位18位校验
function is_idCard($idcard){
	$length=strlen($idcard);
	if($length==18){
		return true;
	}
	return false;
}
//获取数据字典列表
function zidian($type){
	$result= M('dictionary')->where(array('type'=>$type,'level'=>1))->order('id asc,listorder asc')->select();
	if(!empty($result)){
		foreach($result as $k=>$v){
			$result[$k]['two_level']=M('dictionary')->where(array('type'=>$type,'pid'=>$v['id']))->order('id desc,listorder asc')->select();
		}
	}
	
	return $result;
}
//图片上传
function upload_picture(){
	$upload = new \Think\Upload();// 实例化上传类
	$upload->maxSize   =     3145728 ;// 设置附件上传大小
	$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
	$upload->rootPath  =      '.'; // 设置附件上传根目录
	$upload->savePath  =      '/Public/UploadPic/'; // 设置附件上传（子）目录
	$info   = $upload->upload();
	return $info;
}

function upload_error(){
	$upload = new \Think\Upload();
	$err=$upload->getError();
	return $err;
}



function my_checkdir($dir){
	if(is_dir($dir)){
		return true;
	}
	
	if(mkdir($dir, 0777, true)){
		return true;
	} else {
		return  "目录 {$savepath} 创建失败！";
	}
}

function cut_str($sourcestr,$cutlength)  
{  
   $returnstr='';  
   $i=0;  
   $n=0;  
   $str_length=strlen($sourcestr);//字符串的字节数  
   while (($n<$cutlength) and ($i<=$str_length))  
   {  
      $temp_str=substr($sourcestr,$i,1);  
      $ascnum=Ord($temp_str);//得到字符串中第$i位字符的ascii码  
      if ($ascnum>=224)    //如果ASCII位高与224，  
      {  
		 $returnstr=$returnstr.substr($sourcestr,$i,3); //根据UTF-8编码规范，将3个连续的字符计为单个字符          
         $i=$i+3;            //实际Byte计为3  
         $n++;            //字串长度计1  
      }  
      elseif ($ascnum>=192) //如果ASCII位高与192，  
      {  
         $returnstr=$returnstr.substr($sourcestr,$i,2); //根据UTF-8编码规范，将2个连续的字符计为单个字符  
         $i=$i+2;            //实际Byte计为2  
         $n++;            //字串长度计1  
      }  
      elseif ($ascnum>=65 && $ascnum<=90) //如果是大写字母，  
      {  
         $returnstr=$returnstr.substr($sourcestr,$i,1);  
         $i=$i+1;            //实际的Byte数仍计1个  
         $n++;            //但考虑整体美观，大写字母计成一个高位字符  
      }  
      else                //其他情况下，包括小写字母和半角标点符号，  
      {  
         $returnstr=$returnstr.substr($sourcestr,$i,1);  
         $i=$i+1;            //实际的Byte数计1个  
         $n=$n+0.5;        //小写字母和半角标点等与半个高位字符宽...  
      }  
   }  
         if ($str_length>$i){  
          $returnstr = $returnstr . "...";//超过长度时在尾处加上省略号  
      }  
    return $returnstr;  
} 

	
