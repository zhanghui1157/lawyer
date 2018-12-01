<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller\Base;

/**
 * Description of SelectWinController
 *
 * @author 陈近荣
 */
class SelectWinController extends \Admin\Controller\CommonController {
    /*
     * 选择图标
     */
    public function ico()
    {
        $this->show();
    }
    public function selectmerchant()
    {
        $Condition = array(); 
        $Condition['name']=array(array('like','%'.I('get.name').'%'));
        $this->RecCount = M('merchant')->where($Condition)->count();
        $Page   =   new \Think\Page($this->RecCount,$this->PageSize);
        $list = M('merchant')->field('id,name')->where($Condition)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->list=$list;
        $this->assign('page',$Page->show());
        $this->assign('search',I('get.'));
        $this->show();
    }
    public function selectshop()
    {
        $Condition = array(); 
        $Condition['shopname']=array(array('like','%'.I('get.name').'%'));
        $this->RecCount = M('shop')->where($Condition)->count();
        $Page   =   new \Think\Page($this->RecCount,$this->PageSize);
        $list = D('ShopView')->where($Condition)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->list=$list;
        $this->assign('page',$Page->show());
        $this->assign('search',I('get.'));
        $this->show();
    }
    public function getLocation()
    {
        $this->assign('search',I('get.'));
        $this->show();
    }
}
