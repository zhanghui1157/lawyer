<?php 
namespace Admin\Widget;
use Think\Controller;
/**
 * {:W('Admin/Visit/showData')}
 */
class VisitWidget extends Controller{
	public function showData(){
		$data=M('visit')->order('detail_time asc')->group('detail_time')->field('detail_time')->select();
        foreach ($data as $key => $value) {
            $data1[$key]=$value['detail_time'];
        }
        

        foreach ($data1 as $k => $v) {
            $data2[$k]=M('visit')->where(array('detail_time'=>$v))->count();
        }
        $array=array('data1'=>$data1,'data2'=>$data2);
        $this->assign('array',$array);
        $this->display(T('Application://admin@Widget/visit'));
	}
}