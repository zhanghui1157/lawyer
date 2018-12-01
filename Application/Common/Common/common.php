<?php


//操作日志
//$type 1关键操作  2错误操作 
function actlogs($actcode,$msg,$uid){
	$log=M('sys_log');
	$data=array();
	$data['typecode']=$actcode;
	$data['title']=$msg;
	$data['userid']=$uid;
	$data['addtime']=date('Y-m-d H:i:s');
	$log->add($data);
}


function sendNotice($userid,$content,$type='sys'){
	$data=array(
		'title'=>$content,
		'user_id'=>$userid,
		'typecode'=>$type,
		'is_read'=>0,
		'addtime'=>time()
	);
	$r=M('sys_msg')->add($data);
	return $r;
}
function sendWeiNotice($openid,$content,$msgurl,$type){
	
	$token=getAccessToken();
	if(!empty($token)){
		$templates=array(
			'order_pay'=>'gMLs82Lmz-P8yz6fzZvAEXgoyQZVkbPDOAed4cN6evs',//订单支付成功
			'order_finish'=>'99wsgTEhpWl6dsfq2tWcmp5nxBcWROmbX9QiYKTUG7E',//订单完成
			'order_fahuo'=>'Edaz0WtLSOQ7jUB3F6kZQqBNmhXPrmgkXkTJP5t_1hE',//商品发货
			'order_return_succ'=>'Olxm-BpE3az3YmkvSd9BUsmRLuX9uDZ339k2eFrMgvM',//退货成功
			'order_return_fail'=>'XPbIGMMsF8C2_YQ_QfV-RWd0BO8Cef0YrwkwXIqMTs8',//退货申请失败
			'order_close'=>'AXIHMayx8i58GVy5HLZknXO0JKRiFy5PObz2jN35NSw',//订单取消
			'shop_fahuo'=>'EdW6Wlc3UgaGL0Ao86xyosNMmoBb7zwr6P--OtaSTCQ',//提醒商家发货
			'shop_open'=>'ph2wE2SIyVqViab4-ifhVbspa4qwtLUECPQh9qmA-zg',//开店成功
			'order_pei'=>'gMLs82Lmz-P8yz6fzZvAEXgoyQZVkbPDOAed4cN6evs'//送货通知
		);
		$templateId=$templates[$type];
		if(empty($templateId)){
			return false;	
		}
		$url='https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$token;
		$data=array(
			'touser'=>$openid,
			'template_id'=>$templateId,
			'url'=>$msgurl,
			'topcolor'=>"#FF0000",
			'data'=>$content
		);
		$res = json_decode(curlGet($url,json_encode($data)));		
		if($res->errcode=='0'){
			return true;	
		}else{
			F('wxmsg2/'.date('YmdHis'),$res);
			return false;	
		}
	}else{
		return false;	
	}
}
function getOpenIdByUser($id){
	$openid=D('User')->where("user_id='$id'")->getField('openid');
	return $openid;
}
//解析miniui grid列表表单,参数传递$_POST['data']
//返回数组$acts['add'],$acts['modify'],$acts['remove']
function exMiniGridForm($json){
	$data=json_decode($json,true);
	if(is_array($data) && count($data)>0){
		$acts=array('add'=>array(),'modify'=>array(),'remove'=>array());
		foreach($data as $row){
			$rownew=rowFilter($row);
			if($row['_state']=='added'){				
				$acts['add'][]=$rownew;
			}else if($row['_state']=='modified'){
				$acts['modify'][]=$rownew;
			}else if($row['_state']=='removed'){
				$acts['remove'][]=$rownew;
			}
		}
		return $acts;
	}else
		return array();
}

//解析json表单并过滤,参数传递$_POST['data']
//返回数组
function exJsonForm($json,$exField=array()){
	$data=json_decode($json,true);
	return rowFilter($data,$exField);
}

function exJsonArr($json){
	$data=json_decode($json,true);
	if(is_array($data) && count($data)>0){
		foreach($data as $k=>$row){
			$data[$k]=rowFilter($row);
		}
		return $data;
	}else
		return array();
}

