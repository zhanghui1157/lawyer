<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    protected $getID3='';
    protected function _initialize(){
        vendor('getid3.getid3');
        $this->getID3 = new \getID3();    //实例化类
    }

    public function setBankInfo(){
        $openid=I('openid');
        $data['bank_name']=I('bank_name');//开户名
        $data['bank_card']=I('bank_card');//卡号
        $data['bank_num']=I('bank_num');//开户行
		$old=M('user')->where(array('openid'=>$openid))->where($data)->find();
        if($old){
            json(2,'未修改银行卡信息');
        }else{
            $res=M('user')->where(array('openid'=>$openid))->save($data);
			if($res){
				json(1,'添加银行卡信息成功');
				
			}else{
				json(0,'添加银行卡信息失败');
			}   
        }
    }
	
	
	//提现申请
    public function getMoney(){
        $openid=I('openid');
        $data['user_id']=M('user')->where(array('openid'=>$openid))->getField('id');
        $data['money']=I('money');
        $data['create_time']=time();

        $money_log=M('money_log');
        $lawyer_money=M('lawyer_money');
        $money_log->startTrans();

        $now_money=$lawyer_money->where(array('user_id'=>$data['user_id']))->getField('now_money');
        if($now_money<$data['money']){
            json(0,'余额不足');
        }
        $res1=$money_log->add($data);
        $res2=$lawyer_money->where(array('user_id'=>$data['user_id']))->setDec('now_money',$data['money']);
        if($res1 && $res2){
            $money_log->commit();

            //给下单用户发信息
            $content='提现申请审核发起成功，提现金额： '.$data['money']. ' 元，申请时间： ' .time('Y-m-d H:i',time()) .' 请等待审核';

            //发信息钩子
            $param=array(
                'user_id'=>$data['user_id'],
                'content'=>$content,
            );
            \Think\Hook::listen('AddMessage',$param);   

            json(1,'提现申请成功！');
        }else{
            $money_log->rollback();
            json(0,'提现申请失败！');
        }
    }
	
	//getWallet
    //参数：openid
    //返回值：提现记录列表和收入记录列表、余额、银行卡号
    public function getWallet(){
        $openid=I('openid');
        $user=M('user')->where(array('openid'=>$openid))->field('id,bank_card')->find();
        //银行卡前后四位
        $data['bank_start']=substr($user['bank_card'], 0,4);
        $data['bank_end']=substr($user['bank_card'], -1,4);

        $data['money']=M('lawyer_money')->where(array('user_id'=>$user['id']))->getField('now_money');
        $data['min_money']=F('config')['cashnum'];

        $data['income_log']=M('lawyer_cold')->where(array('user_id'))->field('money,create_time,status,source')->select();
        if(!empty($data['income_log'])){
            foreach ($data['income_log'] as $k => &$v) {
                $v['create_time']=date('Y-m-d H:i',$v['create_time']);
				
                if($v['status']==1){
                    $v['status']='冻结';
                }else{
                    $v['status']='解冻';
                }
				
				if($v['source']==1){
                    $v['source']='订单咨询';
                }elseif($v['source']==2){
                    $v['source']='复听';
                }elseif($v['source']==3){
                    $v['source']='问答';
                }elseif($v['source']==4){
                    $v['source']='电话咨询';
                }
            }
        }else{
            $data['income_log']=null;
        }
        $data['spand_log']=M('money_log')->where(array('user_id'))->field('money,create_time,status,check_time')->select();
        if(!empty($data['spand_log'])){
            foreach ($data['spand_log'] as $k => &$v) {
                $v['create_time']=date('Y-m-d H:i',$v['create_time']);
                $v['check_time']=date('Y-m-d H:i',$v['check_time']);
                if($v['status']==1){
                    $v['status']='待审核';
                }elseif($v['status']==2){
                    $v['status']='通过';
                }else{
                    $v['status']='驳回';
                }
            }
        }else{
            $data['spand_log']=null;
        }
		
		json(1,'获取信息成功！',$data);
    }

    public function getBankInfo(){
        $openid=I('openid');
        $data=M('user')->where(array('openid'=>$openid))->field('id,bank_num,bank_card,bank_name')->find();
		if($data){
			json(1,'获取银行卡信息成功',$data);   
        }else{
			json(0,'获取银行卡信息失败');    
        } 
    }

	
    // 首页
    public function getlist(){
		
        $openid=I('openid');
        $id=M('user')->where(array('openid'=>$openid))->getField('id');
        // 获取幻灯片    
        $data['banner'] = M('article')->where(array('status'=>1))->field('id,title,img_url')->order('id desc')->limit(5)->select();     

        // 获取最新档案
        $where['recommend']=1;
        $where['status']=3; 
        $data['sale'] = D('Order')->where($where)->field(true)->limit(5)->order('id desc')->select();
        foreach ($data['sale'] as &$v) {
            $v['user_name']=M('user')->where(array('id'=>$v['user_id']))->getField('user_name');
            $v['combo_price']=M('combo')->where(array('id'=>$v['combo_id']))->getField('price');
            $v['combo_number']=M('combo')->where(array('id'=>$v['combo_id']))->getField('number');
            $v['cate']=M('dictionary')->where(array('id'=>$v['cate']))->getField('name');
            $v['cate_er']=M('dictionary')->where(array('id'=>$v['cate_er']))->getField('name');
        }

        //首页获取律师未处理接单
        $dealWh['is_user']=1;
        $dealWh['user_id']=$id;
        $dealWh['answer_status']=0;
        $deal=M('order_deal')->where($dealWh)->field('order_id')->select();

        if(!empty($deal)){
            foreach ($deal as $k => &$v) {
                $order[$k]=$v['order_id'];
            }
            $orderWh['id']=array('in',$order);
            $data['order'] = D('Order')->where($orderWh)->order('id desc')->select();
            foreach ($data['order'] as $kk => &$vv) {
                $vv['cate']=M('dictionary')->where(array('id'=>$vv['cate']))->getField('name');
                $vv['cate_er']=M('dictionary')->where(array('id'=>$vv['cate_er']))->getField('name');
            }
        }
        json(1,'获取成功！',$data);
    }

    //首页轮播详情
    public function imgDetail(){
        $id=I('id');
        $article=M('article')->where(array('id'=>$id))->find();
        $article['content']=htmlspecialchars_decode($article['content']);
        if($article['is_connect']==1){
            $user=M('user_info')->alias('u')
            ->join('db_check_info c on c.user_id=u.user_id')
            ->where(array('u.user_id'=>$article['lawyer']))
            ->find();
            $user['user_name']=M('user')->where(array('id'=>$user['user_id']))->getField('user_name');
            $data['user']=$user;
        }
        
        $data['article']=$article;
       
        if($data){
            json(1,'获取成功！',$data);
        }else{
            json(0,'获取失败！');
        }
    }

    // 律师接单统计
    public function lawyerOrderList(){
        $openid=I('openid');
        $id=M('user')->where(array('openid'=>$openid))->getField('id');
        $data=M('order_deal')->where(array('user_id'=>$id))->order('id desc')->select();
        if($data){
            json(1,'获取成功！',$data);
        }else{
            json(0,'获取失败！');
        }
    }

    //发现页面
    public function getFindList(){
        $where=I('');
        $openid=I('openid');
        if(!empty($openid) && empty(I('tp'))){
            $lawyer=M('user')->where(array('openid'=>$openid))->getField('focus');
            if(!empty($lawyer)){
                $lawyer=explode(',',$lawyer);
                foreach ($lawyer as $key => &$value) {
                    $lawyer[$key]=D('User')->alias('a')
                        ->join('db_check_info i on a.id=i.user_id','RIGHT')
                        ->join('db_user_info o on a.id=o.user_id',"LEFT")
                        ->where(array('a.id'=>$value))->find();
                    $lawyer[$key]['people']=M('order_deal')->where(array('user_id'=>$lawyer[$key]['user_id']))->count();    
                    $lawyer[$key]['keywords']=explode(',',$lawyer[$key]['keywords']);
                    if(!empty($lawyer[$key]['keywords'])){
                        foreach ($lawyer[$key]['keywords'] as $kk=>&$vv) {
                            $lawyer[$key]['keywords'][$kk]=M('dictionary')->where(array('id'=>$vv))->getField('name');
                        }
                    }   
                }
            }
            json(1,'获取成功！',$lawyer);
        }

        $data['user']=M('user')->where(array('openid'=>$openid))->field(true)->find();

        //律师
        //执业地
        if(!empty(I('now_city'))){
            $lawyerWh['i.now_city']=I('now_city');
        }
        //律师擅长领域
        if(!empty(I('lawyerKey'))){
            $lawyerKey=M('dictionary')->where(array('name'=>I('lawyerKey')))->getField('id');
            $lawyerWh['i.keywords']=array('like','%'.$lawyerKey.'%');
        }
        // 家乡
        if(!empty(I('home_city'))){
            $lawyerWh['o.home_city']=I('home_city');
        }
        // 校友
        if(!empty(I('school'))){
            $lawyerWh['i.school']=I('school');
        }

        if(!empty(I('order'))){
            $order='i.create_time desc';
        }
        // else{
        //     $order='i.id desc';
        // }
        $lawyerWh['identity']=2;
        $lawyerWh['status']=2;
		$lawyerWh['is_show']=1;
        G('begin');
        // $count=D('User')->alias('')->where($lawyerWh)->count();
        $Page   =   new \Org\Util\UIPage($count,20);
        $data['lawyer'] = D('User')->alias('a')
            ->join('db_check_info i on a.id=i.user_id')
            ->join('db_user_info o on a.id=o.user_id')
            ->where($lawyerWh)->order($order)->limit($Page->firstRow.','.$Page->listRows)
            ->field('i.user_id,i.headimg,i.keywords,i.now_city,i.now_pro,i.company,i.school,o.home_pro,o.home_city,o.home_area')
            ->select();
        
        
        if(empty($data['lawyer'])){
            $data['lawyer']=null;
        }else{
            foreach ($data['lawyer'] as &$v) {
                $v['user_name']=M('user')->where(array('id'=>$v['user_id']))->getField('user_name');
                $v['headimg']=M('check_info')->where(array('user_id'=>$v['user_id']))->getField('headimg');
                
                $combo=M('combo')->where(array('id'=>$v['combo_id']))->field('price,number')->find();

                $v['combo_price']=$combo['price'];
                $v['combo_number']=$combo['number'];
                $v['people']=M('order_deal')->where(array('user_id'=>$v['user_id']))->count();
                $v['keywords']=explode(',',$v['keywords']);
                foreach ($v['keywords'] as $kk=>&$vv) {
                    $v['keywords'][$kk]=M('dictionary')->where(array('id'=>$vv))->getField('name');
                }
            }
        }
        
        //优问
        //类型
        if(!empty(I('answerKey'))){
            $answerKey=M('dictionary')->where(array('name'=>I('answerKey')))->getField('id');
            $answerWh['second']=$answerKey;
        }
        if(!empty(I('answerMoney'))){
            $answerMoney=M('dictionary')->where(array('name'=>I('answerMoney')))->getField('id');
            $answerWh['money']=$answerMoney;
        }
        $answerWh['check_status']=2;
        // dump($answerWh);
        $count=D('Answer')->where($answerWh)->count();
        $Page   =   new \Org\Util\UIPage($count,20);
        $data['answer'] = D('Answer')->where($answerWh)
            ->order('id desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->field('title,user_id,first,second,comment_point')
            ->select();
        if(empty($data['answer'])){
            $data['answer']=null;
        }else{
            foreach ($data['answer'] as &$v) {
                $v['user_name']=M('user')->where(array('id'=>$v['user_id']))->getField('user_name');
                $v['headimg']=M('check_info')->where(array('user_id'=>$v['user_id']))->getField('headimg');

                $v['first']=M('dictionary')->where(array('id'=>$v['first']))->getField('name');
                $v['second']=M('dictionary')->where(array('id'=>$v['second']))->getField('name');
                $v['answer_type']=explode(',',$v['answer_type']);
                foreach ($v['answer_type'] as $kk=>&$vv) {
                    $v['answer_type'][$kk]=M('dictionary')->where(array('id'=>$vv))->getField('name');
                }
            }
        }
        
        G('end');
        //咨询
        // 类型
        if(!empty(I('orderType'))){
            $type=M('dictionary')->where(array('name'=>I('orderType')))->getField('id');
            $orderWh['cate_er']=$type;
        }
		$orderWh['status']=array('in',array(2,3));
        $orderWh['is_show']=1;
        //排序
        if(!empty(I('orderStatus'))){
            if(I('orderStatus')=='时间'){
                $order='create_time desc';
            }elseif(I('orderStatus')=='好评'){
                $order='comment_point desc';
            }elseif(I('orderStatus')=='已解答'){
				$orderWh['status']=3;
			}elseif(I('orderStatus')=='待解答'){
				$orderWh['status']=2;
			}
        }else{
            $order='status asc,id desc';
        }
        
        //判断是律师还是普通会员

        //if($data['user']['status']==2 && $data['user']['type']==2){
            //$orderWh['status']=array('in',array(2,3));
        //}

        $user_id=$data['user']['id'];
        $count= D('Order')->where($orderWh)->count();
        $Page   =   new \Org\Util\UIPage($count,10);
        $data['order'] = D('Order')
            ->where($orderWh)
            ->order($order)
            ->limit($Page->firstRow.','.$Page->listRows)
            ->field('')
            ->select();
        if(empty($data['order'])){
            $data['order']=null;
        }else{
            foreach ($data['order'] as &$v) {
                $v['cate']=M('dictionary')->where(array('id'=>$v['cate']))->getField('name');
                $v['cate_er']=M('dictionary')->where(array('id'=>$v['cate_er']))->getField('name');

                //检查订单是否需要解答
                $orderdeal['is_user']=1;
                $orderdeal['answer_status']=1;
                $orderdeal['user_id']=$user_id;
                $newid=M('order_deal')->where($orderdeal)->getField('id');
                if(!empty($newid)){
                    $v['newid']=$v['id'];
                }
                //判断订单的人数
                $number=M('combo')->where(array('id'=>$v['combo_id']))->getField('number');
                $have=M('order_deal')->where(array('order_id'=>$v['id'],'is_user'=>1))->count();
                if($number>=$have){
                    $v['is_rob']=false;
                }else{
                    $v['is_rob']=true;
                }
            }
        }
            
        
        $data['price']=M('dictionary')->where(array('type'=>1))->field('id,name')->select();
        json(1,'获取成功！',$data);
    }

    //获取关注的律师、问答、咨询列表
	public function getFocusList(){
		$openid=I('openid');
		$list=M('user')->where(array('openid'=>$openid))->field('focus,order,answer')->find();
		if(!empty($list)){
			//关注的律师
			if(empty($list['focus'])){
				$lawyer=null;
			}else{
				$lawyer=explode(',',$list['focus']);
				foreach ($lawyer as $key => &$value) {
					$lawyer[$key]=D('User')->alias('a')
						->join('db_check_info i on a.id=i.user_id','RIGHT')
						->join('db_user_info o on a.id=o.user_id',"LEFT")
						->where(array('a.id'=>$value))->find();
					$lawyer[$key]['people']=M('order_deal')->where(array('user_id'=>$lawyer[$key]['user_id']))->count();    
					$lawyer[$key]['keywords']=explode(',',$lawyer[$key]['keywords']);
					if(!empty($lawyer[$key]['keywords'])){
						foreach ($lawyer[$key]['keywords'] as $kk=>&$vv) {
							$lawyer[$key]['keywords'][$kk]=M('dictionary')->where(array('id'=>$vv))->getField('name');
						}
					}   
				}
			
			}
			
			//关注的咨询
			if(empty($list['order'])){
				$order=null;
			}else{
				$where['id']=array('in',explode(',',$list['order']));
				$order = D('Order')->where($where)->order('id desc')->select();
				foreach ($order as &$v) {
					$v['cate']=M('dictionary')->where(array('id'=>$v['cate']))->getField('name');
					$v['cate_er']=M('dictionary')->where(array('id'=>$v['cate_er']))->getField('name');

					//检查订单是否需要解答
					$orderdeal['is_user']=1;
					$orderdeal['answer_status']=0;
					$orderdeal['user_id']=$user_id;
					$newid=M('order_deal')->where($orderdeal)->getField('id');
					if(!empty($newid)){
						$v['newid']=$v['id'];
					}
					//判断订单的人数
					$number=M('combo')->where(array('id'=>$v['combo_id']))->getField('number');
					$have=M('order_deal')->where(array('order_id'=>$v['id'],'is_user'=>1))->count();
					if($number>=$have){
						$v['is_rob']=false;
					}else{
						$v['is_rob']=true;
					}
				}
			}
			
			//关注的问答
			if(empty($list['answer'])){
				$answer=null;
			}else{
				$where['id']=array('in',explode(',',$list['answer']));
				$answer = D('Answer')->where($where)->order('id desc')->select();
				foreach ($answer as &$v) {
					$v['user_name']=M('user')->where(array('id'=>$v['user_id']))->getField('user_name');
					$v['headimg']=M('check_info')->where(array('user_id'=>$v['user_id']))->getField('headimg');
					$v['first']=M('dictionary')->where(array('id'=>$v['first']))->getField('name');
					$v['second']=M('dictionary')->where(array('id'=>$v['second']))->getField('name');
					$v['answer_type']=explode(',',$v['answer_type']);
					foreach ($v['answer_type'] as $kk=>&$vv) {
						$v['answer_type'][$kk]=M('dictionary')->where(array('id'=>$vv))->getField('name');
					}
				}
			}
			$data=array('order'=>$order,'answer'=>$answer,'lawyer'=>$lawyer);
			json(0,'成功',$data);
		}
	}
	
	
	
	//将传过来的字符串转化为数组
    public function changeStr(){
        $str=I('str');
        $str=explode(',',$str);
        foreach ($str as $k => &$v) {
            $data[$k]['filePath']=$v;
            $data[$k]['src']=$v;
            $data[$k]['id']=$k;
        }

        // foreach ($str as $k => &$v) {
        //     $newstr=explode('_',$v);

        //     $data[$k]['filePath']=$newstr[0];
        //     $data[$k]['src']=$newstr[0];
        //     $data[$k]['id']=$k;
        //     $data[$k]['duration']=$newstr[1];
        // }
        json(0,'成功',$data);
    }

    //删除上传的语音
    public function delVoicePath(){
        $res=I('');
        $voicePath=$res['voicePath'];
        $path=$res['path'];
        $delpath='.'.$path;

        unlink($delpath);

        $voicePath=explode(',',$voicePath);
        if(count($voicePath)<=1){
            $data['str']='';
            $data['arr']='';
        }else{
            $voice=$this->delByValue($voicePath, $path);
            if(empty($voice)){
                $newarr='';
            }else{
                $i=0;
                foreach ($voice as $k => &$v) {
                    $newarr[$i]['filePath']=$v;
                    $newarr[$i]['src']=$v;
                    $newarr[$i]['id']=$k;

                    $ThisFileInfo = $this->getID3->analyze($v);   //分析文件
                    $newarr[$i]['time']=round($ThisFileInfo['playtime_seconds'],0);         //获取MP3文件时长

                    $i++;
                }
            }
            $data['str']=implode(',',$voice);
            $data['arr']=$newarr;
        }   
        json(0,'成功',$data);
    }

    //律师抢单
    public function lawyerRobOrder(){
        $openid=I('openid');
        $order=I('id');

        $id=M('user')->where(array('openid'=>$openid))->getField('id');
        $order_deal=M('order_deal')->where(array('order_id'=>$order,'user_id'=>$id,'is_user'=>1))->find();
        if(!empty($order_deal)){
            json(0,'请勿重复抢单');
        }else{
            $data['order_id']=$order;
            $data['user_id']=$id;
            $data['create_time']=time();
            //添加之前先判断数据库中是否接单人数达到上限
            //订单对应的套餐需要的人数
            $number=M('combo')->alias('o')->join('db_order r on r.combo_id=o.id')->where(array('r.id'=>$order))->getField('o.number');
            //订单已经抢单的人数
            $have=M('order_deal')->where(array('order_id'=>$order,'is_user'=>1))->count();

            if($number<=$have){
                json(0,'抢单人数已达上限');
            }else{
                $have=$have+1;
                if($number==$have){
                    $orderdata['is_top']=2;
                }else{
                    $orderdata['is_top']=1;
                }
                M('order')->where(array('id'=>$order))->save($orderdata);
                $res=M('order_deal')->add($data);
                if($res){
                    //发信息钩子
                    $time=F('config')['end_time'];
                    $content='您已抢单成功，请在'.$time.'分钟内解答,超过期限所抢订单将失效';
                    $param=array(
                        'user_id'=>$id,
                        'content'=>$content,
                    );
                    \Think\Hook::listen('AddMessage',$param);   

                    json(1,'抢单成功');
                }else{
                    json(0,'抢单失败');
                }
            }   
        }
    }

    //律师详情
    public function lawyerDetail(){

        $id=I('id');
        $data=$this->getLawyerInfo($id);
        $data['user_info']=M('user_info')->where(array('user_id'=>$id))->find();
        $data['user_info']['keywords']=explode(',',$data['user_info']['keywords']);
        if(!empty($data['user_info']['keywords'])){
            foreach ($data['user_info']['keywords'] as $kk=>&$vv) {
                $data['user_info']['keywords'][$kk]=M('dictionary')->where(array('id'=>$vv))->getField('name');
            }
        }

        //判断律师是否关注
        $openid=I('openid');
        $focus=M('user')->where(array('openid'=>$openid))->getField('focus');
        $focus=explode(',',$focus);
        if(in_array($id,$focus)){
            $data['is_focus']=1;
        }else{
            $data['is_focus']=2;
        } 
        json(1,'获取成功！',$data);
    }

    //律师付费咨询的数据
    public function getLawyerAsk(){
        $info['askdes']=F('config')['askdes'];
        $info['askmoney']=F('config')['askmoney'];
        json(0,'获取成功',$info);
    }


    //律师提现审核
    public function lawyerGetMoney(){
        $openid=I('openid');
        $id=M('user')->where(array('openid'=>$openid))->getField('id');
        $money=M('lawyer_money')->where(array('user_id'=>$id))->getField('now_money');
        $data['money']=I('money');
        if($money>=$data['money_log']){
            $data['user_id']=$id;
            $data['create_time']=time();
            $res=M('money_log')->add($data);
            if($res){
                json(1,'提现发起成功，等待审核');
            }else{
                json(0,'提现发起失败');
            }  
        }else{
            json(0,'账户余额不足');
        }       
    }

    //律师解答
    public function lawyerAnswer(){
        $customerinfo['user_name']='zhanghui';
        $lawyerinfo['user_name']='shangsan';
        $lawyerinfo['mobile']='18095103554';
        $customerphone='18095103554';

        $orderinfo['user_id']=16;
        $message="{custemer:".$customerinfo['user_name'].",layer:".$lawyerinfo['user_name'].",layertel:".$lawyerinfo['mobile']."}";
        sendMessage('SMS_151085462',$customerphone,$message,$orderinfo['user_id']);
        exit;
        $openid=I('openid');
        $order=I('id');

        $data['deal_type']=I('tp');
        $data['deal_time']=time();
		$data['answer_status']=1;
        $user_id=M('order')->where(array('id'=>$order))->getField('user_id');
        $id=M('user')->where(array('openid'=>$openid))->getField('id');

        $answer=M('answer');
        $voice=M('voice');

        if(I('tp')==1){
            $data['deal_content']=I('content','','');
            $res=M('order_deal')->where(array('user_id'=>$id,'order_id'=>$order))->field('deal_content,deal_time,deal_type,answer_status')->save($data);
        }else{
            $res=M('order_deal')->where(array('user_id'=>$id,'order_id'=>$order))->field('deal_time,deal_type,answer_status')->save($data);

            $data2['user_id']=$id;
            $data2['type']=1;
            $data2['create_time']=time();
            $data2['content_id']=$order;
            $content=I('content');
            $content=explode(',',$content);
            foreach ($content as $k => &$v) {
                $file=explode('_',$v);
                $data2['path']=$file[0];
                $data2['duration']=$file[1];
                $voice->add($data2);
            }
        }

        //判断订单的实际需要人数和解答的人数，如果相等的话就改变订单的状态status
        $combo_id=M('order')->where(array('id'=>$order))->getField('combo_id');
        $number=M('combo')->where(array('id'=>$combo_id))->getField('number');
        //现有订单的已经解答的人数
        $where['answer_status']=1;
        $where['is_user']=1;
		$where['order_id']=$order;
        $deal_count=M('order_deal')->where($where)->count();
        if($deal_count==$number){
            M('order')->where(array('id'=>$order))->setField(array('status'=>3));
        }

        if($res){
            //对律师发信息钩子
            $mobile=M('user')->where(array('id'=>$user_id))->getField('mobile');
            $content='您已为手机号码为 '.$mobile.' 的用户解答成功，佣金将在用户评价后分配到您的账户中。';
            $param=array(
                'user_id'=>$id,
                'content'=>$content,
            );
            \Think\Hook::listen('AddMessage',$param); 

            //用户发信息钩子
            $lawyer_info=M('user')->where(array('id'=>$id))->field('mobile,user_name')->find();
            $content='您的订单已经有律师为您解答，解答律师'.$lawyer_info['user_name'].'律师的电话为 '.$lawyer_info['mobile'].' 如有需要请电话联系，请在所有律师解答完成后给律师评价，超过一天系统将自动评价';
            $param=array(
                'user_id'=>$user_id,
                'content'=>$content,
            );
            \Think\Hook::listen('AddMessage',$param);  

            //律师解答后给用户发送通知消息
            $message="{lawyer:".$lawyer_info['user_name'].",tel:".$lawyer_info['mobile']."}";
            sendMessage('SMS_150183941',$mobile,$message,$user_id);

            //律师解答后给律师发送通知消息
            $user_name=M('order')->where(array('id'=>$order))->getField('name');
            $message="{cusertem:".$user_name.",custemtel:".$mobile."}";
            sendMessage('SMS_151090418',$lawyer_info['mobile'],$message,$id);

            json(1,'解答成功');
        }else{
            json(1,'解答失败');
        }
    }

    //问答评论
    public function answerComment(){
        $answer_id=I('answer_id');//问答id

        $openid=I('openid');
        $id=M('user')->where(array('openid'=>$openid))->getField('id');
        $res=M('answer_comment')->where(array('answer_id'=>$answer_id,'user_id'=>$id))->find();
        if($res){//如果有数据则提示
            json(0,'您已有评价记录');
        }else{//没有则评论
            $data['point']=str_replace('分', '', I('point'));
            $data['create_time']=time();
            $data['user_id']=$id;
            $data['answer_id']=$answer_id;
            $res=M('answer_comment')->add($data);

            //给此条问答评分
            $count=M('answer_comment')->where(array('answer_id'=>$answer_id))->field('point')->count();

            $sum=M('answer_comment')->where(array('answer_id'=>$answer_id))->field('point')->sum('point');
            $point=round(($sum/$count),2);
            M('answer')->where(array('id'=>$answer_id))->setField(array('comment_point'=>$point));   
            if($res){
                json(1,'评价成功');
            }else{
                json(0,'评价失败');
            }
        }
    }

    //问答语音列表
    public function answerListDetail(){
        $order_id=I('order_id');
        $lawyer_id=I('lawyer_id');

        $openid=I('openid');
        $user_id=M('user')->where(array('id'=>$openid))->getField('id');
		
		$answer=M('answer')->where(array('id'=>$order_id))->field('type,content')->find();
		if($answer['type']==1){
            $answer['content']=html_entity_decode($answer['content']);
			$list[0]=$answer;
			$list['type']=1;
		}else{
			$where['type']=2;
			$where['content_id']=I('order_id');
			$where['user_id']=I('lawyer_id');
			$list=M('voice')->where($where)->select();

			if($list){
				foreach ($list as $k => $v) {
					$list[$k]['id']=$k;
					$ThisFileInfo = $this->getID3->analyze('.'.$v['path']);   //分析文件
					$list[$k]['time']=round($ThisFileInfo['playtime_seconds'],0);              //获取MP3文件时长
				}
			}else{
				$list=null;
			}
			$list['type']=2;
		}

        //判断用户是否可评论
        if($lawyer_id!=$user_id){
            $comment=M('answer_comment')->where(array('user_id'=>$user_id,'answer_id'=>$order_id))->find();
            if(empty($comment)){//如果没有记录则可评论
                $data['comment']=1;
            }else{//不可评论
                $data['comment']=0;
            }
        }
           
        $lawyer = D('User')->alias('a')
            ->join('db_user_info o on o.user_id='.I('lawyer_id'))
            ->join('db_check_info i on i.user_id='.I('lawyer_id'))
            ->where(array('a.id'=>I('lawyer_id')))->find();

        $lawyer['user_name']=M('user')->where(array('id'=>$lawyer['user_id']))->getField('user_name');
        $lawyer['headimg']=M('check_info')->where(array('user_id'=>$lawyer['user_id']))->getField('headimg');
        $lawyer['combo_price']=M('combo')->where(array('id'=>$lawyer['combo_id']))->getField('price');
        $lawyer['combo_number']=M('combo')->where(array('id'=>$lawyer['combo_id']))->getField('number');
        $lawyer['people']=M('order_deal')->where(array('user_id'=>$lawyer['user_id']))->count();    
        $lawyer['keywords']=explode(',',$lawyer['keywords']);
        foreach ($lawyer['keywords'] as $kk=>&$vv) {
            $lawyer['keywords'][$kk]=M('dictionary')->where(array('id'=>$vv))->getField('name');
        }

        $data['deal']=$deal;
        $data['list']=$list;
        $data['lawyer']=$lawyer;
        $data['user_id']=$user_id;

        if($data){
            json(1,'获取成功',$data);
        }else{
            json(1,'获取成功');
        }
    }

    //问答订单回答语音列表
    public function answerList(){
        $order_id=I('order_id');
        $lawyer_id=I('lawyer_id');

        $openid=I('openid');
        $user_id=M('user')->where(array('openid'=>$openid))->getField('id');
        if(I('order_id') && I('lawyer_id')){
            $where['content_id']=I('order_id');
            $where['user_id']=I('lawyer_id');
            $where['type']=1;
            $deal=M('order_deal')->where(array('order_id'=>I('order_id'),'user_id'=>$lawyer_id))->find();
            $list=M('voice')->where($where)->select();
        }else{
            $where['type']=2;
            $where['content_id']=I('answer_id');
            $where['user_id']=I('lawyer_id');
            $list=M('voice')->where($where)->select();
        }
        if($list){
            foreach ($list as $k => $v) {
                $list[$k]['id']=$k;
                $ThisFileInfo = $this->getID3->analyze('.'.$v['path']);   //分析文件
                $list[$k]['time']=round($ThisFileInfo['playtime_seconds'],0);         //获取MP3文件时长
            }
        }
            
        $lawyer = D('User')->alias('a')
            ->join('db_user_info o on o.user_id='.I('lawyer_id'))
            ->join('db_check_info i on i.user_id='.I('lawyer_id'))
            ->where(array('a.id'=>I('lawyer_id')))->find();
        $lawyer['people']=M('order_deal')->where(array('user_id'=>I('lawyer_id')))->count();    
        $lawyer['user_name']=M('user')->where(array('id'=>I('lawyer_id')))->getField('user_name');
        $lawyer['headimg']=M('check_info')->where(array('user_id'=>$lawyer['user_id']))->getField('headimg');
        $lawyer['combo_price']=M('combo')->where(array('id'=>$lawyer['combo_id']))->getField('price');
        $lawyer['combo_number']=M('combo')->where(array('id'=>$lawyer['combo_id']))->getField('number');
        $lawyer['keywords']=explode(',',$lawyer['keywords']);
        foreach ($lawyer['keywords'] as $kk=>&$vv) {
            $lawyer['keywords'][$kk]=M('dictionary')->where(array('id'=>$vv))->getField('name');
        }

        $data['deal']=$deal;
        $data['list']=$list;
        $data['lawyer']=$lawyer;
        $data['user_id']=$user_id;

        if($data){
            json(1,'获取成功',$data);
        }else{
            json(1,'获取成功');
        }
    }

    //用户评价
    public function userComment(){
        //用户评价，如果本单律师已经全部评论则给用户分配佣金
        $order=I('id');//订单id
        $lawyer=I('lawyer_id');//所评价的律师id

        $data['point']=str_replace('分', '', I('point'));
        $data['status']=2;
        $data['comment_time']=time();

        //用户评分之后更新律师的好评分数
        $orderdealModel=M('order_deal');
        $userModel=M('user');
        $orderdealModel->startTrans();

        $res1=$orderdealModel->where(array('order_id'=>$order,'user_id'=>$lawyer))->field('point,status,comment_time')->save($data);
        $res1=$orderdealModel->where(array('order_id'=>$order,'user_id'=>$lawyer))->find();

        //查询律师所有的好评分数之后更新user表中的相关字段
        $sum=$orderdealModel->where(array('user_id'=>$lawyer))->sum('point');
        $dealWhere['user_id']=$lawyer;
        $dealWhere['point']=array('neq','');
        $sumCount=$orderdealModel->where($dealWhere)->count();
        
        $saveUser['comment_point']=round($sum/$sumCount,2);
        $res2=$userModel->where(array('id'=>$lawyer))->save($saveUser);

        if($res1 && $res2){
            $orderdealModel->commit();
        }else{
            $orderdealModel->rollback();
        }

        //订单所需人数
        $combo=M('combo')->alias('o')
            ->join('db_order r on r.combo_id=o.id')
            ->where(array('r.id'=>$order))
            ->field('o.number,r.money')
            ->find();

        $number=$combo['number'];
        $money=$combo['money'];
        $percent=F('config')['orderpercent'];

        //订单已经评论人数
        $comment_count=M('order_deal')->where(array('order_id'=>$order,'status'=>2))->count();

        //如果订单评论人数达到订单所需人数，则分配佣金
        if($comment_count>=$number){
            $comment_point=M('order_deal')->where(array('order_id'=>$order,'status'=>2))->sum('point');
            $save['comment_point']=round($comment_point/$comment_count,1);

            M('order')->where(array('id'=>$order))->save($save);
            $percent=1-(0.01*$percent);

            $Morde=M('order_deal');
            $Mcold=M('lawyer_cold');
            $Morde->startTrans();
            $order_deal=$Morde->where(array('order_id'=>$order,'status'=>2))->select();
            $point=$Morde->where(array('order_id'=>$order,'status'=>2))->sum('point');
            foreach ($order_deal as $k => &$v) {
                $pei['get_money']=round($percent*$money*($v['point']/$point),2);
                $pei['money_status']=2;
                $give_money=$Morde->where(array('id'=>$v['id']))->field('get_money,money_status')->save($pei);
                
                $cold['user_id']=$v['user_id'];
                $cold['order_id']=$v['order_id'];
                $cold['money']=$pei['get_money'];
                $cold['create_time']=time();
				$cold['source']=1;
                $cold_give=$Mcold->add($cold);
                if($give_money && $cold_give){
                    //发信息钩子
                    $time=F('config')['cashday'];
                    $content='您的佣金已分配到您的账户，在'.$time.'天之后可以提现';
                    $param=array(
                        'user_id'=>$v['user_id'],
                        'content'=>$content,
                    );
                    \Think\Hook::listen('AddMessage',$param);  

                    $Morde->commit();
                }else{
                    $Mcold->rollback();
                }
            }
            
        }

        if($res1){
            json(1,'评价成功');
        }else{
            json(0,'评价失败');
        }
    }

    //问答详情
    public function answerDetail(){
        $id=I('id');//问答id
        $openid=I('openid');
        $type=I('tp');
		
		//判断是否关注
        $focus=M('user')->where(array('openid'=>$openid))->getField('answer');
        $focus=explode(',',$focus);
        if(in_array($id,$focus)){
            $data['is_focus']=1;
        }else{
            $data['is_focus']=2;
        } 
		
        $user_id=M('user')->where(array('openid'=>$openid))->getField('id');//用户id
        $answer=D('answer')->where(array('id'=>$id))->field('id,title,money,type,desc,content,user_id,answer_type,first,second')->find();
        if($type==1){
            if($answer['type']=1){//如果是文本的话截取
                $answer['content']=cut_str($answer['content'],50);
            }
            
        }
        if($answer['type']==2){
            $answer['voice']=M('voice')->where(array('type'=>2,'content_id'=>$answer['id']))->field('id,path,duration')->select();
            foreach ($answer['voice'] as $k => &$v) {
                $ThisFileInfo = $this->getID3->analyze('.'.$v['path']);   //分析文件
                $v['time']=round($ThisFileInfo['playtime_seconds'],0);         //获取MP3文件时长
            }
        }
        $data['answer']=$answer;
        $data['answer']['first']=M('dictionary')->where(array('id'=>$data['answer']['first']))->getField('name');
        $data['answer']['second']=M('dictionary')->where(array('id'=>$data['answer']['second']))->getField('name');
        $data['answer']['money']=M('dictionary')->where(array('id'=>$data['answer']['money']))->getField('name');

        //自动加1
        D('answer')->where(array('id'=>$id))->setInc('scan',1);
        $where['user_id']=$user_id;
        $where['type']=3;
        $where['content_id']=$id;
        //判断是否购买问答
        $is_buy=M('pay_log')->where($where)->find();
        if($is_buy){
            $data['answer']['buy_status']=1;
        }else{
            $data['answer']['buy_status']=0;
        }       

        $lawyer_id=$data['answer']['user_id'];
        $data['lawyer']=$this->getLawyerInfo($lawyer_id);

        $answer_type=$data['answer']['answer_type'];
        //$anWhere['answer_type']=array('like','%'.$answer_type.'%');
        $anWhere['id']=array('not in',$id);    
        $anWhere['check_status']=2;    
        $data['list']=D('Answer')->where($anWhere)->limit(5)->order('id desc')->select();  
        foreach ($data['list'] as &$v) {
            $v['user_name']=M('user')->where(array('id'=>$v['user_id']))->getField('user_name');
            $v['headimg']=M('check_info')->where(array('user_id'=>$v['user_id']))->getField('headimg');
            $v['first']=M('dictionary')->where(array('id'=>$v['first']))->getField('name');
            $v['second']=M('dictionary')->where(array('id'=>$v['second']))->getField('name');
            $v['answer_type']=explode(',',$v['answer_type']);
        }
        $data['user_id']=$user_id;
        json(1,'获取成功！',$data);
    }

    //咨询详情
    public function orderDetail(){
        $id=I('id');
        $openid=I('openid');
        $user=M('user')->where(array('openid'=>$openid))->find();//用户id
		
		//判断是否关注
        $focus=$user['order'];
        $focus=explode(',',$focus);
        if(in_array($id,$focus)){
            $data['is_focus']=1;
        }else{
            $data['is_focus']=2;
        } 
		
        $data['order']=D('Order')->where(array('id'=>$id))->field(true)->find();
        
        $data['order']['des']=str_replace('&amp;nbsp;','',$data['order']['des']);
        $data['order']['cate']=M('dictionary')->where(array('id'=>$data['order']['cate']))->getField('name');
        $data['order']['cate_er']=M('dictionary')->where(array('id'=>$data['order']['cate_er']))->getField('name');

        //判断进入页面人的身份
        if($user['status']!=2){//不是律师
            //判断是否是下单客户
            if($data['order']['user_id']==$user['id']){//是下单客户
                $data['userStatus']=1;
            }else{//不是下单客户
                $data['userStatus']=2;
                $data['orderStatus']=1;
            }
        }else{//是律师
            //判断是否是接单律师
            $is_order=M('order_deal')->where(array('user_id'=>$user['id'],'order_id'=>$id,'is_user'=>1))->find();
            if($is_order){//是接单律师

                $comboId=M('order')->where(array('id'=>$id))->getField('combo_id');
                $comboCount=M('combo')->where(array('id'=>$comboId))->getField('number');//订单需要的人数
                if($comboCount>1){
                    $data['orderStatus']=1;
                }
                if(empty($is_order['deal_time'])){//律师未处理
                    $data['lawyerStatus']=1;
                }else{//律师已处理
                    $data['lawyerStatus']=2;
                }
            }else{//不是接单律师
                //判断订单是否可抢
                $orderCount=M('order_deal')->where(array('order_id'=>$id,'is_user'=>1))->count();//已经抢单的有效律师
                $comboId=M('order')->where(array('id'=>$id))->getField('combo_id');
                $comboCount=M('combo')->where(array('id'=>$comboId))->getField('number');//订单需要的人数
                $data['orderCount']=$orderCount;
                $data['comboCount']=$comboCount;
                if($orderCount>=$comboCount){//如果抢单人数够了
                    $data['orderStatus']=1;
                }else{//抢单人数不够，律师允许抢单
                    $data['orderStatus']=2;
                }
				
            }
			if($data['order']['user_id']==$user['id']){//是下单
                $data['userStatus']=1;
            }else{//不是下单
                $data['userStatus']=2;
            }
        }

        //判断订单的状态
        if($data['order']['status']==3){//订单处理完成
            $data['dealStatus']=1;
        }else{//订单处理中
            $data['dealStatus']=2;
        }
        $where['user_id']=$user['id'];
        $where['type']=array('in',array(1,2));
        $where['content_id']=$id;
        //判断是否购买问答
        $is_buy=M('pay_log')->where($where)->find();
        if($is_buy){
            $data['answer']['buy_status']=1;
        }else{
            $data['answer']['buy_status']=0;
        }

        $lawyer=M('order_deal')->where(array('order_id'=>$id,'is_user'=>1))->field('user_id,deal_type,deal_content')->select();

        if($lawyer['deal_type']==2){
            $lawyer['voice']=M('voice')->where(array('type'=>1,'content_id'=>$lawyer['id']))->field('id,path,duration')->select();
            foreach ($answer['voice'] as $k => &$v) {
                $ThisFileInfo = $this->getID3->analyze('.'.$v['path']);   //分析文件
                $v['time']=round($ThisFileInfo['playtime_seconds'],0);         //获取MP3文件时长
            }
        }
        
        foreach ($lawyer as &$v) {
            $v['company']=M('check_info')->where(array(array('user_id'=>$v['user_id'])))->getField('company');
            $v['user_name']=M('user')->where(array('id'=>$v['user_id']))->getField('user_name');
            $v['comment_point']=M('user')->where(array('id'=>$v['user_id']))->getField('comment_point');
            $v['headimg']=M('check_info')->where(array('user_id'=>$v['user_id']))->getField('headimg');
            if($v['user_id']==$user_id){
                $v['buy_status']=1;
            }else{
                $v['buy_status']=0;
            }
        }
        $data['lawyer']=$lawyer;
        $data['user']=$user;
        $data['money']=F('config')['relistenmoney'];
		
        // dump($data);
        json(1,'获取成功',$data);
    }
	
	//分享立减
	public function toReduce(){
		$share['user_id']=M('user')->where(array('openid'=>I('openid')))->getField('id');
		$share['create_time']=time();
		M('share')->add($share);
	}

    //套餐列表
    public function getComboList(){
        $where['status']=1;

        $count= D('Combo')->where($where)->count();
        $Page   =   new \Org\Util\UIPage($count,10);
        $data['list'] = D('Combo')->where($where)
                ->field('id,title,des,number,price')
                ->order('id asc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $data['agency']=F('config')['agency'];

		$time1=strtotime(date('Y-m-d',time()));
		$time2=strtotime(date('Y-m-d',time()). ' 23:59:59');
	
		$share['create_time']=array('between',array($time1,$time2));
		$share['user_id']=M('user')->where(array('openid'=>I('openid')))->getField('id');
	
		$sharedes=M('share')->where($share)->find();
		if(!empty($sharedes)){
			$data['share_money']=0;
			$data['reduce_money']=F('config')['share_money'];
		}else{
			$data['share_money']=F('config')['share_money'];
		}
        json(1,'获取成功！',$data);
    }

    //消息列表
    public function getMessageList(){
        $openid=I('openid');
        $where['user_id']=M('user')->where(array('openid'=>$openid))->getField('id');
        $count= D('Message')->where($where)->count();
        $Page   = new \Org\Util\UIPage($count,10);
        $data = D('Message')->where($where)
                ->order('id desc')->limit(15)->select();
        foreach ($data as $k => &$v) {
            $v['create_time']=date('Y-m-d H:i',$v['create_time']);
        }
        if(!$data) $data = null;
        json(1,'获取成功！',$data);
    }

    //消息详情
    public function getMessageDetail(){
        $id=I('id');
        $data=D('Message')->where(array('id'=>$id))->find();
        $data['create_time']=date('Y-m-d H:i',$data['create_time']);
        $info['read_time']=time();
        $info['status']=2;
        D('Message')->where(array('id'=>$id))->save($info);
        json(1,'获取成功！',$data);
    }

    //设置会员信息
    public function setUserInfo(){
        $openid=I('openid');
        $user_info=$this->getUserInfo($openid);

        $data=I('');

        //检测手机验证码
        $code=M('user_code')->where(array('user_id'=>$user_info['id']))->order('id desc')->find();
        if($code['end_time']<time()){
            json(0,'验证码过期',$code);
        }
        if($code['phone']!=$data['mobile']){
            json(0,'手机号码不匹配');
        }
        if($code['code']!=$data['code']){
            json(0,'验证码错误',$data);
        }

        $data1['mobile']=I('mobile');
        $data1['type']=2;
        $data1['user_name']=I('name');
        $user=M('user');
        $uInfo=M('user_info');

        $user->startTrans();
        $res1=$user->where(array('id'=>$user_info['id']))->save($data1);
        //检测有的话更新，没有的添加
        if($uInfo->where(array('user_id'=>$user_info['id']))->find()){
            $res2=$uInfo->where(array('user_id'=>$user_info['id']))->save($data);
        }else{
            $data['user_id']=$user_info['id'];
            $data['create_time']=time();
            $res2=$uInfo->add($data);
        }
        if($res2){
            $user->commit();
            json(1,'会员信息设置成功！');
        }else{
            $user->rollback();
            json(0,'会员信息设置失败');
        }
    }

    //发送手机验证码
    public function getPhoneCode(){
        $openid=I('openid');
        $user_info=$this->getUserInfo($openid);
        //检测是否发送过信息
        $info=M('user_code')->where(array('user_id'=>$user_info['id']))->find();
        if(!empty($info)){
            if($info['expire_time']>time()){
                json(0,'操作过于频繁');
            }
        }
        
        $mobile=I('mobile');
        vendor('Sms.TopClient');
        vendor('Sms.AlibabaAliqinFcSmsNumSendRequest');
        $c = new \TopClient;
        $c ->appkey = '24661147' ;
        $c ->secretKey = '1e78cd2f07848c70b1847b9572ff948f';
        $req = new \AlibabaAliqinFcSmsNumSendRequest;
        $verify=rand(123456, 999999);
        $req ->setExtend( "" );
        $req ->setSmsType( "normal" );
        $req ->setSmsFreeSignName( "身份验证" );
        $req ->setSmsParam( "{code:'$verify',product:'律师去哪了'} ");
        $req ->setRecNum( "$mobile" );
        $req ->setSmsTemplateCode( "SMS_34700330" );
        $resp = $c ->execute( $req );
        $success=$resp->result->success;
        $error=$resp->sub_msg;
        if($success){
            $code['create_time']=time();
            $code['expire_time']=time()+90;
            $code['end_time']=time()+300;
            $code['user_id']=$user_info['id'];
            $code['phone']=$mobile;
            $code['code']=$verify;
            M('user_code')->add($code);
            json(1,'验证码发送成功');
        }else{
            json(0,'验证码发送失败');
        }
    }

    //检测律师是否是认证会员
    public function checkLawyer(){
        $openid=I('openid');
        $user_info=$this->getUserInfo($openid);
        if($user_info['type']==1){
            json(0,'请先验证手机号码！');
        }else{
            if($user_info['status']==2){
                json(2,'已经是认证律师');
            }else{
                if($user_info['status']==0 || $user_info['status']==3){
                    json(1,'允许认证');
                }elseif($user_info['status']==1){
                    json(3,'已经提交过认证资料，请等待审核');
                }  
            }
        }
    }

    //律师申请认证
    public function lawyerCheck(){
        $data=I('');
        $openid=I('openid');
        $user_info=$this->getUserInfo($openid);

        $data1['identity']=2;
        $data1['apply_time']=time();
        $data1['status']=1;

        $user=M('user');
        $uInfo=M('check_info');
        $user->startTrans();
        $res1=$user->where(array('id'=>$user_info['id']))->save($data1);
        //检测有的话更新，没有的添加

        if($uInfo->where(array('user_id'=>$user_info['id']))->find()){
            $data['create_time']=time();
            $res2=$uInfo->where(array('user_id'=>$user_info['id']))->save($data);
        }else{
            $data['user_id']=$user_info['id'];
            $data['create_time']=time();
            $res2=$uInfo->add($data);
        }

        if($res1 && $res2){
            $user->commit();
            json(1,'提交成功，请等待审核');
        }else{
            $user->rollback();
            json(0,'提交失败，请重试');
        }

    }

    //上传图片接口
    public function uploadImg(){
        $res=uploadFile();
    }

    //上传MP3接口
    public function uploadVoice(){
        $res=uploadVoice();
    }

    //获取问答金额
    public function getAnswerMoney(){
        //问答价格
        $money=M('dictionary')->where(array('type'=>1))->field('id,name as money')->select();
        //问答类别
        $cate=M('dictionary')->where(array('type'=>2,'level'=>1))->field('id,name')->select();
        foreach ($cate as $k => &$v) {
            $cate[$k]['er']=M('dictionary')->where(array('type'=>2,'level'=>2,'pid'=>$v['id']))->field('id,name')->select();
        }
        $data['money_list']=$money;
        $data['cate_list']=$cate;
        json(0,'获取成功！',$data);
    }

    //发布问答接口
    public function setAnswer(){
        $openid=I('openid');
        $id=M('user')->where(array('openid'=>$openid))->getField('id');

        $answer=M('answer');
        $voice=M('voice');
        $data['title']=I('title');
        $data['money']=I('money');
        $data['first']=I('first');
        $data['second']=I('second');
        $data['type']=I('tp');
        $data['create_time']=time();
        $data['user_id']=$id;

        if(I('tp')==1){
            $data['content']=I('content','','');
            $res=$answer->add($data);
        }else{
            $res=$answer->add($data);

            $data2['user_id']=$id;
            $data2['type']=2;
            $data2['create_time']=time();
            $data2['content_id']=$res;
            $content=I('content');
            $content=explode(',',$content);
            foreach ($content as $k => &$v) {
                $file=explode('_',$v);
                $data2['path']=$file[0];
                $data2['duration']=$file[1];
                $voice->add($data2);
            }
        }
            
        if($res){
            json(1,'添加成功，请等待审核！');
        }else{
            json(0,'添加失败');
        }
    }

    //订单解答
    public function setOrderAnswer(){
        $openid=I('openid');
        $user_id=M('user')->where(array('openid'=>$openid))->getField('id');
        $id=I('id');

        $data['deal_type']=I('tp');
        $data['user_id']=$user_id;
        $data['deal_time']=time();

        $order=M('order_deal')->where(array('order_id'=>$id,'user_id'=>$user_id))->find();
        if($order){
            if(I('tp')==1){//添加文本
                $data['deal_content']=I('content','','');
                $res=M('order_deal')->where(array('order'=>$id,'user_id'=>$user_id))->field(true)->save($data);
            }else{//添加语音
                $res=M('order_deal')->where(array('order'=>$id,'user_id'=>$user_id))->field(true)->save($data);
                $data2['user_id']=$user_id;
                $data2['type']=1;
                $data2['create_time']=time();
                $data2['content_id']=$id;
                $content=I('content');
                $content=explode(',',$content);
                foreach ($content as $k => &$v) {
                    $file=explode('_',$v);
                    $data2['path']=$file[0];
                    $data2['duration']=$file[1];
                    M('voice')->add($data2);
                }
            }
            
            if($res){
                json(1,'添加成功');
            }else{
                json(0,'添加失败');
            }
        }else{
            json(0,'订单不存在');
        }
            
    }

    //获取学校列表接口
    public function getAllSchool(){
        $provinces = M('province_info')->field('pr_id,pr_province')->select();
        $province = array();
        foreach ($provinces as $k => $v) {
            $citys = M('city_info')->where(array('ci_province'=>$v['pr_id']))->select();
            $city = array();
            foreach ($citys as $kv => $vv) {
                $shools=M('shool_info')->where(array('sh_city'=>$vv['ci_id']))->select();
                $shool=array();
                foreach ($shools as $km => $vm) {
                    array_push($shool,$vm['sh_shool']);
                }
                $city[$kv][$vv['ci_city']]=$shool;
            }
            $province[$k][$v['pr_province']] = $city;
        }  

        echo json_encode($province);
    }

    //获取问答类型
    public function getAllKeywords(){

        $ones=M('dictionary')->where(array('type'=>2,'level'=>1))->field('id,name')->select();
        $one=array();
        foreach ($ones as $k => &$v) {
            $twos=M('dictionary')->where(array('type'=>2,'level'=>2,'pid'=>$v['id']))->field('id,name')->select();
            $two=array();
            foreach ($twos as $key => $value) {
                array_push($two,$value['name']);
            }

            $one[$k][$v['name']] = $two;
        }
        echo json_encode($one);
    }

    public function getProvince(){
        $province=M('province_info')->field('pr_id,pr_province')->select();
        json(1,'获取成功',$province);
    }

    public function getCity(){
        $province=I('province');
        $id=M('province_info')->field('pr_id')->where(array('pr_province'=>$province))->getField('pr_id');
        $city=M('city_info')->where(array('ci_province'=>$id))->select();
        json(1,'获取成功',$city);
    }

    public function getSchool(){
        $city=I('city');
        $id=M('city_info')->where(array('ci_city'=>$city))->getField('ci_id');
        $school=M('shool_info')->where(array('sh_city'=>$id))->select();
        json(1,'获取成功',$school);
    }

    //获取律师和会员的信息
    public function getUser(){
        $openid=I('openid');
        $info=M('user')->where(array('openid'=>$openid))->find();

        $check=M('user_info')->where(array('user_id'=>$info['id']))->find();

        if($info['identity']==2 && $info['status']==2){
            $info['answer_num']=M('answer')->where(array('user_id'=>$info['id']))->count();
        }

        $userKeywords=M('check_info')->where(array('user_id'=>$info['id']))->getField('keywords');
        $check_info=M('check_info')->where(array('user_id'=>$info['id']))->find();
        $userKeywords=explode(',',$userKeywords);
        if(!empty($userKeywords)){
            foreach ($userKeywords as $kk=>&$vv) {
                $userKeywords[$kk]=M('dictionary')->where(array('id'=>$vv))->getField('name');
            }
        }


        $money=M('lawyer_money')->where(array('user_id'=>$info['id']))->field('now_money')->find();
        
        $money['wait_money']=M('lawyer_cold')->where(array('user_id'=>$info['id'],'status'=>0))->sum('money');
        $money['now_money']=round($money['now_money']);
        $money['wait_money']=round($money['wait_money']);

        $cashnum=F('cofig')['cashnum'];
        //发布的问答数量
        $data['userKeywords']=$userKeywords[0] == null ? null:$userKeywords; 
        $data['base']=$info;
        $data['check']=$check;
        $data['money']=$money;
        $data['check_info']=$check_info;
        $data['cashnum']=$cashnum;
        json(0,'获取信息成功',$data);
    }

    //获取标签接口
    public function getKeywords(){
        $openid=I('openid');
        $id=M('user')->where(array('openid'=>$openid))->getField('id');
        $data=M('dictionary')->where(array('type'=>2,'level'=>1))->field('id,name')->select();
        foreach ($data as $k => &$v) {
            $data[$k]['er']=M('dictionary')->where(array('type'=>2,'level'=>2,'pid'=>$v['id']))->field('id,name')->select();
        }
        $array['data']=$data;
        $keywords=M('check_info')->where(array('user_id'=>$id))->getField('keywords');
        $keywords=explode(',',$keywords);
        $array['keywords']=$keywords;
        json(0,'获取信息成功',$array);
    }

    //获取

    //律师添加标签
    public function setKeywords(){
        $key=I('id');
        $openid=I('openid');
        $id=M('user')->where(array('openid'=>$openid))->getField('id');
        $keywords=M('check_info')->where(array('user_id'=>$id))->find();
        if(empty($keywords)){
            $info['user_id']=$id;
            $info['keywords']=$key;
            $res=M('check_info')->add($info);
            if($res){
                $data['keywords']=$key;
                json(1,'添加成功', array_merge($data,array()));
            }else{
                json(0,'添加失败', array_merge($data,array()));
            }
        }else{
            $keywords=M('check_info')->where(array('user_id'=>$id))->getField('keywords');
            $keywords=explode(',',$keywords);
            $keywords=$this->delByValue($keywords,'');

            if(in_array($key,$keywords)){
                $keywords=$this->delByValue($keywords,$key);
            }else{
                array_push($keywords,$key);
            }
            $data['keywords']=$keywords;
            //获取擅长领域可选个数
            $num=F('config')['keynum'];
            $count=count($keywords);
            if($count>$num){
                json(2,'最多'.$num.'个标签',$keywords);
            }

            $keywords=implode(',',$keywords);
            $res=M('check_info')->where(array('user_id'=>$id))->setField(array('keywords'=>$keywords));
            $keywords=explode(',',$keywords);
            if($res){
                json(1,'操作成功', $keywords);
            }else{
                json(0,'操作失败', $keywords);
            }
        }        
    }

    //律师接单统计
    public function getOrderList(){
        $openid=I('openid');
        $user_info=$this->getUserInfo($openid);
        $where['user_id']=$user_info['id'];

        //今日接单统计
        $time1=strtotime(date('Y-m-d',time()));
        $time2=strtotime(date('Y-m-d',time()).' 23:59:59');
        $where['create_time']=array('between',array($time1,$time2));
        $today=$this->getOrderListFunction($where);

        
        //昨日接单统计
        $time1=strtotime(date("Y-m-d",strtotime("-1 day")));
        $time2=strtotime(date("Y-m-d",strtotime("-1 day")).' 23:59:59');
        $where['create_time']=array('between',array($time1,$time2));
        $yesterday=$this->getOrderListFunction($where);
        
        //本周接单统计
        $time1=strtotime(date('Y-m-d',time()-date('w',time())*60*60*24));
        $time2=strtotime(date('Y-m-d',time()+(7-date('w',time()))*60*60*24).' 23:59:59');
        $where['create_time']=array('between',array($time1,$time2));
        $week=$this->getOrderListFunction($where);
        
        //本月接单统计
        $time1=mktime(0,0,0,date('m'),1,date('Y'));
        $time2=mktime(23,59,59,date('m'),date('t'),date('Y'));
        $where['create_time']=array('between',array($time1,$time2));
        $month=$this->getOrderListFunction($where);

        //其他接单统计
        $time1=mktime(0,0,0,date('m'),1,date('Y'));
        $where['create_time']=array('lt',$time1);
        $other=$this->getOrderListFunction($where);

        $data['today']=$today;
        $data['yesterday']=$yesterday;
        $data['week']=$week;
        $data['month']=$month;
        $data['other']=$other;
        $data['user_info']=$user_info;
        json(1,'获取接单记录成功',$data);
    }

    function getOrderListFunction($where){
        $data['sum']=M('order_deal')->where($where)->sum('get_money');
        $data['count']=M('order_deal')->where($where)->count();
        $data['list']=M('order_deal')->field('id,order_id,deal_time,create_time,status,user_id')->order('deal_time asc')->where($where)->select();
        foreach ($data['list'] as $k => &$v) {
            if(empty($v['deal_time'])){
                $v['orderDeal']=1;
            }else{
                $v['orderDeal']=2;
            }
            
            $v['order_info']=M('order')->field('id,name,cate,type,markmoney,mstatus,province,city,area,create_time,money')->where(array('id'=>$v['order_id']))->find();
            $v['order_info']['cate']=M('dictionary')->where(array('id'=>$v['order_info']['cate']))->getField('name');
            if(empty($v['deal_time'] && $v['is_user']==1)){
                $v['newid']=$v['order_info']['id'];
            }
        }
        return $data;
    }   

    //获取律师的问答记录
    public function getLawyerAnswer(){
        if(I('openid')){
            $openid=I('openid');
            $id=M('user')->where(array('openid'=>$openid))->getField('id');
            $where['user_id']=$id;
        }

        if(I('user_id')){
            $user_id=I('user_id');
            $id=I('user_id');
            $where['user_id']=I('user_id');
        }
            
        $data=M('answer')->where($where)->order('id desc')->field('id,user_id,title,first,second,scan')->select();
        if(!empty($data)){
            foreach ($data as $key => &$value){
                $data[$key]['user_name']=M('user')->where(array('id'=>$id))->getField('user_name');
                $data[$key]['headimg']=M('check_info')->where(array('user_id'=>$id))->getField('headimg');
                $data[$key]['first']=M('dictionary')->where(array('id'=>$value['first']))->getField('name');
                $data[$key]['second']=M('dictionary')->where(array('id'=>$value['second']))->getField('name');
            }
        }else{
            $data=null;
        }   
        json(0,'获取成功',$data);
        
    }

    //关注或取消关注
    public function focusLawyer(){
        $id=I('id');
        $openid=I('openid');
        $type=I('tp');
		if($type==1){//添加或取消咨询的关注
			$list=M("user")->where(array('openid'=>$openid))->getField('order');
			if(empty($list)){
				$save['order']=$id;
			}else{
				$focus=explode(',',$list);
				$focus=$this->delByValue($focus,'');

				if(!in_array($id, $focus)){
					array_push($focus,$id);
					$data['is_focus']=true;
				}else{
					$focus=$this->delByValue($focus,$id);
					$data['is_focus']=false;
				}
				if(empty($focus)){
					$save['order']=('');
				}else{
					$save['order']=implode(',',$focus);
				}
	
			}
			$res=M("user")->where(array('openid'=>$openid))->setField($save);

		}elseif($type==2){//添加或取消问答的关注
			$list=M("user")->where(array('openid'=>$openid))->getField('answer');
			if(empty($list)){
				$save['answer']=$id;
			}else{
			
				$focus=explode(',',$list);
				$focus=$this->delByValue($focus,'');
	
				if(!in_array($id, $focus)){
					array_push($focus,$id);
					$data['is_focus']=true;
				}else{
					$focus=$this->delByValue($focus,$id);
					$data['is_focus']=false;
				}
				if(empty($focus)){
					$save['answer']=('');
				}else{
					$save['answer']=implode(',',$focus);
				}
	
			}
			$res=M("user")->where(array('openid'=>$openid))->setField($save);

		}else{
			$list=M('user')->where(array('openid'=>$openid))->getField('focus');

			if(empty($list)){
				$save['focus']=$id;
			}else{
				$focus=explode(',',$list);
				$focus=$this->delByValue($focus,'');

				if(!in_array($id, $focus)){
					array_push($focus,$id);
					$data['is_focus']=true;
				}else{
					$focus=$this->delByValue($focus,$id);
					$data['is_focus']=false;
				}
				if(empty($focus)){
					$save['focus']=('');
				}else{
					$save['focus']=implode(',',$focus);
				}
	
			}
			

			$res=M('user')->where(array('openid'=>$openid))->setField($save);
		}
		
        if($res){
            json(1,'操作成功',$data);
        }else{
            json(0,'操作失败',$data);
        }
    }

    //我的购买
    public function buyLog(){
        $where['transaction_id']=array('neq',' ');
        
        $openid=I('openid');
        $id=M('user')->where(array('openid'=>$openid))->getField('id');
        //套餐
        $where['user_id']=$id;
        $where['type']=1;
        $order=M('order')->where($where)->order('id desc')->select();

        if(empty($order)){
            $order=null;
        }else{
            foreach ($order as $k=> &$v) {
                //$order[$k]['order_name']=M('combo')->where(array('id'=>$v['combo_id']))->getField('title');
                $v['create_time']=date('Y-m-d H:i',$v['create_time']);
				$v['combo_name']=M('combo')->where(array('id'=>$v['combo_id']))->getField('title');
                if($v['status']==1){
                    $v['status']='待处理';
                }elseif($v['status']==2){
                    $v['status']='待解决';
                }elseif($v['status']==3){
                    $v['status']='已完成';
                }
				$cate=M('dictionary')->where(array('id'=>$v['cate']))->getField('name');
				$cate_er=M('dictionary')->where(array('id'=>$v['cate_er']))->getField('name');
				if(!empty($cate)){
					if(!empty($cate_er)){
						$v['title']=$v['name'].' 咨询 | '.$cate."·".$cate_er;
					}else{
						$v['title']=$v['name'].' 咨询 | '.$cate;
					}
				}else{
					$v['title']=$v['name'].' 咨询';
				}
				
			
            }
        }
            

        //复听
        $listenWhere['user_id']=$id;
        $listenWhere['type']=2;
        $listen=M('pay_log')->where($listenWhere)->order('id desc')->select();
        if(empty($listen)){
            $listen=null;
        }else{
            foreach ($listen as $k => &$v) {
                $pay_log_order=M('order')->where(array('id'=>$v['content_id']))->order('id desc')->find();

				if(!empty($pay_log_order)){
					$v['create_time']=date('Y-m-d H:i',$pay_log_order['create_time']);
					if($pay_log_order['status']==1){
						$v['status']='待处理';
					}elseif($pay_log_order['status']==2){
						$v['status']='待解决';
					}elseif($pay_log_order['status']==3){
						$v['status']='已完成';
					}
					$cate=M('dictionary')->where(array('id'=>$pay_log_order['cate']))->getField('name');
					$cate_er=M('dictionary')->where(array('id'=>$pay_log_order['cate_er']))->getField('name');
					if(!empty($cate)){
						if(!empty($cate_er)){
							$v['title']=$pay_log_order['name'].' 咨询 | '.$cate."·".$cate_er;
						}else{
							$v['title']=$pay_log_order['name'].' 咨询 | '.$cate;
						}
					}else{
						$v['title']=$pay_log_order['name'].' 咨询';
					}
				}
            }
        }
        //问答
        $answerWhere['r.transaction_id']=array('neq',' ');
        $answerWhere['r.user_id']=$id;
        $answerWhere['r.type']=3;
        $answer=M('order')
            ->alias('r')
            ->join('db_answer a on a.id=r.combo_id')
            ->where($answerWhere)
            ->order('r.id desc')
            ->field('a.title,a.id,a.user_id,a.first,a.second,a.answer_type,a.scan,a.comment_point,r.create_time,r.money')
            ->select();

        if(empty($answer)){
            $answer=null;
        }else{
            foreach ($answer as &$v) {
                $v['user_name']=M('user')->where(array('id'=>$v['user_id']))->getField('user_name');
                $v['headimg']=M('check_info')->where(array('user_id'=>$v['user_id']))->getField('headimg');
                $v['first']=M('dictionary')->where(array('id'=>$v['first']))->getField('name');
                $v['second']=M('dictionary')->where(array('id'=>$v['second']))->getField('name');
                $v['answer_type']=explode(',',$v['answer_type']);
                foreach ($v['answer_type'] as $kk=>&$vv) {
                    $v['answer_type'][$kk]=M('dictionary')->where(array('id'=>$vv))->getField('name');
                }
                $v['create_time']=date('Y-m-d H:i',$v['create_time']);
            }
        }
        
        //律师
        foreach ($answer as $k => &$v) {
            $deal=$this->getLawyerInfo($v['user_id']);
            if(!empty($deal)){
                $lawyer[$k]=$deal;
            }
        }
        $data['user_id']=$id;
        $data['order']=$order;
        $data['lawyer']=$lawyer;
        $data['listen']=$listen;
        $data['answer']=$answer;
        json(1,'获取成功',$data);
    }

    //获取单页内容
    public function getArticle(){
        $id=I('id');
        $data=M('article')->where(array('id'=>$id))->field('title,content,create_time')->find();
        $data['content']=str_replace('&amp;nbsp;','',$data['content']);
        $data['create_time']=date('Y-m-d H:i',$data['create_time']);
        json(1,'获取成功',$data);
    }

    //删除数组中指定的元素
    function delByValue($arr, $value){
        $keys = array_keys($arr, $value);
        if(!empty($keys)){
            foreach ($keys as $key) {
                unset($arr[$key]);
            }
        }
        return $arr;
    }

    //openid获取用户信息
    public function getUserInfo($openid){
        $info=M('user')->where(array('openid'=>$openid))->find();
        return $info;
    }

    //获取律师详情
    public function getLawyerInfo($id){
        $data['base']=M('user')
            ->alias('u')
            ->join('db_user_info i on i.user_id='.$id)
            ->where(array('u.id'=>$id))->field('u.user_name,u.focus,u.comment_point,u.id,i.home_pro')->find();
        $data['base']['people']=M('order_deal')->where(array('user_id'=>$data['base']['id']))->count();
        $data['info']=M('check_info')
            ->alias('o')
            ->join('db_user_info i on i.user_id=o.user_id')
            ->where(array('o.user_id'=>$id))->find();

        $keywords=explode(',',$data['info']['keywords']);
        foreach ($keywords as $key => &$value) {
            $keywords[$key]=M('dictionary')->where(array('id'=>$value))->getField('name');
        }
        $data['info']['keywords']=$keywords;
        return $data;
    }

        //首页轮播详情
    public function imgWebDetail(){
        $id=I('id');
        $article=M('article')->where(array('id'=>$id))->find();
        $article['content']=htmlspecialchars_decode($article['content']);
        if($article['is_connect']==1){
            $user=M('user_info')->alias('u')
            ->join('db_check_info c on c.user_id=u.user_id')
            ->where(array('u.user_id'=>$article['lawyer']))
            ->find();
            $user['user_name']=M('user')->where(array('id'=>$user['user_id']))->getField('user_name');
            $data['user']=$user;
        }
        
        $data['article']    =   $article;
        $this->assign('data',    $data);
        $this->display();
    }
}