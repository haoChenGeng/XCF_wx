<?php

/* 	if (!defined('BASEPATH'))
		exit('No direct script access allowed'); */

	function myLog($fileName, $message, $mode=FILE_APPEND){
		$path = FCPATH.'log/';
		$logfile_suffix = '-'.date('Ymd',time()).'.log';
		file_put_contents($path.$fileName.$logfile_suffix,date('Y-m-d H:i:s',time())."\t".$message."\r\n\r\n",$mode);
	}