//过滤一组输入
function rowFilter($data,$exField=array()){
	foreach($data as $k=>$val){
		if(is_array($exField) && in_array($k,$exField)){
			continue;	
		}
		$data[$k]=IFilter($val);
	}
	return $data;
}

//判断字符串长度
//参数 min定长或最小长度，max最大长度
function ICheckLen($str,$min=0,$max=0){
	if(!empty($min) && empty($max)){
		//定长
		if(dstrlen($str)==$min)
			return true;
		else
			return false;
	}else{
		if(dstrlen($str)>$max || dstrlen($str)<$min)
			return false;
		else
			return true;
	}
}
//数据验证
//参数：数据，验证类型，其他要求
function IFilterReg($val,$type,$paras){
	$regs=array('WN'=>'/^[0-9a-zA-Z]*$/',//字母、数字
				'WNU'=>'/^[0-9a-zA-Z_]*$/',//字母、数字下划线
				'NB'=>'/^[0-9]*$/',//数字
				'CN'=>'/^[\u4e00-\u9fa5]*$/',//汉字
				'EMAIL'=>'/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
				'ID'=>'/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|[x|X])$/',//身份证
				'FL'=>'/^(([0-9]*)|([0-9]+[.]{1}[0-9]+))$/',//小数 //
				'MB'=>'/^1[3|5|7|8|][0-9]{9}$/',//手机号
				'TEL'=>'/^((\d{7,8})|(\d{4}|\d{3})-(\d{7,8})|(\d{4}|\d{3})-(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1})|(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1}))$/',//电话号码	
				'LGT'=>'',//长度大于等于
				'LLT'=>'',//长度小于等于
				'LBT'=>''//长度介于	
				);
	$type=strtoupper($type);
	$rule=$regs[$type];
	if(!empty($val)){
		if(!empty($rule) ){
			preg_match($rule,$val,$result);
			if(count($result)>0)
				return true;
			else
				return false;
		}else{
			$result=true;
			if($type=='LGT'){
				//长度大于等于
				if(dstrlen($val)<$paras)
					$result=false;
					
			}else if($type=='LLT'){
				//长度小于等于
				if(dstrlen($val)>$paras)
					$result=false;
			}else if($type=='LBT'){
				//长度介于
				if(dstrlen($val)<$paras[0] || dstrlen($val)>$paras[1])
					$result=false;
			}
			return $result;
		}
	}
	return true;
}
//输入字符串过滤
function IFilter($str){
	return htmlspecialchars($str, ENT_QUOTES);
}

//创建唯一编码，依据当前时间
function CreateIDCode($pre){
	$time=time();
	$ymd=date('ymd',$time);
	$his=date('H-i-s',$time);
	$mi=ceil(microtime()*10000);
	if($mi<1000){
		$mi='0'.$mi;	
	}
	return $pre.$ymd.$mi;
}

function ResetArray($arr,$key){
	$newarr=array();
	foreach($arr as $row){
		$newarr[$row[$key]]=$row;	
	}
	return $newarr;
}
//$f 0:201501,1:2015-01
function getPlusMonth($add,$f=0,$month=0){
	$format=empty($f)?'Ym':'Y-m';
	$sub=empty($f)?4:5;
	$month=empty($month)?date($format):$month;
	$y=intval(substr($month,0,4));
	$m=intval(substr($month,$sub,2))+$add;
	$premonth=date($format,mktime(0,0,0,$m,1,$y));
	return $premonth;
}
function trimEmptyField(&$arr,$newval=0,$except=array()){
	foreach($arr as $k=>$val){
		if(!in_array($k,$except)){
			if(empty($val))
				$arr[$k]=$newval;
		}
	}
}

function array_remove(&$arr,$val){
	foreach($arr as $k=>$v){
		if($val==$v){
			unset($arr[$k]);	
		}	
	}
}

/**
 * 将list_to_tree的树还原成列表
 *
 * @param array $tree
 *        	原来的树
 * @param string $child
 *        	孩子节点的键
 * @param string $order
 *        	排序显示的键，一般是主键 升序排列
 * @param array $list
 *        	过渡用的中间数组，
 * @return array 返回排过序的列表数组
 * @author yangweijie <yangweijiester@gmail.com>
 */
