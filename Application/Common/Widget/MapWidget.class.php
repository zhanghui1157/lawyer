<?php 
namespace Common\Widget;

use Think\Controller;
/**
* 百度地图插件
* create by zhanghui
* 2018-6-29
*
* {:W('Common/Map/showMap',array(array('location'=>'gps','location_addr'=>'gps_addr','dataLocation'=>$entity['gps'],'dataAddr'=>$entity['gps_addr'])))}
*
* 使用方法及参数介绍
* location--------坐标字段
* location_addr---------坐标地理位置解析后的地址字段
* dataLocation-----------已有的数据的地理位置坐标
* dataAddr--------------已有的地理位置名称
*/


class MapWidget extends Controller{
	public function showMap($param){
		$this->assign('param',$param);
		$this->display(T('Application://Common@Widget/map'));
	}
}