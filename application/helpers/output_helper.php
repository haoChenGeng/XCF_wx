<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


function Message($arr) {
	ob_start();
	$CI =& get_instance();
	$CI->load->view('ui/Message', $arr);
	ob_end_flush();
	exit();
}

function Message_select($url,$arr) {
	ob_start();
	$CI =& get_instance();
	$CI->load->view($url, $arr);
	ob_end_flush();
	exit();
}
