<?php
namespace Admin\Controller\Money;
class CashCheckController extends \Admin\Controller\CommonController {
    protected $Mod='';

    public function _initialize(){
        parent::_initialize();
        $this->Mod=D('MoneyLog');
    }
    public function lists(){ 


        $where['status']=1;
        $data=$this->Mod->getAllList($where);

        $this->assign('list',$data['list']);
        $this->assign('count',$data['count']);
        $this->assign('page',$data['page']);
        $this->display('lists');
    }
	
	public function toExcel(){
        Vendor('PHPExcel.PHPExcel');
	    //$objPHPExcel=\PHPExcel_IOFactory::load($file);//实例化excel类库
        //创建对象
        $excel = new \PHPExcel();
        $excel->getActiveSheet()->setTitle('提现列表');
 
        // 设置单元格高度
        // 所有单元格默认高度
        $excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(25);
        // 第一行的默认高度
        $excel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);
        // 垂直居中
        $excel->getActiveSheet()->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        // 设置水平居中
        $excel->getActiveSheet()->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
 
        //Excel表格式
        $letter = array('A','B','C','D','E','F','F','G','H','I','J','K','L');
        //表头数组
        $tableheader = array('编号','律师姓名','开户行','开户名','卡号','提现金额');
        $tablestyle = array(
            array('width'=>'5'),
            array('width'=>'20'),
            array('width'=>'100'),
            array('width'=>'20'),
            array('width'=>'100'),
            array('width'=>'10')
        );
 
        // id , plate_num，color，msg，place，name，time,phone，weixin，wx_name，status，process
        //填充表头信息

		$where['status']=1;
        $complain_info=$this->Mod->getAllList($where);

        for($i = 0;$i < count($tableheader);$i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1","$tableheader[$i]");
            $excel->getActiveSheet()->getColumnDimension($letter[$i])->setWidth($tablestyle[$i]['width']);
        }
        //填充表格信息
        for ($i = 2;$i <= count($complain_info) + 1;$i++) {
            $data = $complain_info['list'][$i - 2];
   
            $excel->getActiveSheet()->setCellValue("$letter[0]$i","{$data[$i-1]}");
            $excel->getActiveSheet()->setCellValue("$letter[1]$i","{$data['user_name']}");
            $excel->getActiveSheet()->setCellValue("$letter[2]$i","{$data['bank_num']}");
            $excel->getActiveSheet()->setCellValue("$letter[3]$i","{$data['bank_name']}");
            $excel->getActiveSheet()->setCellValue("$letter[4]$i","{$data['bank_card']}");
            $excel->getActiveSheet()->setCellValue("$letter[5]$i","{$data['money']}");
        }
 
        //创建Excel输入对象
        $write = new \PHPExcel_Writer_Excel5($excel);
        $filename = RUNTIME_PATH .'file/'.date("Y-m-d-H.i.s",time()).'.xls';
        $write->save($filename);
 
        // 进行下一步文件压缩
        if($is_download_mv){
            $this->mv_arr[] = $filename;
            $this->filezip();
        }else{
            $result['code'] =200;
            $result['filename'] =$filename;
        }
        //直接下载的代码
        header("Pragma: public");
        header("Expires: 0");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="提现列表.xls"');
        header("Content-Transfer-Encoding:binary");
        $res=$write->save('php://output');
		if($res){
			echo '文件服务器存储路径为： '.$filename;
		}else{
			echo '打印失败';
		}
		$this->display();
    }



    public function cashCheck(){
        $id=I('get.id');
        $data['status']=I('get.status');
        $data['check_time']=time();
        $res=$this->Mod->where(array('id'=>$id))->save($data);
        if($res){
            $this->setMsgExit('成功,等待发放');
        }else{
            $this->setErrorExit('服务器繁忙');
        }
    }
}
?>