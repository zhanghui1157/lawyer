<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller\Base;

/**
 * Description of ItserviceController
 *
 * @author 陈近荣
 */
class ItserviceController extends \Admin\Controller\CommonController {
    /*
     * 错误报告列表
     */
    public function postloglists()
    {
        $condition = array();
        $search = I('get.');
        if(I('get.enddate')!='' && I('get.startdate')!='')
        {
            $Condition['addtime'] = array(array('egt',strtotime(I('get.startdate'))),array('elt',strtotime(I('get.enddate'))),'and');
        }
        elseif(I('get.startdate')!='')
        {
            $Condition['addtime']=array('egt',  strtotime(I('get.startdate')));
        }
        elseif(I('get.enddate')!='')
        {
            $Condition['addtime']=array('elt',  strtotime(I('get.enddate')));
        }
        if($search['typeid']&&$search['typeid']!=-1)
        {
            $condition['typeid'] = $search['typeid'];
        }
        $condition['ip'] = array('like','%'.$search['ip'].'%');
        $this->RecCount = M('postlog')->where($Condition)->count();
        $Page   =   new \Org\Util\UIPage($this->RecCount,10);
        $show       = $Page->show();// 分页显示输出
        $list = M('postlog')->where($Condition)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->list=$list;
        $this->assign('search',I('get.'));
        $this->assign('page',$show);
        $this->show();
    }
    /*
     * 系统调试日志列表
     */
    public function debuglists()
    {
        $condition = array();
        $search = I('get.');
        if(I('get.enddate')!='' && I('get.startdate')!='')
        {
            $Condition['addtime'] = array(array('egt',strtotime(I('get.startdate'))),array('elt',strtotime(I('get.enddate'))),'and');
        }
        elseif(I('get.startdate')!='')
        {
            $Condition['addtime']=array('egt',  strtotime(I('get.startdate')));
        }
        elseif(I('get.enddate')!='')
        {
            $Condition['addtime']=array('elt',  strtotime(I('get.enddate')));
        }
        if($search['typeid']&&$search['typeid']!=-1)
        {
            $condition['typeid'] = $search['typeid'];
        }
        if($search['level']&&$search['level']!=-1)
        {
            $condition['level'] = $search['level'];
        }
        $condition['remark'] = array('like','%'.$search['remark'].'%');
        $this->RecCount = M('systemLog')->where($Condition)->count();
        $Page   =   new \Org\Util\UIPage($this->RecCount,10);
        $show       = $Page->show();// 分页显示输出
        $list = M('systemLog')->where($Condition)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->list=$list;
        $this->assign('search',I('get.'));
        $this->assign('page',$show);
        $this->show();
    }
}
