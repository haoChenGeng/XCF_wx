<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class model_excelOper extends CI_Model {
	private $logfile_suffix;
	public function __construct()
	{
		parent::__construct();
		$this->logfile_suffix = '('.date('Y-m',time()).').txt';
		set_time_limit( 1800 );
		ini_set('memory_limit', '512M');
		require_once( FCPATH.'data/PHPExcel/Classes/PHPExcel.php' );
	}
	
	//获取excel表的内容
	public function getExcelContent($filename,$dataDes){
		$reader = $this->initRead($filename);
		foreach ($dataDes as $key => $val){
			$sheet = $reader->getSheet($val['sheet']);                    //获取excel中的某个表
			$limit = isset($val['limit']) ? $val['limit'] : 0;
			if ($limit >0){
				$rows = $val['row'];                                      //$limit>0 表示获取行号$row之后$limit行的数据($row=1 时仅获取当前行数据)
			}else{
				$rows = $sheet->getHighestRow()+1;                        //$limit<=0  表示获取表$row行到最大行数之前$limit行的数据
			}
			$rows += $limit;
			$j = 0;
			for ($i=$val['row']; $i<$rows; $i++ , $j++) {
				foreach ($val['fieldKey'] as $k=>$v){
					$data[$key][$j][$v] = trim($this->getCell($sheet,$i,$k));
				}
			}
		}
		return $data;
	}
	
	//将数据写入excel文件
	public function writeExcelContent($filename,&$dbData,$exportFieldDes,$exportDataDes){
		$workbook = new PHPExcel();
		$boxFormat = $this->initWorkbook($workbook);
		foreach ($exportFieldDes as $key => $val){
			$workbook->setActiveSheetIndex($key);
			$worksheet = $workbook->getActiveSheet();
			$this->setfieldname($worksheet, $val, $boxFormat);           
			$this->setContent($worksheet,$dbData,$exportDataDes[$key]);
		}
		$this->outputFile($workbook, $filename);
	}
	
	private function initWorkbook(&$workbook){
		// set default font name and size
		$workbook->getDefaultStyle()->getFont()->setName('Arial');
		$workbook->getDefaultStyle()->getFont()->setSize(10);
		$workbook->getDefaultStyle()->getAlignment()->setIndent(1);
		$boxFormat = array(
				'font' => array(
						'name' => 'Arial',
						'size' => '10',
				),
				'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'wrap'       => false,
						'indent'     => 0
				)
		);
		return $boxFormat;
	}
	
	private function initRead($filename){
		$inputFileType = PHPExcel_IOFactory::identify($filename);
		$objReader = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setReadDataOnly(true);
		$reader = $objReader->load($filename);
		return $reader;
	}
	
	private function getCell(&$worksheet,$row,$col,$default_val='') {
		$col -= 1; // we use 1-based, PHPExcel uses 0-based column index
		// we use 1-based, PHPExcel used 1-based row index
		return ($worksheet->cellExistsByColumnAndRow($col,$row)) ? $worksheet->getCellByColumnAndRow($col,$row)->getValue() : $default_val;
	}
	
	private function setCell( &$worksheet, $row, $col, $val, $style=NULL ) {
		$col -= 1; // we use 1-based, PHPExcel uses 0-based column index
		$worksheet->setCellValueByColumnAndRow( $col, $row, $val );
		if ($style) {
			$worksheet->getStyleByColumnAndRow($col,$row)->applyFromArray( $style );
		}
	}
	
	private function setfieldname(&$worksheet, $fieldName, $style=NULL){
		//mergeCellsByColumnAndRow($colIndex, $currRow, $colIndex, ($currRow + $mergeRow - 1))->))->setCellValueByColumnAndRow($colIndex, $currRow, $value);
		foreach ($fieldName as $key => $val){
			if ($key == 'width'){
				foreach ($val as $k=>$v){
					$worksheet->getColumnDimensionByColumn($k-1)->setWidth($v);
				}
			}else{
				$rowCol = explode(",",$key);
				$rowCol[1] --;
				$worksheet->setCellValueByColumnAndRow( $rowCol[1], $rowCol[0], $val );
				if ($style) {
					$worksheet->getStyleByColumnAndRow($rowCol[1], $rowCol[0])->applyFromArray( $style );
				}
				if (isset($rowCol[3])){
					$rowCol[3]--;
					$worksheet->mergeCellsByColumnAndRow($rowCol[1], $rowCol[0], $rowCol[3], $rowCol[2]);
				}
			}
		}
	}
	
	private function setContent(&$worksheet,&$dbData,&$exportDataDes){
		$i=$exportDataDes['row'];
		foreach ($dbData as $key => $val){
			foreach ( $exportDataDes['fieldKey'] as $k => $v ) {
				$this->setCell($worksheet, $i, $k, $val[$v]);
			}
			$i++;
		}
	}
	
	private function outputFile(&$workbook,$filename){
		$workbook->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'('.time().').xls"');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($workbook, 'Excel5');
		$objWriter->save('php://output');
		$this->clearSpreadsheetCache();
	}
	
}
