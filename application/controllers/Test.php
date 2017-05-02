<?php
if (!defined('BASEPATH')){
	exit('No direct script access allowed');
}
    
// header("Content-type: text/html; charset=utf-8");
#include_once(__DIR__.DIRECTORY_SEPARATOR.'/weixin/api.php');

class Test extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
    }
    
    function index() {
    	$this->load->library('Fund_interface');
//     	var_dump($this->fund_interface->RenewFundAESKey('123456'));
    	$res = $this->fund_interface->Trans_applied('20170301', '20170501');
    	var_dump($res);
    }
    
}