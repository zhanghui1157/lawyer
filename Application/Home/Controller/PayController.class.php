<?php
namespace Home\Controller;
use Think\Controller;

class PayController extends Controller {
    protected function _initialize(){
        vendor('WxPay.WxPay#Api');
    }
    
    // 微信支付获取参数
    public function toPay(){

        $data= I('');
        $order=M('order');
        $plat_log=M('plat_money');
        $trade_no=time().rand(1000,9999);

        $user_info=M('user')->where(array('openid'=>$data['openid']))->find();
        $add['combo_id']=$data['combo_id'];
        $add['create_time']=time();
        $add['user_id']=$user_info['id'];
		if(!empty($data['lawyer_id'])){
			$add['lawyer_id']=$data['lawyer_id'];
		}
		
		if(empty($data['share_money'])){
			$share_money=0;
		}else{
			$share_money=$data['share_money'];
		}
		
		$money=$data['money']-$share_money;
		if($money<=0){
			$add['status']=1;
			$add['money']=0;
			$add['transaction_id']=1;
		}else{
			$add['money']=$money;
		}
        $add['money']=$data['money'];
        $add['type']=$data['tp'];
		
        $add['trade_no']=$trade_no;
        $res1=$order->field('user_id,combo_id,create_time,money,type,trade_no,status,transaction_id,lawyer_id')->add($add);

        //传递过来的参数为订单商品总价格  
        /** 
         * 
         * 统一下单，WxPayUnifiedOrder中out_trade_no、body、total_fee、trade_type必填 
         * appid、mchid、spbill_create_ip、nonce_str不需要填入 
         * @param WxPayUnifiedOrder $inputObj 
         * @param int $timeOut 
         * @throws WxPayException 
         * @return 成功时返回，其他抛异常 
         */  
        $wxOrderData = new \WxPayUnifiedOrder();  
        //唯一订单号  
        
        $wxOrderData->SetOut_trade_no($trade_no);  
        //代表JSAPI模式，不要修改，公众号支付，H5，小程序都是这个  
        $wxOrderData->SetTrade_type('JSAPI');  
        //价格，单位为分  
        $wxOrderData->SetTotal_fee($data['money']* 100);  
        //商品简介  
        $wxOrderData->SetBody('在线支付');  
        //使用小程序用户的openid  
        $wxOrderData->SetOpenid($data['openid']);  
        //异步回调验证路径，开发者自定义  
        $wxOrderData->SetNotify_url('https://'.$_SERVER['HTTP_HOST'].'/Home/pay/notify');
        $rss =  $this->getPaySignature($wxOrderData);
		
        //这个是我又封装的一个生成签名的方法  
        header("Content-Type: application/json");
        return json($rss);  
    }


    //微信支付签名 
    private function getPaySignature($wxOrderData)  
    {  
        $wxOrder = \WxPayApi::unifiedOrder($wxOrderData);

        return $wxOrder;  
    }  

