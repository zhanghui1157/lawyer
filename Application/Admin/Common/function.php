<?php
/*
 * 写入管理日志
 */

function addlog($manager, $context) {
    $ip = get_client_ip();
    $time = time();
    M('managerLog')->add(array('manager_id' => $manager['id'], 'managername' => $manager['adminname'],'addtime' => $time, 'ip' => $ip, 'loginfo' => $context));
}
//获取指定的提示信息
function messge($key) {
    return $message = L($key);
}
/*
 * 获取系统资源
 */
function getresource($moudle)
{
    return M('resource')->where(array('m'=>$moudle))->order('display desc,orderid asc')->select();
}


//获取地区列表
function area_list(){
	 $one_level=M('region')->where(array('type'=>0))->order('id asc')->select();
	 if(!empty($one_level)){
	 	foreach($one_level as $k=>$v){
	 		$one_level[$k]['two_level']=M('region')->where(array('pid'=>$v['id']))->order('id asc')->select();
	 		if(!empty($one_level[$k]['two_level'])){
	 			foreach($one_level[$k]['two_level'] as $key=>$val){
	 				$one_level[$k]['two_level'][$key]['three_level']=M('region')->where(array('pid'=>$val['id']))->order('id asc')->select();
	 			}
	 		}
	 	}
	 	
	 }
	 
	 return $one_level;
}


// function saveExcel($data,$name='',$titleRow=null){
// 	if(empty($name)){
// 		$name=date('Ymd');
// 	}
//     error_reporting(E_ALL);
//     date_default_timezone_set('Europe/London');
//     Vendor('PHPExcel');
//     //import("Org.Util.PHPExcel");   
//     //import("Org.Util.PHPExcel.IOFactory");
//     $objPHPExcel = new \PHPExcel();
//     /*以下是一些设置 ，什么作者  标题啊之类的*/
//     $objPHPExcel->getProperties()->setCreator("huike")
//                            ->setLastModifiedBy("huike")
//                            ->setTitle('excel export')
//                            ->setSubject('excel export')
//                            ->setDescription('excel export')
//                            ->setKeywords("excel")
//                           ->setCategory("result file");
//      /*以下就是对处理Excel里的数据*/
//     $sheet=$objPHPExcel->setActiveSheetIndex(0);
//     //列名
//     $colNames=array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
//     $startRow=1;
//     //第一行设置列名，为空则跳过
//     if(is_array($titleRow)){
//     	foreach($titleRow as $k=>$v){
//     		$key=$colNames[$k].$startRow;
//     		$sheet->setCellValue($key, $v);
// 		}
//     	$startRow=2;    	
//     }
    
    
//     foreach($data as $k => $row){
//     	//循环行
//     	$rowNum=$k+$startRow;  
//     	$colIndex=0;//起始列
//     	foreach($row as $field){
//     		//循环列
//          	//$objPHPExcel->setActiveSheetIndex(0)
//             $colName=$colNames[$colIndex].$rowNum;               
//         	$sheet->setCellValue($colName,$field);
//         	$colIndex++;//下一格
//         }
//     }
//     $objPHPExcel->getActiveSheet()->setTitle('sheet1');
//     $objPHPExcel->setActiveSheetIndex(0);
//      header('Content-Type: application/vnd.ms-excel');
//      header('Content-Disposition: attachment;filename="'.$name.'.xls"');
//      header('Cache-Control: max-age=0');
//      $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
//      $objWriter->save('php://output');
//      exit;
// }

