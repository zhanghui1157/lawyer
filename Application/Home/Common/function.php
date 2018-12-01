<?php  	

function hook($hook,$params=array()){
    \Think\Hook::listen($hook,$params);        //监听一个钩子
}

function sendMail($to, $subject, $content) {
    vendor('PHPMailer.class#smtp'); 
    vendor('PHPMailer.class#phpmailer');    //注意这里的大小写哦，不然会出现找不到类，PHPMailer是文件夹名字，class#phpmailer就代表class.phpmailer.php文件名
    $mail = new PHPMailer();
    // 装配邮件服务器
    if (C('MAIL_SMTP')) {
        $mail->IsSMTP();
    }
    $mail->Host = C('MAIL_HOST');  //这里的参数解释见下面的配置信息注释
    $mail->SMTPAuth = C('MAIL_SMTPAUTH');  
    $mail->Username = C('MAIL_USERNAME');
    $mail->Password = C('MAIL_PASSWORD');
    // $mail->SMTPSecure = C('MAIL_SECURE');
    $mail->CharSet = C('MAIL_CHARSET');
    // 装配邮件头信息
    $mail->From = C('MAIL_USERNAME');
    $mail->AddAddress($to);
    $mail->FromName = C('MAIL_FROMNAME');
    $mail->IsHTML(C('MAIL_ISHTML'));
    $mail->SMTPSecure = "ssl";// 使用ssl协议方式
    $mail->Port = 465;// 163邮箱的ssl协议方式端口号是465/994
    // 装配邮件正文信息
    $mail->Subject = $subject;
    $mail->Body = $content;
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    // 发送邮件
    if (!$mail->Send()) {
        return FALSE;
    } else {
        return TRUE;
    }

    
}

//律师解答成功后给用户发信息
function sendMessage($code,$mobile,$array,$user_id){

    vendor('Sms.TopClient');
    vendor('Sms.AlibabaAliqinFcSmsNumSendRequest');
    $c = new \TopClient;
    $c ->appkey = '24661147' ;
    $c ->secretKey = '1e78cd2f07848c70b1847b9572ff948f';
    $req = new \AlibabaAliqinFcSmsNumSendRequest;

    $req ->setExtend( "" );
    $req ->setSmsType( "normal" );
    $req ->setSmsFreeSignName( "律师在哪" );
    $req ->setSmsParam( "$array");
    $req ->setRecNum( "$mobile" );
    $req ->setSmsTemplateCode( $code );
    $resp = $c ->execute( $req );
    $success=$resp->result->success;
    $error=$resp->sub_msg;
    $data['user_id']=$user_id;
    $data['create_time']=time();
    $data['phone']=$mobile;
    if($success){
        $data['content']='短信发送成功';
    }else{
        $data['content']='短信发送失败';
    }

    $res=M('info')->add($data);
    
}