function tree_to_list($tree, $child = '_child', $order = 'id', &$list = array()) {
	if (is_array ( $tree )) {
		$refer = array ();
		foreach ( $tree as $key => $value ) {
			$reffer = $value;
			if (isset ( $reffer [$child] )) {
				unset ( $reffer [$child] );
				tree_to_list ( $value [$child], $child, $order, $list );
			}
			$list [] = $reffer;
		}
		$list = list_sort_by ( $list, $order, $sortby = 'asc' );
	}
	return $list;
}
/**
 * 把返回的数据集转换成Tree
 *
 * @param array $list
 *        	要转换的数据集
 * @param string $pid
 *        	parent标记字段
 * @param string $level
 *        	level标记字段
 * @return array
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0) {
	// 创建Tree
	$tree = array ();
	if (is_array ( $list )) {
		// 创建基于主键的数组引用
		$refer = array ();
		foreach ( $list as $key => $data ) {
			$refer [$data [$pk]] = & $list [$key];
		}
		foreach ( $list as $key => $data ) {
			// 判断是否存在parent
			$parentId = $data [$pid];
			if ($root == $parentId) {
				$tree [] = & $list [$key];
			} else {
				if (isset ( $refer [$parentId] )) {
					$parent = & $refer [$parentId];
					$parent [$child] [] = & $list [$key];
				}
			}
		}
	}
	return $tree;
}
/**
 * 对查询结果集进行排序
 *
 * @access public
 * @param array $list
 *        	查询结果
 * @param string $field
 *        	排序的字段名
 * @param array $sortby
 *        	排序类型
 *        	asc正向排序 desc逆向排序 nat自然排序
 * @return array
 *
 */
