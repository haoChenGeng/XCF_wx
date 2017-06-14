<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
header("Content-type: text/html; charset=utf-8");

class Xnxcfindex extends MY_Controller {
    function __construct() {
		parent::__construct();
    }
    
    function index() {
    	if(ENVIRONMENT=='production')
    		$fileName = str_replace('//', '/', WXCODEPATH.'application/controllers/User.php');
   		else
   			$fileName = str_replace('//', '/', FCPATH.'application/controllers/User.php');
    	require_once $fileName;
    	$callClass = new User;
    	$callClass->home();
    }
    
	public function WK8YGc3Yi2oP3($accessCode=''){
		if (!empty($accessCode) && isset($_SESSION['accessList'][$accessCode])){
			$loadPHP = current($_SESSION['accessList'][$accessCode]);
			if(ENVIRONMENT=='production')
				$fileName = str_replace('//', '/', WXCODEPATH.'application/controllers'.$loadPHP.'.php');
			else 
				$fileName = str_replace('//', '/', FCPATH.'application/controllers'.$loadPHP.'.php');
			require_once $fileName;
			$className = explode('/', $loadPHP);
			$className = end($className);
			$callClass = new $className;
			$callClass->index($accessCode);
		}else{
			$fileName = str_replace('//', '/', $application_folder.'/controllers/admin/Account.php');
			//$fileName = str_replace('//', '/', FCPATH.'application/controllers/admin/Account.php');
			require_once $fileName;
			$callClass = new Account;
			$callClass->login();
		}
	}
	
}