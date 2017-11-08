<?php

/* 	if (!defined('BASEPATH'))
		exit('No direct script access allowed'); */

	function myLog($fileName, $message, $mode=FILE_APPEND){
		$path = FCPATH.'log/';
		$logfile_suffix = '-'.date('Ymd',time()).'.log';
		if (!empty($_POST) && empty($_POST['havingLog'])){
			file_put_contents($path.$fileName.$logfile_suffix,date('Y-m-d H:i:s',time())."\t输入数据为".serialize($_POST)."\r\n\r\n",$mode);
			$_POST['havingLog'] = 1;
		}
		file_put_contents($path.$fileName.$logfile_suffix,date('Y-m-d H:i:s',time())."\t".$message."\r\n\r\n",$mode);
	}