function list_sort_by($list, $field, $sortby = 'asc') {
	if (is_array ( $list )) {
		$refer = $resultSet = array ();
		foreach ( $list as $i => $data )
			$refer [$i] = &$data [$field];
		switch ($sortby) {
			case 'asc' : // 正向排序
				asort ( $refer );
				break;
			case 'desc' : // 逆向排序
				arsort ( $refer );
				break;
			case 'nat' : // 自然排序
				natcasesort ( $refer );
				break;
		}
		foreach ( $refer as $key => $val )
			$resultSet [] = &$list [$key];
		return $resultSet;
	}
	return false;
}
/*****************************缓存******************************/
/**
 * 字符串截取，支持中文和其他编码
 *
 * @access public
 * @param string $str
 *        	需要转换的字符串
 * @param string $start
 *        	开始位置
 * @param string $length
 *        	截取长度
 * @param string $charset
 *        	编码格式
 * @param string $suffix
 *        	截断显示字符
 * @return string
 */
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true) {
	if (function_exists ( "mb_substr" ))
		$slice = mb_substr ( $str, $start, $length, $charset );
	elseif (function_exists ( 'iconv_substr' )) {
		$slice = iconv_substr ( $str, $start, $length, $charset );
		if (false === $slice) {
			$slice = '';
		}
	} else {
		$re ['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re ['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re ['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re ['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		preg_match_all ( $re [$charset], $str, $match );
		$slice = join ( "", array_slice ( $match [0], $start, $length ) );
	}
	
	return $suffix && $str != $slice ? $slice . '...' : $slice;
}
/**
 * 方法增强，根据$length自动判断是否应该显示...
 * 字符串截取，支持中文和其他编码
 * QQ:125682133
 *
 * @access public
 * @param string $str
 *        	需要转换的字符串
 * @param string $start
 *        	开始位置
 * @param string $length
 *        	截取长度
 * @param string $charset
 *        	编码格式
 * @param string $suffix
 *        	截断显示字符
 * @return string
 */
function msubstr_local($str, $start = 0, $length, $charset = "utf-8") {
	if (function_exists ( "mb_substr" ))
		$slice = mb_substr ( $str, $start, $length, $charset );
	elseif (function_exists ( 'iconv_substr' )) {
		$slice = iconv_substr ( $str, $start, $length, $charset );
		if (false === $slice) {
			$slice = '';
		}
	} else {
		$re ['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re ['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re ['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re ['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		preg_match_all ( $re [$charset], $str, $match );
		
		$slice = join ( "", array_slice ( $match [0], $start, $length ) );
	}
	return (strlen ( $str ) > strlen ( $slice )) ? $slice . '...' : $slice;
}
function subHtml($text,$length){
	$text=str_replace('&nbsp;','', trim(strip_tags($text)));
	return msubstr_local($text,0,$length);
}
function dstrlen($str){
	return (strlen($str)+mb_strlen($str,'utf-8'))/2;	
}
//计算时间戳小时时间差，参数必须为时间戳int类型
function dayDiff($start,$end){
	$hours=hourDiff($start,$end);
	return intval(ceil(floatval($hours)/24));
}
//计算时间戳小时时间差，参数必须为时间戳int类型
function hourDiff($start,$end){
	if(is_int($start) && is_int($end)){
		return ($start-$end)/60/60;
	}else{
		return 0;	
	}
}

//计算时间戳分钟时间差，参数必须为时间戳int类型
function minDiff($start,$end){
	if(is_int($start) && is_int($end)){
		return ($start-$end)/60;
	}else{
		return 0;	
	}
}
//生成随机字符串
function createNoncestr( $length = 32 ) 
{
	$chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
	$str ="";
	for ( $i = 0; $i < $length; $i++ )  {  
		$str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
	}  
	return $str;
}

/**
 * 生成签名
 * @return 签名
 */
function MakeSign($arr)
{
	//签名步骤一：按字典序排序参数
	ksort($arr);
	$buff = "";
	foreach ($arr as $k => $v)
	{
		if($k != "sign"){
			$buff .= $k . "=" . $v . "&";
		}
	}
	$buff = trim($buff, "&");
	$string = sha1($buff);
	return $string;
}

function getWWWROOT(){
	return str_ireplace(str_replace("/","\\",'/Application/Common/Common/common.php'),'',__FILE__)."\\";	
}

function trimEmoji($str){	
	return $str;
}

//将miniui布尔类型转换为数据库tinyint
function parsePostBool(&$post,$field){
	foreach($field as $f){		
		if($post[$f]=='true'){
			$val=1;	
		}else{
			$val=0;	
		}
		$post[$f]=$val;
	}
}

/*----------------------数据字典----------------------*/
//取得数据字典项目对应名称
function getDic($code){
	//$dicItem=S('sys_dic_'.$code);
	//if(empty($dic)){
		$Dic=M('sys_dic');
		$dicItem=$Dic->field('title,diccode,valuetype,valuetext')->where("state=0 and diccode='$code'")->find();
		$list=exDicValueText($dicItem['valuetext']);
		$list=ResetArray($list,'id');
		$dicItem['items']=$list;
		//S('sys_dic_'.$code,$dicItem);
	//}
	return $dicItem;
}
//转换字典项目值为数组
function exDicValueText($valuetext){
	$list=array();
	$items=explode(',',$valuetext);
	if(!empty($valuetext)){
		foreach($items as $str){
			$tmp=explode(':',$str);
			$list[]=array('id'=>$tmp[0],'value'=>$tmp[1]);
		}
	}
	return $list;
}
function getDicTitle($code,$id){
	$title='无';
	$dicitem=getDic($code);
	if(!empty($dicitem)){		
		$items=$dicitem['items'];
		if(is_array($items)){
			$title=$items[$id]['value'];
		}
	}
	return $title;
}
function transModelDic(&$data,$arr){
	$pre='txt_';
	foreach($arr as $k=>$v){
		if(isset($data[$k])){
			$data[$pre.$k]=getDicTitle($v,$data[$k]);
		}	
	}
}
//加载一组字典项目，并转为js数组声明
function defineDicJs($items){
	$busiDicStr='';
	foreach($items as $code){
		$js=getDicJS($code);
		if(empty($js)){
			$js='null';
		}
		$busiDicStr.="var dic_{$code}= $js;\r\n";
	}
	return $busiDicStr;
}


//加载一个字典项目，并转为js格式
function getDicJS($code){
	$return='[';
	$dic=getDic($code);	
	
	foreach($dic['items'] as $k=>$a){
		$return.="{\"id\":$k,\"text\":'$a[value]'},";
	}
	$return=trim($return,',').']';
	return $return;
}

//根据id取得地区名称，分隔符'_'
function getAreaTitle($ids){
	$idlist=explode('_',$ids);
	$title='';	
	$arealist=getAreaList();
	if(is_array($idlist) && sizeof($idlist)>=2){		
		$province=$arealist[$idlist[0]];//省
		$title=$province['province_name'];
		$city=$province['city'][$idlist[1]];//市
		$title.=$city['city_name'];
		if(sizeof($idlist)==3){
			$area=$city['area'][$idlist[2]];
			$title.=$area['area_name'];//区
		}
	}else{
		$title=$arealist[$ids]['province_name'];//只有省
	}
	return $title;
}
//取得所有地区数组
function getAreaList(){	
	$area=require_once(APP_PATH.'/common/Common/area.php');
	return $area;
}

function getSysSetting($code){
	$data=F('appset/'.$code);
	if(empty($data)){
		$set=M('sys_setting')->field('content')->where(array('setcode'=>$code))->find();
		if(is_array($set)){
			$value=unserialize($set['content']);	
			$data=$value;
			F('appset/'.$code,$value);
		}
	}
	return $data;
}
function setSysSetting($code,$value){
	F('appset/'.$code,$value);
	if(M('sys_setting')->where(array('setcode'=>$code))->count()<=0){
		$data['setcode']=$code;
		$data['content']=serialize($value);
		M('sys_setting')->add($data);
	}else{
		M('sys_setting')->where(array('setcode'=>$code))->save(array('content'=>serialize($value)));
	}
}
//获取配置文件参数值，只支持一维数组
function getConfigValue($code,$id){
	$arr=C($code);
	$value='';
	if(!empty($arr) && is_array($arr)){
		$value=$arr[$id];
	}
	return $value;
}
/*----------------------处理图片路径----------------------*/

//生成全网址头像
function formatImgUrl($url,$sex)
{
	$serverurl = $_SERVER['SERVER_NAME'];
	if(trim($url)=='')
	{
		return 'http://'.$serverurl.'/'.__ROOT__.'/public/'.($sex==0?'/images/headimg_man.png':'/images/headimg_women.png');
	}
	else
	{
		if(stripos(trim($url),'http://')>-1)
		{
			return $url;
		}
		else
		{
			return 'http://'.$serverurl.'/'.__ROOT__.'/UploadFiles/'.$url;
		}
	}
}
function formatFileUrl($url)
{
	if(trim($url)=='')
	{
		return '';	
	}
	$serverurl = $_SERVER['SERVER_NAME'];
	if(stripos(trim($url),'http://')>-1)
	{
		return $url;
	}
	else
	{
		return 'http://'.$serverurl.'/'.__ROOT__.'/UploadFiles/'.$url;
	}
}


/*------------------微信--------------------*/


// 判断是否是在微信浏览器里
function isWeixinBrowser() {
	$agent = $_SERVER ['HTTP_USER_AGENT'];
	if (! strpos ( $agent, "icroMessenger" )) {
		return false;
	}
	return true;
}
// php获取当前访问的完整url地址
function GetCurUrl() {
	$url = 'http://';
	if (isset ( $_SERVER ['HTTPS'] ) && $_SERVER ['HTTPS'] == 'on') {
		$url = 'https://';
	}
//	if ($_SERVER ['SERVER_PORT'] != '80') {
//		$url .= $_SERVER ['HTTP_HOST'] . ':' . $_SERVER ['SERVER_PORT'] . $_SERVER ['REQUEST_URI'];
//	} else {
		$url .= $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'];
//	}
	// 兼容后面的参数组装
	if (stripos ( $url, '?' ) === false) {
		$url .= '?t=' . time ();
	}
	return $url;
}
function GetCurHost() {
	$url = 'http://';
	if (isset ( $_SERVER ['HTTPS'] ) && $_SERVER ['HTTPS'] == 'on') {
		$url = 'https://';
	}
	$url .= $_SERVER ['HTTP_HOST'];
	return $url;
}
	
// 获取当前用户的OpenId
function getOpenid() {	
	$key='wx_openid';
	$openid =session($key);
	if (empty ( $openid ) ) {
		$callback = GetCurUrl();
		$openid=OAuthWeixin( $callback );
		if(!$openid){
			return false;	
		}
		session ($key,$openid);
	}
	return $openid;
}

//网页授权身份验证，用于获取openid
function OAuthWeixin($callback) {
	$info = getWxmpInfo();

	$param ['appid'] = $info ['appid'];
	$state='123';
	if (empty($_GET['code'])) {
		//重定向到微信获取code
		$param ['redirect_uri'] = $callback ;
		$param ['response_type'] = 'code';
		$param ['scope'] = 'snsapi_base';
		$param ['state'] = $state;
		$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?' . http_build_query ( $param ) . '#wechat_redirect';
		redirect ( $url );
	} else{				
	
		//返回code.
		$param ['secret'] = $info['appsecret'];
		$param ['code'] =$_GET['code'];		
		$param ['grant_type'] = 'authorization_code';		
		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?' . http_build_query ( $param );
		$content = curlGet( $url );
		$content = json_decode( $content, true );
		if(empty( $content ['openid'])){
			return false;
		}			
		return $content ['openid'];
	}
}
function curlGet($url,$data=''){
	$ch = curl_init();
	//echo 1;
	//设置超时
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	if(!empty($data)){
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	}
	$res = curl_exec($ch);
	curl_close($ch);
	return $res;
}
function getJsApiTicket() {
    // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
	$key='weixin_jsapi_token';
    $ticketSess = F($key);
    if (empty($ticketSess) || (time()-$ticketSess['time'])>6000) {
      $accessToken = getAccessToken();
      // 如果是企业号用以下 URL 获取 ticket
      //$url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
      $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
      $res = json_decode(curlGet($url));
      $ticket = $res->ticket;
      if ($ticket) {
		  $ticketSess['ticket']=$ticket;
		  $ticketSess['time']=time();
          F($key,$ticketSess);
      }else{
		   F('JSApiErr',$res);
		   return false;  
	  }
    }else{
		$ticket=$ticketSess['ticket'];
	}
    return $ticket;
  }
function getAccessToken() {
	$key='weixin_token';
    $tokensess = F($key);
    if (empty($tokensess) || (time()-$tokensess['time'])>6000) {
      // 如果是企业号用以下URL获取access_token
      //$url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$info[appid]&corpsecret=$info[appsecret]";
		$info = getWxmpInfo();
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$info[appid]&secret=$info[appsecret]";
		$str=curlGet($url);
		$res = json_decode(curlGet($url));
		$token = $res->access_token;
		if (!empty($token)) {
			$tokensess=array();
			$tokensess['token']=$token;
			$tokensess['time']=time();
			F($key,$tokensess);
		}else{
			return false;
		}
    }else{
		$token=$tokensess['token'];
	}
    return $token;
  }
function refreshAccessToken(){
	 $key='weixin_token';
	 F($key,0);
	 getAccessToken();
}
function createWxMenu($menu){
	$token=getAccessToken();
	$url='https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$token;	
	
	$datastr= json_encode($menu,JSON_UNESCAPED_UNICODE);
	$res = json_decode(curlGet($url,$datastr));
	return $res;
}

function getWxMenu(){
	$token=getAccessToken();
	$url='https://api.weixin.qq.com/cgi-bin/menu/get?access_token='.$token;	
	$res = json_decode(curlGet($url,$datastr));
	return $res;
}

// 获取公众号的信息
function getWxmpInfo($token = '') {
	$info = getSysSetting('weixinparamter');
	return $info;
}
function getWxUserInfo($openid=''){
	
	$callback = GetCurUrl ();
	if (empty ( $openid )) {
		return false;
	}
	$token=getAccessToken();
	if(!$token){
		return false;
	}
		
	$url='https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$token.'&openid='.$openid;
	$content = curlGet($url);
	$content = json_decode( $content, true );
	return $content;
}


//微信用户登录
function weixinRegist($openid){		
	if(empty($openid)){			
		exit('微信用户openid未能正常获取');
	}	
				
	//注册微信用户
	$info=getWxUserInfo($openid);
	if($info===false ){
		exit("获取用户信息失败");
	}
	if(!empty($info['errcode'])){
		if($info['errcode']=='40001'){
			refreshAccessToken();
		}
		print_r($info);exit;
	}
	if($info['subscribe']=='0')
		return 'unscribe';
	$member=array();
	if(isset($_GET['sp'])){
		$spreader_id=intval($_GET['sp']);
		
	}
	$member['openid']=$info['openid'];//$openid;
	$member['nickname']=$info['nickname'];
	$member['user_name']=$info['nickname'];
	$member['sex']=$info['sex'];
	$member['headimg']=$info['headimgurl'];
	$member['user_name']=$info['nickname'];	
	$member['country']=$info['country'];
	$member['province']=$info['province'];
	$member['city']=$info['city'];
	$member['create_time']=time();	
	$r=M('member')->add($member);
			
	if($r){	
		$mod=M('member')->find($r);
		return $mod;
	}else{
		return false;	
	}	

}


/*----------------------------业务杂项-------------------------*/
function showAjaxNoHeader($msg,$state,$info=array()){
	$data=array('status'=>$state,'info'=>$msg);
	$data=array_merge($data,$info);
	echo json_encode($data);
	exit;
}

function defaultImg($url){
	$url=empty($url)? __ROOT__.'/Public/hdimg/nohead.jpg': $url;
	return $url;
}


//解析查询条件字符串 
//search=cate-0|attr_car_brand-14
function explodeSearchParm($str){
	F('array',$str);
	$data=array();
	$data_list=array();
	$sepItem='|';
	$sepVal='-';
	$sepPre='_';
	$arr=explode($sepItem,$str);
	$attrPre='attr';
	$specPre='spec';
	$data[$attrPre]=array();
	$data[$specPre]=array();
	
	foreach($arr as $a){
		
		list($key,$val)=explode($sepVal,$a);
		if(strpos($key,$attrPre.$sepPre)!==false){
			F('attr',$key);
			$key=str_replace($attrPre.$sepPre,'',$key);
			$data[$attrPre][$key]=$val;
		}else if(strpos($key,$specPre.$sepPre)!==false){
			F('spec',$key);
			$key=str_replace($specPre.$sepPre,'',$key);
			$data[$specPre][$key]=$val;
		}else{
			F('kw',$key);
			$data[$key]=$val;
		}
	}
	return $data;
}

function showPager($count,$pageSize){
	$page=new \Think\Page($count,$pageSize);
	$page->setConfig('prev','<');
	$page->setConfig('next','>');
	$page->setConfig('first','<<');
	$page->setConfig('last','>>');
	$page->lastSuffix=false;
	$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %LAST% ');
	$pager=$page->show();
	return $pager;
}

function getPayment(){
	$config=C('payment');
	foreach($config as $k=>$c){
		if($c['is_hide']=='1'){
			unset($config[$k]);	
		}	
	}
	return $config;
}
function getOrderGoodsNames($order_id){
	$names=M('mall_order_goods')->where("order_id='$order_id'")->getField('goods_name',',');
	return $names;
}

function weiCusPay($type,$id,$openid,$price,$body){
	$cuspay=new \Common\Lib\Weixincuspay();
	$now=time();
	$trade_no="{$type}-{$now}-$id-";
	$trade_no=str_pad($trade_no,32,'0');
	$data=array(
		'orderid'=>$trade_no,
		'openid'=>$openid,
		'price'=>$price,
		'body'=>$body,
	);
	$res=$cuspay->payorder($data);
	$msg=array('state'=>false,'msg'=>'');
	if($res['return_code']=='SUCCESS' && $res['result_code']=='SUCCESS'){
		$msg['state']=true;
		$msg['msg']=$trade_no;
		return $trade_no;
	}else{
		F('cuspay',$return);	
		$msg['msg']=$res['return_msg'].$res['err_code'].$res['err_code_des'];		
	}
	return $msg;
}

function promoteVip($userid){
	$limit=C('score_vip');
	$score=D('User')->where("user_id='$userid'")->getField('score');
	if(intval($score)>=$limit){
		D('User')->where("user_id='$userid'")->save(array('is_vip'=>1));
	}
}
function jitui($alias,$tag,$order=''){
		$appKey=C('jitui_appKey');
		$masterSecret=C('masterSecret');
		$client=new \Common\Lib\Client($appKey, $masterSecret);

		// 完整的推送示例
		// 这只是使用样例,不应该直接用于实际生产环境中 !!
		try {
			$response = $client->push()
			->setPlatform(array('android'))
			// 一般情况下，关于 audience 的设置只需要调用 addAlias、addTag、addTagAnd  或 addRegistrationId
			// 这四个方法中的某一个即可，这里仅作为示例，当然全部调用也可以，多项 audience 调用表示其结果的交集
			// 即是说一般情况下，下面三个方法和没有列出的 addTagAnd 一共四个，只适用一个便可满足大多数的场景需求
			
			->addAlias($alias)//527
		    ->addTag(array($tag))
			// ->addRegistrationId($registration_id)

			->message('您有一个订单待处理,订单编号'.$order, array(
					'title' => '5发菜推送',
					// 'content_type' => 'text',
					'extras' => array(
							'key' => 'value',
							'jiguang'
					),
			))
			
			->options(array(
					// sendno: 表示推送序号，纯粹用来作为 API 调用标识，
					// API 返回时被原样返回，以方便 API 调用方匹配请求与返回
					// 这里设置为 100 仅作为示例
					
					// 'sendno' => 100,
					
					// time_to_live: 表示离线消息保留时长(秒)，
					// 推送当前用户不在线时，为该用户保留多长时间的离线消息，以便其上线时再次推送。
					// 默认 86400 （1 天），最长 10 天。设置为 0 表示不保留离线消息，只有推送当前在线的用户可以收到
					// 这里设置为 1 仅作为示例
					
					// 'time_to_live' => 1,
					
					// apns_production: 表示APNs是否生产环境，
					// True 表示推送生产环境，False 表示要推送开发环境；如果不指定则默认为推送生产环境
					
					'apns_production' => false,
					
					// big_push_duration: 表示定速推送时长(分钟)，又名缓慢推送，把原本尽可能快的推送速度，降低下来，
					// 给定的 n 分钟内，均匀地向这次推送的目标用户推送。最大值为1400.未设置则不是定速推送
					// 这里设置为 1 仅作为示例
					
					// 'big_push_duration' => 1
			))
			->send();
			return $response;
			
		} catch (\Common\Lib\Exceptions\APIConnectionException $e) {
			// try something here
			print $e;
		} catch (\Common\Lib\Exceptions\APIRequestException $e) {
			// try something here
			print $e;
		}
		
	}
function bai_tui($type,$channel_id){//1:安卓，2：ios
	if($type==1){
		vendor('AppTui.PushSDK');
		// 创建SDK对象.
		$sdk = new \PushSDK();
		$channelId = $channel_id;
		
		// message content.
		$message = array (
		// 消息的标题.
				'title' => '推送消息',
				// 消息内容
				'description' => "您有一个订单待处理" 
		);
		
		// 设置消息类型为 通知类型.
		$opts = array (
				'msg_type' => 1
		);
		
		// 向目标设备发送一条消息
		$rs = $sdk -> pushMsgToSingleDevice($channelId, $message, $opts);
		
		// 判断返回值,当发送失败时, $rs的结果为false, 可以通过getError来获得错误信息.
		if($rs === false){
			
			echo $sdk->getLastErrorMsg();
		}else{
			return $rs;
		}
	}else{
		vendor('AppTui_ios.PushSDK');
		// 创建SDK对象.
		$sdk = new \PushSDK();
		
		$channelId = $channel_id;
		// message content.
		$message = array (
				'aps' => array (
						// 消息内容
						'alert' => "您有一个订单待处理",
				),
		);
		
		// 设置消息类型为 通知类型.
		$opts = array (
				'msg_type' => 1,        // iOS不支持透传, 只能设置 msg_type:1, 即通知消息.
				'deploy_status' => 1,   // iOS应用的部署状态:  1：开发状态；2：生产状态； 若不指定，则默认设置为生产状态。
		);
		
		// 向目标设备发送一条消息
		$rs = $sdk -> pushMsgToSingleDevice($channelId, $message, $opts);
		
		// 判断返回值,当发送失败时, $rs的结果为false, 可以通过getError来获得错误信息.
		if($rs === false){
			echo $sdk->getLastErrorMsg();
		}else{
			return $rs;

		}
		
	}

	

}