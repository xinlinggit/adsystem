<?php
namespace cms\common\model;
class PHPExcel
{
	//Excel输出
	public static function excelPut($Excel,$expTableData){
		$Excel['sheetTitle']=iconv('utf-8', 'gb2312',$Excel['sheetTitle']);
		$cellName = $Excel['cellName'];
		$xlsCell = $Excel['xlsCell'];
		$cellNum = count($xlsCell);//计算总列数
		$dataNum = count($expTableData);//计算数据总行数
		vendor("PHPExcel.PHPExcel");
		$objPHPExcel = new \PHPExcel();
		$sheet0 = $objPHPExcel->getActiveSheet(0);
		$sheet0->setTitle("Sheet1");
		//设置表格标题A1
		$sheet0->mergeCells('A1:'.$cellName[$cellNum-1].'1');//表头合并单元格
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1',$Excel['fileName']);
		// $objPHPExcel->setActiveSheetIndex(0)
		// ->setCellValue('A1',$Excel['sheetTitle'].date('Y年m月d日',time()));
		$sheet0->getStyle('A1')->getFont()->setSize(20);
		$sheet0->getStyle('A1')->getFont()->setName('微软雅黑');
		//设置行高和列宽
		//横向水平宽度
		if(isset($Excel['H'])){
			foreach ($Excel['H'] as $key => $value) {
				$sheet0->getColumnDimension($key)->setWidth($value); 
			}
		}
		//纵向垂直高度
		if(isset($Excel['V'])){
			foreach ($Excel['V'] as $key => $value) {
				$sheet0->getRowDimension($key)->setRowHeight($value);
			}
		}
		//第二行：表头要加粗和居中，加入颜色
		$sheet0->getStyle('A1')
		->applyFromArray(['font' => ['bold' => false],'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical'=>\PHPExcel_Style_Alignment::VERTICAL_CENTER]]);
		$setcolor = $sheet0->getStyle("A2:".$cellName[$cellNum-1]."2")->getFill();
		$setcolor->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
		$colors=['00a000','53a500','3385FF','00a0d0','D07E0E','c000c0','0C8080','EFE4B0'];//设置总颜色
		$selectcolor=$colors[mt_rand(0,count($colors)-1)];//获取随机颜色
		$setcolor->getStartColor()->setRGB($selectcolor);
		//根据表格数据设置列名称
		for($i=0;$i<$cellNum;$i++){
			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue($cellName[$i].'2', $xlsCell[$i][1])
			->getStyle($cellName[$i].'2')
			->applyFromArray(['font' => ['bold' => true],'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical'=>\PHPExcel_Style_Alignment::VERTICAL_CENTER]]);
		}
		//body：渲染表中数据内容部分
		for($i=0;$i<$dataNum;$i++){
			for($j=0;$j<$cellNum;$j++){
				$sheet0->getStyle($cellName[$j].($i+3))->applyFromArray(['alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical'=>\PHPExcel_Style_Alignment::VERTICAL_CENTER]]);
				$sheet0->setCellValueExplicit($cellName[$j].($i+3),$expTableData[$i][$xlsCell[$j][0]],\PHPExcel_Cell_DataType::TYPE_STRING);
				$sheet0->getStyle($cellName[$j].($i+3))->getNumberFormat()->setFormatCode("@");
			}
		}
		//设置边框
    	$sheet0->getStyle('A2:'.$cellName[$cellNum-1].($i+2))->applyFromArray(['borders' => ['allborders' => ['style' => \PHPExcel_Style_Border::BORDER_THIN]]]);
		//$sheet0->setCellValue("A".($dataNum+10)," ");//多设置一些行
		header('pragma:public');
		header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$Excel['sheetTitle'].'.xlsx"');
		header("Content-Disposition:attachment;filename=".$Excel['fileName'].".xlsx");
		//attachment新窗口打印inline本窗口打印
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	}
}