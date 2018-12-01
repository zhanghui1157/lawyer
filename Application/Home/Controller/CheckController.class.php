<?php
namespace Home\Controller;
use Think\Controller;
header("Content-Type:text/html;charset=utf-8");
header('Access-Control-Allow-Origin:*');
class CheckController extends Controller {

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

    
    //openid获取用户信息
    public function getUserInfo($openid){
        $info=M('user')->where(array('openid'=>$openid))->find();
        return $info;
    }
    //会员id获取用户信息
    public function getUserInfoById($id){
        $info=M('user')->where(array('id'=>$id))->find();
        return $info;
    }
    //获取律师详情
    public function getLawyerInfo(){
		$id=I('id');
        $data['data']=M('user')
            ->alias('u')
            ->join('db_user_info i on i.user_id='.$id)
            ->where(array('u.id'=>$id))->field('u.mobile,u.user_name,u.id,u.status,i.home_pro,i.home_city,i.home_area,i.home_addr')->find();
        if(!empty($data['data'])){
			$data['data']['check_info']=M('check_info')->where(array('user_id'=>$id))->find();
			$data['data']['check_info']['keywords_string']='';
			if(!empty($data['data']['check_info']['keywords'])){
				$string=explode(',',$data['data']['check_info']['keywords']);
				if(!empty($string)){
					$keywords_string='';
					foreach($string as $k=>$v){
					   if($k==0){
						   $keywords_string.=M('dictionary')->where(array('id'=>$v))->getField('name');
					   }else{
						   $keywords_string.=','.M('dictionary')->where(array('id'=>$v))->getField('name');
					   }
				    }
					$data['data']['check_info']['keywords_string']=$keywords_string;
				}
				
			}
			json(1,'获取信息成功', $data['data']);
		}else if(empty($data['data'])){
			json(0,'此人不存在');
		}else{
			json(0,'获取信息失败');
		}
		
    } 
	public function layer_rz(){
		$id=M('user')->where(array('openid'=>I('openid')))->getField('id');
		if(empty($id)){
			json(0,'此人不存在');
		}
		$this->assign('id',$id);
		$this->display();
	}
	public function uploadifythumb() {
       
        $monthdir = strval(date('Ym', time()));
        //图片上传设置
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize   =     31457280 ;// 设置附件上传大小
		$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->rootPath  =      '.'; // 设置附件上传根目录
		$upload->savePath  =      '/Public/UploadPic/'.$monthdir.'/'; // 设置附件上传（子）目录
        $images = $upload->upload();
        //判断是否有图
        if ($images) {
			// 生成缩略图并保存
			foreach ($images as $key => $value) {
				$img=$value['savepath'].$value['savename'];		
				$BinImg =substr($img,1); // 获得原图绝对路径,去掉“/Public/UploadPic/”第一个/
				$image = new \Think\Image();
				$image->open($BinImg);// 打开原图
				$image->thumb(640, 640,\Think\Image::IMAGE_THUMB_FIXED)->save($BinImg);
                $data=$img;
            }
			json(1,'图片上传成功',$data);
        } else {
			json(0,$upload->getError());
        }
    }
	public function area_choose(){
		$data=M('district')->where(array('upid'=>I('id')))->field('id,name')->select();
		if(!empty($data)){
			json(1,'获取列表信息成功',$data);
		}else if(empty($data)){
			json(0,'获取列表信息为空');
		}else{
			json(0,'获取列表信息失败');
		}
	}
	public function area_choose_all(){
		$shen_name=M('district')->where(array('id'=>I('shen_id')))->getField('name');
		$shi_name=M('district')->where(array('id'=>I('shi_id')))->getField('name');
		$qu_name=M('district')->where(array('id'=>I('qu_id')))->getField('name');
		$data=$shen_name.'>'.$shi_name.'>'.$qu_name;
		if(!empty($data)){
			json(1,'获取信息成功',$data);
		}else if(empty($data['data'])){
			json(0,'获取信息为空');
		}else{
			json(0,'获取信息失败');
		}
	}
	public function province_choose(){
		$data=M('province_info')->order('pr_id asc')->field('pr_id as id,pr_province as name')->select();
		if(!empty($data)){
			json(1,'获取列表信息成功',$data);
		}else if(empty($data)){
			json(0,'获取列表信息为空');
		}else{
			json(0,'获取列表信息失败');
		}
	}
	public function school_choose(){
		$data=M('shool_info')->where(array('sh_city'=>I('id')))->field('sh_id as id,sh_shool as name')->select();
		if(!empty($data)){
			json(1,'获取列表信息成功',$data);
		}else if(empty($data)){
			json(0,'获取列表信息为空');
		}else{
			json(0,'获取列表信息失败');
		}
	}
	public function city_choose(){
		$data=M('city_info')->where(array('ci_province'=>I('id')))->field('ci_id as id,ci_city as name')->select();
		if(!empty($data)){
			json(1,'获取列表信息成功',$data);
		}else if(empty($data)){
			json(0,'获取列表信息为空');
		}else{
			json(0,'获取列表信息失败');
		}
	}
	public function sch_find(){
		$data=M('shool_info')->where(array('sh_id'=>I('id')))->getField('sh_shool');
		if(!empty($data)){
			json(1,'获取信息成功',$data);
		}else if(empty($data)){
			json(0,'获取信息为空');
		}else{
			json(0,'获取信息失败');
		}
	}
	public function get_shanch(){
		
		$shan_id=explode(',',I('shan_id'));
		$data=M('dictionary')->where(array('type'=>2,'pid'=>0))->field('id,name')->select();
		foreach($data as &$v){
			$v['child']=M('dictionary')->where(array('pid'=>$v['id']))->field('id,name')->select();
			foreach($v['child'] as &$s){
				if(in_array($s['id'],$shan_id)){
					$s['choose']=1;
				}else{
					$s['choose']=0;
				}
			}
		}			
		$this->assign('data',$data);
		$this->display();
	}
	public function find_shanch(){
		$shan_id=explode(',',I('shan_id'));
		$data='';
		if(!empty($shan_id)){
			foreach($shan_id as $k=>$v){
				$value=M('dictionary')->where(array('id'=>$v))->getField('name');
				if($k==0){
					$data.=$value;
				}else{
					$data.=','.$value;
				}
			   
		    }
		}
		if(!empty($data)){
			json(1,'获取信息成功',$data);
		}else if(empty($data)){
			json(0,'获取信息为空');
		}else{
			json(0,'获取信息失败');
		}
		
	}
	public function renzheng(){
		$data=I('post.');
		$now_city_id=M('district')->where(array('id'=>$data['area_arr']))->getField('upid');
		$now_pro_id=M('district')->where(array('id'=>$now_city_id))->getField('upid');
		
		$school_city_id=M('shool_info')->where(array('sh_id'=>$data['school_arr']))->getField('sh_city');
		$school_pro_id=M('city_info')->where(array('ci_id'=>$school_city_id))->getField('ci_province');
		
		$cond=array(
		   'headimg'=>$data['headimg'],
		   'credential'=>$data['credential'],
		   'company_des'=>$data['company_des'],
		   'des'=>$data['des'],
		   'keywords'=>$data['keywords'],
		   'now_pro'=>M('district')->where(array('id'=>$now_pro_id))->getField('name'),
		   'now_city'=>M('district')->where(array('id'=>$now_city_id))->getField('name'),
		   'now_area'=>M('district')->where(array('id'=>$data['area_arr']))->getField('name'),
		   'company'=>$data['company'],
		   'school'=>M('shool_info')->where(array('sh_id'=>$data['school_arr']))->getField('sh_shool'),
		   'school_pro'=>M('province_info')->where(array('pr_id'=>$school_pro_id))->getField('pr_province'),
		   'school_city'=>M('city_info')->where(array('ci_id'=>$school_city_id))->getField('ci_city'),
		   'now_city_id'=>$now_city_id,
		   'now_area_id'=>$data['area_arr'],
		   'now_pro_id'=>$now_pro_id,
		   'school_id'=>$data['school_arr'],
		   'school_pro_id'=>$school_pro_id,
		   'school_city_id'=>$school_city_id
		);
		$result=M('check_info')->where(array('user_id'=>$data['user_id']))->find();
		if(!empty($result)){
			$new=M('check_info')->where(array('user_id'=>$data['user_id']))->save($cond);
			if(false!==$new){
				M('user')->where(array('id'=>$data['user_id']))->save(array('status'=>1,'identity'=>2));
				json(1,'信息已提交，审核中请等待');
			}else{
				json(0,'信息提交失败');
			}
		}else{
			$cond_add=array(
			   'user_id'=>$data['user_id'],
			   'create_time'=>time()
			);
			$conds=array_merge($cond,$cond_add);
			$new=M('check_info')->add($conds);
			if($new){
				M('user')->where(array('id'=>$data['user_id']))->save(array('status'=>1));
				json(1,'信息已提交，审核中请等待');
			}else{
				json(0,'信息提交失败');
			}
		}
	}
	public function lvinfo(){
		$this->assign('lv_info',I('lv_info'));
		$this->display();
	}
        
}