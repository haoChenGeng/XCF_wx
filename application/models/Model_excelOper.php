<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Model_excelOper extends CI_Model {
	public function __construct()
	{
		parent::__construct();
		set_time_limit(1800);
		ini_set('memory_limit', '1024M');
		require_once( FCPATH.'data/PHPExcel/Classes/PHPExcel.php' );
	}
	
	//获取excel表的内容
	public function getExcelContent($filename,$dataDes){
		$reader = $this->initRead($filename);
		$sheetCount = $reader->getSheetCount();
		$data = array();
		if(isset($dataDes['autoSelectSheet'])){
			for ($sheetNum = 0; $sheetNum < $sheetCount; $sheetNum++){
				$sheet = $reader->getSheet($sheetNum);							//获取excel中的表
				$sheetTitle = $sheet->getTitle('Simple');
				if (isset($dataDes[$sheetTitle])){
					$this->getSheetData($data, $sheet, $dataDes[$sheetTitle], $sheetTitle);
				}
			}
		}else{
			foreach ($dataDes as $key => $val){
				if ($val['sheet'] < $sheetCount){
					$sheet = $reader->getSheet($val['sheet']);                    //获取excel中的某个表
					$this->getSheetData($data, $sheet, $val, $key);
					if (isset($val['sheetTitle'])){
						$data[$key]['sheetTitle'] = $sheet->getTitle('Simple');
					}
				}
			}
		}
		return $data;
	}
	
	private function getSheetData(&$data, &$sheet, $sheetDes, $key){
		$limit = isset($sheetDes['limit']) ? $sheetDes['limit'] : 0;
		if ($limit >0){
			$rows = $sheetDes['row'];                                      //$limit>0 表示获取行号$row之后$limit行的数据($row=1 时仅获取当前行数据)
		}else{
			$rows = $sheet->getHighestRow()+1;                        //$limit<=0  表示获取表$row行到最大行数之前|$limit行|的数据
		}
		if (!isset($sheetDes['fieldKey'])){									//自动获取fieldKey
			$keyRow = $sheetDes['KeyPosition']['row'];
			$startColumn = isset($sheetDes['KeyPosition']['startColumn']) ? $sheetDes['KeyPosition']['startColumn'] : 1;
			if (isset($sheetDes['KeyPosition']['endColumn'])){
				$endColumn = $sheetDes['KeyPosition']['endColumn'];
			}else{
				$endColumn = $sheet->getHighestColumn();
				$endColumn = $this->getColumnNum($endColumn);
			}
			for($i = $startColumn; $i<=$endColumn; $i++){
				$filedKey = trim($this->getCell($sheet,$keyRow,$i));
				if (!empty($filedKey)){
					$sheetDes['fieldKey'][$i] = $filedKey;
				}
			}
		}
		$rows += $limit;
		$j = 0;
		for ($i=$sheetDes['row']; $i<$rows; $i++ , $j++) {
			foreach ($sheetDes['fieldKey'] as $k=>$v){
				$data[$key][$j][$v] = trim($this->getCell($sheet,$i,$k));
			}
		}
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
				if (isset($val[$v])){
					$this->setCell($worksheet, $i, $k, $val[$v]);
				}
			}
			$i++;
		}
		if (isset($exportDataDes['groupRow'])){
			foreach ($exportDataDes['groupRow'] as $key =>$val){
				foreach ($val as $k=>$v){
					for($ii = $v[0]; $ii <= $v[1]; $ii++){
						$worksheet->getRowDimension($ii)->setOutlineLevel($k);
					}
				}
			}
		}
		if (isset($exportDataDes['groupColumn'])){
			foreach ($exportDataDes['groupColumn'] as $key =>$val){
				foreach ($val as $k=>$v){
					for($ii = $v[0]; $ii <= $v[1]; $ii++){
						$worksheet->getColumnDimension($this->getColumnAlph($ii))->setOutlineLevel($k);
					}
				}
			}
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
	
	private function getColumnNum($column){
		if (is_numeric($column)){
			return $column;
		}
		$len = strlen($column);
		$num = 0;
		$multiplicator = 1;
		for ($i=$len-1;$i>=0;$i--){
			$num +=  (ord($column[$i])-64)*$multiplicator;
			$multiplicator = $multiplicator*26; 
		}
		return $num;
	}
	
    private function getColumnAlph($column){
    	if (!is_numeric($column)){
    		return $column;
    	}
    	$Alph = '';
    	$multiplicator = 1;
    	while ($column > 0){
    		$i = ($column-1) % 26;
    		$column = $column -($i+1);
    		$Alph = chr($i+65).$Alph;
    		$column = intval($column/26);
    	}
    	return $Alph;
    }
	
	public function excelTime($inputTime,$style){
		if (is_numeric($inputTime)){
			$secondNum = intval(($inputTime - 25569) * 3600 * 24)-28800;   //转换成1970年以来的秒数
			$returnTime = date($style, $secondNum);
		}else{
			$returnTime = date($style,strtotime($inputTime));
		}
		return $returnTime;
	}
	
}
