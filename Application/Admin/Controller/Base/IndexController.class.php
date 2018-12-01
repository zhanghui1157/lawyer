<?php

namespace Admin\Controller\Base;
header("Content-Type: text/html; charset=utf-8");
class IndexController extends \Admin\Controller\CommonController {
    protected $managerUrl = "/Admin/";
    protected $user_auth = array();
    protected function getresourcechild($id)
    {
        return M('resource')->where(array('pid'=>$id,'display'=>1,'id'=>array('in',$this->user_auth)))->field('id,resourcename,moudle,action,method,ico,link,type')->order('pid asc,orderid asc')->select();
    }
    public function index(){ 

        $pro=M('province_info')->select();
        foreach ($pro as $k => $v) {
            $city=M('city_info')->where(array('ci_province'=>$v['pr_id']))->field('')->select();
            foreach ($city as $kk => $vv) {

                $school=M('shool_info')->where(array('sh_city'=>$vv['ci_id']))->field('sh_shool')->select();
                foreach ($school as $kkk => $vvv) {
                    $city[$kk]=array_push($city,$vvv['sh_shool']);
                }

                
                $pro[$k]=array_push($pro,$vv['ci_city']);
                // dump($vv);
            }
            dump($pro);
        }
       
        dump($pro);exit;
        dump(json_encode($pro,JSON_UNESCAPED_UNICODE));
        exit;

        $user_auth_arr = M()->table(C('DB_PREFIX').'manager manager,'.C('DB_PREFIX').'manager_role mrole, '.C('DB_PREFIX').'role_resource r_resource, '.C('DB_PREFIX').'resource resource')->where('manager.id='.$this->_manager['id'].' and manager.id = mrole.manager_id and mrole.role_id = r_resource.role_id and resource.id = r_resource.resource_id')->field('resource.id as id')->select();
        $user_auth = array();
        foreach($user_auth_arr as $r)
        {
            array_push($user_auth, $r['id']);
        }
        $this->user_auth = $user_auth;
        $htmlStr = '';
        foreach(C('MOUDLELIST') as $k=>$v)
        {
            if($user_auth)
            {
                $menu = $this->getMoudleMenuLink($k);
                if($menu!='')
                {
                	$htmlStr=$htmlStr.$this->createmenu('',$v['name'].'管理',$v['ico'],$menu);
                }

            }
        }
        $this->assign('menu', $htmlStr);
        $this->show();
    }
    /*
     * 生成模块的管理菜单
     */
    protected function getMoudleMenuLink($moudle)
    {
        $resource = M('resource')->where(array('moudle'=>$moudle,'pid'=>0,'display'=>1,'id'=>array('in',$this->user_auth)))->field('id,resourcename,moudle,action,method,link,ico,type')->order('pid asc,orderid asc')->select();
        $sec_html = '';
        foreach($resource as $row)
        {
            if($row['c']=='' && $row['a']=='')//两个参数都空代表还有下级菜单
            {
                $thi_html = '';
                $childlist = $this->getresourcechild($row['id'],$user_auth);
                if($childlist)
                {
                    foreach ($childlist as $child)
                    {
                    	$thi_html=$thi_html.$this->createmenu($this->createlink($child['id'],$child['moudle'],$child['action'], $child['method'],$child['type'], $child['link']),$child['resourcename'],$child['ico'],'');
                    }
                    $sec_html=$sec_html.$this->createmenu('',$row['resourcename'],$row['ico'],$thi_html);
                }
                else
                {
                	$sec_html=$sec_html.$this->createmenu($this->createlink($row['id'],$row['moudle'],$row['action'], $row['method'],$row['type'], $row['link']),$row['resourcename'],$row['ico'],'');
                }
            }
            else
            {
            	$sec_html=$sec_html.$this->createmenu($this->createlink($row['id'],$row['moudle'],$row['action'], $row['method'],$row['type'],$row['link']),$row['resourcename'],$row['ico'],'');
            	
           }            
        }
        return $sec_html;
    }
    public function introduce() {
        $data['member_count']=M('user')->where(array('status'=>0))->count();
        $data['lawyer_count']=M('user')->where(array('status'=>2))->count();
        $data['answer_count']=M('answer')->count();
        $data['order_count']=M('order')->count();
        $data['money_count']=M('pay_log')->count();
        $this->assign('data',$data);
        $this->show();
    }

    public function getNoticeData(){
        $data=M('visit')->order('detail_time asc')->group('detail_time')->field('detail_time')->select();
        foreach ($data as $key => $value) {
            $data1[$key]=$value['detail_time'];
        }
        

        foreach ($data1 as $k => $v) {
            $data2[$k]=M('visit')->where(array('detail_time'=>$v))->count();
        }
        $this->ajaxReturn(array('data1'=>$data1,'data2'=>$data2));
    }

    public function map() {
        if(IS_POST)
        {
            $date = I('post.date');     
            $data = array();
            $data['code'] = 1;
            $data['msg'] = '';
            $this->ajaxReturn($data);
        }
        else
        {  
            $company=M('busi_escort')->order('id desc')->field('id,escort_name,showcolor')->select();
            $this->assign('company',$company);
            $this->assign('regions',zidan(1));
            $this->assign('banks',zidan(2));
            $this->show();
        }
    }
    public function logout() {
        $user = $this->_manager;
        session(C('MANAGERSESSION_ID'), null);
        addlog($user, '注销成功');
        $this->success('管理员身份注销成功',$this->managerUrl.'Base/Login', 1);
    }
    //生成链接
    protected function createlink($id,$m,$c,$a,$type,$link)
    {
        if(empty($link) || $link=='')
        {
            if($c=='' && $a=='')
            {
                return '';
            }
            else
            {
            	if(!empty($type)){
            		return $this->managerUrl.$m.'/'.$c.'/'.$a.'/type/'.$type;
            	}else{
            		return $this->managerUrl.$m.'/'.$c.'/'.$a;
            		
            	}
                
            }
        }
        else
        {
            return $link;
        }
    }
    /*
     * 菜单生成
     */
    protected function createmenu($link,$title,$ico,$sec_html)
    {
        if($link=='')
        {
            return '<li><a href="#"><i class="fa fa-'.$ico.'"></i> <span class="nav-label">'.$title.'</span><span class="fa arrow"></span></a><ul class="nav nav-second-level">'.$sec_html.'</ul></li>';
        }
        else
        {
            return '<li><a class="J_menuItem" href="'.$link.'"><i class="fa fa-'.$ico.'"></i> <span class="nav-label">'.$title.'</span></a></li>';
        }
    }
}
