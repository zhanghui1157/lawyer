<?php
namespace Admin\Controller\Base;
class DictionaryController extends \Admin\Controller\CommonController {
    public function lists()
    {
        $Condition = array();
        
        $Condition['name']=array('like','%'.I('get.name').'%');
        $Condition['f_name']="Home";
        $Condition['f_name']="Home";
        $Condition['type']=I('type');
        $this->RecCount = M('dictionary')->where($Condition)->count();
        $Page   =   new \Org\Util\UIPage($this->RecCount,10);
        $list = M('dictionary')->where($Condition)->order('path asc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($list as &$r)
        {

            if($r['pid']!=0)
            {
                $r['parent'] = M('cat')->where(array('id'=>$r['pid']))->find();
            }
        }
        $this->list=create_cat_name2($list);
        $this->assign('page',$Page->show());
        $this->assign('search',I('get.'));        
        $this->show();
    }
    /*
     * 排序页面
     */
    public function listorder()
    {
        $Condition = array();
        $Condition['name']=array('like','%'.I('get.name').'%');
        $Condition['f_name']="Home";
        $Condition['type'] = I('type');
        $this->RecCount = M('dictionary')->where($Condition)->count();
        $Page   =   new \Org\Util\UIPage($this->RecCount,10);
        $list = M('dictionary')->where($Condition)->order('path asc')->limit($Page->firstRow.','.$Page->listRows)->order('listorder asc')->select();
        foreach($list as &$r)
        {
            if($r['pid']!=0)
            {
                $r['parent'] = M('dictionary')->where(array('id'=>$r['pid']))->find();
            }
        }
        $this->list=create_cat_name2($list);
        $this->assign('page',$Page->show());
        $this->assign('search',I('get.'));        
        $this->show();
    }
    /*
     * ajax排序
     */
    public function changelistorder()
    {
        $catid = intval(I('get.id'));
        if($catid)
        {
            $action = I('get.action');
            $cat = M('dictionary')->where(array('id'=>$catid))->find();
            $cat2 = M('dictionary')->where(array('listorder'=>array($action=='up'?'lt':'gt',$cat['listorder'])))->find();
            if($cat2)
            {
                $listorder = ($action=='up'?$cat2['listorder']-1:$cat2['listorder']+1);
                M('dictionary')->where(array('id'=>$catid))->save(array('listorder'=>$listorder));
            }
            else
            {
                $listorder = ($action=='up'?$cat['listorder']-1:$cat['listorder']+1);
                M('dictionary')->where(array('id'=>$catid))->save(array('listorder'=>$listorder));
            }
            $this->setMsgExit('操作成功');
        }
    }
    public function edit()
    {        
        if(IS_POST)
        {
            $catid = intval(I('post.id'));
            
            if($catid==0)
            {
                $cat = array();
                $cat['name'] = I('post.name');
                $cat['pid'] = I('post.cat_id');
                if($cat['pid']==0)
                {
                    $cat['path'] = '';
                    $cat['level'] = 1;
                }
                else
                {
                    $pcat = M('dictionary')->where(array('id'=>$cat['pid']))->find();
                    $cat['path'] = $pcat['path'].($pcat['path']==''?'':',').$pcat['id'];
                    $cat['level'] = $pcat['level']+1;
                }
                $cat['child'] = 0;
                $cat['arrchildid'] = '';
                $cat['allarrchildid'] = '';
                $cat['addtime'] = time();
                $cat['listorder'] = I('post.listorder');
                $cat['type'] = I('post.type');
                $cat['f_name'] = "Home";
                $catid = M('dictionary')->add($cat);
                //更新栏目参数
                $this->update_home_cat($catid);
                addlog($this->_manager,'添加栏目分类['.$name.']成功');
                $this->setMsgExit('添加栏目分类成功'); 
            }
            else
            {
                $cat = array();
                $cat['name'] = I('post.name');
                $cat['listorder'] = I('post.listorder');
                M('dictionary')->where(array('id'=>$catid))->save($cat); 
                //更新栏目参数
                $this->update_home_cat($catid);
                addlog($this->_manager,'编辑栏目分类['.$name.']成功');
                $this->setMsgExit('编辑分类成功');
            }
        }else{
            $catid = intval(I('get.id'));
            if($catid==0)
            {
                $this->assign('entity',array('id'=>0,'pid'=>I('get.pid'),'listorder'=>255));
            }
            else
            {
                $entity = M('dictionary')->where(array('id'=>$catid))->find();
                $this->assign('entity',$entity);
            }
            $cond['f_name']='Home';
            if(!empty(I('type'))){
            	$cond['type']=I('type');
            }
            $catlist = M('dictionary')->field('id,name,level,allarrchildid')->where($cond)->order('id asc')->select();
            array_unshift($catlist,array('id'=>'0','name'=>'顶级分类'));
            $this->assign('catlist',create_cat_name2($catlist));
            $this->assign('type',I('type'));
            $this->show();
        }
    }
    public function del()
    {
        if(I('get.id'))
        {
            $catid = I('get.id');
            $cat = M('dictionary')->where(array('id'=>$catid))->find();
            //存在子栏目禁止删除
            if(M('dictionary')->where(array('pid'=>$catid))->find())
            {
                $this->setErrorExit('该栏目有子栏目,删除失败');
            }
            //存在数据禁止删除
            $Model = M('news');
            switch($cat['moudle'])
            {
                case 'News':
                    $Model = M('news');
                    break;
                case 'Page':
                    $Model = M('page');
                    break;
                case 'Spec':
                    $Model = M('spec');
                    break;
                case 'Download':
                    $Model = M('download');
                    break;
            }
            if($Model->where(array('cat_id'=>$catid))->find())
            {
                $this->setErrorExit('该栏目下有数据,不能删除');
            }
            //删除相关资源
            $parent_resource = M('resource')->where(array('m'=>'home','resourcename'=>'内容管理'))->find();
            $this->delresource($parent_resource['id'],$this->createlink($cat['id'],$cat['moudle'],$cat['child']));
            //删除栏目
            M('dictionary')->where(array('id'=>array('eq',$catid)))->delete();
            //更新父级栏目参数
            $this->update_home_cat($cat['pid']);
            
            addlog($this->_manager,'删除单条分类['.$catid.']成功');
            $this->setMsgExit('已删除一条记录');
        }else
        {
            $this->setErrorExit('请至少指定要删除的对象');
        }
    }
    public function updateresource()
    {
        $parent_resource = M('resource')->where(array('m'=>'Home','resourcename'=>'内容管理'))->find();
        $channellist = M('cat')->where(array('ischannel'=>1))->order('id asc')->select();
        $orderid = 1;
        foreach ($channellist as $channel)
        {
            $resource = array();
            $resource['link'] = $this->createlink($channel['id'],$channel['moudle'],$channel['child']);
            if($resource['link'] !='')
            {
                $resource['resourcename'] = $channel['name'].'管理';
                if(!M('resource')->where(array('link'=>$resource['link'],'m'=>'Home'))->find())
                {
                    $this->addresource('Home',$channel['name'],'Home/'.$channel['moudle'], 'lists','reorder',1,$parent_resource['id'],$this->createlink($channel['id'], $channel['moudle'], $channel['child']),$orderid);
                    $orderid++;
                }
                else
                {
                    M('resource')->where(array('link'=>$resource['link'],'m'=>'Home'))->save($resource);
                }
            }
        }
        //$resourcelist = M('resource')->where(array('pid'=>$parent_resource['id']))->order('id asc')->select();
        $this->setMsgExit('已更新管理菜单,请刷新页面');
    }
    protected function createlink($id,$moudle,$child)
    {
        $url = '';
        switch ($moudle)
        {
            case 'Page':
                if($child==0)
                {
                    $url = '/Admin/Home/Page/lists/cat_id/'.$id;
                }
                else
                {
                    $url = '/Admin/Home/Page/lists/cat_id/'.$id;
                }
                break;
            case 'News':
                $url = '/Admin/Home/News/lists/cat_id/'.$id;
                break;
            case 'Spec':
                $url = '/Admin/Home/Spec/lists/cat_id/'.$id;
                break;
            case 'Download':
                $url = '/Admin/Home/Download/lists/cat_id/'.$id;
                break;
        }
        return $url;
    }
    protected function delresource($pid,$link) {
        $r = M('Resource')->where(array('pid'=>$pid,'link'=>$link))->find();
        if($r){
            M('RoleResource')->where(array('resource_id'=>$r['id']))->delete();
            return M('Resource')->where(array('id'=>$r['id']))->delete();
        }        
        else
        {
            return 0;
        }
    }
    protected function addresource($moudle,$title, $c, $a,$ico,$display,$pid,$link,$orderid) {
        return M('Resource')->add(array('resourcename' => $title, 'c' => $c, 'a' => $a,'addtime'=>time(),'ico'=>$ico,'display'=>$display,'m'=>$moudle,'pid'=>$pid,'orderid'=>$orderid,'readonly'=>1,'link'=>$link));
    }
    protected function update_home_cat($cat_id)
    {
        //栏目路径更新
        $catlist = M('dictionary')->order('level asc')->select();
        foreach($catlist as $r)
        {
            if($r['pid']==0)
            {
                M('dictionary')->where(array('id'=>$r['id']))->save(array('path'=>$r['id']));
            }
            else
            {
                $pcat = M('dictionary')->where(array('id'=>$r['pid']))->find();
                M('dictionary')->where(array('id'=>$r['id']))->save(array('path'=>$pcat['path'].','.$r['id']));
            }
        }
        //子栏目参数更新
        $catlist = M('dictionary')->order('level desc')->select();
        foreach($catlist as $r)
        {
            $_cat = array();
            $_cat['child'] = M('dictionary')->where(array('pid'=>$r['id']))->count();
			$str=createsqlarrstr(M('dictionary')->where(array('pid'=>$r['id']))->getField('id',true));
            $_cat['arrchildid'] =empty($str)?'':$str;
            if($_cat['child']==0)
            {
                $_cat['allarrchildid'] = '';
            }
            else
            {
                $allarrchild = M('dictionary')->field('allarrchildid')->where(array('pid'=>array('eq',$r['id'])))->find();
                $_cat['allarrchildid'] = appendsqlarrstr($_cat['arrchildid'],$allarrchild['allarrchildid']);
            }
            M('dictionary')->where(array('id'=>$r['id']))->save($_cat);
        } 
    }
}
?>