    public function notify(){
        $xml = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");

        //将服务器返回的XML数据转化为数组  
        $data = $this->xmlToArray($xml); 
        if (($data['return_code']=='SUCCESS') && ($data['result_code']=='SUCCESS') ) {  
            $result = $data;  
            //获取服务器返回的数据

            $map['trade_no'] =  $data['out_trade_no'];          //订单单号  
            $orderinfo = M('order')->where($map)->find();

            if($orderinfo){
                if(empty($orderinfo['transaction_id'])){
                    $savedata['openid'] = $data['openid'];   
                    $savedata['send'] = 1;               //付款人openID  
                    $savedata['bank_type'] = $data['bank_type'];            //付款类型  
                    $savedata['transaction_id'] = $data['transaction_id'];  //微信支付流水号
                    //10：已完成，0：待支付，1：已支付，2：已发货
                    $savedata['status'] = 1; 
                    $savedata['update_time'] = time(); //订单状态更改时间
                    $savedata['pay_time'] = time(); //订单状态更改时间
                    $savedata['time_end'] = $data['time_end']; //支付完成时间
                    //更新数据库  
                    // if( $orderinfo['send'] == 0 ) $this->toSendSMS($orderinfo['mobile'], 66235);
                    M('order')->where($map)->save($savedata);

                    $order=M('order');
                    $plat_log=M('plat_money');
                    $pay_log=M('pay_log');

                    $pay['user_id']=$orderinfo['user_id'];
                    $pay['money']=$orderinfo['money'];
                    $pay['type']=$orderinfo['type'];
                    $pay['create_time']=time();
                    $pay['content_id']=$orderinfo['combo_id'];
                    $res2=$pay_log->add($pay);

                    //添加平台收入
                    $percent=F('config');
                    $plat['content_id']=$orderinfo['combo_id'];
                    $plat['order_id']=$orderinfo['combo_id'];
                    $plat['type']=$orderinfo['type'];
                    $plat['create_time']=time();
                    $plat['money']=$orderinfo['money'];
                    if($orderinfo['type']==1){
						$combo_id=M('combo')->where(array('id'=>$plat['content_id']))->getField('title');
						$user_order_info=M('user')->where(array('id'=>$pay['user_id']))->find();
						$content='手机号码为 '.$user_order_info['mobile'].' 的 '.$user_order_info['user_name'].' 用户于 '.date('Y-m-d H:i',time()).' 购买了'.$combo_id.'，'.'订单编号为：'.$data['out_trade_no'].'。';
						sendMail('dingxuegui@126.com','订单提醒',$content);
						
                        $plat['plat_money']=$orderinfo['money']*$percent['orderpercent']*0.01;
                    }elseif($orderinfo['type']==2){
                        $plat['plat_money']=$orderinfo['money'];
                    }elseif($orderinfo['type']==3){
                        $plat['plat_money']=$orderinfo['money']*$percent['quespercent']*0.01;
                    }elseif($orderinfo['type']==4){
                        $plat['plat_money']=$orderinfo['money']*$percent['askpercent']*0.01;
                    }
                    $res3=$plat_log->add($plat);

                    //给律师分配相应的佣金
                    $answer=M('answer')->where(array('id'=>$orderinfo['combo_id']))->find();
                    $cold['user_id']=$answer['user_id'];
                    $cold['order_id']=$orderinfo['id'];
                    $cold['create_time']=time();
					//1套餐订单，2复听咨询，3问答收入
					$cold['source']=$orderinfo['type'];
					
                    if($orderinfo['type']==3){
                        $cold['money']=$orderinfo['money']*(1-$percent['quespercent']*0.01);
                    }elseif($orderinfo['type']==4){
                        $cold['money']=$orderinfo['money']*(1-$percent['askpercent']*0.01);
                    }
                    M('lawyer_cold')->add($cold);
                   
                    if($orderinfo['type']==4){
						$lawyerinfo=M('user')->where(array('id'=>$orderinfo['lawyer_id']))->field('mobile,user_name')->find();
                        $customerinfo=M('user')->where(array('id'=>$orderinfo['user_id']))->field('mobile,user_name')->find();

                        $customer=M('user')->where(array('id'=>$orderinfo['user_id']))->getField('user_name');
						//给下单用户发信息
                        $content='您好，您在律师在哪平台上订购订单咨询已经支付成功，请致电 '.$lawyerinfo['mobile'].' 联系律师为您进行电话咨询服务，订单编号：'.$orderinfo['trade_no'].'。';
                        //发信息钩子
                        $param=array(
                            'user_id'=>$orderinfo['user_id'],
                            'content'=>$content,
                        );
                        \Think\Hook::listen('AddMessage',$param);   

                        //给下单用户发信息发送通知消息
                        $message="{custemer:".$customerinfo['user_name'].",layer:".$lawyerinfo['user_name'].",layertel:".$lawyerinfo['mobile']."}";
                        sendMessage('SMS_151085462',$customerinfo['mobile'],$message,$orderinfo['user_id']);

						//给律师发信息
						$phone=M('user')->where(array('id'=>$orderinfo['user_id']))->getField('mobile');
						$content='您好，手机号码为 '.$customerinfo['mobile'].' 的用户购买电话咨询已经支付成功，请及时致电进行电话咨询服务，订单编号：'.$orderinfo['trade_no'].'。';

                        //发信息钩子
                        $param=array(
                            'user_id'=>$orderinfo['lawyer_id'],
                            'content'=>$content,
                        );
                        \Think\Hook::listen('AddMessage',$param); 

                        //用户下单后给律师发送通知消息
                        $message="{layer:".$lawyerinfo['user_name'].",custemer:".$customerinfo['user_name'].",custel:".$customerinfo['mobile']."}";
                        sendMessage('SMS_151090414',$lawyerinfo['mobile'],$message,$orderinfo['lawyer_id']);


                    }else{
                        if($orderinfo['type']==1){
                            $order=M('combo')->where(array('id'=>$orderinfo['combo_id']))->getField('title');
                            $content='您好，您在律师在哪平台上订购的“'.$order.'”已经支付成功，请保持电话畅通，等待我们专业客服进行案件咨询编写服务，订单编号：'.$orderinfo['trade_no'].'。';
                        }elseif($orderinfo['type']==2){
                            $orderlawyerid=M('order_deal')->where(array('order_id'=>$orderinfo['combo_id'],'is_user'=>1))->field('user_id')->select();
                            foreach ($orderlawyerid as $k => &$v) {
                                $lawyerInfo=M('user')->where(array('id'=>$v['user_id']))->field('user_name,mobile')->find();
                                $lawyerphones.=$lawyerInfo['user_name'].'律师电话 '.$lawyerInfo['mobile'].'， ';
                            }

                            $content='您好，您在律师在哪平台上订购复听业务已经支付成功，购买问答所属的律师电话： '.$lawyerphones.'如有需要请拨电话咨询，订单编号：'.$orderinfo['trade_no'].'。';
                        }elseif($orderinfo['type']==3){
                            $content='您好，您在律师在哪平台上订购的问答业务已经支付成功，请保持电话畅通，等待我们专业客服进行案件咨询编写服务，订单编号：'.$orderinfo['trade_no'].'。';
                        }
                        //发信息钩子
                        $param=array(
                            'user_id'=>$orderinfo['user_id'],
                            'content'=>$content,
                        );
                        \Think\Hook::listen('AddMessage',$param);   
                    }
                } 
            }
        }else{  
            $result = array('code'=>0, 'msg'=>'支付失败');  
        }


        // 返回状态给微信服务器  
        if ($result) {  
            $str='<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';  
        }else{  
            $str='<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>';  
        }  

        return json($result);  
    }


    function xmlToArray($xml){
        //考虑到xml文档中可能会包含<![CDATA[]]>标签，第三个参数设置为LIBXML_NOCDATA
        if (file_exists($xml)) {
            libxml_disable_entity_loader(false);
            $xml_string = simplexml_load_file($xml,'SimpleXMLElement', LIBXML_NOCDATA);
        }else{
            libxml_disable_entity_loader(true);
            $xml_string = simplexml_load_string($xml,'SimpleXMLElement', LIBXML_NOCDATA);
        }
        $result = json_decode(json_encode($xml_string),true);
        return $result;

    }
}