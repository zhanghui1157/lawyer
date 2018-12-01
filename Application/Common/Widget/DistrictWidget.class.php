<?php  
namespace Common\Widget;

use Think\Controller;

class DistrictWidget extends Controller{

	public function J_China_City($param){
		$this->assign('param', $param);
        $this->display(T('Application://Common@Widget/district'));
	}

}